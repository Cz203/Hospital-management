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
            </div>
            <button type="button" class="btn btn-add-schedule" data-bs-toggle="modal"
                data-bs-target="#addScheduleModal">
                <i class="fas fa-plus me-2"></i>Thêm ca trực
            </button>
        </div>
    </div>


    <!-- Weekly Navigation -->
    <div class="card mb-0 border-0">
        <div class="card-header d-flex justify-content-between align-items-center border-0 pb-0">
            <div class="d-flex align-items-center flex-wrap" style="gap:10px;">
                <span>
                    <i class="fas fa-calendar-week me-2"></i>
                    Tuần: <strong><?php echo isset($weekLabel) ? $weekLabel : ''; ?></strong>
                </span>
                <input type="date" id="weekDatePicker" class="form-control form-control-sm"
                    style="max-width: 180px; width: 180px;"
                    value="<?php echo htmlspecialchars(isset($_GET['from']) ? $_GET['from'] : (isset($windowStartDate) ? $windowStartDate->format('Y-m-d') : date('Y-m-d'))); ?>">
            </div>
            <div class="d-flex align-items-center">
                <?php $canPrev = isset($allowPrevWeek) ? $allowPrevWeek : false; ?>
                <?php $canNext = isset($allowNextWeek) ? $allowNextWeek : true; ?>
                <?php $fromParam = isset($_GET['from']) ? $_GET['from'] : (isset($windowStartDate) ? $windowStartDate->format('Y-m-d') : date('Y-m-d')); ?>
                <div class="btn-group" role="group" aria-label="Week nav">
                    <a class="btn btn-sm btn-outline-secondary <?php echo $canPrev ? '' : 'disabled'; ?>"
                        href="<?php echo $canPrev ? ('./doctor_schedule_management?from=' . urlencode($fromParam) . '&w=' . (isset($prevWeekOffset) ? $prevWeekOffset : -1)) : '#'; ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <a class="btn btn-sm btn-outline-secondary <?php echo $canNext ? '' : 'disabled'; ?>"
                        href="<?php echo $canNext ? ('./doctor_schedule_management?from=' . urlencode($fromParam) . '&w=' . (isset($nextWeekOffset) ? $nextWeekOffset : 1)) : '#'; ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>

    <div id="messageBar" class="px-2" style="display:none;"></div>

    <!-- Calendar style weekly table -->
    <?php
    $daysOfWeek = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
    $shiftRows = [
        ['label' => 'Sáng', 'key' => 'Ca sáng'],
        ['label' => 'Chiều', 'key' => 'Ca chiều'],
        ['label' => 'Tối', 'key' => 'Ca tối'],
    ];

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
    ?>
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0" style="min-width:1000px;">
            <thead>
                <tr>
                    <th style="width:100px; background:#fff9db;">Ca làm</th>
                    <?php foreach ($daysOfWeek as $day): ?>
                    <?php
                        $dayOffset = $weekDays[$day];
                        $dayDate = clone $currentWeekStart;
                        $dayDate->add(new DateInterval('P' . $dayOffset . 'D'));
                        $dateStr = $dayDate->format('Y-m-d');
                        $inWindow = true;
                        if (isset($windowStartDate) && isset($windowEndDate)) {
                            $inWindow = ($dayDate >= $windowStartDate) && ($dayDate <= $windowEndDate);
                        }
                        ?>
                    <th class="text-primary" style="white-space:nowrap; background:#ffffff;">
                        <?php echo $day; ?><br>
                        <small><?php echo $dayDate->format('d/m/Y'); ?></small>
                        <?php if (!$inWindow): ?><div class="small text-muted">Ngoài 1 tháng</div><?php endif; ?>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shiftRows as $row): ?>
                <tr>
                    <th style="background:#fff9db; width:100px;"><?php echo $row['label']; ?></th>
                    <?php foreach ($daysOfWeek as $day): ?>
                    <?php
                            $dayOffset = $weekDays[$day];
                            $dayDate = clone $currentWeekStart;
                            $dayDate->add(new DateInterval('P' . $dayOffset . 'D'));
                            $currentDateStr = $dayDate->format('Y-m-d');
                            // Kiểm tra phạm vi 1 tháng trước khi hiển thị nội dung
                            $inWindowCell = true;
                            if (isset($windowStartDate) && isset($windowEndDate)) {
                                $inWindowCell = ($dayDate >= $windowStartDate) && ($dayDate <= $windowEndDate);
                            }
                            $cellItems = [];
                            if ($inWindowCell) {
                                $schedulesForDate = $this->doctorModel->getSchedulesByDate($_SESSION['user_id'], $currentDateStr) ?: [];
                                $cellItems = array_values(array_filter($schedulesForDate, function ($s) use ($row) {
                                    return isset($s['loai_ca']) && $s['loai_ca'] === $row['key'];
                                }));
                            }
                            ?>
                    <td style="vertical-align:top; min-width:140px; background:#ffffff;">
                        <?php if (!$inWindowCell): ?>
                        <span class="text-muted small">Ngoài 1 tháng</span>
                        <?php elseif (empty($cellItems)): ?>
                        <span class="text-muted small">—</span>
                        <?php else: ?>
                        <?php foreach ($cellItems as $schedule): ?>
                        <?php
                                        $displayStart = $schedule['gio_bat_dau'];
                                        $displayEnd = $schedule['gio_ket_thuc'];
                                        ?>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span
                                    class="badge bg-secondary me-2"><?php echo date('H:i', strtotime($displayStart)); ?>
                                    - <?php echo date('H:i', strtotime($displayEnd)); ?></span>
                                <?php if (!empty($schedule['ghi_chu'])): ?>
                                <small class="text-muted d-block"><i
                                        class="fas fa-sticky-note me-1"></i><?php echo htmlspecialchars($schedule['ghi_chu']); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item edit-schedule" href="#"
                                            data-schedule-id="<?php echo $schedule['id']; ?>"
                                            data-date="<?php echo $currentDateStr; ?>">
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
                                            <input type="hidden" name="date" value="<?php echo $currentDateStr; ?>">
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
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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

<!-- Cancel Reason Modal -->
<div class="modal fade" id="cancelReasonModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-ban me-2"></i>Hủy ca trực</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Nhập lý do hủy ca cho ngày <strong id="cancelDateLabel"></strong>:</p>
                <textarea class="form-control" id="cancelReasonInput" rows="3"
                    placeholder="Ví dụ: công tác đột xuất, có ca phẫu thuật..." required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-danger" id="confirmCancelBtn">
                    <i class="fas fa-check me-1"></i>Xác nhận hủy
                </button>
            </div>
        </div>
    </div>
</div>

<script src="./assets/js/schedule_management.js"></script>