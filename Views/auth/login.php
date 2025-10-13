<?php

$page_title = 'Đăng nhập';
?>

<?php include './Views/layouts/header.php'; ?>

<!-- Main Content -->
<main class="login-container">
    <!-- Login Form -->
    <div id="loginForm" class="form-step active">
        <div class="login-header">

            <h3>Đăng nhập</h3>

        </div>

        <div class="login-body">
            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form method="POST" action="./login">
                <input type="hidden" name="role" value="patient">

                <div class="mb-4">
                    <label for="phone" class="form-label">
                        <i class="fas fa-phone me-2"></i>Số điện thoại
                    </label>
                    <input type="tel" class="form-control" id="phone" name="phone" required>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-2"></i>Mật khẩu
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button type="button" class="btn"
                            onclick="togglePasswordVisibility('password', 'passwordIcon')">
                            <i class="fas fa-eye" id="passwordIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-4 text-end">
                    <a href="#" class="forgot-password" onclick="showForgotPassword()">
                        <i class="fas fa-key me-1"></i>Quên mật khẩu?
                    </a>
                </div>

                <button type="submit" class="btn btn-login w-100 mb-4">
                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                </button>

                <div class="text-center">
                    <p class="mb-0">Chưa có tài khoản?
                        <a href="./register" class="register-link">Đăng ký ngay</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <!-- Forgot Password Form -->
    <div id="forgotPasswordForm" class="form-step">

        <div class="login-body">
            <!-- Step Indicators -->
            <div class="step-indicator">
                <div class="step-dot active" id="step1Dot"></div>
                <div class="step-dot" id="step2Dot"></div>
                <div class="step-dot" id="step3Dot"></div>
            </div>

            <!-- Step 1: Phone Number -->
            <div id="forgotStep1" class="form-step active">
                <h5 class="mb-4 text-center">Nhập số điện thoại</h5>
                <div class="mb-4">
                    <label for="forgotPhone" class="form-label">
                        <i class="fas fa-phone me-2"></i>Số điện thoại
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <img src="./assets/img/vn.png" alt="Vietnam Flag" style="width: 20px; height: 20px;"
                                class="me-1">
                            +84
                        </span>
                        <input type="text" class="form-control" id="forgotPhone" maxlength="11"
                            placeholder="Nhập số điện thoại" required>
                        <button type="button" class="btn btn-primary" id="sendForgotOtpBtn">
                            <i class="fas fa-paper-plane me-1"></i>Gửi OTP
                        </button>
                    </div>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>Nhập số điện thoại đã đăng ký
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="#" class="back-to-login" onclick="showLoginForm()">
                        <i class="fas fa-arrow-left"></i>Quay lại đăng nhập
                    </a>
                </div>
            </div>

            <!-- Step 2: OTP Verification -->
            <div id="forgotStep2" class="form-step">
                <h5 class="mb-4 text-center">Xác thực OTP</h5>

                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Mã OTP đã được gửi đến:</strong>
                    <span id="forgotTargetPhone" class="fw-bold"></span>
                </div>

                <div class="alert alert-warning mb-4" id="forgotTestModeAlert" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>TEST MODE:</strong>
                    <span id="forgotTestOtpCode" class="fw-bold fs-5"></span>
                    <br><small>Mã OTP này được hiển thị vì tài khoản Vonage hết tiền.</small>
                </div>

                <div class="alert alert-info mb-4" id="forgotOtpTimerAlert" style="display: none;">
                    <i class="fas fa-clock me-2"></i>
                    <strong>Thời gian còn lại:</strong>
                    <span id="forgotOtpTimer" class="timer"></span>
                </div>

                <div class="mb-4">
                    <label for="forgotOtpCode" class="form-label">
                        <i class="fas fa-key me-2"></i>Mã OTP
                    </label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="forgotOtpCode" maxlength="6" placeholder="123456">
                        <button type="button" class="btn btn-success" id="verifyForgotOtpBtn">
                            <i class="fas fa-check me-1"></i>Xác thực
                        </button>
                    </div>
                </div>

                <div id="forgotOtpStatus" class="alert" style="display: none;"></div>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-secondary" onclick="showForgotStep1()">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </button>
                </div>
            </div>

            <!-- Step 3: New Password -->
            <div id="forgotStep3" class="form-step">
                <h5 class="mb-4 text-center">Đặt mật khẩu mới</h5>

                <div class="mb-4">
                    <label for="newPassword" class="form-label">
                        <i class="fas fa-lock me-2"></i>Mật khẩu mới
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="newPassword" required>
                        <button type="button" class="btn"
                            onclick="togglePasswordVisibility('newPassword', 'newPasswordIcon')">
                            <i class="fas fa-eye" id="newPasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="confirmNewPassword" class="form-label">
                        <i class="fas fa-lock me-2"></i>Xác nhận mật khẩu mới
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="confirmNewPassword" required>
                        <button type="button" class="btn"
                            onclick="togglePasswordVisibility('confirmNewPassword', 'confirmNewPasswordIcon')">
                            <i class="fas fa-eye" id="confirmNewPasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary" id="resetPasswordBtn">
                        <i class="fas fa-save me-2"></i>Đặt lại mật khẩu
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="showForgotStep2()">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </button>
                </div>
            </div>
        </div>

    </div>

</main>

<!-- Custom CSS for Login Page -->
<link rel="stylesheet" href="./assets/css/login.css">

<!-- Custom JS for Login Page -->
<script src="./assets/js/login.js"></script>

<?php include './Views/layouts/footer.php'; ?>