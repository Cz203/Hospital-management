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
}