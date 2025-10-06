<?php
// expects $doctors from controller
$daysOfWeek = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h3 class="mb-3">Lịch làm việc bác sĩ</h3>
            <div class="card p-3 shadow-sm">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Chọn bác sĩ</label>
                        <select id="ds-doctor" class="form-select">
                            <option value="">-- Chọn --</option>
                            <?php foreach (($doctors ?? []) as $d): ?>
                            <option value="<?php echo (int)$d['id']; ?>">
                                <?php echo htmlspecialchars(($d['ten'] ?? 'BS') . (isset($d['chuyen_khoa']) && $d['chuyen_khoa'] ? ' - ' . $d['chuyen_khoa'] : ''), ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ngày</label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" type="button" onclick="stepDate(-1)"><i
                                    class="fas fa-chevron-left"></i></button>
                            <input type="date" id="ds-date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            <button class="btn btn-outline-secondary" type="button" onclick="stepDate(1)"><i
                                    class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" onclick="loadSchedules()">
                            <i class="fas fa-sync me-1"></i> Tải lịch
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Kết quả</h6>
                    <div id="schedule-table" class="table-responsive">Chưa có dữ liệu</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loadSchedules() {
    var doctorId = document.getElementById('ds-doctor').value;
    var date = document.getElementById('ds-date').value;
    if (!doctorId) {
        document.getElementById('schedule-table').innerHTML = '<span class="text-danger">Vui lòng chọn bác sĩ</span>';
        return;
    }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', './reception_get_doctor_schedules', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp && resp.success) {
                    renderSchedules(resp.data || []);
                } else {
                    document.getElementById('schedule-table').innerHTML = '<span class="text-warning">' + (resp
                        .message || 'Không có dữ liệu') + '</span>';
                }
            } catch (e) {
                document.getElementById('schedule-table').innerHTML =
                    '<span class="text-danger">Lỗi tải dữ liệu</span>';
            }
        }
    };
    xhr.send('doctor_id=' + encodeURIComponent(doctorId) + '&date=' + encodeURIComponent(date));
}

function renderSchedules(rows) {
    if (!rows.length) {
        document.getElementById('schedule-table').innerHTML = '<span class="text-muted">Không có lịch</span>';
        return;
    }
    var html = '<table class="table table-striped table-sm">' +
        '<thead>' +
        '<tr>' +
        '<th>Thứ</th>' +
        '<th>Giờ bắt đầu</th>' +
        '<th>Giờ kết thúc</th>' +
        '<th>Loại ca</th>' +
        '<th>Ghi chú</th>' +
        '<th>Trạng thái</th>' +
        '</tr>' +
        '</thead><tbody>';
    rows.forEach(function(r) {
        html += '<tr>' +
            '<td>' + (r.thu_trong_tuan || '') + '</td>' +
            '<td>' + (r.gio_bat_dau || '') + '</td>' +
            '<td>' + (r.gio_ket_thuc || '') + '</td>' +
            '<td>' + (r.loai_ca || '') + '</td>' +
            '<td>' + (r.ghi_chu || '') + '</td>' +
            '<td>' + (r.trang_thai || '') + '</td>' +
            '</tr>';
    });
    html += '</tbody></table>';
    document.getElementById('schedule-table').innerHTML = html;
}

function stepDate(delta) {
    var el = document.getElementById('ds-date');
    var v = el.value || '<?php echo date('Y-m-d'); ?>';
    try {
        var d = new Date(v);
        if (isNaN(d.getTime())) d = new Date();
        d.setDate(d.getDate() + delta);
        var yyyy = d.getFullYear();
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        var dd = String(d.getDate()).padStart(2, '0');
        el.value = yyyy + '-' + mm + '-' + dd;
        loadSchedules();
    } catch (e) {
        loadSchedules();
    }
}
</script>