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
                            <option value="0">-- Tất cả bác sĩ --</option>
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
                sel.innerHTML = '<option value="0">-- Tất cả bác sĩ --</option>';
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
    // Lưu dữ liệu vào bộ nhớ tạm để phục vụ in phiếu
    try {
        window.__queueRows = {};
        rows.forEach(function(x) {
            if (x && x.id) {
                window.__queueRows[String(x.id)] = x;
            }
        });
    } catch (e) {}
    var html = '<table class="table table-striped table-sm align-middle">' +
        '<thead><tr>' +
        '<th>STT</th><th>Tên bệnh nhân</th><th>Bác sĩ</th><th>Dự kiến</th><th>Thao tác</th>' +
        '</tr></thead><tbody>';
    rows.forEach(function(r) {
        html += '<tr>' +
            '<td><span class="badge bg-primary">' + r.so_thu_tu + '</span></td>' +
            '<td>' + (r.ten_benh_nhan || r.benh_nhan_id || '') + '</td>' +
            '<td>' + (r.ten_bac_si || r.bac_si_id || '') + '</td>' +
            '<td>' + (r.thoi_gian_du_kien || '') + '</td>' +

            '<td class="text-nowrap">' +
            '<button class="btn btn-sm btn-outline-secondary" onclick="printTicket(' + r.id +
            ')"><i class="fas fa-print me-1"></i>In phiếu</button>' +
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

// In phiếu bốc số (popup in ấn đơn giản)
function printTicket(id) {
    try {
        var data = (window.__queueRows || {})[String(id)] || null;
        if (!data) {
            alert('Không tìm thấy dữ liệu phiếu');
            return;
        }
        var ngay = data.ngay || (new Date()).toISOString().slice(0, 10);
        var now = new Date();
        var hh = String(now.getHours()).padStart(2, '0');
        var mm = String(now.getMinutes()).padStart(2, '0');
        var timeNow = hh + ':' + mm;
        var tenBn = data.ten_benh_nhan || ('BN #' + (data.benh_nhan_id || ''));
        var tenBs = data.ten_bac_si || ('BS #' + (data.bac_si_id || ''));
        var quay = data.quay || '';
        var duKien = data.thoi_gian_du_kien || '';
        var so = data.so_thu_tu || '';

        var w = window.open('', 'PRINT', 'height=600,width=420');
        if (!w) {
            alert('Trình duyệt chặn cửa sổ in');
            return;
        }
        w.document.write('<html><head><title>Phiếu bốc số</title>');
        w.document.write('<style>\n' +
            'body{font-family:Arial,Helvetica,sans-serif;padding:16px;}\n' +
            '.center{text-align:center;}\n' +
            '.title{font-size:18px;font-weight:700;margin-bottom:8px;}\n' +
            '.big{font-size:56px;font-weight:800;line-height:1.1;margin:12px 0;}\n' +
            '.meta{font-size:13px;color:#555;margin:2px 0;}\n' +
            '.row{margin:4px 0;}\n' +
            '.label{color:#666;}\n' +
            '.footer{margin-top:16px;border-top:1px dashed #999;padding-top:8px;font-size:12px;color:#666;}\n' +
            '</style>');
        w.document.write('</head><body>');
        w.document.write('<div class="center">');
        w.document.write('<div class="title">ThinhViet Clinic</div>');
        w.document.write('<div class="meta">Ngày: ' + ngay + ' | In lúc: ' + timeNow + '</div>');
        if (quay) w.document.write('<div class="meta">Quầy: ' + quay + '</div>');
        w.document.write('<div class="big">#' + so + '</div>');
        w.document.write('</div>');
        w.document.write('<div class="row"><span class="label">Bệnh nhân:</span> ' + tenBn + '</div>');
        w.document.write('<div class="row"><span class="label">Bác sĩ:</span> ' + tenBs + '</div>');
        if (duKien) w.document.write('<div class="row"><span class="label">Dự kiến khám:</span> ' + duKien + '</div>');
        if (data.uu_tien) w.document.write('<div class="row"><strong>ƯU TIÊN</strong></div>');
        if (data.thoi_gian_goi) w.document.write('<div class="row"><span class="label">Giờ gọi:</span> ' + (data
            .thoi_gian_goi || '') + '</div>');
        w.document.write('<div class="footer center">Vui lòng chờ tới lượt. Xin cảm ơn!</div>');
        w.document.write('</body></html>');
        w.document.close();
        w.focus();
        try {
            w.print();
        } catch (e) {}
        // setTimeout(() => { try { w.close(); } catch(e) {} }, 300);
    } catch (e) {
        alert('Không thể in phiếu');
    }
}

// Auto-load and auto-refresh on filter changes
document.addEventListener('DOMContentLoaded', function() {
    try {
        var dsel = document.getElementById('f-doctor');
        if (dsel && !dsel.value) dsel.value = '0'; // default to All doctors
        var dateSel = document.getElementById('f-date');
        var stSel = document.getElementById('f-status');
        if (dsel) dsel.addEventListener('change', function() {
            loadQueue();
        });
        if (dateSel) dateSel.addEventListener('change', function() {
            loadQueue();
        });
        if (stSel) stSel.addEventListener('change', function() {
            loadQueue();
        });
        // initial load
        loadQueue();
    } catch (e) {}
});
</script>

<?php
$content = ob_get_clean();
renderLayout($content, $page_title);
?>