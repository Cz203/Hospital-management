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
    case 'save_examination_form':
        $doctorController->saveExaminationForm();
        break;
    case 'get_examination_form':
        $doctorController->getExaminationForm();
        break;
    case 'print_examination_form':
        $doctorController->printExaminationForm();
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

    case 'get_ultrasound_stats':
        $doctorController->getUltrasoundStats(); // Lấy thống kê siêu âm
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

    case 'contact':
        include 'Views/contact.php'; // Trang liên hệ
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
