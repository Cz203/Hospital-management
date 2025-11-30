<?php

require_once 'Models/Admin.php';
require_once 'Models/Doctor.php';
require_once 'Models/Specialty.php';
require_once 'Models/Patient.php';
require_once 'Models/Reception.php';

class AdminController
{
    private $auth;
    private $adminModel;
    private $doctorModel;
    private $specialtyModel;
    private $patientModel;
    private $receptionModel;

    public function __construct()
    {
        $this->auth = new AuthController();
        $this->adminModel = new Admin();
        $this->doctorModel = new Doctor();
        $this->specialtyModel = new Specialty();
        $this->patientModel = new Patient();
        $this->receptionModel = new Reception();
    }

    /**
     * Dashboard admin
     */
    public function dashboard()
    {
        $this->auth->requireAuth('admin');

        // Lấy thống kê tổng quan
        $stats = $this->getDashboardStats();

        $page_title = 'Dashboard Admin';

        // Start output buffering để lấy content
        ob_start();
        include 'Views/admin/dashboard.php';
        $content = ob_get_clean();

        // Sử dụng renderLayout function
        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, $page_title);
    }

    /**
     * Quản lý lịch làm việc bác sĩ
     */
    public function manageDoctorSchedules()
    {
        $this->auth->requireAuth('admin');

        // Lấy danh sách tất cả bác sĩ
        $doctors = $this->doctorModel->getAll();

        // Lấy lịch làm việc của tất cả bác sĩ
        $allSchedules = [];
        $scheduleStats = [
            'total_schedules' => 0,
            'active_schedules' => 0,
            'morning_shifts' => 0,
            'afternoon_shifts' => 0,
            'evening_shifts' => 0
        ];

        foreach ($doctors as $doctor) {
            $schedules = $this->doctorModel->getSchedules($doctor['id']);
            $allSchedules[$doctor['id']] = [
                'doctor' => $doctor,
                'schedules' => $schedules
            ];

            // Cập nhật thống kê
            $scheduleStats['total_schedules'] += count($schedules);
            foreach ($schedules as $schedule) {
                if ($schedule['trang_thai'] === 'active') {
                    $scheduleStats['active_schedules']++;
                }
                switch ($schedule['loai_ca']) {
                    case 'Ca sáng':
                        $scheduleStats['morning_shifts']++;
                        break;
                    case 'Ca chiều':
                        $scheduleStats['afternoon_shifts']++;
                        break;
                    case 'Ca tối':
                        $scheduleStats['evening_shifts']++;
                        break;
                }
            }
        }

        // Nhóm lịch theo ngày
        $daysOfWeek = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
        $schedulesByDay = [];

        foreach ($daysOfWeek as $day) {
            $schedulesByDay[$day] = [];
            foreach ($allSchedules as $doctorId => $data) {
                $daySchedules = $this->doctorModel->getSchedulesByDay($doctorId, $day);
                if (!empty($daySchedules)) {
                    $schedulesByDay[$day][] = [
                        'doctor' => $data['doctor'],
                        'schedules' => $daySchedules
                    ];
                }
            }
        }

        $page_title = 'Quản lý lịch làm việc bác sĩ';

        // Start output buffering để lấy content
        ob_start();
        include 'Views/admin/doctor_schedules.php';
        $content = ob_get_clean();

        // Sử dụng renderLayout function
        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, $page_title);
    }

    /**
     * Danh sách bác sĩ (Admin view)
     */
    public function doctorsList()
    {
        $this->auth->requireAuth('admin');
        // Phân trang
        $perPage = max(1, (int)($_GET['per_page'] ?? 10));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $total = method_exists($this->doctorModel, 'countAll') ? $this->doctorModel->countAll() : 0;
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
        $offset = ($page - 1) * $perPage;

        // Lấy danh sách bác sĩ (có phân trang nếu có hỗ trợ)
        if (method_exists($this->doctorModel, 'getPaginated')) {
            $doctors = $this->doctorModel->getPaginated($offset, $perPage);
        } else {
            $doctors = $this->doctorModel->getAll();
        }
        // Lấy danh sách chuyên khoa để hiển thị trong modal thêm bác sĩ
        $specialties = $this->specialtyModel->all();
        // CSRF cho form thêm mới
        if (class_exists('SecurityConfig')) {
            SecurityConfig::generateCSRFToken();
        }

        $page_title = 'Danh sách Bác sĩ';

        // Start output buffering để lấy content
        ob_start();
        include 'Views/admin/doctors_list.php';
        $content = ob_get_clean();

        // Sử dụng renderLayout function
        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, $page_title);
    }

    /**
     * Danh sách bệnh nhân (Admin view)
     */
    public function patientsList()
    {
        $this->auth->requireAuth('admin');

        $perPage = max(1, (int)($_GET['per_page'] ?? 10));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $total = method_exists($this->patientModel, 'countAll') ? $this->patientModel->countAll() : 0;
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
        $offset = ($page - 1) * $perPage;

        if (method_exists($this->patientModel, 'getPaginated')) {
            $patients = $this->patientModel->getPaginated($offset, $perPage);
        } else {
            $patients = $this->patientModel->getAll();
        }

        $page_title = 'Danh sách Bệnh nhân';

        ob_start();
        include 'Views/admin/patients_list.php';
        $content = ob_get_clean();

        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, $page_title);
    }

    /**
     * Danh sách lễ tân (Admin view)
     */
    public function receptionList()
    {
        $this->auth->requireAuth('admin');

        $perPage = max(1, (int)($_GET['per_page'] ?? 10));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $total = method_exists($this->receptionModel, 'countAll') ? $this->receptionModel->countAll() : 0;
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
        $offset = ($page - 1) * $perPage;

        if (method_exists($this->receptionModel, 'getPaginated')) {
            $receptions = $this->receptionModel->getPaginated($offset, $perPage);
        } else {
            $receptions = $this->receptionModel->getAll();
        }

        $page_title = 'Danh sách Lễ tân';

        ob_start();
        include 'Views/admin/reception_list.php';
        $content = ob_get_clean();

        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, $page_title);
    }

    public function adminUpdateDoctor()
    {
        $this->auth->requireAuth('admin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ./doctors_list');
            exit();
        }
        if (class_exists('SecurityConfig')) {
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!SecurityConfig::validateCSRFToken($csrfToken)) {
                $_SESSION['error'] = 'Token bảo mật không hợp lệ!';
                header('Location: ./doctors_list');
                exit();
            }
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'Thiếu ID bác sĩ.';
            header('Location: ./doctors_list');
            exit();
        }

        $data = [
            'ten' => trim($_POST['ten'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'so_dien_thoai' => trim($_POST['so_dien_thoai'] ?? ''),
            'chuyen_khoa_id' => (int)($_POST['chuyen_khoa_id'] ?? 0) ?: null,
            'so_giay_phep' => trim($_POST['so_giay_phep'] ?? ''),
            'so_nam_kinh_nghiem' => (int)($_POST['so_nam_kinh_nghiem'] ?? 0),
        ];
        // Validate và chuẩn hóa số điện thoại (nếu có nhập)
        $phone = $data['so_dien_thoai'];
        if ($phone !== '') {
            $digits = preg_replace('/\D+/', '', $phone);
            if (str_starts_with($digits, '84')) {
                $digits = substr($digits, 2);
            } elseif (str_starts_with($digits, '0')) {
                $digits = substr($digits, 1);
            }
            $validPrefixes = [
                '32',
                '33',
                '34',
                '35',
                '36',
                '37',
                '38',
                '39',
                '86',
                '96',
                '97',
                '98',
                '81',
                '82',
                '83',
                '84',
                '85',
                '88',
                '91',
                '94',
                '70',
                '76',
                '77',
                '78',
                '79',
                '89',
                '90',
                '93',
                '52',
                '56',
                '58',
                '92',
                '59',
                '99'
            ];
            if (strlen($digits) !== 9 || !in_array(substr($digits, 0, 2), $validPrefixes, true)) {
                $_SESSION['error'] = 'Số điện thoại không hợp lệ (yêu cầu 0xxxxxxxxx hoặc 84xxxxxxxxx).';
                header('Location: ./doctors_list');
                exit();
            }
            // Chuẩn hóa lưu DB về dạng 0xxxxxxxxx
            $data['so_dien_thoai'] = '0' . $digits;
        }
        // Kiểm tra trùng số điện thoại giữa các bác sĩ khác (cho phép giữ số hiện tại)
        if (!empty($data['so_dien_thoai'])) {
            $currentDoctor = $this->doctorModel->getById($id);
            $currentPhone = $currentDoctor['so_dien_thoai'] ?? '';
            if ($data['so_dien_thoai'] !== $currentPhone) {
                if ($this->doctorModel->phoneExists($data['so_dien_thoai'])) {
                    $_SESSION['error'] = 'Số điện thoại này đã được sử dụng bởi một bác sĩ khác.';
                    header('Location: ./doctors_list');
                    exit();
                }
            }
        }
        try {
            $ok = $this->doctorModel->updateProfile($id, $data);
            $_SESSION['success'] = $ok ? 'Cập nhật bác sĩ thành công!' : 'Không thể cập nhật bác sĩ.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }
        header('Location: ./doctors_list');
        exit();
    }

    public function adminDeleteDoctor()
    {
        $this->auth->requireAuth('admin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ./doctors_list');
            exit();
        }
        if (class_exists('SecurityConfig')) {
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!SecurityConfig::validateCSRFToken($csrfToken)) {
                $_SESSION['error'] = 'Token bảo mật không hợp lệ!';
                header('Location: ./doctors_list');
                exit();
            }
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'Thiếu ID bác sĩ.';
            header('Location: ./doctors_list');
            exit();
        }
        try {
            $ok = method_exists($this->doctorModel, 'deleteById') ? $this->doctorModel->deleteById($id) : false;
            $_SESSION['success'] = $ok ? 'Đã xóa bác sĩ.' : 'Không thể xóa bác sĩ.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }
        header('Location: ./doctors_list');
        exit();
    }

    /**
     * Tạo bác sĩ (Admin)
     */
    public function adminCreateDoctor()
    {
        $this->auth->requireAuth('admin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ./doctors_list');
            exit();
        }
        // Validate CSRF nếu có
        if (class_exists('SecurityConfig')) {
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!SecurityConfig::validateCSRFToken($csrfToken)) {
                $_SESSION['error'] = 'Token bảo mật không hợp lệ!';
                header('Location: ./doctors_list');
                exit();
            }
        }

        $name = trim($_POST['ten'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['mat_khau'] ?? '';
        $phone = trim($_POST['so_dien_thoai'] ?? '');
        $chuyenKhoaId = isset($_POST['chuyen_khoa_id']) ? (int)$_POST['chuyen_khoa_id'] : null;
        $soGiayPhep = trim($_POST['so_giay_phep'] ?? '');
        $soNamKinhNghiem = (int)($_POST['so_nam_kinh_nghiem'] ?? 0);
        // Upload ảnh nếu có
        $imagePath = null;
        if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['hinh_anh'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = 'Tải ảnh thất bại. Vui lòng thử lại.';
                header('Location: ./doctors_list');
                exit();
            }
            // Validate kích thước (<= 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                $_SESSION['error'] = 'Ảnh quá lớn (tối đa 5MB).';
                header('Location: ./doctors_list');
                exit();
            }
            // Validate mime
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif'
            ];
            if (!isset($allowed[$mime])) {
                $_SESSION['error'] = 'Chỉ chấp nhận JPG, PNG, GIF.';
                header('Location: ./doctors_list');
                exit();
            }
            // Tạo thư mục uploads nếu chưa có
            $uploadDir = __DIR__ . '/../uploads';
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);
            $ext = $allowed[$mime];
            $safeName = 'doctor_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destPath = $uploadDir . DIRECTORY_SEPARATOR . $safeName;
            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                $_SESSION['error'] = 'Không thể lưu tệp tải lên.';
                header('Location: ./doctors_list');
                exit();
            }
            // Lưu đường dẫn tương đối để hiển thị
            $imagePath = 'uploads/' . $safeName;
        }

        if ($name === '' || $email === '' || $password === '' || !$chuyenKhoaId) {
            $_SESSION['error'] = 'Vui lòng nhập đủ Tên, Email, Mật khẩu và Chuyên khoa.';
            header('Location: ./doctors_list');
            exit();
        }

        // Validate số điện thoại (nếu có nhập)
        if ($phone !== '') {
            $digits = preg_replace('/\D+/', '', $phone);
            if (str_starts_with($digits, '84')) {
                $digits = substr($digits, 2);
            } elseif (str_starts_with($digits, '0')) {
                $digits = substr($digits, 1);
            }
            $validPrefixes = [
                '32',
                '33',
                '34',
                '35',
                '36',
                '37',
                '38',
                '39',
                '86',
                '96',
                '97',
                '98',
                '81',
                '82',
                '83',
                '84',
                '85',
                '88',
                '91',
                '94',
                '70',
                '76',
                '77',
                '78',
                '79',
                '89',
                '90',
                '93',
                '52',
                '56',
                '58',
                '92',
                '59',
                '99'
            ];
            if (strlen($digits) !== 9 || !in_array(substr($digits, 0, 2), $validPrefixes, true)) {
                $_SESSION['error'] = 'Số điện thoại không hợp lệ (yêu cầu 0xxxxxxxxx hoặc 84xxxxxxxxx).';
                header('Location: ./doctors_list');
                exit();
            }
            $phone = '0' . $digits; // Chuẩn hóa lưu DB
        }

        try {
            $ok = $this->doctorModel->create([
                'ten' => $name,
                'email' => $email,
                'mat_khau' => $password,
                'so_dien_thoai' => $phone,
                'chuyen_khoa_id' => $chuyenKhoaId,
                'so_giay_phep' => $soGiayPhep,
                'so_nam_kinh_nghiem' => $soNamKinhNghiem,
                'hinh_anh' => $imagePath
            ]);
            if ($ok) {
                $_SESSION['success'] = 'Thêm bác sĩ thành công!';
            } else {
                $_SESSION['error'] = 'Không thể thêm bác sĩ. Vui lòng thử lại!';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }

        header('Location: ./doctors_list');
        exit();
    }

    /**
     * Quản lý chuyên khoa - danh sách + form thêm nhanh
     */
    public function specialties()
    {
        $this->auth->requireAuth('admin');
        $specialties = $this->specialtyModel->all();
        $page_title = 'Quản lý Chuyên khoa';
        ob_start();
        include 'Views/admin/specialties_list.php';
        $content = ob_get_clean();
        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, $page_title);
    }

    public function specialtyCreate()
    {
        $this->auth->requireAuth('admin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ./specialties');
            exit();
        }
        $data = [
            'ten' => trim($_POST['ten'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'mo_ta' => trim($_POST['mo_ta'] ?? ''),
            'icon' => trim($_POST['icon'] ?? ''),
            'thu_tu' => (int)($_POST['thu_tu'] ?? 0),
            'trang_thai' => $_POST['trang_thai'] ?? 'active'
        ];
        if (empty($data['ten'])) {
            $_SESSION['error'] = 'Vui lòng nhập tên chuyên khoa';
            header('Location: ./specialties');
            exit();
        }
        try {
            $ok = $this->specialtyModel->create($data);
            $_SESSION['success'] = $ok ? 'Thêm chuyên khoa thành công' : 'Không thể thêm chuyên khoa';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }
        header('Location: ./specialties');
        exit();
    }

    public function specialtyUpdate()
    {
        $this->auth->requireAuth('admin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ./specialties');
            exit();
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'ID không hợp lệ';
            header('Location: ./specialties');
            exit();
        }
        $data = [
            'ten' => trim($_POST['ten'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'mo_ta' => trim($_POST['mo_ta'] ?? ''),
            'icon' => trim($_POST['icon'] ?? ''),
            'thu_tu' => (int)($_POST['thu_tu'] ?? 0),
            'trang_thai' => $_POST['trang_thai'] ?? 'active'
        ];
        if (empty($data['ten'])) {
            $_SESSION['error'] = 'Vui lòng nhập tên chuyên khoa';
            header('Location: ./specialties');
            exit();
        }
        try {
            $ok = $this->specialtyModel->update($id, $data);
            $_SESSION['success'] = $ok ? 'Cập nhật chuyên khoa thành công' : 'Không thể cập nhật chuyên khoa';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }
        header('Location: ./specialties');
        exit();
    }

    public function specialtyDelete()
    {
        $this->auth->requireAuth('admin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ./specialties');
            exit();
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'ID không hợp lệ';
            header('Location: ./specialties');
            exit();
        }
        try {
            $ok = $this->specialtyModel->delete($id);
            if ($ok) {
                $_SESSION['success'] = 'Xóa chuyên khoa thành công';
            } else {
                $_SESSION['error'] = 'Không thể xóa chuyên khoa vì đang được sử dụng bởi bác sĩ';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }
        header('Location: ./specialties');
        exit();
    }

    /**
     * Lấy thống kê dashboard
     */
    private function getDashboardStats()
    {
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();

        $stats = [
            'total_doctors' => 0,
            'total_patients' => 0,
            'total_appointments_today' => 0,
            'total_revenue_month' => 0,
            'appointments_by_month' => [],
            'revenue_by_month' => [],
            // Thêm các chỉ số mới cho phòng khám đa khoa
            'new_patients_today' => 0,
            'examining_patients' => 0,
            'pending_appointments' => 0,
            'unpaid_receipts' => 0,
            'xray_today' => 0,
            'ultrasound_today' => 0,
            'lab_today' => 0,
            'prescriptions_today' => 0,
            'revenue_today' => 0,
            'revenue_week' => 0,
            'bhyt_ratio' => 0,
            'on_duty_doctors' => 0,
            'on_duty_receptions' => 0,
            'completed_exams_today' => 0,
            'appointments_status' => [
                'pending' => 0,
                'confirmed' => 0,
                'examining' => 0,
                'completed' => 0,
                'cancelled' => 0
            ]
        ];

        try {
            $today = date('Y-m-d');
            $currentMonth = date('Y-m');
            $weekStart = date('Y-m-d', strtotime('monday this week'));
            $weekEnd = date('Y-m-d', strtotime('sunday this week'));

            // Tổng số bác sĩ
            $stmt = $db->query("SELECT COUNT(*) as total FROM bac_si");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_doctors'] = (int)($result['total'] ?? 0);

            // Tổng số bệnh nhân
            $stmt = $db->query("SELECT COUNT(*) as total FROM benh_nhan");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_patients'] = (int)($result['total'] ?? 0);

            // Bệnh nhân mới hôm nay
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM benh_nhan WHERE DATE(ngay_tao) = :today");
            $stmt->execute([':today' => $today]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['new_patients_today'] = (int)($result['total'] ?? 0);

            // Lịch hẹn hôm nay
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM lich_hen WHERE ngay_hen = :today");
            $stmt->execute([':today' => $today]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_appointments_today'] = (int)($result['total'] ?? 0);

            // Lịch hẹn theo trạng thái hôm nay
            $stmt = $db->prepare("
                SELECT 
                    SUM(CASE WHEN trang_thai = 'Chờ xác nhận' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN trang_thai = 'Đã xác nhận' THEN 1 ELSE 0 END) as confirmed,
                    SUM(CASE WHEN trang_thai = 'Đang khám' THEN 1 ELSE 0 END) as examining,
                    SUM(CASE WHEN trang_thai = 'Hoàn thành' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN trang_thai = 'hủy' THEN 1 ELSE 0 END) as cancelled
                FROM lich_hen 
                WHERE ngay_hen = :today
            ");
            $stmt->execute([':today' => $today]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['appointments_status'] = [
                'pending' => (int)($result['pending'] ?? 0),
                'confirmed' => (int)($result['confirmed'] ?? 0),
                'examining' => (int)($result['examining'] ?? 0),
                'completed' => (int)($result['completed'] ?? 0),
                'cancelled' => (int)($result['cancelled'] ?? 0)
            ];
            $stats['pending_appointments'] = $stats['appointments_status']['pending'];
            $stats['examining_patients'] = $stats['appointments_status']['examining'];
            $stats['completed_exams_today'] = $stats['appointments_status']['completed'];

            // Doanh thu hôm nay
            $stmt = $db->prepare("
                SELECT COALESCE(SUM(tong_nguoi_benh), 0) as total 
                FROM bien_lai_vien_phi 
                WHERE DATE(ngay_lap) = :today 
                AND trang_thai IN ('Đã thanh toán tiền mặt', 'Đã thanh toán chuyển khoản')
            ");
            $stmt->execute([':today' => $today]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['revenue_today'] = (float)($result['total'] ?? 0);

            // Doanh thu tuần này
            $stmt = $db->prepare("
                SELECT COALESCE(SUM(tong_nguoi_benh), 0) as total 
                FROM bien_lai_vien_phi 
                WHERE DATE(ngay_lap) BETWEEN :week_start AND :week_end
                AND trang_thai IN ('Đã thanh toán tiền mặt', 'Đã thanh toán chuyển khoản')
            ");
            $stmt->execute([':week_start' => $weekStart, ':week_end' => $weekEnd]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['revenue_week'] = (float)($result['total'] ?? 0);

            // Doanh thu tháng này
            $stmt = $db->prepare("
                SELECT COALESCE(SUM(tong_nguoi_benh), 0) as total 
                FROM bien_lai_vien_phi 
                WHERE DATE_FORMAT(ngay_lap, '%Y-%m') = :month 
                AND trang_thai IN ('Đã thanh toán tiền mặt', 'Đã thanh toán chuyển khoản')
            ");
            $stmt->execute([':month' => $currentMonth]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_revenue_month'] = (float)($result['total'] ?? 0);

            // Biên lai chưa thanh toán
            $stmt = $db->query("SELECT COUNT(*) as total FROM bien_lai_vien_phi WHERE trang_thai = 'Chưa thanh toán'");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['unpaid_receipts'] = (int)($result['total'] ?? 0);

            // Tỷ lệ BHYT (tính từ biên lai tháng này)
            $stmt = $db->prepare("
                SELECT 
                    COALESCE(SUM(tong_quy_bhyt), 0) as bhyt_total,
                    COALESCE(SUM(tong_tien_co_ban), 0) as total_base
                FROM bien_lai_vien_phi 
                WHERE DATE_FORMAT(ngay_lap, '%Y-%m') = :month 
                AND trang_thai IN ('Đã thanh toán tiền mặt', 'Đã thanh toán chuyển khoản')
            ");
            $stmt->execute([':month' => $currentMonth]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $bhytTotal = (float)($result['bhyt_total'] ?? 0);
            $totalBase = (float)($result['total_base'] ?? 0);
            $stats['bhyt_ratio'] = $totalBase > 0 ? round(($bhytTotal / $totalBase) * 100, 1) : 0;

            // X-Quang hôm nay
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM phieu_chup_xquang WHERE DATE(ngay_tao) = :today");
            $stmt->execute([':today' => $today]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['xray_today'] = (int)($result['total'] ?? 0);

            // Siêu âm hôm nay
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM phieu_yeu_cau_sieu_am WHERE DATE(ngay_tao) = :today");
            $stmt->execute([':today' => $today]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['ultrasound_today'] = (int)($result['total'] ?? 0);

            // Xét nghiệm hôm nay
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM phieu_yeu_cau_xet_nghiem WHERE DATE(ngay_tao) = :today");
            $stmt->execute([':today' => $today]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['lab_today'] = (int)($result['total'] ?? 0);

            // Đơn thuốc hôm nay
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM don_thuoc WHERE DATE(NgayKe) = :today");
            $stmt->execute([':today' => $today]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['prescriptions_today'] = (int)($result['total'] ?? 0);

            // Bác sĩ đang trực (có lịch làm việc hôm nay)
            $dayOfWeek = date('N'); // 1=Monday, 7=Sunday
            $dayNames = ['', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
            $currentDayName = $dayNames[$dayOfWeek] ?? 'Thứ 2';

            $stmt = $db->prepare("
                SELECT COUNT(DISTINCT bs.id) as total
                FROM bac_si bs
                INNER JOIN lich_lam_viec llv ON bs.id = llv.bac_si_id
                WHERE llv.trang_thai = 'active'
                AND llv.thu_trong_tuan = :day_name
            ");
            $stmt->execute([':day_name' => $currentDayName]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['on_duty_doctors'] = (int)($result['total'] ?? 0);

            // Lễ tân đang trực
            $stmt = $db->prepare("
                SELECT COUNT(DISTINCT lt.id) as total
                FROM le_tan lt
                INNER JOIN lich_lam_viec_le_tan llt ON lt.id = llt.letan_id
                WHERE llt.trang_thai = 'active'
                AND llt.thu_trong_tuan = :day_name
            ");
            $stmt->execute([':day_name' => $currentDayName]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['on_duty_receptions'] = (int)($result['total'] ?? 0);

            // Lịch hẹn theo tháng (12 tháng gần nhất)
            $stmt = $db->prepare("
                SELECT DATE_FORMAT(ngay_hen, '%Y-%m') as ym, COUNT(*) as c
                FROM lich_hen
                WHERE ngay_hen >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(ngay_hen, '%Y-%m')
                ORDER BY ym ASC
            ");
            $stmt->execute();
            $stats['appointments_by_month'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Doanh thu theo tháng (12 tháng gần nhất)
            $stmt = $db->prepare("
                SELECT DATE_FORMAT(ngay_lap, '%Y-%m') as ym, COALESCE(SUM(tong_nguoi_benh), 0) as s
                FROM bien_lai_vien_phi
                WHERE ngay_lap >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                AND trang_thai IN ('Đã thanh toán tiền mặt', 'Đã thanh toán chuyển khoản')
                GROUP BY DATE_FORMAT(ngay_lap, '%Y-%m')
                ORDER BY ym ASC
            ");
            $stmt->execute();
            $stats['revenue_by_month'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting dashboard stats: " . $e->getMessage());
        }

        return $stats;
    }

    /**
     * API: Lấy dữ liệu doanh thu theo filter (ngày/tuần/tháng/năm)
     */
    public function getRevenueStats()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->auth->requireAuth('admin');

        $filter = $_GET['filter'] ?? 'month'; // day, week, month, year

        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();

        try {
            $data = [];

            switch ($filter) {
                case 'day':
                    // 30 ngày gần nhất
                    $stmt = $db->prepare("
                        SELECT DATE(ngay_lap) as label, COALESCE(SUM(tong_nguoi_benh), 0) as value
                        FROM bien_lai_vien_phi
                        WHERE ngay_lap >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                        AND trang_thai IN ('Đã thanh toán tiền mặt', 'Đã thanh toán chuyển khoản')
                        GROUP BY DATE(ngay_lap)
                        ORDER BY label ASC
                    ");
                    $stmt->execute();
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($results as $row) {
                        $data[] = [
                            'label' => date('d/m', strtotime($row['label'])),
                            'value' => $row['value']
                        ];
                    }
                    break;

                case 'week':
                    // 12 tuần gần nhất
                    $stmt = $db->prepare("
                        SELECT YEARWEEK(ngay_lap, 1) as yw, COALESCE(SUM(tong_nguoi_benh), 0) as value
                        FROM bien_lai_vien_phi
                        WHERE ngay_lap >= DATE_SUB(CURDATE(), INTERVAL 12 WEEK)
                        AND trang_thai IN ('Đã thanh toán tiền mặt', 'Đã thanh toán chuyển khoản')
                        GROUP BY YEARWEEK(ngay_lap, 1)
                        ORDER BY yw ASC
                    ");
                    $stmt->execute();
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($results as $row) {
                        $year = substr($row['yw'], 0, 4);
                        $week = substr($row['yw'], 4);
                        $data[] = [
                            'label' => "Tuần $week/$year",
                            'value' => $row['value']
                        ];
                    }
                    break;

                case 'month':
                    // 12 tháng gần nhất
                    $stmt = $db->prepare("
                        SELECT DATE_FORMAT(ngay_lap, '%Y-%m') as ym, COALESCE(SUM(tong_nguoi_benh), 0) as value
                        FROM bien_lai_vien_phi
                        WHERE ngay_lap >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                        AND trang_thai IN ('Đã thanh toán tiền mặt', 'Đã thanh toán chuyển khoản')
                        GROUP BY DATE_FORMAT(ngay_lap, '%Y-%m')
                        ORDER BY ym ASC
                    ");
                    $stmt->execute();
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($results as $row) {
                        $data[] = [
                            'label' => date('m/Y', strtotime($row['ym'] . '-01')),
                            'value' => $row['value']
                        ];
                    }
                    break;

                case 'year':
                    // 5 năm gần nhất
                    $stmt = $db->prepare("
                        SELECT YEAR(ngay_lap) as y, COALESCE(SUM(tong_nguoi_benh), 0) as value
                        FROM bien_lai_vien_phi
                        WHERE ngay_lap >= DATE_SUB(CURDATE(), INTERVAL 5 YEAR)
                        AND trang_thai IN ('Đã thanh toán tiền mặt', 'Đã thanh toán chuyển khoản')
                        GROUP BY YEAR(ngay_lap)
                        ORDER BY y ASC
                    ");
                    $stmt->execute();
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($results as $row) {
                        $data[] = [
                            'label' => $row['y'],
                            'value' => $row['value']
                        ];
                    }
                    break;
            }

            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error getting revenue stats: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    // ========== QUẢN LÝ LỊCH LÀM VIỆC BÁC SĨ (ADMIN) ==========

    /**
     * Admin thêm lịch làm việc cho bác sĩ
     */
    public function adminAddSchedule()
    {
        $this->auth->requireAuth('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        // Nhận JSON data
        $input = json_decode(file_get_contents('php://input'), true);

        $doctorId = (int)($input['doctor_id'] ?? 0);
        $thuTrongTuan = trim($input['thu_trong_tuan'] ?? '');
        $gioBatDau = trim($input['gio_bat_dau'] ?? '');
        $gioKetThuc = trim($input['gio_ket_thuc'] ?? '');
        $loaiCa = trim($input['loai_ca'] ?? '');
        $ghiChu = trim($input['ghi_chu'] ?? '');

        // Validation
        if ($doctorId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn bác sĩ!']);
            exit();
        }

        if (empty($thuTrongTuan) || empty($gioBatDau) || empty($gioKetThuc) || empty($loaiCa)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc!']);
            exit();
        }

        // Kiểm tra thời gian hợp lệ
        if (strtotime($gioBatDau) >= strtotime($gioKetThuc)) {
            echo json_encode(['success' => false, 'message' => 'Giờ kết thúc phải sau giờ bắt đầu!']);
            exit();
        }

        // Kiểm tra bác sĩ có tồn tại không
        $doctor = $this->doctorModel->getById($doctorId);
        if (!$doctor) {
            echo json_encode(['success' => false, 'message' => 'Bác sĩ không tồn tại!']);
            exit();
        }

        // Kiểm tra xung đột lịch
        if ($this->doctorModel->checkScheduleConflict($doctorId, $thuTrongTuan, $gioBatDau, $gioKetThuc)) {
            echo json_encode(['success' => false, 'message' => 'Lịch làm việc này bị xung đột với lịch hiện có!']);
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
            echo json_encode([
                'success' => true,
                'message' => 'Thêm lịch làm việc thành công!',
                'schedule_id' => $newId
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi thêm lịch làm việc!']);
        }
        exit();
    }

    /**
     * Admin cập nhật lịch làm việc của bác sĩ
     */
    public function adminUpdateSchedule()
    {
        $this->auth->requireAuth('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        // Nhận JSON data
        $input = json_decode(file_get_contents('php://input'), true);

        $scheduleId = (int)($input['schedule_id'] ?? 0);
        $doctorId = (int)($input['doctor_id'] ?? 0);
        $thuTrongTuan = trim($input['thu_trong_tuan'] ?? '');
        $gioBatDau = trim($input['gio_bat_dau'] ?? '');
        $gioKetThuc = trim($input['gio_ket_thuc'] ?? '');
        $loaiCa = trim($input['loai_ca'] ?? '');
        $ghiChu = trim($input['ghi_chu'] ?? '');
        $trangThai = trim($input['trang_thai'] ?? 'active');

        // Validation
        if ($scheduleId <= 0 || $doctorId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thông tin lịch không hợp lệ!']);
            exit();
        }

        if (empty($thuTrongTuan) || empty($gioBatDau) || empty($gioKetThuc) || empty($loaiCa)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc!']);
            exit();
        }

        // Kiểm tra thời gian hợp lệ
        if (strtotime($gioBatDau) >= strtotime($gioKetThuc)) {
            echo json_encode(['success' => false, 'message' => 'Giờ kết thúc phải sau giờ bắt đầu!']);
            exit();
        }

        // Kiểm tra xung đột lịch (loại trừ lịch hiện tại)
        if ($this->doctorModel->checkScheduleConflict($doctorId, $thuTrongTuan, $gioBatDau, $gioKetThuc, $scheduleId)) {
            echo json_encode(['success' => false, 'message' => 'Lịch làm việc này bị xung đột với lịch khác!']);
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

        $success = $this->doctorModel->updateSchedule($scheduleId, $doctorId, $data);
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật lịch làm việc thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật lịch làm việc!']);
        }
        exit();
    }

    /**
     * Admin xóa lịch làm việc của bác sĩ
     */
    public function adminDeleteSchedule()
    {
        $this->auth->requireAuth('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        // Nhận JSON data
        $input = json_decode(file_get_contents('php://input'), true);

        $scheduleId = (int)($input['schedule_id'] ?? 0);
        $doctorId = (int)($input['doctor_id'] ?? 0);

        // Validation
        if ($scheduleId <= 0 || $doctorId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thông tin lịch không hợp lệ!']);
            exit();
        }

        // Xóa lịch làm việc
        $success = $this->doctorModel->deleteSchedule($scheduleId, $doctorId);
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Xóa lịch làm việc thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi xóa lịch làm việc!']);
        }
        exit();
    }

    /**
     * Admin lấy thông tin chi tiết lịch làm việc
     */
    public function adminGetScheduleInfo()
    {
        $this->auth->requireAuth('admin');

        $scheduleId = (int)($_GET['schedule_id'] ?? 0);
        $doctorId = (int)($_GET['doctor_id'] ?? 0);

        if ($scheduleId <= 0 || $doctorId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thông tin không hợp lệ!']);
            exit();
        }

        $schedule = $this->doctorModel->getScheduleById($scheduleId, $doctorId);
        if ($schedule) {
            echo json_encode(['success' => true, 'data' => $schedule]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy lịch làm việc!']);
        }
        exit();
    }

    // ========== QUẢN LÝ LỊCH HẸN (ADMIN) ==========

    /**
     * Hiển thị trang quản lý lịch hẹn
     */
    public function appointments()
    {
        $this->auth->requireAuth('admin');

        // Lấy filters từ query string
        $filters = [];
        $filters['trang_thai'] = $_GET['trang_thai'] ?? '';
        $filters['loai_lich'] = $_GET['loai_lich'] ?? '';
        $filters['ngay_hen'] = $_GET['ngay_hen'] ?? '';
        $filters['bac_si_id'] = $_GET['bac_si_id'] ?? '';
        $filters['search'] = $_GET['search'] ?? '';

        // Phân trang
        $perPage = 20;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters['limit'] = $perPage;
        $filters['offset'] = ($page - 1) * $perPage;

        // Lấy dữ liệu
        require_once 'Models/Appointment.php';
        $appointmentModel = new Appointment();
        $appointments = $appointmentModel->getAll($filters);
        $total = $appointmentModel->countAll($filters);
        $totalPages = ceil($total / $perPage);

        // Lấy danh sách bác sĩ cho filter
        $doctors = $this->doctorModel->getAll();

        // Thống kê
        $stats = $this->getAppointmentStats();

        $page_title = 'Quản lý lịch hẹn';

        // Start output buffering
        ob_start();
        include 'Views/admin/appointments.php';
        $content = ob_get_clean();

        // Render layout
        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, $page_title);
    }

    /**
     * Hủy lịch hẹn (Admin)
     */
    public function cancelAppointment()
    {
        $this->auth->requireAuth('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $appointmentId = (int)($input['appointment_id'] ?? 0);
        $reason = trim($input['reason'] ?? 'Admin hủy lịch hẹn');

        if ($appointmentId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID lịch hẹn không hợp lệ!']);
            exit();
        }

        require_once 'Models/Appointment.php';
        $appointmentModel = new Appointment();

        $success = $appointmentModel->updateStatus($appointmentId, 'hủy', $reason);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Đã hủy lịch hẹn thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi hủy lịch hẹn!']);
        }
        exit();
    }

    /**
     * Lấy thống kê lịch hẹn
     */
    private function getAppointmentStats()
    {
        require_once 'Models/Appointment.php';
        $appointmentModel = new Appointment();

        $today = date('Y-m-d');
        $thisMonth = date('Y-m');

        $stats = [
            'total' => $appointmentModel->countAll([]),
            'today' => $appointmentModel->countAll(['ngay_hen' => $today]),
            'pending' => $appointmentModel->countAll(['trang_thai' => 'Chờ xác nhận']),
            'confirmed' => $appointmentModel->countAll(['trang_thai' => 'Đã xác nhận']),
            'completed' => $appointmentModel->countAll(['trang_thai' => 'Hoàn thành']),
            'cancelled' => $appointmentModel->countAll(['trang_thai' => 'hủy    '])
        ];

        return $stats;
    }

    // ========== QUẢN LÝ LỊCH LÀM VIỆC LỄ TÂN (ADMIN) ==========

    /**
     * Trang quản lý lịch làm việc lễ tân (admin xem & chỉnh) – giao diện giống lịch bác sĩ
     */
    public function receptionSchedules()
    {
        $this->auth->requireAuth('admin');

        // Lấy danh sách lễ tân
        require_once 'Models/Reception.php';
        $receptionModel = new Reception();
        $receptions = $receptionModel->getAll();

        // Lấy lịch làm việc lễ tân và thống kê
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();

        $allSchedules = [];
        $scheduleStats = [
            'total_schedules' => 0,
            'active_schedules' => 0,
            'morning_shifts' => 0,
            'afternoon_shifts' => 0,
        ];

        foreach ($receptions as $rec) {
            $rid = (int)$rec['id'];
            $sql = "SELECT * FROM lich_lam_viec_le_tan
                    WHERE letan_id = :id
                    ORDER BY 
                        CASE thu_trong_tuan 
                            WHEN 'Thứ 2' THEN 1
                            WHEN 'Thứ 3' THEN 2
                            WHEN 'Thứ 4' THEN 3
                            WHEN 'Thứ 5' THEN 4
                            WHEN 'Thứ 6' THEN 5
                            WHEN 'Thứ 7' THEN 6
                            WHEN 'Chủ nhật' THEN 7
                        END, gio_bat_dau";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $rid, PDO::PARAM_INT);
            $stmt->execute();
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $allSchedules[$rid] = [
                'reception' => $rec,
                'schedules' => $schedules,
            ];

            // Cập nhật thống kê
            $scheduleStats['total_schedules'] += count($schedules);
            foreach ($schedules as $schedule) {
                if (($schedule['trang_thai'] ?? 'active') === 'active') {
                    $scheduleStats['active_schedules']++;
                }
                switch ($schedule['loai_ca']) {
                    case 'Ca sáng':
                        $scheduleStats['morning_shifts']++;
                        break;
                    case 'Ca chiều':
                        $scheduleStats['afternoon_shifts']++;
                        break;
                }
            }
        }

        // Nhóm lịch theo ngày giống bác sĩ
        $daysOfWeek = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
        $schedulesByDay = [];

        foreach ($daysOfWeek as $day) {
            $schedulesByDay[$day] = [];
            foreach ($allSchedules as $rid => $data) {
                $daySchedules = array_values(array_filter($data['schedules'], function ($sc) use ($day) {
                    return ($sc['thu_trong_tuan'] ?? '') === $day && ($sc['trang_thai'] ?? 'active') === 'active';
                }));
                if (!empty($daySchedules)) {
                    $schedulesByDay[$day][] = [
                        'reception' => $data['reception'],
                        'schedules' => $daySchedules,
                    ];
                }
            }
        }

        $pageTitle = 'Quản lý lịch làm việc lễ tân';

        ob_start();
        include 'Views/admin/reception_schedules.php';
        $content = ob_get_clean();

        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, $pageTitle);
    }

    // ===== ADMIN RECEPTION SCHEDULE MANAGEMENT (LỄ TÂN) =====

    public function adminAddReceptionSchedule()
    {
        $this->auth->requireAuth('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $receptionId = (int)($input['reception_id'] ?? 0);
        $thuTrongTuan = trim($input['thu_trong_tuan'] ?? '');
        $gioBatDau = trim($input['gio_bat_dau'] ?? '');
        $gioKetThuc = trim($input['gio_ket_thuc'] ?? '');
        $loaiCa = trim($input['loai_ca'] ?? '');
        $ghiChu = trim($input['ghi_chu'] ?? '');

        if ($receptionId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn lễ tân!']);
            exit();
        }
        if (empty($thuTrongTuan) || empty($gioBatDau) || empty($gioKetThuc) || empty($loaiCa)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc!']);
            exit();
        }
        if (strtotime($gioBatDau) >= strtotime($gioKetThuc)) {
            echo json_encode(['success' => false, 'message' => 'Giờ kết thúc phải sau giờ bắt đầu!']);
            exit();
        }

        require_once 'Models/Reception.php';
        $receptionModel = new Reception();
        $rec = $receptionModel->getById($receptionId);
        if (!$rec) {
            echo json_encode(['success' => false, 'message' => 'Lễ tân không tồn tại!']);
            exit();
        }

        // Kiểm tra xung đột lịch trong bảng lich_lam_viec_le_tan
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        $checkSql = "SELECT COUNT(*) FROM lich_lam_viec_le_tan
                     WHERE letan_id = :id AND thu_trong_tuan = :thu
                       AND trang_thai = 'active'
                       AND (gio_bat_dau < :gio_ket_thuc AND gio_ket_thuc > :gio_bat_dau)";
        $st = $db->prepare($checkSql);
        $st->bindParam(':id', $receptionId, PDO::PARAM_INT);
        $st->bindParam(':thu', $thuTrongTuan);
        $st->bindParam(':gio_bat_dau', $gioBatDau);
        $st->bindParam(':gio_ket_thuc', $gioKetThuc);
        $st->execute();
        if ((int)$st->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Ca trực bị xung đột với ca đã có!']);
            exit();
        }

        $insertSql = "INSERT INTO lich_lam_viec_le_tan
                      (letan_id, thu_trong_tuan, gio_bat_dau, gio_ket_thuc, loai_ca, ghi_chu, trang_thai)
                      VALUES (:id, :thu, :gio_bat_dau, :gio_ket_thuc, :loai_ca, :ghi_chu, 'active')";
        $ins = $db->prepare($insertSql);
        $ins->bindParam(':id', $receptionId, PDO::PARAM_INT);
        $ins->bindParam(':thu', $thuTrongTuan);
        $ins->bindParam(':gio_bat_dau', $gioBatDau);
        $ins->bindParam(':gio_ket_thuc', $gioKetThuc);
        $ins->bindParam(':loai_ca', $loaiCa);
        $ins->bindParam(':ghi_chu', $ghiChu);

        if ($ins->execute()) {
            $newId = (int)$db->lastInsertId();
            echo json_encode([
                'success' => true,
                'message' => 'Thêm lịch làm việc lễ tân thành công!',
                'schedule_id' => $newId,
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi thêm lịch làm việc!']);
        }
        exit();
    }

    public function adminUpdateReceptionSchedule()
    {
        $this->auth->requireAuth('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $scheduleId = (int)($input['schedule_id'] ?? 0);
        $receptionId = (int)($input['reception_id'] ?? 0);
        $thuTrongTuan = trim($input['thu_trong_tuan'] ?? '');
        $gioBatDau = trim($input['gio_bat_dau'] ?? '');
        $gioKetThuc = trim($input['gio_ket_thuc'] ?? '');
        $loaiCa = trim($input['loai_ca'] ?? '');
        $ghiChu = trim($input['ghi_chu'] ?? '');
        $trangThai = trim($input['trang_thai'] ?? 'active');

        if ($scheduleId <= 0 || $receptionId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thông tin lịch không hợp lệ!']);
            exit();
        }
        if (empty($thuTrongTuan) || empty($gioBatDau) || empty($gioKetThuc) || empty($loaiCa)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc!']);
            exit();
        }
        if (strtotime($gioBatDau) >= strtotime($gioKetThuc)) {
            echo json_encode(['success' => false, 'message' => 'Giờ kết thúc phải sau giờ bắt đầu!']);
            exit();
        }

        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();

        // Kiểm tra xung đột, loại trừ chính lịch này
        $checkSql = "SELECT COUNT(*) FROM lich_lam_viec_le_tan
                     WHERE letan_id = :id AND thu_trong_tuan = :thu
                       AND trang_thai = 'active'
                       AND id != :sid
                       AND (gio_bat_dau < :gio_ket_thuc AND gio_ket_thuc > :gio_bat_dau)";
        $st = $db->prepare($checkSql);
        $st->bindParam(':id', $receptionId, PDO::PARAM_INT);
        $st->bindParam(':thu', $thuTrongTuan);
        $st->bindParam(':sid', $scheduleId, PDO::PARAM_INT);
        $st->bindParam(':gio_bat_dau', $gioBatDau);
        $st->bindParam(':gio_ket_thuc', $gioKetThuc);
        $st->execute();
        if ((int)$st->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Lịch làm việc này bị xung đột với lịch khác!']);
            exit();
        }

        $updateSql = "UPDATE lich_lam_viec_le_tan
                      SET thu_trong_tuan = :thu, gio_bat_dau = :gio_bat_dau,
                          gio_ket_thuc = :gio_ket_thuc, loai_ca = :loai_ca,
                          ghi_chu = :ghi_chu, trang_thai = :trang_thai,
                          ngay_cap_nhat = NOW()
                      WHERE id = :sid AND letan_id = :id";
        $stmt = $db->prepare($updateSql);
        $stmt->bindParam(':thu', $thuTrongTuan);
        $stmt->bindParam(':gio_bat_dau', $gioBatDau);
        $stmt->bindParam(':gio_ket_thuc', $gioKetThuc);
        $stmt->bindParam(':loai_ca', $loaiCa);
        $stmt->bindParam(':ghi_chu', $ghiChu);
        $stmt->bindParam(':trang_thai', $trangThai);
        $stmt->bindParam(':sid', $scheduleId, PDO::PARAM_INT);
        $stmt->bindParam(':id', $receptionId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật lịch làm việc lễ tân thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật lịch làm việc!']);
        }
        exit();
    }

    public function adminDeleteReceptionSchedule()
    {
        $this->auth->requireAuth('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $scheduleId = (int)($input['schedule_id'] ?? 0);
        $receptionId = (int)($input['reception_id'] ?? 0);

        if ($scheduleId <= 0 || $receptionId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thông tin lịch không hợp lệ!']);
            exit();
        }

        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        $sql = "DELETE FROM lich_lam_viec_le_tan WHERE id = :sid AND letan_id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':sid', $scheduleId, PDO::PARAM_INT);
        $stmt->bindParam(':id', $receptionId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Xóa lịch làm việc lễ tân thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi xóa lịch làm việc!']);
        }
        exit();
    }

    public function adminGetReceptionScheduleInfo()
    {
        $this->auth->requireAuth('admin');

        $scheduleId = (int)($_GET['schedule_id'] ?? 0);
        $receptionId = (int)($_GET['reception_id'] ?? 0);

        if ($scheduleId <= 0 || $receptionId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thông tin lịch không hợp lệ!']);
            exit();
        }

        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        $sql = "SELECT * FROM lich_lam_viec_le_tan WHERE id = :sid AND letan_id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':sid', $scheduleId, PDO::PARAM_INT);
        $stmt->bindParam(':id', $receptionId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy lịch làm việc!']);
        }
        exit();
    }

    /**
     * Trang đăng ký face recognition cho nhân viên (chỉ admin)
     */
    public function faceRegistration()
    {
        $this->auth->requireAuth('admin');

        require_once 'Models/Doctor.php';
        require_once 'Models/Reception.php';

        $doctorModel = new Doctor();
        $receptionModel = new Reception();

        // Lấy danh sách bác sĩ và lễ tân
        $doctors = $doctorModel->getAll();
        $receptions = $receptionModel->getAll();

        $page_title = 'Đăng ký nhận diện khuôn mặt';

        ob_start();
        include 'Views/admin/face_registration.php';
        $content = ob_get_clean();

        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, $page_title);
    }

    /**
     * API: Lưu face encoding (chỉ admin)
     */
    public function saveFaceEncoding()
    {
        $this->auth->requireAuth('admin');
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $userId = (int)($input['user_id'] ?? 0);
        $userType = trim($input['user_type'] ?? '');
        $faceEncoding = $input['face_encoding'] ?? null;
        $imageData = $input['image_data'] ?? null;

        if ($userId <= 0 || empty($userType) || empty($faceEncoding)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin bắt buộc!']);
            exit();
        }

        if (!in_array($userType, ['doctor', 'reception', 'admin'])) {
            echo json_encode(['success' => false, 'message' => 'Loại người dùng không hợp lệ!']);
            exit();
        }

        // Lưu ảnh mẫu nếu có
        $sampleImagePath = null;
        if ($imageData && !empty($imageData)) {
            $sampleImagePath = $this->saveSampleImage($imageData, $userId, $userType);
        }

        require_once 'Models/Attendance.php';
        $attendanceModel = new Attendance();

        $faceEncodingJson = is_string($faceEncoding) ? $faceEncoding : json_encode($faceEncoding);

        $result = $attendanceModel->saveFaceEncoding($userId, $userType, $faceEncodingJson, $sampleImagePath);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Đăng ký nhận diện khuôn mặt thành công!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi lưu face encoding!'
            ]);
        }
        exit();
    }

    /**
     * API: Lấy danh sách users theo type (doctor/reception)
     */
    public function getUsers()
    {
        $this->auth->requireAuth('admin');
        header('Content-Type: application/json; charset=utf-8');

        $userType = $_GET['type'] ?? '';

        if (!in_array($userType, ['doctor', 'reception'])) {
            echo json_encode(['success' => false, 'message' => 'Loại người dùng không hợp lệ!']);
            exit();
        }

        require_once 'Models/Doctor.php';
        require_once 'Models/Reception.php';

        if ($userType === 'doctor') {
            $model = new Doctor();
            $users = $model->getAll();
        } else {
            $model = new Reception();
            $users = $model->getAll();
        }

        echo json_encode([
            'success' => true,
            'users' => $users ?: []
        ]);
        exit();
    }

    /**
     * API: Lấy thông tin user
     */
    public function getUserInfo()
    {
        $this->auth->requireAuth('admin');
        header('Content-Type: application/json; charset=utf-8');

        $userId = (int)($_GET['id'] ?? 0);
        $userType = $_GET['type'] ?? '';

        if ($userId <= 0 || !in_array($userType, ['doctor', 'reception'])) {
            echo json_encode(['success' => false, 'message' => 'Thông tin không hợp lệ!']);
            exit();
        }

        require_once 'Models/Doctor.php';
        require_once 'Models/Reception.php';

        if ($userType === 'doctor') {
            $model = new Doctor();
        } else {
            $model = new Reception();
        }

        $user = $model->getById($userId);

        if ($user) {
            echo json_encode([
                'success' => true,
                'user' => $user
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy người dùng!'
            ]);
        }
        exit();
    }

    /**
     * Lưu ảnh mẫu face registration
     */
    private function saveSampleImage($imageData, $userId, $userType)
    {
        try {
            // Loại bỏ phần "data:image/png;base64," nếu có
            if (strpos($imageData, ',') !== false) {
                $imageData = explode(',', $imageData)[1];
            }

            $decodedData = base64_decode($imageData, true);
            if ($decodedData === false) {
                error_log("Failed to decode base64 image data");
                return null;
            }

            $uploadDir = __DIR__ . '/../uploads/face_samples/';
            if (!is_dir($uploadDir)) {
                $created = mkdir($uploadDir, 0777, true);
                if (!$created) {
                    error_log("Failed to create directory: $uploadDir");
                    return null;
                }
            }

            $fileName = 'face_sample_' . $userType . '_' . $userId . '_' . time() . '.jpg';
            $filePath = $uploadDir . $fileName;

            $written = file_put_contents($filePath, $decodedData);
            if ($written === false) {
                error_log("Failed to write file: $filePath");
                return null;
            }

            return 'uploads/face_samples/' . $fileName;
        } catch (Exception $e) {
            error_log("Error saving sample image: " . $e->getMessage());
            return null;
        }
    }
}
