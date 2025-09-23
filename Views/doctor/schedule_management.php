<?php
// File này sẽ được include từ DoctorController
// Không cần kiểm tra session ở đây vì đã được kiểm tra trong Controller
?>

<!-- Custom CSS -->
<link rel="stylesheet" href="./assets/css/schedule_management.css">

<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-calendar-alt me-3"></i>
                    Đăng ký lịch làm việc
                </h1>

                <small class="text-muted">
                    <i class="fas fa-clock me-1"></i>
                    Cập nhật lần cuối: <span id="current-datetime"></span>
                </small>
                <div class="mt-2">
                    <div class="alert alert-info py-2 px-3 mb-0" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        Vào <strong>Thứ 3</strong> hoặc <strong>Thứ 4</strong>: bác sĩ được <strong>thay đổi/xóa ca
                            trực</strong> và <strong>phải nhập lý do</strong>.
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-add-schedule" data-bs-toggle="modal"
                data-bs-target="#addScheduleModal">
                <i class="fas fa-plus me-2"></i>Thêm ca trực
            </button>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stats-card text-center">
                <div class="stats-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stats-number"><?php echo $stats['total_schedules'] ?? 0; ?></div>
                <div class="stats-label">Tổng ca trực</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stats-card text-center">
                <div class="stats-icon">
                    <i class="fas fa-sun"></i>
                </div>
                <div class="stats-number"><?php echo $stats['morning_shifts'] ?? 0; ?></div>
                <div class="stats-label">Ca sáng</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stats-card text-center">
                <div class="stats-icon">
                    <i class="fas fa-cloud-sun"></i>
                </div>
                <div class="stats-number"><?php echo $stats['afternoon_shifts'] ?? 0; ?></div>
                <div class="stats-label">Ca chiều</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stats-card text-center">
                <div class="stats-icon">
                    <i class="fas fa-moon"></i>
                </div>
                <div class="stats-number"><?php echo $stats['evening_shifts'] ?? 0; ?></div>
                <div class="stats-label">Ca tối</div>
            </div>
        </div>
    </div>

    <!-- Weekly Navigation -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-calendar-week me-2"></i>
                Tuần: <strong><?php echo isset($weekLabel) ? $weekLabel : ''; ?></strong>
                <?php if (isset($windowStartDate) && isset($windowEndDate)): ?>
                <small class="text-muted ms-2">(Khoảng: <?php echo $windowStartDate->format('d/m/Y'); ?> →
                    <?php echo $windowEndDate->format('d/m/Y'); ?>)</small>
                <?php endif; ?>
            </div>
            <div>
                <?php $canPrev = isset($allowPrevWeek) ? $allowPrevWeek : false; ?>
                <?php $canNext = isset($allowNextWeek) ? $allowNextWeek : true; ?>
                <?php $fromParam = isset($_GET['from']) ? $_GET['from'] : (isset($windowStartDate) ? $windowStartDate->format('Y-m-d') : date('Y-m-d')); ?>
                <a class="btn btn-sm btn-outline-secondary me-2 <?php echo $canPrev ? '' : 'disabled'; ?>"
                    href="<?php echo $canPrev ? ('./doctor_schedule_management?from=' . urlencode($fromParam) . '&w=' . (isset($prevWeekOffset) ? $prevWeekOffset : -1)) : '#'; ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <a class="btn btn-sm btn-outline-secondary <?php echo $canNext ? '' : 'disabled'; ?>"
                    href="<?php echo $canNext ? ('./doctor_schedule_management?from=' . urlencode($fromParam) . '&w=' . (isset($nextWeekOffset) ? $nextWeekOffset : 1)) : '#'; ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
        <div class="card-body py-2">
            <div class="d-flex align-items-center" style="gap:10px;">
                <label for="weekDatePicker" class="mb-0"><i class="fas fa-calendar me-2"></i>Chọn ngày </label>
                <?php
                $fromDefault = isset($windowStartDate) ? $windowStartDate->format('Y-m-d') : date('Y-m-d');
                ?>
                <input type="date" id="weekDatePicker" class="form-control" style="max-width: 220px;"
                    value="<?php echo htmlspecialchars(isset($_GET['from']) ? $_GET['from'] : $fromDefault); ?>">

            </div>
        </div>
    </div>

    <!-- Schedule Grid -->
    <div class="row">
        <?php
        $daysOfWeek = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];

        // Lấy ngày đầu tuần từ Controller nếu có
        $currentWeekStart = isset($weekStartDate) ? clone $weekStartDate : (function () {
            $t = new DateTime();
            $t->modify('monday this week');
            return $t;
        })();

        $weekDays = [
            'Thứ 2' => 0,
            'Thứ 3' => 1,
            'Thứ 4' => 2,
            'Thứ 5' => 3,
            'Thứ 6' => 4,
            'Thứ 7' => 5,
            'Chủ nhật' => 6
        ];

        // Map ngoại lệ theo ngày -> schedule_id
        $exceptionsMap = [];
        if (isset($exceptions) && is_array($exceptions)) {
            foreach ($exceptions as $ex) {
                $d = $ex['ngay'];
                $sid = $ex['schedule_id'];
                if (!isset($exceptionsMap[$d])) $exceptionsMap[$d] = [];
                $exceptionsMap[$d][$sid] = $ex;
            }
        }

        foreach ($daysOfWeek as $day):
            $dayOffset = $weekDays[$day];
            $dayDate = clone $currentWeekStart;
            $dayDate->add(new DateInterval('P' . $dayOffset . 'D'));
            $inWindow = true;
            if (isset($windowStartDate) && isset($windowEndDate)) {
                $inWindow = ($dayDate >= $windowStartDate) && ($dayDate <= $windowEndDate);
            }
            $currentDateStr = isset($weekDaysDates[$day]) ? $weekDaysDates[$day] : null;
            $daySchedules = $currentDateStr ? ($this->doctorModel->getSchedulesByDate($_SESSION['user_id'], $currentDateStr)) : [];

        ?>
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-4">
            <div class="card schedule-card">
                <div class="day-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-calendar-day me-2"></i>
                            <div class="day-title"><?php echo $day; ?>
                                <small class="text-white ms-2">
                                    <?php echo isset($weekDaysDates[$day]) ? date('d/m', strtotime($weekDaysDates[$day])) : ''; ?>
                                </small>
                            </div>

                        </div>

                    </div>
                </div>
                <div class="card-body p-3">
                    <?php if (!$inWindow): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p class="mb-0">Ngoài phạm vi 1 tháng</p>
                    </div>
                    <?php elseif (empty($daySchedules)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p class="mb-0">Chưa có ca trực</p>
                    </div>
                    <?php else: ?>
                    <?php $hasVisible = false; ?>
                    <?php foreach ($daySchedules as $schedule): ?>
                    <?php $ex = null; ?>
                    <?php
                                $displayLoaiCa = $schedule['loai_ca'];
                                $displayStart = $schedule['gio_bat_dau'];
                                $displayEnd = $schedule['gio_ket_thuc'];
                                $modifiedToday = false;
                                if ($ex && isset($ex['action']) && $ex['action'] === 'modify') {
                                    $modifiedToday = true;
                                    if (!empty($ex['loai_ca'])) $displayLoaiCa = $ex['loai_ca'];
                                    if (!empty($ex['gio_bat_dau'])) $displayStart = $ex['gio_bat_dau'];
                                    if (!empty($ex['gio_ket_thuc'])) $displayEnd = $ex['gio_ket_thuc'];
                                }
                                $badgeClass = 'shift-' . strtolower(str_replace('Ca ', '', $displayLoaiCa));
                                ?>
                    <?php $hasVisible = true; ?>
                    <div class="schedule-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="shift-badge <?php echo $badgeClass; ?>">
                                    <?php echo $displayLoaiCa; ?>
                                </span>
                                <?php if ($ex && isset($ex['action']) && $ex['action'] === 'modify'): ?>
                                <span class="badge bg-warning text-dark ms-1">Đã chỉnh sửa hôm nay</span>
                                <?php endif; ?>
                                <?php if ($schedule['trang_thai'] === 'inactive'): ?>
                                <span class="badge bg-secondary ms-1">Tạm dừng</span>
                                <?php endif; ?>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item edit-schedule" href="#"
                                            data-schedule-id="<?php echo $schedule['id']; ?>"
                                            data-date="<?php echo isset($weekDaysDates[$day]) ? $weekDaysDates[$day] : ''; ?>">
                                            <i class="fas fa-edit me-2"></i>Chỉnh sửa ngày này
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form method="POST" action="./doctor_cancel_schedule_for_date" class="d-inline">
                                            <input type="hidden" name="schedule_id"
                                                value="<?php echo $schedule['id']; ?>">
                                            <input type="hidden" name="date"
                                                value="<?php echo isset($weekDaysDates[$day]) ? $weekDaysDates[$day] : ''; ?>">
                                            <input type="hidden" name="from"
                                                value="<?php echo isset($_GET['from']) ? htmlspecialchars($_GET['from']) : (isset($windowStartDate) ? $windowStartDate->format('Y-m-d') : date('Y-m-d')); ?>">
                                            <button type="submit" class="dropdown-item text-danger"
                                                onclick="return confirm('Hủy ca trực cho ngày này? Vui lòng chắc chắn!')">
                                                <i class="fas fa-ban me-2"></i>Hủy ca trực (ngày này)
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="time-display">
                            <i class="fas fa-clock me-1"></i>
                            <?php echo date('H:i', strtotime($displayStart)); ?> -
                            <?php echo date('H:i', strtotime($displayEnd)); ?>
                        </div>
                        <?php if (!empty($schedule['ghi_chu'])): ?>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-sticky-note me-1"></i>
                                <?php echo htmlspecialchars($schedule['ghi_chu']); ?>
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php if (!$hasVisible): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p class="mb-0">Chưa có ca trực</p>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Thêm ca trực mới
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="./doctor_add_schedule">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="thu_trong_tuan" class="form-label">Thứ trong tuần <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="thu_trong_tuan" name="thu_trong_tuan" required>
                                <option value="">Chọn thứ</option>
                                <option value="Thứ 2">Thứ 2</option>
                                <option value="Thứ 3">Thứ 3</option>
                                <option value="Thứ 4">Thứ 4</option>
                                <option value="Thứ 5">Thứ 5</option>
                                <option value="Thứ 6">Thứ 6</option>
                                <option value="Thứ 7">Thứ 7</option>
                                <option value="Chủ nhật">Chủ nhật</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="loai_ca" class="form-label">Loại ca <span class="text-danger">*</span></label>
                            <select class="form-select" id="loai_ca" name="loai_ca" required>
                                <option value="">Chọn loại ca</option>
                                <option value="Ca sáng">Ca sáng (6:00 - 12:00)</option>
                                <option value="Ca chiều">Ca chiều (12:00 - 18:00)</option>
                                <option value="Ca tối">Ca tối (18:00 - 23:59)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="gio_bat_dau" class="form-label">Giờ bắt đầu <span
                                    class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="gio_bat_dau" name="gio_bat_dau" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gio_ket_thuc" class="form-label">Giờ kết thúc <span
                                    class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="gio_ket_thuc" name="gio_ket_thuc" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="ghi_chu" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="ghi_chu" name="ghi_chu" rows="3"
                            placeholder="Nhập ghi chú về ca trực (tùy chọn)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Lưu ca trực
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div class="modal fade" id="editScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Chỉnh sửa ca trực
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="./doctor_modify_schedule_for_date">
                <input type="hidden" id="edit_schedule_id" name="schedule_id">
                <input type="hidden" name="from"
                    value="<?php echo isset($_GET['from']) ? htmlspecialchars($_GET['from']) : (isset($windowStartDate) ? $windowStartDate->format('Y-m-d') : date('Y-m-d')); ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày áp dụng</label>
                            <input type="text" class="form-control"
                                value="<?php echo isset($weekLabel) ? $weekLabel : ''; ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_loai_ca" class="form-label">Loại ca <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="edit_loai_ca" name="loai_ca" required>
                                <option value="">Chọn loại ca</option>
                                <option value="Ca sáng">Ca sáng (6:00 - 12:00)</option>
                                <option value="Ca chiều">Ca chiều (12:00 - 18:00)</option>
                                <option value="Ca tối">Ca tối (18:00 - 23:59)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_gio_bat_dau" class="form-label">Giờ bắt đầu <span
                                    class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="edit_gio_bat_dau" name="gio_bat_dau" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_gio_ket_thuc" class="form-label">Giờ kết thúc <span
                                    class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="edit_gio_ket_thuc" name="gio_ket_thuc" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_trang_thai" class="form-label">Trạng thái</label>
                            <select class="form-select" id="edit_trang_thai" name="trang_thai">
                                <option value="active">Hoạt động</option>
                                <option value="inactive">Tạm dừng</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_ghi_chu" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="edit_ghi_chu" name="ghi_chu" rows="3"
                            placeholder="Nhập ghi chú về ca trực (tùy chọn)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Cập nhật ca trực
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="./assets/js/schedule_management.js"></script>