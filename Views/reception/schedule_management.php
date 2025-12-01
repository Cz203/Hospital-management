<?php
// Lịch làm việc Lễ tân – sử dụng layout & style tương tự bác sĩ
?>

<link rel="stylesheet" href="./assets/css/schedule_management.css">

<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-calendar-alt me-3"></i>
                    Lịch làm việc lễ tân
                </h1>
            </div>
            <button type="button" class="btn btn-add-schedule" data-bs-toggle="modal"
                data-bs-target="#addReceptionScheduleModal">
                <i class="fas fa-plus me-2"></i>Thêm ca trực
            </button>
        </div>
    </div>

    <?php
    $daysOfWeek = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
    $shiftRows = [
        ['label' => 'Sáng', 'key' => 'Ca sáng'],
        ['label' => 'Chiều', 'key' => 'Ca chiều'],
    ];
    ?>

    <div class="card mb-0 border-0">
        <div class="card-header d-flex justify-content-between align-items-center border-0 pb-0">
            <div class="d-flex align-items-center flex-wrap" style="gap:10px;">
                <span>
                    <i class="fas fa-user-clock me-2"></i>
                    Lễ tân: <strong><?php echo htmlspecialchars($receptionName ?? ''); ?></strong>
                </span>
            </div>
        </div>
    </div>

    <div id="messageBar" class="px-2" style="display:none;"></div>

    <div class="table-responsive mt-3">
        <table class="table table-bordered align-middle mb-0" style="min-width:900px;">
            <thead>
                <tr>
                    <th style="width:100px; background:#fff9db;">Ca làm</th>
                    <?php foreach ($daysOfWeek as $day): ?>
                        <th class="text-primary" style="white-space:nowrap; background:#ffffff;">
                            <?php echo $day; ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shiftRows as $row): ?>
                    <tr>
                        <th style="background:#fff9db; width:100px;"><?php echo $row['label']; ?></th>
                        <?php foreach ($daysOfWeek as $day): ?>
                            <?php $cellSchedules = $groupedSchedules[$day][$row['key']] ?? []; ?>
                            <td>
                                <?php if (empty($cellSchedules)): ?>
                                    <div class="text-muted small">Không có ca</div>
                                <?php else: ?>
                                    <?php foreach ($cellSchedules as $sc): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <div>
                                                <div class="fw-semibold">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php echo htmlspecialchars(substr($sc['gio_bat_dau'], 0, 5)); ?>
                                                    -
                                                    <?php echo htmlspecialchars(substr($sc['gio_ket_thuc'], 0, 5)); ?>
                                                </div>
                                                <?php if (!empty($sc['ghi_chu'])): ?>
                                                    <div class="text-muted small">
                                                        <i class="fas fa-sticky-note me-1"></i>
                                                        <?php echo htmlspecialchars($sc['ghi_chu']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <form method="POST" action="./reception_delete_schedule"
                                                onsubmit="return confirm('Xóa ca trực này?');">
                                                <input type="hidden" name="schedule_id" value="<?php echo (int)$sc['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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

<!-- Modal thêm ca trực lễ tân -->
<div class="modal fade" id="addReceptionScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Thêm ca trực lễ tân
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="./reception_add_schedule">
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
                                <option value="Ca sáng">Ca sáng (07:00 - 11:30)</option>
                                <option value="Ca chiều">Ca chiều (13:00 - 21:00)</option>
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

<script>
    // Tự động fill giờ giống bác sĩ
    document.addEventListener("DOMContentLoaded", function() {
        const loaiCa = document.getElementById("loai_ca");
        const gioBatDau = document.getElementById("gio_bat_dau");
        const gioKetThuc = document.getElementById("gio_ket_thuc");
        if (loaiCa && gioBatDau && gioKetThuc) {
            loaiCa.addEventListener("change", function() {
                switch (this.value) {
                    case "Ca sáng":
                        gioBatDau.value = "07:00";
                        gioKetThuc.value = "11:30";
                        break;
                    case "Ca chiều":
                        gioBatDau.value = "13:00";
                        gioKetThuc.value = "21:00";
                        break;
                }
            });
        }
    });
</script>