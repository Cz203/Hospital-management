<?php

require_once 'Models/Admin.php';
require_once 'Models/Doctor.php';
require_once 'Models/Patient.php';

class AuthController
{

    public function login()
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
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? '';

            if (empty($email) || empty($password) || empty($role)) {
                $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
                header("Location: /hospital_management/login");
                exit();
            }

            $user = null;

            switch ($role) {
                case 'admin':
                    $admin = new Admin();
                    $user = $admin->login($email, $password);
                    if ($user) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['ten'];
                        $_SESSION['user_role'] = 'admin';
                        header("Location: /hospital_management/admin_dashboard");
                        exit();
                    }
                    break;

                case 'doctor':
                    $doctor = new Doctor();
                    $user = $doctor->login($email, $password);
                    if ($user) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['ten'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = 'doctor';
                        $_SESSION['specialization'] = $user['specialization'];
                        header("Location: /hospital_management/doctor_dashboard");
                        exit();
                    }
                    break;

                case 'patient':
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
                    break;
            }

            if (!$user) {
                $_SESSION['error'] = "Email hoặc mật khẩu không đúng!";
                header("Location: /hospital_management/login");
                exit();
            }
        }

        // Hiển thị form login
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
                        'name' => $name,
                        'email' => $email,
                        'password' => $password,
                        'phone' => $_POST['phone'] ?? ''
                    ];
                    $success = $admin->create($data);
                    break;

                case 'doctor':
                    $doctor = new Doctor();
                    $data = [
                        'name' => $name,
                        'email' => $email,
                        'password' => $password,
                        'phone' => $_POST['phone'] ?? '',
                        'specialization' => $_POST['specialization'] ?? '',
                        'license_number' => $_POST['license_number'] ?? '',
                        'experience_years' => $_POST['experience_years'] ?? 0
                    ];
                    $success = $doctor->create($data);
                    break;

                case 'patient':
                    $patient = new Patient();
                    $data = [
                        'name' => $name,
                        'email' => $email,
                        'password' => $password,
                        'phone' => $_POST['phone'] ?? '',
                        'date_of_birth' => $_POST['date_of_birth'] ?? '',
                        'gender' => $_POST['gender'] ?? '',
                        'address' => $_POST['address'] ?? '',
                        'blood_group' => $_POST['blood_group'] ?? ''
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