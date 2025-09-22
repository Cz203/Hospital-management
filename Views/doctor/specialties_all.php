<?php $page_title = 'Tất cả chuyên khoa';
include 'Views/layouts/header.php'; ?>

<section class="page-header bg-gradient-primary text-white" style="margin-top: 81px;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold mb-2"><i class="fas fa-list me-3"></i>Tất cả chuyên khoa</h1>
                <p class="lead mb-0">Chọn chuyên khoa để xem danh sách bác sĩ tương ứng</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="./home" class="btn btn-outline-light btn-lg"><i class="fas fa-home me-2"></i>Trang chủ</a>
            </div>
        </div>
    </div>
</section>

<section class="doctors-section py-5">
    <div class="container">
        <div class="row">
            <?php foreach (($specialties ?? []) as $sp):
                $iconClass = !empty($sp['icon']) ? $sp['icon'] : 'fas fa-stethoscope';
                $count = isset($sp['doctor_count']) ? (int)$sp['doctor_count'] : 0;
                $badgeColor = $count > 0 ? 'success' : 'secondary';
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4 d-flex">
                <a class="text-decoration-none text-reset d-block h-100 w-100"
                    href="./doctors_by_specialty?slug=<?php echo urlencode($sp['slug'] ?? ''); ?>">
                    <div class="specialty-card animate-on-scroll h-100">
                        <div class="specialty-icon"><i class="<?php echo htmlspecialchars($iconClass); ?>"></i></div>
                        <h5><?php echo htmlspecialchars($sp['ten']); ?></h5>
                        <p><?php echo htmlspecialchars($sp['mo_ta'] ?? ''); ?></p>
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
    </div>
</section>

<style>
.specialty-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    border: 1px solid #e9ecef;
    padding: 1.5rem;
    text-align: center;
    transition: all .25s;
    display: flex;
    flex-direction: column;
}

.specialty-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 32px rgba(0, 0, 0, 0.12)
}

.specialty-icon {
    font-size: 2rem;
    color: #0d6efd;
    margin-bottom: .75rem
}

.doctor-count {
    margin-top: auto;
}
</style>

<?php include 'Views/layouts/footer.php'; ?>