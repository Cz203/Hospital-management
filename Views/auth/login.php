<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống Quản lý Bệnh viện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="././/assets/css/login.css">

</head>

<body>
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-hospital fa-3x mb-3"></i>
            <h3>Đăng nhập</h3>
            <p class="mb-0">Hệ thống Quản lý Bệnh viện</p>
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

            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                <?php echo $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form method="POST" action="/hospital_management/login">
                <div class="role-selector">
                    <div class="role-option" data-role="patient">
                        <i class="fas fa-user"></i><br>
                        <small>Bệnh nhân</small>
                    </div>
                    <div class="role-option active" data-role="admin">
                        <i class="fas fa-user-shield"></i><br>
                        <small>Admin</small>
                    </div>
                    <div class="role-option" data-role="doctor">
                        <i class="fas fa-user-md"></i><br>
                        <small>Bác sĩ</small>
                    </div>

                </div>

                <input type="hidden" name="role" id="selectedRole" value="admin">

                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Mật khẩu
                    </label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">
                        Ghi nhớ đăng nhập
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập
                </button>
            </form>

            <div class="text-center mt-3">
                <p class="mb-0">Chưa có tài khoản?
                    <a href="/hospital_management/register" class="text-decoration-none">Đăng ký ngay</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Xử lý chọn role
    document.querySelectorAll('.role-option').forEach(option => {
        option.addEventListener('click', function() {
            // Xóa active class từ tất cả options
            document.querySelectorAll('.role-option').forEach(opt => {
                opt.classList.remove('active');
            });

            // Thêm active class cho option được chọn
            this.classList.add('active');

            // Cập nhật giá trị hidden input
            document.getElementById('selectedRole').value = this.dataset.role;
        });
    });
    </script>
</body>

</html>