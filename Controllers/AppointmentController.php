<?php
require_once 'Models/Appointment.php';
require_once 'Models/Doctor.php';
require_once 'Models/Patient.php';
require_once 'Controllers/AuthController.php';

class AppointmentController
{
    private $appointmentModel;
    private $doctorModel;
    private $patientModel;
    private $auth;

    public function __construct()
    {
        $this->appointmentModel = new Appointment();
        $this->doctorModel = new Doctor();
        $this->patientModel = new Patient();
        $this->auth = new AuthController();
    }

    /**
     * Hiển thị trang đặt lịch khám tại bệnh viện
     */
    public function hospitalAppointment()
    {
        // Kiểm tra đăng nhập
        $this->auth->requireAuth('patient');

        $encryptedDoctorId = $_GET['doctor_id'] ?? null;
        $doctorId = null;
        $doctor = null;
        $schedules = [];
        $availableTimeSlots = [];

        // Giải mã doctor_id nếu có
        if ($encryptedDoctorId) {
            try {
                // Giải mã base64 và thêm salt để bảo mật
                $decoded = base64_decode($encryptedDoctorId);
                $doctorId = intval($decoded);

                // Kiểm tra ID hợp lệ
                if ($doctorId > 0) {
                    $doctor = $this->doctorModel->getById($doctorId);
                    if ($doctor) {
                        $schedules = $this->doctorModel->getSchedules($doctorId) ?: [];
                    }
                }
            } catch (Exception $e) {
                // Nếu giải mã thất bại, không hiển thị thông tin bác sĩ
                $doctorId = null;
                $doctor = null;
            }
        }

        // Lấy danh sách chuyên khoa
        $specialties = $this->appointmentModel->getSpecialties();

        $page_title = 'Đặt lịch khám tại bệnh viện';

        // Render trang độc lập không dùng main layout
        include 'Views/appointment/hospital_appointment.php';
    }

    /**
     * Hiển thị trang đặt lịch tư vấn trực tuyến 
     */
    public function consultationBooking()
    {
        // Kiểm tra đăng nhập
        $this->auth->requireAuth('patient');

        $encryptedDoctorId = $_GET['doctor_id'] ?? null;
        $doctorId = null;
        $doctor = null;
        $schedules = [];

        if ($encryptedDoctorId) {
            try {
                $decoded = base64_decode($encryptedDoctorId);
                $doctorId = intval($decoded);
                if ($doctorId > 0) {
                    $doctor = $this->doctorModel->getById($doctorId);
                    if ($doctor) {
                        $schedules = $this->doctorModel->getSchedules($doctorId) ?: [];
                    }
                }
            } catch (Exception $e) {
                $doctorId = null;
                $doctor = null;
            }
        }

        // Render trang đặt lịch tư vấn trực tuyến
        include 'Views/appointment/consultation_booking.php';
    }

    /**
     * Lấy danh sách bác sĩ theo chuyên khoa (AJAX)
     */
    public function getDoctorsBySpecialty()
    {
        header('Content-Type: application/json');

        $specialty = $_POST['specialty'] ?? '';
        $doctors = $this->appointmentModel->getDoctorsBySpecialty($specialty);

        echo json_encode([
            'success' => true,
            'data' => $doctors
        ]);
        exit();
    }

    /**
     * Lấy lịch làm việc của bác sĩ (AJAX)
     */
    public function getDoctorSchedule()
    {
        header('Content-Type: application/json');

        $doctorId = $_POST['doctor_id'] ?? '';

        if (empty($doctorId)) {
            echo json_encode([
                'success' => false,
                'message' => 'Thiếu thông tin bác sĩ'
            ]);
            exit();
        }

        $schedules = $this->doctorModel->getSchedules($doctorId);

        echo json_encode([
            'success' => true,
            'data' => $schedules
        ]);
        exit();
    }

    /**
     * Lấy khung giờ có sẵn của bác sĩ trong ngày (AJAX)
     */
    public function getAvailableTimeSlots()
    {
        header('Content-Type: application/json');

        $doctorId = $_POST['doctor_id'] ?? '';
        $date = $_POST['date'] ?? '';

        if (empty($doctorId) || empty($date)) {
            echo json_encode([
                'success' => false,
                'message' => 'Thiếu thông tin bác sĩ hoặc ngày'
            ]);
            exit();
        }

        // Kiểm tra ngày không được là ngày quá khứ
        $today = date('Y-m-d');
        $now = date('H:i');
        $selectedDate = date('Y-m-d', strtotime($date));

        if ($selectedDate < $today) {
            echo json_encode([
                'success' => false,
                'message' => "Không thể đặt lịch cho ngày quá khứ! Hôm nay: $today, chọn: $selectedDate"
            ]);
            exit();
        }

        // Trả về kèm trạng thái disabled để UI hiển thị slot đã được đặt
        $timeSlots = $this->appointmentModel->getAvailableTimeSlots($doctorId, $date, true);

        // Xác định cửa sổ tháng dựa trên mốc ngày 23:
        // - Trước ngày 23: 23/tháng trước → hết tháng hiện tại
        // - Từ ngày 23: 23/tháng này → hết tháng kế tiếp
        $openDay = 23;
        $anchor = new DateTime(date('Y-m-01'));
        $anchor->setDate((int)$anchor->format('Y'), (int)$anchor->format('m'), $openDay);
        if (new DateTime($today) < $anchor) {
            $bookingStart = (clone $anchor)->modify('-1 month');
            $bookingEnd = (clone $anchor)->modify('last day of this month');
        } else {
            $bookingStart = clone $anchor;
            $bookingEnd = (clone $anchor)->modify('+1 month')->modify('last day of this month');
        }

        // Bổ sung quy tắc hiển thị theo tuần cho bệnh nhân (1 hoặc 2 tuần):
        // - Thứ 2 (Mon): chỉ tuần này (đến Chủ nhật tuần này, 23:59)
        // - Thứ 3 → CN: tuần này + tuần sau (đến Chủ nhật tuần sau, 23:59)
        $todayObj = new DateTime($today);
        $weekStart = clone $todayObj;
        $weekStart->modify('monday this week');
        $weekEnd = clone $weekStart;
        $weekEnd->modify('sunday this week');
        $dow = (int)$todayObj->format('N'); // 1=Mon..7=Sun
        $weeklyEnd = ($dow === 1) ? (clone $weekEnd) : (clone $weekEnd)->modify('+7 days');
        $weeklyEnd->setTime(23, 59, 59);

        // Cửa sổ cuối cùng cho bệnh nhân là giao giữa (today..bookingEnd theo tháng) và (today..weeklyEnd)
        $finalStart = max($todayObj->getTimestamp(), $bookingStart->getTimestamp());
        $finalEnd = min($bookingEnd->getTimestamp(), $weeklyEnd->getTimestamp());

        $selDateObj = DateTime::createFromFormat('Y-m-d', $selectedDate);
        if (!$selDateObj || $selDateObj->getTimestamp() < $finalStart || $selDateObj->getTimestamp() > $finalEnd) {
            // Ngoài phạm vi cho phép → trả về rỗng để UI ẩn ngày và không hiển thị slot
            $timeSlots = [];
        }

        // Nếu chọn ngày hôm nay, đánh dấu disabled cho các giờ đã qua
        if ($selectedDate == $today && !empty($timeSlots)) {
            $currentTime = strtotime($now);
            foreach ($timeSlots as &$slot) {
                $slotTime = strtotime($slot['time']);
                if ($slotTime <= $currentTime) {
                    $slot['disabled'] = true;
                }
            }
            unset($slot);
        }

        echo json_encode([
            'success' => true,
            'data' => $timeSlots
        ]);
        exit();
    }

    /**
     * Kiểm tra xung đột lịch hẹn (AJAX)
     */
    public function checkConflict()
    {
        header('Content-Type: application/json');

        $doctorId = $_POST['doctor_id'] ?? '';
        $date = $_POST['date'] ?? '';
        $time = $_POST['time'] ?? '';

        if (empty($doctorId) || empty($date) || empty($time)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin kiểm tra']);
            exit();
        }

        $conflict = $this->appointmentModel->checkConflict($doctorId, $date, $time);
        echo json_encode(['success' => !$conflict, 'conflict' => $conflict]);
        exit();
    }

    /**
     * Đặt lịch hẹn mới
     */
    public function bookAppointment()
    {
        // Kiểm tra đăng nhập
        $this->auth->requireAuth('patient');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ./hospital_appointment");
            exit();
        }

        $patientId = $_SESSION['user_id'];
        $doctorId = $_POST['doctor_id'] ?? '';
        $date = $_POST['date'] ?? '';
        $time = $_POST['time'] ?? '';
        $reason = $_POST['reason'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $loaiLich = $_POST['loai_lich'] ?? 'Trực tiếp';
        $linkTuVan = $_POST['link_tu_van'] ?? '';

        // Validation
        if (empty($doctorId) || empty($date) || empty($time)) {
            $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin bắt buộc!";
            header("Location: ./hospital_appointment?doctor_id=" . $doctorId);
            exit();
        }

        // Kiểm tra ngày không được là ngày quá khứ
        $today = date('Y-m-d');
        $now = date('H:i');
        $selectedDate = date('Y-m-d', strtotime($date));
        $selectedTime = $time;


        // Kiểm tra ngày quá khứ
        if ($selectedDate < $today) {
            $_SESSION['error'] = "Không thể đặt lịch cho ngày quá khứ! Ngày hôm nay: $today, ngày chọn: $selectedDate";
            header("Location: ./hospital_appointment?doctor_id=" . $doctorId);
            exit();
        }

        // Kiểm tra nếu chọn ngày hôm nay thì giờ phải sau giờ hiện tại
        if ($selectedDate == $today) {
            $currentTime = strtotime($now);
            $appointmentTime = strtotime($selectedTime);

            if ($appointmentTime <= $currentTime) {
                $_SESSION['error'] = "Không thể đặt lịch cho thời gian đã qua! Giờ hiện tại: $now, giờ chọn: $selectedTime";
                header("Location: ./hospital_appointment?doctor_id=" . $doctorId);
                exit();
            }
        }

        // Kiểm tra xung đột lịch hẹn (chỉ chặn khi cùng ngày + cùng giờ + cùng bác sĩ,
        // và trạng thái lịch hẹn hiện có thuộc 'Chờ xác nhận' hoặc 'Đã xác nhận')
        if ($this->appointmentModel->checkConflict($doctorId, $date, $time)) {
            $date_vn = date('d-m-Y', strtotime($date));
            $_SESSION['error'] = "Khung giờ $time ngày $date_vn đã được đặt cho bác sĩ này. Vui lòng chọn thời gian khác!";
            header("Location: ./hospital_appointment?doctor_id=" . $doctorId);
            exit();
        }

        // Tạo lịch hẹn 
        $appointmentData = [
            'benh_nhan_id' => $patientId,
            'bac_si_id' => $doctorId,
            'ngay_hen' => $date,
            'gio_hen' => $time,
            'ly_do' => $reason,
            'loai_lich' => $loaiLich,
            'trang_thai' => 'Chờ xác nhận',
            'ghi_chu' => $notes,
            'link_tu_van' => $linkTuVan,
            'dia_chi_kham' => null // Tư vấn trực tuyến không cần địa chỉ
        ];

        $appointmentId = $this->appointmentModel->create($appointmentData);

        if ($appointmentId) {
            // Emit socket event to notify doctor about new appointment
            $this->emitNewAppointmentNotification($doctorId, $patientId, $date, $time);

            // Also notify patient about successful booking
            $this->emitPatientBookingConfirmation($patientId, $doctorId, $date, $time);

            // Send email to doctor about new booking (non-blocking best-effort)
            try {
                require_once 'Services/MailService.php';
                $doctor = $this->doctorModel->getById((int)$doctorId);
                $patient = $this->patientModel->getById((int)$patientId);
                if ($doctor && $patient) {
                    $mailer = new MailService();
                    $zoomLinkMaybe = ($loaiLich === 'Tư vấn' && !empty($linkTuVan)) ? $linkTuVan : null;
                    $mailer->sendAppointmentBookedToDoctor($doctor, $patient, $date, $time, $loaiLich, $zoomLinkMaybe);
                }
            } catch (Exception $e) {
                error_log('Mail (new booking to doctor) error: ' . $e->getMessage());
            }

            $_SESSION['success'] = "Đặt lịch hẹn thành công! Bác sĩ sẽ xác nhận lịch hẹn của bạn.";
            header("Location: ./patient_appointments");
            exit();
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi đặt lịch hẹn. Vui lòng thử lại!";
            header("Location: ./hospital_appointment?doctor_id=" . $doctorId);
            exit();
        }
    }

    /**
     * Hiển thị lịch hẹn của bệnh nhân
     */
    public function patientAppointments()
    {
        // Kiểm tra đăng nhập
        $this->auth->requireAuth('patient');

        $patientId = $_SESSION['user_id'];

        // Lấy tất cả lịch hẹn của bệnh nhân
        $allAppointments = $this->appointmentModel->getByPatientId($patientId) ?: [];

        // Phân loại appointments theo trạng thái
        $upcomingAppointments = [];
        $completedAppointments = [];
        $cancelledAppointments = [];

        foreach ($allAppointments as $appointment) {
            switch ($appointment['trang_thai']) {
                case 'Chờ xác nhận':
                case 'Đã xác nhận':
                    $upcomingAppointments[] = $appointment;
                    break;
                case 'Hoàn thành':
                    $completedAppointments[] = $appointment;
                    break;
                case 'hủy':
                    $cancelledAppointments[] = $appointment;
                    break;
                default:
                    $upcomingAppointments[] = $appointment;
                    break;
            }
        }

        // Include trực tiếp file view với dữ liệu
        include 'Views/patient/appointments.php';
    }

    /**
     * Hủy lịch hẹn
     */
    public function cancelAppointment()
    {
        // Kiểm tra đăng nhập
        $this->auth->requireAuth('patient');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ./patient_appointments");
            exit();
        }

        $appointmentId = $_POST['appointment_id'] ?? '';
        $patientId = $_SESSION['user_id'];

        if (empty($appointmentId)) {
            $_SESSION['error'] = "ID lịch hẹn không hợp lệ!";
            header("Location: ./patient_appointments");
            exit();
        }

        // Kiểm tra lịch hẹn có thuộc về bệnh nhân này không
        $appointment = $this->appointmentModel->getById($appointmentId);
        if (!$appointment || $appointment['benh_nhan_id'] != $patientId) {
            $_SESSION['error'] = "Không tìm thấy lịch hẹn!";
            header("Location: ./patient_appointments");
            exit();
        }

        // Chỉ cho phép hủy lịch hẹn chưa hoàn thành
        if (in_array($appointment['trang_thai'], ['Hoàn thành', 'hủy'])) {
            $_SESSION['error'] = "Không thể hủy lịch hẹn đã hoàn thành hoặc đã hủy!";
            header("Location: ./patient_appointments");
            exit();
        }

        // Cập nhật trạng thái thành hủy
        if ($this->appointmentModel->updateStatus($appointmentId, 'hủy', 'Bệnh nhân hủy lịch hẹn')) {
            // Lấy lại lịch hẹn để đọc thời điểm cập nhật (ngày/giờ hủy)
            $fresh = $this->appointmentModel->getById($appointmentId);
            $cancelDate = isset($fresh['ngay_cap_nhat']) ? date('Y-m-d', strtotime($fresh['ngay_cap_nhat'])) : date('Y-m-d');
            $cancelTime = isset($fresh['ngay_cap_nhat']) ? date('H:i', strtotime($fresh['ngay_cap_nhat'])) : date('H:i');
            // Emit socket notification for patient cancellation
            $this->emitPatientCancellationNotification($appointment);

            // Send email to doctor about patient cancellation
            try {
                require_once 'Services/MailService.php';
                $doctorModel = new Doctor();
                $doctor = $doctorModel->getById($appointment['bac_si_id']);
                $patient = $this->patientModel->getById($patientId);
                if ($doctor && $patient) {
                    $mailer = new MailService();
                    $mailer->sendPatientCancelledToDoctor(
                        $doctor,
                        $patient,
                        $appointment['ngay_hen'],
                        $appointment['gio_hen'],
                        $appointment['loai_lich'] ?? 'Trực tiếp',
                        $cancelDate,
                        $cancelTime
                    );
                }
            } catch (Exception $e) {
                error_log('Mail (patient cancelled -> doctor) error: ' . $e->getMessage());
            }

            $_SESSION['success'] = "Hủy lịch hẹn thành công!";
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi hủy lịch hẹn!";
        }

        header("Location: ./patient_appointments");
        exit();
    }

    /**
     * Emit socket notification for new appointment
     */
    private function emitNewAppointmentNotification($doctorId, $patientId, $date, $time)
    {
        try {
            // Get patient information
            $patientModel = new Patient();
            $patient = $patientModel->getById($patientId);
            $patientName = $patient ? $patient['ten'] : 'Bệnh nhân';

            // Prepare notification data
            $dateVn = $date;
            try {
                $dt = DateTime::createFromFormat('Y-m-d', $date);
                if ($dt) {
                    $dateVn = $dt->format('d-m-Y');
                }
            } catch (Exception $e) {
            }
            $notificationData = [
                'doctorId' => $doctorId,
                'patientId' => $patientId,
                'patientName' => $patientName,
                'appointmentDate' => $date,
                'appointmentTime' => $time,
                'message' => "Bệnh nhân $patientName đã đặt lịch hẹn vào $dateVn lúc $time",
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // Send HTTP request to socket server
            require_once 'Services/SocketService.php';
            SocketService::emit('new_appointment', $notificationData);
        } catch (Exception $e) {
            error_log("Socket notification error: " . $e->getMessage());
        }
    }

    /**
     * Emit socket notification for patient booking confirmation
     */
    private function emitPatientBookingConfirmation($patientId, $doctorId, $date, $time)
    {
        try {
            // Get doctor information
            $doctorModel = new Doctor();
            $doctor = $doctorModel->getById($doctorId);
            $doctorName = $doctor ? $doctor['ten'] : 'Bác sĩ';

            // Prepare notification data
            $dateVn = $date;
            try {
                $dt = DateTime::createFromFormat('Y-m-d', $date);
                if ($dt) {
                    $dateVn = $dt->format('d-m-Y');
                }
            } catch (Exception $e) {
            }
            $notificationData = [
                'patientId' => $patientId,
                'doctorId' => $doctorId,
                'doctorName' => $doctorName,
                'appointmentDate' => $date,
                'appointmentTime' => $time,
                'message' => "Bạn đã đặt lịch hẹn thành công với bác sĩ $doctorName vào $dateVn lúc $time",
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // Send HTTP request to socket server
            require_once 'Services/SocketService.php';
            SocketService::emit('patient_booking_confirmation', $notificationData);
        } catch (Exception $e) {
            error_log("Patient booking confirmation error: " . $e->getMessage());
        }
    }

    /**
     * Emit socket notification for patient cancellation
     */
    private function emitPatientCancellationNotification($appointment)
    {
        try {
            $patientId = $appointment['benh_nhan_id'];
            $doctorId = $appointment['bac_si_id'];

            // Get doctor and patient information
            require_once 'Models/Doctor.php';
            $doctorModel = new Doctor();
            $doctor = $doctorModel->getById($doctorId);
            $doctorName = $doctor ? $doctor['ten'] : 'Bác sĩ';

            $patientModel = new Patient();
            $patient = $patientModel->getById($patientId);
            $patientName = $patient ? $patient['ten'] : 'Bệnh nhân';

            // Prepare notification data
            $notificationData = [
                'appointmentId' => $appointment['id'],
                'patientId' => $patientId,
                'doctorId' => $doctorId,
                'patientName' => $patientName,
                'doctorName' => $doctorName,
                'appointmentDate' => $appointment['ngay_hen'],
                'appointmentTime' => $appointment['gio_hen'],
                'message' => "Bệnh nhân $patientName đã hủy lịch hẹn vào {$appointment['ngay_hen']} lúc {$appointment['gio_hen']}",
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // Send notification to doctor
            require_once 'Services/SocketService.php';
            SocketService::emit('appointment_cancelled_by_patient', $notificationData);
        } catch (Exception $e) {
            error_log("Patient cancellation notification error: " . $e->getMessage());
        }
    }
}