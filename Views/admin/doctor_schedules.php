<?php
// File này sẽ được include từ AdminController
// Không cần kiểm tra session ở đây vì đã được kiểm tra trong Controller
?>

<!-- Custom CSS -->
<link rel="stylesheet" href="./assets/css/admin_schedules.css">
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-calendar-check me-3"></i>
                    Quản lý lịch làm việc bác sĩ
                </h1>

            </div>
            <div class="header-actions">
                <button class="btn btn-outline-primary" onclick="refreshData()">
                    <i class="fas fa-sync-alt me-2"></i>Làm mới
                </button>
            </div>
        </div>
    </div>
    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stats-card text-center">
                <div class="stats-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stats-number"><?php echo $scheduleStats['total_schedules']; ?></div>
                <div class="stats-label">Tổng ca trực</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stats-card text-center">
                <div class="stats-icon">
                    <i class="fas fa-sun"></i>
                </div>
                <div class="stats-number"><?php echo $scheduleStats['morning_shifts']; ?></div>
                <div class="stats-label">Ca sáng</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stats-card text-center">
                <div class="stats-icon">
                    <i class="fas fa-cloud-sun"></i>
                </div>
                <div class="stats-number">
                    <?php echo $scheduleStats['afternoon_shifts']; ?></div>
                <div class="stats-label">Ca chiều</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stats-card text-center">
                <div class="stats-icon">
                    <i class="fas fa-moon"></i>
                </div>
                <div class="stats-number">
                    <?php echo $scheduleStats['evening_shifts']; ?></div>
                <div class="stats-label">Ca tối</div>
            </div>
        </div>
    </div>

    <!-- Doctor Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2"></i>Lọc theo bác sĩ
                        </h5>
                        <small class="text-muted" id="filterStatus">Hiển thị tất cả bác sĩ</small>
                    </div>
                    <div class="filter-actions">
                        <select class="form-select" id="doctorFilter" onchange="filterByDoctor()">
                            <option value="">Tất cả bác sĩ</option>
                            <?php foreach ($doctors as $doctor): ?>
                                <option value="<?php echo $doctor['id']; ?>">
                                    <?php echo htmlspecialchars($doctor['ten']); ?> -
                                    <?php echo htmlspecialchars($doctor['chuyen_khoa']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Schedule -->
    <div class="row">
        <?php
        // Tính toán ngày trong tuần hiện tại
        $today = new DateTime();
        $currentWeekStart = clone $today;
        $currentWeekStart->modify('monday this week');

        $weekDays = [
            'Thứ 2' => 0,
            'Thứ 3' => 1,
            'Thứ 4' => 2,
            'Thứ 5' => 3,
            'Thứ 6' => 4,
            'Thứ 7' => 5,
            'Chủ nhật' => 6
        ];

        foreach ($daysOfWeek as $day):
            $dayOffset = $weekDays[$day];
            $dayDate = clone $currentWeekStart;
            $dayDate->add(new DateInterval('P' . $dayOffset . 'D'));
            $formattedDate = $dayDate->format('d/m/Y');
        ?>
            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-4">
                <div class="day-card">
                    <div class="day-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-calendar-day me-2"></i>
                                <div class="day-title"><?php echo $day; ?></div>
                                <div class="day-date"><?php echo $formattedDate; ?></div>
                            </div>
                            <span class="day-badge"><?php echo count($schedulesByDay[$day]); ?> bác sĩ</span>
                        </div>
                    </div>
                    <div class="day-body">
                        <?php if (empty($schedulesByDay[$day])): ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <p class="mb-0">Không có bác sĩ trực</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($schedulesByDay[$day] as $doctorData): ?>
                                <div class="doctor-schedule" data-doctor-id="<?php echo $doctorData['doctor']['id']; ?>">
                                    <div class="doctor-info">
                                        <div class="doctor-avatar">
                                            <?php if (!empty($doctorData['doctor']['hinh_anh']) && file_exists($doctorData['doctor']['hinh_anh'])): ?>
                                                <img src="<?php echo htmlspecialchars($doctorData['doctor']['hinh_anh']); ?>"
                                                    alt="<?php echo htmlspecialchars($doctorData['doctor']['ten']); ?>"
                                                    class="avatar-image">
                                            <?php else: ?>
                                                <?php echo strtoupper(substr($doctorData['doctor']['ten'], 0, 1)); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="doctor-details">
                                            <h6 class="doctor-name"><?php echo htmlspecialchars($doctorData['doctor']['ten']); ?>
                                            </h6>
                                            <small
                                                class="doctor-specialty"><?php echo htmlspecialchars($doctorData['doctor']['chuyen_khoa']); ?></small>
                                        </div>
                                    </div>
                                    <div class="schedule-list">
                                        <?php foreach ($doctorData['schedules'] as $schedule): ?>
                                            <div class="schedule-item">
                                                <div class="schedule-time">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php echo date('H:i', strtotime($schedule['gio_bat_dau'])); ?> -
                                                    <?php echo date('H:i', strtotime($schedule['gio_ket_thuc'])); ?>
                                                </div>
                                                <div class="schedule-type">
                                                    <span
                                                        class="shift-badge shift-<?php echo strtolower(str_replace('Ca ', '', $schedule['loai_ca'])); ?>">
                                                        <?php echo $schedule['loai_ca']; ?>
                                                    </span>
                                                </div>
                                                <?php if (!empty($schedule['ghi_chu'])): ?>
                                                    <div class="schedule-note">
                                                        <i class="fas fa-sticky-note me-1"></i>
                                                        <?php echo htmlspecialchars($schedule['ghi_chu']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="./assets/js/doctor_schedules.js"></script>