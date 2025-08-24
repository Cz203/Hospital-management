<?php
$page_title = 'Hồ sơ cá nhân - Bệnh nhân';
require_once 'Views/layouts/layout_helper.php';

// Lấy thông tin patient hiện tại
$patient = new Patient();
$patient_info = $patient->getById($_SESSION['user_id']);

ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-user-injured text-primary me-2"></i>
                    Hồ sơ cá nhân
                </h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="fas fa-edit me-2"></i>Chỉnh sửa
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Thông tin cơ bản -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Thông tin cơ bản
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Họ và tên:</label>
                                <p class="form-control-plaintext"><?php echo $patient_info['ten']; ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Email:</label>
                                <p class="form-control-plaintext"><?php echo $patient_info['email']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Số điện thoại:</label>
                                <p class="form-control-plaintext">
                                    <?php echo $patient_info['so_dien_thoai'] ?: 'Chưa cập nhật'; ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Ngày sinh:</label>
                                <p class="form-control-plaintext">
                                    <?php echo $patient_info['ngay_sinh'] ? date('d/m/Y', strtotime($patient_info['ngay_sinh'])) : 'Chưa cập nhật'; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Giới tính:</label>
                                <p class="form-control-plaintext">
                                    <?php echo $patient_info['gioi_tinh'] ?: 'Chưa cập nhật'; ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Nhóm máu:</label>
                                <p class="form-control-plaintext">
                                    <?php if ($patient_info['nhom_mau']): ?>
                                    <span class="badge bg-danger"><?php echo $patient_info['nhom_mau']; ?></span>
                                    <?php else: ?>
                                    Chưa cập nhật
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Địa chỉ:</label>
                                <p class="form-control-plaintext">
                                    <?php echo $patient_info['dia_chi'] ?: 'Chưa cập nhật'; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Ngày tạo tài khoản:</label>
                                <p class="form-control-plaintext">
                                    <?php echo date('d/m/Y', strtotime($patient_info['ngay_tao'])); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Cập nhật lần cuối:</label>
                                <p class="form-control-plaintext">
                                    <?php
                                    if (isset($patient_info['ngay_cap_nhat']) && $patient_info['ngay_cap_nhat']) {
                                        echo date('d/m/Y H:i', strtotime($patient_info['ngay_cap_nhat']));
                                    } else {
                                        echo 'Chưa cập nhật';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avatar và thống kê -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="avatar-large mb-3">
                        <?php echo strtoupper(substr($patient_info['ten'], 0, 1)); ?>
                    </div>
                    <h5 class="mb-1"><?php echo $patient_info['ten']; ?></h5>
                    <p class="text-muted mb-2">Bệnh nhân</p>
                    <?php if ($patient_info['nhom_mau']): ?>
                    <p class="text-danger mb-3">Nhóm máu: <?php echo $patient_info['nhom_mau']; ?></p>
                    <?php endif; ?>
                    <div class="d-grid">
                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#changePasswordModal">
                            <i class="fas fa-key me-2"></i>Đổi mật khẩu
                        </button>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Thống kê sức khỏe
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="text-primary mb-1">12</h4>
                                <small class="text-muted">Lịch hẹn</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="text-success mb-1">8</h4>
                                <small class="text-muted">Đã hoàn thành</small>
                            </div>
                        </div>
                    </div>
                    <div class="row text-center mt-3">
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="text-warning mb-1">5</h4>
                                <small class="text-muted">Hồ sơ bệnh án</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="text-info mb-1">3</h4>
                                <small class="text-muted">Đơn thuốc</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal chỉnh sửa profile -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa thông tin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="./patient_update_profile">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Họ và tên</label>
                                <input type="text" class="form-control" name="name"
                                    value="<?php echo $patient_info['ten']; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email"
                                    value="<?php echo $patient_info['email']; ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="tel" class="form-control" name="phone"
                                    value="<?php echo $patient_info['so_dien_thoai']; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ngày sinh</label>
                                <input type="date" class="form-control" name="date_of_birth"
                                    value="<?php echo $patient_info['ngay_sinh']; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Giới tính</label>
                                <select class="form-control" name="gioi_tinh">
                                    <option value="">Chọn giới tính</option>
                                    <option value="Nam"
                                        <?php echo $patient_info['gioi_tinh'] == 'Nam' ? 'selected' : ''; ?>>Nam
                                    </option>
                                    <option value="Nu"
                                        <?php echo $patient_info['gioi_tinh'] == 'Nu' ? 'selected' : ''; ?>>Nữ</option>
                                    <option value="Khac"
                                        <?php echo $patient_info['gioi_tinh'] == 'Khac' ? 'selected' : ''; ?>>Khác
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nhóm máu</label>
                                <select class="form-control" name="nhom_mau">
                                    <option value="">Chọn nhóm máu</option>
                                    <option value="A+"
                                        <?php echo $patient_info['nhom_mau'] == 'A+' ? 'selected' : ''; ?>>A+
                                    </option>
                                    <option value="A-"
                                        <?php echo $patient_info['nhom_mau'] == 'A-' ? 'selected' : ''; ?>>A-
                                    </option>
                                    <option value="B+"
                                        <?php echo $patient_info['nhom_mau'] == 'B+' ? 'selected' : ''; ?>>B+
                                    </option>
                                    <option value="B-"
                                        <?php echo $patient_info['nhom_mau'] == 'B-' ? 'selected' : ''; ?>>B-
                                    </option>
                                    <option value="AB+"
                                        <?php echo $patient_info['nhom_mau'] == 'AB+' ? 'selected' : ''; ?>>AB+
                                    </option>
                                    <option value="AB-"
                                        <?php echo $patient_info['nhom_mau'] == 'AB-' ? 'selected' : ''; ?>>AB-
                                    </option>
                                    <option value="O+"
                                        <?php echo $patient_info['nhom_mau'] == 'O+' ? 'selected' : ''; ?>>O+
                                    </option>
                                    <option value="O-"
                                        <?php echo $patient_info['nhom_mau'] == 'O-' ? 'selected' : ''; ?>>O-
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <textarea class="form-control" name="address"
                            rows="3"><?php echo $patient_info['dia_chi']; ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal đổi mật khẩu -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Đổi mật khẩu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="changePasswordForm" onsubmit="return handlePasswordChange(event)">
                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label mt-3">Mật khẩu hiện tại</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="currentPassword" name="currentPassword"
                                required>
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="togglePasswordVisibility('currentPassword', 'currentPasswordIcon')">
                                <i class="fas fa-eye" id="currentPasswordIcon"></i>
                            </button>
                        </div>
                        <div id="currentPasswordError" class="text-danger mt-1" style="display: none;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu mới</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="newPassword" name="newPassword" required
                                oninput="showPasswordStrength(this.value)">
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="togglePasswordVisibility('newPassword', 'newPasswordIcon')">
                                <i class="fas fa-eye" id="newPasswordIcon"></i>
                            </button>
                        </div>
                        <div id="newPasswordError" class="text-danger mt-1" style="display: none;"></div>
                        <div id="passwordStrength" class="mt-1 small"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Xác nhận mật khẩu mới</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                                required>
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="togglePasswordVisibility('confirmPassword', 'confirmPasswordIcon')">
                                <i class="fas fa-eye" id="confirmPasswordIcon"></i>
                            </button>
                        </div>
                        <div id="confirmPasswordError" class="text-danger mt-1" style="display: none;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="changePasswordBtn">
                        <i class="fas fa-key"></i> Đổi mật khẩu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.avatar-large {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2.5rem;
    font-weight: 600;
    margin: 0 auto;
}

.stat-item {
    padding: 15px 0;
}

.stat-item h4 {
    font-weight: 700;
}
</style>

<?php
$content = ob_get_clean();
renderLayout($content, $page_title);
?>

<script src="./assets/js/validate.js"></script>