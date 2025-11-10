<?php

$page_title = 'Đăng ký - ThinhViet Hospital';

// Lấy dữ liệu form đã lưu (nếu có)
$formData = $_SESSION['form_data'] ?? [];
?>

<?php include './Views/layouts/header.php'; ?>

<!-- Main Content -->
<main class="register-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7 col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Đăng ký tài khoản</h3>
                    </div>
                    <div class="card-body">

                        <!-- Progress Bar -->
                        <div class="progress mb-4">
                            <div class="progress-bar" id="progressBar" role="progressbar" style="width: 33.33%;"
                                aria-valuenow="33" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        <!-- Step Indicators -->
                        <div class="d-flex justify-content-between mb-4">
                            <div class="step-indicator active" id="step1Indicator">
                                <div class="step-number">1</div>
                                <div class="step-text">Xác thực SĐT</div>
                            </div>
                            <div class="step-indicator" id="step2Indicator">
                                <div class="step-number">2</div>
                                <div class="step-text">Mật khẩu</div>
                            </div>
                            <div class="step-indicator" id="step3Indicator">
                                <div class="step-number">3</div>
                                <div class="step-text">Thông tin</div>
                            </div>
                        </div>

                        <form id="registerForm" method="POST" action="./register">
                            <!-- Bước 1: Xác thực số điện thoại và OTP -->
                            <div id="step1" class="form-step active">
                                <h5 class="section-title mb-4">
                                    <i class="icofont-phone me-2"></i>Bước 1: Xác thực số điện thoại
                                </h5>

                                <!-- Xác thực số điện thoại -->
                                <div class="mb-3">
                                    <label for="so_dien_thoai" class="form-label">
                                        <i class="icofont-phone me-1"></i>Số điện thoại *
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <img src="./assets/img/vn.png" alt="Vietnam Flag"
                                                style="width: 20px; height: 20px;" class="me-1">
                                            +84
                                        </span>
                                        <input type="text" class="form-control" id="so_dien_thoai" name="so_dien_thoai"
                                            maxlength="11" placeholder="Nhập số điện thoại"
                                            value="<?php echo htmlspecialchars($formData['so_dien_thoai'] ?? ''); ?>"
                                            required>
                                        <button type="button" class="btn btn-outline-primary btn-send-otp"
                                            id="sendOtpBtn">
                                            <i class="icofont-paper-plane me-1"></i>Gửi OTP
                                        </button>
                                    </div>
                                </div>

                                <!-- OTP Verification Section -->
                                <div class="otp-section" id="otpSection">
                                    <div class="mb-3">
                                        <div class="alert alert-info">
                                            <i class="icofont-info-circle me-2"></i>
                                            <strong>Mã OTP đã được gửi đến:</strong>
                                            <span id="targetPhoneDisplay" class="fw-bold"></span>
                                        </div>
                                        <div class="alert alert-warning" id="testModeAlert" style="display: none;">
                                            <i class="icofont-warning me-2"></i>
                                            <strong>TEST MODE:</strong>
                                            <span id="testOtpCode" class="fw-bold fs-5"></span>
                                            <br><small>Mã OTP này được hiển thị vì tài khoản Vonage hết tiền. Vui
                                                lòng sử dụng mã này để xác thực.</small>
                                        </div>
                                        <div class="alert alert-info" id="otpTimerAlert" style="display: none;">
                                            <i class="icofont-clock-time me-2"></i>
                                            <strong>Thời gian còn lại:</strong>
                                            <span id="otpTimer" class="fw-bold fs-5 text-danger"></span>
                                            <br><small>Mã OTP sẽ hết hạn sau khi hết thời gian.</small>
                                        </div>
                                        <label for="otp_code" class="form-label">
                                            <i class="icofont-key me-1"></i>Mã OTP *
                                        </label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="otp_code" name="otp_code"
                                                pattern="[0-9]{6}" maxlength="6" placeholder="123456"
                                                value="<?php echo htmlspecialchars($formData['otp_code'] ?? ''); ?>">
                                            <button type="button" class="btn btn-outline-success" id="verifyOtpBtn">
                                                <i class="icofont-check-circled me-1"></i>Xác thực
                                            </button>
                                        </div>
                                        <div class="form-text">
                                            <i class="icofont-sms me-1"></i>Nhập mã 6 số đã được gửi qua SMS
                                        </div>
                                    </div>
                                    <div id="otpStatus" class="alert" style="display: none;"></div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-primary w-100" id="step1NextBtn" disabled>
                                        <i class="icofont-arrow-right me-2"></i>Tiếp tục
                                    </button>
                                </div>
                            </div>

                            <!-- Bước 2: Nhập mật khẩu -->
                            <div id="step2" class="form-step">
                                <h5 class="section-title mb-4">
                                    <i class="icofont-lock me-2"></i>Bước 2: Tạo mật khẩu
                                </h5>

                                <!-- Mật khẩu -->
                                <div class="mb-3">
                                    <label for="mat_khau" class="form-label">
                                        <i class="icofont-lock me-1"></i>Mật khẩu *
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="mat_khau" name="mat_khau"
                                            value="<?php echo htmlspecialchars($formData['mat_khau'] ?? ''); ?>"
                                            required oninput="validatePassword()">
                                        <button type="button" class="btn btn-outline-secondary"
                                            onclick="togglePasswordVisibility('mat_khau', 'matKhauIcon')">
                                            <i class="icofont-eye" id="matKhauIcon"></i>
                                        </button>
                                    </div>

                                    <!-- Password Strength Indicator -->
                                    <div class="password-strength mt-2" id="passwordStrength" style="display: none;">
                                        <div class="strength-bar">
                                            <div class="strength-fill" id="strengthFill"></div>
                                        </div>
                                        <div class="strength-text" id="strengthText"></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="xac_nhan_mat_khau" class="form-label">
                                        <i class="icofont-lock me-1"></i>Xác nhận mật khẩu *
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="xac_nhan_mat_khau"
                                            name="xac_nhan_mat_khau"
                                            value="<?php echo htmlspecialchars($formData['xac_nhan_mat_khau'] ?? ''); ?>"
                                            required oninput="validateConfirmPassword()">
                                        <button type="button" class="btn btn-outline-secondary"
                                            onclick="togglePasswordVisibility('xac_nhan_mat_khau', 'xacNhanMatKhauIcon')">
                                            <i class="icofont-eye" id="xacNhanMatKhauIcon"></i>
                                        </button>
                                    </div>
                                    <div class="password-match mt-2" id="passwordMatch" style="display: none;">
                                        <small id="matchText"></small>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <button type="button" class="btn btn-secondary" id="step2PrevBtn">
                                        <i class="icofont-arrow-left me-2"></i>Quay lại
                                    </button>
                                    <button type="button" class="btn btn-primary" id="step2NextBtn" disabled>
                                        <i class="icofont-arrow-right me-2"></i>Tiếp tục
                                    </button>
                                </div>
                            </div>

                            <!-- Bước 3: Thông tin cá nhân -->
                            <div id="step3" class="form-step">
                                <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger">
                                    <i class="icofont-warning me-2"></i>
                                    <?php echo $_SESSION['error'];
                                        unset($_SESSION['error']); ?>
                                </div>
                                <?php endif; ?>

                                <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success">
                                    <i class="icofont-check-circled me-2"></i>
                                    <?php echo $_SESSION['success'];
                                        unset($_SESSION['success']); ?>
                                </div>
                                <?php endif; ?>

                                <h5 class="section-title mb-4">
                                    <i class="icofont-user-alt-4 me-2"></i>Bước 3: Thông tin cá nhân
                                </h5>

                                <!-- CCCD ở dòng đầu tiên -->
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="cccd" class="form-label">
                                            <i class="icofont-id-card me-1"></i>Căn cước công dân (CCCD)
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="cccd" name="cccd"
                                            placeholder="Nhập 12 số CCCD để tự động điền thông tin" maxlength="12"
                                            pattern="\d{12}"
                                            value="<?php echo htmlspecialchars($formData['cccd'] ?? ''); ?>" required>
                                        <div class="form-text">
                                            <i class="icofont-info-circle"></i> CCCD gồm 12 số - Nhập CCCD để tự động
                                            điền thông tin bên dưới
                                        </div>
                                        <div id="cccd-verification-result" class="mt-2"></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">
                                            <i class="icofont-user-alt-4 me-1"></i>Họ và tên *
                                        </label>
                                        <input type="text" class="form-control" id="name" name="ten"
                                            value="<?php echo htmlspecialchars($formData['ten'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">
                                            <i class="icofont-email me-1"></i>Email *
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="ngay_sinh" class="form-label">
                                            <i class="icofont-calendar me-1"></i>Ngày sinh
                                        </label>
                                        <input type="date" class="form-control" id="ngay_sinh" name="ngay_sinh"
                                            value="<?php echo htmlspecialchars($formData['ngay_sinh'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="gioi_tinh" class="form-label">
                                            <i class="icofont-users-alt-4 me-1"></i>Giới tính
                                        </label>
                                        <select class="form-select" id="gioi_tinh" name="gioi_tinh">
                                            <option value="">Chọn giới tính</option>
                                            <option value="Nam"
                                                <?php echo ($formData['gioi_tinh'] ?? '') === 'Nam' ? 'selected' : ''; ?>>
                                                Nam</option>
                                            <option value="Nữ"
                                                <?php echo ($formData['gioi_tinh'] ?? '') === 'Nữ' ? 'selected' : ''; ?>>
                                                Nữ</option>
                                            <option value="Khác"
                                                <?php echo ($formData['gioi_tinh'] ?? '') === 'Khác' ? 'selected' : ''; ?>>
                                                Khác</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="dia_chi" class="form-label">
                                            <i class="icofont-location-pin me-1"></i>Địa chỉ
                                        </label>
                                        <input type="text" class="form-control" id="dia_chi" name="dia_chi"
                                            value="<?php echo htmlspecialchars($formData['dia_chi'] ?? ''); ?>">
                                    </div>
                                </div>

                                <!-- Hidden role field for patient -->
                                <input type="hidden" name="role" value="patient">

                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-secondary mb-2 w-100" id="step3PrevBtn">
                                        <i class="icofont-arrow-left me-2"></i>Quay lại
                                    </button>
                                    <button type="button" class="btn submit-btn" id="submitBtn">
                                        <i class="icofont-user-alt-3 me-2"></i>Hoàn tất đăng ký
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-0">
                                Đã có tài khoản?
                                <a href="./login" class="login-link">
                                    <i class="icofont-login me-1"></i>Đăng nhập ngay
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</main>

<!-- Custom CSS for Register Page -->
<link rel="stylesheet" href="./assets/css/signup.css">

<!-- Custom JS for Register Page -->
<script src="./assets/js/register.js"></script>

<?php include './Views/layouts/footer.php'; ?>