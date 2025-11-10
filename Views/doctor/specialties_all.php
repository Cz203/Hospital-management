<?php
$page_title = 'Tất cả chuyên khoa';
include 'Views/layouts/header.php';
?>

<!-- Page Header (Novena Style) -->
<section class="section page-header-section"
    style="background: linear-gradient(135deg, #223a66 0%, #1e5f8e 100%); position: relative; overflow: hidden; padding-top: 100px; padding-bottom: 60px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="section-title">
                    <h2 class="text-white mb-3">Tất cả chuyên khoa</h2>
                    <div class="divider mx-auto my-4" style="background: rgba(255,255,255,0.3);"></div>
                    <p class="text-white-50">Chọn chuyên khoa để xem danh sách bác sĩ tương ứng</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="text-center">
                    <div class="d-inline-flex">
                        <div class="text-center mr-5">
                            <div class="h2 text-white mb-1"><?php echo count($specialties ?? []); ?>+</div>
                            <small class="text-white-50">Chuyên khoa</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Specialties Grid (Novena Style) -->
<section class="section" style="padding-top: 80px; padding-bottom: 80px;">
    <div class="container">
        <div class="row">
            <?php foreach (($specialties ?? []) as $sp):
                $name = $sp['ten'];
                $count = isset($sp['doctor_count']) ? (int)$sp['doctor_count'] : 0;

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
                $badgeColor = $count > 0 ? 'primary' : 'secondary';
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <a class="text-decoration-none"
                    href="./doctors_by_specialty?slug=<?php echo urlencode($sp['slug'] ?? ''); ?>">
                    <div class="specialty-card-novena">
                        <div class="icon">
                            <i class="<?php echo htmlspecialchars($iconClass); ?>"></i>
                        </div>
                        <h5><?php echo htmlspecialchars($name); ?></h5>
                        <p><?php echo htmlspecialchars($desc); ?></p>

                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'Views/layouts/footer.php'; ?>