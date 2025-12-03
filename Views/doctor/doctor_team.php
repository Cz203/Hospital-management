<?php
require_once 'Models/Doctor.php';
require_once 'Models/Specialty.php';
$doctorModel = new Doctor();
$doctors = $doctorModel->getAll();

// Lấy chuyên khoa từ bảng chuyen_khoa thay vì từ bac_si
$spModel = new Specialty();
$specialtyRows = $spModel->all();
$specialties = array_map(function ($row) {
    return $row['ten'];
}, $specialtyRows);

// Set page title for header
$page_title = 'Đội ngũ bác sĩ';

// Include header
include 'Views/layouts/header.php';
?>

<!-- Page Header (Novena Style) -->
<section class="section page-header-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="section-title">
                    <h2 class="text-white mb-3">Đội ngũ bác sĩ</h2>
                    <div class="divider mx-auto my-4" style="background: rgba(255,255,255,0.3);"></div>
                    <p class="text-white-50">Tìm kiếm và lọc theo chuyên khoa để chọn bác sĩ phù hợp</p>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="text-center">
                    <div class="d-inline-flex">
                        <div class="text-center mr-5">
                            <div class="h2 text-white mb-1"><?php echo count($doctors); ?>+</div>
                            <small class="text-white-50">Bác sĩ</small>
                        </div>
                        <div class="text-center">
                            <div class="h2 text-white mb-1"><?php echo count($specialties); ?>+</div>
                            <small class="text-white-50">Chuyên khoa</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filter Section (Novena Style) -->
<section class="section gray-bg" style="padding-top: 40px; padding-bottom: 40px;">
    <div class="container">
        <div class="row align-items-end">
            <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                <label class="form-label font-weight-bold mb-2">
                    <i class="icofont-filter mr-2"></i>Chuyên khoa
                </label>
                <select id="specialtyFilter" class="form-control">
                    <option value="">Tất cả chuyên khoa</option>
                    <?php foreach ($specialties as $spec): ?>
                        <option value="<?php echo htmlspecialchars($spec); ?>">
                            <?php echo htmlspecialchars($spec); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                <label class="form-label font-weight-bold mb-2">
                    <i class="icofont-search-1 mr-2"></i>Tìm kiếm
                </label>
                <input id="searchInput" type="text" class="form-control"
                    placeholder="Nhập tên bác sĩ hoặc số điện thoại...">
            </div>
            <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                <label class="form-label font-weight-bold mb-2">
                    <i class="icofont-sort mr-2"></i>Sắp xếp
                </label>
                <select id="sortFilter" class="form-control">
                    <option value="name">Theo tên A-Z</option>
                    <option value="experience">Theo kinh nghiệm</option>
                    <option value="specialty">Theo chuyên khoa</option>
                </select>
            </div>
        </div>
    </div>
</section>

<!-- Doctors Grid (Novena Style) -->
<section class="section" style="padding-top: 60px; padding-bottom: 80px;">
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
                        // Nếu chỉ có tên file thì thêm đường dẫn uploads/BS/
                        $imgSrc = './uploads/BS/' . $doc['hinh_anh'];
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
                <div class="col-lg-4 col-md-6 mb-4 doctor-item" data-spec="<?php echo htmlspecialchars($specialty); ?>"
                    data-name="<?php echo htmlspecialchars($doc['ten']); ?>"
                    data-phone="<?php echo htmlspecialchars($doc['so_dien_thoai'] ?: ''); ?>"
                    data-experience="<?php echo $experience; ?>">

                    <div class="doctor-card-novena">
                        <img src="<?php echo $imgSrc; ?>" alt="Bác sĩ <?php echo htmlspecialchars($doc['ten']); ?>"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">

                        <h5><?php echo htmlspecialchars($doc['ten']); ?></h5>
                        <p class="specialty">
                            <i class="icofont-stethoscope"></i>
                            Chuyên khoa <?php echo htmlspecialchars($specialty); ?>
                        </p>
                        <p class="experience">
                            <i class="icofont-clock-time"></i>
                            <?php echo $experience; ?> năm kinh nghiệm
                        </p>
                        <div class="doctor-actions mt-3">
                            <a href="./hospital_appointment?doctor_id=<?php echo base64_encode($doc['id']); ?>"
                                class="btn btn-main btn-round-full btn-sm mb-2 w-100">
                                <i class="icofont-calendar mr-2"></i>Đặt lịch
                            </a>
                            <a href="./consultation_booking?doctor_id=<?php echo base64_encode($doc['id']); ?>"
                                class="btn btn-main-2 btn-round-full btn-sm w-100">
                                <i class="icofont-video-cam mr-2"></i>Tư vấn
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- No Results Message -->
        <div id="noResults" class="text-center py-5" style="display: none;">
            <div class="no-results-icon mb-3">
                <i class="icofont-search-1" style="font-size: 4rem; color: #999;"></i>
            </div>
            <h4 class="text-muted">Không tìm thấy bác sĩ</h4>
            <p class="text-muted">Vui lòng thử lại với từ khóa khác hoặc chọn chuyên khoa khác</p>
            <button class="btn btn-main btn-round-full" onclick="clearFilters()">
                <i class="icofont-refresh mr-2"></i>Xóa bộ lọc
            </button>
        </div>
    </div>
</section>



<style>
    /* Page Header (Novena Style) */
    .page-header-section {
        position: relative;
        overflow: hidden;
        padding-top: 100px;
        padding-bottom: 40px;
        background-image: url('./assets/img/banner1.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    /* Overlay để làm mờ background image */
    .page-header-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(34, 58, 102, 0.85) 0%, rgba(26, 45, 77, 0.85) 100%);
        z-index: 1;
    }

    /* Đảm bảo content nằm trên overlay */
    .page-header-section .container {
        position: relative;
        z-index: 2;
    }

    .page-header-section .divider {
        width: 60px;
        height: 3px;
        background: rgba(255, 255, 255, 0.3);
    }

    .page-header-section .section-title h2 {
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    .page-header-section .section-title p {
        font-size: 1rem;
        margin-bottom: 0;
    }

    .page-header-section .h2 {
        font-size: 2rem;
    }

    /* Filter Section (Novena Style) */
    .filter-section .form-control {
        border-radius: 5px;
        border: 1px solid #ddd;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    .filter-section .form-control:focus {
        border-color: #223a66;
        box-shadow: 0 0 0 0.2rem rgba(34, 58, 102, 0.25);
        outline: none;
    }

    /* Doctor Cards (Novena Style) - sử dụng class từ novena-custom.css */
    .doctor-card-novena .doctor-actions {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #eee;
    }

    .doctor-card-novena .doctor-actions .btn {
        margin-bottom: 8px;
    }

    /* No Results */
    .no-results-icon {
        opacity: 0.5;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header-section {
            padding-top: 80px !important;
            padding-bottom: 30px !important;
        }

        .page-header-section .section-title h2 {
            font-size: 1.75rem;
        }

        .page-header-section .h2 {
            font-size: 1.5rem;
        }

        .filter-section {
            padding-top: 30px !important;
            padding-bottom: 30px !important;
        }

        .doctor-card-novena img {
            width: 120px;
            height: 120px;
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

        // Initialize from URL params if provided (q for name, spec for specialty)
        (function initFromParams() {
            try {
                const params = new URLSearchParams(window.location.search);
                const q = (params.get('q') || '').trim();
                const spec = (params.get('spec') || '').trim();
                if (q) {
                    searchInput.value = q;
                }
                if (spec) {
                    // Try to match by exact option value first
                    let matched = false;
                    for (let i = 0; i < specSelect.options.length; i++) {
                        const opt = specSelect.options[i];
                        if ((opt.value || '').toLowerCase() === spec.toLowerCase() || (opt.text || '')
                            .toLowerCase() === spec.toLowerCase()) {
                            specSelect.value = opt.value;
                            matched = true;
                            break;
                        }
                    }
                    // If not matched, leave select as-is (filters will still apply by name if q is set)
                }
            } catch (e) {}
        })();

        // Apply filters after initializing params
        applyFilters();
    });
</script>

<?php
// Include footer
include 'Views/layouts/footer.php';
?>