<!-- Footer Start -->
<footer class="footer section gray-bg">
    <div class="container">
        <div class="row">
            <!-- Company Info -->
            <div class="col-lg-4 mr-auto col-sm-6">
                <div class="widget mb-5 mb-lg-0">
                    <div class="logo mb-4">
                        <img src="./assets/img/novena-logo.png" alt="ThinhViet Hospital" class="img-fluid">
                    </div>
                    <p>Hệ thống quản lý bệnh viện hiện đại với đội ngũ bác sĩ chuyên môn cao,
                        trang thiết bị tiên tiến và dịch vụ chăm sóc tận tâm.</p>

                    <ul class="list-inline footer-socials mt-4">
                        <li class="list-inline-item">
                            <a href="https://www.facebook.com/"><i class="icofont-facebook"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href="https://twitter.com/"><i class="icofont-twitter"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href="https://www.linkedin.com/"><i class="icofont-linkedin"></i></a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 col-sm-6">
                <div class="widget mb-5 mb-lg-0">
                    <h4 class="text-capitalize mb-3">Liên kết</h4>
                    <div class="divider mb-4"></div>

                    <ul class="list-unstyled footer-menu lh-35">
                        <li><a href="./home">Trang chủ</a></li>
                        <li><a href="./doctor_team">Đặt lịch khám</a></li>
                        <li><a href="./home#chuyenkhoa">Chuyên khoa</a></li>
                        <li><a href="./home#doctors">Bác sĩ</a></li>
                        <li><a href="./contact">Liên hệ</a></li>
                    </ul>
                </div>
            </div>

            <!-- Services -->
            <div class="col-lg-2 col-md-6 col-sm-6">
                <div class="widget mb-5 mb-lg-0">
                    <h4 class="text-capitalize mb-3">Dịch vụ</h4>
                    <div class="divider mb-4"></div>

                    <ul class="list-unstyled footer-menu lh-35">
                        <li><a href="./hospital_appointment">Khám tại viện</a></li>
                        <li><a href="./home_visit_booking">Khám tại nhà</a></li>
                        <li><a href="./consultation_booking">Tư vấn trực tuyến</a></li>
                        <li><a href="./home#chuyenkhoa">Xét nghiệm</a></li>
                        <li><a href="./home#chuyenkhoa">Chẩn đoán hình ảnh</a></li>
                    </ul>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="widget widget-contact mb-5 mb-lg-0">
                    <h4 class="text-capitalize mb-3">Liên hệ</h4>
                    <div class="divider mb-4"></div>

                    <div class="footer-contact-block mb-4">
                        <div class="icon d-flex align-items-center">
                            <i class="icofont-email mr-3"></i>
                            <span class="h6 mb-0">Hỗ trợ 24/7</span>
                        </div>
                        <h4 class="mt-2"><a href="mailto:info@thinhviet.com">info@thinhviet.com</a></h4>
                    </div>

                    <div class="footer-contact-block">
                        <div class="icon d-flex align-items-center">
                            <i class="icofont-support mr-3"></i>
                            <span class="h6 mb-0">Thứ 2 - CN: 24/7</span>
                        </div>
                        <h4 class="mt-2"><a href="tel:+842812345678">(84) 28-1234-5678</a></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-btm py-4 mt-5">
            <div class="row align-items-center justify-content-between">
                <div class="col-lg-6">
                    <div class="copyright">
                        &copy; 2025 <span class="text-color">ThinhViet Hospital</span>. All Rights Reserved.
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-lg-right mt-2 mt-lg-0">
                        <p class="mb-0">Phát triển bởi <strong>Cao Dương Quốc Việt</strong> & <strong>Ung Nguyễn Trường
                                Thịnh</strong></p>
                    </div>
                </div>
            </div>

            <!-- Back to Top -->
            <div class="row">
                <div class="col-lg-4">
                    <a class="backtop js-scroll-trigger" href="#top">
                        <i class="icofont-long-arrow-up"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Essential Scripts -->

<!-- Main jQuery -->
<script src="./assets/plugins/jquery/jquery.js"></script>

<!-- Bootstrap 4.3.2 -->
<script src="./assets/plugins/bootstrap/js/popper.js"></script>
<script src="./assets/plugins/bootstrap/js/bootstrap.min.js"></script>

<!-- Slick Slider -->
<script src="./assets/plugins/slick-carousel/slick/slick.min.js"></script>

<!-- Counterup -->
<script src="./assets/plugins/counterup/jquery.waypoints.min.js"></script>
<script src="./assets/plugins/counterup/jquery.easing.js"></script>
<script src="./assets/plugins/counterup/jquery.counterup.min.js"></script>

<!-- Shuffle -->
<script src="./assets/plugins/shuffle/shuffle.min.js"></script>

<!-- Novena Main Script -->
<script src="./assets/js/novena-script.js"></script>

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