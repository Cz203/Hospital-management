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

                    <form method="POST" action="./reception_patient_store" class="row g-3">
                        <input type="hidden" name="csrf_token"
                            value="<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>" />

                        <div class="col-md-6">
                            <label class="form-label">Họ và tên</label>
                            <input type="text" name="ten" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Mật khẩu</label>
                            <input type="password" name="mat_khau" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="so_dien_thoai" class="form-control"
                                placeholder="0912345678 hoặc 84912345678" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" name="ngay_sinh" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Giới tính</label>
                            <select name="gioi_tinh" class="form-select">
                                <option value="">-- Chọn --</option>
                                <option value="Nam">Nam</option>
                                <option value="Nu">Nữ</option>
                                <option value="Khac">Khác</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Nhóm máu</label>
                            <select name="nhom_mau" class="form-select">
                                <option value="">Không xác định</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Địa chỉ</label>
                            <textarea name="dia_chi" class="form-control" rows="2"></textarea>
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
                        <li>Email không được trùng nhau</li>
                        <li>Số điện thoại lưu chuẩn: 84xxxxxxxxx (tự động chuẩn hóa khi lưu)</li>
                        <li>Trường không bắt buộc có thể để trống</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>