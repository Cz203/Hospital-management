<?php
require_once 'Models/Doctor.php';
require_once 'Models/Specialty.php';
require_once 'Models/Patient.php';
require_once 'Models/Appointment.php';
require_once 'Controllers/AuthController.php';

$auth = new AuthController();

$doctorModel = new Doctor();
$doctors = $doctorModel->getAll();

// Limit to 6 doctors for the home page
$displayDoctors = array_slice($doctors, 0, 6);

// Get specialties directly from chuyen_khoa with doctor counts
$specialtyModel = new Specialty();
$specialties = $specialtyModel->allWithDoctorCounts();

// Calculate statistics - Get from database efficiently
// Ensure doctors is an array
$doctors = is_array($doctors) ? $doctors : [];
$totalDoctors = count($doctors);

// Ensure specialties is an array
$specialties = is_array($specialties) ? $specialties : [];
$totalSpecialties = count($specialties);

// Calculate average experience
$avgExperience = 0;
if ($totalDoctors > 0) {
    $totalExp = 0;
    $validExpCount = 0;
    foreach ($doctors as $doctor) {
        $exp = (int)($doctor['so_nam_kinh_nghiem'] ?? 0);
        if ($exp > 0) {
            $totalExp += $exp;
            $validExpCount++;
        }
    }
    $avgExperience = $validExpCount > 0 ? round($totalExp / $validExpCount) : 0;
}

// Get total patients count from database using COUNT query
$totalPatients = 0;
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    if ($db) {
        $stmt = $db->query("SELECT COUNT(*) as total FROM benh_nhan");
        if ($stmt) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalPatients = (int)($result['total'] ?? 0);
        }
    }
} catch (Exception $e) {
    error_log("Error getting patient count: " . $e->getMessage());
    $totalPatients = 0;
}

// Get total appointments count
$totalAppointments = 0;
try {
    $appointmentModel = new Appointment();
    $totalAppointments = $appointmentModel->countAll();
} catch (Exception $e) {
    error_log("Error getting appointment count: " . $e->getMessage());
    $totalAppointments = 0;
}

// Patients treated = total appointments (represents actual treatments)
// Fallback to patients count if no appointments
$patientsTreated = $totalAppointments > 0 ? $totalAppointments : ($totalPatients > 0 ? $totalPatients : 0);

// Debug: Log values (remove in production)
// error_log("Stats: Doctors=$totalDoctors, Specialties=$totalSpecialties, Experience=$avgExperience, Patients=$totalPatients, Appointments=$totalAppointments, Treated=$patientsTreated");

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
$page_title = 'Trang chủ - ThinhViet Hospital';

// Include header
include 'Views/layouts/header.php';
?>
<link href="https://cdn.jsdelivr.net/npm/@n8n/chat/dist/style.css" rel="stylesheet" />
<!-- Custom theme overrides for n8n chat -->
<style>
    /* Brand palette (Thịnh Việt) */
    :root {
        /* Core colors */
        --chat--color--primary: #223A66;
        /* launcher + accents */
        --chat--color--primary-shade-50: #1b2e4e;
        --chat--color--primary--shade-100: #16233d;
        --chat--color--secondary: #223A66;
        /* user bubble + send icon */

        /* Surfaces */
        --chat--body--background: #f7fafc;
        --chat--footer--background: #ffffff;
        --chat--color-light: #f0f4f8;
        --chat--color-light-shade-100: #d9e2ec;

        /* Window + layout */
        --chat--window--width: 420px;
        --chat--window--height: 600px;
        --chat--border-radius: 14px;
        --chat--window--border-radius: 16px;
        --chat--messages-list--padding: 1rem;

        /* Header */
        --chat--header--background: var(--chat--color--primary);
        --chat--header--color: #ffffff;
        --chat--heading--font-size: 1.1rem;
        --chat--subtitle--font-size: .9rem;
        --chat--subtitle--line-height: 1.6;

        /* Message bubbles */
        --chat--message--font-size: .98rem;
        --chat--message--padding: .75rem .9rem;
        --chat--message--border-radius: 14px;
        --chat--message--bot--background: #ffffff;
        --chat--message--bot--color: #1f2937;
        --chat--message--user--background: var(--chat--color--secondary);
        --chat--message--user--color: #ffffff;
        --chat--message--pre--background: #eef2f7;

        /* Input area */
        --chat--textarea--height: 52px;
        --chat--input--background: #ffffff;
        --chat--input--send--button--background: #ffffff;
        --chat--input--send--button--color: var(--chat--color--secondary);

        /* Launcher (toggle) */
        --chat--toggle--size: 64px;
        --chat--toggle--background: var(--chat--color--primary);
        --chat--toggle--hover--background: var(--chat--color--primary-shade-50);
        --chat--toggle--active--background: var(--chat--color--primary--shade-100);
    }

    /* Polishing */
    .n8n-chat .chat-window {
        box-shadow: 0 20px 40px rgba(16, 19, 48, 0.18);
        border: 1px solid rgba(34, 58, 102, 0.18);
        overflow: hidden;
    }

    .n8n-chat .chat-window-toggle {
        box-shadow: 0 10px 20px rgba(16, 19, 48, 0.18);
    }

    .n8n-chat .chat-header h1 {
        font-weight: 700;
        letter-spacing: .2px;
    }

    .n8n-chat .chat-header p {
        opacity: .95;
    }

    .n8n-chat .chat-message.chat-message-from-user {
        border: none;
    }

    .n8n-chat .chat-message.chat-message-from-bot {
        border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .n8n-chat .chat-input textarea::placeholder {
        opacity: .8;
    }

    /* Enhanced header styling */
    .n8n-chat .chat-header {
        background: linear-gradient(135deg, #223A66 0%, #16233d 100%) !important;
        position: relative;
        padding: 16px 16px 14px 72px;
        min-height: 72px;
    }

    .n8n-chat .chat-header::before {
        content: '';
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        width: 44px;
        height: 44px;
        border-radius: 12px;
        box-shadow: 0 6px 14px rgba(0, 0, 0, .2);
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
        /* Virtual assistant robot icon */
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='44' height='44' viewBox='0 0 44 44'><rect width='44' height='44' rx='12' fill='%23ffffff'/><circle cx='22' cy='22' r='18' fill='%23223A66'/><g fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><rect x='14' y='16' width='16' height='12' rx='6'/><circle cx='18' cy='22' r='1.5' fill='%23ffffff' stroke='none'/><circle cx='26' cy='22' r='1.5' fill='%23ffffff' stroke='none'/><path d='M16 27c2 2 10 2 12 0'/><line x1='22' y1='16' x2='22' y2='13'/><circle cx='22' cy='12' r='1' fill='%23ffffff' stroke='none'/></g></svg>");
    }

    .n8n-chat .chat-header h1 {
        color: #ffffff !important;
        margin: 0 0 2px 0;
        font-size: 1.15rem !important;
        font-weight: 800;
        letter-spacing: .2px;
    }

    .n8n-chat .chat-header p {
        color: #e8fffa !important;
        margin: 0;
        font-size: .92rem !important;
        opacity: .95;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .n8n-chat .chat-header p::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #9cc7ff;
        box-shadow: 0 0 0 4px rgba(255, 255, 255, .18);
        display: inline-block;
    }

    .n8n-chat .chat-header::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: -1px;
        height: 8px;
        background: linear-gradient(to bottom, rgba(0, 0, 0, .12), rgba(0, 0, 0, 0));
        pointer-events: none;
    }

    .n8n-chat .chat-close-button {
        color: #eaf2ff;
        opacity: .9;
        border-radius: 8px;
        transition: background .2s ease, color .2s ease;
    }

    .n8n-chat .chat-close-button:hover {
        color: #ffffff;
        background: rgba(255, 255, 255, .12);
    }
</style>
<script type="module">
    import {
        createChat
    } from 'https://cdn.jsdelivr.net/npm/@n8n/chat/dist/chat.bundle.es.js';

    createChat({
        webhookUrl: <?php echo json_encode($_ENV['N8N_WEBHOOK_URL'] ?? getenv('N8N_WEBHOOK_URL') ?? ''); ?>,
        // i18n overrides to customize header and texts
        defaultLanguage: 'vi',
        i18n: {
            vi: {
                title: 'Phòng khám đa khoa Thịnh Việt',
                subtitle: 'Trợ lý ảo Phòng khám đa khoa Thịnh Việt',
                footer: '',
                getStarted: 'Bắt đầu trò chuyện',
                inputPlaceholder: 'Nhập câu hỏi của bạn...',
                closeButtonTooltip: 'Đóng chat'
            }
        },
        initialMessages: [
            'Chào mừng bạn đã đến với website phòng khám đa khoa Thịnh Việt.',
            'Tôi có thể giúp gì cho bạn?'
        ],
        // kept for backward-compat; actual placeholder comes from i18n
        inputPlaceholder: 'Nhập câu hỏi của bạn...'
    });
</script>
<!-- Hero Banner Section (Novena Style) -->
<section class="banner">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-12 col-xl-7">
                <div class="block">
                    <div class="divider mb-3"></div>
                    <span class="text-uppercase text-sm letter-spacing">Giải pháp chăm sóc sức khỏe toàn diện</span>
                    <h1 class="mb-3 mt-3">Phòng khám đa khoa ThinhViet</h1>

                    <p class="mb-4 pr-5">Đội ngũ bác sĩ giàu kinh nghiệm, trang thiết bị hiện đại,
                        dịch vụ chăm sóc tận tâm. Chúng tôi cam kết mang đến sự an tâm và sức khỏe tốt nhất cho bạn.</p>

                    <div class="btn-container">
                        <a href="./doctor_team" class="btn btn-main-2 btn-icon btn-round-full">
                            Đặt lịch khám <i class="icofont-simple-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section (Novena Style) -->
<section class="features banner2">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="feature-block d-lg-flex">
                    <!-- Online Appointment -->
                    <div class="feature-item mb-5 mb-lg-0">
                        <div class="feature-icon mb-4">
                            <i class="icofont-surgeon-alt"></i>
                        </div>
                        <span>Dịch vụ 24/7</span>
                        <h4 class="mb-3">Đặt lịch trực tuyến</h4>
                        <p class="mb-4">Hỗ trợ đặt lịch khám bệnh mọi lúc mọi nơi. Chúng tôi áp dụng nguyên tắc chăm sóc
                            sức khỏe gia đình.</p>
                        <a href="./doctor_team" class="btn btn-main-2 btn-round-full">Đặt lịch ngay</a>
                    </div>

                    <!-- Working Hours -->
                    <div class="feature-item mb-5 mb-lg-0">
                        <div class="feature-icon mb-4">
                            <i class="icofont-ui-clock"></i>
                        </div>
                        <span>Giờ làm việc</span>
                        <h4 class="mb-3">Lịch làm việc</h4>
                        <ul class="w-hours list-unstyled">
                            <li class="d-flex justify-content-between">Thứ 2 - Chủ nhật: <span>7:00 - 21:00</span></li>

                        </ul>
                    </div>

                    <!-- Emergency Cases -->
                    <div class="feature-item mb-5 mb-lg-0">
                        <div class="feature-icon mb-4">
                            <i class="icofont-support"></i>
                        </div>
                        <span>Cấp cứu khẩn cấp</span>
                        <h4 class="mb-3">(84) 28-1234-5678</h4>
                        <p>Hỗ trợ 24/7 cho các trường hợp khẩn cấp. Liên hệ ngay với chúng tôi khi cần hỗ trợ y tế khẩn
                            cấp.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Doctors List Section (Novena Style) -->
<section class="section-home" id="doctors">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 text-center">
                <div class="section-title">
                    <h2>Đội ngũ bác sĩ giàu kinh nghiệm</h2>
                    <div class="divider mx-auto my-4"></div>
                    <p>Đội ngũ bác sĩ chuyên môn cao với nhiều năm kinh nghiệm trong các lĩnh vực khác nhau.
                        Cam kết mang đến dịch vụ chăm sóc sức khỏe tốt nhất cho bệnh nhân.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <?php if (!empty($doctors)) : ?>
                <?php $doctorsLimited = array_slice($doctors, 0, 6);
                foreach ($doctorsLimited as $doc) : ?>
                    <?php
                    $spec = $doc['chuyen_khoa'] ?? '';
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="doctor-card-novena">
                            <?php
                            $imgSrc = null;
                            if (!empty($doc['hinh_anh'])) {
                                // Nếu giá trị đã là đường dẫn bắt đầu bằng uploads/ thì dùng trực tiếp
                                if (strpos($doc['hinh_anh'], 'uploads/') === 0) {
                                    $imgSrc = './' . $doc['hinh_anh'];
                                } else {
                                    // Nếu chỉ là tên file thì ghép với thư mục uploads/BS/
                                    $imgSrc = './uploads/BS/' . $doc['hinh_anh'];
                                }
                            }
                            ?>
                            <?php if ($imgSrc): ?>
                                <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                                    alt="Bác sĩ <?php echo htmlspecialchars($doc['ten']); ?>">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center"
                                    style="width:100%;height:220px;background:#f5f5f5;border-radius:8px;">
                                    <i class="icofont-doctor" style="font-size:64px;color:#999;"></i>
                                </div>
                            <?php endif; ?>
                            <h5><?php echo htmlspecialchars($doc['ten']); ?></h5>
                            <p class="specialty">
                                <i class="icofont-stethoscope"></i>
                                Chuyên khoa <?php echo htmlspecialchars($spec ?: 'Đa khoa'); ?>
                            </p>
                            <p class="experience">
                                <i class="icofont-clock-time"></i>
                                <?php echo (int)($doc['so_nam_kinh_nghiem'] ?? 0); ?> năm kinh nghiệm
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="col-12">
                    <div class="text-center">
                        <i class="icofont-doctor fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Chưa có dữ liệu bác sĩ.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="row mt-4">
            <div class="col-12 text-center">
                <a class="btn btn-main-2 btn-round-full btn-icon" href="./doctor_team">
                    Xem tất cả bác sĩ <i class="icofont-simple-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Specialties Section (Novena Style) -->
<section class="section service gray-bg" id="chuyenkhoa">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 text-center">
                <div class="section-title">
                    <h2>Các chuyên khoa của chúng tôi</h2>
                    <div class="divider mx-auto my-4"></div>
                    <p>Đội ngũ bác sĩ chuyên môn cao với nhiều năm kinh nghiệm trong các chuyên khoa đa dạng.
                        Chúng tôi cam kết mang đến dịch vụ chăm sóc sức khỏe toàn diện và chuyên nghiệp.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <?php
            $specialtiesLimited = array_slice($specialties ?? [], 0, 6);
            foreach ($specialtiesLimited as $sp) :
                $name = $sp['ten'];
                $count = (int)($sp['doctor_count'] ?? 0);

                // Lấy icon trực tiếp từ database, nếu không có thì dùng default
                $iconClass = !empty($sp['icon']) ? trim($sp['icon']) : 'icofont-stethoscope';

                // Đảm bảo icon class hợp lệ (nếu có fa- thì giữ nguyên, nếu không có prefix thì thêm icofont-)
                if (!empty($iconClass)) {
                    // Nếu icon không có prefix (icofont- hoặc fa-), thêm icofont-
                    if (strpos($iconClass, 'icofont-') !== 0 && strpos($iconClass, 'fa-') !== 0 && strpos($iconClass, 'fas ') !== 0 && strpos($iconClass, 'far ') !== 0) {
                        $iconClass = 'icofont-' . $iconClass;
                    }
                }

                $desc = $sp['mo_ta'] ?? 'Dịch vụ chăm sóc sức khỏe chuyên nghiệp';
            ?>
                <div class="col-lg-4 col-md-6 col-sm-6 mb-4">
                    <a class="text-decoration-none"
                        href="./doctors_by_specialty?slug=<?php echo urlencode($sp['slug'] ?? ''); ?>">
                        <div class="service-item">
                            <div class="icon d-flex align-items-center">
                                <i class="<?php echo htmlspecialchars($iconClass); ?> text-lg"></i>
                                <h4 class="mt-3 mb-3 ml-3"><?php echo htmlspecialchars($name); ?></h4>
                            </div>

                            <div class="content">
                                <p class="mb-4"><?php echo htmlspecialchars($desc); ?></p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row mt-4">
            <div class="col-12 text-center">
                <a class="btn btn-main-2 btn-round-full btn-icon" href="./specialties_all">
                    Xem tất cả chuyên khoa <i class="icofont-simple-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>


<!-- Stats Section - Modern Design -->
<section class="stats-section-modern">
    <div class="container">
        <div class="row">
            <!-- Total Doctors -->
            <div class="col-lg-3 col-md-6 col-sm-6 mb-4 mb-lg-0">
                <div class="stat-card stat-card-blue">
                    <div class="stat-icon">
                        <i class="icofont-doctor"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">
                            <span class="count"
                                data-count="<?php echo (int)$totalDoctors; ?>"><?php echo (int)$totalDoctors; ?></span><span
                                class="plus-sign">+</span>
                        </h3>
                        <p class="stat-label">Bác sĩ chuyên khoa</p>
                    </div>
                </div>
            </div>

            <!-- Average Experience -->
            <div class="col-lg-3 col-md-6 col-sm-6 mb-4 mb-lg-0">
                <div class="stat-card stat-card-green">
                    <div class="stat-icon">
                        <i class="icofont-flag"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">
                            <span class="count"
                                data-count="<?php echo (int)$avgExperience; ?>"><?php echo (int)$avgExperience; ?></span><span
                                class="plus-sign">+</span>
                        </h3>
                        <p class="stat-label">Năm kinh nghiệm trung bình</p>
                    </div>
                </div>
            </div>

            <!-- Total Specialties -->
            <div class="col-lg-3 col-md-6 col-sm-6 mb-4 mb-lg-0">
                <div class="stat-card stat-card-orange">
                    <div class="stat-icon">
                        <i class="icofont-badge"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">
                            <span class="count"
                                data-count="<?php echo (int)$totalSpecialties; ?>"><?php echo (int)$totalSpecialties; ?></span><span
                                class="plus-sign">+</span>
                        </h3>
                        <p class="stat-label">Chuyên khoa</p>
                    </div>
                </div>
            </div>

            <!-- Patients Treated -->
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="stat-card stat-card-red">
                    <div class="stat-icon">
                        <i class="icofont-patient-bed"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">
                            <span class="count"
                                data-count="<?php echo (int)$patientsTreated; ?>"><?php echo (int)$patientsTreated; ?></span><span
                                class="plus-sign">+</span>
                        </h3>
                        <p class="stat-label">Bệnh nhân đã điều trị</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* Modern Stats Section */
    .stats-section-modern {
        padding: 50px 0;
        background: #f8f9fa;
        position: relative;
    }

    .stat-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 40px 30px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, transparent, currentColor, transparent);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .stat-card:hover::before {
        opacity: 1;
    }

    .stat-card:hover {
        transform: translateY(-15px);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
    }

    .stat-icon {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 25px;
        position: relative;
        transition: all 0.4s ease;
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .stat-icon i {
        font-size: 3rem;
        color: #ffffff;
        z-index: 2;
        position: relative;
    }

    .stat-icon::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: currentColor;
        opacity: 0.1;
        transform: scale(0);
        transition: transform 0.4s ease;
    }

    .stat-card:hover .stat-icon::after {
        transform: scale(1.5);
    }

    .stat-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .stat-number {
        margin: 0 0 15px 0;
        font-size: 3.5rem;
        font-weight: 700;
        line-height: 1.2;
        display: block;
        width: 100%;
    }

    .stat-number .count {
        display: inline-block !important;
        min-width: 80px;
        text-align: center;
        font-weight: 700;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .stat-number .plus-sign {
        margin-left: 4px;
        font-weight: 700;
        display: inline-block;
    }

    .stat-label {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Color Themes */
    .stat-card-blue {
        color: #223a66;
    }

    .stat-card-blue .stat-icon {
        background: linear-gradient(135deg, #223a66 0%, #1e5f8e 100%);
    }

    .stat-card-blue .stat-number,
    .stat-card-blue .stat-number .count,
    .stat-card-blue .stat-number .plus-sign {
        color: #223a66 !important;
    }

    .stat-card-green {
        color: #28a745;
    }

    .stat-card-green .stat-icon {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .stat-card-green .stat-number,
    .stat-card-green .stat-number .count,
    .stat-card-green .stat-number .plus-sign {
        color: #28a745 !important;
    }

    .stat-card-orange {
        color: #fd7e14;
    }

    .stat-card-orange .stat-icon {
        background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
    }

    .stat-card-orange .stat-number,
    .stat-card-orange .stat-number .count,
    .stat-card-orange .stat-number .plus-sign {
        color: #fd7e14 !important;
    }

    .stat-card-red {
        color: #e12454;
    }

    .stat-card-red .stat-icon {
        background: linear-gradient(135deg, #e12454 0%, #c91e42 100%);
    }

    .stat-card-red .stat-number,
    .stat-card-red .stat-number .count,
    .stat-card-red .stat-number .plus-sign {
        color: #e12454 !important;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .stats-section-modern {
            padding: 80px 0;
        }

        .stat-card {
            padding: 35px 25px;
            margin-bottom: 30px;
        }

        .stat-icon {
            width: 90px;
            height: 90px;
            margin-bottom: 20px;
        }

        .stat-icon i {
            font-size: 2.5rem;
        }

        .stat-number {
            font-size: 3rem;
        }

        .stat-label {
            font-size: 1rem;
        }
    }

    @media (max-width: 768px) {
        .stats-section-modern {
            padding: 60px 0;
        }

        .stat-card {
            padding: 30px 20px;
            margin-bottom: 25px;
        }

        .stat-icon {
            width: 80px;
            height: 80px;
            margin-bottom: 18px;
        }

        .stat-icon i {
            font-size: 2.2rem;
        }

        .stat-number {
            font-size: 2.5rem;
        }

        .stat-label {
            font-size: 0.95rem;
        }
    }

    @media (max-width: 576px) {
        .stats-section-modern {
            padding: 50px 0;
        }

        .stat-card {
            padding: 25px 15px;
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            margin-bottom: 15px;
        }

        .stat-icon i {
            font-size: 1.8rem;
        }

        .stat-number {
            font-size: 2rem;
        }

        .stat-label {
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
    }
</style>
<?php
// Include footer
include 'Views/layouts/footer.php';
?>