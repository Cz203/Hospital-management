<?php
$page_title = 'Bác sĩ - ' . ($specialty['ten'] ?? 'Chuyên khoa');
include 'Views/layouts/header.php';

// Lấy icon trực tiếp từ database, nếu không có thì dùng default
$specialtyIcon = !empty($specialty['icon']) ? trim($specialty['icon']) : 'icofont-stethoscope';

// Đảm bảo icon class hợp lệ (nếu có fa- thì giữ nguyên, nếu không có prefix thì thêm icofont-)
if (!empty($specialtyIcon)) {
    // Nếu icon không có prefix (icofont- hoặc fa-), thêm icofont-
    if (strpos($specialtyIcon, 'icofont-') !== 0 && strpos($specialtyIcon, 'fa-') !== 0 && strpos($specialtyIcon, 'fas ') !== 0 && strpos($specialtyIcon, 'far ') !== 0) {
        $specialtyIcon = 'icofont-' . $specialtyIcon;
    }
}
?>

<!-- Page Header (Novena Style) -->
<section class="section page-header-section"
    style="background: linear-gradient(135deg, #223a66 0%, #1e5f8e 100%); position: relative; overflow: hidden; padding-top: 100px; padding-bottom: 60px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <div class="section-title">
                    <h2 class="text-white mb-3">
                        <i class="<?php echo htmlspecialchars($specialtyIcon); ?> mr-2" style="color: #e12454;"></i>
                        <?php echo htmlspecialchars($specialty['ten'] ?? 'Chuyên khoa'); ?>
                    </h2>
                    <div class="divider mx-auto my-4" style="background: rgba(255,255,255,0.3);"></div>
                    <?php if (!empty($specialty['mo_ta'])): ?>
                        <p class="text-white-50"><?php echo htmlspecialchars($specialty['mo_ta']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="text-center">
                    <div class="d-inline-flex align-items-center">
                        <div class="text-center mr-5"></div>
                        <div class="h2 text-white mb-1"><?php echo count($doctors ?? []); ?>+</div>
                        <small class="text-white-50">Bác sĩ</small>
                    </div>
                    <div class="text-center">
                        <a href="./specialties_all" class="btn btn-main-2 btn-round-full btn-icon">
                            <i class="icofont-list mr-2"></i>Tất cả chuyên khoa
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>

<!-- Doctors Grid (Novena Style) -->
<section class="section" style="padding-top: 60px; padding-bottom: 80px;">
    <div class="container">
        <div class="row" id="doctorsContainer">
            <?php if (empty($doctors)): ?>
                <div class="col-12 text-center py-5">
                    <div class="mb-3">
                        <i class="icofont-search-1" style="font-size: 4rem; color: #999;"></i>
                    </div>
                    <h4 class="text-muted">Không có bác sĩ</h4>
                    <p class="text-muted">Chuyên khoa này chưa có bác sĩ nào</p>
                    <a href="./specialties_all" class="btn btn-main btn-round-full mt-3">
                        <i class="icofont-arrow-left mr-2"></i>Quay lại danh sách chuyên khoa
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($doctors as $doc): ?>
                    <?php
                    // Xử lý đường dẫn ảnh bác sĩ
                    $imgSrc = null;

                    if (!empty($doc['hinh_anh'])) {
                        // Chuẩn hóa đường dẫn
                        $hinhAnh = trim($doc['hinh_anh']);
                        $hinhAnh = str_replace('\\', '/', $hinhAnh);

                        // Loại bỏ ./ nếu có ở đầu
                        $hinhAnh = ltrim($hinhAnh, './');

                        // Đảm bảo có uploads/ ở đầu
                        if (strpos($hinhAnh, 'uploads/') !== 0) {
                            $hinhAnh = 'uploads/' . $hinhAnh;
                        }

                        // Đường dẫn web
                        $imgSrcWeb = './' . $hinhAnh;

                        // Đường dẫn filesystem (từ root của project)
                        $imgSrcFs = __DIR__ . '/../../' . $hinhAnh;

                        // Kiểm tra file có tồn tại không
                        if (file_exists($imgSrcFs)) {
                            $imgSrc = $imgSrcWeb;
                        }
                    }

                    // Nếu không có ảnh, sử dụng placeholder SVG
                    if (!$imgSrc) {
                        $imgSrc = 'data:image/svg+xml;base64,' . base64_encode('
                            <svg xmlns="http://www.w3.org/2000/svg" width="150" height="150" viewBox="0 0 150 150">
                                <rect width="150" height="150" fill="#f8f9fa"/>
                                <circle cx="75" cy="55" r="25" fill="#dee2e6"/>
                                <path d="M25 125 Q75 85 125 125" stroke="#dee2e6" stroke-width="4" fill="none"/>
                                <text x="75" y="140" text-anchor="middle" font-family="Arial" font-size="14" fill="#6c757d">BS</text>
                            </svg>
                        ');
                    }

                    $experience = (int)($doc['so_nam_kinh_nghiem'] ?? 0);
                    $docSpecialty = $doc['chuyen_khoa'] ?: 'Đa khoa';
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4 doctor-item" data-spec="<?php echo htmlspecialchars($docSpecialty); ?>"
                        data-name="<?php echo htmlspecialchars($doc['ten']); ?>"
                        data-phone="<?php echo htmlspecialchars($doc['so_dien_thoai'] ?: ''); ?>"
                        data-experience="<?php echo $experience; ?>">

                        <div class="doctor-card-novena">
                            <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                                alt="Bác sĩ <?php echo htmlspecialchars($doc['ten']); ?>"
                                onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNTAiIGhlaWdodD0iMTUwIiB2aWV3Qm94PSIwIDAgMTUwIDE1MCI+PHJlY3Qgd2lkdGg9IjE1MCIgaGVpZ2h0PSIxNTAiIGZpbGw9IiNmOGY5ZmEiLz48Y2lyY2xlIGN4PSI3NSIgY3k9IjU1IiByPSIyNSIgZmlsbD0iI2RlZTJlNiIvPjxwYXRoIGQ9Ik0yNSAxMjUgUTc1IDg1IDEyNSAxMjUiIHN0cm9rZT0iI2RlZTJlNiIgc3Ryb2tlLXdpZHRoPSI0IiBmaWxsPSJub25lIi8+PHRleHQgeD0iNzUiIHk9IjE0MCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE0IiBmaWxsPSIjNmM3NTdkIj5CUzwvdGV4dD48L3N2Zz4=';">

                            <h5><?php echo htmlspecialchars($doc['ten']); ?></h5>
                            <p class="specialty">
                                <i class="icofont-stethoscope"></i>
                                Chuyên khoa <?php echo htmlspecialchars($docSpecialty); ?>
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
            <?php endif; ?>
        </div>

        <!-- No Results Message (for filtering) -->
        <div id="noResults" class="text-center py-5" style="display: none;">
            <div class="no-results-icon mb-3">
                <i class="icofont-search-1" style="font-size: 4rem; color: #999;"></i>
            </div>
            <h4 class="text-muted">Không tìm thấy bác sĩ</h4>
            <p class="text-muted">Vui lòng thử lại với từ khóa khác</p>
            <button class="btn btn-main btn-round-full" onclick="clearFilters()">
                <i class="icofont-refresh mr-2"></i>Xóa bộ lọc
            </button>
        </div>
    </div>
</section>

<style>
    /* Page Header Section */
    .page-header-section {
        position: relative;
        overflow: hidden;
    }

    .page-header-section .section-title h2 {
        font-size: 2.5rem;
        font-weight: 700;
    }

    .page-header-section .divider {
        width: 60px;
        height: 3px;
        background: rgba(255, 255, 255, 0.3);
    }

    /* Doctor Cards */
    .doctor-card-novena {
        background: #fff;
        border-radius: 10px;
        padding: 25px 20px;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .doctor-card-novena:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .doctor-card-novena img {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        margin: 0 auto 20px;
        border: 5px solid #f8f9fa;
        display: block;
    }

    .doctor-card-novena h5 {
        color: #223a66;
        font-weight: 600;
        margin-bottom: 12px;
        font-size: 1.2rem;
    }

    .doctor-card-novena .specialty {
        color: #223a66;
        font-size: 14px;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .doctor-card-novena .specialty i {
        margin-right: 5px;
        color: #223a66;
    }

    .doctor-card-novena .experience {
        color: #666;
        font-size: 13px;
        margin-bottom: 20px;
    }

    .doctor-card-novena .experience i {
        margin-right: 5px;
        color: #999;
    }

    .doctor-card-novena .doctor-actions {
        margin-top: auto;
        padding-top: 15px;
        border-top: 1px solid #f0f0f0;
    }

    .doctor-card-novena .doctor-actions .btn {
        font-size: 14px;
        padding: 10px 20px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header-section .section-title h2 {
            font-size: 2rem;
        }

        .doctor-card-novena img {
            width: 120px;
            height: 120px;
        }
    }
</style>

<?php include 'Views/layouts/footer.php'; ?>