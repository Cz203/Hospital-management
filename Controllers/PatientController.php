<?php
require_once 'Controllers/AuthController.php';
require_once 'Models/Patient.php';

class PatientController
{
    private $auth;
    private $patientModel;

    public function __construct()
    {
        $this->auth = new AuthController();
        $this->patientModel = new Patient();
    }

    /**
     * Cập nhật thông tin hồ sơ bệnh nhân
     */
    public function updateProfile()
    {
        $this->auth->requireAuth('patient');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ./patient_profile');
            exit();
        }

        $patientId = $_SESSION['user_id'];

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $dateOfBirth = trim($_POST['date_of_birth'] ?? '');
        $gender = trim($_POST['gioi_tinh'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $bloodGroup = trim($_POST['nhom_mau'] ?? '');

        // Lưu lại form data để hiển thị lại khi có lỗi
        $_SESSION['form_data'] = [
            'name' => $name,
            'email' => $email,
            'date_of_birth' => $dateOfBirth,
            'gioi_tinh' => $gender,
            'address' => $address,
            'nhom_mau' => $bloodGroup,
        ];

        // Validate cơ bản
        if (empty($name) || empty($email) || empty($dateOfBirth) || empty($gender) || empty($address)) {
            $_SESSION['error'] = 'Vui lòng điền đầy đủ các trường bắt buộc!';
            header('Location: ./patient_profile');
            exit();
        }

        // Validate email dạng @gmail.com (khớp với HTML pattern)
        if (!preg_match('/^[A-Za-z0-9._%+-]+@gmail\.com$/', $email)) {
            $_SESSION['error'] = 'Email phải có định dạng @gmail.com';
            header('Location: ./patient_profile');
            exit();
        }

        // Validate ngày sinh: không ở tương lai và tối thiểu 6 tuổi
        $dob = strtotime($dateOfBirth);
        if ($dob === false || $dob > time()) {
            $_SESSION['error'] = 'Ngày sinh không hợp lệ!';
            header('Location: ./patient_profile');
            exit();
        }
        try {
            $dobDate = new DateTime($dateOfBirth);
            $today = new DateTime();
            $age = $today->diff($dobDate)->y;
            if ($age < 6) {
                $_SESSION['error'] = 'Tuổi phải từ 6 trở lên.';
                header('Location: ./patient_profile');
                exit();
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Ngày sinh không hợp lệ!';
            header('Location: ./patient_profile');
            exit();
        }

        try {
            // Lấy thông tin hiện tại để kiểm tra trùng email
            $current = $this->patientModel->getById($patientId);
            if (!$current) {
                $_SESSION['error'] = 'Không tìm thấy tài khoản.';
                header('Location: ./patient_profile');
                exit();
            }

            if (strcasecmp($email, $current['email']) !== 0) {
                // Email thay đổi -> kiểm tra tồn tại
                if ($this->patientModel->emailExists($email)) {
                    $_SESSION['error'] = 'Email đã được sử dụng. Vui lòng chọn email khác.';
                    header('Location: ./patient_profile');
                    exit();
                }
            }

            $updateData = [
                'ten' => $name,
                'email' => $email,
                'ngay_sinh' => $dateOfBirth,
                'gioi_tinh' => $gender,
                'dia_chi' => $address,
                'nhom_mau' => $bloodGroup,
            ];

            $ok = $this->patientModel->updateProfileWithEmail($patientId, $updateData);

            if ($ok) {
                unset($_SESSION['form_data']);
                $_SESSION['success'] = 'Cập nhật thông tin thành công!';
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật thông tin.';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
        }

        header('Location: ./patient_profile');
        exit();
    }

    /**
     * Upload ảnh đại diện bệnh nhân
     */
    public function uploadAvatar()
    {
        $this->auth->requireAuth('patient');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ./patient_profile');
            exit();
        }

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Vui lòng chọn ảnh hợp lệ.';
            header('Location: ./patient_profile');
            exit();
        }

        $file = $_FILES['avatar'];

        // Validate kích thước (tối đa ~5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = 'Kích thước ảnh tối đa 5MB.';
            header('Location: ./patient_profile');
            exit();
        }

        // Validate loại file
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif'
        ];
        if (!isset($allowed[$mime])) {
            $_SESSION['error'] = 'Chỉ chấp nhận JPG, PNG, GIF.';
            header('Location: ./patient_profile');
            exit();
        }

        // Thư mục lưu upload
        $uploadDir = __DIR__ . '/../uploads';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }

        $ext = $allowed[$mime];
        $patientId = $_SESSION['user_id'];
        $safeName = 'patient_' . $patientId . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . DIRECTORY_SEPARATOR . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $_SESSION['error'] = 'Không thể lưu tệp tải lên.';
            header('Location: ./patient_profile');
            exit();
        }

        // Đường dẫn tương đối để hiển thị trong <img src>
        $publicPath = 'uploads/' . $safeName;

        try {
            $ok = $this->patientModel->updateImage($patientId, $publicPath);
            if ($ok) {
                $_SESSION['success'] = 'Cập nhật ảnh đại diện thành công!';
            } else {
                $_SESSION['error'] = 'Không thể cập nhật ảnh đại diện trong hệ thống.';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
        }

        header('Location: ./patient_profile');
        exit();
    }
}


