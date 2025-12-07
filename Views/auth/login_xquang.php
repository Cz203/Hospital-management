<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Bác sĩ X-Quang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/login.css">
    <style>
        .role-switch-bar {
            max-width: 480px;
            margin: 12px auto 0 auto;
            font-size: 0.78rem;
            text-align: center;
            color: #6b7280;
        }

        .role-switch-bar .role-pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 9px;
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            margin: 2px 3px;
            text-decoration: none;
            color: #374151;
            background-color: #ffffff;
            transition: background-color .16s ease, color .16s ease, border-color .16s ease;
        }

        .role-switch-bar .role-pill:hover {
            background-color: #ecfeff;
            border-color: #0d9488;
            color: #0d9488;
        }

        .role-switch-bar .role-pill.active {
            background-color: #0d9488;
            border-color: #0d9488;
            color: #ffffff;
        }
    </style>
</head>

<body>
    <?php
    $current_uri = $_SERVER['REQUEST_URI'] ?? '';
    ?>
    <div class="role-switch-bar">
        <span class="me-1">Chuyển nhanh:</span>
        <a href="./login_doctor"
            class="role-pill <?php echo (strpos($current_uri, 'login_doctor') !== false) ? 'active' : ''; ?>">BS
            Khám</a>
        <a href="./login_xquang"
            class="role-pill <?php echo (strpos($current_uri, 'login_xquang') !== false) ? 'active' : ''; ?>">BS
            X-Quang</a>
        <a href="./login_sieuam"
            class="role-pill <?php echo (strpos($current_uri, 'login_sieuam') !== false) ? 'active' : ''; ?>">BS
            Siêu âm</a>
        <a href="./login_xetnghiem"
            class="role-pill <?php echo (strpos($current_uri, 'login_xetnghiem') !== false) ? 'active' : ''; ?>">BS
            Xét nghiệm</a>
        <a href="./login_reception"
            class="role-pill <?php echo (strpos($current_uri, 'login_reception') !== false) ? 'active' : ''; ?>">Lễ
            tân</a>
        <a href="./login_admin"
            class="role-pill <?php echo (strpos($current_uri, 'login_admin') !== false) ? 'active' : ''; ?>">Admin</a>
    </div>

    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-x-ray fa-3x mb-3"></i>
            <h3>Đăng nhập Bác sĩ X-Quang</h3>
            <p class="mb-0">Chuyên khoa: Chẩn đoán hình ảnh</p>
        </div>

        <div class="login-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $_SESSION['error']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" action="./login_xquang">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="mb-3">
                    <label for="phone" class="form-label">
                        <i class="fas fa-phone"></i> Số điện thoại
                    </label>
                    <input type="tel" class="form-control" id="phone" name="phone" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Mật khẩu
                    </label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>