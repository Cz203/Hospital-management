<div class="container-fluid">
    <div class="row">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Thêm bệnh nhân</h5>
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['success'];
                            unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['error'];
                            unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="./reception_patient_store" class="row g-3" id="patientCreateForm">
                        <input type="hidden" name="csrf_token"
                            value="<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>" />

                        <!-- CCCD ở dòng đầu tiên -->
                        <div class="col-12">
                            <label class="form-label">
                                <i class="fas fa-id-card me-1"></i>Căn cước công dân (CCCD)
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="cccd" id="cccd" class="form-control" maxlength="12"
                                pattern="\d{12}" placeholder="Nhập 12 số CCCD để tự động điền thông tin" required>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i> CCCD gồm 12 số - Nhập CCCD để tự động điền thông tin
                                bên dưới
                            </div>
                            <div id="cccd-verification-result" class="mt-2"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-user me-1"></i>Họ và tên
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="ten" id="ten" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-envelope me-1"></i>Email
                                <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-lock me-1"></i>Mật khẩu
                                <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="mat_khau" id="mat_khau" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-phone me-1"></i>Số điện thoại
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="so_dien_thoai" id="so_dien_thoai" class="form-control"
                                placeholder="0912345678 hoặc 84912345678" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-calendar me-1"></i>Ngày sinh
                            </label>
                            <input type="date" name="ngay_sinh" id="ngay_sinh" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-venus-mars me-1"></i>Giới tính
                            </label>
                            <select name="gioi_tinh" id="gioi_tinh" class="form-select">
                                <option value="">-- Chọn --</option>
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Địa chỉ
                            </label>
                            <textarea name="dia_chi" id="dia_chi" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Lưu bệnh nhân
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Gợi ý/Quy ước</h6>
                    <ul class="small mb-0">
                        <li>Nhập CCCD trước để tự động điền thông tin</li>
                        <li>Email không được trùng nhau</li>
                        <li>Số điện thoại lưu chuẩn: 84xxxxxxxxx (tự động chuẩn hóa khi lưu)</li>
                        <li>Trường có dấu <span class="text-danger">*</span> là bắt buộc</li>
                    </ul>

                    <h6 class="card-title mt-3">CCCD mẫu để test</h6>
                    <div class="small">
                        <div class="mb-2">
                            <strong>001234567890</strong><br>
                            <small class="text-muted">Cao Dương Quốc Việt - 2003-03-22</small>
                        </div>
                        <div class="mb-2">
                            <strong>002345678901</strong><br>
                            <small class="text-muted">Trần Thị B - 1995-05-20</small>
                        </div>
                        <div class="mb-2">
                            <strong>003456789012</strong><br>
                            <small class="text-muted">Lê Văn C - 1988-12-10</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/reception_patient_create.js"></script>