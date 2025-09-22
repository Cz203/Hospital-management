<?php
require_once 'Models/Doctor.php';
require_once 'Models/Specialty.php';
require_once 'Controllers/AuthController.php';

$auth = new AuthController();

$doctorModel = new Doctor();
$doctors = $doctorModel->getAll();

// Limit to 6 doctors for the home page
$displayDoctors = array_slice($doctors, 0, 6);

// Get specialties directly from chuyen_khoa with doctor counts
$specialtyModel = new Specialty();
$specialties = $specialtyModel->allWithDoctorCounts();

// Define specialty colors for consistent styling
$specialtyColors = [
    'Nội tổng quát' => ['text' => 'text-primary', 'bg' => 'primary'],
    'Ung bướu' => ['text' => 'text-danger', 'bg' => 'danger'],
    'Sản phụ khoa' => ['text' => 'text-pink', 'bg' => 'pink'],
    'Chẩn đoán hình ảnh' => ['text' => 'text-info', 'bg' => 'info'],
    'Xét nghiệm' => ['text' => 'text-warning', 'bg' => 'warning'],
    'Ngoại khoa' => ['text' => 'text-success', 'bg' => 'success'],
    'Tiêu hóa' => ['text' => 'text-orange', 'bg' => 'orange'],
    'Nội tiết' => ['text' => 'text-purple', 'bg' => 'purple'],
    'Tim mạch' => ['text' => 'text-danger', 'bg' => 'danger'],
    'Nam khoa' => ['text' => 'text-blue', 'bg' => 'blue'],
    'Cơ xương khớp' => ['text' => 'text-secondary', 'bg' => 'secondary'],
    'Truyền nhiễm' => ['text' => 'text-warning', 'bg' => 'warning'],
    'Thần kinh' => ['text' => 'text-indigo', 'bg' => 'indigo'],
    'Nhi khoa' => ['text' => 'text-info', 'bg' => 'info'],
    'Mắt' => ['text' => 'text-primary', 'bg' => 'primary'],
    'Tai mũi họng' => ['text' => 'text-success', 'bg' => 'success'],
    'Da liễu' => ['text' => 'text-warning', 'bg' => 'warning'],
    'Răng hàm mặt' => ['text' => 'text-light', 'bg' => 'light']
];

// Set page title
$page_title = 'Trang chủ';

// Include header
include 'Views/layouts/header.php';
?>

<!-- Hero Section -->
<section class="hero-section" id="home">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <div class="hero-content">
                    <h1 class="hero-title">ThinhViet Hospital</h1>

                    <!-- Search Section -->
                    <div class="hero-search animate-on-scroll">
                        <div class="search-container">
                            <div class="search-box">
                                <div class="search-input-group">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" class="search-input"
                                        placeholder="Tìm kiếm bác sĩ, chuyên khoa...">
                                    <button type="button" class="search-btn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="search-suggestions">
                                <div class="suggestion-tags">
                                    <span class="suggestion-tag">Nội khoa</span>
                                    <span class="suggestion-tag">Ngoại khoa</span>
                                    <span class="suggestion-tag">Tim mạch</span>
                                    <span class="suggestion-tag">Nhi khoa</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>



<!-- Appointment Section -->
<section class="appointment-section" id="appointment">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-4 mb-4 text-white animate-on-scroll">Đặt lịch khám bệnh</h2>
                <p class="lead text-white-50 animate-on-scroll">
                    Chọn hình thức khám bệnh phù hợp với nhu cầu của bạn. Chúng tôi cung cấp đa dạng dịch vụ
                    để đảm bảo sự thuận tiện và hiệu quả trong việc chăm sóc sức khỏe.
                </p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="appointment-card animate-on-scroll">
                    <div class="appointment-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h4>Khám tại nhà</h4>
                    <p>
                        Bác sĩ sẽ đến tận nhà để khám bệnh, phù hợp cho người già, trẻ em và người khó di chuyển.
                        Dịch vụ tận tâm, an toàn và tiết kiệm thời gian.
                    </p>
                    <ul class="list-unstyled text-start mb-4">
                        <li><i class="fas fa-check text-success me-2"></i>Tiết kiệm thời gian</li>
                        <li><i class="fas fa-check text-success me-2"></i>An toàn, tiện lợi</li>
                        <li><i class="fas fa-check text-success me-2"></i>Chăm sóc tận tâm</li>
                        <li><i class="fas fa-check text-success me-2"></i>Phù hợp mọi lứa tuổi</li>
                    </ul>
                    <a href="./doctor_team" class="btn btn-warning">
                        <i class="fas fa-calendar-plus me-2"></i>Đặt lịch khám tại nhà
                    </a>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="appointment-card animate-on-scroll">
                    <div class="appointment-icon">
                        <i class="fas fa-hospital"></i>
                    </div>
                    <h4>Khám tại bệnh viện</h4>
                    <p>
                        Khám bệnh trực tiếp tại bệnh viện với trang thiết bị hiện đại và đội ngũ bác sĩ chuyên môn cao.
                        Xét nghiệm toàn diện và chẩn đoán chính xác.
                    </p>
                    <ul class="list-unstyled text-start mb-4">
                        <li><i class="fas fa-check text-success me-2"></i>Trang thiết bị hiện đại</li>
                        <li><i class="fas fa-check text-success me-2"></i>Bác sĩ chuyên môn cao</li>
                        <li><i class="fas fa-check text-success me-2"></i>Xét nghiệm toàn diện</li>
                        <li><i class="fas fa-check text-success me-2"></i>Chẩn đoán chính xác</li>
                    </ul>
                    <a href="./doctor_team" class="btn btn-success">
                        <i class="fas fa-calendar-check me-2"></i>Đặt lịch khám tại viện
                    </a>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="appointment-card animate-on-scroll">
                    <div class="appointment-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h4>Tư vấn trực tuyến</h4>
                    <p>
                        Tư vấn sức khỏe trực tuyến với bác sĩ chuyên khoa, giải đáp thắc mắc và đưa ra lời khuyên.
                        Tiện lợi, nhanh chóng và bảo mật thông tin.
                    </p>
                    <ul class="list-unstyled text-start mb-4">
                        <li><i class="fas fa-check text-success me-2"></i>Tiện lợi, nhanh chóng</li>
                        <li><i class="fas fa-check text-success me-2"></i>Tiết kiệm chi phí</li>
                        <li><i class="fas fa-check text-success me-2"></i>Bảo mật thông tin</li>
                        <li><i class="fas fa-check text-success me-2"></i>Mọi lúc, mọi nơi</li>
                    </ul>
                    <a href="./consultation_booking" class="btn btn-info">
                        <i class="fas fa-video me-2"></i>Đặt lịch tư vấn
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Doctors List Section -->
<section class="doctors-list-section" id="doctors">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-4 mb-4 animate-on-scroll">Danh sách bác sĩ</h2>
                <p class="lead text-muted animate-on-scroll">
                    Đội ngũ bác sĩ chuyên môn cao với nhiều năm kinh nghiệm trong các lĩnh vực khác nhau.
                    Cam kết mang đến dịch vụ chăm sóc sức khỏe tốt nhất cho bệnh nhân.
                </p>
            </div>
        </div>

        <div class="row">
            <?php if (!empty($doctors)): ?>
            <?php $doctorsLimited = array_slice($doctors, 0, 6);
                foreach ($doctorsLimited as $doc): ?>
            <?php
                    $spec = $doc['chuyen_khoa'] ?? '';
                    $colors = $specialtyColors[$spec] ?? ['text' => 'text-primary', 'bg' => 'primary'];
                    $textClass = $colors['text'];
                    ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="doctor-card animate-on-scroll">
                    <div class="doctor-avatar">
                        <img src="<?php echo $doc['hinh_anh']; ?>"
                            alt="Bác sĩ <?php echo htmlspecialchars($doc['ten']); ?>"
                            onerror="this.src='./assets/img/default-doctor.jpg'">
                    </div>
                    <h5><?php echo htmlspecialchars($doc['ten']); ?></h5>
                    <p class="<?php echo $textClass; ?> mb-2">
                        <i class="fas fa-stethoscope me-1"></i>
                        Chuyên khoa <?php echo htmlspecialchars($spec ?: 'Đa khoa'); ?>
                    </p>
                    <p class="text-muted small mb-3">
                        <i class="fas fa-clock me-1"></i>
                        <?php echo (int)($doc['so_nam_kinh_nghiem'] ?? 0); ?> năm kinh nghiệm
                    </p>

                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="col-12">
                <div class="text-center">
                    <i class="fas fa-user-md fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Chưa có dữ liệu bác sĩ.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="row mt-4">
            <div class="col-12 text-center">
                <a class="btn btn-primary btn-lg animate-on-scroll" href="./doctor_team">
                    <i class="fas fa-users me-2"></i>Xem tất cả bác sĩ
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Specialties Section -->
<section class="doctors-section" id="chuyenkhoa">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-4 mb-4 text-white animate-on-scroll">Các Chuyên khoa</h2>
                <p class="lead text-white-50 animate-on-scroll">
                    Đội ngũ bác sĩ chuyên môn cao với nhiều năm kinh nghiệm trong các chuyên khoa đa dạng.
                    Chúng tôi cam kết mang đến dịch vụ chăm sóc sức khỏe toàn diện và chuyên nghiệp.
                </p>
            </div>
        </div>

        <div class="row">
            <?php $specialtiesLimited = array_slice($specialties ?? [], 0, 8);
            foreach ($specialtiesLimited as $sp):
                $name = $sp['ten'];
                $count = (int)($sp['doctor_count'] ?? 0);
                $iconClass = !empty($sp['icon']) ? $sp['icon'] : 'fas fa-stethoscope';
                $desc = $sp['mo_ta'] ?? '';
                $badgeColor = 'primary';
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <a class="text-decoration-none text-reset"
                    href="./doctors_by_specialty?slug=<?php echo urlencode($sp['slug'] ?? ''); ?>">
                    <div class="specialty-card animate-on-scroll">
                        <div class="specialty-icon">
                            <i class="<?php echo htmlspecialchars($iconClass); ?>"></i>
                        </div>
                        <h5><?php echo htmlspecialchars($name); ?></h5>
                        <p><?php echo htmlspecialchars($desc); ?></p>
                        <div class="doctor-count">
                            <span class="badge bg-<?php echo $badgeColor; ?>">
                                <i class="fas fa-user-md me-1"></i><?php echo $count; ?> Bác sĩ
                            </span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="row mt-3">
            <div class="col-12 text-center">
                <a class="btn btn-primary btn-lg animate-on-scroll" href="./specialties_all">
                    <i class="fas fa-users me-2"></i>Xem tất chuyên khoa
                </a>
            </div>
        </div>
    </div>
</section>


<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="stats-card animate-on-scroll">
                    <h3 class="text-gradient">Thống kê đội ngũ y tế</h3>
                    <div class="row">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-item">
                                <div class="stat-number text-primary"><?php echo count($doctors); ?>+</div>
                                <div class="stat-label">Bác sĩ chuyên khoa</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-item">
                                <div class="stat-number text-success">
                                    <?php echo is_array($specialties) ? count($specialties) : 0; ?>+</div>
                                <div class="stat-label">Chuyên khoa</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-item">
                                <div class="stat-number text-info"><?php
                                                                    $avgExperience = 0;
                                                                    if (count($doctors) > 0) {
                                                                        $totalExp = 0;
                                                                        foreach ($doctors as $doctor) {
                                                                            $totalExp += (int)($doctor['so_nam_kinh_nghiem'] ?? 0);
                                                                        }
                                                                        $avgExperience = round($totalExp / count($doctors));
                                                                    }
                                                                    echo $avgExperience;
                                                                    ?>+</div>
                                <div class="stat-label">Năm kinh nghiệm TB</div>
                            </div>

                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-item">
                                <div class="stat-number text-warning">50,000+</div>
                                <div class="stat-label">Bệnh nhân đã điều trị</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
// Include footer
include 'Views/layouts/footer.php';
?>