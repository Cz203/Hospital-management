<?php
require_once 'Models/Doctor.php';
require_once 'Controllers/AuthController.php';

class DoctorController
{
    private $doctorModel;
    private $auth;

    public function __construct()
    {
        $this->doctorModel = new Doctor();
        $this->auth = new AuthController();
    }

    /**
     * Hiển thị trang quản lý lịch làm việc
     */
    public function manageSchedule()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ
        $this->auth->requireAuth('doctor');

        $doctorId = $_SESSION['user_id'];
        $schedules = $this->doctorModel->getSchedules($doctorId);
        $stats = $this->doctorModel->getScheduleStats($doctorId);

        // Nhóm lịch theo thứ trong tuần
        $schedulesByDay = [];
        $daysOfWeek = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];

        foreach ($daysOfWeek as $day) {
            $schedulesByDay[$day] = $this->doctorModel->getSchedulesByDay($doctorId, $day);
        }

        // Start output buffering để lấy content
        ob_start();
        include 'Views/doctor/schedule_management.php';
        $content = ob_get_clean();

        // Sử dụng renderLayout function
        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, 'Đăng ký lịch làm việc');
    }

    /**
     * Hiển thị trang quản lý lịch hẹn
     */
    public function appointmentManagement()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ
        $this->auth->requireAuth('doctor');

        $doctorId = $_SESSION['user_id'];

        // Lấy tất cả lịch hẹn của bác sĩ
        require_once 'Models/Appointment.php';
        $appointmentModel = new Appointment();
        $allAppointments = $appointmentModel->getByDoctorId($doctorId) ?: [];

        // Phân loại appointments theo trạng thái
        $pendingAppointments = [];
        $confirmedAppointments = [];
        $completedAppointments = [];
        $cancelledAppointments = [];

        foreach ($allAppointments as $appointment) {
            switch ($appointment['trang_thai']) {
                case 'Chờ xác nhận':
                    $pendingAppointments[] = $appointment;
                    break;
                case 'Đã xác nhận':
                    $confirmedAppointments[] = $appointment;
                    break;
                case 'Đang khám':
                    // handled separately below (examining)
                    break;
                case 'Hoàn thành':
                    $completedAppointments[] = $appointment;
                    break;
                case 'hủy':
                    $cancelledAppointments[] = $appointment;
                    break;
                default:
                    // ignore unknown statuses; do not mix into pending
                    break;
            }
        }

        // Lấy thống kê
        $stats = $appointmentModel->getStats($doctorId);

        // Nhóm thêm danh sách đang khám
        $examiningAppointments = array_values(array_filter($allAppointments, function ($a) {
            return isset($a['trang_thai']) && $a['trang_thai'] === 'Đang khám';
        }));

        // Start output buffering để lấy content
        ob_start();
        include 'Views/doctor/appointment_management.php';
        $content = ob_get_clean();

        // Sử dụng renderLayout function
        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, 'Quản lý lịch hẹn');
    }

    /**
     * Cập nhật trạng thái lịch hẹn
     */
    public function updateAppointmentStatus()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ
        $this->auth->requireAuth('doctor');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ./doctor_appointment_management");
            exit();
        }

        $appointmentId = $_POST['appointment_id'] ?? '';
        $status = $_POST['status'] ?? '';
        $note = $_POST['note'] ?? '';
        $doctorId = $_SESSION['user_id'];

        if (empty($appointmentId) || empty($status)) {
            $_SESSION['error'] = "Thiếu thông tin cần thiết!";
            header("Location: ./doctor_appointment_management");
            exit();
        }

        // Kiểm tra lịch hẹn có thuộc về bác sĩ này không
        require_once 'Models/Appointment.php';
        $appointmentModel = new Appointment();
        $appointment = $appointmentModel->getById($appointmentId);

        if (!$appointment || $appointment['bac_si_id'] != $doctorId) {
            $_SESSION['error'] = "Không tìm thấy lịch hẹn hoặc không có quyền!";
            header("Location: ./doctor_appointment_management");
            exit();
        }

        // Cập nhật trạng thái
        if ($appointmentModel->updateStatus($appointmentId, $status, $note)) {
            // Debug: Log appointment data
            error_log("Appointment update - ID: $appointmentId, Loai: {$appointment['loai_lich']}, Status: $status");

            // Nếu là lịch tư vấn và được xác nhận, tạo Zoom meeting link
            if ($appointment['loai_lich'] === 'Tư vấn' && $status === 'Đã xác nhận') {
                error_log("Creating Zoom meeting link for consultation appointment ID: $appointmentId");
                $this->createZoomMeetingLink($appointment);
            }

            // Emit socket notification for status change
            $this->emitAppointmentStatusChange($appointment, $status, $note);

            $_SESSION['success'] = "Cập nhật trạng thái lịch hẹn thành công!";
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi cập nhật trạng thái!";
        }

        header("Location: ./doctor_appointment_management");
        exit();
    }

    /**
     * Thêm lịch làm việc mới
     */
    public function addSchedule()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ
        $this->auth->requireAuth('doctor');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ./doctor_schedule_management");
            exit();
        }

        $doctorId = $_SESSION['user_id'];

        // Validate dữ liệu
        $thuTrongTuan = $_POST['thu_trong_tuan'] ?? '';
        $gioBatDau = $_POST['gio_bat_dau'] ?? '';
        $gioKetThuc = $_POST['gio_ket_thuc'] ?? '';
        $loaiCa = $_POST['loai_ca'] ?? '';
        $ghiChu = $_POST['ghi_chu'] ?? '';

        // Validation
        if (empty($thuTrongTuan) || empty($gioBatDau) || empty($gioKetThuc) || empty($loaiCa)) {
            $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin bắt buộc!";
            header("Location: ./doctor_schedule_management");
            exit();
        }

        // Kiểm tra thời gian hợp lệ
        if (strtotime($gioBatDau) >= strtotime($gioKetThuc)) {
            $_SESSION['error'] = "Giờ kết thúc phải sau giờ bắt đầu!";
            header("Location: ./doctor_schedule_management");
            exit();
        }

        // Kiểm tra xung đột lịch
        if ($this->doctorModel->checkScheduleConflict($doctorId, $thuTrongTuan, $gioBatDau, $gioKetThuc)) {
            $_SESSION['error'] = "Lịch làm việc này bị xung đột với lịch hiện có!";
            header("Location: ./doctor_schedule_management");
            exit();
        }

        // Thêm lịch làm việc
        $data = [
            'thu_trong_tuan' => $thuTrongTuan,
            'gio_bat_dau' => $gioBatDau,
            'gio_ket_thuc' => $gioKetThuc,
            'loai_ca' => $loaiCa,
            'ghi_chu' => $ghiChu,
            'trang_thai' => 'active'
        ];

        if ($this->doctorModel->addSchedule($doctorId, $data)) {
            $_SESSION['success'] = "Thêm lịch làm việc thành công!";
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi thêm lịch làm việc!";
        }

        header("Location: ./doctor_schedule_management");
        exit();
    }

    /**
     * Cập nhật lịch làm việc
     */
    public function updateSchedule()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ
        $this->auth->requireAuth('doctor');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ./doctor_schedule_management");
            exit();
        }

        $doctorId = $_SESSION['user_id'];
        $scheduleId = $_POST['schedule_id'] ?? '';

        if (empty($scheduleId)) {
            $_SESSION['error'] = "ID lịch làm việc không hợp lệ!";
            header("Location: ./doctor_schedule_management");
            exit();
        }

        // Kiểm tra lịch có thuộc về bác sĩ này không
        $schedule = $this->doctorModel->getScheduleById($scheduleId, $doctorId);
        if (!$schedule) {
            $_SESSION['error'] = "Không tìm thấy lịch làm việc!";
            header("Location: ./doctor_schedule_management");
            exit();
        }

        // Validate dữ liệu
        $thuTrongTuan = $_POST['thu_trong_tuan'] ?? '';
        $gioBatDau = $_POST['gio_bat_dau'] ?? '';
        $gioKetThuc = $_POST['gio_ket_thuc'] ?? '';
        $loaiCa = $_POST['loai_ca'] ?? '';
        $ghiChu = $_POST['ghi_chu'] ?? '';
        $trangThai = $_POST['trang_thai'] ?? 'active';

        // Validation
        if (empty($thuTrongTuan) || empty($gioBatDau) || empty($gioKetThuc) || empty($loaiCa)) {
            $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin bắt buộc!";
            header("Location: ./doctor_schedule_management");
            exit();
        }

        // Kiểm tra thời gian hợp lệ
        if (strtotime($gioBatDau) >= strtotime($gioKetThuc)) {
            $_SESSION['error'] = "Giờ kết thúc phải sau giờ bắt đầu!";
            header("Location: ./doctor_schedule_management");
            exit();
        }

        // Kiểm tra xung đột lịch (loại trừ lịch hiện tại)
        if ($this->doctorModel->checkScheduleConflict($doctorId, $thuTrongTuan, $gioBatDau, $gioKetThuc, $scheduleId)) {
            $_SESSION['error'] = "Lịch làm việc này bị xung đột với lịch hiện có!";
            header("Location: ./doctor_schedule_management");
            exit();
        }

        // Cập nhật lịch làm việc
        $data = [
            'thu_trong_tuan' => $thuTrongTuan,
            'gio_bat_dau' => $gioBatDau,
            'gio_ket_thuc' => $gioKetThuc,
            'loai_ca' => $loaiCa,
            'ghi_chu' => $ghiChu,
            'trang_thai' => $trangThai
        ];

        if ($this->doctorModel->updateSchedule($scheduleId, $doctorId, $data)) {
            $_SESSION['success'] = "Cập nhật lịch làm việc thành công!";
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi cập nhật lịch làm việc!";
        }

        header("Location: ./doctor_schedule_management");
        exit();
    }

    /**
     * Xóa lịch làm việc
     */
    public function deleteSchedule()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ
        $this->auth->requireAuth('doctor');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ./doctor_schedule_management");
            exit();
        }

        $doctorId = $_SESSION['user_id'];
        $scheduleId = $_POST['schedule_id'] ?? '';

        if (empty($scheduleId)) {
            $_SESSION['error'] = "ID lịch làm việc không hợp lệ!";
            header("Location: ./doctor_schedule_management");
            exit();
        }

        // Kiểm tra lịch có thuộc về bác sĩ này không
        $schedule = $this->doctorModel->getScheduleById($scheduleId, $doctorId);
        if (!$schedule) {
            $_SESSION['error'] = "Không tìm thấy lịch làm việc!";
            header("Location: ./doctor_schedule_management");
            exit();
        }

        // Xóa lịch làm việc
        if ($this->doctorModel->deleteSchedule($scheduleId, $doctorId)) {
            $_SESSION['success'] = "Xóa lịch làm việc thành công!";
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi xóa lịch làm việc!";
        }

        header("Location: ./doctor_schedule_management");
        exit();
    }

    /**
     * Lấy thông tin lịch làm việc để edit (AJAX)
     */
    public function getScheduleInfo()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ
        $this->auth->requireAuth('doctor');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        $doctorId = $_SESSION['user_id'];
        $scheduleId = $_POST['schedule_id'] ?? '';

        if (empty($scheduleId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID lịch làm việc không hợp lệ']);
            exit();
        }

        $schedule = $this->doctorModel->getScheduleById($scheduleId, $doctorId);

        if (!$schedule) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy lịch làm việc']);
            exit();
        }

        echo json_encode(['success' => true, 'data' => $schedule]);
        exit();
    }

    /**
     * Lấy lịch làm việc theo thứ (AJAX)
     */
    public function getSchedulesByDay()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ
        $this->auth->requireAuth('doctor');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        $doctorId = $_SESSION['user_id'];
        $thuTrongTuan = $_POST['thu_trong_tuan'] ?? '';

        if (empty($thuTrongTuan)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thứ trong tuần không hợp lệ']);
            exit();
        }

        $schedules = $this->doctorModel->getSchedulesByDay($doctorId, $thuTrongTuan);

        echo json_encode(['success' => true, 'data' => $schedules]);
        exit();
    }

    /**
     * Dashboard bác sĩ
     */
    public function dashboard()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ
        $this->auth->requireAuth('doctor');

        $doctorId = $_SESSION['user_id'];
        $doctor = $this->doctorModel->getById($doctorId);
        $stats = $this->doctorModel->getScheduleStats($doctorId);
        $schedules = $this->doctorModel->getSchedules($doctorId);

        // Lấy lịch hôm nay
        $today = date('l');
        $todayVietnamese = $this->getVietnameseDay($today);
        $todaySchedules = $this->doctorModel->getSchedulesByDay($doctorId, $todayVietnamese);

        // Start output buffering để lấy content
        ob_start();
        include 'Views/doctor/dashboard.php';
        $content = ob_get_clean();

        // Sử dụng renderLayout function
        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, 'Dashboard Bác sĩ');
    }

    /**
     * Chuyển đổi ngày tiếng Anh sang tiếng Việt
     */
    private function getVietnameseDay($englishDay)
    {
        $days = [
            'Monday' => 'Thứ 2',
            'Tuesday' => 'Thứ 3',
            'Wednesday' => 'Thứ 4',
            'Thursday' => 'Thứ 5',
            'Friday' => 'Thứ 6',
            'Saturday' => 'Thứ 7',
            'Sunday' => 'Chủ nhật'
        ];

        return $days[$englishDay] ?? '';
    }

    /**
     * Danh sách bác sĩ theo chuyên khoa (slug)
     */
    public function listBySpecialty()
    {
        // public page tương tự doctor_team (không dùng main layout)
        $slug = $_GET['slug'] ?? '';
        if ($slug === '') {
            include 'Views/doctor/specialties_all.php';
            return;
        }
        require_once 'Models/Specialty.php';
        $spModel = new Specialty();
        $specialty = $spModel->getBySlug($slug);
        if (!$specialty) {
            include 'Views/doctor/specialties_all.php';
            return;
        }
        $doctors = $this->doctorModel->getBySpecialization($specialty['ten']);
        include 'Views/doctor/doctors_by_specialty.php';
    }

    /**
     * Hiển thị tất cả chuyên khoa (grid)
     */
    public function specialtiesAll()
    {
        // public page giống doctor_team (không dùng main layout)
        require_once 'Models/Specialty.php';
        $spModel = new Specialty();
        // Lấy kèm số lượng bác sĩ mỗi chuyên khoa
        if (method_exists($spModel, 'allWithDoctorCounts')) {
            $specialties = $spModel->allWithDoctorCounts();
        } else {
            $specialties = $spModel->all();
        }
        include 'Views/doctor/specialties_all.php';
    }

    /**
     * Emit socket notification for appointment status change
     */
    private function emitAppointmentStatusChange($appointment, $newStatus, $note)
    {
        try {
            $patientId = $appointment['benh_nhan_id'];
            $doctorId = $appointment['bac_si_id'];

            // Get doctor and patient information
            $doctor = $this->doctorModel->getById($doctorId);
            $doctorName = $doctor ? $doctor['ten'] : 'Bác sĩ';

            require_once 'Models/Patient.php';
            $patientModel = new Patient();
            $patient = $patientModel->getById($patientId);
            $patientName = $patient ? $patient['ten'] : 'Bệnh nhân';

            // Prepare notification data
            $dateVn = $appointment['ngay_hen'];
            try {
                $dt = DateTime::createFromFormat('Y-m-d', $appointment['ngay_hen']);
                if ($dt) {
                    $dateVn = $dt->format('d-m-Y');
                }
            } catch (Exception $e) {
            }
            $notificationData = [
                'appointmentId' => $appointment['id'],
                'patientId' => $patientId,
                'doctorId' => $doctorId,
                'patientName' => $patientName,
                'doctorName' => $doctorName,
                'appointmentDate' => $appointment['ngay_hen'],
                'appointmentTime' => $appointment['gio_hen'],
                'newStatus' => $newStatus,
                'note' => $note,
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // Send different notifications based on status
            if ($newStatus === 'hủy') {
                // Notify patient about cancellation
                $notificationData['message'] = "Bác sĩ $doctorName đã hủy lịch hẹn của bạn vào $dateVn lúc {$appointment['gio_hen']}";
                $this->sendSocketNotification('appointment_cancelled_by_doctor', $notificationData);
            } else {
                // Notify patient about status change
                $notificationData['message'] = "Bác sĩ $doctorName đã cập nhật trạng thái lịch hẹn ngày $dateVn thành: $newStatus";
                $this->sendSocketNotification('appointment_status_changed', $notificationData);
            }
        } catch (Exception $e) {
            error_log("Appointment status change notification error: " . $e->getMessage());
        }
    }

    /**
     * Tạo Zoom meeting link cho lịch tư vấn
     */
    private function createZoomMeetingLink($appointment)
    {
        try {
            error_log("Starting Zoom meeting link creation for appointment ID: " . $appointment['id']);
            require_once 'Services/ZoomService.php';

            $zoomService = new ZoomService();

            // Kiểm tra xem service có sẵn sàng không
            if (!$zoomService->isReady()) {
                error_log("Zoom service not ready for appointment ID: " . $appointment['id']);
                return;
            }

            error_log("Zoom service is ready, proceeding with meeting creation");

            // Lấy thông tin bác sĩ và bệnh nhân
            $doctor = $this->doctorModel->getById($appointment['bac_si_id']);
            require_once 'Models/Patient.php';
            $patientModel = new Patient();
            $patient = $patientModel->getById($appointment['benh_nhan_id']);

            if (!$doctor || !$patient) {
                error_log("Cannot find doctor or patient for appointment ID: " . $appointment['id']);
                return;
            }

            // Chuẩn bị dữ liệu cho Zoom meeting
            $meetingData = [
                'appointment_id' => $appointment['id'],
                'ngay_hen' => $appointment['ngay_hen'],
                'gio_hen' => $appointment['gio_hen'],
                'ly_do' => $appointment['ly_do'],
                'doctor_name' => $doctor['ten'],
                'doctor_email' => $doctor['email'],
                'patient_email' => $patient['email']
            ];

            // Tạo Zoom meeting link
            $result = $zoomService->createMeetingLink($meetingData);

            if ($result['success']) {
                // Cập nhật link_tu_van trong database
                require_once 'Models/Appointment.php';
                $appointmentModel = new Appointment();
                $appointmentModel->updateMeetLink($appointment['id'], $result['meet_link']);

                error_log("Zoom meeting link created successfully for appointment ID: " . $appointment['id']);
            } else {
                error_log("Failed to create Zoom meeting link for appointment ID: " . $appointment['id'] . " - Error: " . $result['error']);
            }
        } catch (Exception $e) {
            error_log("Error creating Zoom meeting link for appointment ID: " . $appointment['id'] . " - " . $e->getMessage());
        }
    }

    /**
     * Send socket notification via HTTP request
     */
    private function sendSocketNotification($event, $data)
    {
        try {
            // Read socket server URL from config; fallback to localhost for dev
            $cfg = @include __DIR__ . '/../config/socket.php';
            if (!is_array($cfg) || empty($cfg['server_url'])) {
                $cfg = @include __DIR__ . '/../../config/socket.php';
            }
            $mode = isset($cfg['mode']) ? $cfg['mode'] : 'auto';
            $prod = isset($cfg['server_url']) ? $cfg['server_url'] : '';
            $dev = isset($cfg['dev_url']) ? $cfg['dev_url'] : '';
            $override = isset($_GET['socket']) ? $_GET['socket'] : null;
            if ($override === 'dev' || $override === 'prod') {
                $mode = $override;
            }
            if ($mode === 'dev') {
                $baseUrl = $dev ?: 'http://localhost:3001';
            } elseif ($mode === 'prod') {
                $baseUrl = $prod ?: ($dev ?: 'http://localhost:3001');
            } else {
                $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
                $baseUrl = $isHttps ? ($prod ?: ($dev ?: 'http://localhost:3001')) : ($dev ?: ($prod ?: 'http://localhost:3001'));
            }
            $baseUrl = rtrim($baseUrl, '/');
            $socketUrl = $baseUrl . '/emit';

            $postData = json_encode([
                'event' => $event,
                'data' => $data
            ]);

            // Use cURL for better reliability
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $socketUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postData)
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($result === false || $httpCode !== 200) {
                error_log("Socket notification failed. HTTP Code: $httpCode, Error: $error");
            } else {
                error_log("Socket notification sent successfully: " . $result);
            }
        } catch (Exception $e) {
            error_log("Socket notification error: " . $e->getMessage());
        }
    }

    /**
     * Trang khám bệnh - hiển thị danh sách lịch hẹn theo ngày
     */
    public function examination()
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

            // Lấy ngày được chọn (mặc định là hôm nay)
            $selectedDate = $_GET['date'] ?? date('Y-m-d');

            // Validate ngày
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
                $selectedDate = date('Y-m-d');
            }

            // Kiểm tra xem có cần tự động mở modal không
            $autoOpenModal = false;
            $appointmentId = null;

            if (isset($_GET['start_exam']) && !empty($_GET['start_exam'])) {
                $appointmentId = $_GET['start_exam'];
                $autoOpenModal = true;
                // Cập nhật trạng thái thành "Đang khám"
                require_once 'Models/Appointment.php';
                $appointmentModel = new Appointment();
                $appointmentModel->updateStatus($appointmentId, 'Đang khám');
            } elseif (isset($_GET['continue_exam']) && !empty($_GET['continue_exam'])) {
                $appointmentId = $_GET['continue_exam'];
                $autoOpenModal = true;
            }

            // Lấy lịch hẹn theo ngày được chọn
            require_once 'Models/Appointment.php';
            $appointmentModel = new Appointment();
            $appointments = $appointmentModel->getAppointmentsByDoctorAndDate($doctorId, $selectedDate);
            $stats = $appointmentModel->getStatsByDoctorAndDate($doctorId, $selectedDate);

            // Include view với thông tin auto open modal
            include 'Views/doctor/examination.php';
        } catch (Exception $e) {
            error_log("Doctor examination error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tải trang khám bệnh!';
            header('Location: ./doctor_dashboard');
            exit();
        }
    }

    /**
     * Bắt đầu khám bệnh - đổi trạng thái thành "Đang khám"
     */
    public function startExamination()
    {
        $this->auth->requireAuth('doctor');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ./doctor_examination');
            exit();
        }

        try {
            $appointmentId = $_POST['appointment_id'] ?? null;
            $doctorId = $_SESSION['user_id'];

            if (!$appointmentId) {
                if (!empty($_POST['ajax'])) {
                    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin lịch hẹn!']);
                    exit();
                } else {
                    $_SESSION['error'] = 'Thiếu thông tin lịch hẹn!';
                    header('Location: ./doctor_examination');
                    exit();
                }
            }

            require_once 'Models/Appointment.php';
            $appointmentModel = new Appointment();
            $success = $appointmentModel->startExamination($appointmentId, $doctorId);

            if ($success) {
                if (!empty($_POST['ajax'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true]);
                    exit();
                }
                $_SESSION['success'] = 'Đã bắt đầu khám bệnh thành công!';
            } else {
                if (!empty($_POST['ajax'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Không thể bắt đầu khám bệnh. Vui lòng thử lại!']);
                    exit();
                }
                $_SESSION['error'] = 'Không thể bắt đầu khám bệnh. Vui lòng thử lại!';
            }

            // Redirect về trang examination với ngày hiện tại
            $selectedDate = $_POST['selected_date'] ?? date('Y-m-d');
            header('Location: ./doctor_examination?date=' . $selectedDate);
            exit();
        } catch (Exception $e) {
            error_log("Doctor startExamination error: " . $e->getMessage());
            if (!empty($_POST['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi bắt đầu khám bệnh!']);
                exit();
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi bắt đầu khám bệnh!';
                header('Location: ./doctor_examination');
                exit();
            }
        }
    }

    /**
     * Lưu (upsert) phiếu tiền sử dị ứng
     */
    public function saveAllergyHistory()
    {
        $this->auth->requireAuth('doctor');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        try {
            $patientId = $_POST['patient_id'] ?? null;
            if (!$patientId) {
                echo json_encode(['success' => false, 'message' => 'Thiếu patient_id']);
                exit();
            }
            if (!ctype_digit((string)$patientId)) {
                echo json_encode(['success' => false, 'message' => 'patient_id không hợp lệ']);
                exit();
            }

            require_once 'Models/Appointment.php';
            $model = new Appointment();
            $ok = $model->upsertAllergyHistory($patientId, $_POST);
            echo json_encode(['success' => $ok ? true : false, 'message' => $ok ? 'OK' : 'DB error']);
        } catch (Exception $e) {
            error_log('saveAllergyHistory error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }

    /**
     * Lấy phiếu tiền sử dị ứng theo bệnh nhân
     */
    public function getAllergyHistory()
    {
        $this->auth->requireAuth('doctor');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        try {
            $patientId = $_POST['patient_id'] ?? null;
            if (!$patientId) {
                echo json_encode(['success' => false, 'message' => 'Thiếu patient_id']);
                exit();
            }
            require_once 'Models/Appointment.php';
            $model = new Appointment();
            $data = $model->getAllergyHistoryByPatient($patientId);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            error_log('getAllergyHistory error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
}