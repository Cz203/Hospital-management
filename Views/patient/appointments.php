<?php
// Lấy dữ liệu lịch hẹn từ controller
$upcomingAppointments   = $upcomingAppointments   ?? [];
$completedAppointments  = $completedAppointments  ?? [];
$cancelledAppointments  = $cancelledAppointments  ?? [];
// Ngày hẹn đang lọc (nếu có)
$selectedDate           = $selectedDate ?? ($_GET['ngay_hen'] ?? '');
// Thông tin phân trang
$currentPage            = $currentPage ?? ($_GET['page'] ?? 1);
$totalPages             = $totalPages ?? 1;

// Include layout helper & pagination helper
require_once 'Views/layouts/layout_helper.php';
require_once 'Views/layouts/pagination_helper.php';

// Helper format ngày giờ
function formatDateTime($date, $time)
{
    if (empty($date)) {
        return '';
    }
    $dateTime = trim($date . ' ' . ($time ?? ''));
    return date('d/m/Y H:i', strtotime($dateTime));
}

// Helper lấy badge class theo trạng thái
function getStatusBadgeClass($status)
{
    switch ($status) {
        case 'Đã xác nhận':
        case 'Hoàn thành':
            return 'bg-success';
        case 'Chờ xác nhận':
            return 'bg-warning text-dark';
        case 'hủy':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

// Helper text trạng thái hiển thị
function getStatusText($status)
{
    switch ($status) {
        case 'Đã xác nhận':
            return 'Đã xác nhận';
        case 'Chờ xác nhận':
            return 'Chờ xác nhận';
        case 'Hoàn thành':
            return 'Hoàn thành';
        case 'hủy':
            return 'Đã hủy';
        default:
            return $status;
    }
}

// Bắt đầu buffer layout
ob_start();
?>

<div class="container-fluid">
    <!-- Header đơn giản, hiện đại -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 d-flex align-items-center">
                <span
                    class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center me-2"
                    style="width:36px;height:36px;">
                    <i class="fas fa-calendar-check text-primary"></i>
                </span>
                Lịch hẹn của tôi
            </h1>
            <p class="text-muted mb-0 small">
                Xem nhanh, quản lý đơn giản các lịch hẹn khám bệnh của bạn.
            </p>
        </div>
        <div>
            <a href="./doctor_team" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Đặt lịch hẹn mới
            </a>
        </div>
    </div>
    <!-- Bộ lọc theo ngày hẹn (thanh filter gọn, hiện đại) -->
    <form method="GET" action="./patient_appointments"
        class="row g-2 align-items-end bg-light border rounded-3 px-3 py-2 mb-3">
        <div class="col-sm-6 col-md-4 col-lg-3">
            <label for="ngay_hen" class="form-label small mb-1 text-muted">Lọc theo ngày hẹn</label>
            <input type="date" id="ngay_hen" name="ngay_hen" class="form-control form-control-sm"
                value="<?php echo htmlspecialchars($selectedDate ?? ''); ?>">
        </div>
        <div class="col-sm-auto d-flex gap-2 mb-1">
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fas fa-filter me-1"></i>Lọc
            </button>
            <?php if (!empty($selectedDate)): ?>
            <a href="./patient_appointments" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times me-1"></i>Xóa lọc
            </a>
            <?php endif; ?>
        </div>
        <?php if (!empty($selectedDate)): ?>
        <div
            class="col-12 col-md-auto ms-md-auto text-muted small d-flex align-items-center justify-content-start justify-content-md-end">
            <span class="me-1">Đang lọc theo ngày:</span>
            <strong>
                <?php
                    $dt = \DateTime::createFromFormat('Y-m-d', $selectedDate);
                    echo $dt ? $dt->format('d/m/Y') : htmlspecialchars($selectedDate);
                    ?>
            </strong>
        </div>
        <?php endif; ?>
    </form>
    <!-- Card chứa tabs + nội dung -->
    <div class="card shadow-sm border-0">
        <div class="card-header border-0 pb-0 bg-white">
            <ul class="nav nav-tabs card-header-tabs" id="appointmentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active d-flex align-items-center" id="upcoming-tab" data-bs-toggle="tab"
                        data-bs-target="#upcoming" type="button" role="tab">
                        <i class="fas fa-clock me-2"></i>
                        <span>Lịch hẹn sắp tới</span>
                        <span class="badge rounded-pill bg-primary ms-2">
                            <?php echo count($upcomingAppointments); ?>
                        </span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link d-flex align-items-center" id="completed-tab" data-bs-toggle="tab"
                        data-bs-target="#completed" type="button" role="tab">
                        <i class="fas fa-check-circle me-2"></i>
                        <span>Đã hoàn thành</span>
                        <span class="badge rounded-pill bg-success ms-2">
                            <?php echo count($completedAppointments); ?>
                        </span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link d-flex align-items-center" id="cancelled-tab" data-bs-toggle="tab"
                        data-bs-target="#cancelled" type="button" role="tab">
                        <i class="fas fa-times-circle me-2"></i>
                        <span>Đã hủy</span>
                        <span class="badge rounded-pill bg-danger ms-2">
                            <?php echo count($cancelledAppointments); ?>
                        </span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">


    <div class="tab-content" id="appointmentTabContent">
                <!-- Lịch hẹn sắp tới -->
                <div class="tab-pane fade show active" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab">
                    <?php if (empty($upcomingAppointments)): ?>
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <span
                                class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center"
                                style="width:64px;height:64px;">
                                <i class="fas fa-calendar-day text-primary fa-lg"></i>
                            </span>
                        </div>
                        <h6 class="fw-semibold mb-1">Chưa có lịch hẹn sắp tới</h6>
                        <p class="text-muted mb-3 small">
                            Hãy đặt lịch để được bác sĩ tư vấn và khám bệnh nhanh chóng.
                        </p>
                        <a href="./doctor_team" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-calendar-plus me-2"></i>Đặt lịch hẹn ngay
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ngày giờ</th>
                                    <th>Bác sĩ</th>
                                    <th>Chuyên khoa</th>
                                    <th class="w-25">Lý do khám</th>
                                    <th>Trạng thái</th>
                                    <th>Link tư vấn</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcomingAppointments as $appointment): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            <?php echo htmlspecialchars(formatDateTime($appointment['ngay_hen'], $appointment['gio_hen'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">
                                            <?php echo htmlspecialchars($appointment['ten_bac_si'] ?? 'N/A'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($appointment['chuyen_khoa'] ?? 'N/A'); ?>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 220px;">
                                            <?php echo htmlspecialchars($appointment['ly_do'] ?? 'Không có'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge rounded-pill <?php echo getStatusBadgeClass($appointment['trang_thai']); ?>">
                                            <?php echo getStatusText($appointment['trang_thai']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($appointment['link_tu_van'])): ?>
                                        <a href="<?php echo htmlspecialchars($appointment['link_tu_van']); ?>"
                                            target="_blank" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-video me-1"></i>Tư vấn
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted small">Không có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-secondary me-2"
                                                onclick="viewAppointmentDetails(<?php echo (int)$appointment['id']; ?>)">
                                                <i class="fas fa-eye me-1"></i>Chi tiết
                                            </button>
                                        <?php if (in_array($appointment['trang_thai'], ['Chờ xác nhận', 'Đã xác nhận'])): ?>
                                            <button type="button" class="btn btn-outline-danger"
                                                onclick="cancelAppointment(<?php echo (int)$appointment['id']; ?>)">
                                                <i class="fas fa-times me-1"></i>Hủy
                                            </button>
                                        <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Lịch hẹn đã hoàn thành -->
                <div class="tab-pane fade" id="completed" role="tabpanel" aria-labelledby="completed-tab">
                    <?php if (empty($completedAppointments)): ?>
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <span
                                class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center"
                                style="width:64px;height:64px;">
                                <i class="fas fa-check-circle text-success fa-lg"></i>
                            </span>
            </div>
                        <h6 class="fw-semibold mb-1">Chưa có lịch hẹn đã hoàn thành</h6>
                        <p class="text-muted mb-0 small">
                            Các lịch hẹn sau khi bác sĩ khám xong sẽ được hiển thị tại đây.
                        </p>
        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ngày giờ</th>
                                    <th>Bác sĩ</th>
                                    <th>Chuyên khoa</th>
                                    <th class="w-25">Lý do khám</th>
                                    <th>Kết quả</th>
                                    <th>Link tư vấn</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($completedAppointments as $appointment): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            <?php echo htmlspecialchars(formatDateTime($appointment['ngay_hen'], $appointment['gio_hen'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">
                                            <?php echo htmlspecialchars($appointment['ten_bac_si'] ?? 'N/A'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($appointment['chuyen_khoa'] ?? 'N/A'); ?>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 220px;">
                                            <?php echo htmlspecialchars($appointment['ly_do'] ?? 'Không có'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill bg-success">Hoàn thành</span>
                                    </td>
                                    <td>
                                        <?php if (!empty($appointment['link_tu_van'])): ?>
                                        <a href="<?php echo htmlspecialchars($appointment['link_tu_van']); ?>"
                                            target="_blank" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-video me-1"></i>Xem lại
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted small">Không có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-secondary"
                                                onclick="viewAppointmentDetails(<?php echo (int)$appointment['id']; ?>)">
                                                <i class="fas fa-eye me-1"></i>Chi tiết
                                            </button>
                                            <button type="button" class="btn btn-outline-primary"
                                                onclick="downloadReport(<?php echo (int)$appointment['id']; ?>)">
                                                <i class="fas fa-download me-1"></i>Tải về
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Lịch hẹn đã hủy -->
                <div class="tab-pane fade" id="cancelled" role="tabpanel" aria-labelledby="cancelled-tab">
                    <?php if (empty($cancelledAppointments)): ?>
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <span
                                class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center"
                                style="width:64px;height:64px;">
                                <i class="fas fa-times-circle text-danger fa-lg"></i>
                            </span>
            </div>
                        <h6 class="fw-semibold mb-1">Chưa có lịch hẹn đã hủy</h6>
                        <p class="text-muted mb-0 small">
                            Các lịch hẹn hủy sẽ được lưu tại đây để bạn có thể xem lại thông tin.
                        </p>
        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ngày giờ</th>
                                    <th>Bác sĩ</th>
                                    <th>Chuyên khoa</th>
                                    <th class="w-25">Lý do khám</th>
                                    <th>Lý do hủy</th>
                                    <th>Link tư vấn</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cancelledAppointments as $appointment): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            <?php echo htmlspecialchars(formatDateTime($appointment['ngay_hen'], $appointment['gio_hen'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">
                                            <?php echo htmlspecialchars($appointment['ten_bac_si'] ?? 'N/A'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($appointment['chuyen_khoa'] ?? 'N/A'); ?>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 220px;">
                                            <?php echo htmlspecialchars($appointment['ly_do'] ?? 'Không có'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 220px;">
                                            <?php echo htmlspecialchars($appointment['ghi_chu'] ?? 'Không có lý do'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($appointment['link_tu_van'])): ?>
                                        <a href="<?php echo htmlspecialchars($appointment['link_tu_van']); ?>"
                                            target="_blank" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-video me-1"></i>Xem lại
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted small">Không có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            onclick="viewAppointmentDetails(<?php echo (int)$appointment['id']; ?>)">
                                            <i class="fas fa-eye me-1"></i>Chi tiết
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white border-0 pt-0">
            <?php
            // Giữ lại filter ngày khi chuyển trang
            renderPagination(
                (int) $currentPage,
                (int) $totalPages,
                './patient_appointments',
                ['ngay_hen' => $selectedDate]
            );
            ?>
        </div>
    </div>
</div>

<?php
// Lấy nội dung đã render
$content = ob_get_clean();

// Render layout chính
renderLayout($content, 'Lịch hẹn của tôi - Hệ thống Quản lý Bệnh viện');
?>

<script>
// Đánh dấu page để socket client nhận diện + lưu tab đang mở
document.addEventListener('DOMContentLoaded', function() {
    if (document && document.body) {
        document.body.dataset.page = 'patient_appointments';
    }

    // Lưu lại tab đang mở để khi F5 vẫn giữ đúng tab
    try {
        var TAB_KEY = 'patientAppointmentsActiveTab';
        var tabsRoot = document.getElementById('appointmentTabs');
        if (tabsRoot) {
            var tabButtons = tabsRoot.querySelectorAll('button[data-bs-target]');

            // Khi chuyển tab thì lưu lại ID pane (upcoming/completed/cancelled)
            tabButtons.forEach(function(btn) {
                btn.addEventListener('shown.bs.tab', function(e) {
                    var target = e.target.getAttribute('data-bs-target') || '';
                    if (!target) return;
                    var paneId = target.charAt(0) === '#' ? target.substring(1) : target;
                    try {
                        localStorage.setItem(TAB_KEY, paneId);
                    } catch (err) {}
                });
            });

            // Khi load lại trang thì đọc tab đã lưu và kích hoạt
            var savedPane = null;
            try {
                savedPane = localStorage.getItem(TAB_KEY);
            } catch (err) {
                savedPane = null;
            }

            if (savedPane) {
                var btnSelector = 'button[data-bs-target="#' + savedPane + '"]';
                var savedBtn = tabsRoot.querySelector(btnSelector);
                if (savedBtn && !savedBtn.classList.contains('active')) {
                    try {
                        if (window.bootstrap && bootstrap.Tab) {
                            var tab = bootstrap.Tab.getOrCreateInstance(savedBtn);
                            tab.show();
                        } else {
                            // Fallback: tự toggle class nếu vì lý do nào đó bootstrap.Tab chưa sẵn sàng
                            tabButtons.forEach(function(b) {
                                b.classList.remove('active');
                            });
                            savedBtn.classList.add('active');
                            ['upcoming', 'completed', 'cancelled'].forEach(function(id) {
                                var pane = document.getElementById(id);
                                if (!pane) return;
                                pane.classList.remove('show', 'active');
                            });
                            var activePane = document.getElementById(savedPane);
                            if (activePane) {
                                activePane.classList.add('show', 'active');
                            }
                        }
                    } catch (err) {}
                }
            }
        }
    } catch (e) {
        // Nếu localStorage không khả dụng thì bỏ qua, vẫn dùng tab mặc định
    }
});

function viewAppointmentDetails(appointmentId) {
    fetch('./get_appointment_detail?id=' + encodeURIComponent(appointmentId))
        .then(function(r) {
            return r.json();
        })
        .then(function(resp) {
            if (!resp || !resp.success) {
                alert(resp && resp.message ? resp.message : 'Không thể tải chi tiết lịch hẹn');
                return;
            }
            var d = resp.data || {};
            var html = '' +
                '<div class="mb-2"><strong>Bác sĩ:</strong> ' + (d.doctor_name || '') + '</div>' +
                '<div class="mb-2"><strong>Email:</strong> ' + (d.doctor_email || '') + '</div>' +
                '<div class="mb-2"><strong>Chuyên khoa:</strong> ' + (d.chuyen_khoa || '') + '</div>' +
                '<div class="mb-2"><strong>Ngày hẹn:</strong> ' + (d.ngay_hen || '') + '</div>' +
                '<div class="mb-2"><strong>Giờ hẹn:</strong> ' + (d.gio_hen || '') + '</div>' +
                '<div class="mb-2"><strong>Ngày tạo:</strong> ' + (d.ngay_tao || '') + '</div>';
            showAppointmentModal('Chi tiết lịch hẹn', html);
        })
        .catch(function() {
            alert('Lỗi tải chi tiết lịch hẹn');
        });
}

function cancelAppointment(appointmentId) {
    if (!confirm("Bạn có chắc chắn muốn hủy lịch hẹn này?")) {
        return;
    }
        var form = document.createElement("form");
        form.method = "POST";
        form.action = "./cancel_appointment";

        var input = document.createElement("input");
        input.type = "hidden";
        input.name = "appointment_id";
        input.value = appointmentId;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
}

function downloadReport(appointmentId) {
    // TODO: Implement download report thực tế
    alert("Tải báo cáo lịch hẹn ID: " + appointmentId);
}

function showAppointmentModal(title, bodyHtml) {
    try {
        var old = document.getElementById('appt-detail-modal');
        if (old && old.parentNode) {
            old.parentNode.removeChild(old);
        }
    } catch (e) {}

    var wrap = document.createElement('div');
    wrap.id = 'appt-detail-modal';
    wrap.className = 'modal fade';
    wrap.tabIndex = -1;
    wrap.innerHTML = '' +
        '<div class="modal-dialog modal-dialog-centered">' +
        '  <div class="modal-content">' +
        '    <div class="modal-header">' +
        '      <h5 class="modal-title">' + title + '</h5>' +
        '      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
        '    </div>' +
        '    <div class="modal-body">' + bodyHtml + '</div>' +
        '    <div class="modal-footer">' +
        '      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>' +
        '    </div>' +
        '  </div>' +
        '</div>';

    document.body.appendChild(wrap);
    var modal = new bootstrap.Modal(wrap);
    modal.show();
}
</script>