<?php

/**
 * Main entry point for the clinic management system
 * Handles routing based on action parameter
 */

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get action from URL (via .htaccess rewrite or query string)
$action = $_GET['action'] ?? '';

// If no action, redirect to home
if (empty($action)) {
    $action = 'home';
}

// Require necessary files
require_once 'Controllers/AuthController.php';
require_once 'Controllers/PatientController.php';
require_once 'Controllers/DoctorController.php';
require_once 'Controllers/AdminController.php';
require_once 'Controllers/ReceptionController.php';
require_once 'Controllers/AppointmentController.php';
require_once 'Controllers/MedicalRecordController.php';
require_once 'Controllers/PrescriptionController.php';
require_once 'Controllers/ReceiptController.php';
require_once 'Controllers/PatientReceiptController.php';
require_once 'Controllers/NotificationController.php';
require_once 'Controllers/SMSController.php';

// Route to appropriate controller method
try {
    switch ($action) {
        // ========== AUTH ROUTES ==========
        case 'login':
        case 'login_patient':
            $controller = new AuthController();
            $controller->loginPatient();
            break;

        case 'login_admin':
            $controller = new AuthController();
            $controller->loginAdmin();
            break;

        case 'login_doctor':
            $controller = new AuthController();
            $controller->loginDoctor();
            break;

        case 'login_reception':
            $controller = new AuthController();
            $controller->loginReception();
            break;

        case 'login_xquang':
            $controller = new AuthController();
            $controller->loginXrayDoctor();
            break;

        case 'login_sieuam':
            $controller = new AuthController();
            $controller->loginSieuAm();
            break;

        case 'login_xetnghiem':
            $controller = new AuthController();
            $controller->loginXetnghiem();
            break;

        case 'register':
        case 'signup':
            $controller = new AuthController();
            $controller->register();
            break;

        case 'logout':
            $controller = new AuthController();
            $controller->logout();
            break;

        case 'change_password':
            $controller = new AuthController();
            $controller->changePassword();
            break;

        // ========== HOME ROUTES ==========
        case 'home':
        case '':
            // Include home view directly
            include 'Views/home.php';
            exit();
            break;

        // ========== PATIENT ROUTES ==========
        case 'patient_dashboard':
            $auth = new AuthController();
            $auth->requireAuth('patient');
            include 'Views/patient/dashboard.php';
            exit();
            break;

        case 'patient_profile':
            $auth = new AuthController();
            $auth->requireAuth('patient');
            include 'Views/patient/profile.php';
            exit();
            break;

        case 'patient_update_profile':
            $controller = new PatientController();
            $controller->updateProfile();
            break;

        case 'patient_appointments':
            $auth = new AuthController();
            $auth->requireAuth('patient');
            include 'Views/patient/appointments.php';
            exit();
            break;

        case 'patient_medical_records':
            $controller = new MedicalRecordController();
            $controller->patientIndex();
            break;

        case 'patient_receipts':
            $auth = new AuthController();
            $auth->requireAuth('patient');
            include 'Views/patient/receipts.php';
            exit();
            break;

        // ========== DOCTOR ROUTES ==========
        case 'doctor_dashboard':
            $controller = new DoctorController();
            $controller->dashboard();
            break;

        case 'doctor_schedule':
        case 'doctor_manage_schedule':
            $controller = new DoctorController();
            $controller->manageSchedule();
            break;

        case 'doctor_today_appointments':
            $controller = new DoctorController();
            $controller->todayAppointments();
            break;

        case 'doctor_appointment_management':
            $controller = new DoctorController();
            $controller->appointmentManagement();
            break;

        case 'doctor_examination':
            $controller = new DoctorController();
            $controller->examination();
            break;

        case 'doctor_attendance':
            $controller = new DoctorController();
            $controller->attendance();
            break;

        case 'doctor_process_attendance':
            $controller = new DoctorController();
            $controller->processAttendance();
            break;

        case 'doctor_get_today_attendance':
            $controller = new DoctorController();
            $controller->getTodayAttendance();
            break;

        // Doctor API routes
        case 'doctor_start_examination':
            $controller = new DoctorController();
            $controller->startExamination();
            break;

        case 'doctor_save_examination':
            $controller = new DoctorController();
            $controller->saveExaminationForm();
            break;

        case 'doctor_get_examination':
            $controller = new DoctorController();
            $controller->getExaminationForm();
            break;

        case 'doctor_print_examination':
            $controller = new DoctorController();
            $controller->printExaminationForm();
            break;

        case 'doctor_complete_examination':
            $controller = new DoctorController();
            $controller->completeExamination();
            break;

        case 'doctor_check_examination_completion':
            $controller = new DoctorController();
            $controller->checkExaminationCompletion();
            break;

        // ========== ADMIN ROUTES ==========
        case 'admin_dashboard':
            $controller = new AdminController();
            $controller->dashboard();
            break;

        case 'admin_doctors_list':
            $controller = new AdminController();
            $controller->doctorsList();
            break;

        case 'admin_patients_list':
            $controller = new AdminController();
            $controller->patientsList();
            break;

        case 'admin_reception_list':
            $controller = new AdminController();
            $controller->receptionList();
            break;

        case 'admin_manage_doctor_schedules':
            $controller = new AdminController();
            $controller->manageDoctorSchedules();
            break;

        case 'admin_appointments':
            $controller = new AdminController();
            $controller->appointments();
            break;

        case 'admin_face_registration':
            $controller = new AdminController();
            $controller->faceRegistration();
            break;

        case 'admin_save_face_encoding':
            $controller = new AdminController();
            $controller->saveFaceEncoding();
            break;

        case 'admin_get_users':
            $controller = new AdminController();
            $controller->getUsers();
            break;

        case 'admin_get_user_info':
            $controller = new AdminController();
            $controller->getUserInfo();
            break;

        // ========== RECEPTION ROUTES ==========
        case 'reception_dashboard':
            $controller = new ReceptionController();
            $controller->dashboard();
            break;

        case 'reception_doctor_schedules':
            $controller = new ReceptionController();
            $controller->doctorSchedules();
            break;

        case 'reception_schedule_management':
            $controller = new ReceptionController();
            $controller->scheduleManagement();
            break;

        case 'reception_attendance':
            $controller = new ReceptionController();
            $controller->attendance();
            break;

        case 'reception_process_attendance':
            $controller = new ReceptionController();
            $controller->processAttendance();
            break;

        case 'reception_get_today_attendance':
            $controller = new ReceptionController();
            $controller->getTodayAttendance();
            break;

        // ========== APPOINTMENT ROUTES ==========
        case 'hospital_appointment':
            $controller = new AppointmentController();
            $controller->hospitalAppointment();
            break;

        case 'book_appointment':
            $controller = new AppointmentController();
            $controller->bookAppointment();
            break;

        // ========== API ROUTES (Doctor) ==========
        case 'api_doctor_update_appointment_status':
            $controller = new DoctorController();
            $controller->updateAppointmentStatus();
            break;

        case 'api_doctor_add_schedule':
            $controller = new DoctorController();
            $controller->addSchedule();
            break;

        case 'api_doctor_update_schedule':
            $controller = new DoctorController();
            $controller->updateSchedule();
            break;

        case 'api_doctor_delete_schedule':
            $controller = new DoctorController();
            $controller->deleteSchedule();
            break;

        case 'api_doctor_get_schedule_info':
            $controller = new DoctorController();
            $controller->getScheduleInfo();
            break;

        case 'api_doctor_get_schedules_by_day':
            $controller = new DoctorController();
            $controller->getSchedulesByDay();
            break;

        case 'api_doctor_modify_schedule_for_date':
            $controller = new DoctorController();
            $controller->modifyScheduleForDate();
            break;

        case 'api_doctor_cancel_schedule_for_date':
            $controller = new DoctorController();
            $controller->cancelScheduleForDate();
            break;

        // ========== DEFAULT / 404 ==========
        default:
            // Try to find a matching controller method
            // If not found, show 404 or redirect to home
            http_response_code(404);
            echo '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Trang không tìm thấy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        .error-container {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 72px;
            color: #dc3545;
            margin: 0;
        }
        p {
            font-size: 18px;
            color: #666;
            margin: 20px 0;
        }
        a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404</h1>
        <p>Trang không tìm thấy</p>
        <p><a href="./home">Quay về trang chủ</a></p>
    </div>
</body>
</html>';
            exit();
    }
} catch (Exception $e) {
    // Log error
    error_log("Routing error: " . $e->getMessage());

    // Show error page
    http_response_code(500);
    echo '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lỗi hệ thống</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        .error-container {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 48px;
            color: #dc3545;
            margin: 0;
        }
        p {
            font-size: 18px;
            color: #666;
            margin: 20px 0;
        }
        a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Lỗi hệ thống</h1>
        <p>Đã xảy ra lỗi khi xử lý yêu cầu của bạn.</p>
        <p><a href="./home">Quay về trang chủ</a></p>
    </div>
</body>
</html>';
    exit();
}