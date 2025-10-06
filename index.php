<?php
session_start();

// Load Composer autoloader để sử dụng Vonage SDK
require_once 'vendor/autoload.php';

require_once 'Controllers/AuthController.php';
require_once 'Controllers/DoctorController.php';
require_once 'Controllers/AdminController.php';
require_once 'Controllers/AppointmentController.php';
require_once 'Controllers/PatientController.php';
require_once 'Controllers/ReceptionController.php';
// Khởi tạo Controllers
$auth = new AuthController();
$doctorController = new DoctorController();
$adminController = new AdminController();
$appointmentController = new AppointmentController();
$patientController = new PatientController();
$receptionController = new ReceptionController();

// Lấy action từ URL - hỗ trợ cả URL đẹp và URL cũ
$action = $_GET['action'] ?? 'home';

// Nếu không có action trong GET, thử lấy từ REQUEST_URI

// Routing
switch ($action) {
    // ===== AUTHENTICATION ROUTES =====
    case 'login':
        $auth->loginPatient(); // Đăng nhập bệnh nhân
        break;

    case 'login_admin':
        $auth->loginAdmin(); // Đăng nhập admin
        break;

    case 'login_doctor':
        $auth->loginDoctor(); // Đăng nhập bác sĩ
        break;

    case 'login_reception':
        $auth->loginReception(); // Đăng nhập lễ tân
        break;

    case 'register':
        $auth->register(); // Đăng ký tài khoản mới
        break;

    // ===== SMS/OTP ROUTES =====
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

        // ===== PASSWORD MANAGEMENT ROUTES =====
    case 'change_password':
        $auth->changePassword(); // Đổi mật khẩu
        break;

    case 'reset_password':
        $auth->resetPassword(); // Reset mật khẩu
        break;

    case 'logout':
        $auth->logout(); // Đăng xuất
        break;

    // ===== ADMIN ROUTES =====
    case 'admin_dashboard':
        $adminController->dashboard(); // Trang chủ admin
        break;

    case 'doctor_schedules':
        $adminController->manageDoctorSchedules(); // Quản lý lịch làm việc bác sĩ
        break;

    case 'doctors_list':
        $adminController->doctorsList(); // Danh sách bác sĩ (Admin)
        break;
    case 'admin_create_doctor':
        $adminController->adminCreateDoctor(); // Thêm bác sĩ (Admin)
        break;
    case 'admin_update_doctor':
        $adminController->adminUpdateDoctor(); // Cập nhật bác sĩ (Admin)
        break;
    case 'admin_delete_doctor':
        $adminController->adminDeleteDoctor(); // Xóa bác sĩ (Admin)
        break;

    // ===== SPECIALTIES (ADMIN) =====
    case 'specialties':
        $adminController->specialties();
        break;
    case 'specialty_create':
        $adminController->specialtyCreate();
        break;
    case 'specialty_update':
        $adminController->specialtyUpdate();
        break;
    case 'specialty_delete':
        $adminController->specialtyDelete();
        break;

    // ===== DASHBOARD ROUTES =====
    case 'doctor_dashboard':
        $auth->requireAuth('doctor');
        include 'Views/doctor/dashboard.php'; // Trang chủ bác sĩ
        break;

    case 'patient_dashboard':
        $auth->requireAuth('patient');
        include 'Views/patient/dashboard.php'; // Trang chủ bệnh nhân
        break;

    case 'reception_dashboard':
        $receptionController->dashboard(); // Trang chính lễ tân
        break;

    case 'reception_doctor_schedules':
        $receptionController->doctorSchedules(); // Trang lịch làm việc bác sĩ (lễ tân)
        break;

    case 'reception_queue':
        $auth->requireAuth('letan');
        include 'Views/reception/queue.php'; // Giao diện bốc số
        break;


    // ===== PATIENT ROUTES =====
    case 'patient_medical_records':
        $auth->requireAuth('patient');
        include 'Views/patient/medical_records.php'; // Hồ sơ bệnh án
        break;

    case 'home_visit_booking':
        $auth->requireAuth('patient');
        include 'Views/appointment/home_visit_booking.php'; // Đặt lịch khám tại nhà
        break;
    case 'consultation_booking':
        $appointmentController->consultationBooking(); // Đặt lịch tư vấn trực tuyến
        break;

    // ===== SPECIALTIES PUBLIC PAGES =====
    case 'doctors_by_specialty':
        $doctorController->listBySpecialty();
        break;
    case 'specialties_all':
        $doctorController->specialtiesAll();
        break;

    // ===== PROFILE ROUTES =====
    case 'admin_profile':
        $auth->requireAuth('admin');
        include 'Views/admin/profile.php'; // Hồ sơ admin
        break;

    case 'doctor_profile':
        $auth->requireAuth('doctor');
        include 'Views/doctor/profile.php'; // Hồ sơ bác sĩ
        break;

    case 'patient_profile':
        $auth->requireAuth('patient');
        include 'Views/patient/profile.php'; // Hồ sơ bệnh nhân
        break;

    case 'patient_update_profile':
        $patientController->updateProfile(); // Cập nhật hồ sơ bệnh nhân
        break;

    case 'patient_upload_avatar':
        $patientController->uploadAvatar(); // Upload ảnh đại diện bệnh nhân
        break;

    // ===== DOCTOR TEAM ROUTES =====
    case 'doctor_team':
        include 'Views/doctor/doctor_team.php'; // Danh sách bác sĩ
        break;

    // ===== DOCTOR SCHEDULE MANAGEMENT ROUTES =====
    case 'doctor_schedule_management':
        $doctorController->manageSchedule(); // Quản lý lịch làm việc
        break;

    case 'doctor_appointment_management':
        $doctorController->appointmentManagement(); // Quản lý lịch hẹn
        break;

    case 'update_appointment_status':
        $doctorController->updateAppointmentStatus(); // Cập nhật trạng thái lịch hẹn
        break;

    case 'doctor_add_schedule':
        $doctorController->addSchedule(); // Thêm lịch làm việc
        break;

    case 'doctor_update_schedule':
        $doctorController->updateSchedule(); // Cập nhật lịch làm việc
        break;

    case 'doctor_delete_schedule':
        $doctorController->deleteSchedule(); // Xóa lịch làm việc
        break;

    case 'doctor_get_schedule_info':
        $doctorController->getScheduleInfo(); // Lấy thông tin lịch làm việc
        break;

    case 'doctor_get_schedules_by_day':
        $doctorController->getSchedulesByDay(); // Lấy lịch làm việc theo ngày
        break;

    case 'doctor_modify_schedule_for_date':
        $doctorController->modifyScheduleForDate(); // Chỉnh sửa lịch cho 1 ngày cụ thể
        break;

    case 'doctor_cancel_schedule_for_date':
        $doctorController->cancelScheduleForDate(); // Hủy lịch cho 1 ngày cụ thể
        break;

    case 'doctor_examination':
        $doctorController->examination(); // Khám bệnh - danh sách lịch hẹn hôm nay
        break;
    case 'start_examination':
        $doctorController->startExamination(); // Bắt đầu khám bệnh
        break;
    case 'save_allergy_history':
        $doctorController->saveAllergyHistory();
        break;
    case 'get_allergy_history':
        $doctorController->getAllergyHistory();
        break;

    // ===== APPOINTMENT ROUTES =====
    case 'hospital_appointment':
        $appointmentController->hospitalAppointment(); // Đặt lịch khám tại bệnh viện
        break;

    case 'book_appointment':
        $appointmentController->bookAppointment(); // Đặt lịch hẹn
        break;

    case 'cancel_appointment':
        $appointmentController->cancelAppointment(); // Hủy lịch hẹn
        break;

    case 'get_doctors_by_specialty':
        $appointmentController->getDoctorsBySpecialty(); // Lấy danh sách bác sĩ theo chuyên khoa (AJAX)
        break;

    case 'get_available_time_slots':
        $appointmentController->getAvailableTimeSlots(); // Lấy khung giờ có sẵn (AJAX)
        break;

    case 'check_conflict':
        $appointmentController->checkConflict(); // Kiểm tra xung đột (AJAX)
        break;

    case 'get_doctor_schedule':
        $appointmentController->getDoctorSchedule(); // Lấy lịch làm việc bác sĩ (AJAX)
        break;

    case 'reception_find_patient':
        $receptionController->findPatientByPhone(); // Tra cứu BN theo SĐT (AJAX)
        break;

    case 'reception_get_doctor_schedules':
        $receptionController->getDoctorSchedules(); // API lấy lịch làm việc (lễ tân)
        break;

    case 'reception_complete_patient':
        $receptionController->completePatientProfile(); // Bổ sung thông tin còn thiếu (AJAX)
        break;

    case 'reception_patient_create':
        $receptionController->patientCreateForm(); // Form thêm bệnh nhân
        break;

    case 'reception_patient_store':
        $receptionController->patientStore(); // Lưu bệnh nhân mới
        break;

    // ===== RECEPTION QUEUE (WALK-IN) =====
    case 'reception_issue_ticket':
        $receptionController->issueQueueTicket();
        break;
    case 'reception_queue_list':
        $receptionController->queueList();
        break;
    case 'reception_queue_update':
        $receptionController->queueUpdateStatus();
        break;
    case 'reception_queue_reassign':
        $receptionController->queueReassign();
        break;

    case 'patient_appointments':
        $appointmentController->patientAppointments(); // Lịch hẹn của bệnh nhân
        break;

    // ===== GENERAL ROUTES =====
    case 'home':
        include 'Views/home.php'; // Trang chủ
        break;

    // ===== DEFAULT ROUTE =====
    default:
        // Nếu action không tồn tại, kiểm tra nếu user đã đăng nhập thì redirect về dashboard tương ứng
        if ($auth->isLoggedIn()) {
            $role = $_SESSION['user_role'];
            switch ($role) {
                case 'admin':
                    header("Location: ./admin_dashboard"); // Redirect admin về dashboard
                    exit();
                case 'doctor':
                    header("Location: ./doctor_dashboard"); // Redirect bác sĩ về dashboard
                    exit();
                case 'patient':
                    header("Location: ./patient_dashboard"); // Redirect bệnh nhân về dashboard
                    exit();
                case 'letan':
                    header("Location: ./reception_dashboard"); // Redirect lễ tân về dashboard
                    exit();
            }
        }

        // Nếu không đăng nhập hoặc action không tồn tại, hiển thị trang chủ
        include 'Views/home.php';
        break;
}