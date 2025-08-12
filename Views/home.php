<?php

require_once 'Models/Doctor.php';
require_once 'Controllers/AuthController.php';

$auth = new AuthController();

$doctorModel = new Doctor();
$doctors = $doctorModel->getAll();

// Limit to 6 doctors for the home page
$displayDoctors = array_slice($doctors, 0, 6);

// Count doctors by specialty
$specialtyCounts = [];
foreach ($doctors as $doctor) {
    $specialty = $doctor['chuyen_khoa'] ?? 'Khác';
    if (!isset($specialtyCounts[$specialty])) {
        $specialtyCounts[$specialty] = 0;
    }
    $specialtyCounts[$specialty]++;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản lý Bệnh viện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/home.css">

</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/hospital_management/home">
                <i class="fas fa-hospital"></i> ThinhViet Hospital
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">
                        <a class="nav-link" href="#appointment">Đặt lịch</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Tính năng</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#chuyenkhoa">Chuyên khoa</a>
                    </li>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/hospital_management/login">Đăng nhập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/hospital_management/register">Đăng ký</a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item"
                                    href="/hospital_management/<?php echo $_SESSION['user_role']; ?>_dashboard">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a></li>
                            <li><a class="dropdown-item"
                                    href="/hospital_management/<?php echo $_SESSION['user_role']; ?>_profile">
                                    <i class="fas fa-user me-2"></i>Hồ sơ
                                </a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="/hospital_management/logout">
                                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                </a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="hero-title">ThinhViet Hospital</h1>
                    <p class="hero-subtitle">
                        Giải pháp quản lý toàn diện cho bệnh viện, phòng khám với giao diện thân thiện và tính năng đầy
                        đủ
                    </p>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="hero-buttons">
                        <a href="/hospital_management/login" class="btn btn-primary btn-hero">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập
                        </a>
                        <a href="/hospital_management/register" class="btn btn-outline-light btn-hero">
                            <i class="fas fa-user-plus"></i> Đăng ký
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="hero-buttons">
                        <a href="/hospital_management/<?php echo $_SESSION['user_role']; ?>_dashboard"
                            class="btn btn-primary btn-hero">
                            <i class="fas fa-tachometer-alt"></i> Vào Dashboard
                        </a>
                        <a href="/hospital_management/logout" class="btn btn-outline-light btn-hero">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <!-- thống kê đội ngũ y tế các bác sĩ -->
    <div class="row mt-5">
        <div class="col-12 text-center">
            <div class="stats-card bg-white rounded-3 p-4 shadow-lg">
                <h3 class="text-primary mb-3">Thống kê đội ngũ y tế</h3>
                <div class="row">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="stat-item">
                            <div class="stat-number text-primary"><?php echo count($doctors); ?>+</div>
                            <div class="stat-label">Bác sĩ chuyên khoa</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="stat-item">
                            <div class="stat-number text-success"><?php echo count($specialtyCounts); ?>+</div>
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
    <!-- Appointment Section -->
    <section class="appointment-section py-5" id="appointment"
        style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px);">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-4 mb-4 text-white">Đặt lịch khám bệnh</h2>
                    <p class="lead text-white-50">
                        Chọn hình thức khám bệnh phù hợp với nhu cầu của bạn
                    </p>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="appointment-card text-center p-4 bg-white rounded-3 shadow-lg h-100">
                        <div class="appointment-icon mb-3">
                            <i class="fas fa-home fa-3x text-primary"></i>
                        </div>
                        <h4 class="mb-3">Khám tại nhà</h4>
                        <p class="text-muted mb-4">
                            Bác sĩ sẽ đến tận nhà để khám bệnh, phù hợp cho người già, trẻ em và người khó di chuyển
                        </p>
                        <ul class="list-unstyled text-start mb-4">
                            <li><i class="fas fa-check text-success me-2"></i>Tiết kiệm thời gian</li>
                            <li><i class="fas fa-check text-success me-2"></i>An toàn, tiện lợi</li>
                            <li><i class="fas fa-check text-success me-2"></i>Chăm sóc tận tâm</li>
                        </ul>
                        <a href="/hospital_management/home_visit_booking" class="btn btn-warning btn-lg w-100">
                            <i class="fas fa-calendar-plus me-2"></i>Đặt lịch khám tại nhà
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="appointment-card text-center p-4 bg-white rounded-3 shadow-lg h-100">
                        <div class="appointment-icon mb-3">
                            <i class="fas fa-hospital fa-3x text-success"></i>
                        </div>
                        <h4 class="mb-3">Khám tại bệnh viện</h4>
                        <p class="text-muted mb-4">
                            Khám bệnh trực tiếp tại bệnh viện với trang thiết bị hiện đại và đội ngũ bác sĩ chuyên môn
                            cao
                        </p>
                        <ul class="list-unstyled text-start mb-4">
                            <li><i class="fas fa-check text-success me-2"></i>Trang thiết bị hiện đại</li>
                            <li><i class="fas fa-check text-success me-2"></i>Bác sĩ chuyên môn cao</li>
                            <li><i class="fas fa-check text-success me-2"></i>Xét nghiệm toàn diện</li>
                        </ul>
                        <a href="/hospital_management/hospital_appointment" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-calendar-check me-2"></i>Đặt lịch khám tại viện
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="appointment-card text-center p-4 bg-white rounded-3 shadow-lg h-100">
                        <div class="appointment-icon mb-3">
                            <i class="fas fa-comments fa-3x text-info"></i>
                        </div>
                        <h4 class="mb-3">Tư vấn trực tuyến</h4>
                        <p class="text-muted mb-4">
                            Tư vấn sức khỏe trực tuyến với bác sĩ chuyên khoa, giải đáp thắc mắc và đưa ra lời khuyên
                        </p>
                        <ul class="list-unstyled text-start mb-4">
                            <li><i class="fas fa-check text-success me-2"></i>Tiện lợi, nhanh chóng</li>
                            <li><i class="fas fa-check text-success me-2"></i>Tiết kiệm chi phí</li>
                            <li><i class="fas fa-check text-success me-2"></i>Bảo mật thông tin</li>
                        </ul>
                        <a href="/hospital_management/register" class="btn btn-info btn-lg w-100">
                            <i class="fas fa-video me-2"></i>Đặt lịch tư vấn
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Doctors List Section -->
    <section class="doctors-list-section py-5" style="background: white;">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-4 mb-4">Danh sách bác sĩ</h2>
                    <p class="lead text-muted">
                        Đội ngũ bác sĩ chuyên môn cao với nhiều năm kinh nghiệm
                    </p>
                </div>
            </div>

            <div class="row">
                <?php if (!empty($doctors)): ?>
                <?php $doctorsLimited = array_slice($doctors, 0, 6);
                    foreach ($doctorsLimited as $doc): ?>
                <?php
                        $spec = $doc['chuyen_khoa'] ?? '';
                        $colors = $specialtyColors[$spec] ?? ['text' => 'text-primary', 'bg' => '667eea'];
                        $avatarBg = $colors['bg'];
                        $textClass = $colors['text'];
                        $imgSrc = "https://via.placeholder.com/120x120/{$avatarBg}/ffffff?text=BS";
                        ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="doctor-card text-center p-4 bg-light rounded-3 shadow-lg h-100">
                        <div class="doctor-avatar mb-3">
                            <img src="<?php echo $imgSrc; ?>" alt="Bác sĩ" class="rounded-circle">
                        </div>
                        <h5 class="mb-2"><?php echo htmlspecialchars($doc['ten']); ?></h5>
                        <p class="<?php echo $textClass; ?> mb-2">Chuyên khoa
                            <?php echo htmlspecialchars($spec ?: 'Đa khoa'); ?></p>
                        <p class="text-muted small mb-3"><?php echo (int)($doc['so_nam_kinh_nghiem'] ?? 0); ?> năm kinh
                            nghiệm</p>
                        <div class="doctor-info">
                            <span class="badge bg-success me-2">Có lịch</span>
                            <span
                                class="badge bg-info"><?php echo htmlspecialchars($doc['so_dien_thoai'] ?? 'Liên hệ'); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="col-12">
                    <p class="text-center text-muted">Chưa có dữ liệu bác sĩ.</p>
                </div>
                <?php endif; ?>
            </div>

            <div class="row mt-4">
                <div class="col-12 text-center">
                    <a class="btn btn-info btn-lg" href="/hospital_management/doctor_team">
                        <i class="fas fa-users me-2"></i>Xem tất cả bác sĩ
                    </a>
                </div>
            </div>
        </div>
    </section>


    <!-- Doctors & Specialties Section -->
    <section class="doctors-section py-5" id="chuyenkhoa"
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-4 mb-4 text-white">Các Chuyên khoa</h2>
                    <p class="lead text-white-50">
                        Đội ngũ bác sĩ chuyên môn cao với nhiều năm kinh nghiệm trong các chuyên khoa
                    </p>
                </div>
            </div>

            <div class="row">
                <?php
                // Define specialty icons and colors
                $specialtyConfig = [
                    'Nội tổng quát' => ['icon' => 'fas fa-stethoscope', 'color' => 'primary', 'desc' => 'Điều trị các bệnh lý nội khoa tổng quát'],
                    'Ung bướu' => ['icon' => 'fas fa-microscope', 'color' => 'danger', 'desc' => 'Chẩn đoán và điều trị ung thư'],
                    'Sản phụ khoa' => ['icon' => 'fas fa-baby', 'color' => 'pink', 'desc' => 'Chăm sóc sức khỏe phụ nữ và thai sản'],
                    'Chẩn đoán hình ảnh' => ['icon' => 'fas fa-x-ray', 'color' => 'info', 'desc' => 'X-quang, CT, MRI, siêu âm'],
                    'Xét nghiệm' => ['icon' => 'fas fa-flask', 'color' => 'warning', 'desc' => 'Xét nghiệm máu, sinh hóa, vi sinh'],
                    'Ngoại khoa' => ['icon' => 'fas fa-user-md', 'color' => 'success', 'desc' => 'Phẫu thuật tổng quát'],
                    'Tiêu hóa' => ['icon' => 'fas fa-stomach', 'color' => 'orange', 'desc' => 'Bệnh lý đường tiêu hóa'],
                    'Nội tiết' => ['icon' => 'fas fa-pills', 'color' => 'purple', 'desc' => 'Bệnh lý nội tiết và chuyển hóa'],
                    'Tim mạch' => ['icon' => 'fas fa-heartbeat', 'color' => 'danger', 'desc' => 'Bệnh lý tim mạch'],
                    'Nam khoa' => ['icon' => 'fas fa-mars', 'color' => 'blue', 'desc' => 'Sức khỏe nam giới'],
                    'Cơ xương khớp' => ['icon' => 'fas fa-bone', 'color' => 'secondary', 'desc' => 'Bệnh lý cơ xương khớp'],
                    'Truyền nhiễm' => ['icon' => 'fas fa-virus', 'color' => 'warning', 'desc' => 'Bệnh truyền nhiễm'],
                    'Thần kinh' => ['icon' => 'fas fa-brain', 'color' => 'indigo', 'desc' => 'Bệnh lý thần kinh'],
                    'Nhi khoa' => ['icon' => 'fas fa-child', 'color' => 'info', 'desc' => 'Chăm sóc sức khỏe trẻ em'],
                    'Mắt' => ['icon' => 'fas fa-eye', 'color' => 'primary', 'desc' => 'Bệnh lý mắt và thị giác'],
                    'Tai mũi họng' => ['icon' => 'fas fa-head-side-cough', 'color' => 'success', 'desc' => 'Bệnh lý tai mũi họng'],
                    'Da liễu' => ['icon' => 'fas fa-allergies', 'color' => 'warning', 'desc' => 'Bệnh lý da và thẩm mỹ'],
                    'Răng hàm mặt' => ['icon' => 'fas fa-tooth', 'color' => 'light', 'desc' => 'Nha khoa và phẫu thuật hàm mặt']
                ];

                foreach ($specialtyCounts as $specialty => $count):
                    if (isset($specialtyConfig[$specialty])):
                        $config = $specialtyConfig[$specialty];
                ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="specialty-card text-center p-4 bg-white rounded-3 shadow-lg h-100">
                        <div class="specialty-icon mb-3">
                            <i class="<?php echo $config['icon']; ?> fa-3x text-<?php echo $config['color']; ?>"></i>
                        </div>
                        <h5 class="mb-2"><?php echo htmlspecialchars($specialty); ?></h5>
                        <p class="text-muted small mb-3"><?php echo $config['desc']; ?></p>
                        <div class="doctor-count">
                            <span class="badge bg-<?php echo $config['color']; ?>"><?php echo $count; ?> Bác sĩ</span>
                        </div>
                    </div>
                </div>
                <?php
                    endif;
                endforeach;
                ?>
            </div>


        </div>
    </section>



    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4">
        <div class="container">
            <p class="mb-0">&copy; 2025 Cao Dương Quốc Việt - Ung Nguyễn Trường Thịnh</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function showAllDoctors() {
        alert('Chức năng này sẽ hiển thị trang danh sách đầy đủ tất cả bác sĩ với thông tin chi tiết hơn!');
        // Có thể redirect đến trang danh sách bác sĩ chi tiết
        // window.location.href = '/hospital_management/doctors_list';
    }
    </script>
</body>

</html>