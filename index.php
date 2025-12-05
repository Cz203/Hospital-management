<?php
session_start();

// Set timezone Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Load Composer autoloader để sử dụng Vonage SDK
require_once 'vendor/autoload.php';

// Load environment variables from .env (if present)
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
} catch (Throwable $e) {
    // ignore if dotenv not available
}

require_once 'Controllers/AuthController.php';
require_once 'Controllers/DoctorController.php';
require_once 'Controllers/AdminController.php';
require_once 'Controllers/AppointmentController.php';
require_once 'Controllers/PrescriptionController.php';
require_once 'Controllers/PatientController.php';
require_once 'Controllers/ReceptionController.php';
require_once 'Controllers/ReceiptController.php';
require_once 'Controllers/MedicalRecordController.php';
require_once 'Controllers/PatientReceiptController.php';
require_once 'Controllers/NotificationController.php';
// Khởi tạo Controllers
$auth = new AuthController();
$doctorController = new DoctorController();
$adminController = new AdminController();
$appointmentController = new AppointmentController();
$prescriptionController = new PrescriptionController();
$patientController = new PatientController();
$receptionController = new ReceptionController();
$medicalRecordController = new MedicalRecordController();
$patientReceiptController = new PatientReceiptController();
$notificationController = new NotificationController();

// Khởi tạo ReceiptController
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();
$receiptController = new ReceiptController($db);

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
    case 'login_xquang':
        $auth->loginXrayDoctor(); // Đăng nhập bác sĩ X-Quang (chuyên khoa 16)
        break;
    case 'login_xetnghiem':
        $auth->loginXetnghiem(); // Đăng nhập bác sĩ Xét nghiệm (chuyên khoa 17)
        break;

    case 'login_sieuam':
        $auth->loginSieuam(); // Đăng nhập bác sĩ Siêu âm (chuyên khoa 18)

        break;

    case 'register':
        $auth->register(); // Đăng ký tài khoản mới
        break;

    case 'verify_cccd':
        // API xác thực CCCD giả lập
        header('Content-Type: application/json');
        require_once 'Services/CCCDService.php';
        require_once 'config/database.php';

        $cccd = trim($_POST['cccd'] ?? '');
        $ten = trim($_POST['ten'] ?? '');
        $ngaySinh = trim($_POST['ngay_sinh'] ?? '');

        if (empty($cccd)) {
            echo json_encode([
                'success' => false,
                'message' => 'Vui lòng nhập số CCCD'
            ]);
            exit();
        }

        $database = new Database();
        $db = $database->getConnection();
        $cccdService = new CCCDService($db);

        $result = $cccdService->verifyCCCD(
            $cccd,
            !empty($ten) ? $ten : null,
            !empty($ngaySinh) ? $ngaySinh : null
        );

        echo json_encode($result);
        exit();

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

    case 'refresh_socket_token':
        $auth->refreshSocketToken(); // API: Refresh JWT token cho socket
        break;

    // ===== ADMIN ROUTES =====
    case 'admin_dashboard':
        $adminController->dashboard(); // Trang chủ admin
        break;

    case 'get_revenue_stats':
        $adminController->getRevenueStats(); // API: Lấy thống kê doanh thu theo filter
        break;

    case 'get_appointment_stats':
        $adminController->getAppointmentStatsByFilter(); // API: Lấy thống kê lịch hẹn theo filter
        break;

    case 'get_patient_stats':
        $adminController->getPatientStatsByFilter(); // API: Lấy thống kê số lượng bệnh nhân theo filter
        break;

    case 'get_appointment_status_stats':
        $adminController->getAppointmentStatusStats(); // API: Lấy tỷ lệ trạng thái lịch hẹn (biểu đồ tròn)
        break;

    case 'get_specialty_stats':
        $adminController->getSpecialtyStats(); // API: Lấy top chuyên khoa được đặt lịch nhiều nhất (biểu đồ tròn)
        break;

    case 'doctor_schedules':
        $adminController->manageDoctorSchedules(); // Quản lý lịch làm việc bác sĩ
        break;

    case 'admin_reception_schedules':
        $adminController->receptionSchedules(); // Quản lý lịch làm việc lễ tân
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

    case 'patients':
        $adminController->patientsList(); // Danh sách bệnh nhân (Admin)
        break;

    case 'reception_list':
        $adminController->receptionList(); // Danh sách lễ tân (Admin)
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

    // ===== ADMIN SCHEDULE MANAGEMENT =====
    case 'admin_add_schedule':
        $adminController->adminAddSchedule();
        break;
    case 'admin_update_schedule':
        $adminController->adminUpdateSchedule();
        break;
    case 'admin_delete_schedule':
        $adminController->adminDeleteSchedule();
        break;
    case 'admin_get_schedule_info':
        $adminController->adminGetScheduleInfo();
        break;

    // ADMIN RECEPTION SCHEDULE MANAGEMENT
    case 'admin_add_reception_schedule':
        $adminController->adminAddReceptionSchedule();
        break;
    case 'admin_update_reception_schedule':
        $adminController->adminUpdateReceptionSchedule();
        break;
    case 'admin_delete_reception_schedule':
        $adminController->adminDeleteReceptionSchedule();
        break;
    case 'admin_get_reception_schedule_info':
        $adminController->adminGetReceptionScheduleInfo();
        break;

    // ===== ADMIN APPOINTMENT MANAGEMENT =====
    case 'admin_appointments':
        $adminController->appointments();
        break;
    case 'admin_cancel_appointment':
        $adminController->cancelAppointment();
        break;

    // Quản lý bệnh nhân & lễ tân (Admin)
    case 'admin_create_patient':
        $adminController->adminCreatePatient();
        break;
    case 'admin_update_patient':
        $adminController->adminUpdatePatient();
        break;
    case 'admin_delete_patient':
        $adminController->adminDeletePatient();
        break;

    case 'admin_create_reception':
        $adminController->adminCreateReception();
        break;
    case 'admin_update_reception':
        $adminController->adminUpdateReception();
        break;
    case 'admin_delete_reception':
        $adminController->adminDeleteReception();
        break;

    // ===== ADMIN FACE RECOGNITION =====
    case 'admin_face_registration':
        $adminController->faceRegistration(); // Trang đăng ký face recognition
        break;
    case 'admin_save_face_encoding':
        $adminController->saveFaceEncoding(); // API: Lưu face encoding
        break;
    case 'admin_get_users':
        $adminController->getUsers(); // API: Lấy danh sách users
        break;
    case 'admin_get_user_info':
        $adminController->getUserInfo(); // API: Lấy thông tin user
        break;
    case 'admin_get_registered_faces':
        $adminController->getRegisteredFaces(); // API: Lấy danh sách người đã đăng ký khuôn mặt
        break;
    case 'admin_delete_face_encoding':
        $adminController->deleteFaceEncoding(); // API: Xóa face encoding (sau khi verify)
        break;

    // ===== DASHBOARD ROUTES =====
    case 'doctor_dashboard':
        $auth->requireAuth('doctor');
        include 'Views/doctor/dashboard.php'; // Trang chủ bác sĩ
        break;

    case 'xray_dashboard':
        $auth->requireAuth('xray_doctor');
        include 'Views/doctor/xray_dashboard.php'; // Dashboard riêng cho bác sĩ X-Quang
        break;
    case 'xetnghiem_dashboard':
        $auth->requireAuth('xetnghiem_doctor');
        include 'Views/doctor/xetnghiem_dashboard.php'; // Dashboard riêng cho bác sĩ Xét nghiệm
        break;

    case 'sieuam_dashboard':
        $auth->requireAuth('sieuam_doctor');
        include 'Views/doctor/sieuam_dashboard.php'; // Dashboard riêng cho bác sĩ Siêu âm
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

    case 'reception_schedule_management':
        $receptionController->scheduleManagement(); // Lịch làm việc lễ tân
        break;

    case 'reception_add_schedule':
        $receptionController->addSchedule(); // Lưu ca trực lễ tân
        break;

    case 'reception_delete_schedule':
        $receptionController->deleteSchedule(); // Xóa ca trực lễ tân
        break;

    case 'reception_queue':
        $auth->requireAuth('letan');
        include 'Views/reception/queue.php'; // Giao diện bốc số
        break;


    // ===== PATIENT ROUTES =====
    case 'patient_medical_records':
        $medicalRecordController->patientIndex(); // Hồ sơ bệnh án
        break;
    case 'get_patient_medical_records':
        $medicalRecordController->getPatientRecords(); // API lấy danh sách hồ sơ bệnh án
        break;
    case 'patient_receipts':
        $patientReceiptController->patientIndex(); // Biên lai viện phí (bệnh nhân)
        break;
    case 'get_patient_receipts':
        $patientReceiptController->getPatientReceipts(); // API danh sách biên lai (bệnh nhân)
        break;
    case 'render_patient_receipt_detail':
        $patientReceiptController->renderPatientReceiptDetail(); // Render chi tiết biên lai (modal bệnh nhân)
        break;
    case 'render_patient_medical_record_detail':
        $medicalRecordController->renderPatientDetail(); // Render view chi tiết hồ sơ bệnh án (PHP template)
        break;
    case 'view_patient_lab_request':
        $medicalRecordController->viewPatientLabRequest(); // View phiếu chỉ định xét nghiệm cho patient (PDF-like)
        break;
    case 'view_patient_lab_result':
        $medicalRecordController->viewPatientLabResult(); // View kết quả xét nghiệm cho patient (PDF-like)
        break;
    case 'view_patient_ultrasound_request':
        $medicalRecordController->viewPatientUltrasoundRequest(); // View phiếu chỉ định siêu âm cho patient (PDF-like)
        break;
    case 'view_patient_ultrasound_result':
        $medicalRecordController->viewPatientUltrasoundResult(); // View kết quả siêu âm cho patient (PDF-like)
        break;
    case 'view_patient_xray_request':
        $medicalRecordController->viewPatientXrayRequest(); // View phiếu chỉ định X-Quang cho patient (PDF-like)
        break;
    case 'view_patient_xray_result':
        $medicalRecordController->viewPatientXrayResult(); // View kết quả X-Quang cho patient (PDF-like)
        break;
    case 'view_patient_examination_form':
        $medicalRecordController->viewPatientExaminationForm(); // View phiếu khám bệnh cho patient (PDF-like)
        break;
    case 'view_patient_prescription_form':
        $medicalRecordController->viewPatientPrescriptionForm(); // View đơn thuốc cho patient (PDF-like)
        break;
    case 'view_patient_xray_images':
        $medicalRecordController->viewPatientXrayImages(); // View hình ảnh X-Quang cho patient
        break;
    case 'view_patient_ultrasound_images':
        $medicalRecordController->viewPatientUltrasoundImages(); // View hình ảnh Siêu âm cho patient
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

    case 'doctor_today_appointments':
        $doctorController->todayAppointments(); // Lịch hẹn hôm nay
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

    // ===== DOCTOR ATTENDANCE =====
    case 'doctor_attendance':
        $doctorController->attendance(); // Trang chấm công bác sĩ
        break;
    case 'doctor_process_attendance':
        $doctorController->processAttendance(); // API: Xử lý chấm công
        break;
    case 'doctor_get_today_attendance':
        $doctorController->getTodayAttendance(); // API: Lấy trạng thái chấm công hôm nay
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
    case 'save_examination_form':
        $doctorController->saveExaminationForm();
        break;
    case 'get_examination_form':
        $doctorController->getExaminationForm();
        break;
    case 'print_examination_form':
        $doctorController->printExaminationForm();
        break;
    case 'complete_examination':
        $doctorController->completeExamination(); // Hoàn thành khám bệnh
        break;
    case 'check_examination_completion':
        $doctorController->checkExaminationCompletion(); // Kiểm tra điều kiện hoàn thành
        break;

    case 'doctor_medical_records':
        $medicalRecordController->index(); // Hồ sơ bệnh án - tra cứu danh sách bệnh án
        break;
    case 'get_doctor_medical_records':
        $medicalRecordController->getRecords(); // API lấy danh sách hồ sơ bệnh án
        break;
    case 'get_doctor_medical_record_detail':
        $medicalRecordController->getDetail(); // API lấy chi tiết hồ sơ bệnh án (JSON)
        break;

    case 'render_doctor_medical_record_detail':
        $medicalRecordController->renderDetail(); // Render view chi tiết hồ sơ bệnh án (PHP template)
        break;

    case 'get_xray_suggestions':
        $doctorController->getXraySuggestions(); // Lấy gợi ý X-Quang từ database
        break;

    case 'calculate_xray_price':
        $doctorController->calculateXrayPrice(); // Tính giá tiền X-Quang
        break;

    case 'save_xray_form':
        $doctorController->saveXrayForm(); // Lưu phiếu chụp X-Quang
        break;

    case 'print_xray_form':
        $doctorController->printXrayForm(); // In phiếu chụp X-Quang
        break;

    case 'view_xray_form':
        $doctorController->viewXrayForm(); // Xem chi tiết phiếu chụp X-Quang
        break;

    case 'get_xray_form_data':
        $doctorController->getXrayFormData(); // Lấy dữ liệu phiếu chụp X-Quang
        break;

    case 'get_xray_form_by_exam_id':
        $doctorController->getXrayFormByExamId(); // Lấy phiếu chụp X-Quang theo ID phiếu khám bệnh
        break;

    case 'get_patient_bhyt_status':
        $doctorController->getPatientBhytStatus(); // Lấy trạng thái BHYT của bệnh nhân
        break;

    case 'get_xray_result_data':
        $doctorController->getXrayResultData(); // Dữ liệu trả kết quả X-Quang
        break;

    case 'upload_xray_images':
        $doctorController->uploadXrayImages(); // Upload ảnh X-Quang
        break;

    case 'save_xray_result':
        $doctorController->saveXrayResult(); // Lưu kết quả X-Quang
        break;

    case 'print_xray_result':
        $doctorController->printXrayResult(); // In kết quả X-Quang
        break;

    case 'complete_xray_result':
        $doctorController->completeXrayResult(); // Hoàn thành kết quả X-Quang
        break;

    case 'get_saved_xray_result':
        $doctorController->getSavedXrayResult(); // Lấy dữ liệu kết quả đã lưu
        break;

    case 'save_xray_images':
        $doctorController->saveXrayImages(); // Lưu ảnh X-Quang vào database
        break;

    case 'get_saved_xray_images':
        $doctorController->getSavedXrayImages(); // Lấy ảnh X-Quang đã lưu
        break;

    case 'get_xray_stats_today':
        $doctorController->getXrayStatsToday(); // Thống kê dashboard hôm nay
        break;

    case 'get_requested_xray':
        $doctorController->getRequestedXrayList(); // Danh sách phiếu chụp trạng thái Đã yêu cầu
        break;

    case 'get_xray_result_by_exam':
        $doctorController->getXrayResultByExamIdForView(); // KQ X-Quang theo exam id
        break;

    case 'get_ultrasound_result_by_exam':
        $doctorController->getUltrasoundResultByExamIdForView(); // KQ Siêu âm theo exam id
        break;

    case 'get_saved_ultrasound_images':
        $doctorController->getSavedUltrasoundImages(); // Hình ảnh siêu âm đã lưu
        break;

    case 'get_ultrasound_suggestions':
        $doctorController->getUltrasoundSuggestions(); // Gợi ý siêu âm
        break;
    case 'save_ultrasound_form':
        $doctorController->saveUltrasoundForm(); // Lưu phiếu yêu cầu siêu âm
        break;
    case 'get_ultrasound_form_data':
        $doctorController->getUltrasoundFormData(); // Lấy dữ liệu phiếu yêu cầu siêu âm
        break;
    case 'print_ultrasound_form':
        $doctorController->printUltrasoundForm(); // In phiếu yêu cầu siêu âm
        break;

    case 'get_prescription_form_data':
        $doctorController->getPrescriptionFormData(); // Lấy dữ liệu đơn thuốc theo exam ID
        break;

    case 'print_prescription_form':
        $doctorController->printPrescriptionForm(); // In đơn thuốc
        break;

    case 'save_receipt':
        $receiptController->saveReceipt(); // Lưu biên lai viện phí
        break;

    case 'print_receipt_form':
        $receiptController->printReceiptForm(); // In biên lai viện phí
        break;

    case 'get_ultrasound_stats':
        $doctorController->getUltrasoundStats(); // Lấy thống kê siêu âm
        break;

    case 'getReceiptData':
        $doctorController->getReceiptData(); // Lấy dữ liệu yêu cầu cho biên lai
        break;

    case 'get_receipt_code':
        $receiptController->getReceiptCode(); // Lấy mã biên lai
        break;

    case 'get_medications':
        $doctorController->getMedications(); // Lấy danh sách thuốc
        break;

    case 'get_current_doctor':
        $doctorController->getCurrentDoctor(); // Lấy thông tin bác sĩ hiện tại
        break;

    case 'get_dich_vu_kham':
        $doctorController->getDichVuKham(); // Lấy đơn giá dịch vụ khám bệnh
        break;

    case 'check_lab_duplicate':
        $doctorController->checkLabDuplicate(); // Kiểm tra trùng lặp yêu cầu xét nghiệm
        break;

    case 'get_ultrasound_requests':
        $doctorController->getUltrasoundRequests(); // Lấy danh sách yêu cầu siêu âm
        break;

    case 'get_ultrasound_result':
        $doctorController->getUltrasoundResult(); // Lấy kết quả siêu âm
        break;

    case 'save_ultrasound_result':
        $doctorController->saveUltrasoundResult(); // Lưu kết quả siêu âm
        break;

    case 'save_sieu_am_result':
        $doctorController->saveSieuAmResult(); // Lưu kết quả siêu âm với hình ảnh
        break;

    case 'upload_sieu_am_images':
        $doctorController->uploadSieuAmImages(); // Upload hình ảnh siêu âm
        break;

    case 'get_sieu_am_images':
        $doctorController->getSieuAmImages(); // Lấy hình ảnh siêu âm
        break;

    case 'get_sieu_am_result':
        $doctorController->getSieuAmResult(); // Lấy kết quả siêu âm
        break;

    case 'delete_sieu_am_image':
        $doctorController->deleteSieuAmImage(); // Xóa hình ảnh siêu âm
        break;

    case 'complete_sieu_am_result':
        $doctorController->completeSieuAmResult(); // Hoàn thành phiếu siêu âm
        break;

    // ===== LAB ROUTES =====
    case 'get_lab_suggestions':
        $doctorController->getLabSuggestions(); // Gợi ý xét nghiệm
        break;
    case 'save_lab_form':
        $doctorController->saveLabForm(); // Lưu phiếu yêu cầu xét nghiệm
        break;
    case 'get_lab_form_data':
        $doctorController->getLabFormData(); // Lấy dữ liệu phiếu yêu cầu xét nghiệm
        break;
    case 'print_lab_form':
        $doctorController->printLabForm(); // In phiếu yêu cầu xét nghiệm
        break;
    case 'get_lab_result_by_exam':
        $doctorController->getLabResultByExam(); // Lấy kết quả xét nghiệm theo phiếu khám
        break;
    case 'get_xetnghiem_requests':
        $doctorController->getXetnghiemRequests(); // Lấy danh sách yêu cầu xét nghiệm
        break;
    case 'get_lab_dashboard_stats':
        $doctorController->getLabDashboardStats(); // Lấy thống kê dashboard xét nghiệm
        break;
    case 'get_xetnghiem_result':
        $doctorController->getXetnghiemResult(); // Lấy kết quả xét nghiệm
        break;
    case 'get_xetnghiem_detail':
        $doctorController->getXetnghiemDetail(); // Lấy chi tiết yêu cầu xét nghiệm
        break;
    case 'get_test_suggestions':
        $doctorController->getTestSuggestions(); // Lấy gợi ý xét nghiệm
        break;
    case 'save_xetnghiem_result':
        $doctorController->saveXetnghiemResult(); // Lưu kết quả xét nghiệm
        break;
    case 'print_xetnghiem_result':
        $doctorController->printXetnghiemResult(); // In kết quả xét nghiệm
        break;
    case 'complete_xetnghiem_request':
        $doctorController->completeXetnghiemRequest(); // Hoàn thành yêu cầu xét nghiệm
        break;

    case 'get_chi_so_xet_nghiem':
        $doctorController->getChiSoXetNghiem(); // Lấy dữ liệu chỉ số xét nghiệm
        break;

    case 'sieuam_history':
        include 'Views/doctor/sieuam_history.php'; // Lịch sử siêu âm
        break;

    case 'xetnghiem_history':
        include 'Views/doctor/xetnghiem_history.php'; // Lịch sử xét nghiệm
        break;

    case 'get_sieuam_history':
        $doctorController->getSieuamHistory(); // Lấy dữ liệu lịch sử siêu âm
        break;

    case 'get_xetnghiem_history':
        $doctorController->getXetnghiemHistory(); // Lấy dữ liệu lịch sử xét nghiệm
        break;

    case 'get_xetnghiem_history_detail':
        $doctorController->getXetnghiemHistoryDetail(); // Lấy chi tiết lịch sử xét nghiệm
        break;

    case 'get_sieuam_result_view':
        $doctorController->getSieuamResultView(); // Lấy thông tin kết quả siêu âm để xem
        break;


    case 'get_sieuam_images':
        $doctorController->getSieuAmImages(); // Lấy hình ảnh siêu âm
        break;

    case 'xray_history':
        include 'Views/doctor/xray_history.php';
        break;

    case 'get_xray_history':
        $doctorController->getXrayHistory();
        break;

    case 'get_xray_result_view':
        $doctorController->getXrayResultView(); // JSON for read-only modal
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

    case 'get_appointment_detail':
        $appointmentController->getAppointmentDetail(); // JSON chi tiết lịch hẹn theo role
        break;

    case 'reception_find_patient':
        $receptionController->findPatientByPhone(); // Tra cứu BN theo SĐT (AJAX)
        break;

    case 'reception_get_doctor_schedules':
        $receptionController->getDoctorSchedules(); // API lấy lịch làm việc (lễ tân)
        break;

    case 'get_doctor_info':
        $doctorController->getDoctorInfo(); // Lấy thông tin bác sĩ hiện tại
        break;
    case 'search_medications':
        $doctorController->searchMedications(); // Tìm kiếm thuốc
        break;

    case 'search_medications_public':
        // API công khai không cần authentication
        require_once 'Controllers/DoctorController.php';
        $controller = new DoctorController();
        $controller->searchMedicationsPublic();
        break;

    case 'get_prescription_by_exam':
        header('Content-Type: application/json');
        $prescriptionController->getPrescriptionByExamId();
        exit();

        // ===== NOTIFICATIONS (DB) =====
    case 'notifications':
        // GET list notifications for current user
        $notificationController->list();
        break;
    case 'notifications_mark_all_read':
        // POST/GET mark all as read
        $notificationController->markAllRead();
        break;



    case 'save_prescription':
        header('Content-Type: application/json');
        // Nhận JSON từ client
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $result = $prescriptionController->savePrescription($input);
        echo json_encode($result);
        exit();

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

    // ===== RECEPTION PAYMENT =====
    case 'reception_payment':
        $receptionController->payment(); // Trang thanh toán biên lai
        break;
    case 'reception_get_unpaid_receipts':
        $receptionController->getUnpaidReceipts(); // API lấy danh sách biên lai chưa thanh toán
        break;
    case 'reception_get_all_receipts':
        $receptionController->getAllReceipts(); // API lấy tất cả biên lai (cho thống kê)
        break;
    case 'reception_search_receipts':
        $receptionController->searchReceipts(); // API tìm kiếm biên lai
        break;
    case 'reception_get_receipt_details':
        $receptionController->getReceiptDetails(); // API lấy chi tiết biên lai
        break;
    case 'reception_process_payment':
        $receptionController->processPayment(); // API xử lý thanh toán
        break;
    case 'reception_create_vnpay_url':
        $receptionController->createVNPayUrl(); // API tạo URL VNPAY
        break;
    case 'reception_vnpay_return':
        $receptionController->vnpayReturn(); // Xử lý kết quả VNPAY
        break;

    // ===== RECEPTION ATTENDANCE =====
    case 'reception_attendance':
        $receptionController->attendance(); // Trang chấm công lễ tân
        break;
    case 'reception_process_attendance':
        $receptionController->processAttendance(); // API: Xử lý chấm công
        break;
    case 'reception_get_today_attendance':
        $receptionController->getTodayAttendance(); // API: Lấy trạng thái chấm công hôm nay
        break;

    case 'patient_appointments':
        $appointmentController->patientAppointments(); // Lịch hẹn của bệnh nhân
        break;

    // ===== GENERAL ROUTES =====
    case 'home':
        include 'Views/home.php'; // Trang chủ
        break;

    case 'contact':
        include 'Views/contact.php'; // Trang liên hệ
        break;

    case 'lookup_medical_record':
        $medicalRecordController->lookupMedicalRecord(); // Tra cứu hồ sơ (không cần đăng nhập)
        break;

    case 'lookup_medical_record_detail':
        $medicalRecordController->lookupMedicalRecordDetail(); // Xem chi tiết hồ sơ từ tra cứu
        break;

    case 'lookup_patient_lab_request':
        $medicalRecordController->lookupPatientLabRequest(); // Tra cứu phiếu chỉ định xét nghiệm
        break;
    case 'lookup_patient_lab_result':
        $medicalRecordController->lookupPatientLabResult(); // Tra cứu kết quả xét nghiệm
        break;
    case 'lookup_patient_ultrasound_request':
        $medicalRecordController->lookupPatientUltrasoundRequest(); // Tra cứu phiếu chỉ định siêu âm
        break;
    case 'lookup_patient_ultrasound_result':
        $medicalRecordController->lookupPatientUltrasoundResult(); // Tra cứu kết quả siêu âm
        break;
    case 'lookup_patient_xray_request':
        $medicalRecordController->lookupPatientXrayRequest(); // Tra cứu phiếu chỉ định X-Quang
        break;
    case 'lookup_patient_xray_result':
        $medicalRecordController->lookupPatientXrayResult(); // Tra cứu kết quả X-Quang
        break;
    case 'lookup_patient_examination_form':
        $medicalRecordController->lookupPatientExaminationForm(); // Tra cứu phiếu khám bệnh
        break;
    case 'lookup_patient_prescription_form':
        $medicalRecordController->lookupPatientPrescriptionForm(); // Tra cứu đơn thuốc
        break;
    case 'lookup_patient_xray_images':
        $medicalRecordController->lookupPatientXrayImages(); // Tra cứu hình ảnh X-Quang
        break;
    case 'lookup_patient_ultrasound_images':
        $medicalRecordController->lookupPatientUltrasoundImages(); // Tra cứu hình ảnh Siêu âm
        break;

    // ===== DEFAULT ROUTE =====
    default:
        // Mọi action không tồn tại đều trả về trang 404, không tự động redirect về dashboard
        http_response_code(404);
        include 'Views/not_found.php';
        break;
}
