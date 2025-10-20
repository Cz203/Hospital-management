<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Bác sĩ Siêu âm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/login.css">
</head>

<body>
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-stethoscope fa-3x mb-3"></i>
            <h3>Đăng nhập Bác sĩ Siêu âm</h3>
            <p class="mb-0">Chuyên khoa: Siêu âm</p>
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

            <form method="POST" action="./login_sieuam">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="mb-3">
                    <label for="phone" class="form-label">
                        <i class="fas fa-phone"></i> Số điện thoại
                    </label>
<<<<<<< HEAD
                    <input type="tel" class="form-control" id="phone" name="phone" required>
=======
                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="Nhập số điện thoại" required>
>>>>>>> origin/Thinh
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
    <script>
        // Format số điện thoại khi nhập
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Chỉ giữ lại số
            e.target.value = value;
        });

        // Validate số điện thoại trước khi submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const phone = document.getElementById('phone').value;
            if (phone.length < 10) {
                e.preventDefault();
                alert('Số điện thoại phải có ít nhất 10 chữ số!');
                return false;
            }
        });
    </script>
</body>

</html>