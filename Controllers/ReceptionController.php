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
        date_default_timezone_set('Asia/Ho_Chi_Minh');
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

        // Emit realtime for doctor appointment list
        try {
            // Lấy tên bệnh nhân phục vụ thông báo
            $patientName = '';
            if (class_exists('Patient')) {
                $p = $this->patientModel->getById($patientId);
                if ($p && !empty($p['ten'])) $patientName = $p['ten'];
            }
            require_once 'Services/SocketService.php';
            SocketService::emit('new_appointment', [
                'doctorId' => $doctorId,
                'patientName' => $patientName,
                'appointmentDate' => $today,
                // For walk-in (Trực tiếp), show current time in notification
                'appointmentTime' => date('H:i'),
                'appointmentId' => (int)$appointmentId,
            ]);
            // generic update broadcast (fallback)
            SocketService::emit('appointment_update', [
                'doctorId' => $doctorId,
                'appointmentId' => (int)$appointmentId,
                'patientName' => $patientName,
                'message' => 'Bạn vừa có một lịch mới: ' . ($patientName ?: 'Bệnh nhân'),
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

        try {
            $pdo = $this->doctorModel->getConnection();
            $sql = "SELECT t.*, bn.ten AS ten_benh_nhan, bs.ten AS ten_bac_si, lh.gio_hen AS thoi_gian_du_kien
                    FROM phieu_boc_so t
                    JOIN benh_nhan bn ON bn.id = t.benh_nhan_id
                    JOIN bac_si bs ON bs.id = t.bac_si_id
                    LEFT JOIN lich_hen lh ON lh.id = t.lich_hen_id
                    WHERE t.ngay = :d";
            $params = [':d' => $date];
            if ($doctorId > 0) {
                $sql .= " AND t.bac_si_id = :bs";
                $params[':bs'] = $doctorId;
            }
            if (!empty($status)) {
                $sql .= " AND t.trang_thai = :st";
                $params[':st'] = $status;
            } else {
                // Mặc định ẩn các phiếu đã bắt đầu khám khỏi giao diện lễ tân
                $sql .= " AND t.trang_thai <> 'dang_kham'";
            }
            $sql .= " ORDER BY t.uu_tien DESC, t.so_thu_tu ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            echo json_encode(['success' => true, 'data' => $rows]);
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

            // Cập nhật trạng thái thanh toán
            $result = $this->bienLaiModel->updatePaymentStatus(
                $receiptId,
                $paymentStatus,
                $paymentMethod,
                $paymentNote
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
                    // Update receipt status
                    $this->bienLaiModel->updatePaymentStatus(
                        $receiptId,
                        'Đã thanh toán chuyển khoản',
                        'Thanh toán VNPAY',
                        'VNPAY Transaction: ' . $result['transaction_id']
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
}