    <!-- Footer -->
    <footer class="footer-section">
        <div class="footer-top">
            <div class="container">
                <div class="row">
                    <!-- Company Info -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="footer-widget">
                            <div class="footer-logo mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="footer-brand-icon me-2">
                                        <i class="fas fa-heartbeat"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 text-dark">ThinhViet Hospital</h5>
                                        <small class="text-dark">Chăm sóc sức khỏe toàn diện</small>
                                    </div>
                                </div>
                            </div>
                            <p class="text-dark mb-3">
                                Hệ thống quản lý bệnh viện hiện đại với đội ngũ bác sĩ chuyên môn cao,
                                trang thiết bị tiên tiến và dịch vụ chăm sóc tận tâm.
                            </p>
                            <div class="social-links">
                                <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="col-lg-2 col-md-6 mb-4">
                        <div class="footer-widget">
                            <h6 class="text-dark mb-3">Liên kết nhanh</h6>
                            <ul class="footer-links">
                                <li><a href="#home">Trang chủ</a></li>
                                <li><a href="#appointment">Đặt lịch</a></li>
                                <li><a href="#chuyenkhoa">Chuyên khoa</a></li>
                                <li><a href="#doctors">Bác sĩ</a></li>
                                <li><a href="#features">Tính năng</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Services -->
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="footer-widget">
                            <h6 class="text-dark mb-3">Dịch vụ</h6>
                            <ul class="footer-links">
                                <li><a href="./hospital_appointment">Khám tại viện</a></li>
                                <li><a href="./home_visit_booking">Khám tại nhà</a></li>
                                <li><a href="./consultation_booking">Tư vấn trực tuyến</a></li>
                                <li><a href="#">Xét nghiệm</a></li>
                                <li><a href="#">Chẩn đoán hình ảnh</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="footer-widget">
                            <h6 class="text-dark mb-3">Liên hệ</h6>
                            <div class="contact-info">
                                <div class="contact-item mb-2">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    <span class="text-dark">123 Đường ABC, Quận 1, TP.HCM</span>
                                </div>
                                <div class="contact-item mb-2">
                                    <i class="fas fa-phone me-2"></i>
                                    <span class="text-dark">(84) 28-1234-5678</span>
                                </div>
                                <div class="contact-item mb-2">
                                    <i class="fas fa-envelope me-2"></i>
                                    <span class="text-dark">info@thinhviet.com</span>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-clock me-2"></i>
                                    <span class="text-dark">24/7 Khẩn cấp</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="mb-0 text-dark">
                            &copy; 2025 <strong>ThinhViet Hospital</strong>.
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-0 text-dark">
                            Phát triển bởi <strong>Cao Dương Quốc Việt</strong> & <strong>Ung Nguyễn Trường
                                Thịnh</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="./assets/js/main.js"></script>

    <!-- Additional JS for specific pages -->
    <?php if (isset($additional_js)): ?>
    <?php foreach ($additional_js as $js): ?>
    <script src="<?php echo $js; ?>"></script>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Page specific scripts -->
    <?php if (isset($page_scripts)): ?>
    <script>
<?php echo $page_scripts; ?>
    </script>
    <?php endif; ?>
    </body>

    </html>