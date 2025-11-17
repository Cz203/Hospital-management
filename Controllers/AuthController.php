<?php

require_once 'Models/Admin.php';
require_once 'Models/Doctor.php';
require_once 'Models/Patient.php';
require_once 'config/security.php';

class AuthController
{
    // ===== Constants =====
    private const ERROR_MESSAGES = [
        'empty_fields' => "Vui lòng điền đầy đủ thông tin!",
        'invalid_credentials' => "Số điện thoại hoặc mật khẩu không đúng!",
        'invalid_specialization_xray' => "Tài khoản không thuộc chuyên khoa Chẩn đoán hình ảnh.",
        'invalid_specialization_sieuam' => "Tài khoản không thuộc chuyên khoa Siêu âm.",
        'invalid_admin_credentials' => "Số điện thoại & Mật khẩu không hợp lệ!"
    ];

    // ===== Private helpers (no behavior change) =====
    private function normalizePhoneLocal($raw)
    {
        $raw = trim((string)$raw);
        if ($raw === '') return $raw;
        // prefer using SMSController if available to keep a single normalization rule
        try {
            require_once 'Controllers/SMSController.php';
            if (class_exists('SMSController')) {
                $sms = new SMSController();
                return $sms->normalizePhoneNumber($raw);
            }
        } catch (Exception $e) {
        }
        // fallback simple normalization for 0/84
        if (str_starts_with($raw, '0')) return '84' . substr($raw, 1);
        if (str_starts_with($raw, '84')) return $raw;
        return $raw;
    }
    private function setUserSessionSafe(array $user, $role, array $extra = [])
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'] ?? null;
        $_SESSION['user_name'] = $user['ten'] ?? $user['name'] ?? 'Bác sĩ';
        $_SESSION['last_activity'] = time();
        // Store minimal state: id + role for routing/authorization; avoid PII (name/email)
        $_SESSION['user_role'] = is_string($role) ? $role : '';
    }

    // ===== Common Login Logic =====
    private function handleLogin($role, $modelClass, $redirectPath, $loginPath, $specializationCheck = null, $customErrorMsg = null, $securityOptions = [])
    {
        if ($this->isLoggedIn()) {
            $this->redirectIfLoggedInToDashboard();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection (chỉ cho admin)
            if (isset($securityOptions['csrf']) && $securityOptions['csrf']) {
                $csrfToken = $_POST['csrf_token'] ?? '';
                if (!SecurityConfig::validateCSRFToken($csrfToken)) {
                    $_SESSION['error'] = "Token bảo mật không hợp lệ!";
                    header("Location: ./{$loginPath}");
                    exit();
                }
            }

            // Rate Limiting (chỉ cho admin)
            if (isset($securityOptions['rate_limit']) && $securityOptions['rate_limit']) {
                if (!SecurityConfig::checkRateLimit('admin_login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'))) {
                    $_SESSION['error'] = "Quá nhiều lần thử. Vui lòng thử lại sau 15 phút.";
                    header("Location: ./{$loginPath}");
                    exit();
                }
            }

            // Input sanitization (chỉ cho admin)
            if (isset($securityOptions['sanitize']) && $securityOptions['sanitize']) {
                $phone = SecurityConfig::sanitizeInput($_POST['phone'] ?? '');
            } else {
                $phone = $_POST['phone'] ?? '';
            }
            $password = $_POST['password'] ?? '';

            if (empty($phone) || empty($password)) {
                $_SESSION['error'] = self::ERROR_MESSAGES['empty_fields'];
                header("Location: ./{$loginPath}");
                exit();
            }

            $normalized = $this->normalizePhoneLocal($phone);
            $model = new $modelClass();
            $user = $model->login($normalized, $password);

            if ($user) {
                // Specialization check nếu cần
                if ($specializationCheck && !$specializationCheck($user)) {
                    $_SESSION['error'] = $customErrorMsg ?? self::ERROR_MESSAGES['invalid_credentials'];
                    header("Location: ./{$loginPath}");
                    exit();
                }

                $this->setUserSessionSafe($user, $role);
                header("Location: ./{$redirectPath}");
                exit();
            }

            $errorMsg = $customErrorMsg ?? self::ERROR_MESSAGES['invalid_credentials'];
            $_SESSION['error'] = $errorMsg;
            header("Location: ./{$loginPath}");
            exit();
        }

        // Generate CSRF token nếu cần (chỉ cho admin)
        if (isset($securityOptions['csrf']) && $securityOptions['csrf']) {
            SecurityConfig::generateCSRFToken();
        }

        include "Views/auth/{$loginPath}.php";
    }

    // ===== User Context Resolution Helpers =====
    private function resolveUserBySessionRole($uid, $sessionRole): ?array
    {
        try {
            if ($sessionRole === 'admin') {
                $admin = new Admin();
                $a = $admin->getById($uid);
                if ($a) {
                    return ['role' => 'admin', 'name' => $a['ten'] ?? '', 'email' => $a['email'] ?? ''];
                }
            } elseif ($sessionRole === 'letan') {
                require_once 'config/database.php';
                $db = new Database();
                $conn = $db->getConnection();
                $stmt = $conn->prepare('SELECT * FROM le_tan WHERE id = :id');
                $stmt->execute([':id' => $uid]);
                $r = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
                if ($r) {
                    return ['role' => 'letan', 'name' => $r['ten'] ?? '', 'email' => $r['email'] ?? ''];
                }
            } elseif (in_array($sessionRole, ['doctor', 'xray_doctor', 'sieuam_doctor', 'xetnghiem_doctor'], true)) {
                $doc = new Doctor();
                $d = $doc->getById($uid);
                if ($d) {
                    return [
                        'role' => $sessionRole,
                        'name' => $d['ten'] ?? '',
                        'email' => $d['email'] ?? '',
                        'chuyen_khoa_id' => isset($d['chuyen_khoa_id']) ? (int)$d['chuyen_khoa_id'] : null
                    ];
                }
            } elseif ($sessionRole === 'patient') {
                $pat = new Patient();
                if (method_exists($pat, 'getById')) {
                    $p = $pat->getById($uid);
                    if ($p) {
                        return ['role' => 'patient', 'name' => $p['ten'] ?? '', 'email' => $p['email'] ?? ''];
                    }
                }
            }
        } catch (Exception $e) {
        }
        return null;
    }

    private function resolveUserByFallback($uid): ?array
    {
        // Try Doctor first (to avoid cross-table ID collision)
        try {
            $doc = new Doctor();
            $d = $doc->getById($uid);
            if ($d) {
                $spec = isset($d['chuyen_khoa_id']) ? (int)$d['chuyen_khoa_id'] : 0;
                $role = ($spec === 16) ? 'xray_doctor' : (($spec === 18) ? 'sieuam_doctor' : 'doctor');
                return [
                    'role' => $role,
                    'name' => $d['ten'] ?? '',
                    'email' => $d['email'] ?? '',
                    'chuyen_khoa_id' => $spec
                ];
            }
        } catch (Exception $e) {
        }

        // Try Reception
        try {
            require_once 'config/database.php';
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare('SELECT * FROM le_tan WHERE id = :id');
            $stmt->execute([':id' => $uid]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            if ($r) {
                return ['role' => 'letan', 'name' => $r['ten'] ?? '', 'email' => $r['email'] ?? ''];
            }
        } catch (Exception $e) {
        }

        // Try Patient
        try {
            $pat = new Patient();
            if (method_exists($pat, 'getById')) {
                $p = $pat->getById($uid);
                if ($p) {
                    return ['role' => 'patient', 'name' => $p['ten'] ?? '', 'email' => $p['email'] ?? ''];
                }
            }
        } catch (Exception $e) {
        }

        // Try Admin last
        try {
            $admin = new Admin();
            $a = $admin->getById($uid);
            if ($a) {
                return ['role' => 'admin', 'name' => $a['ten'] ?? '', 'email' => $a['email'] ?? ''];
            }
        } catch (Exception $e) {
        }

        return null;
    }

    // Resolve current user context (role/name/email) from DB for this request
    public function resolveCurrentUserContext(): array
    {
        static $cached;
        if ($cached !== null) return $cached;

        $cached = ['id' => null, 'role' => '', 'name' => '', 'email' => '', 'chuyen_khoa_id' => null];
        $uid = $_SESSION['user_id'] ?? null;
        if (!$uid) return $cached;

        $cached['id'] = (int)$uid;
        $sessionRole = $_SESSION['user_role'] ?? '';

        // If session role is explicitly set by login, honor it to avoid cross-table ID collisions
        if ($sessionRole !== '') {
            $result = $this->resolveUserBySessionRole($uid, $sessionRole);
            if ($result) {
                $cached = array_merge($cached, $result);
                return $cached;
            }
            // If session role set but record not found, continue with fallback detection below
        }

        // Fallback detection if session role is not set or record not found for session role
        $result = $this->resolveUserByFallback($uid);
        if ($result) {
            $cached = array_merge($cached, $result);
        }

        return $cached;
    }

    private function userHasRole(string $expected): bool
    {
        $uid = $_SESSION['user_id'] ?? null;
        if (!$uid) return false;
        if ($expected === '') return true;
        // Prefer session role set by the login route
        $sessionRole = $_SESSION['user_role'] ?? '';
        if ($sessionRole !== '') {
            if ($expected === 'doctor') {
                return in_array($sessionRole, ['doctor', 'xray_doctor', 'sieuam_doctor', 'xetnghiem_doctor'], true);
            }
            return $sessionRole === $expected;
        }
        // Fallback: resolve from DB when session role is missing
        $ctx = $this->resolveCurrentUserContext();
        if ($expected === 'doctor') {
            return in_array($ctx['role'], ['doctor', 'xray_doctor', 'sieuam_doctor', 'xetnghiem_doctor'], true);
        }
        return $ctx['role'] === $expected;
    }

    private function redirectIfLoggedInToDashboard()
    {
        if (!$this->isLoggedIn()) return false;
        // Prefer session role decided by login page
        $role = $_SESSION['user_role'] ?? '';
        if ($role === '') {
            $ctx = $this->resolveCurrentUserContext();
            $role = $ctx['role'] ?? '';
        }
        switch ($role) {
            case 'admin':
                header("Location: ./admin_dashboard");
                break;
            case 'doctor':
                header("Location: ./doctor_dashboard");
                break;
            case 'xray_doctor':
                header("Location: ./xray_dashboard");
                break;
            case 'sieuam_doctor':
                header("Location: ./sieuam_dashboard");
                break;
            case 'xetnghiem_doctor':
                header("Location: ./xetnghiem_dashboard");
                break;
            case 'patient':
                header("Location: ./home");
                break;
            case 'letan':
                header("Location: ./reception_dashboard");
                break;
            default:
                header("Location: ./");
        }
        exit();
    }

    public function loginAdmin()
    {
        $securityOptions = [
            'csrf' => true,
            'rate_limit' => true,
            'sanitize' => true
        ];
        $this->handleLogin('admin', 'Admin', 'admin_dashboard', 'login_admin', null, self::ERROR_MESSAGES['invalid_admin_credentials'], $securityOptions);
    }

    public function loginDoctor()
    {
        $securityOptions = ['csrf' => true];
        $this->handleLogin('doctor', 'Doctor', 'doctor_dashboard', 'login_doctor', null, self::ERROR_MESSAGES['invalid_admin_credentials'], $securityOptions);
    }


    public function loginReception()
    {
        require_once 'Models/Reception.php';
        $securityOptions = ['csrf' => true];
        $this->handleLogin('letan', 'Reception', 'reception_dashboard', 'login_reception', null, null, $securityOptions);
    }
    public function loginXrayDoctor()
    {
        if ($this->isLoggedIn()) {
            $this->redirectIfLoggedInToDashboard();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!SecurityConfig::validateCSRFToken($csrfToken)) {
                $_SESSION['error'] = "Token bảo mật không hợp lệ!";
                header("Location: ./login_xquang");
                exit();
            }

            $phone = $_POST['phone'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($phone) || empty($password)) {
                $_SESSION['error'] = self::ERROR_MESSAGES['empty_fields'];
                header("Location: ./login_xquang");
                exit();
            }

            $normalized = $this->normalizePhoneLocal($phone);
            $doctor = new Doctor();
            $user = $doctor->loginWithSpecialization($normalized, $password, 16);

            if ($user) {
                $this->setUserSessionSafe($user, 'xray_doctor');
                header("Location: ./xray_dashboard");
                exit();
            }

            $_SESSION['error'] = self::ERROR_MESSAGES['invalid_credentials'];
            header("Location: ./login_xquang");
            exit();
        }

        SecurityConfig::generateCSRFToken();
        include 'Views/auth/login_xquang.php';
    }


    public function loginSieuam()
    {
        if ($this->isLoggedIn()) {
            $this->redirectIfLoggedInToDashboard();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!SecurityConfig::validateCSRFToken($csrfToken)) {
                $_SESSION['error'] = "Token bảo mật không hợp lệ!";
                header("Location: ./login_sieuam");
                exit();
            }

            $phone = $_POST['phone'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($phone) || empty($password)) {
                $_SESSION['error'] = self::ERROR_MESSAGES['empty_fields'];
                header("Location: ./login_sieuam");
                exit();
            }

            $normalized = $this->normalizePhoneLocal($phone);
            $doctor = new Doctor();
            $user = $doctor->loginWithSpecialization($normalized, $password, 18);

            if ($user) {
                $this->setUserSessionSafe($user, 'sieuam_doctor');
                header("Location: ./sieuam_dashboard");
                exit();
            }

            $_SESSION['error'] = self::ERROR_MESSAGES['invalid_credentials'];
            header("Location: ./login_sieuam");
            exit();
        }

        SecurityConfig::generateCSRFToken();
        include 'Views/auth/login_sieuam.php';
    }

    public function loginXetnghiem()
    {
        if ($this->isLoggedIn()) {
            $role = $_SESSION['user_role'];
            if ($role === 'xetnghiem_doctor') {
                header("Location: ./xetnghiem_dashboard");
                exit();
            }
            switch ($role) {
                case 'doctor':
                    header("Location: ./doctor_dashboard");
                    break;
                case 'sieuam_doctor':
                    header("Location: ./sieuam_dashboard");
                    break;
                case 'xray_doctor':
                    header("Location: ./xray_dashboard");
                    break;
                case 'admin':
                    header("Location: ./admin_dashboard");
                    break;
                case 'patient':
                    header("Location: ./patient_dashboard");
                    break;
                default:
                    header("Location: ./home");
            }
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sdt = $_POST['phone'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($sdt) || empty($password)) {
                $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin!";
                header("Location: ./login_xetnghiem");
                exit();
            }

            try {
                $database = new Database();
                $pdo = $database->getConnection();

                // Chuẩn hóa đối sánh số điện thoại (hỗ trợ 0/84)
                $raw = trim($sdt);
                $p1 = $raw;
                $p2 = $raw;
                if (str_starts_with($raw, '84')) {
                    $p2 = '0' . substr($raw, 2);
                } elseif (str_starts_with($raw, '0')) {
                    $p2 = '84' . substr($raw, 1);
                }

                // Tìm bác sĩ với chuyen_khoa_id = 17 (Xét nghiệm) theo số điện thoại
                $stmt = $pdo->prepare("
                    SELECT b.*, ck.ten as chuyen_khoa_ten 
                    FROM bac_si b 
                    JOIN chuyen_khoa ck ON b.chuyen_khoa_id = ck.id 
                    WHERE (b.so_dien_thoai = :p1 OR b.so_dien_thoai = :p2) AND b.chuyen_khoa_id = 17
                ");
                $stmt->bindParam(':p1', $p1);
                $stmt->bindParam(':p2', $p2);
                $stmt->execute();
                $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($doctor && password_verify($password, $doctor['mat_khau'])) {
                    // Regenerate session ID để tránh session fixation
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $doctor['id'];
                    $_SESSION['user_name'] = $doctor['ten'];
                    $_SESSION['user_email'] = $doctor['email'];
                    $_SESSION['user_role'] = 'xetnghiem_doctor';
                    $_SESSION['chuyen_khoa_id'] = $doctor['chuyen_khoa_id'];
                    $_SESSION['chuyen_khoa_ten'] = $doctor['chuyen_khoa_ten'];
                    $_SESSION['last_activity'] = time();

                    header("Location: ./xetnghiem_dashboard");
                    exit();
                }

                $_SESSION['error'] = "Số điện thoại hoặc mật khẩu không đúng!";
                header("Location: ./login_xetnghiem");
                exit();
            } catch (Exception $e) {
                $_SESSION['error'] = "Lỗi hệ thống!";
                header("Location: ./login_xetnghiem");
                exit();
            }
        }

        include 'Views/auth/login_xetnghiem.php';
    }

    public function loginPatient()
    {
        $securityOptions = ['csrf' => true];
        $this->handleLogin('patient', 'Patient', 'home', 'login', null, null, $securityOptions);
    }


    public function register()
    {
        // Kiểm tra nếu user đã đăng nhập thì redirect về dashboard tương ứng
        if ($this->isLoggedIn()) {
            $role = $_SESSION['user_role'];
            switch ($role) {
                case 'admin':
                    header("Location: ./admin_dashboard");
                    exit();
                case 'doctor':
                    header("Location: ./doctor_dashboard");
                    exit();
                case 'patient':
                    header("Location: ./home");
                    exit();
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['ten'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['mat_khau'] ?? '';
            $confirm_password = $_POST['xac_nhan_mat_khau'] ?? '';
            $role = $_POST['role'] ?? '';
            $phone = $_POST['so_dien_thoai'] ?? '';
            $otp_code = $_POST['otp_code'] ?? '';

            if (empty($name) || empty($email) || empty($password) || empty($role) || empty($phone)) {
                $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
                header("Location: ./register");
                exit();
            }

            if ($password !== $confirm_password) {
                $_SESSION['error'] = "Mật khẩu xác nhận không khớp!";
                header("Location: ./register");
                exit();
            }

            // Validate độ mạnh mật khẩu
            if (strlen($password) < 6) {
                $_SESSION['error'] = "Mật khẩu phải có ít nhất 6 ký tự!";
                header("Location: ./register");
                exit();
            }

            if (strlen($password) > 50) {
                $_SESSION['error'] = "Mật khẩu không được quá 50 ký tự!";
                header("Location: ./register");
                exit();
            }

            // Kiểm tra ký tự đặc biệt
            if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
                $_SESSION['error'] = "Mật khẩu phải chứa ít nhất một ký tự đặc biệt (!@#$%^&*)!";
                header("Location: ./register");
                exit();
            }

            // Xác thực OTP
            if (empty($otp_code)) {
                $_SESSION['error'] = "Vui lòng nhập mã OTP để xác thực số điện thoại!";
                header("Location: ./register");
                exit();
            }

            require_once 'Controllers/SMSController.php';
            $smsController = new SMSController();

            // Chuẩn hóa số điện thoại trước khi verify
            $normalized_phone = $smsController->normalizePhoneNumber($phone);
            $otpResult = $smsController->verifyOTP($normalized_phone, $otp_code);

            if (!$otpResult['success']) {
                $_SESSION['error'] = $otpResult['message'];
                header("Location: ./register");
                exit();
            }

            $success = false;

            // Chuẩn hóa số điện thoại để lưu vào database
            $normalized_phone_for_db = $smsController->normalizePhoneNumber($phone);

            // Validation ở Controller trước khi gọi Model
            $patient = new Patient();

            // Kiểm tra email đã tồn tại chưa
            if ($patient->emailExists($email)) {
                $_SESSION['error'] = "Email đã được sử dụng. Vui lòng chọn email khác.";
                $_SESSION['form_data'] = $_POST; // Lưu lại dữ liệu form
                header("Location: ./register");
                exit();
            }

            // Kiểm tra số điện thoại đã tồn tại chưa
            if ($patient->phoneExists($normalized_phone_for_db)) {
                $_SESSION['error'] = "Số điện thoại đã được sử dụng. Vui lòng chọn số khác.";
                $_SESSION['form_data'] = $_POST; // Lưu lại dữ liệu form
                header("Location: ./register");
                exit();
            }

            try {
                switch ($role) {
                    case 'admin':
                        $admin = new Admin();
                        $data = [
                            'ten' => $name,
                            'email' => $email,
                            'mat_khau' => $password,
                            'so_dien_thoai' => $normalized_phone_for_db,
                            'phone_verified' => 1
                        ];
                        $success = $admin->create($data);
                        break;

                    case 'doctor':
                        $doctorModel = new Doctor();
                        $data = [
                            'ten' => $name,
                            'email' => $email,
                            'mat_khau' => $password,
                            'so_dien_thoai' => $normalized_phone_for_db,
                            'phone_verified' => 1,
                            'chuyen_khoa' => $_POST['chuyen_khoa'] ?? '',
                            'so_giay_phep' => $_POST['so_giay_phep'] ?? '',
                            'so_nam_kinh_nghiem' => $_POST['so_nam_kinh_nghiem'] ?? 0
                        ];
                        $success = $doctorModel->create($data);
                        break;

                    case 'patient':
                        $cccd = trim($_POST['cccd'] ?? '');

                        // Validate CCCD nếu được nhập
                        if (!empty($cccd)) {
                            require_once 'Services/CCCDService.php';
                            require_once 'config/database.php';
                            $database = new Database();
                            $db = $database->getConnection();
                            $cccdService = new CCCDService($db);

                            $verifyResult = $cccdService->verifyCCCD($cccd, $name, $_POST['ngay_sinh'] ?? null);

                            if (!$verifyResult['success']) {
                                $_SESSION['error'] = $verifyResult['message'];
                                $_SESSION['form_data'] = $_POST;
                                header("Location: ./register");
                                exit();
                            }
                        }

                        $data = [
                            'ten' => $name,
                            'email' => $email,
                            'mat_khau' => $password,
                            'so_dien_thoai' => $normalized_phone_for_db,
                            'phone_verified' => 1,
                            'ngay_sinh' => $_POST['ngay_sinh'] ?? '',
                            'gioi_tinh' => $_POST['gioi_tinh'] ?? '',
                            'dia_chi' => $_POST['dia_chi'] ?? '',
                            'cccd' => $cccd
                        ];
                        $success = $patient->create($data);
                        break;
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Có lỗi xảy ra khi đăng ký: " . $e->getMessage();
                header("Location: ./register");
                exit();
            }

            if ($success) {
                // Xóa OTP sau khi đăng ký thành công
                $smsController->clearOTPAfterRegistration();
                // Xóa form_data session
                unset($_SESSION['form_data']);
                $_SESSION['success'] = "Đăng ký thành công! Vui lòng đăng nhập.";
                header("Location: ./login");
                exit();
            } else {
                $_SESSION['error'] = "Có lỗi xảy ra khi đăng ký!";
                header("Location: ./register");
                exit();
            }
        }

        // Hiển thị form register
        include 'Views/auth/register.php';
    }

    public function logout()
    {
        // Clear only our keys to avoid nuking unrelated PHP session data
        unset($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['last_activity'], $_SESSION['user_name'], $_SESSION['user_email']);
        session_regenerate_id(true);
        header("Location: ./");
        exit();
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public function requireAuth($role = null)
    {
        if (!$this->isLoggedIn()) {
            header("Location: ./login");
            exit();
        }

        // Kiểm tra session timeout (30 phút)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            session_destroy();
            header("Location: ./login");
            exit();
        }

        // Cập nhật last activity
        $_SESSION['last_activity'] = time();

        if ($role && !$this->userHasRole($role)) {
            header("Location: ./");
            exit();
        }
    }

    public function changePassword()
    {
        // Kiểm tra đăng nhập
        if (!$this->isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập trước']);
            exit();
        }

        // Chỉ xử lý POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        // Nhận dữ liệu JSON
        $input = json_decode(file_get_contents('php://input'), true);

        $currentPassword = $input['currentPassword'] ?? '';
        $newPassword = $input['newPassword'] ?? '';
        $confirmPassword = $input['confirmPassword'] ?? '';
        $role = $_SESSION['user_role'] ?? '';
        if ($role === '') {
            $ctx = $this->resolveCurrentUserContext();
            $role = $ctx['role'] ?? '';
        }

        // Validate dữ liệu
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
            exit();
        }

        // Kiểm tra mật khẩu hiện tại và cập nhật
        $userId = $_SESSION['user_id'];
        $success = false;
        $errorMessage = '';

        try {
            switch ($role) {
                case 'admin':
                    $admin = new Admin();
                    $user = $admin->getById($userId);
                    if ($user && password_verify($currentPassword, $user['mat_khau'])) {
                        $success = $admin->updatePassword($userId, $newPassword);
                    } else {
                        $errorMessage = 'Mật khẩu hiện tại không đúng';
                    }
                    break;

                case 'doctor':
                    $doctorModel = new Doctor();
                    $user = $doctorModel->getById($userId);
                    if ($user && password_verify($currentPassword, $user['mat_khau'])) {
                        $success = $doctorModel->updatePassword($userId, $newPassword);
                    } else {
                        $errorMessage = 'Mật khẩu hiện tại không đúng';
                    }
                    break;

                case 'patient':
                    $patient = new Patient();
                    $user = $patient->getById($userId);
                    if ($user && password_verify($currentPassword, $user['mat_khau'])) {
                        $success = $patient->updatePassword($userId, $newPassword);
                    } else {
                        $errorMessage = 'Mật khẩu hiện tại không đúng';
                    }
                    break;

                default:
                    $errorMessage = 'Role không hợp lệ';
                    break;
            }
        } catch (Exception $e) {
            $errorMessage = 'Có lỗi xảy ra: ' . $e->getMessage();
        }

        // Trả về kết quả
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Thay đổi mật khẩu thành công']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $errorMessage ?: 'Có lỗi xảy ra khi thay đổi mật khẩu']);
        }
        exit();
    }

    public function resetPassword()
    {
        // Chỉ xử lý POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        // Nhận dữ liệu JSON
        $input = json_decode(file_get_contents('php://input'), true);

        $phoneNumber = $input['phone_number'] ?? '';
        $newPassword = $input['new_password'] ?? '';

        // Validate dữ liệu
        if (empty($phoneNumber) || empty($newPassword)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
            exit();
        }

        // Validate mật khẩu mới
        if (strlen($newPassword) < 6) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự']);
            exit();
        }

        if (strlen($newPassword) > 50) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Mật khẩu không được quá 50 ký tự']);
            exit();
        }

        // Kiểm tra có ít nhất 1 ký tự đặc biệt
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $newPassword)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Mật khẩu phải chứa ít nhất 1 ký tự đặc biệt']);
            exit();
        }

        // Normalize phone number
        $normalizedPhone = $phoneNumber;
        if (strlen($normalizedPhone) === 11 && $normalizedPhone[0] === '0') {
            $normalizedPhone = substr($normalizedPhone, 1);
        }
        if (!str_starts_with($normalizedPhone, '84')) {
            $normalizedPhone = '84' . $normalizedPhone;
        }

        // Tìm user theo số điện thoại
        $patient = new Patient();
        $user = $patient->getByPhone($normalizedPhone);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy tài khoản với số điện thoại này']);
            exit();
        }

        // Debug: Log user found
        error_log("User found for reset password: " . json_encode($user));

        // Cập nhật mật khẩu mới
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            error_log("Attempting to update password for user ID: " . $user['id']);
            $success = $patient->updatePasswordById($user['id'], $hashedPassword);
            error_log("Password update result: " . ($success ? 'true' : 'false'));

            if ($success) {
                // Clear OTP session if exists
                if (isset($_SESSION['otp_data'])) {
                    unset($_SESSION['otp_data']);
                }
                echo json_encode(['success' => true, 'message' => 'Đặt lại mật khẩu thành công.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật mật khẩu']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
        exit();
    }
}