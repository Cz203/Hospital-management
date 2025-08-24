<?php
session_start();

// Load Composer autoloader để sử dụng Vonage SDK
require_once 'vendor/autoload.php';

require_once 'Controllers/AuthController.php';

// Khởi tạo AuthController
$auth = new AuthController();

// Lấy action từ URL - hỗ trợ cả URL đẹp và URL cũ
$action = $_GET['action'] ?? 'home';

// Nếu không có action trong GET, thử lấy từ REQUEST_URI



// Routing
switch ($action) {
    case 'login':
        $auth->loginPatient();
        break;

    case 'login_admin':
        $auth->loginAdmin();
        break;

    case 'login_doctor':
        $auth->loginDoctor();
        break;

    case 'register':
        $auth->register();
        break;

    case 'send_otp':
        require_once 'Controllers/SMSController.php';
        $smsController = new SMSController();

        // Nhận JSON data
        $input = json_decode(file_get_contents('php://input'), true);
        $phone_number = $input['phone_number'] ?? '';
        $check_database = $input['check_database'] ?? false;
        $for_registration = $input['for_registration'] ?? false;

        if (empty($phone_number)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Số điện thoại không được để trống']);
            exit();
        }

        $result = $smsController->sendOTP($phone_number, $check_database, $for_registration);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
        break;

    case 'verify_otp':
        require_once 'Controllers/SMSController.php';
        $smsController = new SMSController();

        // Nhận JSON data
        $input = json_decode(file_get_contents('php://input'), true);
        $phone_number = $input['phone_number'] ?? '';
        $otp_code = $input['otp_code'] ?? '';

        if (empty($phone_number) || empty($otp_code)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Số điện thoại và mã OTP không được để trống']);
            exit();
        }

        $result = $smsController->verifyOTP($phone_number, $otp_code);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
        break;

    case 'change_password':
        $auth->changePassword();
        break;

    case 'reset_password':
        $auth->resetPassword();
        break;

    case 'logout':
        $auth->logout();
        break;

    case 'admin_dashboard':
        $auth->requireAuth('admin');
        include 'Views/admin/dashboard.php';
        break;

    case 'doctor_dashboard':
        $auth->requireAuth('doctor');
        include 'Views/doctor/dashboard.php';
        break;

    case 'patient_dashboard':
        $auth->requireAuth('patient');
        include 'Views/patient/dashboard.php';
        break;

    case 'patient_appointments':
        $auth->requireAuth('patient');
        include 'Views/patient/appointments.php';
        break;

    case 'patient_medical_records':
        $auth->requireAuth('patient');
        include 'Views/patient/medical_records.php';
        break;

    case 'home_visit_booking':
        $auth->requireAuth('patient');
        include 'Views/appointment/home_visit_booking.php';
        break;

    case 'hospital_appointment':
        $auth->requireAuth('patient');
        include 'Views/appointment/hospital_appointment.php';
        break;

    case 'admin_profile':
        $auth->requireAuth('admin');
        include 'Views/admin/profile.php';
        break;

    case 'doctor_profile':
        $auth->requireAuth('doctor');
        include 'Views/doctor/profile.php';
        break;

    case 'patient_profile':
        $auth->requireAuth('patient');
        include 'Views/patient/profile.php';
        break;

    case 'doctor_team':
        // Danh sách toàn bộ bác sĩ
        include 'Views/doctor/doctor_team.php';
        break;

    case 'home':
        // Trang chủ - luôn hiển thị, không redirect
        include 'Views/home.php';
        break;

    default:
        // Nếu action không tồn tại, kiểm tra nếu user đã đăng nhập thì redirect về dashboard tương ứng
        if ($auth->isLoggedIn()) {
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

        // Nếu không đăng nhập hoặc action không tồn tại, hiển thị trang chủ
        include 'Views/home.php';
        break;
}
