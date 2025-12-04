<?php
// Quản lý lịch làm việc lễ tân cho Admin – giao diện tương tự quản lý lịch bác sĩ
?>

<!-- Custom CSS dùng chung với lịch bác sĩ -->
<link rel="stylesheet" href="./assets/css/admin_schedules.css">

<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-user-clock me-3"></i>
                    Quản lý lịch làm việc lễ tân
                </h1>
            </div>
            <div class="header-actions">
                <button class="btn btn-success me-2" onclick="showAddReceptionScheduleModalWithSelect()">
                    <i class="fas fa-plus me-2"></i>Thêm lịch mới
                </button>
                <button class="btn btn-outline-primary" onclick="refreshReceptionData()">
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
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stats-number"><?php echo $scheduleStats['active_schedules']; ?></div>
                <div class="stats-label">Ca đang hoạt động</div>
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
    </div>

    <!-- Reception Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2"></i>Lọc theo lễ tân
                        </h5>
                        <small class="text-muted" id="receptionFilterStatus">Hiển thị tất cả lễ tân</small>
                    </div>
                    <div class="filter-actions">
                        <select class="form-select" id="receptionFilter" onchange="filterByReception()">
                            <option value="">Tất cả lễ tân</option>
                            <?php foreach ($receptions as $rec): ?>
                            <option value="<?php echo (int)$rec['id']; ?>">
                                <?php echo htmlspecialchars($rec['ten'] ?? ''); ?> -
                                <?php echo htmlspecialchars($rec['email'] ?? ''); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Schedule (cards giống lịch bác sĩ) -->
    <div class="row">
        <?php
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
                        <span class="day-badge"><?php echo count($schedulesByDay[$day]); ?> lễ tân</span>
                    </div>
                </div>
                <div class="day-body">
                    <?php if (empty($schedulesByDay[$day])): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p class="mb-0">Không có lễ tân trực</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($schedulesByDay[$day] as $recData): ?>
                    <div class="doctor-schedule reception-schedule"
                        data-reception-id="<?php echo (int)$recData['reception']['id']; ?>">
                        <div class="doctor-info">
                            <div class="doctor-avatar">
                                <?php echo strtoupper(substr($recData['reception']['ten'] ?? 'L', 0, 1)); ?>
                            </div>
                            <div class="doctor-details">
                                <h6 class="doctor-name">
                                    <?php echo htmlspecialchars($recData['reception']['ten'] ?? ''); ?>
                                </h6>
                                <small class="doctor-specialty">
                                    <?php
                                                // Hiển thị trạng thái truy cập
                                                $status = $recData['reception']['trang_thai_truy_cap'] ?? 'active';
                                                $statusClass = '';
                                                $statusText = '';

                                                switch ($status) {
                                                    case 'active':
                                                        $statusClass = 'success';
                                                        $statusText = 'Hoạt động';
                                                        break;
                                                    case 'inactive':
                                                        $statusClass = 'secondary';
                                                        $statusText = 'Không hoạt động';
                                                        break;
                                                    case 'blocked':
                                                        $statusClass = 'danger';
                                                        $statusText = 'Bị chặn';
                                                        break;
                                                    case 'suspended':
                                                        $statusClass = 'warning';
                                                        $statusText = 'Tạm khóa';
                                                        break;
                                                    default:
                                                        $statusClass = 'success';
                                                        $statusText = 'Hoạt động';
                                                }
                                                ?>
                                    <span
                                        class="badge bg-<?php echo $statusClass; ?> badge-sm"><?php echo $statusText; ?></span>
                                </small>
                            </div>
                            <button class="btn btn-sm btn-success"
                                onclick="showAddReceptionScheduleModal(<?php echo (int)$recData['reception']['id']; ?>, '<?php echo $day; ?>')">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div class="schedule-list">
                            <?php foreach ($recData['schedules'] as $schedule): ?>
                            <div class="schedule-item">
                                <div class="d-flex justify-content-between align-items-start w-100">
                                    <div class="flex-grow-1">
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
                                            <?php echo htmlspecialchars($schedule['ghi_chu'] ?? ''); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="schedule-actions">
                                        <button class="btn btn-sm btn-outline-primary me-1"
                                            onclick="showEditReceptionScheduleModal(<?php echo (int)$schedule['id']; ?>, <?php echo (int)$recData['reception']['id']; ?>)"
                                            title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"
                                            onclick="deleteReceptionSchedule(<?php echo (int)$schedule['id']; ?>, <?php echo (int)$recData['reception']['id']; ?>)"
                                            title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
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

<!-- Modal Thêm/Sửa Lịch Làm Việc Lễ Tân -->
<div class="modal fade" id="receptionScheduleModal" tabindex="-1" aria-labelledby="receptionScheduleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receptionScheduleModalLabel">Thêm lịch làm việc lễ tân</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="receptionScheduleForm">
                    <input type="hidden" id="receptionScheduleId" name="schedule_id">
                    <input type="hidden" id="receptionIdHidden" name="reception_id_hidden">

                    <div class="mb-3" id="receptionSelectGroup">
                        <label for="receptionSelect" class="form-label">Chọn lễ tân <span
                                class="text-danger">*</span></label>
                        <select class="form-select" id="receptionSelect" required>
                            <option value="">-- Chọn lễ tân --</option>
                            <?php foreach ($receptions as $rec): ?>
                            <option value="<?php echo (int)$rec['id']; ?>">
                                <?php echo htmlspecialchars($rec['ten'] ?? ''); ?> -
                                <?php echo htmlspecialchars($rec['email'] ?? ''); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="receptionThuTrongTuan" class="form-label">Thứ trong tuần <span
                                class="text-danger">*</span></label>
                        <select class="form-select" id="receptionThuTrongTuan" name="thu_trong_tuan" required>
                            <option value="">-- Chọn thứ --</option>
                            <option value="Thứ 2">Thứ 2</option>
                            <option value="Thứ 3">Thứ 3</option>
                            <option value="Thứ 4">Thứ 4</option>
                            <option value="Thứ 5">Thứ 5</option>
                            <option value="Thứ 6">Thứ 6</option>
                            <option value="Thứ 7">Thứ 7</option>
                            <option value="Chủ nhật">Chủ nhật</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="receptionLoaiCa" class="form-label">Loại ca <span
                                class="text-danger">*</span></label>
                        <select class="form-select" id="receptionLoaiCa" name="loai_ca" required>
                            <option value="">-- Chọn ca --</option>
                            <option value="Ca sáng">Ca sáng (07:00 - 11:30)</option>
                            <option value="Ca chiều">Ca chiều (13:00 - 21:00)</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="receptionGioBatDau" class="form-label">Giờ bắt đầu <span
                                    class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="receptionGioBatDau" name="gio_bat_dau" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="receptionGioKetThuc" class="form-label">Giờ kết thúc <span
                                    class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="receptionGioKetThuc" name="gio_ket_thuc"
                                required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="receptionGhiChu" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="receptionGhiChu" name="ghi_chu" rows="3"
                            placeholder="Nhập ghi chú về ca trực (tùy chọn)"></textarea>
                    </div>

                    <div class="mb-3" id="receptionTrangThaiGroup" style="display: none;">
                        <label for="receptionTrangThai" class="form-label">Trạng thái</label>
                        <select class="form-select" id="receptionTrangThai" name="trang_thai">
                            <option value="active">Hoạt động</option>
                            <option value="inactive">Không hoạt động</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="saveReceptionSchedule()">
                    <i class="fas fa-save me-2"></i>Lưu lại
                </button>
            </div>
        </div>
    </div>
</div>

<script src="./assets/js/admin_reception_schedules.js"></script>