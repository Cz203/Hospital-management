<?php

require_once 'Models/Admin.php';
require_once 'Models/Doctor.php';
require_once 'Models/Patient.php';

class AuthController
{

    public function loginAdmin()
    {
        if ($this->isLoggedIn()) {
            $role = $_SESSION['user_role'];
            if ($role === 'admin') {
                header("Location: /hospital_management/admin_dashboard");
                exit();
            }
            // Nếu đã đăng nhập role khác thì đưa về dashboard tương ứng
            switch ($role) {
                case 'doctor':
                    header("Location: /hospital_management/doctor_dashboard");
                    exit();
                case 'patient':
                    header("Location: /hospital_management/patient_dashboard");
                    exit();
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
                header("Location: /hospital_management/login_admin");
                exit();
            }

            $admin = new Admin();
            $user = $admin->login($email, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['ten'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = 'admin';
                header("Location: /hospital_management/admin_dashboard");
                exit();
            }

            $_SESSION['error'] = "Email hoặc mật khẩu không đúng!";
            header("Location: /hospital_management/login_admin");
            exit();
        }

        include 'Views/auth/login_admin.php';
    }

    public function loginDoctor()
    {
        if ($this->isLoggedIn()) {
            $role = $_SESSION['user_role'];
            if ($role === 'doctor') {
                header("Location: /hospital_management/doctor_dashboard");
                exit();
            }
            switch ($role) {
                case 'admin':
                    header("Location: /hospital_management/admin_dashboard");
                    exit();
                case 'patient':
                    header("Location: /hospital_management/patient_dashboard");
                    exit();
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
                header("Location: /hospital_management/login_doctor");
                exit();
            }

            $doctor = new Doctor();
            $user = $doctor->login($email, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['ten'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = 'doctor';
                $_SESSION['specialization'] = $user['chuyen_khoa'];
                header("Location: /hospital_management/doctor_dashboard");
                exit();
            }

            $_SESSION['error'] = "Email hoặc mật khẩu không đúng!";
            header("Location: /hospital_management/login_doctor");
            exit();
        }

        include 'Views/auth/login_doctor.php';
    }

    public function loginPatient()
    {
        if ($this->isLoggedIn()) {
            $role = $_SESSION['user_role'];
            if ($role === 'patient') {
                header("Location: /hospital_management/patient_dashboard");
                exit();
            }
            switch ($role) {
                case 'admin':
                    header("Location: /hospital_management/admin_dashboard");
                    exit();
                case 'doctor':
                    header("Location: /hospital_management/doctor_dashboard");
                    exit();
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
                header("Location: /hospital_management/login");
                exit();
            }

            $patient = new Patient();
            $user = $patient->login($email, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['ten'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = 'patient';
                header("Location: /hospital_management/patient_dashboard");
                exit();
            }

            $_SESSION['error'] = "Email hoặc mật khẩu không đúng!";
            header("Location: /hospital_management/login");
            exit();
        }

        include 'Views/auth/login.php';
    }

    public function register()
    {
        // Kiểm tra nếu user đã đăng nhập thì redirect về dashboard tương ứng
        if ($this->isLoggedIn()) {
            $role = $_SESSION['user_role'];
            switch ($role) {
                case 'admin':
                    header("Location: /hospital_management/admin_dashboard");
                    exit();
                case 'doctor':
                    header("Location: /hospital_management/doctor_dashboard");
                    exit();
                case 'patient':
                    header("Location: /hospital_management/patient_dashboard");
                    exit();
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $role = $_POST['role'] ?? '';

            if (empty($name) || empty($email) || empty($password) || empty($role)) {
                $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
                header("Location: /hospital_management/register");
                exit();
            }

            if ($password !== $confirm_password) {
                $_SESSION['error'] = "Mật khẩu xác nhận không khớp!";
                header("Location: /hospital_management/register");
                exit();
            }

            $success = false;

            switch ($role) {
                case 'admin':
                    $admin = new Admin();
                    $data = [
                        'ten' => $name,
                        'email' => $email,
                        'mat_khau' => $password,
                        'so_dien_thoai' => $_POST['phone'] ?? ''
                    ];
                    $success = $admin->create($data);
                    break;

                case 'doctor':
                    $doctor = new Doctor();
                    $data = [
                        'ten' => $name,
                        'email' => $email,
                        'mat_khau' => $password,
                        'so_dien_thoai' => $_POST['phone'] ?? '',
                        'chuyen_khoa' => $_POST['specialization'] ?? '',
                        'so_giay_phep' => $_POST['license_number'] ?? '',
                        'so_nam_kinh_nghiem' => $_POST['experience_years'] ?? 0
                    ];
                    $success = $doctor->create($data);
                    break;

                case 'patient':
                    $patient = new Patient();
                    $data = [
                        'ten' => $name,
                        'email' => $email,
                        'mat_khau' => $password,
                        'so_dien_thoai' => $_POST['phone'] ?? '',
                        'ngay_sinh' => $_POST['date_of_birth'] ?? '',
                        'gioi_tinh' => $_POST['gender'] ?? '',
                        'dia_chi' => $_POST['address'] ?? '',
                        'nhom_mau' => $_POST['blood_group'] ?? ''
                    ];
                    $success = $patient->create($data);
                    break;
            }

            if ($success) {
                $_SESSION['success'] = "Đăng ký thành công! Vui lòng đăng nhập.";
                header("Location: /hospital_management/login");
                exit();
            } else {
                $_SESSION['error'] = "Có lỗi xảy ra khi đăng ký!";
                header("Location: /hospital_management/register");
                exit();
            }
        }

        // Hiển thị form register
        include 'Views/auth/register.php';
    }

    public function logout()
    {
        session_destroy();
        header("Location: /hospital_management/");
        exit();
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public function requireAuth($role = null)
    {
        if (!$this->isLoggedIn()) {
            header("Location: /hospital_management/login");
            exit();
        }

        if ($role && $_SESSION['user_role'] !== $role) {
            header("Location: /hospital_management/");
            exit();
        }
    }
}