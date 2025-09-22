<?php $page_title = 'Bác sĩ - ' . ($specialty['ten'] ?? 'Chuyên khoa');
include 'Views/layouts/header.php'; ?>

<section class="page-header bg-gradient-primary text-white" style="margin-top: 81px;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold mb-2"><i
                        class="fas fa-stethoscope me-3"></i><?php echo htmlspecialchars($specialty['ten']); ?></h1>
                <?php if (!empty($specialty['mo_ta'])): ?><p class="lead mb-0">
                    <?php echo htmlspecialchars($specialty['mo_ta']); ?></p><?php endif; ?>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="./specialties_all" class="btn btn-outline-light btn-lg"><i class="fas fa-list me-2"></i>Tất cả
                    chuyên khoa</a>
            </div>
        </div>
    </div>
</section>

<section class="doctors-section py-5">
    <div class="container">
        <div class="row" id="doctorsContainer">
            <?php foreach ($doctors as $doc): ?>
            <?php
                // Chuẩn hóa đường dẫn ảnh và để trình duyệt tự fallback qua onerror
                $imgSrc = trim($doc['hinh_anh'] ?? '');
                $imgSrc = str_replace('\\', '/', $imgSrc);
                if ($imgSrc !== '') {
                    if (preg_match('#^https?://#i', $imgSrc)) {
                        // giữ nguyên URL tuyệt đối
                    } elseif (strpos($imgSrc, 'uploads/') === 0) {
                        $imgSrc = './' . $imgSrc; // ./uploads/...
                    } else {
                        $imgSrc = './uploads/' . ltrim($imgSrc, '/');
                    }
                } else {
                    $imgSrc = './assets/img/default-doctor.jpg';
                }

                $experience = (int)($doc['so_nam_kinh_nghiem'] ?? 0);
                $specialty = $doc['chuyen_khoa'] ?: 'Đa khoa';
                ?>
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3 doctor-item animate-on-scroll"
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
                    </div>

                    <div class="doctor-body">
                        <h5 class="doctor-name"><?php echo htmlspecialchars($doc['ten']); ?></h5>
                        <p class="doctor-specialty">
                            <i class="fas fa-stethoscope me-2"></i>
                            <?php echo htmlspecialchars($specialty); ?>
                        </p>

                        <div class="doctor-meta text-muted">
                            <span><?php echo $experience; ?> năm kinh nghiệm</span>
                        </div>

                        <div class="doctor-actions">
                            <a href="./hospital_appointment?doctor_id=<?php echo base64_encode($doc['id']); ?>"
                                class="btn btn-primary btn-sm w-100 mb-2">
                                <i class="fas fa-calendar-plus me-2"></i>Đặt lịch
                            </a>
                            <a href="./consultation_booking?doctor_id=<?php echo base64_encode($doc['id']); ?>"
                                class="btn btn-outline-primary btn-sm w-100">
                                <i class="fas fa-video me-2"></i>Tư vấn
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
.doctor-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06);
    border: 1px solid #e9ecef;
    transition: all .2s ease
}

.doctor-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12)
}

.doctor-header {
    padding: 1rem 1rem .75rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    text-align: center
}

.doctor-avatar {
    width: 84px;
    height: 84px;
    margin: 0 auto .75rem;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #fff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08)
}

.doctor-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover
}

.doctor-body {
    padding: 1rem 1rem 1.25rem
}

.doctor-name {
    font-size: 1.05rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: .3rem;
    text-align: center
}

.doctor-specialty {
    color: #0d6efd;
    font-weight: 600;
    text-align: center;
    margin-bottom: .75rem
}

.doctor-stats .stat-item {
    display: flex;
    align-items: center;
    margin-bottom: .25rem;
    font-size: .9rem;
    color: #6c757d
}

.doctor-stats .stat-item i {
    width: 20px;
    margin-right: .75rem;
    font-size: 1rem
}

.doctor-actions {
    border-top: 1px solid #e9ecef;
    padding-top: .75rem
}
</style>

<?php include 'Views/layouts/footer.php'; ?>