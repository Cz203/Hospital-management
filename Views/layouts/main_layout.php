<?php
// Đảm bảo session đã được start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra user đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login");
    exit();
}
var_dump($_SESSION);
$user_role = $_SESSION['user_role'];
$page_title = $page_title ?? 'Hệ thống Quản lý Bệnh viện';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #f8f9fa;
    }

    .main-content {
        margin-left: 250px;
        padding: 20px;
        min-height: 100vh;
    }

    .navbar {
        margin-left: 250px;
    }

    .navbar-brand {
        font-weight: 600;
        font-size: 1.5rem;
    }

    .navbar-nav .nav-link {
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .navbar-nav .nav-link:hover {
        color: #667eea !important;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
    }

    .dropdown-menu {
        border: none;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }

    .dropdown-item {
        padding: 8px 20px;
        transition: all 0.3s ease;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #667eea;
    }

    .role-badge {
        font-size: 0.75rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
    }

    .role-admin {
        background-color: #dc3545;
        color: white;
    }

    .role-doctor {
        background-color: #198754;
        color: white;
    }

    .role-patient {
        background-color: #0d6efd;
        color: white;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
        }

        .navbar {
            margin-left: 0;
        }

        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .sidebar.show {
            transform: translateX(0);
        }
    }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <?php
    switch ($user_role) {
        case 'admin':
            include 'Views/layouts/admin_sidebar.php';
            break;
        case 'doctor':
            include 'Views/layouts/doctor_sidebar.php';
            break;
        case 'patient':
            include 'Views/layouts/patient_sidebar.php';
            break;
    }
    ?>

    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <!-- Mobile Toggle -->
            <button class="navbar-toggler d-lg-none" type="button" onclick="toggleSidebar()">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Logo -->
            <a class="navbar-brand" href="./home">
                <i class="fas fa-hospital text-primary me-2"></i>
                Hospital Management
            </a>

            <!-- Navigation Items -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if ($user_role == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="./admin_dashboard">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-users me-1"></i>Quản lý người dùng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-calendar-alt me-1"></i>Lịch hẹn
                        </a>
                    </li>
                    <?php elseif ($user_role == 'doctor'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="./doctor_dashboard">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-calendar-check me-1"></i>Lịch hẹn
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-user-injured me-1"></i>Bệnh nhân
                        </a>
                    </li>
                    <?php elseif ($user_role == 'patient'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="./patient_dashboard">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-calendar-plus me-1"></i>Đặt lịch
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-file-medical me-1"></i>Hồ sơ bệnh án
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>

                <!-- User Menu -->
                <div class="navbar-nav">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button"
                            data-bs-toggle="dropdown">
                            <div class="user-avatar me-2">
                                <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                            </div>
                            <div class="user-info">
                                <div>
                                    <div class="fw-bold"><?php echo $_SESSION['user_name']; ?></div>
                                    <small class="text-muted">
                                        <?php
                                        $role_text = '';
                                        $role_class = '';
                                        switch ($user_role) {
                                            case 'admin':
                                                $role_text = 'Quản trị viên';
                                                $role_class = 'role-admin';
                                                break;
                                            case 'doctor':
                                                $role_text = 'Bác sĩ';
                                                $role_class = 'role-doctor';
                                                break;
                                            case 'patient':
                                                $role_text = 'Bệnh nhân';
                                                $role_class = 'role-patient';
                                                break;
                                        }
                                        ?>
                                        <span
                                            class="role-badge <?php echo $role_class; ?>"><?php echo $role_text; ?></span>
                                    </small>
                                </div>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="./<?php echo $user_role; ?>_profile"><i
                                        class="fas fa-user me-2"></i>Hồ sơ</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Cài đặt</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="./logout"><i class="fas fa-sign-out-alt me-2"></i>Đăng
                                    xuất</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <?php echo $_SESSION['success']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo $_SESSION['error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Page Content -->
        <?php echo $content ?? ''; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('show');
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.querySelector('.navbar-toggler');

        if (window.innerWidth <= 768) {
            if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        }
    });
    </script>
</body>

</html>