<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Hệ thống Quản lý Bệnh viện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/signup.css">

</head>

<body>
    <div class="register-card">
        <div class="register-header">
            <i class="fas fa-hospital fa-3x mb-3"></i>
            <h3>Đăng ký tài khoản</h3>
            <p class="mb-0">Hệ thống Quản lý Bệnh viện</p>
        </div>

        <div class="register-body">
            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" action="/hospital_management/register" id="registerForm">


                <!-- Thông tin chung -->
                <div class="section-title"><i class="fas fa-id-badge"></i> Thông tin chung</div>
                <hr class="section-divider" />
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-user"></i> Họ và tên *
                            </label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email *
                            </label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Mật khẩu *
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-lock"></i> Xác nhận mật khẩu *
                            </label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone"></i> Số điện thoại
                            </label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                    </div>
                </div>

                <!-- Thông tin riêng cho Doctor -->
                <div class="form-section" id="doctorFields">
                    <div class="section-title"><i class="fas fa-user-md"></i> Thông tin bác sĩ</div>
                    <hr class="section-divider" />
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="specialization" class="form-label">
                                    <i class="fas fa-stethoscope"></i> Chuyên khoa
                                </label>
                                <select class="form-control" id="specialization" name="specialization">
                                    <option value="">Chọn chuyên khoa</option>
                                    <option value="Tim mạch">Tim mạch</option>
                                    <option value="Thần kinh">Thần kinh</option>
                                    <option value="Nhi khoa">Nhi khoa</option>
                                    <option value="Da liễu">Da liễu</option>
                                    <option value="Mắt">Mắt</option>
                                    <option value="Tai mũi họng">Tai mũi họng</option>
                                    <option value="Răng hàm mặt">Răng hàm mặt</option>
                                    <option value="Chấn thương chỉnh hình">Chấn thương chỉnh hình</option>
                                    <option value="Sản phụ khoa">Sản phụ khoa</option>
                                    <option value="Ung bướu">Ung bướu</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="license_number" class="form-label">
                                    <i class="fas fa-id-card"></i> Số chứng chỉ hành nghề
                                </label>
                                <input type="text" class="form-control" id="license_number" name="license_number">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="experience_years" class="form-label">
                                    <i class="fas fa-clock"></i> Số năm kinh nghiệm
                                </label>
                                <input type="number" class="form-control" id="experience_years" name="experience_years"
                                    min="0" max="50">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thông tin riêng cho Patient -->
                <div class="form-section active" id="patientFields">
                    <div class="section-title"><i class="fas fa-user"></i> Thông tin bệnh nhân</div>
                    <hr class="section-divider" />
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_of_birth" class="form-label">
                                    <i class="fas fa-calendar"></i> Ngày sinh
                                </label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="gender" class="form-label">
                                    <i class="fas fa-venus-mars"></i> Giới tính
                                </label>
                                <select class="form-control" id="gender" name="gender">
                                    <option value="">Chọn giới tính</option>
                                    <option value="Nam">Nam</option>
                                    <option value="Nữ">Nữ</option>
                                    <option value="Khác">Khác</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="blood_group" class="form-label">
                                    <i class="fas fa-tint"></i> Nhóm máu
                                </label>
                                <select class="form-control" id="blood_group" name="blood_group">
                                    <option value="">Chọn nhóm máu</option>
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
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">
                            <i class="fas fa-map-marker-alt"></i> Địa chỉ
                        </label>
                        <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="agree" required>
                    <label class="form-check-label" for="agree">
                        Tôi đồng ý với <a href="#" class="text-decoration-none">điều khoản sử dụng</a>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-register w-100">
                    <i class="fas fa-user-plus"></i> Đăng ký
                </button>
            </form>

            <div class="text-center mt-3">
                <p class="mb-0">Đã có tài khoản?
                    <a href="/hospital_management/login" class="text-decoration-none">Đăng nhập ngay</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./assets/js/validate.js"></script>
</body>

</html>