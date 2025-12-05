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

            // Truyền data vào view cho patient
            include 'Views/patient/medical_record_detail.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController renderPatientDetail error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Hiển thị phiếu chỉ định xét nghiệm cho patient (PDF-like view - chỉ phiếu yêu cầu)
     */
    public function viewPatientLabRequest()
    {
        $this->auth->requireAuth('patient');

        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            if ($lichHenId <= 0) {
                echo '<div class="alert alert-danger">Thiếu lich_hen_id</div>';
                exit();
            }

            $patientId = $_SESSION['user_id'];
            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);

            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy lịch hẹn hoặc lịch hẹn chưa hoàn thành</div>';
                exit();
            }

            include 'Views/patient/view_lab_request.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController viewPatientLabRequest error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Hiển thị kết quả xét nghiệm cho patient (PDF-like view - chỉ kết quả)
     */
    public function viewPatientLabResult()
    {
        $this->auth->requireAuth('patient');

        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            if ($lichHenId <= 0) {
                echo '<div class="alert alert-danger">Thiếu lich_hen_id</div>';
                exit();
            }

            $patientId = $_SESSION['user_id'];
            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);

            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy lịch hẹn hoặc lịch hẹn chưa hoàn thành</div>';
                exit();
            }

            include 'Views/patient/view_lab_result.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController viewPatientLabResult error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Hiển thị phiếu chỉ định siêu âm cho patient (PDF-like view - chỉ phiếu yêu cầu)
     */
    public function viewPatientUltrasoundRequest()
    {
        $this->auth->requireAuth('patient');

        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            if ($lichHenId <= 0) {
                echo '<div class="alert alert-danger">Thiếu lich_hen_id</div>';
                exit();
            }

            $patientId = $_SESSION['user_id'];
            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);

            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy lịch hẹn hoặc lịch hẹn chưa hoàn thành</div>';
                exit();
            }

            include 'Views/patient/view_ultrasound_request.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController viewPatientUltrasoundRequest error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Hiển thị kết quả siêu âm cho patient (PDF-like view - chỉ kết quả)
     */
    public function viewPatientUltrasoundResult()
    {
        $this->auth->requireAuth('patient');

        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            if ($lichHenId <= 0) {
                echo '<div class="alert alert-danger">Thiếu lich_hen_id</div>';
                exit();
            }

            $patientId = $_SESSION['user_id'];
            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);

            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy lịch hẹn hoặc lịch hẹn chưa hoàn thành</div>';
                exit();
            }

            include 'Views/patient/view_ultrasound_result.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController viewPatientUltrasoundResult error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Hiển thị phiếu chỉ định X-Quang cho patient (PDF-like view - chỉ phiếu yêu cầu)
     */
    public function viewPatientXrayRequest()
    {
        $this->auth->requireAuth('patient');

        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            if ($lichHenId <= 0) {
                echo '<div class="alert alert-danger">Thiếu lich_hen_id</div>';
                exit();
            }

            $patientId = $_SESSION['user_id'];
            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);

            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy lịch hẹn hoặc lịch hẹn chưa hoàn thành</div>';
                exit();
            }

            include 'Views/patient/view_xray_request.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController viewPatientXrayRequest error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Hiển thị kết quả X-Quang cho patient (PDF-like view - chỉ kết quả)
     */
    public function viewPatientXrayResult()
    {
        $this->auth->requireAuth('patient');

        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            if ($lichHenId <= 0) {
                echo '<div class="alert alert-danger">Thiếu lich_hen_id</div>';
                exit();
            }

            $patientId = $_SESSION['user_id'];
            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);

            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy lịch hẹn hoặc lịch hẹn chưa hoàn thành</div>';
                exit();
            }

            include 'Views/patient/view_xray_result.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController viewPatientXrayResult error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Hiển thị phiếu khám bệnh cho patient (PDF-like view)
     */
    public function viewPatientExaminationForm()
    {
        $this->auth->requireAuth('patient');

        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            if ($lichHenId <= 0) {
                echo '<div class="alert alert-danger">Thiếu lich_hen_id</div>';
                exit();
            }

            $patientId = $_SESSION['user_id'];
            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);

            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy lịch hẹn hoặc lịch hẹn chưa hoàn thành</div>';
                exit();
            }

            include 'Views/patient/view_examination_form.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController viewPatientExaminationForm error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Hiển thị đơn thuốc cho patient (PDF-like view)
     */
    public function viewPatientPrescriptionForm()
    {
        $this->auth->requireAuth('patient');

        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            if ($lichHenId <= 0) {
                echo '<div class="alert alert-danger">Thiếu lich_hen_id</div>';
                exit();
            }

            $patientId = $_SESSION['user_id'];
            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);

            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy lịch hẹn hoặc lịch hẹn chưa hoàn thành</div>';
                exit();
            }

            include 'Views/patient/view_prescription_form.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController viewPatientPrescriptionForm error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Hiển thị hình ảnh X-Quang cho patient
     */
    public function viewPatientXrayImages()
    {
        $this->auth->requireAuth('patient');

        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            if ($lichHenId <= 0) {
                echo '<div class="alert alert-danger">Thiếu lich_hen_id</div>';
                exit();
            }

            $patientId = $_SESSION['user_id'];
            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);

            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy lịch hẹn hoặc lịch hẹn chưa hoàn thành</div>';
                exit();
            }

            // Lấy hình ảnh từ xrayResult
            $xrayImages = [];
            if (!empty($data['xray_result'])) {
                // Kiểm tra xem hinh_anh đã được gán chưa
                if (!empty($data['xray_result']['hinh_anh']) && is_array($data['xray_result']['hinh_anh'])) {
                    $xrayImages = $data['xray_result']['hinh_anh'];
                } else {
                    // Nếu chưa có, lấy trực tiếp từ database
                    $xrayRequest = $data['xray_request'] ?? null;
                    if ($xrayRequest) {
                        require_once 'config/database.php';
                        $database = new Database();
                        $db = $database->getConnection();
                        
                        // Lấy kết quả X-Quang mới nhất
                        $xrayResultSql = "SELECT * FROM ket_qua_xquang WHERE id_phieu_chup_xquang = :xray_id ORDER BY ngay_cap_nhat DESC LIMIT 1";
                        $xrayResultStmt = $db->prepare($xrayResultSql);
                        $xrayResultStmt->execute([':xray_id' => $xrayRequest['id']]);
                        $xrayResult = $xrayResultStmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($xrayResult) {
                            // Lấy hình ảnh
                            $xrayImageSql = "SELECT * FROM ket_qua_xquang_hinh_anh WHERE ket_qua_id = :ket_qua_id ORDER BY id";
                            $xrayImageStmt = $db->prepare($xrayImageSql);
                            $xrayImageStmt->execute([':ket_qua_id' => $xrayResult['id']]);
                            $xrayImages = $xrayImageStmt->fetchAll(PDO::FETCH_ASSOC);
                        }
                    }
                }
            }

            $data['xray_images'] = $xrayImages;

            include 'Views/patient/view_xray_images.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController viewPatientXrayImages error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Hiển thị hình ảnh Siêu âm cho patient
     */
    public function viewPatientUltrasoundImages()
    {
        $this->auth->requireAuth('patient');

        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            if ($lichHenId <= 0) {
                echo '<div class="alert alert-danger">Thiếu lich_hen_id</div>';
                exit();
            }

            $patientId = $_SESSION['user_id'];
            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);

            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy lịch hẹn hoặc lịch hẹn chưa hoàn thành</div>';
                exit();
            }

            // Lấy hình ảnh từ ultrasoundResult
            $ultrasoundImages = [];
            if (!empty($data['ultrasound_result']) && !empty($data['ultrasound_result']['hinh_anh'])) {
                $ultrasoundImages = $data['ultrasound_result']['hinh_anh'];
            }

            $data['ultrasound_images'] = $ultrasoundImages;

            include 'Views/patient/view_ultrasound_images.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController viewPatientUltrasoundImages error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Tra cứu hồ sơ bệnh án (không cần đăng nhập)
     * Cho phép tra cứu bằng mã bệnh nhân hoặc số điện thoại
     */
    public function lookupMedicalRecord()
    {
        try {
            // Nếu đã đăng nhập với role patient, redirect về trang hồ sơ của họ
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'patient') {
                header('Location: ./patient_medical_records');
                exit();
            }

            // Xử lý yêu cầu xóa session (tra cứu mới)
            if (isset($_GET['clear']) && $_GET['clear'] == '1') {
                unset($_SESSION['lookup_ho_ten']);
                unset($_SESSION['lookup_so_dien_thoai']);
                unset($_SESSION['lookup_cccd']);
                header('Location: ./lookup_medical_record');
                exit();
            }

            // Xử lý POST request - lưu thông tin vào session và redirect
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ho_ten']) && !empty($_POST['so_dien_thoai']) && !empty($_POST['cccd'])) {
                // Lưu thông tin tra cứu vào session (không lưu trong URL)
                $_SESSION['lookup_ho_ten'] = trim($_POST['ho_ten']);
                $_SESSION['lookup_so_dien_thoai'] = trim($_POST['so_dien_thoai']);
                $_SESSION['lookup_cccd'] = trim($_POST['cccd']);
                $_SESSION['lookup_from_post'] = true; // Flag để biết đây là request từ POST redirect
                
                // Redirect về trang kết quả (không có thông tin nhạy cảm trong URL)
                header('Location: ./lookup_medical_record');
                exit();
            }

            // Nếu là GET request và không phải từ POST redirect (F5 hoặc truy cập trực tiếp), xóa session
            if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_SESSION['lookup_from_post'])) {
                unset($_SESSION['lookup_ho_ten']);
                unset($_SESSION['lookup_so_dien_thoai']);
                unset($_SESSION['lookup_cccd']);
            }

            // Xóa flag sau khi đã xử lý
            unset($_SESSION['lookup_from_post']);

            // Lấy thông tin từ session (sau khi redirect) hoặc từ GET (cho filter ngày)
            $hoTen = isset($_SESSION['lookup_ho_ten']) ? trim($_SESSION['lookup_ho_ten']) : '';
            $soDienThoai = isset($_SESSION['lookup_so_dien_thoai']) ? trim($_SESSION['lookup_so_dien_thoai']) : '';
            $cccd = isset($_SESSION['lookup_cccd']) ? trim($_SESSION['lookup_cccd']) : '';
            $selectedDate = trim($_GET['selected_date'] ?? ''); // Filter ngày vẫn dùng GET vì không nhạy cảm
            
            $patient = null;
            $records = [];

            // Debug log để kiểm tra
            error_log("Lookup - hoTen: " . ($hoTen ?: 'empty') . ", soDienThoai: " . ($soDienThoai ?: 'empty') . ", cccd: " . ($cccd ?: 'empty'));

            if (!empty($hoTen) && !empty($soDienThoai) && !empty($cccd)) {
                // Tìm bệnh nhân theo Họ tên, Số điện thoại và CCCD
                require_once 'Models/Patient.php';
                $patientModel = new Patient();
                $patient = $patientModel->findByLookupInfo($hoTen, $soDienThoai, $cccd);

                if ($patient) {
                    // Lấy danh sách lịch hẹn đã hoàn thành của bệnh nhân này (có filter theo ngày nếu có)
                    $records = $this->medicalRecordModel->getRecordsByPatientId($patient['id'], $selectedDate ?: null);
                    
                    // Set thông báo thành công
                    if (!empty($records)) {
                        $_SESSION['lookup_success'] = 'Tra cứu thành công! Tìm thấy ' . count($records) . ' hồ sơ khám bệnh.';
                    } else {
                        $_SESSION['lookup_success'] = 'Tra cứu thành công! Tuy nhiên, bệnh nhân này chưa có hồ sơ khám bệnh nào.';
                    }
                }
            }

            // Include view
            include 'Views/lookup_medical_record.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController lookupMedicalRecord error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tra cứu hồ sơ!';
            include 'Views/lookup_medical_record.php';
        }
        exit();
    }

    /**
     * Xem chi tiết hồ sơ từ tra cứu (không cần đăng nhập)
     * Yêu cầu exam_id và patient_id để đảm bảo bảo mật
     */
    public function lookupMedicalRecordDetail()
    {
        try {
            $lichHenId = (int)($_GET['exam_id'] ?? 0);
            $patientId = (int)($_GET['patient_id'] ?? 0);

            if ($lichHenId <= 0 || $patientId <= 0) {
                echo '<div class="alert alert-danger">Thiếu thông tin cần thiết</div>';
                exit();
            }

            // Kiểm tra lịch hẹn thuộc về bệnh nhân này và đã hoàn thành
            require_once 'config/database.php';
            $database = new Database();
            $db = $database->getConnection();
            
            $checkStmt = $db->prepare("SELECT id FROM lich_hen WHERE id = :lich_hen_id AND benh_nhan_id = :patient_id AND trang_thai = 'Hoàn thành'");
            $checkStmt->bindParam(':lich_hen_id', $lichHenId, PDO::PARAM_INT);
            $checkStmt->bindParam(':patient_id', $patientId, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                echo '<div class="alert alert-danger">Không tìm thấy hồ sơ hoặc bạn không có quyền xem hồ sơ này</div>';
                exit();
            }

            // Lấy dữ liệu chi tiết
            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);

            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy chi tiết hồ sơ</div>';
                exit();
            }

            // Include view chi tiết (sử dụng view của patient)
            include 'Views/patient/medical_record_detail.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController lookupMedicalRecordDetail error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Helper method để kiểm tra quyền truy cập khi tra cứu không đăng nhập
     * Kiểm tra lịch hẹn thuộc về bệnh nhân và đã hoàn thành
     */
    private function verifyLookupAccess($lichHenId, $patientId)
    {
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $checkStmt = $db->prepare("SELECT id FROM lich_hen WHERE id = :lich_hen_id AND benh_nhan_id = :patient_id AND trang_thai = 'Hoàn thành'");
        $checkStmt->bindParam(':lich_hen_id', $lichHenId, PDO::PARAM_INT);
        $checkStmt->bindParam(':patient_id', $patientId, PDO::PARAM_INT);
        $checkStmt->execute();
        
        return $checkStmt->rowCount() > 0;
    }

    /**
     * Tra cứu phiếu chỉ định xét nghiệm (không cần đăng nhập)
     */
    public function lookupPatientLabRequest()
    {
        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            $patientId = (int)($_GET['patient_id'] ?? 0);

            if ($lichHenId <= 0 || $patientId <= 0) {
                echo '<div class="alert alert-danger">Thiếu thông tin cần thiết</div>';
                exit();
            }

            if (!$this->verifyLookupAccess($lichHenId, $patientId)) {
                echo '<div class="alert alert-danger">Không tìm thấy hồ sơ hoặc bạn không có quyền xem hồ sơ này</div>';
                exit();
            }

            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);
            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy chi tiết hồ sơ</div>';
                exit();
            }

            include 'Views/patient/view_lab_request.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController lookupPatientLabRequest error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Tra cứu kết quả xét nghiệm (không cần đăng nhập)
     */
    public function lookupPatientLabResult()
    {
        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            $patientId = (int)($_GET['patient_id'] ?? 0);

            if ($lichHenId <= 0 || $patientId <= 0) {
                echo '<div class="alert alert-danger">Thiếu thông tin cần thiết</div>';
                exit();
            }

            if (!$this->verifyLookupAccess($lichHenId, $patientId)) {
                echo '<div class="alert alert-danger">Không tìm thấy hồ sơ hoặc bạn không có quyền xem hồ sơ này</div>';
                exit();
            }

            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);
            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy chi tiết hồ sơ</div>';
                exit();
            }

            include 'Views/patient/view_lab_result.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController lookupPatientLabResult error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Tra cứu phiếu chỉ định siêu âm (không cần đăng nhập)
     */
    public function lookupPatientUltrasoundRequest()
    {
        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            $patientId = (int)($_GET['patient_id'] ?? 0);

            if ($lichHenId <= 0 || $patientId <= 0) {
                echo '<div class="alert alert-danger">Thiếu thông tin cần thiết</div>';
                exit();
            }

            if (!$this->verifyLookupAccess($lichHenId, $patientId)) {
                echo '<div class="alert alert-danger">Không tìm thấy hồ sơ hoặc bạn không có quyền xem hồ sơ này</div>';
                exit();
            }

            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);
            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy chi tiết hồ sơ</div>';
                exit();
            }

            include 'Views/patient/view_ultrasound_request.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController lookupPatientUltrasoundRequest error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Tra cứu kết quả siêu âm (không cần đăng nhập)
     */
    public function lookupPatientUltrasoundResult()
    {
        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            $patientId = (int)($_GET['patient_id'] ?? 0);

            if ($lichHenId <= 0 || $patientId <= 0) {
                echo '<div class="alert alert-danger">Thiếu thông tin cần thiết</div>';
                exit();
            }

            if (!$this->verifyLookupAccess($lichHenId, $patientId)) {
                echo '<div class="alert alert-danger">Không tìm thấy hồ sơ hoặc bạn không có quyền xem hồ sơ này</div>';
                exit();
            }

            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);
            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy chi tiết hồ sơ</div>';
                exit();
            }

            include 'Views/patient/view_ultrasound_result.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController lookupPatientUltrasoundResult error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Tra cứu phiếu chỉ định X-Quang (không cần đăng nhập)
     */
    public function lookupPatientXrayRequest()
    {
        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            $patientId = (int)($_GET['patient_id'] ?? 0);

            if ($lichHenId <= 0 || $patientId <= 0) {
                echo '<div class="alert alert-danger">Thiếu thông tin cần thiết</div>';
                exit();
            }

            if (!$this->verifyLookupAccess($lichHenId, $patientId)) {
                echo '<div class="alert alert-danger">Không tìm thấy hồ sơ hoặc bạn không có quyền xem hồ sơ này</div>';
                exit();
            }

            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);
            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy chi tiết hồ sơ</div>';
                exit();
            }

            include 'Views/patient/view_xray_request.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController lookupPatientXrayRequest error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Tra cứu kết quả X-Quang (không cần đăng nhập)
     */
    public function lookupPatientXrayResult()
    {
        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            $patientId = (int)($_GET['patient_id'] ?? 0);

            if ($lichHenId <= 0 || $patientId <= 0) {
                echo '<div class="alert alert-danger">Thiếu thông tin cần thiết</div>';
                exit();
            }

            if (!$this->verifyLookupAccess($lichHenId, $patientId)) {
                echo '<div class="alert alert-danger">Không tìm thấy hồ sơ hoặc bạn không có quyền xem hồ sơ này</div>';
                exit();
            }

            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);
            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy chi tiết hồ sơ</div>';
                exit();
            }

            include 'Views/patient/view_xray_result.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController lookupPatientXrayResult error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Tra cứu phiếu khám bệnh (không cần đăng nhập)
     */
    public function lookupPatientExaminationForm()
    {
        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            $patientId = (int)($_GET['patient_id'] ?? 0);

            if ($lichHenId <= 0 || $patientId <= 0) {
                echo '<div class="alert alert-danger">Thiếu thông tin cần thiết</div>';
                exit();
            }

            if (!$this->verifyLookupAccess($lichHenId, $patientId)) {
                echo '<div class="alert alert-danger">Không tìm thấy hồ sơ hoặc bạn không có quyền xem hồ sơ này</div>';
                exit();
            }

            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);
            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy chi tiết hồ sơ</div>';
                exit();
            }

            include 'Views/patient/view_examination_form.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController lookupPatientExaminationForm error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Tra cứu đơn thuốc (không cần đăng nhập)
     */
    public function lookupPatientPrescriptionForm()
    {
        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            $patientId = (int)($_GET['patient_id'] ?? 0);

            if ($lichHenId <= 0 || $patientId <= 0) {
                echo '<div class="alert alert-danger">Thiếu thông tin cần thiết</div>';
                exit();
            }

            if (!$this->verifyLookupAccess($lichHenId, $patientId)) {
                echo '<div class="alert alert-danger">Không tìm thấy hồ sơ hoặc bạn không có quyền xem hồ sơ này</div>';
                exit();
            }

            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);
            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy chi tiết hồ sơ</div>';
                exit();
            }

            include 'Views/patient/view_prescription_form.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController lookupPatientPrescriptionForm error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Tra cứu hình ảnh X-Quang (không cần đăng nhập)
     */
    public function lookupPatientXrayImages()
    {
        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            $patientId = (int)($_GET['patient_id'] ?? 0);

            if ($lichHenId <= 0 || $patientId <= 0) {
                echo '<div class="alert alert-danger">Thiếu thông tin cần thiết</div>';
                exit();
            }

            if (!$this->verifyLookupAccess($lichHenId, $patientId)) {
                echo '<div class="alert alert-danger">Không tìm thấy hồ sơ hoặc bạn không có quyền xem hồ sơ này</div>';
                exit();
            }

            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);
            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy chi tiết hồ sơ</div>';
                exit();
            }

            include 'Views/patient/view_xray_images.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController lookupPatientXrayImages error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }

    /**
     * Tra cứu hình ảnh Siêu âm (không cần đăng nhập)
     */
    public function lookupPatientUltrasoundImages()
    {
        try {
            $lichHenId = (int)($_GET['exam_id'] ?? $_GET['lich_hen_id'] ?? 0);
            $patientId = (int)($_GET['patient_id'] ?? 0);

            if ($lichHenId <= 0 || $patientId <= 0) {
                echo '<div class="alert alert-danger">Thiếu thông tin cần thiết</div>';
                exit();
            }

            if (!$this->verifyLookupAccess($lichHenId, $patientId)) {
                echo '<div class="alert alert-danger">Không tìm thấy hồ sơ hoặc bạn không có quyền xem hồ sơ này</div>';
                exit();
            }

            $data = $this->medicalRecordModel->getRecordDetailForPatient($lichHenId, $patientId);
            if (!$data) {
                echo '<div class="alert alert-danger">Không tìm thấy chi tiết hồ sơ</div>';
                exit();
            }

            include 'Views/patient/view_ultrasound_images.php';
        } catch (Exception $e) {
            error_log("MedicalRecordController lookupPatientUltrasoundImages error: " . $e->getMessage());
            echo '<div class="alert alert-danger">Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
    }
}
