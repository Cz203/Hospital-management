<?php
require_once 'Views/layouts/layout_helper.php';
require_once 'Models/Specialty.php';

$page_title = 'Bốc số - Lễ tân';
$specModel = new Specialty();
$specialties = $specModel->all();

ob_start();
?>

<div class="container-fluid">
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>Phát phiếu bốc số</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Số điện thoại bệnh nhân <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="q-patient" class="form-control"
                                placeholder="Nhập số điện thoại (0xxxxxxxxx hoặc 84xxxxxxxxx)">
                            <div class="form-text">Có thể tra cứu bệnh nhân ở trang Dashboard trước.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Chuyên khoa <span class="text-danger">*</span></label>
                            <select id="q-specialty" class="form-select">
                                <option value="">-- Chọn chuyên khoa --</option>
                                <?php foreach (($specialties ?? []) as $s): ?>
                                    <option value="<?php echo (int)$s['id']; ?>">
                                        <?php echo htmlspecialchars($s['ten'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Ưu tiên</label>
                            <select id="q-priority" class="form-select">
                                <option value="0">Thường</option>
                                <option value="1">Ưu tiên</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Quầy</label>
                            <input type="text" id="q-counter" class="form-control" placeholder="VD: A1">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button class="btn btn-primary w-100" onclick="issueTicket()">
                                <i class="fas fa-plus-circle me-1"></i> Phát phiếu
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="fas fa-stream me-2"></i>Hàng đợi hôm nay</h5>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="date" id="f-date" class="form-control form-control-sm"
                            value="<?php echo date('Y-m-d'); ?>">
                        <button class="btn btn-light btn-sm" onclick="refreshAllQueues()">
                            <i class="fas fa-sync"></i> Làm mới
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Tabs chuyên khoa -->
                    <ul class="nav nav-tabs nav-tabs-custom" id="specialtyTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-all" data-bs-toggle="tab"
                                data-bs-target="#panel-all" type="button" role="tab" onclick="switchSpecialty(0)">
                                <i class="fas fa-list me-1"></i> Tất cả
                            </button>
                        </li>
                        <?php foreach (($specialties ?? []) as $index => $s): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-<?php echo $s['id']; ?>" data-bs-toggle="tab"
                                    data-bs-target="#panel-<?php echo $s['id']; ?>" type="button" role="tab"
                                    onclick="switchSpecialty(<?php echo $s['id']; ?>)"
                                    data-specialty-id="<?php echo $s['id']; ?>">
                                    <?php echo htmlspecialchars($s['ten'], ENT_QUOTES, 'UTF-8'); ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Tab panels -->
                    <div class="tab-content" id="specialtyTabContent">
                        <div class="tab-pane fade show active" id="panel-all" role="tabpanel">
                            <div class="p-3">
                                <div id="queue-table-all" class="table-responsive">
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-spinner fa-spin me-2"></i> Đang tải...
                                    </div>
                                </div>
                                <div id="queue-pagination-all" class="mt-3"></div>
                            </div>
                        </div>
                        <?php foreach (($specialties ?? []) as $s): ?>
                            <div class="tab-pane fade" id="panel-<?php echo $s['id']; ?>" role="tabpanel">
                                <div class="p-3">
                                    <div id="queue-table-<?php echo $s['id']; ?>" class="table-responsive">
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-spinner fa-spin me-2"></i> Đang tải...
                                        </div>
                                    </div>
                                    <div id="queue-pagination-<?php echo $s['id']; ?>" class="mt-3"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .nav-tabs-custom {
        border-bottom: 2px solid #dee2e6;
        padding: 0 1rem;
        margin: 0;
    }

    .nav-tabs-custom .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6c757d;
        padding: 0.75rem 1.25rem;
        font-weight: 500;
        transition: all 0.3s;
    }

    .nav-tabs-custom .nav-link:hover {
        border-bottom-color: #0dcaf0;
        color: #0dcaf0;
        background-color: rgba(13, 202, 240, 0.05);
    }

    .nav-tabs-custom .nav-link.active {
        border-bottom-color: #0dcaf0;
        color: #0dcaf0;
        background-color: transparent;
        font-weight: 600;
    }

    .queue-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        transition: all 0.3s;
        background: #fff;
    }

    .queue-card:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .queue-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0d6efd;
    }

    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .priority-badge {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: #fff;
        font-weight: 600;
    }
</style>

<script>
    let currentSpecialtyId = 0;
    let queueData = {};

    // Load doctors on specialty change
    document.getElementById('q-specialty').addEventListener('change', function() {
        var sid = this.value;
        if (!sid) return;
        var xhr = new XMLHttpRequest();
        xhr.open('GET', './get_doctors_by_specialty?specialty_id=' + encodeURIComponent(sid), true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                try {
                    var resp = JSON.parse(xhr.responseText);
                    // Không cần load vào select nữa vì đã dùng tabs
                } catch (e) {}
            }
        };
        xhr.send();
    });

    function issueTicket() {
        var phone = (document.getElementById('q-patient').value || '').trim();
        var specialtyId = parseInt(document.getElementById('q-specialty').value || '0', 10);
        var priority = parseInt(document.getElementById('q-priority').value || '0', 10);
        var counter = (document.getElementById('q-counter').value || '').trim();
        if (!phone || !specialtyId) {
            alert('Vui lòng nhập số điện thoại bệnh nhân và chọn chuyên khoa');
            return;
        }

        // Bước 1: tìm bệnh nhân theo SĐT
        var findParams = new URLSearchParams();
        findParams.set('phone', phone);
        var xhrFind = new XMLHttpRequest();
        xhrFind.open('POST', './reception_find_patient', true);
        xhrFind.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhrFind.onreadystatechange = function() {
            if (xhrFind.readyState === 4) {
                try {
                    var respFind = JSON.parse(xhrFind.responseText);
                    if (!respFind || !respFind.success || !respFind.data || !respFind.data.id) {
                        alert((respFind && respFind.message) || 'Không tìm thấy bệnh nhân với số điện thoại này');
                        return;
                    }
                    var patientId = respFind.data.id;

                    // Bước 2: phát phiếu
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
                                    alert('Đã phát số: #' + resp.data.ticket.so_thu_tu);
                                    // Reset form
                                    document.getElementById('q-patient').value = '';
                                    document.getElementById('q-counter').value = '';
                                    // Refresh queue của chuyên khoa vừa phát
                                    loadQueue(specialtyId);
                                    // Nếu đang xem tab "Tất cả", refresh luôn
                                    if (currentSpecialtyId === 0) {
                                        loadQueue(0);
                                    }
                                } else {
                                    alert(resp.message || 'Không thể phát số');
                                }
                            } catch (e) {
                                alert('Lỗi máy chủ');
                            }
                        }
                    };
                    xhr.send(params.toString());
                } catch (e) {
                    alert('Lỗi tìm bệnh nhân theo số điện thoại');
                }
            }
        };
        xhrFind.send(findParams.toString());
    }

    function switchSpecialty(specialtyId) {
        currentSpecialtyId = specialtyId;
        loadQueue(specialtyId);
    }

    function refreshAllQueues() {
        loadQueue(currentSpecialtyId);
    }

    function loadQueue(specialtyId, page) {
        specialtyId = specialtyId !== undefined ? specialtyId : currentSpecialtyId;
        page = page || 1;
        var date = document.getElementById('f-date').value;

        var tableId = specialtyId === 0 ? 'queue-table-all' : 'queue-table-' + specialtyId;
        var paginationId = specialtyId === 0 ? 'queue-pagination-all' : 'queue-pagination-' + specialtyId;

        // Hiển thị loading
        document.getElementById(tableId).innerHTML =
            '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i> Đang tải...</div>';

        var url = './reception_queue_list?ngay=' + encodeURIComponent(date) + '&page=' + encodeURIComponent(page);
        if (specialtyId > 0) {
            url += '&chuyen_khoa_id=' + encodeURIComponent(specialtyId);
        }

        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp && resp.success) {
                        renderQueue(resp.data || [], resp.pagination || null, tableId, paginationId, specialtyId);
                    } else {
                        document.getElementById(tableId).innerHTML =
                            '<div class="text-center text-warning py-4">' + (resp.message || 'Không có dữ liệu') +
                            '</div>';
                    }
                } catch (e) {
                    document.getElementById(tableId).innerHTML =
                        '<div class="text-center text-danger py-4">Lỗi tải dữ liệu</div>';
                }
            }
        };
        xhr.send();
    }

    function renderQueue(rows, pagination, tableId, paginationId, specialtyId) {
        if (!rows.length) {
            document.getElementById(tableId).innerHTML =
                '<div class="text-center text-muted py-4"><i class="fas fa-inbox me-2"></i> Chưa có phiếu bốc số</div>';
            document.getElementById(paginationId).innerHTML = '';
            return;
        }

        // Lưu dữ liệu vào bộ nhớ và gắn số thứ tự hiển thị (reset 1..n theo danh sách hiện tại)
        try {
            if (!window.__queueRows) window.__queueRows = {};
            rows.forEach(function(x, idx) {
                if (x && x.id) {
                    // Số thứ tự hiển thị theo từng chuyên khoa/danh sách hiện tại: 1..n
                    x.__display_index = idx + 1;
                    window.__queueRows[String(x.id)] = x;
                }
            });
        } catch (e) {}

        var html = '<div class="row g-3">';
        rows.forEach(function(r) {
            var statusClass = 'bg-secondary';
            var statusText = 'Chờ';
            switch (r.trang_thai) {
                case 'cho':
                    statusClass = 'bg-info';
                    statusText = 'Chờ';
                    break;
                case 'dang_goi':
                    statusClass = 'bg-warning';
                    statusText = 'Đang gọi';
                    break;
                case 'dang_kham':
                    statusClass = 'bg-primary';
                    statusText = 'Đang khám';
                    break;
                case 'xong':
                    statusClass = 'bg-success';
                    statusText = 'Hoàn thành';
                    break;
                case 'huy':
                    statusClass = 'bg-danger';
                    statusText = 'Đã hủy';
                    break;
            }

            html += '<div class="col-md-6 col-lg-4">';
            html += '<div class="queue-card">';
            html += '<div class="d-flex justify-content-between align-items-start mb-2">';
            html += '<div>';
            // Hiển thị STT theo danh sách hiện tại thay vì số thứ tự toàn hệ thống
            var displayNum = (typeof r.__display_index === 'number' ? r.__display_index : '')
            html += '<div class="queue-number">#' + displayNum + '</div>';
            html += '<div class="text-muted small">' + (r.ten_benh_nhan || 'BN #' + r.benh_nhan_id) + '</div>';
            html += '</div>';
            html += '<span class="badge ' + statusClass + ' status-badge">' + statusText + '</span>';
            html += '</div>';

            html += '<div class="mb-2">';
            html += '<i class="fas fa-user-md text-primary me-1"></i>';
            html += '<span class="small">' + (r.ten_bac_si || 'BS #' + r.bac_si_id) + '</span>';
            if (r.ten_chuyen_khoa) {
                html += '<br><i class="fas fa-stethoscope text-info me-1"></i>';
                html += '<span class="small text-muted">' + r.ten_chuyen_khoa + '</span>';
            }
            html += '</div>';

            if (r.thoi_gian_du_kien) {
                html += '<div class="mb-2">';
                html += '<i class="fas fa-clock text-success me-1"></i>';
                html += '<span class="small">Dự kiến: ' + r.thoi_gian_du_kien + '</span>';
                html += '</div>';
            }

            if (r.uu_tien == 1) {
                html += '<div class="mb-2">';
                html += '<span class="badge priority-badge">Ưu tiên</span>';
                html += '</div>';
            }

            html += '<div class="d-flex gap-2 mt-3">';
            html += '<button class="btn btn-sm btn-outline-primary flex-fill" onclick="printTicket(' + r.id + ')">';
            html += '<i class="fas fa-print me-1"></i> In phiếu';
            html += '</button>';
            html += '</div>';

            html += '</div></div>';
        });
        html += '</div>';

        document.getElementById(tableId).innerHTML = html;

        // Phân trang
        renderPagination(pagination, paginationId, specialtyId);
    }

    function renderPagination(pagination, paginationId, specialtyId) {
        var pagEl = document.getElementById(paginationId);
        if (!pagination || !pagination.total_pages || pagination.total_pages < 1) {
            pagEl.innerHTML = '';
            return;
        }

        var current = pagination.current_page || 1;
        var totalPages = pagination.total_pages;

        var phtml = '<nav aria-label="Queue pagination"><ul class="pagination pagination-sm justify-content-center mb-0">';

        // Prev
        var prevDisabled = current <= 1 ? ' disabled' : '';
        phtml += '<li class="page-item' + prevDisabled + '">';
        phtml += '<button type="button" class="page-link" onclick="if(' + current + ' > 1) loadQueue(' + specialtyId + ',' +
            (current - 1) + ');">&laquo;</button></li>';

        // Window of pages
        var windowSize = 2;
        var start = Math.max(1, current - windowSize);
        var end = Math.min(totalPages, current + windowSize);

        if (start > 1) {
            phtml += '<li class="page-item' + (current === 1 ? ' active' : '') + '">';
            phtml += '<button type="button" class="page-link" onclick="loadQueue(' + specialtyId + ',1)">1</button></li>';
            if (start > 2) {
                phtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        for (var i = start; i <= end; i++) {
            if (i === 1 || i === totalPages) continue;
            var active = i === current ? ' active' : '';
            phtml += '<li class="page-item' + active + '">';
            phtml += '<button type="button" class="page-link" onclick="loadQueue(' + specialtyId + ',' + i + ')">' + i +
                '</button></li>';
        }

        if (end < totalPages) {
            if (end < totalPages - 1) {
                phtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            phtml += '<li class="page-item' + (current === totalPages ? ' active' : '') + '">';
            phtml += '<button type="button" class="page-link" onclick="loadQueue(' + specialtyId + ',' + totalPages +
                ')">' + totalPages + '</button></li>';
        }

        // Next
        var nextDisabled = current >= totalPages ? ' disabled' : '';
        phtml += '<li class="page-item' + nextDisabled + '">';
        phtml += '<button type="button" class="page-link" onclick="if(' + current + ' < ' + totalPages + ') loadQueue(' +
            specialtyId + ',' + (current + 1) + ');">&raquo;</button></li>';

        phtml += '</ul></nav>';
        pagEl.innerHTML = phtml;
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
                        refreshAllQueues();
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

    // In phiếu bốc số
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
            // In số thứ tự hiển thị (1..n theo danh sách)
            var so = (typeof data.__display_index === 'number' ? data.__display_index : '');

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
        } catch (e) {
            alert('Không thể in phiếu');
        }
    }

    // Auto-load on page load
    document.addEventListener('DOMContentLoaded', function() {
        var dateSel = document.getElementById('f-date');
        if (dateSel) {
            dateSel.addEventListener('change', function() {
                refreshAllQueues();
            });
        }
        // Load queue ban đầu cho tab "Tất cả"
        loadQueue(0);

        // Listen for socket events to refresh queue in real-time
        function setupQueueSocketListener() {
            // Listen for custom event from socket-client.js
            window.addEventListener('queueUpdate', function(event) {
                if (event && event.detail) {
                    handleQueueUpdate(event.detail);
                }
            });

            // Also listen directly to socket if available (fallback)
            if (typeof window.socketManager !== 'undefined' && window.socketManager.socket) {
                // Listen for queue_update event (specific for queue)
                window.socketManager.socket.on('queue_update', function(data) {
                    handleQueueUpdate(data);
                });

                // Also listen for appointment_update that might contain queue info
                window.socketManager.socket.on('appointment_update', function(data) {
                    if (data && (data.queueStatus || data.queueId)) {
                        handleQueueUpdate(data);
                    }
                });
            } else {
                // Retry if socket not ready yet
                setTimeout(setupQueueSocketListener, 500);
            }
        }

        function handleQueueUpdate(data) {
            if (!data) return;

            // Get current specialty from active tab
            const activeTab = document.querySelector('#specialtyTabs .nav-link.active');
            let specialtyId = 0;
            if (activeTab && activeTab.getAttribute('data-specialty-id')) {
                specialtyId = parseInt(activeTab.getAttribute('data-specialty-id')) || 0;
            }

            // If status is 'dang_kham', the ticket should be removed from view
            if (data.queueStatus === 'dang_kham') {
                // Refresh current tab
                loadQueue(specialtyId);
                // Also refresh "Tất cả" tab if not already viewing it
                if (specialtyId !== 0) {
                    setTimeout(() => loadQueue(0), 100);
                }
            } else if (data.queueStatus || data.action === 'new_ticket') {
                // For other status updates or new tickets, refresh current tab
                // If new ticket and we have specialtyId, refresh that specialty tab
                if (data.action === 'new_ticket' && data.specialtyId) {
                    // Refresh the specialty tab that received the new ticket
                    loadQueue(data.specialtyId);
                    // Also refresh "Tất cả" tab
                    if (specialtyId !== 0 || data.specialtyId !== 0) {
                        setTimeout(() => loadQueue(0), 100);
                    }
                } else {
                    // Refresh current tab
                    loadQueue(specialtyId);
                }
            }
        }

        // Setup socket listener
        setupQueueSocketListener();
    });
</script>

<?php
$content = ob_get_clean();
renderLayout($content, $page_title);
?>