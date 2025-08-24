<?php
require_once 'Models/Doctor.php';
$doctorModel = new Doctor();
$doctors = $doctorModel->getAll();

$specialties = array_values(array_unique(array_filter(array_map(function ($d) {
    return $d['chuyen_khoa'] ?? '';
}, $doctors))));

// Set page title for header
$page_title = 'Đội ngũ bác sĩ - ThinhViet Hospital';

// Include header
include 'Views/layouts/header.php';
?>

<!-- Page Header -->
<section class="page-header bg-gradient-primary text-white" style="margin-top: 81px;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">
                    <i class="fas fa-user-md me-3"></i>Đội ngũ bác sĩ
                </h1>
                <p class="lead mb-0">Tìm kiếm và lọc theo chuyên khoa để chọn bác sĩ phù hợp</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="header-stats">
                    <div class="d-flex justify-content-lg-end gap-4">
                        <div class="text-center">
                            <div class="h3 mb-1"><?php echo count($doctors); ?>+</div>
                            <small>Bác sĩ</small>
                        </div>
                        <div class="text-center">
                            <div class="h3 mb-1"><?php echo count($specialties); ?>+</div>
                            <small>Chuyên khoa</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filter Section -->
<section class="filter-section py-4 bg-light">
    <div class="container">
        <div class="row align-items-end g-3">
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-filter me-2"></i>Chuyên khoa
                </label>
                <select id="specialtyFilter" class="form-select form-select-lg">
                    <option value="">Tất cả chuyên khoa</option>
                    <?php foreach ($specialties as $spec): ?>
                        <option value="<?php echo htmlspecialchars($spec); ?>">
                            <?php echo htmlspecialchars($spec); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-4 col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-search me-2"></i>Tìm kiếm
                </label>
                <input id="searchInput" type="text" class="form-control form-control-lg"
                    placeholder="Nhập tên bác sĩ hoặc số điện thoại...">
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-sort me-2"></i>Sắp xếp
                </label>
                <select id="sortFilter" class="form-select form-select-lg">
                    <option value="name">Theo tên A-Z</option>
                    <option value="experience">Theo kinh nghiệm</option>
                    <option value="specialty">Theo chuyên khoa</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="d-grid">
                    <a href="./hospital_appointment" class="btn btn-primary btn-lg">
                        <i class="fas fa-calendar-check me-2"></i>Đặt lịch
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Doctors Grid -->
<section class="doctors-section py-5">
    <div class="container">
        <div class="row" id="doctorsContainer">
            <?php foreach ($doctors as $doc): ?>
                <?php

                // Kiểm tra và sử dụng ảnh từ database
                $imgSrc = null;

                // Kiểm tra xem có ảnh trong database không (trường hinh_anh)
                if (!empty($doc['hinh_anh'])) {
                    // Nếu hinh_anh đã có đường dẫn uploads/ thì sử dụng trực tiếp
                    if (strpos($doc['hinh_anh'], 'uploads/') === 0) {
                        $imgSrc = './' . $doc['hinh_anh'];
                    } else {
                        // Nếu chỉ có tên file thì thêm đường dẫn uploads/
                        $imgSrc = './uploads/' . $doc['hinh_anh'];
                    }

                    // Kiểm tra file có tồn tại không
                    if (!file_exists($imgSrc)) {
                        $imgSrc = null; // Reset nếu file không tồn tại
                    }
                }

                // Nếu không có ảnh hoặc file không tồn tại, sử dụng placeholder
                if (!$imgSrc) {
                    $imgSrc = 'data:image/svg+xml;base64,' . base64_encode('
                        <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120">
                            <rect width="120" height="120" fill="#f8f9fa"/>
                            <circle cx="60" cy="45" r="20" fill="#dee2e6"/>
                            <path d="M20 100 Q60 70 100 100" stroke="#dee2e6" stroke-width="3" fill="none"/>
                            <text x="60" y="115" text-anchor="middle" font-family="Arial" font-size="12" fill="#6c757d">BS</text>
                        </svg>
                    ');
                }

                $experience = (int)($doc['so_nam_kinh_nghiem'] ?? 0);
                $specialty = $doc['chuyen_khoa'] ?: 'Đa khoa';
                ?>
                <div class="col-lg-4 col-md-6 mb-4 doctor-item animate-on-scroll"
                    data-spec="<?php echo htmlspecialchars($specialty); ?>"
                    data-name="<?php echo htmlspecialchars($doc['ten']); ?>"
                    data-phone="<?php echo htmlspecialchars($doc['so_dien_thoai'] ?: ''); ?>"
                    data-experience="<?php echo $experience; ?>">

                    <div class="doctor-card h-100">
                        <div class="doctor-header">
                            <div class="doctor-avatar">
                                <img src="<?php echo $imgSrc; ?>" alt="Bác sĩ <?php echo htmlspecialchars($doc['ten']); ?>"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div class="avatar-placeholder"
                                    style="display: none; width: 100%; height: 100%; background: #f8f9fa; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #6c757d; font-size: 2rem;">
                                    <i class="fas fa-user-md"></i>
                                </div>
                            </div>
                            <div class="doctor-status">
                                <span class="status-badge online">Đang hoạt động</span>
                            </div>
                        </div>

                        <div class="doctor-body">
                            <h5 class="doctor-name"><?php echo htmlspecialchars($doc['ten']); ?></h5>
                            <p class="doctor-specialty">
                                <i class="fas fa-stethoscope me-2"></i>
                                <?php echo htmlspecialchars($specialty); ?>
                            </p>

                            <div class="doctor-stats">
                                <div class="stat-item">
                                    <i class="fas fa-clock text-primary"></i>
                                    <span><?php echo $experience; ?> năm kinh nghiệm</span>
                                </div>
                                <?php if (!empty($doc['so_giay_phep'])): ?>
                                    <div class="stat-item">
                                        <i class="fas fa-certificate text-success"></i>
                                        <span>GPL: <?php echo htmlspecialchars($doc['so_giay_phep']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($doc['so_dien_thoai'])): ?>
                                    <div class="stat-item">
                                        <i class="fas fa-phone text-info"></i>
                                        <span><?php echo htmlspecialchars($doc['so_dien_thoai']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="doctor-actions">
                                <a href="./hospital_appointment" class="btn btn-primary btn-sm w-100 mb-2">
                                    <i class="fas fa-calendar-plus me-2"></i>Đặt lịch khám
                                </a>
                                <a href="./consultation_booking" class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fas fa-video me-2"></i>Tư vấn trực tuyến
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- No Results Message -->
        <div id="noResults" class="text-center py-5" style="display: none;">
            <div class="no-results-icon mb-3">
                <i class="fas fa-search fa-3x text-muted"></i>
            </div>
            <h4 class="text-muted">Không tìm thấy bác sĩ</h4>
            <p class="text-muted">Vui lòng thử lại với từ khóa khác hoặc chọn chuyên khoa khác</p>
            <button class="btn btn-primary" onclick="clearFilters()">
                <i class="fas fa-refresh me-2"></i>Xóa bộ lọc
            </button>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 class="mb-3">Cần tư vấn thêm?</h3>
                <p class="mb-0">Liên hệ với chúng tôi để được hỗ trợ và tư vấn chi tiết về dịch vụ khám chữa bệnh</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="tel:02812345678" class="btn btn-light btn-lg me-2">
                    <i class="fas fa-phone me-2"></i>Gọi ngay
                </a>
                <a href="./contact" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-envelope me-2"></i>Liên hệ
                </a>
            </div>
        </div>
    </div>
</section>

<style>
    /* Page Header */
    .page-header {
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="rgba(255,255,255,0.05)" points="0,1000 1000,0 1000,1000"/></svg>');
        background-size: cover;
    }

    .page-header .container {
        position: relative;
        z-index: 2;
    }

    .header-stats .h3 {
        font-weight: 800;
        color: rgba(255, 255, 255, 0.9);
    }

    /* Filter Section */
    .filter-section {
        border-bottom: 1px solid #e9ecef;
    }

    .form-select,
    .form-control {
        border-radius: 12px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .form-select:focus,
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    /* Doctor Cards */
    .doctor-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }

    .doctor-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .doctor-header {
        position: relative;
        padding: 2rem 2rem 1rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        text-align: center;
    }

    .doctor-avatar {
        width: 120px;
        height: 120px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        overflow: hidden;
        border: 4px solid #fff;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .doctor-card:hover .doctor-avatar {
        transform: scale(1.05);
        border-color: var(--primary-color);
    }

    .doctor-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .doctor-status {
        position: absolute;
        top: 1rem;
        right: 1rem;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-badge.online {
        background: #d4edda;
        color: #155724;
    }

    .doctor-body {
        padding: 1.5rem 2rem 2rem;
    }

    .doctor-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--gray-800);
        margin-bottom: 0.5rem;
        text-align: center;
    }

    .doctor-specialty {
        color: var(--primary-color);
        font-weight: 600;
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .doctor-stats {
        margin-bottom: 1.5rem;
    }

    .stat-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.75rem;
        font-size: 0.875rem;
        color: var(--gray-600);
    }

    .stat-item i {
        width: 20px;
        margin-right: 0.75rem;
        font-size: 1rem;
    }

    .doctor-actions {
        border-top: 1px solid #e9ecef;
        padding-top: 1.5rem;
    }

    .doctor-actions .btn {
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .doctor-actions .btn:hover {
        transform: translateY(-2px);
    }

    /* CTA Section */
    .cta-section {
        position: relative;
        overflow: hidden;
    }

    .cta-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="rgba(255,255,255,0.05)" points="0,0 1000,1000 0,1000"/></svg>');
        background-size: cover;
    }

    .cta-section .container {
        position: relative;
        z-index: 2;
    }

    /* No Results */
    .no-results-icon {
        opacity: 0.5;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            text-align: center;
            margin-top: 80px !important;
        }

        .header-stats {
            justify-content: center !important;
            margin-top: 2rem;
        }

        .doctor-card {
            margin-bottom: 2rem;
        }

        .cta-section .text-lg-end {
            text-align: center !important;
            margin-top: 2rem;
        }

        .cta-section .btn {
            display: block;
            width: 100%;
            margin-bottom: 1rem;
        }
    }

    @media (max-width: 576px) {
        .page-header {
            margin-top: 70px !important;
        }

        .doctor-header {
            padding: 1.5rem 1rem 1rem;
        }

        .doctor-body {
            padding: 1rem 1.5rem 1.5rem;
        }

        .doctor-avatar {
            width: 100px;
            height: 100px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const specSelect = document.getElementById('specialtyFilter');
        const searchInput = document.getElementById('searchInput');
        const sortFilter = document.getElementById('sortFilter');
        const items = Array.from(document.querySelectorAll('.doctor-item'));
        const noResults = document.getElementById('noResults');

        function applyFilters() {
            const spec = specSelect.value.trim().toLowerCase();
            const q = searchInput.value.trim().toLowerCase();
            const sortBy = sortFilter.value;

            let visibleItems = 0;

            items.forEach(el => {
                const elSpec = (el.dataset.spec || '').toLowerCase();
                const elName = (el.dataset.name || '').toLowerCase();
                const elPhone = (el.dataset.phone || '').toLowerCase();
                const elExperience = parseInt(el.dataset.experience || 0);

                const matchSpec = !spec || elSpec.includes(spec);
                const matchQ = !q || elName.includes(q) || elPhone.includes(q);

                if (matchSpec && matchQ) {
                    el.style.display = '';
                    visibleItems++;
                } else {
                    el.style.display = 'none';
                }
            });

            // Show/hide no results message
            if (visibleItems === 0) {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }

            // Sort items
            sortItems(sortBy);
        }

        function sortItems(sortBy) {
            const container = document.getElementById('doctorsContainer');
            const items = Array.from(container.children);

            items.sort((a, b) => {
                switch (sortBy) {
                    case 'name':
                        return (a.dataset.name || '').localeCompare(b.dataset.name || '');
                    case 'experience':
                        return (parseInt(b.dataset.experience || 0) - parseInt(a.dataset.experience || 0));
                    case 'specialty':
                        return (a.dataset.spec || '').localeCompare(b.dataset.spec || '');
                    default:
                        return 0;
                }
            });

            items.forEach(item => container.appendChild(item));
        }

        function clearFilters() {
            specSelect.value = '';
            searchInput.value = '';
            sortFilter.value = 'name';
            applyFilters();
        }

        // Event listeners
        specSelect.addEventListener('change', applyFilters);
        searchInput.addEventListener('input', applyFilters);
        sortFilter.addEventListener('change', applyFilters);

        // Make clearFilters available globally
        window.clearFilters = clearFilters;

        // Initialize
        applyFilters();
    });
</script>

<?php
// Include footer
include 'Views/layouts/footer.php';
?>