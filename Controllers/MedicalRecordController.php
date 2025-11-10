<?php
require_once 'Controllers/AuthController.php';
require_once 'Models/Doctor.php';
require_once 'Models/MedicalRecord.php';

class MedicalRecordController
{
    private $auth;
    private $doctorModel;
    private $medicalRecordModel;

    public function __construct()
    {
        $this->auth = new AuthController();
        $this->doctorModel = new Doctor();
        $this->medicalRecordModel = new MedicalRecord();
    }

    /**
     * Trang hồ sơ bệnh án - tra cứu danh sách bệnh án của các bệnh nhân mà bác sĩ phụ trách
     */
    public function index()
    {
        $this->auth->requireAuth('doctor');

        try {
            $doctorId = $_SESSION['user_id'];

            // Lấy thông tin bác sĩ
            $doctor = $this->doctorModel->getById($doctorId);
            if (!$doctor) {
                $_SESSION['error'] = 'Không tìm thấy thông tin bác sĩ!';
                header('Location: ./doctor_dashboard');
                exit();
            }

            // Include view
            include 'Views/doctor/medical_records.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController index error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tải trang hồ sơ bệnh án!';
            header('Location: ./doctor_dashboard');
            exit();
        }
    }

    /**
     * API lấy danh sách hồ sơ bệnh án với filter
     */
    public function getRecords()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->auth->requireAuth('doctor');

        try {
            $doctorId = $_SESSION['user_id'];
            $input = json_decode(file_get_contents('php://input'), true) ?: [];

            error_log("MedicalRecordController getRecords - Doctor ID: " . $doctorId);
            error_log("MedicalRecordController getRecords - Filters: " . json_encode($input));

            // Lấy dữ liệu từ Model
            $result = $this->medicalRecordModel->getRecordsByDoctor($doctorId, $input);

            error_log("MedicalRecordController getRecords - Result total: " . $result['total']);
            error_log("MedicalRecordController getRecords - Result data count: " . count($result['data']));

            echo json_encode([
                'success' => true,
                'data' => $result['data'],
                'total' => $result['total'],
                'page' => $result['page'],
                'limit' => $result['limit'],
                'total_pages' => $result['total_pages']
            ]);
        } catch (Exception $e) {
            error_log("MedicalRecordController getRecords error: " . $e->getMessage());
            error_log("MedicalRecordController getRecords stack trace: " . $e->getTraceAsString());
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * API lấy chi tiết hồ sơ bệnh án (bao gồm phiếu khám, đơn thuốc, xét nghiệm, siêu âm, X-Quang)
     * Trả về JSON (giữ lại để tương thích)
     */
    public function getDetail()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->auth->requireAuth('doctor');

        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            if ($lichHenId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Thiếu lich_hen_id']);
                exit();
            }

            $doctorId = $_SESSION['user_id'];

            // Lấy dữ liệu từ Model
            $data = $this->medicalRecordModel->getRecordDetail($lichHenId, $doctorId);

            if (!$data) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy lịch hẹn hoặc lịch hẹn chưa hoàn thành']);
                exit();
            }

            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            error_log("MedicalRecordController getDetail error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * Render view chi tiết hồ sơ bệnh án (PHP template)
     * Template này chỉ cần làm 1 lần, mỗi lần gọi chỉ cần truyền data khác vào
     */
    public function renderDetail()
    {
        $this->auth->requireAuth('doctor');

        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            if ($lichHenId <= 0) {
                echo '<div class="alert alert-danger">Thiếu lich_hen_id</div>';
                exit();
            }

            $doctorId = $_SESSION['user_id'];

            // Lấy dữ liệu từ Model
            $data = $this->medicalRecordModel->getRecordDetail($lichHenId, $doctorId);

            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy lịch hẹn hoặc lịch hẹn chưa hoàn thành</div>';
                exit();
            }

            // Truyền data vào view
            // Include view - view sẽ tự động có biến $data
            include 'Views/doctor/medical_record_detail.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController renderDetail error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Trang hồ sơ bệnh án cho bệnh nhân
     */
    public function patientIndex()
    {
        $this->auth->requireAuth('patient');

        try {
            $patientId = $_SESSION['user_id'];

            // Include view
            include 'Views/patient/medical_records.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController patientIndex error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tải trang hồ sơ bệnh án!';
            header('Location: ./patient_dashboard');
            exit();
        }
    }

    /**
     * API lấy danh sách hồ sơ bệnh án của bệnh nhân với filter
     */
    public function getPatientRecords()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->auth->requireAuth('patient');

        try {
            $patientId = $_SESSION['user_id'];
            $input = json_decode(file_get_contents('php://input'), true) ?: [];

            // Lấy dữ liệu từ Model
            $result = $this->medicalRecordModel->getRecordsByPatient($patientId, $input);

            echo json_encode([
                'success' => true,
                'data' => $result['data'],
                'total' => $result['total'],
                'page' => $result['page'],
                'limit' => $result['limit'],
                'total_pages' => $result['total_pages']
            ]);
        } catch (Exception $e) {
            error_log("MedicalRecordController getPatientRecords error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * Render view chi tiết hồ sơ bệnh án cho bệnh nhân (PHP template)
     */
    public function renderPatientDetail()
    {
        $this->auth->requireAuth('patient');

        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            if ($lichHenId <= 0) {
                echo '<div class="alert alert-danger">Thiếu lich_hen_id</div>';
                exit();
            }

            $patientId = $_SESSION['user_id'];

            // Lấy dữ liệu từ Model
            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);

            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy lịch hẹn hoặc lịch hẹn chưa hoàn thành</div>';
                exit();
            }

            // Truyền data vào view - dùng lại view của doctor (có thể tạo riêng sau nếu cần)
            include 'Views/doctor/medical_record_detail.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController renderPatientDetail error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }
}
