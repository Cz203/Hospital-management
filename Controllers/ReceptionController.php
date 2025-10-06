<?php

require_once 'Controllers/AuthController.php';
require_once 'Models/Reception.php';
require_once 'Models/Patient.php';
require_once 'Models/Doctor.php';
require_once 'config/security.php';

class ReceptionController
{
    private $auth;
    private $receptionModel;
    private $patientModel;
    private $doctorModel;

    public function __construct()
    {
        $this->auth = new AuthController();
        $this->receptionModel = new Reception();
        $this->patientModel = new Patient();
        $this->doctorModel = new Doctor();
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
        $allowed = ['email', 'ngay_sinh', 'gioi_tinh', 'dia_chi', 'nhom_mau', 'bao_hiem_y_te'];
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
        $password = $_POST['mat_khau'] ?? '';
        $phone = trim($_POST['so_dien_thoai'] ?? '');
        $dob = trim($_POST['ngay_sinh'] ?? '');
        $gender = trim($_POST['gioi_tinh'] ?? '');
        $address = trim($_POST['dia_chi'] ?? '');
        $blood = trim($_POST['nhom_mau'] ?? '');

        // Basic validation
        if ($name === '' || $email === '' || $password === '' || $phone === '') {
            $_SESSION['error'] = 'Vui lòng nhập đủ Tên, Email, Mật khẩu, Số điện thoại.';
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
            $ok = $this->patientModel->create([
                'ten' => $name,
                'email' => $email,
                'mat_khau' => $password,
                'so_dien_thoai' => $phoneNorm,
                'phone_verified' => 1,
                'ngay_sinh' => $dob,
                'gioi_tinh' => $gender,
                'dia_chi' => $address,
                'nhom_mau' => $blood,
            ]);
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
}