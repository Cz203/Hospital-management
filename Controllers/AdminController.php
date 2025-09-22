<?php

require_once 'Models/Admin.php';
require_once 'Models/Doctor.php';
require_once 'Models/Specialty.php';

class AdminController
{
    private $auth;
    private $adminModel;
    private $doctorModel;
    private $specialtyModel;

    public function __construct()
    {
        $this->auth = new AuthController();
        $this->adminModel = new Admin();
        $this->doctorModel = new Doctor();
        $this->specialtyModel = new Specialty();
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
        // Đây là placeholder, bạn có thể implement logic thống kê thực tế
        return [
            'total_doctors' => 10,
            'total_patients' => 150,
            'total_appointments' => 45,
            'total_revenue' => 50000000
        ];
    }
}