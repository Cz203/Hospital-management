<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'ThinhViet Hospital'; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="./assets/css/home.css?v=<?php echo filemtime('./assets/css/home.css'); ?>">

    <!-- Additional CSS for specific pages -->
    <?php if (isset($additional_css)): ?>
    <?php foreach ($additional_css as $css): ?>
    <link rel="stylesheet" href="<?php echo $css; ?>">
    <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container">
            <!-- Brand -->
            <a class="navbar-brand d-flex align-items-center" href="./home">
                <div class="brand-icon me-2">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="brand-text">
                    <span class="brand-name">ThinhViet</span>
                    <span class="brand-subtitle">Hospital</span>
                </div>
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Main Navigation -->
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">
                            <i class="fas fa-home me-1"></i>Trang chủ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#appointment">
                            <i class="fas fa-calendar-check me-1"></i>Đặt lịch
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#chuyenkhoa">
                            <i class="fas fa-stethoscope me-1"></i>Chuyên khoa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#doctors">
                            <i class="fas fa-user-md me-1"></i>Bác sĩ
                        </a>
                    </li>
                </ul>

                <!-- Authentication Links -->
                <ul class="navbar-nav">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light btn-sm px-3" href="login">
                            <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="nav-link btn btn-primary btn-sm px-3" href="./register">
                            <i class="fas fa-user-plus me-1"></i>Đăng ký
                        </a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <div class="dropdown d-flex align-items-center">
                            <div class="user-avatar me-2">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <span class="user-name me-1"><?php echo $_SESSION['user_name']; ?></span>
                            <button class="btn btn-link text-white p-0 ms-1" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="./<?php echo $_SESSION['user_role']; ?>_dashboard">
                                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="./<?php echo $_SESSION['user_role']; ?>_profile">
                                        <i class="fas fa-user me-2"></i>Hồ sơ
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="./logout">
                                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Scroll Progress Bar removed to reduce lag -->