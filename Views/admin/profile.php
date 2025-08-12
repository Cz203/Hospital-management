<?php
$page_title = 'Hồ sơ cá nhân - Admin';
require_once 'Views/layouts/layout_helper.php';

// Lấy thông tin admin hiện tại
$admin = new Admin();
$admin_info = $admin->getById($_SESSION['user_id']);

ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-user-shield text-primary me-2"></i>
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
                                <p class="form-control-plaintext"><?php echo $admin_info['ten']; ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Email:</label>
                                <p class="form-control-plaintext"><?php echo $admin_info['email']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Số điện thoại:</label>
                                <p class="form-control-plaintext">
                                    <?php echo $admin_info['so_dien_thoai'] ?: 'Chưa cập nhật'; ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Vai trò:</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-danger">Quản trị viên</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Ngày tạo tài khoản:</label>
                                <p class="form-control-plaintext">
                                    <?php echo date('d/m/Y', strtotime($admin_info['ngay_tao'])); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Cập nhật lần cuối:</label>
                                <p class="form-control-plaintext">
                                    <?php echo date('d/m/Y H:i', strtotime($admin_info['ngay_cap_nhat'])); ?></p>
                            </div>
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
                    <?php echo strtoupper(substr($admin_info['ten'], 0, 1)); ?>
                </div>
                <h5 class="mb-1"><?php echo $admin_info['ten']; ?></h5>
                <p class="text-muted mb-3">Quản trị viên hệ thống</p>
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
                    Thống kê hoạt động
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="stat-item">
                            <h4 class="text-primary mb-1">150</h4>
                            <small class="text-muted">Người dùng</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-item">
                            <h4 class="text-success mb-1">45</h4>
                            <small class="text-muted">Báo cáo</small>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa thông tin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/hospital_management/admin_update_profile">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Họ và tên</label>
                        <input type="text" class="form-control" name="name" value="<?php echo $admin_info['ten']; ?>"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email"
                            value="<?php echo $admin_info['email']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="tel" class="form-control" name="phone"
                            value="<?php echo $admin_info['so_dien_thoai']; ?>">
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
            <form method="POST" action="/hospital_management/admin_change_password">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu hiện tại</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu mới</label>
                        <input type="password" class="form-control" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Xác nhận mật khẩu mới</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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