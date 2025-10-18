<?php
require_once 'Models/Doctor.php';
require_once 'Models/XraySuggestion.php';
require_once 'Models/PhieuChupXquang.php';
require_once 'Models/PhieuYeuCauSieuAm.php';
require_once 'Models/KetQuaSieuAm.php';
require_once 'Models/SieuAmHinhAnh.php';
require_once 'Models/LabTest.php';
require_once 'Controllers/AuthController.php';
require_once 'config/database.php';

class DoctorController
{
    private $doctorModel;
    private $xraySuggestionModel;
    private $phieuChupXquangModel;
    private $phieuYeuCauSieuAmModel;
    private $ketQuaSieuAmModel;
    private $sieuAmHinhAnhModel;
    private $labTestModel;
    private $auth;
    private $db;

    public function __construct()
    {
        $this->doctorModel = new Doctor();
        $this->xraySuggestionModel = new XraySuggestion();
        $this->phieuChupXquangModel = new PhieuChupXquang();
        $this->phieuYeuCauSieuAmModel = new PhieuYeuCauSieuAm();
        $this->labTestModel = new LabTest();
        
        // DB connection for simple queries
        require_once 'config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
        
        $this->ketQuaSieuAmModel = new KetQuaSieuAm($this->db);
        $this->sieuAmHinhAnhModel = new SieuAmHinhAnh($this->db);
        $this->auth = new AuthController();
    }

    /**
     * Hiển thị trang quản lý lịch làm việc
     */
    public function manageSchedule()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ (cả doctor, xray_doctor và sieuam_doctor)
        if ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor' && $_SESSION['user_role'] !== 'sieuam_doctor') {
            header("Location: ./login");
            exit();
        }

        $doctorId = $_SESSION['user_id'];
        $schedules = $this->doctorModel->getSchedules($doctorId);
        $stats = $this->doctorModel->getScheduleStats($doctorId);

        // Không nhóm theo thứ nữa; sẽ lấy theo ngày cụ thể khi render tuần
        $daysOfWeek = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];

        // Support custom start date (?from=YYYY-MM-DD) and weekly view with offset (?w=...)
        $fromParam = (isset($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'])) ? $_GET['from'] : date('Y-m-d');
        $fromDate = DateTime::createFromFormat('Y-m-d', $fromParam);
        if (!$fromDate) {
            $fromDate = new DateTime();
        }
        $fromDate->setTime(0, 0, 0);

        // Cửa sổ hiển thị/điều hướng lấy theo chính sách booking (dành cho bệnh nhân)
        $todayDt = new DateTime(date('Y-m-d'));

        // Base week is the Monday of the week containing the 'from' date
        $baseWeekStart = clone $fromDate;
        $baseWeekStart->modify('monday this week');

        // Weekly context with offset
        $weekOffset = isset($_GET['w']) && is_numeric($_GET['w']) ? (int)$_GET['w'] : 0;
        $weekStartDate = clone $baseWeekStart;
        if ($weekOffset !== 0) {
            if ($weekOffset > 0) {
                $weekStartDate->add(new DateInterval('P' . ($weekOffset * 7) . 'D'));
            } else {
                $weekStartDate->sub(new DateInterval('P' . (abs($weekOffset) * 7) . 'D'));
            }
        }
        $weekEndDate = clone $weekStartDate;
        $weekEndDate->add(new DateInterval('P6D'));
        $weekLabel = $weekStartDate->format('d/m') . ' - ' . $weekEndDate->format('d/m/Y');
        $prevWeekOffset = $weekOffset - 1;
        $nextWeekOffset = $weekOffset + 1;
        $weekDaysDates = [];
        $weekDaysOrder = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
        foreach ($weekDaysOrder as $idx => $vnDay) {
            $d = clone $weekStartDate;
            $d->add(new DateInterval('P' . $idx . 'D'));
            $weekDaysDates[$vnDay] = $d->format('Y-m-d');
        }

        // Lấy ngoại lệ theo ngày trong phạm vi tuần đang xem để áp dụng khi render
        $exceptions = $this->doctorModel->getScheduleExceptionsByDateRange(
            $doctorId,
            $weekStartDate->format('Y-m-d'),
            $weekEndDate->format('Y-m-d')
        );

        // Booking window for patients: từ mốc 23
        $openDay = 23;
        $anchor = new DateTime(date('Y-m-01'));
        $anchor->setDate((int)$anchor->format('Y'), (int)$anchor->format('m'), $openDay);
        if ($todayDt < $anchor) {
            // Trước ngày 23: hiển thị từ 23 tháng trước → hết tháng hiện tại
            $bookingStart = (clone $anchor)->modify('-1 month');
            $bookingEnd = (clone $anchor)->modify('last day of this month');
        } else {
            // Từ ngày 23 trở đi: hiển thị 23 tháng này → hết tháng kế tiếp
            $bookingStart = clone $anchor;
            $bookingEnd = (clone $anchor)->modify('+1 month')->modify('last day of this month');
        }

        // Gán cửa sổ hiển thị cho view (để ẩn/hiện card ngày)
        $windowStartDate = clone $bookingStart;
        $windowEndDate = clone $bookingEnd;

        // Cho phép điều hướng tuần trong [bookingStart .. bookingEnd]
        $navWindowStart = clone $bookingStart;
        $navWindowEnd = clone $bookingEnd;
        $daysDiff = (int)floor(($navWindowEnd->getTimestamp() - $navWindowStart->getTimestamp()) / 86400);
        $maxWeekOffset = max(0, (int)floor($daysDiff / 7));
        $allowPrevWeek = $weekStartDate > $navWindowStart; // chỉ khi tuần hiện tại nằm sau start
        $allowNextWeek = ($weekEndDate < $navWindowEnd);

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
        // Kiểm tra đăng nhập và quyền bác sĩ (cả doctor, xray_doctor và sieuam_doctor)
        if ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor' && $_SESSION['user_role'] !== 'sieuam_doctor') {
            header("Location: ./login");
            exit();
        }

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
        // Kiểm tra đăng nhập và quyền bác sĩ (cả doctor, xray_doctor và sieuam_doctor)
        if ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor' && $_SESSION['user_role'] !== 'sieuam_doctor') {
            header("Location: ./login");
            exit();
        }

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
            // Nếu là lịch tư vấn và được xác nhận, tạo Zoom meeting link
            if ($appointment['loai_lich'] === 'Tư vấn' && $status === 'Đã xác nhận') {
                error_log("Creating Zoom meeting link for consultation appointment ID: $appointmentId");
                $this->createZoomMeetingLink($appointment);
            }

            // Emit socket notification for status change
            $this->emitAppointmentStatusChange($appointment, $status, $note);

            // If doctor cancels, notify patient via email
            if ($status === 'hủy') {
                try {
                    require_once 'Services/MailService.php';
                    require_once 'Models/Patient.php';
                    $patientModel = new Patient();
                    $patient = $patientModel->getById($appointment['benh_nhan_id']);
                    $doctor = $this->doctorModel->getById($doctorId);
                    if ($patient && $doctor) {
                        // Lấy lại lịch hẹn để lấy thời điểm cập nhật (ngày/giờ hủy)
                        require_once 'Models/Appointment.php';
                        $apptModelFresh = new Appointment();
                        $fresh = $apptModelFresh->getById($appointmentId);
                        $cancelDate = isset($fresh['ngay_cap_nhat']) ? date('Y-m-d', strtotime($fresh['ngay_cap_nhat'])) : date('Y-m-d');
                        $cancelTime = isset($fresh['ngay_cap_nhat']) ? date('H:i', strtotime($fresh['ngay_cap_nhat'])) : date('H:i');
                        $mailer = new MailService();
                        $mailer->sendDoctorCancelledToPatient(
                            $patient,
                            $doctor,
                            $appointment['ngay_hen'],
                            $appointment['gio_hen'],
                            $appointment['loai_lich'] ?? 'Trực tiếp',
                            $cancelDate,
                            $cancelTime
                        );
                    }
                } catch (Exception $e) {
                    error_log('Mail (doctor cancelled -> patient) error: ' . $e->getMessage());
                }
            }

            // If confirmed, send email to patient (include link_tu_van if available) and reminder to doctor
            if ($status === 'Đã xác nhận') {
                try {
                    require_once 'Services/MailService.php';
                    $doctor = $this->doctorModel->getById($doctorId);
                    require_once 'Models/Patient.php';
                    $patientModel = new Patient();
                    $patient = $patientModel->getById($appointment['benh_nhan_id']);
                    if ($doctor && $patient) {
                        // Re-fetch latest appointment to get updated link_tu_van (e.g., Zoom link just created)
                        require_once 'Models/Appointment.php';
                        $apptModelFresh = new Appointment();
                        $fresh = $apptModelFresh->getById($appointmentId);
                        $zoomLink = isset($fresh['link_tu_van']) ? $fresh['link_tu_van'] : (isset($appointment['link_tu_van']) ? $appointment['link_tu_van'] : null);
                        $mailer = new MailService();
                        $mailer->sendAppointmentConfirmedToPatient(
                            $patient,
                            $doctor,
                            $fresh['ngay_hen'] ?? $appointment['ngay_hen'],
                            $fresh['gio_hen'] ?? $appointment['gio_hen'],
                            ($fresh['loai_lich'] ?? $appointment['loai_lich'] ?? 'Trực tiếp'),
                            $zoomLink
                        );

                        // Send reminder email to doctor as well (with CTA if tư vấn)
                        $mailer->sendAppointmentConfirmedReminderToDoctor(
                            $doctor,
                            $patient,
                            $fresh['ngay_hen'] ?? $appointment['ngay_hen'],
                            $fresh['gio_hen'] ?? $appointment['gio_hen'],
                            ($fresh['loai_lich'] ?? $appointment['loai_lich'] ?? 'Trực tiếp'),
                            $zoomLink
                        );
                    }
                } catch (Exception $e) {
                    error_log('Mail (confirmed to patient) error: ' . $e->getMessage());
                }
            }

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
        // Kiểm tra đăng nhập và quyền bác sĩ (cả doctor, xray_doctor và sieuam_doctor)
        if ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor' && $_SESSION['user_role'] !== 'sieuam_doctor') {
            header("Location: ./login");
            exit();
        }

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

        // Chỉ cho bác sĩ đăng ký/hoàn tất lịch cơ bản từ ngày 23 → 25 hằng tháng
        if (!$this->isWithinDoctorRegistrationWindow()) {
            $_SESSION['error'] = 'Chỉ được đăng ký/hoàn tất lịch từ ngày 23 đến 25 hằng tháng!';
            header('Location: ./doctor_schedule_management');
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

        $newId = $this->doctorModel->addSchedule($doctorId, $data);
        if ($newId) {
            // Nếu đang trong ngày 23-25, khóa từ NGÀY 23 → hết tháng hiện tại bằng ngoại lệ cancel
            $tz = new DateTimeZone('Asia/Ho_Chi_Minh');
            $today = new DateTime('now', $tz);
            $day = (int)$today->format('j');
            if ($day >= 23 && $day <= 25) {
                // Tính từ ngày 23 của THÁNG HIỆN TẠI đến hết tháng (bao gồm 23/24/25…)
                $startLock = new DateTime($today->format('Y-m-') . '23', $tz);
                $startLock->setTime(0, 0, 0);
                $endLock = clone $today;
                $endLock->modify('last day of this month');
                $endLock->setTime(23, 59, 59);
                if ($startLock <= $endLock) {
                    $cursor = clone $startLock;
                    while ($cursor <= $endLock) {
                        $dateYmd = $cursor->format('Y-m-d');
                        try {
                            $this->doctorModel->addCancelException($doctorId, (int)$newId, $dateYmd, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25');
                        } catch (Exception $e) {
                        }
                        $cursor->modify('+1 day');
                    }
                }
            }
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
        // Kiểm tra đăng nhập và quyền bác sĩ (cả doctor, xray_doctor và sieuam_doctor)
        if ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor' && $_SESSION['user_role'] !== 'sieuam_doctor') {
            header("Location: ./login");
            exit();
        }

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

        // Áp dụng quy định: Chỉ được thay đổi ca trực vào Thứ 2 và phải có lý do
        if (!$this->isMonday()) {
            $_SESSION['error'] = "Chỉ được thay đổi ca trực vào Thứ 2.";
            header("Location: ./doctor_schedule_management");
            exit();
        }

        if (empty(trim($ghiChu))) {
            $_SESSION['error'] = "Vui lòng cung cấp lý do chính đáng khi thay đổi ca trực (bắt buộc).";
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
        // Kiểm tra đăng nhập và quyền bác sĩ (cả doctor, xray_doctor và sieuam_doctor)
        if ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor' && $_SESSION['user_role'] !== 'sieuam_doctor') {
            header("Location: ./login");
            exit();
        }

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

        // Chỉ cho phép xóa vào Thứ 2
        if (!$this->isMonday()) {
            $_SESSION['error'] = "Chỉ được xóa ca trực vào Thứ 2.";
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
        // Kiểm tra đăng nhập và quyền bác sĩ (cả doctor, xray_doctor và sieuam_doctor)
        if ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor' && $_SESSION['user_role'] !== 'sieuam_doctor') {
            header("Location: ./login");
            exit();
        }

        header('Content-Type: application/json');
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
        // Kiểm tra đăng nhập và quyền bác sĩ (cả doctor, xray_doctor và sieuam_doctor)
        if ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor' && $_SESSION['user_role'] !== 'sieuam_doctor') {
            header("Location: ./login");
            exit();
        }

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
     * Chỉnh sửa lịch làm việc CHO MỘT NGÀY CỤ THỂ (tạo ngoại lệ theo ngày)
     */
    public function modifyScheduleForDate()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ (cả doctor, xray_doctor và sieuam_doctor)
        if ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor' && $_SESSION['user_role'] !== 'sieuam_doctor') {
            header("Location: ./login");
            exit();
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ./doctor_schedule_management');
            exit();
        }
        $doctorId = $_SESSION['user_id'];
        $scheduleId = $_POST['schedule_id'] ?? '';
        $date = $_POST['date'] ?? '';
        $loaiCa = $_POST['loai_ca'] ?? '';
        $gioBatDau = $_POST['gio_bat_dau'] ?? '';
        $gioKetThuc = $_POST['gio_ket_thuc'] ?? '';
        $ghiChu = $_POST['ghi_chu'] ?? '';
        $fromParam = $_POST['from'] ?? null;

        // Chỉ cho phép thao tác vào Thứ 2
        if (!$this->isMonday()) {
            $_SESSION['error'] = 'Chỉ được thay đổi ca trực vào Thứ 2.';
            header('Location: ./doctor_schedule_management');
            exit();
        }

        if (!$scheduleId || !$date || !$loaiCa || !$gioBatDau || !$gioKetThuc) {
            $_SESSION['error'] = 'Thiếu thông tin bắt buộc!';
            header('Location: ./doctor_schedule_management');
            exit();
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $_SESSION['error'] = 'Ngày không hợp lệ!';
            header('Location: ./doctor_schedule_management');
            exit();
        }
        if (strtotime($gioBatDau) >= strtotime($gioKetThuc)) {
            $_SESSION['error'] = 'Giờ kết thúc phải sau giờ bắt đầu!';
            header('Location: ./doctor_schedule_management');
            exit();
        }
        if (empty(trim($ghiChu))) {
            $_SESSION['error'] = 'Vui lòng nhập lý do thay đổi!';
            header('Location: ./doctor_schedule_management');
            exit();
        }

        // Kiểm tra lịch thuộc bác sĩ
        $schedule = $this->doctorModel->getScheduleById($scheduleId, $doctorId);
        if (!$schedule) {
            $_SESSION['error'] = 'Không tìm thấy lịch làm việc!';
            header('Location: ./doctor_schedule_management');
            exit();
        }

        // Giới hạn trong 1 tháng kể từ mốc from (hoặc hôm nay)
        $tz = new DateTimeZone('Asia/Ho_Chi_Minh');
        $start = $fromParam && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromParam) ? $fromParam : date('Y-m-d');
        $windowStart = DateTime::createFromFormat('Y-m-d', $start, $tz);
        $windowEnd = clone $windowStart;
        $windowEnd->modify('+1 month');
        $dateObj = DateTime::createFromFormat('Y-m-d', $date, $tz);
        if (!$dateObj || $dateObj < $windowStart || $dateObj > $windowEnd) {
            $_SESSION['error'] = 'Ngày chỉnh sửa nằm ngoài phạm vi 1 tháng!';
            header('Location: ./doctor_schedule_management');
            exit();
        }

        // Giới hạn: chỉ cho phép chỉnh sửa NGÀY thuộc TUẦN SAU (không phải tuần này)
        $today = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $today->setTime(0, 0, 0);
        $mondayThisWeek = clone $today;
        $mondayThisWeek->modify('monday this week');
        $nextWeekStart = clone $mondayThisWeek;
        $nextWeekStart->modify('+7 days');
        $nextWeekEnd = clone $nextWeekStart;
        $nextWeekEnd->modify('+6 days');
        $dateObjMid = (clone $dateObj)->setTime(0, 0, 0);
        if ($dateObjMid < $nextWeekStart || $dateObjMid > $nextWeekEnd) {
            $_SESSION['error'] = 'Chỉ được chỉnh sửa ca trực cho các ngày thuộc TUẦN SAU (Thứ 2 → Chủ nhật tuần sau).';
            $redir = './doctor_schedule_management' . ($fromParam ? ('?from=' . urlencode($fromParam)) : '');
            header('Location: ' . $redir);
            exit();
        }

        // Kiểm tra xung đột theo ngày (sau khi áp dụng ngoại lệ khác)
        $schedIdInt = (int)$scheduleId;
        if ($this->doctorModel->checkDateScheduleConflict($doctorId, $date, $gioBatDau, $gioKetThuc, $schedIdInt)) {
            $_SESSION['error'] = 'Khung giờ mới bị trùng với ca khác trong ngày ' . $date . '!';
            $redir = './doctor_schedule_management' . ($fromParam ? ('?from=' . urlencode($fromParam)) : '');
            header('Location: ' . $redir);
            exit();
        }

        $ok = $this->doctorModel->upsertModifyException($doctorId, $schedIdInt, $date, [
            'gio_bat_dau' => $gioBatDau,
            'gio_ket_thuc' => $gioKetThuc,
            'loai_ca' => $loaiCa,
            'ghi_chu' => $ghiChu,
        ]);

        $_SESSION[$ok ? 'success' : 'error'] = $ok ? 'Đã cập nhật ca trực cho ngày ' . $date . '!' : 'Không thể cập nhật ca trực cho ngày này!';
        $redir = './doctor_schedule_management' . ($fromParam ? ('?from=' . urlencode($fromParam)) : '');
        header('Location: ' . $redir);
        exit();
    }

    /**
     * Hủy ca trực CHO MỘT NGÀY CỤ THỂ (tạo ngoại lệ cancel)
     */
    public function cancelScheduleForDate()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ (cả doctor, xray_doctor và sieuam_doctor)
        if ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor' && $_SESSION['user_role'] !== 'sieuam_doctor') {
            header("Location: ./login");
            exit();
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ./doctor_schedule_management');
            exit();
        }
        $doctorId = $_SESSION['user_id'];
        $scheduleId = $_POST['schedule_id'] ?? '';
        $date = $_POST['date'] ?? '';
        $reason = $_POST['reason'] ?? '';
        $fromParam = $_POST['from'] ?? null;

        if (!$this->isMonday()) {
            $_SESSION['error'] = 'Chỉ được xóa ca trực vào Thứ 2.';
            header('Location: ./doctor_schedule_management');
            exit();
        }
        if (!$scheduleId || !$date) {
            $_SESSION['error'] = 'Thiếu thông tin bắt buộc!';
            header('Location: ./doctor_schedule_management');
            exit();
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $_SESSION['error'] = 'Ngày không hợp lệ!';
            header('Location: ./doctor_schedule_management');
            exit();
        }
        if (empty(trim($reason))) {
            $_SESSION['error'] = 'Vui lòng nhập lý do hủy ca!';
            header('Location: ./doctor_schedule_management');
            exit();
        }
        $schedule = $this->doctorModel->getScheduleById($scheduleId, $doctorId);
        if (!$schedule) {
            $_SESSION['error'] = 'Không tìm thấy lịch làm việc!';
            header('Location: ./doctor_schedule_management');
            exit();
        }
        $tz = new DateTimeZone('Asia/Ho_Chi_Minh');
        $start = $fromParam && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromParam) ? $fromParam : date('Y-m-d');
        $windowStart = DateTime::createFromFormat('Y-m-d', $start, $tz);
        $windowEnd = clone $windowStart;
        $windowEnd->modify('+1 month');
        $dateObj = DateTime::createFromFormat('Y-m-d', $date, $tz);
        if (!$dateObj || $dateObj < $windowStart || $dateObj > $windowEnd) {
            $_SESSION['error'] = 'Ngày hủy nằm ngoài phạm vi 1 tháng!';
            header('Location: ./doctor_schedule_management');
            exit();
        }

        // Giới hạn: chỉ cho phép HỦY ngày thuộc TUẦN SAU
        $today = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $today->setTime(0, 0, 0);
        $mondayThisWeek = clone $today;
        $mondayThisWeek->modify('monday this week');
        $nextWeekStart = clone $mondayThisWeek;
        $nextWeekStart->modify('+7 days');
        $nextWeekEnd = clone $nextWeekStart;
        $nextWeekEnd->modify('+6 days');
        $dateObjMid = (clone $dateObj)->setTime(0, 0, 0);
        if ($dateObjMid < $nextWeekStart || $dateObjMid > $nextWeekEnd) {
            $_SESSION['error'] = 'Chỉ được hủy ca trực cho các ngày thuộc TUẦN SAU (Thứ 2 → Chủ nhật tuần sau).';
            $redir = './doctor_schedule_management' . ($fromParam ? ('?from=' . urlencode($fromParam)) : '');
            header('Location: ' . $redir);
            exit();
        }

        $ok = $this->doctorModel->addCancelException($doctorId, (int)$scheduleId, $date, $reason);
        $_SESSION[$ok ? 'success' : 'error'] = $ok ? 'Đã hủy ca trực ngày ' . $date . '!' : 'Không thể hủy ca trực ngày này!';
        $redir = './doctor_schedule_management' . ($fromParam ? ('?from=' . urlencode($fromParam)) : '');
        header('Location: ' . $redir);
        exit();
    }

    /**
     * Dashboard bác sĩ
     */
    public function dashboard()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ (cả doctor và xray_doctor)
        if ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor') {
            header("Location: ./login");
            exit();
        }

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
     * Đăng ký/hoàn tất lịch cơ bản: chỉ 23 → 25 hằng tháng
     */
    private function isWithinDoctorRegistrationWindow(): bool
    {
        $dt = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $day = (int)$dt->format('j');
        // Cho phép đăng ký từ ngày 23 → 25 hằng tháng
        return $day >= 23 && $day <= 25;
    }

    /**
     * Kiểm tra hôm nay là Thứ 4
     */
    private function isMonday()
    {
        $dt = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        return (int)$dt->format('N') === 1; // 4 = monday (VN timezone)
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
                // Đồng bộ hàng đợi: chuyển phiếu sang 'dang_kham' và phát realtime
                try {
                    require_once 'Models/QueueTicket.php';
                    $qt = new QueueTicket();
                    $qt->updateStatusByAppointmentId((int)$appointmentId, 'dang_kham');
                    $this->sendSocketNotification('appointment_update', [
                        'doctorId' => $doctorId,
                        'appointmentId' => (int)$appointmentId,
                        'queueStatus' => 'dang_kham',
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                } catch (Exception $e) {
                }
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
                // Đồng bộ hàng đợi: đặt trạng thái phiếu sang 'dang_kham' và ẩn khỏi lễ tân
                try {
                    require_once 'Models/QueueTicket.php';
                    $qt = new QueueTicket();
                    $qt->updateStatusByAppointmentId((int)$appointmentId, 'dang_kham');
                    // Emit realtime để lễ tân và bác sĩ cập nhật giao diện
                    $this->sendSocketNotification('appointment_update', [
                        'doctorId' => $doctorId,
                        'appointmentId' => (int)$appointmentId,
                        'queueStatus' => 'dang_kham',
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                } catch (Exception $e) {
                }
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

    // ===== Examination form: save & print =====
    public function saveExaminationForm()
    {
        $this->auth->requireAuth('doctor');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        try {
            $doctorId = $_SESSION['user_id'];
            $patientId = $_POST['patient_id'] ?? null;
            if (!$patientId) {
                echo json_encode(['success' => false, 'message' => 'Thiếu patient_id']);
                exit();
            }

            require_once 'Models/PhieuKhamBenh.php';
            $model = new PhieuKhamBenh();

            // Map payload from form names
            $payload = [
                'benh_nhan_id' => $patientId,
                'bac_si_id' => $doctorId,
                'so_y_te' => $_POST['so_y_te'] ?? null,
                'benh_vien' => $_POST['benh_vien'] ?? null,
                'buong_kham' => $_POST['buong_kham'] ?? null,
                'ho_ten' => $_POST['ho_ten'] ?? null,
                'ngay_sinh' => $_POST['ngay_sinh'] ?? null,
                'thang_sinh' => $_POST['thang_sinh'] ?? null,
                'nam_sinh' => $_POST['nam_sinh'] ?? null,
                'tuoi' => $_POST['tuoi'] ?? null,
                'gioi_tinh' => $_POST['gioi_tinh'] ?? null,
                'nghe_nghiep' => $_POST['nghe_nghiep'] ?? null,
                'dan_toc' => $_POST['dan_toc'] ?? null,
                'ngoai_kieu' => $_POST['ngoai_kieu'] ?? null,
                'noi_lam_viec' => $_POST['noi_lam_viec'] ?? null,
                'dia_chi' => $_POST['dia_chi'] ?? null,
                'doi_tuong_bhyt' => isset($_POST['doi_tuong']) && in_array('BHYT', (array)$_POST['doi_tuong']) ? 1 : 0,
                'doi_tuong_thu_phi' => isset($_POST['doi_tuong']) && in_array('Thu phí', (array)$_POST['doi_tuong']) ? 1 : 0,
                'doi_tuong_mien' => isset($_POST['doi_tuong']) && in_array('Miễn', (array)$_POST['doi_tuong']) ? 1 : 0,
                'doi_tuong_khac' => isset($_POST['doi_tuong']) && in_array('Khác', (array)$_POST['doi_tuong']) ? 1 : 0,
                'bhyt_ngay' => $_POST['bhyt_ngay'] ?? null,
                'bhyt_thang' => $_POST['bhyt_thang'] ?? null,
                'bhyt_nam' => $_POST['bhyt_nam'] ?? null,
                'so_the_bhyt' => $_POST['so_the_bhyt'] ?? null,
                'dien_thoai_bao_tin' => $_POST['dien_thoai_bao_tin'] ?? null,
                'gio_kham' => $_POST['gio_kham'] ?? null,
                'phut_kham' => $_POST['phut_kham'] ?? null,
                'ngay_kham' => $_POST['ngay_kham'] ?? null,
                'thang_kham' => $_POST['thang_kham'] ?? null,
                'nam_kham' => $_POST['nam_kham'] ?? null,
                'chan_doan_gioi_thieu' => $_POST['chan_doan_gioi_thieu'] ?? null,
                'qua_trinh_benh_li' => $_POST['qua_trinh_benh_li'] ?? null,
                'tien_su_ban_than' => $_POST['tien_su_ban_than'] ?? null,
                'tien_su_gia_dinh' => $_POST['tien_su_gia_dinh'] ?? null,
                'kham_toan_than' => $_POST['kham_toan_than'] ?? null,
                'mach' => $_POST['mach'] ?? null,
                'nhiet_do' => $_POST['nhiet_do'] ?? null,
                'huyet_ap_tam_thu' => $_POST['huyet_ap_tam_thu'] ?? null,
                'huyet_ap_tam_truong' => $_POST['huyet_ap_tam_truong'] ?? null,
                'nhip_tho' => $_POST['nhip_tho'] ?? null,
                'kham_cac_bo_phan' => $_POST['kham_cac_bo_phan'] ?? null,
                'tom_tat_lam_sang' => $_POST['tom_tat_lam_sang'] ?? null,
                'chan_doan_vao_vien' => $_POST['chan_doan_vao_vien'] ?? null,
                'da_xu_li' => $_POST['da_xu_li'] ?? null,
                'khoa_dieu_tri' => $_POST['khoa_dieu_tri'] ?? null,
                'chu_y' => $_POST['chu_y'] ?? null,
                'ngay_ky' => $_POST['ngay_ky'] ?? null,
                'thang_ky' => $_POST['thang_ky'] ?? null,
                'nam_ky' => $_POST['nam_ky'] ?? null,
                'ten_bac_si' => $_POST['ten_bac_si'] ?? null,
                'id_lich_hen' => $_POST['appointment_id'] ?? null,
            ];

            // Upsert by appointment if appointment_id is provided
            if (!empty($payload['id_lich_hen'])) {
                $newId = $model->upsertByAppointmentId((int)$payload['id_lich_hen'], $payload);
            } else {
                $newId = $model->create($payload);
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => $newId ? true : false, 'id' => $newId]);
        } catch (Exception $e) {
            error_log('saveExaminationForm error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
        exit();
    }

    public function getExaminationForm()
    {
        $this->auth->requireAuth('doctor');
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        try {
            $appointmentId = $_POST['appointment_id'] ?? null;
            if (!$appointmentId) { echo json_encode(['success'=>false,'message'=>'Thiếu appointment_id']); exit(); }
            require_once 'Models/PhieuKhamBenh.php';
            $model = new PhieuKhamBenh();
            $row = $model->getByAppointmentId((int)$appointmentId);
            echo json_encode(['success'=>true,'data'=>$row]);
        } catch (Exception $e) {
            error_log('getExaminationForm error: ' . $e->getMessage());
            echo json_encode(['success'=>false,'message'=>'Server error']);
        }
        exit();
    }

    public function printExaminationForm()
    {
        $this->auth->requireAuth('doctor');
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo 'Thiếu id phiếu khám bệnh';
            exit();
        }
        require_once 'Models/PhieuKhamBenh.php';
        $model = new PhieuKhamBenh();
        $record = $model->getById((int)$id);
        if (!$record) {
            echo 'Không tìm thấy phiếu khám bệnh';
            exit();
        }
        
        // Include the beautiful print view
        include 'Views/doctor/print_examination_form.php';
        exit();
    }

    /**
     * Lấy gợi ý X-Quang từ database
     */
    public function getXraySuggestions()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ (cả doctor và xray_doctor)
        if ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $keyword = $_POST['keyword'] ?? '';
        
        // Debug log
        error_log('X-Ray Suggestions - Keyword: ' . $keyword);
        
        if (empty($keyword)) {
            // Lấy tất cả gợi ý nếu không có từ khóa
            $suggestions = $this->xraySuggestionModel->getAllActive();
        } else {
            // Tìm kiếm theo từ khóa
            $suggestions = $this->xraySuggestionModel->search($keyword);
        }
        
        // Debug log
        error_log('X-Ray Suggestions - Found: ' . count($suggestions) . ' suggestions');

        echo json_encode([
            'success' => true,
            'data' => $suggestions
        ]);
    }

    /**
     * Lấy gợi ý Siêu âm từ database (bảng sieuam_suggestions)
     */
    public function getUltrasoundSuggestions()
    {
        // Allow both doctor and xray_doctor; if not logged in, still allow for testing autocomplete
        if (!isset($_SESSION['user_role'])) {
            // no-op; continue
        } elseif ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';
        try {
            $sql = "SELECT id, ten_goi_y, gia_tien FROM sieuam_suggestions WHERE trang_thai=1 AND ten_goi_y LIKE :kw ORDER BY thu_tu, ten_goi_y LIMIT 50";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':kw' => '%' . $keyword . '%']);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Throwable $e) {
            error_log('getUltrasoundSuggestions error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Tính giá tiền X-Quang
     */
    public function calculateXrayPrice()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ (cả doctor và xray_doctor)
        if ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $suggestionNames = $_POST['suggestions'] ?? '';
        
        // Debug log
        error_log('X-Ray Price Calculation - Input: ' . $suggestionNames);
        
        if (empty($suggestionNames)) {
            echo json_encode([
                'success' => true,
                'total_price' => 0,
                'details' => []
            ]);
            exit();
        }

        $totalPrice = $this->xraySuggestionModel->calculateTotalPrice($suggestionNames);
        $details = $this->xraySuggestionModel->getPriceDetails($suggestionNames);
        
        // Debug log
        error_log('X-Ray Price Calculation - Total: ' . $totalPrice . ', Details: ' . json_encode($details));

        echo json_encode([
            'success' => true,
            'total_price' => $totalPrice,
            'details' => $details
        ]);
    }

    /**
     * Lưu phiếu chụp X-Quang
     */
    public function saveXrayForm()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ
        if ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        try {
            // Lấy dữ liệu từ POST
            $idPhieuKhamBenh = $_POST['id_phieu_kham_benh'] ?? '';
            $soDienThoai = $_POST['so_dien_thoai'] ?? '0777871608';
            $quan = $_POST['quan'] ?? 'Gò Vấp';
            $yeuCauChup = $_POST['yeu_cau_chup'] ?? '';
            $bacSiKham = $_POST['bac_si_kham'] ?? '';
            $chanDoanVaoVien = $_POST['chan_doan_vao_vien'] ?? '';

            // Validate required fields
            if (empty($idPhieuKhamBenh) || empty($yeuCauChup)) {
                echo json_encode(['success' => false, 'message' => 'Thiếu thông tin bắt buộc']);
                exit();
            }

            // Kiểm tra yêu cầu chụp X-Quang có hợp lệ không
            $validation = $this->phieuChupXquangModel->validateXrayRequests($yeuCauChup);
            if (!$validation['valid']) {
                echo json_encode(['success' => false, 'message' => $validation['message']]);
                exit();
            }

            // Kiểm tra xem đã có phiếu chụp X-Quang cho phiếu khám này chưa
            try {
                $existingXray = $this->phieuChupXquangModel->getByExamId($idPhieuKhamBenh);
            } catch (Exception $e) {
                error_log('Error getting existing X-Ray form: ' . $e->getMessage());
                $existingXray = null;
            }
            
            $data = [
                'id_phieu_kham_benh' => $idPhieuKhamBenh,
                'so_dien_thoai' => $soDienThoai,
                'quan' => $quan,
                'yeu_cau_chup' => $yeuCauChup,
                'bac_si_kham' => $bacSiKham,
                'chan_doan_vao_vien' => $chanDoanVaoVien
            ];

            if ($existingXray) {
                // Cập nhật phiếu chụp X-Quang đã có
                try {
                    $id = $this->phieuChupXquangModel->update($existingXray['id'], $data);
                    // Sau khi cập nhật, đảm bảo trạng thái là "Đã yêu cầu"
                    $this->phieuChupXquangModel->updateStatus($id, 'Đã yêu cầu');
                    $message = 'Cập nhật phiếu chụp X-Quang thành công';
                } catch (Exception $e) {
                    error_log('Error updating X-Ray form: ' . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật phiếu chụp X-Quang']);
                    exit();
                }
            } else {
                // Tạo phiếu chụp X-Quang mới
                try {
                    $id = $this->phieuChupXquangModel->save($data);
                    // Sau khi tạo mới, trạng thái mặc định đã là "Đã yêu cầu"
                    $message = 'Lưu phiếu chụp X-Quang thành công';
                } catch (Exception $e) {
                    error_log('Error saving X-Ray form: ' . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu phiếu chụp X-Quang']);
                    exit();
                }
            }
            
            if ($id) {
                echo json_encode([
                    'success' => true, 
                    'message' => $message,
                    'id' => $id
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu phiếu chụp X-Quang']);
            }

        } catch (Exception $e) {
            error_log('Save X-Ray form error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * In phiếu chụp X-Quang
     */
    public function printXrayForm()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ
        if ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $id = $_GET['id'] ?? '';
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID phiếu chụp X-Quang']);
            exit();
        }

        $phieuChup = $this->phieuChupXquangModel->getById($id);
        
        if (!$phieuChup) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy phiếu chụp X-Quang']);
            exit();
        }

        // Include print view
        include 'Views/doctor/print_xray_form.php';
    }

    /**
     * Xem chi tiết phiếu chụp X-Quang (không in)
     */
    public function viewXrayForm()
    {
        if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            echo 'Thiếu ID phiếu chụp X-Quang';
            exit();
        }

        $phieuChup = $this->phieuChupXquangModel->getById($id);
        if (!$phieuChup) {
            echo 'Không tìm thấy phiếu chụp X-Quang';
            exit();
        }

        include 'Views/doctor/view_xray_form.php';
    }

    /**
     * API: Danh sách phiếu chụp trạng thái "Đã yêu cầu"
     */
    public function getRequestedXrayList()
    {
        if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $date = isset($_GET['date']) ? $_GET['date'] : null; // format YYYY-MM-DD
        $keyword = isset($_GET['name']) ? trim($_GET['name']) : (isset($_GET['keyword']) ? trim($_GET['keyword']) : null);

        try {
            $list = $this->phieuChupXquangModel->getRequested($limit, $offset, $date, $keyword);
            echo json_encode(['success' => true, 'data' => $list]);
        } catch (Exception $e) {
            error_log('getRequestedXrayList error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * API: Lấy dữ liệu hiển thị trả kết quả X-Quang theo ID phiếu
     */
    public function getXrayResultData()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID']);
            exit();
        }

        try {
            $data = $this->phieuChupXquangModel->getById($id);
            if (!$data) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy']);
                return;
            }
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            error_log('getXrayResultData error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Upload ảnh X-Quang cho phiếu px.id
     */
    public function uploadXrayImages()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID phiếu']);
            return;
        }
        if (!isset($_FILES['images'])) {
            echo json_encode(['success' => false, 'message' => 'Không có file tải lên']);
            return;
        }
        $baseDir = __DIR__ . '/../uploads';
        $targetDir = dirname(__DIR__) . '/uploads/xray/' . $id;
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }
        $files = $_FILES['images'];
        $saved = [];
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            $tmp = $files['tmp_name'][$i];
            $name = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $files['name'][$i]);
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','gif'])) continue;
            $newName = uniqid('xray_', true) . '.' . $ext;
            $dest = $targetDir . '/' . $newName;
            if (move_uploaded_file($tmp, $dest)) {
                $url = './uploads/xray/' . $id . '/' . $newName;
                $saved[] = $url;
            }
        }
        echo json_encode(['success' => true, 'count' => count($saved), 'files' => $saved]);
    }

    /**
     * Lưu kết quả X-Quang vào bảng ket_qua_xquang
     */
    public function saveXrayResult()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }

        $id_phieu_chup_xquang = isset($input['id_phieu_chup_xquang']) ? (int)$input['id_phieu_chup_xquang'] : 0;
        if ($id_phieu_chup_xquang <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID phiếu chụp X-Quang']);
            return;
        }

        try {
            $db = require_once 'config/database.php';
            $database = new Database();
            $conn = $database->getConnection();

            // Kiểm tra xem đã có kết quả chưa
            $checkSql = "SELECT id FROM ket_qua_xquang WHERE id_phieu_chup_xquang = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([$id_phieu_chup_xquang]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Cập nhật kết quả hiện có
                $sql = "UPDATE ket_qua_xquang SET 
                        chuan_doan = ?, noi_dung = ?, ket_luan = ?, bac_si_xquang = ?, ngay_doc = NOW()
                        WHERE id_phieu_chup_xquang = ?";
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([
                    $input['chuan_doan'] ?? '',
                    $input['noi_dung'] ?? '',
                    $input['ket_luan'] ?? '',
                    $input['bac_si_xquang'] ?? '',
                    $id_phieu_chup_xquang
                ]);
            } else {
                // Tạo kết quả mới
                $sql = "INSERT INTO ket_qua_xquang (id_phieu_chup_xquang, chuan_doan, noi_dung, ket_luan, bac_si_xquang, ngay_doc) 
                        VALUES (?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([
                    $id_phieu_chup_xquang,
                    $input['chuan_doan'] ?? '',
                    $input['noi_dung'] ?? '',
                    $input['ket_luan'] ?? '',
                    $input['bac_si_xquang'] ?? ''
                ]);
            }

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Đã lưu kết quả X-Quang']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu kết quả']);
            }
        } catch (Exception $e) {
            error_log('saveXrayResult error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * In kết quả X-Quang
     */
    public function printXrayResult()
    {
        if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor')) {
            echo 'Unauthorized';
            return;
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo 'Thiếu ID';
            return;
        }

        try {
            $db = require_once 'config/database.php';
            $database = new Database();
            $conn = $database->getConnection();

            // Lấy dữ liệu kết quả X-Quang
            $sql = "SELECT kq.*, px.*, pk.ho_ten, pk.tuoi, pk.gioi_tinh, pk.dia_chi, pk.nam_sinh,
                           COALESCE(px.chan_doan_vao_vien, pk.chan_doan_vao_vien) as chan_doan_vao_vien,
                           bs.ten as bac_si_chi_dinh, ck.ten as khoa_chi_dinh
                    FROM ket_qua_xquang kq
                    JOIN phieu_chup_xquang px ON kq.id_phieu_chup_xquang = px.id
                    JOIN phieu_kham_benh pk ON px.id_phieu_kham_benh = pk.id
                    LEFT JOIN bac_si bs ON pk.bac_si_id = bs.id
                    LEFT JOIN chuyen_khoa ck ON bs.chuyen_khoa_id = ck.id
                    WHERE kq.id_phieu_chup_xquang = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                echo 'Không tìm thấy kết quả X-Quang';
                return;
            }

            // Include print view
            include 'Views/doctor/print_xray_result.php';
        } catch (Exception $e) {
            error_log('printXrayResult error: ' . $e->getMessage());
            echo 'Lỗi hệ thống';
        }
    }

    /**
     * Hoàn thành kết quả X-Quang (cập nhật trạng thái)
     */
    public function completeXrayResult()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID']);
            return;
        }

        $id = (int)$input['id'];
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            return;
        }

        try {
            $db = require_once 'config/database.php';
            $database = new Database();
            $conn = $database->getConnection();

            // Cập nhật trạng thái phiếu chụp X-Quang
            $sql = "UPDATE phieu_chup_xquang SET trang_thai = 'Hoàn thành' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([$id]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Đã hoàn thành kết quả X-Quang']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật trạng thái']);
            }
        } catch (Exception $e) {
            error_log('completeXrayResult error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lấy dữ liệu kết quả X-Quang đã lưu
     */
    public function getSavedXrayResult()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID']);
            return;
        }

        try {
            $db = require_once 'config/database.php';
            $database = new Database();
            $conn = $database->getConnection();

            // Lấy dữ liệu kết quả đã lưu
            $sql = "SELECT * FROM ket_qua_xquang WHERE id_phieu_chup_xquang = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                echo json_encode(['success' => true, 'data' => $data]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Chưa có dữ liệu kết quả']);
            }
        } catch (Exception $e) {
            error_log('getSavedXrayResult error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lưu ảnh X-Quang vào bảng ket_qua_xquang_hinh_anh
     */
    public function saveXrayImages()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['id_phieu_chup_xquang']) || !isset($input['images'])) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }

        $id_phieu_chup_xquang = (int)$input['id_phieu_chup_xquang'];
        $images = $input['images'];
        
        if ($id_phieu_chup_xquang <= 0 || empty($images)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu ảnh']);
            return;
        }

        try {
            $db = require_once 'config/database.php';
            $database = new Database();
            $conn = $database->getConnection();

            // Lấy ket_qua_id từ bảng ket_qua_xquang
            $sql = "SELECT id FROM ket_qua_xquang WHERE id_phieu_chup_xquang = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id_phieu_chup_xquang]);
            $ketQua = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ketQua) {
                echo json_encode(['success' => false, 'message' => 'Chưa có kết quả X-Quang']);
                return;
            }

            $ketQuaId = $ketQua['id'];
            $savedCount = 0;

            // Lưu từng ảnh vào bảng ket_qua_xquang_hinh_anh
            foreach ($images as $imageUrl) {
                $sql = "INSERT INTO ket_qua_xquang_hinh_anh (ket_qua_id, file_path, file_name, mime_type) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                
                $fileName = basename($imageUrl);
                $mimeType = 'image/' . strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                $result = $stmt->execute([
                    $ketQuaId,
                    $imageUrl,
                    $fileName,
                    $mimeType
                ]);

                if ($result) {
                    $savedCount++;
                }
            }

            echo json_encode(['success' => true, 'message' => 'Đã lưu ' . $savedCount . ' ảnh', 'count' => $savedCount]);
        } catch (Exception $e) {
            error_log('saveXrayImages error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lấy ảnh X-Quang đã lưu
     */
    public function getSavedXrayImages()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID']);
            return;
        }

        try {
            $db = require_once 'config/database.php';
            $database = new Database();
            $conn = $database->getConnection();

            // Lấy ảnh từ bảng ket_qua_xquang_hinh_anh
            $sql = "SELECT hinh.* FROM ket_qua_xquang_hinh_anh hinh
                    JOIN ket_qua_xquang kq ON hinh.ket_qua_id = kq.id
                    WHERE kq.id_phieu_chup_xquang = ?
                    ORDER BY hinh.ngay_tai ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            error_log('getSavedXrayImages error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Thống kê X-Quang cho dashboard hôm nay
     * - total_today: tổng phiếu tạo hôm nay
     * - completed_today: số phiếu trạng thái Hoàn thành (cập nhật hôm nay)
     * - waiting_count: số phiếu trạng thái Đã yêu cầu (không giới hạn ngày)
     */
    public function getXrayStatsToday()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor' && $_SESSION['user_role'] !== 'admin')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        try {
            $database = new Database();
            $db = $database->getConnection();

            // Optional date param (YYYY-MM-DD). Default today
            $date = isset($_GET['date']) && preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $_GET['date']) ? $_GET['date'] : null;
            if ($date === null) {
                $stmtDate = $db->query("SELECT CURDATE() AS d");
                $dateRow = $stmtDate->fetch(PDO::FETCH_ASSOC);
                $date = $dateRow ? $dateRow['d'] : date('Y-m-d');
            }

            // Tổng phiếu tạo theo ngày
            $stmt1 = $db->prepare("SELECT COUNT(*) AS c FROM phieu_chup_xquang WHERE DATE(ngay_tao) = ?");
            $stmt1->execute([$date]);
            $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
            $totalToday = (int)($row1['c'] ?? 0);

            // Hoàn thành theo ngày (lọc theo ngày tạo phiếu)
            $stmt2 = $db->prepare("SELECT COUNT(*) AS c FROM phieu_chup_xquang WHERE trang_thai = 'Hoàn thành' AND DATE(ngay_tao) = ?");
            $stmt2->execute([$date]);
            $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
            $completedToday = (int)($row2['c'] ?? 0);

            // Đang chờ chụp: trạng thái Đã yêu cầu theo ngày tạo
            $stmt3 = $db->prepare("SELECT COUNT(*) AS c FROM phieu_chup_xquang WHERE trang_thai = 'Đã yêu cầu' AND DATE(ngay_tao) = ?");
            $stmt3->execute([$date]);
            $row3 = $stmt3->fetch(PDO::FETCH_ASSOC);
            $waitingCount = (int)($row3['c'] ?? 0);

            echo json_encode([
                'success' => true,
                'data' => [
                    'total_today' => $totalToday,
                    'completed_today' => $completedToday,
                    'waiting_count' => $waitingCount,
                    'date' => $date,
                ]
            ]);
        } catch (Exception $e) {
            error_log('getXrayStatsToday error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lấy dữ liệu phiếu chụp X-Quang
     */
    public function getXrayFormData()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ
        if ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $id = $_GET['id'] ?? '';
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID phiếu chụp X-Quang']);
            exit();
        }

        $phieuChup = $this->phieuChupXquangModel->getById($id);
        
        if (!$phieuChup) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy phiếu chụp X-Quang']);
            exit();
        }

        echo json_encode([
            'success' => true,
            'data' => $phieuChup
        ]);
    }

    /**
     * Lịch sử X-Quang: lọc theo ngày (DATE(px.ngay_tao)) và tìm theo tên bệnh nhân
     */
    public function getXrayHistory()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor' && $_SESSION['user_role'] !== 'admin')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $date = isset($_GET['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date']) ? $_GET['date'] : null;
        $requestId = isset($_GET['request_id']) ? trim($_GET['request_id']) : '';
        $patientCode = isset($_GET['patient_code']) ? trim($_GET['patient_code']) : '';

        try {
            $database = new Database();
            $db = $database->getConnection();

            $sql = "SELECT px.id, px.trang_thai, px.ngay_tao, kq.ket_luan, kq.noi_dung,
                           pk.ho_ten, pk.tuoi, COALESCE(bn.gioi_tinh, pk.gioi_tinh) as gioi_tinh, bn.ma_benh_nhan
                    FROM phieu_chup_xquang px
                    JOIN phieu_kham_benh pk ON px.id_phieu_kham_benh = pk.id
                    JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                    LEFT JOIN ket_qua_xquang kq ON kq.id_phieu_chup_xquang = px.id";

            $conds = [];
            $params = [];
            $conds[] = 'px.trang_thai = ?'; $params[] = 'Hoàn thành'; // Chỉ hiển thị phiếu đã hoàn thành
            if ($date) { $conds[] = 'DATE(px.ngay_tao) = ?'; $params[] = $date; }
            if ($requestId !== '') { 
                // Search by request ID (px.id)
                if (is_numeric($requestId)) {
                    $conds[] = 'px.id = ?'; 
                    $params[] = $requestId; 
                } else {
                    $conds[] = 'px.id LIKE ?'; 
                    $params[] = '%'.$requestId.'%'; 
                }
            }
            if ($patientCode !== '') { 
                // Search by patient code (bn.ma_benh_nhan)
                $conds[] = 'bn.ma_benh_nhan LIKE ?'; 
                $params[] = '%'.$patientCode.'%'; 
            }
            $sql .= ' WHERE ' . implode(' AND ', $conds);
            $sql .= ' ORDER BY px.ngay_tao DESC, px.id DESC LIMIT 200';

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $rows]);
        } catch (Exception $e) {
            error_log('getXrayHistory error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lấy dữ liệu kết quả X-Quang đầy đủ để hiển thị modal xem phiếu (read-only)
     */
    public function getXrayResultView()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor' && $_SESSION['user_role'] !== 'admin')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0; // id px
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID']);
            return;
        }

        try {
            $database = new Database();
            $db = $database->getConnection();

            $sql = "SELECT px.id, px.ngay_tao, px.ngay_cap_nhat, px.trang_thai, px.yeu_cau_chup,
                           pk.ho_ten, pk.nam_sinh, pk.gioi_tinh, pk.dia_chi,
                           COALESCE(px.chan_doan_vao_vien, pk.chan_doan_vao_vien) as chan_doan_vao_vien, 
                           pk.ten_bac_si AS bac_si_chi_dinh,
                           kq.noi_dung, kq.ket_luan, kq.bac_si_xquang
                    FROM phieu_chup_xquang px
                    JOIN phieu_kham_benh pk ON px.id_phieu_kham_benh = pk.id
                    LEFT JOIN ket_qua_xquang kq ON kq.id_phieu_chup_xquang = px.id
                    WHERE px.id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(!$row){ echo json_encode(['success'=>false,'message'=>'Không tìm thấy']); return; }
            echo json_encode(['success'=>true,'data'=>$row]);
        } catch (Exception $e) {
            error_log('getXrayResultView error: '.$e->getMessage());
            echo json_encode(['success'=>false,'message'=>'Lỗi hệ thống']);
        }
    }

    /**
     * Lấy KQ X-Quang theo id phiếu khám bệnh để hiển thị trong modal khám bệnh (read-only)
     */
    public function getXrayResultByExamIdForView()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor' && $_SESSION['user_role'] !== 'admin')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $examId = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;
        if ($examId <= 0) { echo json_encode(['success'=>false,'message'=>'Thiếu exam id']); return; }

        try {
            $database = new Database();
            $db = $database->getConnection();
            $sql = "SELECT px.id, px.ngay_tao, px.ngay_cap_nhat, px.trang_thai,
                           pk.ho_ten, pk.nam_sinh, pk.gioi_tinh, pk.dia_chi,
                           COALESCE(px.chan_doan_vao_vien, pk.chan_doan_vao_vien) as chan_doan_vao_vien, 
                           pk.ten_bac_si AS bac_si_chi_dinh,
                           kq.noi_dung, kq.ket_luan, kq.bac_si_xquang
                    FROM phieu_chup_xquang px
                    JOIN phieu_kham_benh pk ON px.id_phieu_kham_benh = pk.id
                    LEFT JOIN ket_qua_xquang kq ON kq.id_phieu_chup_xquang = px.id
                    WHERE px.id_phieu_kham_benh = ?
                    ORDER BY px.id DESC LIMIT 1";
            $st = $db->prepare($sql);
            $st->execute([$examId]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if(!$row){ echo json_encode(['success'=>false,'message'=>'Chưa có kết quả X-Quang']); return; }
            echo json_encode(['success'=>true,'data'=>$row]);
        } catch (Exception $e) {
            error_log('getXrayResultByExamIdForView error: '.$e->getMessage());
            echo json_encode(['success'=>false,'message'=>'Lỗi hệ thống']);
        }
    }

    /**
     * Lấy KQ Siêu âm theo id phiếu khám bệnh để hiển thị trong modal khám bệnh (read-only)
     */
    public function getUltrasoundResultByExamIdForView()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'sieuam_doctor' && $_SESSION['user_role'] !== 'admin')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $examId = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;
        if ($examId <= 0) { echo json_encode(['success'=>false,'message'=>'Thiếu exam id']); return; }

        try {
            // Tìm phiếu yêu cầu siêu âm từ exam_id trước
            $database = new Database();
            $db = $database->getConnection();
            $sql = "SELECT pysa.id FROM phieu_yeu_cau_sieu_am pysa 
                    WHERE pysa.id_phieu_kham_benh = ? AND pysa.trang_thai = 'Hoàn thành'";
            $stmt = $db->prepare($sql);
            $stmt->execute([$examId]);
            $pysaRow = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$pysaRow) {
                echo json_encode(['success' => false, 'message' => 'Chưa có kết quả siêu âm']);
                return;
            }
            
            // Lấy kết quả siêu âm từ phieu_id
            $result = $this->ketQuaSieuAmModel->getByPhieuYeuCauId($pysaRow['id']);
            
            if ($result) {
                // Thêm thông tin bổ sung
                $result['noi_dung'] = $result['yeu_cau_sieu_am'];
                
                echo json_encode([
                    'success' => true,
                    'result' => $result
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Chưa có kết quả siêu âm']);
            }
        } catch (Exception $e) {
            error_log('getUltrasoundResultByExamIdForView error: '.$e->getMessage());
            echo json_encode(['success'=>false,'message'=>'Lỗi hệ thống']);
        }
    }

    /**
     * Lấy hình ảnh siêu âm đã lưu
     */
    public function getSavedUltrasoundImages()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'sieuam_doctor' && $_SESSION['user_role'] !== 'admin')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $resultId = isset($_GET['result_id']) ? (int)$_GET['result_id'] : 0;
        if ($resultId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thiếu result_id']);
            return;
        }

        try {
            $images = $this->sieuAmHinhAnhModel->getByKetQuaId($resultId);
            
            echo json_encode([
                'success' => true,
                'images' => $images
            ]);
        } catch (Exception $e) {
            error_log('getSavedUltrasoundImages error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lấy phiếu chụp X-Quang theo ID phiếu khám bệnh
     */
    public function getXrayFormByExamId()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ
        if ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $examId = $_GET['exam_id'] ?? '';
        
        if (empty($examId)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID phiếu khám bệnh']);
            exit();
        }

        // Tìm phiếu chụp X-Quang theo ID phiếu khám bệnh
        try {
            $phieuChup = $this->phieuChupXquangModel->getByExamId($examId);
        } catch (Exception $e) {
            error_log('Error getting X-Ray form by exam ID: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi khi lấy phiếu chụp X-Quang']);
            exit();
        }
        
        if (!$phieuChup) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy phiếu chụp X-Quang']);
            exit();
        }

        echo json_encode([
            'success' => true,
            'data' => $phieuChup
        ]);
    }

    /**
     * Lấy trạng thái BHYT của bệnh nhân
     */
    public function getPatientBhytStatus()
    {
        // Kiểm tra đăng nhập và quyền bác sĩ
        if ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'xray_doctor') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $examId = $_GET['exam_id'] ?? '';
        
        if (empty($examId)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID phiếu khám bệnh']);
            exit();
        }

        try {
            // Lấy đối tượng từ phiếu khám bệnh đã lưu
            $sql = "SELECT pk.doi_tuong_bhyt, pk.doi_tuong_thu_phi, pk.doi_tuong_mien, pk.doi_tuong_khac,
                           pk.so_the_bhyt, bn.id as benh_nhan_id, pk.id as phieu_kham_id
                    FROM phieu_kham_benh pk
                    JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                    WHERE pk.id = ?";
            
            // Get database connection
            require_once 'config/database.php';
            $database = new Database();
            $db = $database->getConnection();
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                echo json_encode(['success' => false, 'message' => 'Lỗi database']);
                exit();
            }

            $stmt->execute([$examId]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$patient) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin bệnh nhân']);
                exit();
            }

            // Kiểm tra đối tượng từ phiếu khám bệnh đã lưu
            $doiTuongBhyt = $patient['doi_tuong_bhyt'];
            $doiTuongThuPhi = $patient['doi_tuong_thu_phi'];
            $doiTuongMien = $patient['doi_tuong_mien'];
            $doiTuongKhac = $patient['doi_tuong_khac'];
            $soTheBhyt = $patient['so_the_bhyt'] ?? '';
            
            // Ưu tiên BHYT trước
            $hasBhyt = $doiTuongBhyt == 1;
            

            echo json_encode([
                'success' => true,
                'hasBhyt' => $hasBhyt,
                'soTheBhyt' => $soTheBhyt,
                'doiTuongBhyt' => $doiTuongBhyt,
                'doiTuongThuPhi' => $doiTuongThuPhi,
                'doiTuongMien' => $doiTuongMien,
                'doiTuongKhac' => $doiTuongKhac,
                'benhNhanId' => $patient['benh_nhan_id'],
                'phieuKhamId' => $patient['phieu_kham_id']
            ]);

        } catch (Exception $e) {
            error_log('Error getting patient BHYT status: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lưu phiếu yêu cầu siêu âm
     */
    public function saveUltrasoundForm()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            if (!isset($_POST['exam_id']) || empty($_POST['exam_id'])) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc']);
                return;
            }

            $examId = $_POST['exam_id'];
            $maBenhNhan = $_POST['ma_benh_nhan'] ?? '';
            $hoTen = $_POST['ho_ten'] ?? '';
            $tuoi = $_POST['tuoi'] ?? '';
            $gioiTinh = $_POST['gioi_tinh'] ?? '';
            $diaChi = $_POST['dia_chi'] ?? '';
            $doiTuong = $_POST['doi_tuong'] ?? '';
            $soTheBhyt = $_POST['so_the_bhyt'] ?? '';
            $phongKham = $_POST['phong_kham'] ?? '';
            $soDienThoai = $_POST['so_dien_thoai'] ?? '';
            $quanHuyen = $_POST['quan_huyen'] ?? '';
            $chanDoan = $_POST['chan_doan'] ?? '';
            $yeuCau = $_POST['yeu_cau'] ?? '';
            $bacSiKham = $_POST['bac_si_kham'] ?? '';
            $ngay = $_POST['ngay'] ?? '';
            $thang = $_POST['thang'] ?? '';
            $nam = $_POST['nam'] ?? '';
            $thoiGianYeuCau = date('Y-m-d H:i:s');

            // Kiểm tra yêu cầu siêu âm có hợp lệ không
            $validation = $this->phieuYeuCauSieuAmModel->validateUltrasoundRequests($yeuCau);
            if (!$validation['valid']) {
                echo json_encode(['success' => false, 'message' => $validation['message']]);
                return;
            }

            // Kiểm tra xem đã có phiếu siêu âm chưa
            $existingForm = $this->phieuYeuCauSieuAmModel->getByExamId($examId);

            $data = [
                'id_phieu_kham_benh' => $examId,
                'so_ho_so' => $maBenhNhan,  // Map ma_benh_nhan to so_ho_so
                'ho_ten' => $hoTen,
                'tuoi' => $tuoi,
                'gioi_tinh' => $gioiTinh,
                'doi_tuong' => $doiTuong,
                'so_the_bhyt' => $soTheBhyt,
                'phong_kham' => $phongKham,
                'chan_doan' => $chanDoan,
                'yeu_cau' => $yeuCau,
                'bac_si_kham' => $bacSiKham,
                'thoi_gian_yeu_cau' => $thoiGianYeuCau
            ];

            if ($existingForm) {
                // Cập nhật phiếu đã có
                $result = $this->phieuYeuCauSieuAmModel->update($existingForm['id'], $data);
                $message = 'Cập nhật phiếu yêu cầu siêu âm thành công';
            } else {
                // Tạo phiếu mới
                $result = $this->phieuYeuCauSieuAmModel->save($data);
                $message = 'Lưu phiếu yêu cầu siêu âm thành công';
            }

            if ($result) {
                echo json_encode(['success' => true, 'message' => $message]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu phiếu yêu cầu siêu âm']);
            }

        } catch (Exception $e) {
            error_log('Error saving ultrasound form: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lấy dữ liệu phiếu yêu cầu siêu âm theo exam ID
     */
    public function getUltrasoundFormData()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $examId = $_GET['exam_id'] ?? '';
            if (empty($examId)) {
                echo json_encode(['success' => false, 'message' => 'Exam ID không hợp lệ']);
                return;
            }

            $formData = $this->phieuYeuCauSieuAmModel->getByExamId($examId);
            
            if ($formData) {
                echo json_encode(['success' => true, 'data' => $formData]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy phiếu yêu cầu siêu âm']);
            }

        } catch (Exception $e) {
            error_log('Error getting ultrasound form data: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * In phiếu yêu cầu siêu âm
     */
    public function printUltrasoundForm()
    {
        try {
            $id = $_GET['id'] ?? '';
            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
                return;
            }

            $formData = $this->phieuYeuCauSieuAmModel->getById($id);
            if (!$formData) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy phiếu yêu cầu siêu âm']);
                return;
            }

            include 'Views/doctor/print_ultrasound_form.php';

        } catch (Exception $e) {
            error_log('Error printing ultrasound form: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lấy thống kê siêu âm
     */
    public function getUltrasoundStats()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $date = $_GET['date'] ?? date('Y-m-d');
            
            $database = new Database();
            $pdo = $database->getConnection();

            // Tổng yêu cầu hôm nay
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_today 
                FROM phieu_yeu_cau_sieu_am 
                WHERE DATE(ngay_tao) = ?
            ");
            $stmt->execute([$date]);
            $totalToday = $stmt->fetch(PDO::FETCH_ASSOC)['total_today'];

            // Đã siêu âm xong hôm nay
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as completed_today 
                FROM phieu_yeu_cau_sieu_am 
                WHERE DATE(ngay_tao) = ? AND trang_thai = 'Hoàn thành'
            ");
            $stmt->execute([$date]);
            $completedToday = $stmt->fetch(PDO::FETCH_ASSOC)['completed_today'];

            // Đang chờ siêu âm
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as pending 
                FROM phieu_yeu_cau_sieu_am 
                WHERE trang_thai = 'Đã yêu cầu'
            ");
            $stmt->execute();
            $pending = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];

            echo json_encode([
                'success' => true,
                'stats' => [
                    'total_today' => (int)$totalToday,
                    'completed_today' => (int)$completedToday,
                    'pending' => (int)$pending
                ]
            ]);

        } catch (Exception $e) {
            error_log('Error getting ultrasound stats: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lấy danh sách yêu cầu siêu âm
     */
    public function getUltrasoundRequests()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $date = $_GET['date'] ?? date('Y-m-d');
            $keyword = $_GET['name'] ?? '';
            
            $database = new Database();
            $pdo = $database->getConnection();

            $sql = "
                SELECT pysa.*, pk.ho_ten, pk.tuoi, COALESCE(bn.gioi_tinh, pk.gioi_tinh) as gioi_tinh, 
                       pk.chan_doan_vao_vien as chan_doan, bn.ma_benh_nhan
                FROM phieu_yeu_cau_sieu_am pysa
                JOIN phieu_kham_benh pk ON pysa.id_phieu_kham_benh = pk.id
                JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                WHERE pysa.trang_thai = 'Đã yêu cầu' AND DATE(pysa.ngay_tao) = ?
            ";
            $params = [$date];

            if (!empty($keyword)) {
                $sql .= " AND bn.ma_benh_nhan LIKE ?";
                $params[] = "%{$keyword}%";
            }

            $sql .= " ORDER BY pysa.ngay_tao DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'requests' => $requests
            ]);

        } catch (Exception $e) {
            error_log('Error getting ultrasound requests: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lấy kết quả siêu âm
     */
    public function getUltrasoundResult()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $id = $_GET['id'] ?? '';
            
            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
                return;
            }

            $result = $this->phieuYeuCauSieuAmModel->getById($id);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'result' => $result
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy kết quả siêu âm']);
            }

        } catch (Exception $e) {
            error_log('Error getting ultrasound result: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lưu kết quả siêu âm
     */
    public function saveUltrasoundResult()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $id = $_POST['id'] ?? '';
            $ketQua = $_POST['ket_qua'] ?? '';
            $ketLuan = $_POST['ket_luan'] ?? '';
            
            if (empty($id) || empty($ketQua) || empty($ketLuan)) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
                return;
            }

            $database = new Database();
            $pdo = $database->getConnection();

            // Cập nhật kết quả siêu âm
            $stmt = $pdo->prepare("
                UPDATE phieu_yeu_cau_sieu_am 
                SET ket_qua = ?, ket_luan = ?, trang_thai = 'Hoàn thành', ngay_cap_nhat = NOW()
                WHERE id = ?
            ");
            
            $result = $stmt->execute([$ketQua, $ketLuan, $id]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Lưu kết quả siêu âm thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu kết quả siêu âm']);
            }

        } catch (Exception $e) {
            error_log('Error saving ultrasound result: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lưu kết quả siêu âm với hình ảnh
     */
    public function saveSieuAmResult() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $phieuId = $_POST['phieu_id'] ?? null;
            $ketQuaKhaoSat = $_POST['ket_qua_khao_sat'] ?? '';
            $ketLuan = $_POST['ket_luan'] ?? '';
            $bacSiSieuAm = $_SESSION['user_name'] ?? '';
            
            if (!$phieuId || empty($ketQuaKhaoSat) || empty($ketLuan)) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc']);
                return;
            }
            
            // Kiểm tra xem đã có kết quả chưa
            $existingResult = $this->ketQuaSieuAmModel->getByPhieuYeuCauId($phieuId);
            
            $ketQuaData = [
                'id_phieu_yeu_cau_sieu_am' => $phieuId,
                'ket_qua_khao_sat' => $ketQuaKhaoSat,
                'ket_luan' => $ketLuan,
                'bac_si_sieu_am' => $bacSiSieuAm
            ];
            
            if ($existingResult) {
                // Cập nhật kết quả hiện có
                $result = $this->ketQuaSieuAmModel->update($existingResult['id'], $ketQuaData);
                $ketQuaId = $existingResult['id'];
                $message = 'Cập nhật kết quả siêu âm thành công';
            } else {
                // Tạo kết quả mới
                $ketQuaId = $this->ketQuaSieuAmModel->save($ketQuaData);
                $result = $ketQuaId !== false;
                $message = 'Lưu kết quả siêu âm thành công';
            }
            
            if ($result) {
                echo json_encode([
                    'success' => true, 
                    'message' => $message,
                    'ket_qua_id' => $ketQuaId
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu kết quả siêu âm']);
            }

        } catch (Exception $e) {
            error_log('Error saving sieu am result: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Upload hình ảnh siêu âm
     */
    public function uploadSieuAmImages() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $ketQuaId = $_POST['ket_qua_id'] ?? null;
            
            if (!$ketQuaId) {
                echo json_encode(['success' => false, 'message' => 'Thiếu ID kết quả siêu âm']);
                return;
            }
            
            if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
                echo json_encode(['success' => false, 'message' => 'Không có file ảnh nào được chọn']);
                return;
            }
            
            $uploadDir = 'uploads/sieuam/' . $ketQuaId . '/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $uploadedFiles = [];
            $imageDataArray = [];
            
            $files = $_FILES['images'];
            $fileCount = count($files['name']);
            
            for ($i = 0; $i < $fileCount; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $fileName = $files['name'][$i];
                    $fileTmp = $files['tmp_name'][$i];
                    $fileSize = $files['size'][$i];
                    $fileType = $files['type'][$i];
                    
                    // Tạo tên file unique
                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $uniqueFileName = uniqid() . '_' . time() . '.' . $extension;
                    $filePath = $uploadDir . $uniqueFileName;
                    
                    if (move_uploaded_file($fileTmp, $filePath)) {
                        $imageDataArray[] = [
                            'ten_file' => $fileName,
                            'duong_dan' => $filePath,
                            'kich_thuoc' => $fileSize,
                            'loai_file' => $fileType
                        ];
                        
                        $uploadedFiles[] = [
                            'original_name' => $fileName,
                            'saved_path' => $filePath,
                            'size' => $fileSize
                        ];
                    }
                }
            }
            
            if (!empty($imageDataArray)) {
                $savedIds = $this->sieuAmHinhAnhModel->saveMultiple($ketQuaId, $imageDataArray);
                
                if ($savedIds) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Upload ' . count($uploadedFiles) . ' ảnh thành công',
                        'uploaded_files' => $uploadedFiles,
                        'saved_ids' => $savedIds
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu thông tin ảnh vào database']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Không có ảnh nào được upload thành công']);
            }

        } catch (Exception $e) {
            error_log('Error uploading sieu am images: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lấy hình ảnh siêu âm theo ID kết quả
     */
    public function getSieuAmImages() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $ketQuaId = $_GET['ket_qua_id'] ?? $_GET['result_id'] ?? null;
            
            if (!$ketQuaId) {
                echo json_encode(['success' => false, 'message' => 'Thiếu ID kết quả siêu âm']);
                return;
            }
            
            $images = $this->sieuAmHinhAnhModel->getByKetQuaId($ketQuaId);
            
            if ($images !== false) {
                echo json_encode([
                    'success' => true, 
                    'images' => $images
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi lấy danh sách ảnh']);
            }

        } catch (Exception $e) {
            error_log('Error getting sieu am images: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lấy kết quả siêu âm theo ID phiếu yêu cầu
     */
    public function getSieuAmResult() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $phieuId = $_GET['phieu_id'] ?? null;
            
            if (!$phieuId) {
                echo json_encode(['success' => false, 'message' => 'Thiếu ID phiếu yêu cầu']);
                return;
            }
            
            $result = $this->ketQuaSieuAmModel->getByPhieuYeuCauId($phieuId);
            
            if ($result) {
                // Lấy thêm hình ảnh nếu có
                $images = $this->sieuAmHinhAnhModel->getByKetQuaId($result['id']);
                
                echo json_encode([
                    'success' => true, 
                    'result' => $result,
                    'images' => $images ?: []
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy kết quả siêu âm']);
            }

        } catch (Exception $e) {
            error_log('Error getting sieu am result: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Xóa hình ảnh siêu âm
     */
    public function deleteSieuAmImage() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $imageId = $_POST['image_id'] ?? null;
            
            if (!$imageId) {
                echo json_encode(['success' => false, 'message' => 'Thiếu ID hình ảnh']);
                return;
            }
            
            $result = $this->sieuAmHinhAnhModel->delete($imageId);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Xóa ảnh thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa ảnh']);
            }

        } catch (Exception $e) {
            error_log('Error deleting sieu am image: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lấy lịch sử siêu âm
     */
    public function getSieuamHistory() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $date = $_GET['date'] ?? null;
            $requestId = $_GET['request_id'] ?? null;
            $patientCode = $_GET['patient_code'] ?? null;
            
            $history = $this->getSieuamHistoryData($date, $requestId, $patientCode);
            
            echo json_encode([
                'success' => true,
                'history' => $history
            ]);
            
        } catch (Exception $e) {
            error_log('Error getting sieuam history: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi hệ thống'
            ]);
        }
    }

    /**
     * Lấy dữ liệu lịch sử siêu âm
     */
    private function getSieuamHistoryData($date = null, $requestId = null, $patientCode = null) {
        $sql = "SELECT kq.*, kq.bac_si_sieu_am, pysa.id as phieu_id, pysa.yeu_cau as yeu_cau_sieu_am, pysa.chan_doan,
                       pk.ho_ten, pk.tuoi, pk.gioi_tinh, pk.dia_chi, pk.ten_bac_si,
                       bn.ma_benh_nhan
                FROM ket_qua_sieu_am kq
                JOIN phieu_yeu_cau_sieu_am pysa ON kq.id_phieu_yeu_cau_sieu_am = pysa.id
                JOIN phieu_kham_benh pk ON pysa.id_phieu_kham_benh = pk.id
                JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                WHERE pysa.trang_thai = 'Hoàn thành'";
        
        $params = [];
        
        if ($date) {
            $sql .= " AND DATE(kq.ngay_tao) = :date";
            $params[':date'] = $date;
        }
        
        if ($requestId) {
            $sql .= " AND pysa.id = :request_id";
            $params[':request_id'] = $requestId;
        }
        
        if ($patientCode) {
            $sql .= " AND bn.ma_benh_nhan LIKE :patient_code";
            $params[':patient_code'] = '%' . $patientCode . '%';
        }
        
        $sql .= " ORDER BY kq.ngay_tao DESC";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin kết quả siêu âm để xem
     */
    public function getSieuamResultView() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $resultId = $_GET['result_id'] ?? null;
            
            if (!$resultId) {
                echo json_encode(['success' => false, 'message' => 'Thiếu ID kết quả']);
                return;
            }
            
            $result = $this->ketQuaSieuAmModel->getById($resultId);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'result' => $result
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy kết quả siêu âm']);
            }
            
        } catch (Exception $e) {
            error_log('Error getting sieuam result view: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }


    /**
     * Hoàn thành phiếu siêu âm - cập nhật trạng thái thành "Hoàn thành"
     */
    public function completeSieuAmResult() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $phieuId = $_POST['phieu_id'] ?? null;
            
            if (!$phieuId) {
                echo json_encode(['success' => false, 'message' => 'Thiếu ID phiếu yêu cầu siêu âm']);
                return;
            }
            
            // Cập nhật trạng thái của phiếu yêu cầu siêu âm
            $sql = "UPDATE phieu_yeu_cau_sieu_am SET trang_thai = 'Hoàn thành' WHERE id = :phieu_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':phieu_id', $phieuId);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Hoàn thành phiếu siêu âm thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật trạng thái phiếu']);
            }

        } catch (Exception $e) {
            error_log('Error completing sieu am result: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lưu phiếu yêu cầu xét nghiệm
     */
    public function saveLabForm()
    {
        try {
            // Debug: Log all POST data
            error_log('saveLabForm POST data: ' . print_r($_POST, true));
            
            // Prepare data for Model
            $data = [
                'exam_id' => $_POST['exam_id'] ?? '',
                'so_ho_so' => $_POST['so_ho_so'] ?? '',
                'ho_ten' => $_POST['ho_ten'] ?? '',
                'tuoi' => $_POST['tuoi'] ?? '',
                'gioi_tinh' => $_POST['gioi_tinh'] ?? '',
                'doi_tuong' => $_POST['doi_tuong'] ?? '',
                'so_the_bhyt' => $_POST['so_the_bhyt'] ?? '',
                'phong_kham' => $_POST['phong_kham'] ?? '',
                'chan_doan' => $_POST['chan_doan'] ?? '',
                'yeu_cau' => $_POST['yeu_cau'] ?? '',
                'bac_si_kham' => $_POST['bac_si_kham'] ?? '',
                'ngay' => $_POST['ngay'] ?? '',
                'thang' => $_POST['thang'] ?? '',
                'nam' => $_POST['nam'] ?? ''
            ];

            // Use Model to handle business logic
            $result = $this->labTestModel->saveLabForm($data);
            echo json_encode($result);

        } catch (Exception $e) {
            error_log('Error saving lab form: ' . $e->getMessage());
            error_log('Error trace: ' . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }

    /**
     * Lấy dữ liệu phiếu xét nghiệm
     */
    public function getLabFormData()
    {
        try {
            $examId = $_GET['exam_id'] ?? '';

            // Use Model to handle data retrieval
            $result = $this->labTestModel->getLabFormData($examId);
            echo json_encode($result);

        } catch (Exception $e) {
            error_log('Error getting lab form data: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * In phiếu xét nghiệm
     */
    public function printLabForm()
    {
        try {
            $formId = $_GET['id'] ?? '';

            if (empty($formId)) {
                echo json_encode(['success' => false, 'message' => 'Thiếu thông tin phiếu']);
                return;
            }

            $stmt = $this->db->prepare("
                SELECT pxn.*, pkb.ngay_kham, pkb.gio_kham
                FROM phieu_yeu_cau_xet_nghiem pxn
                JOIN phieu_kham_benh pkb ON pxn.id_phieu_kham_benh = pkb.id
                WHERE pxn.id = ?
            ");
            $stmt->execute([$formId]);
            $formData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$formData) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy phiếu xét nghiệm']);
                return;
            }

            // Tạo view để in
            include 'Views/doctor/print_lab_form.php';

        } catch (Exception $e) {
            error_log('Error printing lab form: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lấy gợi ý xét nghiệm
     */
    public function getLabSuggestions()
    {
        try {
            $query = $_POST['keyword'] ?? '';

            if (empty($query)) {
                echo json_encode(['success' => true, 'data' => []]);
                return;
            }

            $stmt = $this->db->prepare("
                SELECT * FROM xet_nghiem_suggestions 
                WHERE ten_goi_y LIKE ? AND trang_thai = 1 
                ORDER BY thu_tu ASC, ten_goi_y ASC 
                LIMIT 10
            ");
            $searchTerm = '%' . $query . '%';
            $stmt->execute([$searchTerm]);
            $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $suggestions]);

        } catch (Exception $e) {
            error_log('Error getting lab suggestions: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lấy kết quả xét nghiệm theo phiếu khám
     */
    public function getLabResultByExam()
    {
        try {
            $examId = $_GET['exam_id'] ?? '';

            if (empty($examId)) {
                echo json_encode(['success' => false, 'message' => 'Thiếu thông tin phiếu khám']);
                return;
            }

            $stmt = $this->db->prepare("
                SELECT pxn.*, kqxn.ket_qua_khao_sat, kqxn.ket_luan, kqxn.bac_si_xet_nghiem, kqxn.ngay_tao as ngay_ket_qua
                FROM phieu_yeu_cau_xet_nghiem pxn
                LEFT JOIN ket_qua_xet_nghiem kqxn ON pxn.id = kqxn.id_phieu_yeu_cau_xet_nghiem
                WHERE pxn.id_phieu_kham_benh = ?
            ");
            $stmt->execute([$examId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                echo json_encode(['success' => true, 'result' => $result]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Chưa có kết quả xét nghiệm']);
            }

        } catch (Exception $e) {
            error_log('Error getting lab result: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }
}