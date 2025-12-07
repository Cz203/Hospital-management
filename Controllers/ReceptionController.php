<?php

require_once 'Controllers/AuthController.php';
require_once 'Models/Reception.php';
require_once 'Models/Patient.php';
require_once 'Models/Doctor.php';
require_once 'Models/BienLai.php';
require_once 'Services/VNPayService.php';
require_once 'config/security.php';

class ReceptionController
{
    private $auth;
    private $receptionModel;
    private $patientModel;
    private $doctorModel;
    private $bienLaiModel;
    private $vnpayService;

    public function __construct()
    {
        $this->auth = new AuthController();
        $this->receptionModel = new Reception();
        $this->patientModel = new Patient();
        $this->doctorModel = new Doctor();
        $this->bienLaiModel = new BienLai();
        $this->vnpayService = new VNPayService();
    }

    /**
     * Trang chính của lễ tân (wrapper layout + form tra cứu)
     */
    public function dashboard()
    {
        $this->auth->requireAuth('letan');

        if (class_exists('SecurityConfig')) {
            SecurityConfig::generateCSRFToken();
        }

        $page_title = 'Dashboard Lễ tân';

        ob_start();
        include 'Views/reception/dashboard.php';
        $content = ob_get_clean();

        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, $page_title);
    }

    /**
     * Trang xem lịch làm việc bác sĩ (Lễ tân)
     */
    public function doctorSchedules()
    {
        $this->auth->requireAuth('letan');

        // Danh sách bác sĩ cho dropdown
        $doctors = $this->doctorModel->getAll();
        $page_title = 'Lịch làm việc bác sĩ';

        ob_start();
        include 'Views/reception/doctor_schedules.php';
        $content = ob_get_clean();

        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, $page_title);
    }

    /**
     * Lịch làm việc của Lễ tân (tự đăng ký ca trực, lịch lặp theo tuần)
     */
    public function scheduleManagement()
    {
        $this->auth->requireAuth('letan');

        // Lấy thông tin lễ tân hiện tại
        $receptionId = $_SESSION['user_id'];
        $receptionName = $_SESSION['user_name'] ?? 'Lễ tân';

        // Lấy tất cả lịch làm việc của lễ tân từ bảng lich_lam_viec_le_tan
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();

        $sql = "SELECT * FROM lich_lam_viec_le_tan 
                WHERE letan_id = :id
                ORDER BY 
                    CASE thu_trong_tuan 
                        WHEN 'Thứ 2' THEN 1
                        WHEN 'Thứ 3' THEN 2
                        WHEN 'Thứ 4' THEN 3
                        WHEN 'Thứ 5' THEN 4
                        WHEN 'Thứ 6' THEN 5
                        WHEN 'Thứ 7' THEN 6
                        WHEN 'Chủ nhật' THEN 7
                    END, gio_bat_dau";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $receptionId, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Gom lịch theo ngày & loại ca để render dạng bảng
        $groupedSchedules = [];
        foreach ($rows as $row) {
            $day = $row['thu_trong_tuan'];
            $shift = $row['loai_ca'];
            if (!isset($groupedSchedules[$day])) {
                $groupedSchedules[$day] = [];
            }
            if (!isset($groupedSchedules[$day][$shift])) {
                $groupedSchedules[$day][$shift] = [];
            }
            $groupedSchedules[$day][$shift][] = $row;
        }

        $page_title = 'Lịch làm việc lễ tân';

        ob_start();
        include 'Views/reception/schedule_management.php';
        $content = ob_get_clean();

        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, $page_title);
    }

    /**
     * Thêm ca trực lễ tân (lịch lặp theo thứ)
     */
    public function addSchedule()
    {
        $this->auth->requireAuth('letan');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ./reception_schedule_management');
            exit();
        }

        $receptionId = $_SESSION['user_id'];
        $thu = $_POST['thu_trong_tuan'] ?? '';
        $loaiCa = $_POST['loai_ca'] ?? '';
        $gioBatDau = $_POST['gio_bat_dau'] ?? '';
        $gioKetThuc = $_POST['gio_ket_thuc'] ?? '';
        $ghiChu = $_POST['ghi_chu'] ?? '';

        if ($thu === '' || $loaiCa === '' || $gioBatDau === '' || $gioKetThuc === '') {
            $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin ca trực.';
            header('Location: ./reception_schedule_management');
            exit();
        }

        // Giống logic bác sĩ: chỉ cho phép đăng ký/hoàn tất lịch cơ bản từ ngày 23 → 25 hằng tháng
        if (!$this->isWithinReceptionRegistrationWindow()) {
            $_SESSION['error'] = 'Chỉ được đăng ký/hoàn tất lịch từ ngày 23 đến 25 hằng tháng!';
            header('Location: ./reception_schedule_management');
            exit();
        }

        // Kiểm tra thời gian hợp lệ: giờ kết thúc phải sau giờ bắt đầu (giống bác sĩ)
        if (strtotime($gioBatDau) >= strtotime($gioKetThuc)) {
            $_SESSION['error'] = 'Giờ kết thúc phải sau giờ bắt đầu!';
            header('Location: ./reception_schedule_management');
            exit();
        }

        try {
            require_once 'config/database.php';
            $database = new Database();
            $db = $database->getConnection();

            // Kiểm tra xung đột ca trực (đơn giản: cùng thứ, trùng giờ)
            $checkSql = "SELECT COUNT(*) FROM lich_lam_viec_le_tan 
                         WHERE letan_id = :id AND thu_trong_tuan = :thu 
                           AND (gio_bat_dau < :gio_ket_thuc AND gio_ket_thuc > :gio_bat_dau)";
            $st = $db->prepare($checkSql);
            $st->bindParam(':id', $receptionId, PDO::PARAM_INT);
            $st->bindParam(':thu', $thu);
            $st->bindParam(':gio_bat_dau', $gioBatDau);
            $st->bindParam(':gio_ket_thuc', $gioKetThuc);
            $st->execute();
            if ((int)$st->fetchColumn() > 0) {
                $_SESSION['error'] = 'Ca trực bị trùng với ca đã đăng ký.';
                header('Location: ./reception_schedule_management');
                exit();
            }

            $insertSql = "INSERT INTO lich_lam_viec_le_tan
                          (letan_id, thu_trong_tuan, gio_bat_dau, gio_ket_thuc, loai_ca, ghi_chu, trang_thai)
                          VALUES (:id, :thu, :gio_bat_dau, :gio_ket_thuc, :loai_ca, :ghi_chu, 'active')";
            $ins = $db->prepare($insertSql);
            $ins->bindParam(':id', $receptionId, PDO::PARAM_INT);
            $ins->bindParam(':thu', $thu);
            $ins->bindParam(':gio_bat_dau', $gioBatDau);
            $ins->bindParam(':gio_ket_thuc', $gioKetThuc);
            $ins->bindParam(':loai_ca', $loaiCa);
            $ins->bindParam(':ghi_chu', $ghiChu);
            $ins->execute();

            $_SESSION['success'] = 'Thêm ca trực thành công.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi khi thêm ca trực: ' . $e->getMessage();
        }

        header('Location: ./reception_schedule_management');
        exit();
    }

    /**
     * Xóa ca trực lễ tân
     */
    public function deleteSchedule()
    {
        $this->auth->requireAuth('letan');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ./reception_schedule_management');
            exit();
        }

        // Giống bác sĩ: chỉ cho phép xóa ca trực vào Thứ 2 (theo giờ Việt Nam)
        if (!$this->isMonday()) {
            $_SESSION['error'] = 'Chỉ được xóa ca trực vào Thứ 2.';
            header('Location: ./reception_schedule_management');
            exit();
        }

        $receptionId = $_SESSION['user_id'];
        $scheduleId = isset($_POST['schedule_id']) ? (int)$_POST['schedule_id'] : 0;

        if ($scheduleId <= 0) {
            $_SESSION['error'] = 'Ca trực không hợp lệ.';
            header('Location: ./reception_schedule_management');
            exit();
        }

        try {
            require_once 'config/database.php';
            $database = new Database();
            $db = $database->getConnection();

            $sql = "DELETE FROM lich_lam_viec_le_tan WHERE id = :sid AND letan_id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':sid', $scheduleId, PDO::PARAM_INT);
            $stmt->bindParam(':id', $receptionId, PDO::PARAM_INT);
            $stmt->execute();

            $_SESSION['success'] = 'Đã xóa ca trực.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi khi xóa ca trực: ' . $e->getMessage();
        }

        header('Location: ./reception_schedule_management');
        exit();
    }

    /**
     * API: Lấy lịch làm việc theo bác sĩ theo NGÀY (áp dụng ngoại lệ theo ngày)
     */
    public function getDoctorSchedules()
    {
        $this->auth->requireAuth('letan');
        header('Content-Type: application/json');

        $doctorId = isset($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : 0;
        $date = $_POST['date'] ?? '';

        if ($doctorId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thiếu hoặc sai bác sĩ']);
            exit();
        }

        try {
            if ($date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                // Lấy lịch theo NGÀY, áp dụng ngoại lệ từ bảng lich_lam_viec_ngoai_le
                $data = $this->doctorModel->getSchedulesByDate($doctorId, $date) ?: [];
            } else {
                // Fallback: nếu không có date hợp lệ, trả cơ bản theo thứ của hôm nay
                $vnDayMap = [
                    'Monday' => 'Thứ 2',
                    'Tuesday' => 'Thứ 3',
                    'Wednesday' => 'Thứ 4',
                    'Thursday' => 'Thứ 5',
                    'Friday' => 'Thứ 6',
                    'Saturday' => 'Thứ 7',
                    'Sunday' => 'Chủ nhật'
                ];
                $today = $vnDayMap[date('l')] ?? 'Thứ 2';
                $data = $this->doctorModel->getSchedulesByDay($doctorId, $today) ?: [];
            }
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * Hoàn thiện hồ sơ bệnh nhân: chỉ được thêm trường còn thiếu, không sửa/xóa
     */
    public function completePatientProfile()
    {
        $this->auth->requireAuth('letan');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        header('Content-Type: application/json');

        // CSRF
        $csrf = $_POST['csrf_token'] ?? '';
        if (class_exists('SecurityConfig') && !SecurityConfig::validateCSRFToken($csrf)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'CSRF token không hợp lệ']);
            exit();
        }

        $patientId = isset($_POST['patient_id']) ? (int)$_POST['patient_id'] : 0;
        if ($patientId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu patient_id']);
            exit();
        }

        // Các trường cho phép sửa/xóa (để trống để xóa)
        $allowed = ['email', 'ngay_sinh', 'gioi_tinh', 'dia_chi', 'cccd', 'bao_hiem_y_te'];
        $payload = [];
        foreach ($allowed as $k) {
            if (isset($_POST[$k])) {
                $payload[$k] = trim((string)$_POST[$k]);
            }
        }
        if (empty($payload)) {
            echo json_encode(['success' => false, 'message' => 'Không có trường nào để cập nhật']);
            exit();
        }

        try {
            // Cho phép sửa/xóa (nếu giá trị rỗng => xóa = NULL)
            if (method_exists($this->patientModel, 'updateFieldsByReception')) {
                $ok = $this->patientModel->updateFieldsByReception($patientId, $payload);
            } else if (method_exists($this->patientModel, 'completeMissingFields')) {
                // Back-compat (không xoá được): dùng completeMissingFields nếu chưa có method mới
                $ok = $this->patientModel->completeMissingFields($patientId, $payload);
            } else {
                $ok = false;
            }
            if ($ok) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể cập nhật dữ liệu']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * Form thêm bệnh nhân (Lễ tân)
     */
    public function patientCreateForm()
    {
        $this->auth->requireAuth('letan');

        // CSRF token
        if (class_exists('SecurityConfig')) {
            SecurityConfig::generateCSRFToken();
        }

        $page_title = 'Thêm bệnh nhân';
        ob_start();
        include 'Views/reception/patient_create.php';
        $content = ob_get_clean();
        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, $page_title);
    }

    /**
     * Lưu bệnh nhân mới vào bảng benh_nhan
     */
    public function patientStore()
    {
        $this->auth->requireAuth('letan');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ./reception_patient_create');
            exit();
        }

        if (class_exists('SecurityConfig')) {
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!SecurityConfig::validateCSRFToken($csrfToken)) {
                $_SESSION['error'] = 'Token bảo mật không hợp lệ!';
                header('Location: ./reception_patient_create');
                exit();
            }
        }

        $name = trim($_POST['ten'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['so_dien_thoai'] ?? '');
        $dob = trim($_POST['ngay_sinh'] ?? '');
        $genderRaw = trim($_POST['gioi_tinh'] ?? '');
        $address = trim($_POST['dia_chi'] ?? '');
        $cccd = trim($_POST['cccd'] ?? '');
        $baoHiemId = isset($_POST['bao_hiem_y_te_id']) ? (int)$_POST['bao_hiem_y_te_id'] : null;
        $baoHiemCode = trim($_POST['bao_hiem_y_te'] ?? '');

        // Debug: Log tất cả giá trị nhận được từ form
        error_log('ReceptionController::patientStore - Gender RAW from POST: ' . var_export($genderRaw, true));
        error_log('ReceptionController::patientStore - CCCD RAW from POST: ' . var_export($cccd, true));
        error_log('ReceptionController::patientStore - All POST data: ' . json_encode($_POST));

        // Validate và đảm bảo CCCD hợp lệ
        if ($cccd !== '') {
            // Kiểm tra format CCCD (12 số)
            if (!preg_match('/^\d{12}$/', $cccd)) {
                $_SESSION['error'] = 'CCCD phải có đúng 12 số.';
                header('Location: ./reception_patient_create');
                exit();
            }

            // Verify lại CCCD để đảm bảo tính nhất quán
            require_once 'Services/CCCDService.php';
            require_once 'config/database.php';
            $database = new Database();
            $db = $database->getConnection();
            $cccdService = new CCCDService($db);

            $verifyResult = $cccdService->verifyCCCD($cccd, $name, $dob !== '' ? $dob : null);

            // Nếu CCCD đã được đăng ký, không cho phép tạo mới
            if (isset($verifyResult['already_registered']) && $verifyResult['already_registered']) {
                $_SESSION['error'] = 'CCCD này đã được đăng ký trong hệ thống.';
                header('Location: ./reception_patient_create');
                exit();
            }

            // Nếu verify không thành công nhưng có dữ liệu, cảnh báo
            if (!$verifyResult['success'] && isset($verifyResult['cccd_data'])) {
                error_log('ReceptionController::patientStore - CCCD verification failed but has data: ' . json_encode($verifyResult));
            }

            error_log('ReceptionController::patientStore - CCCD verification result: ' . json_encode([
                'cccd_input' => $cccd,
                'verified' => $verifyResult['verified'] ?? false,
                'success' => $verifyResult['success'] ?? false,
                'cccd_from_db' => isset($verifyResult['cccd_data']['cccd']) ? $verifyResult['cccd_data']['cccd'] : null,
                'ten_from_db' => isset($verifyResult['cccd_data']['ten']) ? $verifyResult['cccd_data']['ten'] : null
            ]));

            // QUAN TRỌNG: Đảm bảo số CCCD được lưu đúng với số CCCD đã verify từ database
            // Nếu có dữ liệu từ database và số CCCD khác với input, sử dụng số từ database
            if (isset($verifyResult['cccd_data']['cccd']) && $verifyResult['cccd_data']['cccd'] !== $cccd) {
                error_log('ReceptionController::patientStore - WARNING: CCCD mismatch! Input: ' . $cccd . ', From DB: ' . $verifyResult['cccd_data']['cccd']);
                error_log('ReceptionController::patientStore - Using verified CCCD from database: ' . $verifyResult['cccd_data']['cccd']);
                // Sử dụng số CCCD từ database (đã verify) để đảm bảo tính nhất quán
                $cccd = $verifyResult['cccd_data']['cccd'];
            }
        }

        // Normalize gender value to match ENUM('Nam','Nữ','Khác')
        // Database ENUM chỉ chấp nhận: 'Nam', 'Nữ', 'Khác'
        $gender = null;
        if ($genderRaw !== '') {
            // Chỉ chấp nhận giá trị khớp chính xác với ENUM
            $validGenders = ['Nam', 'Nữ', 'Khác'];
            // So sánh strict để đảm bảo khớp chính xác
            if (in_array($genderRaw, $validGenders, true)) {
                $gender = $genderRaw;
                error_log('ReceptionController::patientStore - Gender matched: ' . $gender);
            } else {
                // Nếu giá trị không khớp, map về giá trị hợp lệ
                $genderLower = mb_strtolower($genderRaw, 'UTF-8');
                if (in_array($genderLower, ['nam', 'male', 'm'])) {
                    $gender = 'Nam';
                } elseif (in_array($genderLower, ['nữ', 'nu', 'n', 'female', 'f'])) {
                    $gender = 'Nữ';
                } elseif (in_array($genderLower, ['khác', 'khac', 'other', 'o'])) {
                    $gender = 'Khác';
                }
                if ($gender) {
                    error_log('ReceptionController::patientStore - Gender mapped: ' . $genderRaw . ' -> ' . $gender);
                } else {
                    error_log('ReceptionController::patientStore - Gender NOT mapped, will be NULL. Raw value: ' . $genderRaw);
                }
                // Nếu không khớp, để null (không gây lỗi SQL)
            }
        } else {
            error_log('ReceptionController::patientStore - Gender RAW is empty, will be NULL');
        }

        // Mật khẩu mặc định cho bệnh nhân do lễ tân tạo
        $defaultPassword = '1111';

        // Basic validation
        if ($name === '' || $email === '' || $phone === '') {
            $_SESSION['error'] = 'Vui lòng nhập đủ Tên, Email, Số điện thoại.';
            header('Location: ./reception_patient_create');
            exit();
        }

        // Normalize phone to 84xxxxxxxxx
        $digits = preg_replace('/\D+/', '', $phone);
        if (strlen($digits) >= 9 && strlen($digits) <= 11) {
            if (str_starts_with($digits, '84')) {
                $phoneNorm = $digits;
            } elseif (str_starts_with($digits, '0')) {
                $phoneNorm = '84' . substr($digits, 1);
            } else {
                $phoneNorm = '84' . ltrim($digits, '0');
            }
        } else {
            $_SESSION['error'] = 'Số điện thoại không hợp lệ.';
            header('Location: ./reception_patient_create');
            exit();
        }

        try {
            // Check duplicates
            if ($this->patientModel->emailExists($email)) {
                $_SESSION['error'] = 'Email đã được sử dụng.';
                header('Location: ./reception_patient_create');
                exit();
            }
            if ($this->patientModel->phoneExists($phoneNorm)) {
                $_SESSION['error'] = 'Số điện thoại đã được sử dụng.';
                header('Location: ./reception_patient_create');
                exit();
            }

            // Insert
            $createData = [
                'ten' => $name,
                'email' => $email,
                'mat_khau' => $defaultPassword,
                'so_dien_thoai' => $phoneNorm,
                'phone_verified' => 1,
                'ngay_sinh' => $dob !== '' ? $dob : null,
                'gioi_tinh' => $gender, // Đã được normalize ở trên, có thể là 'Nam', 'Nữ', 'Khác' hoặc null
                'dia_chi' => $address !== '' ? $address : null,
                'cccd' => $cccd !== '' ? $cccd : null,
            ];

            // Nếu có thông tin BHYT từ CCCD, lưu vào bảng benh_nhan
            if ($baoHiemId && $baoHiemId > 0) {
                $createData['bao_hiem_y_te_id'] = $baoHiemId;
            }
            if ($baoHiemCode !== '') {
                $createData['bao_hiem_y_te'] = $baoHiemCode;
            }

            $ok = $this->patientModel->create($createData);
            if ($ok) {
                $_SESSION['success'] = 'Thêm bệnh nhân thành công!';
                header('Location: ./reception_patient_create');
                exit();
            }
            $_SESSION['error'] = 'Không thể thêm bệnh nhân. Vui lòng thử lại!';
            header('Location: ./reception_patient_create');
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
            header('Location: ./reception_patient_create');
            exit();
        }
    }
    /**
     * Tra cứu bệnh nhân theo số điện thoại (AJAX hoặc POST)
     */
    public function findPatientByPhone()
    {
        $this->auth->requireAuth('letan');
        header('Content-Type: application/json');

        $phone = $_POST['phone'] ?? '';
        $phone = trim($phone);
        if ($phone === '') {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập số điện thoại']);
            exit();
        }

        // Chuẩn hóa số điện thoại: cho phép 0xxxxxxxxx hoặc 84xxxxxxxxx
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '84')) {
            $normalized = $digits;
        } elseif (str_starts_with($digits, '0')) {
            $normalized = '84' . substr($digits, 1);
        } else {
            // nếu đã là 9-11 số, thêm 84 phía trước
            $normalized = (strlen($digits) >= 9 && strlen($digits) <= 11) ? ('84' . ltrim($digits, '0')) : $digits;
        }

        $patient = $this->patientModel->getByPhone($normalized);
        if ($patient) {
            echo json_encode(['success' => true, 'data' => $patient]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy bệnh nhân với số điện thoại này']);
        }
        exit();
    }

    /**
     * ISSUE: Phát phiếu bốc số walk-in và tạo lich_hen tương ứng hôm nay
     */
    public function issueQueueTicket()
    {
        $this->auth->requireAuth('letan');
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        $patientId = isset($_POST['benh_nhan_id']) ? (int)$_POST['benh_nhan_id'] : 0;
        $specialtyId = isset($_POST['chuyen_khoa_id']) ? (int)$_POST['chuyen_khoa_id'] : 0;
        $priority = isset($_POST['uu_tien']) ? (int)$_POST['uu_tien'] : 0;
        $counter = isset($_POST['quay']) ? trim((string)$_POST['quay']) : null;
        if ($patientId <= 0 || $specialtyId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu benh_nhan_id hoặc chuyen_khoa_id']);
            exit();
        }

        // Chọn bác sĩ đang on-duty ít bận nhất hôm nay
        $today = date('Y-m-d');
        $availableDoctors = $this->getOnDutyDoctorsBySpecialty($specialtyId, $today);
        if (empty($availableDoctors)) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Không có bác sĩ đang trực trong chuyên khoa này hôm nay']);
            exit();
        }
        $doctorId = $this->pickLeastLoadedDoctor($availableDoctors, $today);

        // Chọn slot 20' gần nhất còn trống sau thời điểm hiện tại
        require_once 'Models/Appointment.php';
        $apptModel = new Appointment();
        $slots = $apptModel->getAvailableTimeSlots($doctorId, $today, false);
        $now = date('H:i');
        $chosen = null;
        foreach ($slots as $s) {
            if (isset($s['time']) && $s['time'] > $now) {
                $chosen = $s;
                break;
            }
        }
        if (!$chosen) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Ngoài khung giờ làm việc hôm nay']);
            exit();
        }

        // Tạo lich_hen đã xác nhận (walk-in)
        $appointmentId = $apptModel->create([
            'benh_nhan_id' => $patientId,
            'bac_si_id' => $doctorId,
            'ngay_hen' => $today,
            'gio_hen' => $chosen['time'] . ':00',
            // Walk-in tại quầy: không dùng ly_do, đánh dấu loại lịch "Tại viện"
            'ly_do' => null,
            'loai_lich' => 'Tại viện',
            'trang_thai' => 'Đã xác nhận',
            'ghi_chu' => 'Lễ tân phát số walk-in'
        ]);
        if (!$appointmentId) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Không thể tạo lịch hẹn']);
            exit();
        }

        // Tạo phiếu bốc số
        require_once 'Models/QueueTicket.php';
        $qt = new QueueTicket();
        $ticket = $qt->issueTicket($patientId, $doctorId, (int)$appointmentId, $priority, $counter);

        // Emit realtime for doctor appointment list and queue update
        try {
            // Lấy tên bệnh nhân phục vụ thông báo
            $patientName = '';
            if (class_exists('Patient')) {
                $p = $this->patientModel->getById($patientId);
                if ($p && !empty($p['ten'])) $patientName = $p['ten'];
            }
            require_once 'Services/SocketService.php';

            // Emit for doctor
            SocketService::emit('new_appointment', [
                'doctorId' => $doctorId,
                'patientName' => $patientName,
                'appointmentDate' => $today,
                // For walk-in (Trực tiếp), show current time in notification
                'appointmentTime' => date('H:i'),
                'appointmentId' => (int)$appointmentId,
            ]);

            // Generic update broadcast for doctor
            SocketService::emit('appointment_update', [
                'doctorId' => $doctorId,
                'appointmentId' => (int)$appointmentId,
                'patientName' => $patientName,
                'message' => 'Bạn vừa có một lịch mới: ' . ($patientName ?: 'Bệnh nhân'),
            ]);

            // Emit queue_update for reception queue page
            SocketService::emit('queue_update', [
                'queueId' => $ticket['id'],
                'appointmentId' => (int)$appointmentId,
                'doctorId' => $doctorId,
                'specialtyId' => $specialtyId,
                'queueStatus' => 'cho',
                'action' => 'new_ticket',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            // ignore realtime errors
        }

        echo json_encode(['success' => true, 'data' => [
            'ticket' => $ticket,
            'appointment_id' => (int)$appointmentId,
            'slot' => $chosen
        ]]);
        exit();
    }

    /**
     * Danh sách hàng đợi theo bác sĩ và ngày
     */
    public function queueList()
    {
        $this->auth->requireAuth('letan');
        header('Content-Type: application/json');
        $doctorId = isset($_GET['bac_si_id']) ? (int)$_GET['bac_si_id'] : 0;
        $date = isset($_GET['ngay']) ? $_GET['ngay'] : date('Y-m-d');
        $status = isset($_GET['trang_thai']) ? $_GET['trang_thai'] : null;

        // Phân trang
        $page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page  = max(1, $page);
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        if ($limit <= 0) {
            $limit = 10;
        }
        $offset = ($page - 1) * $limit;

        try {
            $pdo = $this->doctorModel->getConnection();

            $baseSql = "FROM phieu_boc_so t
                    JOIN benh_nhan bn ON bn.id = t.benh_nhan_id
                    JOIN bac_si bs ON bs.id = t.bac_si_id
                    LEFT JOIN lich_hen lh ON lh.id = t.lich_hen_id
                    LEFT JOIN chuyen_khoa ck ON ck.id = bs.chuyen_khoa_id
                    WHERE t.ngay = :d";
            $params = [':d' => $date];

            // Filter theo chuyên khoa nếu có
            $specialtyId = isset($_GET['chuyen_khoa_id']) ? (int)$_GET['chuyen_khoa_id'] : 0;
            if ($specialtyId > 0) {
                $baseSql .= " AND bs.chuyen_khoa_id = :ck";
                $params[':ck'] = $specialtyId;
            }

            if ($doctorId > 0) {
                $baseSql .= " AND t.bac_si_id = :bs";
                $params[':bs'] = $doctorId;
            }
            if (!empty($status)) {
                $baseSql .= " AND t.trang_thai = :st";
                $params[':st'] = $status;
            } else {
                // Mặc định ẩn các phiếu đã bắt đầu khám khỏi giao diện lễ tân
                $baseSql .= " AND t.trang_thai <> 'dang_kham'";
            }

            // Đếm tổng số phiếu
            $countSql = "SELECT COUNT(*) " . $baseSql;
            $stmtCount = $pdo->prepare($countSql);
            $stmtCount->execute($params);
            $total = (int)$stmtCount->fetchColumn();

            // Lấy danh sách phiếu cho trang hiện tại
            $dataSql = "SELECT t.*, bn.ten AS ten_benh_nhan, bs.ten AS ten_bac_si, 
                        bs.chuyen_khoa_id, ck.ten AS ten_chuyen_khoa,
                        lh.gio_hen AS thoi_gian_du_kien "
                . $baseSql
                . " ORDER BY t.uu_tien DESC, t.so_thu_tu ASC
                        LIMIT :limit OFFSET :offset";

            $stmt = $pdo->prepare($dataSql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $totalPages = max(1, (int)ceil($total / $limit));

            echo json_encode([
                'success' => true,
                'data' => $rows,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => $totalPages,
                ],
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * Đổi trạng thái phiếu (gọi/vào khám/xong/bỏ lỡ/hủy)
     * Đồng bộ lich_hen nếu cần
     */
    public function queueUpdateStatus()
    {
        $this->auth->requireAuth('letan');
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        $ticketId = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
        $newStatus = isset($_POST['trang_thai']) ? $_POST['trang_thai'] : '';
        if ($ticketId <= 0 || $newStatus === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu ticket_id hoặc trang_thai']);
            exit();
        }
        // Chỉ cho phép 2 trạng thái: gọi vào, hủy
        $allowed = ['dang_goi', 'huy'];
        if (!in_array($newStatus, $allowed, true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
            exit();
        }
        require_once 'Models/QueueTicket.php';
        $qt = new QueueTicket();
        $ok = $qt->updateStatus($ticketId, $newStatus);
        if (!$ok) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Không thể cập nhật trạng thái phiếu']);
            exit();
        }
        // Đồng bộ lịch hẹn
        $ticket = $qt->getById($ticketId);
        if ($ticket && !empty($ticket['lich_hen_id'])) {
            require_once 'Models/Appointment.php';
            $appt = new Appointment();
            if ($newStatus === 'huy') {
                $appt->updateStatus((int)$ticket['lich_hen_id'], 'hủy');
            }
        }
        // Emit realtime generic update for doctor views
        try {
            if (!empty($ticket['bac_si_id'])) {
                require_once 'Services/SocketService.php';
                SocketService::emit('appointment_update', [
                    'doctorId' => (int)$ticket['bac_si_id'],
                    'appointmentId' => isset($ticket['lich_hen_id']) ? (int)$ticket['lich_hen_id'] : null,
                    'queueStatus' => $newStatus,
                    'queueId' => $ticketId,
                ]);
            }
        } catch (Exception $e) {
        }
        echo json_encode(['success' => true]);
        exit();
    }

    /**
     * Chuyển phiếu sang bác sĩ khác (cùng ngày) và cấp lại số
     */
    public function queueReassign()
    {
        $this->auth->requireAuth('letan');
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        $ticketId = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
        $newDoctorId = isset($_POST['bac_si_id']) ? (int)$_POST['bac_si_id'] : 0;
        $date = isset($_POST['ngay']) ? $_POST['ngay'] : date('Y-m-d');
        if ($ticketId <= 0 || $newDoctorId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu ticket_id hoặc bac_si_id']);
            exit();
        }
        require_once 'Models/QueueTicket.php';
        $qt = new QueueTicket();
        $ticket = $qt->reassignDoctor($ticketId, $newDoctorId, dateYmd: $date);
        if (!$ticket) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Không thể chuyển phiếu']);
            exit();
        }
        echo json_encode(['success' => true, 'data' => $ticket]);
        exit();
    }

    /**
     * Logic cửa sổ đăng ký lịch làm việc cho Lễ tân
     * Giống với bác sĩ: chỉ được đăng ký/hoàn tất lịch cơ bản từ ngày 23 → 25 hằng tháng
     */
    private function isWithinReceptionRegistrationWindow(): bool
    {
        $dt = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $day = (int)$dt->format('j');
        return $day >= 23 && $day <= 25;
    }

    /**
     * Kiểm tra hôm nay là Thứ 2 (theo timezone Việt Nam)
     * Dùng để áp logic: chỉ cho phép lễ tân xóa ca trực vào Thứ 2, giống bác sĩ.
     */
    private function isMonday(): bool
    {
        $dt = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        return (int)$dt->format('N') === 1; // 1 = Monday
    }

    // ===== Helpers =====
    private function getOnDutyDoctorsBySpecialty(int $specialtyId, string $dateYmd): array
    {
        // Lấy danh sách bác sĩ theo chuyên khoa
        $pdo = $this->doctorModel->getConnection();
        $stmt = $pdo->prepare("SELECT id FROM bac_si WHERE chuyen_khoa_id = :ck");
        $stmt->bindParam(':ck', $specialtyId, PDO::PARAM_INT);
        $stmt->execute();
        $ids = array_map(function ($r) {
            return (int)$r['id'];
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
        if (empty($ids)) return [];
        // Lọc bác sĩ có ca làm việc hôm nay (đã áp dụng ngoại lệ)
        $onDuty = [];
        foreach ($ids as $id) {
            $schedules = $this->doctorModel->getSchedulesByDate($id, $dateYmd);
            if (!empty($schedules)) {
                $onDuty[] = $id;
            }
        }
        return $onDuty;
    }

    private function pickLeastLoadedDoctor(array $doctorIds, string $dateYmd): int
    {
        require_once 'Models/Appointment.php';
        $appt = new Appointment();
        $pdo = $this->doctorModel->getConnection();
        $best = $doctorIds[0];
        $bestLoad = PHP_INT_MAX;
        foreach ($doctorIds as $id) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM lich_hen WHERE bac_si_id = :id AND ngay_hen = :d AND trang_thai IN ('Chờ xác nhận','Đã xác nhận','Đang khám')");
            $stmt->execute([':id' => $id, ':d' => $dateYmd]);
            $load = (int)$stmt->fetchColumn();
            if ($load < $bestLoad) {
                $bestLoad = $load;
                $best = $id;
            }
        }
        return $best;
    }

    /**
     * Trang thanh toán biên lai
     */
    public function payment()
    {
        $this->auth->requireAuth('letan');

        if (class_exists('SecurityConfig')) {
            SecurityConfig::generateCSRFToken();
        }

        $page_title = 'Thanh toán biên lai';

        ob_start();
        include 'Views/reception/payment.php';
        $content = ob_get_clean();

        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, $page_title);
    }

    /**
     * API: Lấy danh sách biên lai chưa thanh toán
     */
    public function getUnpaidReceipts()
    {
        $this->auth->requireAuth('letan');

        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $date = isset($_GET['date']) ? $_GET['date'] : null;
            $offset = ($page - 1) * $limit;

            $receipts = $this->bienLaiModel->getUnpaidReceipts($limit, $offset, $date);
            $total = $this->bienLaiModel->countUnpaidReceipts();

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $receipts,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách biên lai: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Tìm kiếm biên lai
     */
    public function searchReceipts()
    {
        $this->auth->requireAuth('letan');

        try {
            $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $date = isset($_GET['date']) ? $_GET['date'] : null;

            if (empty($keyword)) {
                $receipts = $this->bienLaiModel->getUnpaidReceipts($limit, 0, $date);
            } else {
                $receipts = $this->bienLaiModel->searchReceipts($keyword, 'Chưa thanh toán', $limit, $date);
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $receipts
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi tìm kiếm biên lai: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Lấy chi tiết biên lai
     */
    public function getReceiptDetails()
    {
        $this->auth->requireAuth('letan');

        try {
            $receiptId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

            if ($receiptId <= 0) {
                throw new Exception('ID biên lai không hợp lệ');
            }

            $receipt = $this->bienLaiModel->getById($receiptId);
            if (!$receipt) {
                throw new Exception('Không tìm thấy biên lai');
            }

            $details = $this->bienLaiModel->getDetails($receiptId);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => [
                    'receipt' => $receipt,
                    'details' => $details
                ]
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * API: Xử lý thanh toán
     */
    public function processPayment()
    {
        error_log('processPayment - Starting authentication check');
        try {
            $this->auth->requireAuth('letan');
            error_log('processPayment - Authentication successful');
        } catch (Exception $e) {
            error_log('processPayment - Authentication failed: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Authentication failed: ' . $e->getMessage()
            ]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
            return;
        }

        try {
            // Debug: Log all available data
            error_log('Payment processing started');
            error_log('$_POST data: ' . json_encode($_POST));
            error_log('$_GET data: ' . json_encode($_GET));
            error_log('Content-Type: ' . ($_SERVER['CONTENT_TYPE'] ?? 'Not set'));
            error_log('Request method: ' . $_SERVER['REQUEST_METHOD']);

            // Handle both JSON and FormData
            $input = null;
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

            if (strpos($contentType, 'application/json') !== false) {
                $input = json_decode(file_get_contents('php://input'), true);
                error_log('Using JSON input');
            } else {
                // Handle FormData (multipart/form-data or application/x-www-form-urlencoded)
                $input = $_POST;
                error_log('Using POST input');
            }

            if (!$input) {
                error_log('No input data found');
                throw new Exception('Dữ liệu không hợp lệ');
            }

            $receiptId = isset($input['receipt_id']) ? (int)$input['receipt_id'] : 0;
            $paymentMethod = isset($input['payment_method']) ? trim($input['payment_method']) : '';
            $paymentNote = isset($input['payment_note']) ? trim($input['payment_note']) : '';

            // Debug logging
            error_log('Payment processing - Input data: ' . json_encode($input));
            error_log('Payment processing - Receipt ID: ' . $receiptId);
            error_log('Payment processing - Payment Method: ' . $paymentMethod);
            error_log('Payment processing - Payment Note: ' . $paymentNote);

            if ($receiptId <= 0) {
                throw new Exception('ID biên lai không hợp lệ');
            }

            if (empty($paymentMethod)) {
                throw new Exception('Vui lòng chọn phương thức thanh toán');
            }

            // Xác định trạng thái thanh toán dựa trên phương thức
            $paymentStatus = 'Đã thanh toán';
            if ($paymentMethod === 'Tiền mặt') {
                $paymentStatus = 'Đã thanh toán tiền mặt';
            }

            // Lấy id_le_tan từ session (nếu là reception)
            $idLeTan = null;
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'letan') {
                $idLeTan = $_SESSION['user_id'] ?? null;
            }

            // Cập nhật trạng thái thanh toán và id_le_tan
            $result = $this->bienLaiModel->updatePaymentStatus(
                $receiptId,
                $paymentStatus,
                $paymentMethod,
                $paymentNote,
                $idLeTan
            );

            if (!$result) {
                throw new Exception('Lỗi khi cập nhật trạng thái thanh toán');
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Thanh toán thành công'
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * API: Tạo URL thanh toán VNPAY
     */
    public function createVNPayUrl()
    {
        $this->auth->requireAuth('letan');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
            return;
        }

        try {
            // Handle both JSON and FormData
            $input = null;
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

            if (strpos($contentType, 'application/json') !== false) {
                $input = json_decode(file_get_contents('php://input'), true);
            } else {
                $input = $_POST;
            }

            if (!$input) {
                throw new Exception('Dữ liệu không hợp lệ');
            }

            $receiptId = isset($input['receipt_id']) ? (int)$input['receipt_id'] : 0;
            $amount = isset($input['amount']) ? (float)$input['amount'] : 0;

            if ($receiptId <= 0) {
                throw new Exception('ID biên lai không hợp lệ');
            }

            if ($amount <= 0) {
                throw new Exception('Số tiền không hợp lệ');
            }

            // Get receipt details for order info
            $receipt = $this->bienLaiModel->getById($receiptId);
            if (!$receipt) {
                throw new Exception('Không tìm thấy biên lai');
            }

            $orderInfo = "Thanh toán biên lai " . $receipt['ma_bien_lai'] . " - " . $receipt['ho_ten'];
            $vnpayUrl = $this->vnpayService->createPaymentUrl($amount, $orderInfo, $receiptId);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'vnpay_url' => $vnpayUrl,
                'message' => 'Tạo URL thanh toán thành công'
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Xử lý kết quả thanh toán từ VNPAY
     */
    public function vnpayReturn()
    {
        try {
            $result = $this->vnpayService->processPaymentReturn($_GET);

            if ($result['success'] && $result['code'] === '00') {
                // Payment successful
                $txnRef = $result['txn_ref'];
                $receiptId = explode('_', $txnRef)[1] ?? null;

                if ($receiptId) {
                    // Lấy id_le_tan từ session (nếu là reception)
                    $idLeTan = null;
                    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'letan') {
                        $idLeTan = $_SESSION['user_id'] ?? null;
                    }

                    // Update receipt status và id_le_tan
                    $this->bienLaiModel->updatePaymentStatus(
                        $receiptId,
                        'Đã thanh toán chuyển khoản',
                        'Thanh toán VNPAY',
                        'VNPAY Transaction: ' . $result['transaction_id'],
                        $idLeTan
                    );
                }

                // Redirect to success page
                header('Location: ./?action=reception_payment&vnpay_success=1');
                exit;
            } else {
                // Payment failed
                header('Location: ./?action=reception_payment&vnpay_error=1');
                exit;
            }
        } catch (Exception $e) {
            error_log('VNPAY Return Error: ' . $e->getMessage());
            header('Location: ./?action=reception_payment&vnpay_error=1');
            exit;
        }
    }

    /**
     * API lấy tất cả biên lai (cho thống kê)
     */
    public function getAllReceipts()
    {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $date = isset($_GET['date']) ? $_GET['date'] : null;
            $offset = ($page - 1) * $limit;

            $receipts = $this->bienLaiModel->getAllReceipts($limit, $offset, $date);
            $total = $this->bienLaiModel->countAllReceipts();

            $pagination = [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ];

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $receipts,
                'pagination' => $pagination
            ]);
        } catch (Exception $e) {
            error_log('Get all receipts error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách biên lai'
            ]);
        }
    }

    /**
     * Trang chấm công cho lễ tân
     */
    public function attendance()
    {
        $this->auth->requireAuth('letan');

        $page_title = 'Chấm công';

        ob_start();
        include 'Views/reception/attendance.php';
        $content = ob_get_clean();

        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, $page_title);
    }

    /**
     * API: Chấm công check-in/check-out
     */
    public function processAttendance()
    {
        $this->auth->requireAuth('letan');
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $action = trim($input['action'] ?? ''); // 'check_in' hoặc 'check_out'
        $faceEncoding = $input['face_encoding'] ?? null;
        $imageData = $input['image_data'] ?? null;
        $location = $input['location'] ?? null; // Địa điểm chấm công (GPS hoặc địa chỉ) - BẮT BUỘC

        if (empty($action) || !in_array($action, ['check_in', 'check_out']) || empty($faceEncoding)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin bắt buộc!']);
            exit();
        }

        // BẮT BUỘC: Kiểm tra location (GPS)
        if (empty($location)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng bật GPS và cho phép truy cập vị trí để chấm công!']);
            exit();
        }

        // Validate format location (phải có dạng latitude,longitude)
        if (!preg_match('/^-?\d+\.?\d*,-?\d+\.?\d*$/', $location)) {
            echo json_encode(['success' => false, 'message' => 'Định dạng vị trí GPS không hợp lệ!']);
            exit();
        }

        $userId = $_SESSION['user_id'] ?? null;
        $userType = 'reception';

        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập!']);
            exit();
        }

        require_once 'Models/Attendance.php';
        $attendanceModel = new Attendance();

        // Validate ảnh có hợp lệ không (kiểm tra kích thước, format)
        if ($imageData) {
            // Kiểm tra base64 image data
            if (strlen($imageData) < 100) {
                echo json_encode(['success' => false, 'message' => 'Ảnh không hợp lệ!']);
                exit();
            }

            // Decode để kiểm tra kích thước ảnh
            $imageDataDecoded = base64_decode(explode(',', $imageData)[1] ?? $imageData);
            if ($imageDataDecoded === false || strlen($imageDataDecoded) < 1000) {
                echo json_encode(['success' => false, 'message' => 'Ảnh không hợp lệ hoặc quá nhỏ!']);
                exit();
            }

            // Kiểm tra kích thước file (ảnh từ camera thường > 10KB)
            if (strlen($imageDataDecoded) < 10000) {
                echo json_encode(['success' => false, 'message' => 'Ảnh không hợp lệ. Vui lòng chụp lại từ camera!']);
                exit();
            }

            // Kiểm tra ảnh có chứa timestamp watermark không (bằng cách tìm pattern timestamp)
            // Timestamp được vẽ ở góc dưới bên trái, nên ảnh phải có kích thước đủ lớn
            // Nếu ảnh quá nhỏ hoặc không có watermark, có thể là ảnh upload
            $imageInfo = @getimagesizefromstring($imageDataDecoded);
            if ($imageInfo === false) {
                echo json_encode(['success' => false, 'message' => 'Ảnh không hợp lệ. Vui lòng chụp lại từ camera!']);
                exit();
            }

            // Kiểm tra độ phân giải tối thiểu (ảnh từ camera thường có độ phân giải nhất định)
            if ($imageInfo[0] < 320 || $imageInfo[1] < 240) {
                echo json_encode(['success' => false, 'message' => 'Ảnh có độ phân giải quá thấp. Vui lòng chụp lại từ camera!']);
                exit();
            }
        }

        // Lưu ảnh nếu có
        $imagePath = null;
        if ($imageData) {
            $imagePath = $this->saveAttendanceImage($imageData, $userId, $userType, $action);
        }

        // So sánh face encoding để xác nhận danh tính (so sánh trực tiếp với face encoding của user)
        $faceEncodingJson = is_string($faceEncoding) ? $faceEncoding : json_encode($faceEncoding);
        $comparison = $attendanceModel->compareFaceWithUser($faceEncodingJson, $userId, $userType);

        if (!$comparison['match']) {
            $baseMessage = $comparison['message'] ?? '';
            if ($baseMessage === 'Chưa đăng ký khuôn mặt') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Bạn chưa đăng ký nhận diện khuôn mặt. Vui lòng liên hệ quản trị viên để đăng ký trước khi chấm công.',
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không nhận diện được khuôn mặt hoặc không khớp với tài khoản! ' . $baseMessage,
                    'distance' => $comparison['distance'] ?? null,
                    'threshold' => $comparison['threshold'] ?? null,
                    'debug' => $comparison
                ]);
            }
            exit();
        }

        // Xử lý check-in hoặc check-out
        if ($action === 'check_in') {
            $result = $attendanceModel->checkIn($userId, $userType, $faceEncodingJson, $imagePath, $location);
        } else {
            $result = $attendanceModel->checkOut($userId, $userType, $faceEncodingJson, $imagePath, $location);
        }

        if ($result['success']) {
            $result['confidence'] = $recognized['confidence'] ?? 1.0;
        }

        echo json_encode($result);
        exit();
    }

    /**
     * Lưu ảnh chấm công
     */
    private function saveAttendanceImage($imageData, $userId, $userType, $action)
    {
        try {
            // Loại bỏ phần "data:image/png;base64," nếu có
            if (strpos($imageData, ',') !== false) {
                $imageData = explode(',', $imageData)[1];
            }

            $imageData = base64_decode($imageData);
            if ($imageData === false) {
                return null;
            }

            $uploadDir = __DIR__ . '/../uploads/attendance/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = $action . '_' . $userId . '_' . $userType . '_' . time() . '.jpg';
            $filePath = $uploadDir . $fileName;

            file_put_contents($filePath, $imageData);

            return 'uploads/attendance/' . $fileName;
        } catch (Exception $e) {
            error_log("Error saving attendance image: " . $e->getMessage());
            return null;
        }
    }

    /**
     * API: Lấy trạng thái chấm công hôm nay
     */
    public function getTodayAttendance()
    {
        try {
            $this->auth->requireAuth('letan');
            header('Content-Type: application/json; charset=utf-8');

            $userId = $_SESSION['user_id'] ?? null;
            $userType = 'reception';

            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập!']);
                exit();
            }

            require_once 'Models/Attendance.php';
            $attendanceModel = new Attendance();

            $status = $attendanceModel->getTodayStatus($userId, $userType);

            echo json_encode([
                'success' => true,
                'status' => $status ?: null
            ]);
            exit();
        } catch (Exception $e) {
            error_log("Error in getTodayAttendance: " . $e->getMessage());
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi lấy thông tin chấm công: ' . $e->getMessage()
            ]);
            exit();
        }
    }
}