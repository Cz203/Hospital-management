<?php
// Set page title
$page_title = 'Liên hệ';
include 'Views/layouts/header.php';
?>

<section class="contact-hero py-5"
    style="background: linear-gradient(135deg, #ffffff 0%, #eaf3ff 100%); margin-top: 80px;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-4 mb-lg-0">
                <h1 class="display-5 fw-bold mb-3" style="color:#0d6efd;">Liên hệ ThinhViet</h1>
                <p class="lead text-muted mb-0">Chúng tôi luôn sẵn sàng hỗ trợ bạn. Hãy để lại thông tin, đội ngũ sẽ
                    phản hồi trong thời gian sớm nhất.</p>
            </div>
        </div>
    </div>
</section>

<section class="contact-content py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Gửi tin nhắn</h5>
                        <form id="contact-form" method="post" action="#">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Họ và tên</label>
                                    <input type="text" class="form-control" name="name" placeholder="Nguyễn Văn A"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="tel" class="form-control" name="phone" placeholder="09xx xxx xxx"
                                        required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Email (tuỳ chọn)</label>
                                    <input type="email" class="form-control" name="email" placeholder="you@example.com">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Chủ đề</label>
                                    <input type="text" class="form-control" name="subject"
                                        placeholder="Nội dung liên hệ" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Nội dung</label>
                                    <textarea class="form-control" name="message" rows="5"
                                        placeholder="Mô tả chi tiết nội dung cần hỗ trợ..." required></textarea>
                                </div>
                                <div class="col-12 d-grid d-md-flex gap-2 mt-2">
                                    <button type="submit" class="btn btn-primary px-4"><i
                                            class="fas fa-paper-plane me-2"></i>Gửi</button>
                                    <button type="reset" class="btn btn-outline-secondary px-4">Làm mới</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Thông tin liên hệ</h5>
                        <div class="d-flex align-items-start mb-3">
                            <div class="me-3 text-primary"><i class="fas fa-phone fa-lg"></i></div>
                            <div>
                                <div class="fw-semibold">Hotline</div>
                                <div class="text-muted">1900 1234 (7:30 - 17:30)</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3">
                            <div class="me-3 text-primary"><i class="fas fa-envelope fa-lg"></i></div>
                            <div>
                                <div class="fw-semibold">Email</div>
                                <div class="text-muted">support@thinhvietclinic.vn</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3">
                            <div class="me-3 text-primary"><i class="fas fa-location-dot fa-lg"></i></div>
                            <div>
                                <div class="fw-semibold">Địa chỉ</div>
                                <div class="text-muted">123 Nguyễn Trãi, Quận 1, TP.HCM</div>
                            </div>
                        </div>
                        <div class="ratio ratio-4x3 rounded overflow-hidden border"
                            style="border-color: rgba(13,110,253,.15)!important;">
                            <iframe src="https://www.google.com/maps?q=Ho+Chi+Minh+City&output=embed" style="border:0;"
                                allowfullscreen="" loading="lazy"></iframe>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Kết nối với chúng tôi</h6>
                        <div class="d-flex gap-2">
                            <a class="btn btn-outline-primary" href="#"><i class="fab fa-facebook"></i></a>
                            <a class="btn btn-outline-primary" href="#"><i class="fab fa-instagram"></i></a>
                            <a class="btn btn-outline-primary" href="#"><i class="fab fa-youtube"></i></a>
                            <a class="btn btn-outline-primary" href="#"><i class="fab fa-tiktok"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'Views/layouts/footer.php'; ?>