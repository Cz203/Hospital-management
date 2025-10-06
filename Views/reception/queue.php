<?php
require_once 'Views/layouts/layout_helper.php';
require_once 'Models/Specialty.php';

$page_title = 'Bốc số - Lễ tân';
$specModel = new Specialty();
$specialties = $specModel->all();

ob_start();
?>

<div class="container-fluid">
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>Phát phiếu bốc số</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Bệnh nhân (ID)</label>
                        <input type="number" id="q-patient" class="form-control" placeholder="Nhập ID bệnh nhân">
                        <div class="form-text">Bạn có thể tra cứu bệnh nhân ở trang Dashboard trước.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Chuyên khoa</label>
                        <select id="q-specialty" class="form-select">
                            <option value="">-- Chọn chuyên khoa --</option>
                            <?php foreach (($specialties ?? []) as $s): ?>
                            <option value="<?php echo (int)$s['id']; ?>">
                                <?php echo htmlspecialchars($s['ten'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Ưu tiên</label>
                            <select id="q-priority" class="form-select">
                                <option value="0">Thường</option>
                                <option value="1">Ưu tiên</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Quầy</label>
                            <input type="text" id="q-counter" class="form-control" placeholder="VD: A1">
                        </div>
                    </div>
                </div>
                <div class="card-footer d-grid gap-2">
                    <button class="btn btn-primary" onclick="issueTicket()">
                        <i class="fas fa-plus-circle me-1"></i> Phát phiếu
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex align-items-center justify-content-between">
                    <h6 class="mb-0"><i class="fas fa-stream me-2"></i>Hàng đợi hôm nay</h6>
                    <div class="d-flex gap-2 align-items-center">
                        <select id="f-doctor" class="form-select form-select-sm" style="min-width: 220px">
                            <option value="">-- Chọn bác sĩ --</option>
                        </select>
                        <input type="date" id="f-date" class="form-control form-control-sm"
                            value="<?php echo date('Y-m-d'); ?>">
                        <select id="f-status" class="form-select form-select-sm" style="width: 160px">
                            <option value="">Tất cả</option>
                            <option value="dang_goi">Đang gọi</option>
                            <option value="huy">Hủy</option>
                        </select>
                        <button class="btn btn-light btn-sm" onclick="loadQueue()"><i class="fas fa-sync"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="queue-table" class="table-responsive">Chưa có dữ liệu</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Load doctors on specialty change by calling existing endpoint get_doctors_by_specialty
document.getElementById('q-specialty').addEventListener('change', function() {
    var sid = this.value;
    if (!sid) return;
    var xhr = new XMLHttpRequest();
    xhr.open('GET', './get_doctors_by_specialty?specialty_id=' + encodeURIComponent(sid), true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var resp = JSON.parse(xhr.responseText);
                var sel = document.getElementById('f-doctor');
                sel.innerHTML = '<option value="">-- Chọn bác sĩ --</option>';
                (resp.data || []).forEach(function(d) {
                    var opt = document.createElement('option');
                    opt.value = d.id;
                    opt.textContent = d.ten + (d.chuyen_khoa ? (' - ' + d.chuyen_khoa) : '');
                    sel.appendChild(opt);
                });
            } catch (e) {}
        }
    };
    xhr.send();
});

function issueTicket() {
    var patientId = parseInt(document.getElementById('q-patient').value || '0', 10);
    var specialtyId = parseInt(document.getElementById('q-specialty').value || '0', 10);
    var priority = parseInt(document.getElementById('q-priority').value || '0', 10);
    var counter = (document.getElementById('q-counter').value || '').trim();
    if (!patientId || !specialtyId) {
        alert('Vui lòng nhập ID bệnh nhân và chọn chuyên khoa');
        return;
    }
    var params = new URLSearchParams();
    params.set('benh_nhan_id', patientId);
    params.set('chuyen_khoa_id', specialtyId);
    params.set('uu_tien', priority);
    if (counter) params.set('quay', counter);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', './reception_issue_ticket', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp && resp.success) {
                    alert('Đã phát số: #' + resp.data.ticket.so_thu_tu + ' cho BS ' + resp.data.ticket.bac_si_id);
                    // auto select doctor and refresh queue
                    var dsel = document.getElementById('f-doctor');
                    if (resp.data.ticket && resp.data.ticket.bac_si_id) {
                        dsel.value = String(resp.data.ticket.bac_si_id);
                    }
                    loadQueue();
                } else {
                    alert(resp.message || 'Không thể phát số');
                }
            } catch (e) {
                alert('Lỗi máy chủ');
            }
        }
    };
    xhr.send(params.toString());
}

function loadQueue() {
    var d = document.getElementById('f-doctor').value;
    var date = document.getElementById('f-date').value;
    var st = document.getElementById('f-status').value;
    if (!d) {
        document.getElementById('queue-table').innerHTML =
            '<span class="text-muted">Chọn bác sĩ để xem hàng đợi</span>';
        return;
    }
    var url = './reception_queue_list?bac_si_id=' + encodeURIComponent(d) + '&ngay=' + encodeURIComponent(date);
    if (st) url += '&trang_thai=' + encodeURIComponent(st);
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp && resp.success) {
                    renderQueue(resp.data || []);
                } else {
                    document.getElementById('queue-table').innerHTML = '<span class="text-warning">' + (resp
                        .message || 'Không có dữ liệu') + '</span>';
                }
            } catch (e) {
                document.getElementById('queue-table').innerHTML =
                    '<span class="text-danger">Lỗi tải dữ liệu</span>';
            }
        }
    };
    xhr.send();
}

function renderQueue(rows) {
    if (!rows.length) {
        document.getElementById('queue-table').innerHTML = '<span class="text-muted">Không có phiếu</span>';
        return;
    }
    var html = '<table class="table table-striped table-sm align-middle">' +
        '<thead><tr>' +
        '<th>#</th><th>BN</th><th>Ưu tiên</th><th>Trạng thái</th><th>Giờ gọi</th><th>Thao tác</th>' +
        '</tr></thead><tbody>';
    rows.forEach(function(r) {
        html += '<tr>' +
            '<td><span class="badge bg-primary">' + r.so_thu_tu + '</span></td>' +
            '<td>' + (r.benh_nhan_id || '') + '</td>' +
            '<td>' + (r.uu_tien ? '<span class="badge bg-danger">Ưu tiên</span>' : '') + '</td>' +
            '<td>' + (r.trang_thai || '') + '</td>' +
            '<td>' + (r.thoi_gian_goi || '') + '</td>' +
            '<td class="text-nowrap">' +
            '<button class="btn btn-sm btn-outline-primary me-1" onclick="updateStatus(' + r.id +
            ',\'dang_goi\')">Gọi vào</button>' +
            '<button class="btn btn-sm btn-outline-danger" onclick="updateStatus(' + r.id +
            ',\'huy\')">Hủy</button>' +
            '</td>' +
            '</tr>';
    });
    html += '</tbody></table>';
    document.getElementById('queue-table').innerHTML = html;
}

function updateStatus(id, st) {
    var params = new URLSearchParams();
    params.set('ticket_id', id);
    params.set('trang_thai', st);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', './reception_queue_update', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp && resp.success) {
                    loadQueue();
                } else {
                    alert(resp.message || 'Không thể cập nhật');
                }
            } catch (e) {
                alert('Lỗi máy chủ');
            }
        }
    };
    xhr.send(params.toString());
}

// Realtime: auto refresh queue on appointment updates for selected doctor
document.addEventListener('DOMContentLoaded', function() {
    try {
        if (window.socketManager && window.socketManager.socket) {
            window.socketManager.socket.on('appointment_update', function(data) {
                try {
                    var currentDoctor = document.getElementById('f-doctor').value;
                    if (!currentDoctor) return;
                    if (!data || (String(data.doctorId || '') !== String(currentDoctor))) return;
                    // debounce reload a bit
                    clearTimeout(window.__queueReloadTimer);
                    window.__queueReloadTimer = setTimeout(loadQueue, 300);
                } catch (e) {}
            });
        }
    } catch (e) {}
});
</script>

<?php
$content = ob_get_clean();
renderLayout($content, $page_title);
?>