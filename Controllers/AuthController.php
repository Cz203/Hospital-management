<?php

require_once 'Models/Admin.php';
require_once 'Models/Doctor.php';
require_once 'Models/Patient.php';
require_once 'config/security.php';

class AuthController
{

    public function loginAdmin()
    {
        if ($this->isLoggedIn()) {
            $role = $_SESSION['user_role'];
            if ($role === 'admin') {
                header("Location: ./admin_dashboard");
                exit();
            }
            // Nếu đã đăng nhập role khác thì đưa về dashboard tương ứng
            switch ($role) {
                case 'doctor':
                    header("Location: ./doctor_dashboard");
                    exit();
                case 'patient':
                    header("Location: ./patient_dashboard");
                    exit();
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Kiểm tra CSRF token
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!SecurityConfig::validateCSRFToken($csrfToken)) {
                $_SESSION['error'] = "Token bảo mật không hợp lệ!";
                header("Location: ./login_admin");
                exit();
            }

            // Rate limiting
            if (!SecurityConfig::checkRateLimit('admin_login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'))) {
                $_SESSION['error'] = "Quá nhiều lần thử. Vui lòng thử lại sau 15 phút.";
                header("Location: ./login_admin");
                exit();
            }

            $email = SecurityConfig::sanitizeInput($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
                header("Location: ./login_admin");
                exit();
            }

            if (!SecurityConfig::validateEmail($email)) {
                $_SESSION['error'] = "Email không hợp lệ!";
                header("Location: ./login_admin");
                exit();
            }

            $admin = new Admin();
            $user = $admin->login($email, $password);
            if ($user) {
                // Regenerate session ID để tránh session fixation
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['ten'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = 'admin';
                $_SESSION['last_activity'] = time();

                header("Location: ./admin_dashboard");
                exit();
            }

            $_SESSION['error'] = "Email hoặc mật khẩu không đúng!";
            header("Location: ./login_admin");
            exit();
        }

        SecurityConfig::generateCSRFToken();
        include 'Views/auth/login_admin.php';
    }

    public function loginDoctor()
    {
        if ($this->isLoggedIn()) {
            $role = $_SESSION['user_role'];
            if ($role === 'doctor') {
                header("Location: ./doctor_dashboard");
                exit();
            }
            switch ($role) {
                case 'admin':
                    header("Location: ./admin_dashboard");
                    exit();
                case 'patient':
                    header("Location: ./patient_dashboard");
                    exit();
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
                header("Location: ./login_doctor");
                exit();
            }

            $doctorModel = new Doctor();
            $user = $doctorModel->login($email, $password);
            if ($user) {
                // Regenerate session ID để tránh session fixation
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['ten'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = 'doctor';
                $_SESSION['specialization'] = $user['chuyen_khoa'];
                $_SESSION['last_activity'] = time();

                header("Location: ./doctor_dashboard");
                exit();
            }

            $_SESSION['error'] = "Email hoặc mật khẩu không đúng!";
            header("Location: ./login_doctor");
            exit();
        }

        include 'Views/auth/login_doctor.php';
    }

    public function loginXrayDoctor()
    {
        if ($this->isLoggedIn()) {
            $role = $_SESSION['user_role'];
            if ($role === 'xray_doctor') {
                header("Location: ./xray_dashboard");
                exit();
            }
            switch ($role) {
                case 'admin':
                    header("Location: ./admin_dashboard");
                    exit();
                case 'doctor':
                    header("Location: ./doctor_dashboard");
                    exit();
                case 'patient':
                    header("Location: ./patient_dashboard");
                    exit();
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
                header("Location: ./login_xquang");
                exit();
            }

            $doctorModel = new Doctor();
            $user = $doctorModel->login($email, $password);
            if ($user) {
                // Chỉ cho phép bác sĩ có chuyên khoa id = 16 (Chẩn đoán hình ảnh)
                $specId = isset($user['chuyen_khoa_id']) ? (int)$user['chuyen_khoa_id'] : 0;
                if ($specId !== 16) {
                    $_SESSION['error'] = "Tài khoản không thuộc chuyên khoa Chẩn đoán hình ảnh (ID=16).";
                    header("Location: ./login_xquang");
                    exit();
                }

                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['ten'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = 'xray_doctor';
                $_SESSION['specialization_id'] = $specId;
                $_SESSION['last_activity'] = time();

                header("Location: ./xray_dashboard");
                exit();
            }

            $_SESSION['error'] = "Email hoặc mật khẩu không đúng!";
            header("Location: ./login_xquang");
            exit();
        }

        include 'Views/auth/login_xquang.php';
    }

    public function loginPatient()
    {
        if ($this->isLoggedIn()) {
            $role = $_SESSION['user_role'];
            if ($role === 'patient') {
                header("Location: ./patient_dashboard");
                exit();
            }
            switch ($role) {
                case 'admin':
                    header("Location: ./admin_dashboard");
                    exit();
                case 'doctor':
                    header("Location: ./doctor_dashboard");
                    exit();
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
                header("Location: ./login");
                exit();
            }

            $patient = new Patient();
            $user = $patient->login($email, $password);
            if ($user) {
                // Regenerate session ID để tránh session fixation
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['ten'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = 'patient';
                $_SESSION['last_activity'] = time();

                header("Location: patient_dashboard");
                exit();
            }

            $_SESSION['error'] = "Email hoặc mật khẩu không đúng!";
            header("Location: login");
            exit();
        }

        include 'Views/auth/login.php';
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
                    header("Location: ./patient_dashboard");
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
                        $data = [
                            'ten' => $name,
                            'email' => $email,
                            'mat_khau' => $password,
                            'so_dien_thoai' => $normalized_phone_for_db,
                            'phone_verified' => 1,
                            'ngay_sinh' => $_POST['ngay_sinh'] ?? '',
                            'gioi_tinh' => $_POST['gioi_tinh'] ?? '',
                            'dia_chi' => $_POST['dia_chi'] ?? '',
                            'nhom_mau' => $_POST['nhom_mau'] ?? ''
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
        session_destroy();
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

        if ($role && $_SESSION['user_role'] !== $role) {
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