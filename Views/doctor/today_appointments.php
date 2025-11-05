<?php
// Doctor Today Appointments View
?>

<link rel="stylesheet" href="./assets/css/doctor_today_appointments.css">

<div class="container-fluid">
    <!-- Header with Date Info -->
    <div class="page-header mb-4">
        <!-- Top Section -->
        <div class="header-top">
            <div class="doctor-info-section">
                <div class="doctor-avatar">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="doctor-details">
                    <h1 class="page-title mb-1">
                        <i class="fas fa-calendar-day me-2"></i>
                        Lịch hẹn hôm nay
                    </h1>
                    <p class="page-subtitle mb-0">
                        <i class="fas fa-stethoscope me-2"></i>
                        <strong><?php echo htmlspecialchars($doctor['ten'] ?? 'N/A'); ?></strong>
                        <span class="divider">•</span>
                        <span
                            class="specialty"><?php echo htmlspecialchars($doctor['chuyen_khoa'] ?? 'Chuyên khoa'); ?></span>
                    </p>
                </div>
            </div>
            <div class="header-actions">
                <input type="date" class="form-control date-picker" id="dateSelector"
                    value="<?php echo $selectedDate; ?>" onchange="changeDate(this.value)">
                <button class="btn btn-refresh" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>

        <!-- Date Info Banner -->
        <div class="date-info-banner">
            <div class="date-icon-wrapper">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="date-content">
                <div class="date-label">
                    <?php
                    if ($selectedDate === date('Y-m-d')) {
                        echo 'Hôm nay';
                    } else {
                        echo 'Ngày đã chọn';
                    }
                    ?>
                </div>
                <div class="date-value"><?php echo date('d/m/Y', strtotime($selectedDate)); ?></div>
                <div class="date-day"><?php
                                        $days = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'];
                                        echo $days[date('w', strtotime($selectedDate))];
                                        ?></div>
            </div>
            <div class="appointments-count">
                <div class="count-number"><?php echo count($appointments); ?></div>
                <div class="count-label">Lịch hẹn</div>
            </div>
        </div>
    </div>

    <!-- Appointments Timeline -->
    <div class="appointments-timeline">
        <h5 class="timeline-title mb-4">
            <i class="fas fa-list-ul me-2"></i>
            Danh sách lịch hẹn (<?php echo count($appointments); ?> lịch)
        </h5>

        <?php if (empty($appointments)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times fa-4x mb-3"></i>
                <h5>Không có lịch hẹn nào</h5>
                <p class="text-muted">Bạn không có lịch hẹn nào trong ngày này</p>
            </div>
        <?php else: ?>
            <div class="timeline">
                <?php foreach ($appointments as $index => $appointment): ?>
                    <?php
                    $statusClass = match ($appointment['trang_thai']) {
                        'Chờ xác nhận' => 'warning',
                        'Đã xác nhận' => 'success',
                        'Đang khám' => 'info',
                        'Hoàn thành' => 'secondary',
                        'Đã hủy' => 'danger',
                        'hủy' => 'danger',
                        default => 'secondary'
                    };

                    $typeClass = match ($appointment['loai_lich']) {
                        'Trực tiếp' => 'primary',
                        'Tư vấn' => 'info',
                        'Tại nhà' => 'warning',
                        default => 'secondary'
                    };
                    ?>

                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="appointment-card">
                                <div class="appointment-header">
                                    <div class="appointment-time">
                                        <i class="fas fa-clock text-success"></i>
                                        <span
                                            class="time-text"><?php echo date('H:i', strtotime($appointment['gio_hen'])); ?></span>
                                    </div>
                                    <div class="appointment-badges">
                                        <span class="badge bg-<?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($appointment['trang_thai']); ?>
                                        </span>
                                        <span class="badge bg-<?php echo $typeClass; ?>">
                                            <?php echo htmlspecialchars($appointment['loai_lich']); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="appointment-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="info-group">
                                                <i class="fas fa-user-injured text-primary"></i>
                                                <div class="info-content">
                                                    <label>Bệnh nhân</label>
                                                    <strong><?php echo htmlspecialchars($appointment['ten_benh_nhan']); ?></strong>
                                                    <small class="d-block">
                                                        <i class="fas fa-phone text-success"></i>
                                                        <?php echo htmlspecialchars($appointment['so_dien_thoai']); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="info-group">
                                                <i class="fas fa-venus-mars text-info"></i>
                                                <div class="info-content">
                                                    <label>Thông tin</label>
                                                    <strong>
                                                        <?php echo htmlspecialchars($appointment['gioi_tinh'] ?? 'N/A'); ?> -
                                                        <?php
                                                        if (!empty($appointment['ngay_sinh'])) {
                                                            $age = date('Y') - date('Y', strtotime($appointment['ngay_sinh']));
                                                            echo $age . ' tuổi';
                                                        } else {
                                                            echo 'N/A';
                                                        }
                                                        ?>
                                                    </strong>
                                                    <?php if (!empty($appointment['dia_chi'])): ?>
                                                        <small class="d-block">
                                                            <i class="fas fa-map-marker-alt text-danger"></i>
                                                            <?php echo htmlspecialchars(substr($appointment['dia_chi'], 0, 30)); ?>...
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if (!empty($appointment['ly_do'])): ?>
                                            <div class="col-12">
                                                <div class="info-group-full">
                                                    <i class="fas fa-notes-medical text-warning"></i>
                                                    <div class="info-content">
                                                        <label>Lý do khám</label>
                                                        <p class="mb-0"><?php echo htmlspecialchars($appointment['ly_do']); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($appointment['ghi_chu'])): ?>
                                            <div class="col-12">
                                                <div class="alert alert-info mb-0">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    <small><?php echo htmlspecialchars($appointment['ghi_chu']); ?></small>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="appointment-actions">
                                    <?php if ($appointment['trang_thai'] === 'Chờ xác nhận'): ?>
                                        <button class="btn btn-sm btn-success"
                                            onclick="confirmAppointment(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-check me-1"></i>Xác nhận
                                        </button>
                                        <button class="btn btn-sm btn-danger"
                                            onclick="cancelAppointment(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-times me-1"></i>Từ chối
                                        </button>
                                    <?php elseif ($appointment['trang_thai'] === 'Đã xác nhận'): ?>
                                        <button class="btn btn-sm btn-primary"
                                            onclick="startExamination(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-stethoscope me-1"></i>Bắt đầu khám
                                        </button>
                                        <button class="btn btn-sm btn-warning"
                                            onclick="viewDetails(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-eye me-1"></i>Chi tiết
                                        </button>
                                    <?php elseif ($appointment['trang_thai'] === 'Đang khám'): ?>
                                        <button class="btn btn-sm btn-info"
                                            onclick="continueExamination(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-notes-medical me-1"></i>Tiếp tục khám
                                        </button>
                                    <?php elseif ($appointment['trang_thai'] === 'Hoàn thành'): ?>
                                        <button class="btn btn-sm btn-secondary"
                                            onclick="viewDetails(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-eye me-1"></i>Xem chi tiết
                                        </button>
                                    <?php elseif ($appointment['trang_thai'] === 'Đã hủy' || $appointment['trang_thai'] === 'hủy'): ?>
                                        <button class="btn btn-sm btn-outline-danger"
                                            onclick="viewDetails(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-eye me-1"></i>Xem chi tiết
                                        </button>
                                        <?php if (!empty($appointment['ghi_chu'])): ?>
                                            <div class="cancelled-reason mt-2">
                                                <small class="text-danger">
                                                    <i class="fas fa-exclamation-circle me-1"></i>
                                                    <strong>Lý do hủy:</strong> <?php echo htmlspecialchars($appointment['ghi_chu']); ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="./assets/js/doctor_today_appointments.js"></script>