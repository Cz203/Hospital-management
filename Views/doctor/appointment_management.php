<?php
// Helper function để format ngày giờ
function formatDateTime($date, $time)
{
    $dateTime = $date . ' ' . $time;
    return date('d/m/Y H:i', strtotime($dateTime));
}

// Helper function để lấy badge class theo trạng thái
function getStatusBadgeClass($status)
{
    switch ($status) {
        case 'Đã xác nhận':
            return 'bg-success';
        case 'Chờ xác nhận':
            return 'bg-warning';
        case 'Hoàn thành':
            return 'bg-info';
        case 'hủy':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

// Helper function để lấy text trạng thái
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

// Start output buffering
ob_start();
?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-calendar-check text-primary me-2"></i>
                Quản lý lịch hẹn
            </h1>
            <p class="text-muted">Quản lý lịch hẹn khám bệnh của bệnh nhân</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" onclick="refreshAppointments()">
                <i class="fas fa-sync-alt me-2"></i>Làm mới
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng lịch hẹn</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Chờ xác nhận</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['pending'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Đã xác nhận</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['confirmed'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Hoàn thành</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['completed'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-double fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <ul class="nav nav-tabs" id="appointmentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending"
                        type="button" role="tab">
                        <i class="fas fa-clock me-2"></i>Chờ xác nhận
                        <span class="badge bg-warning ms-2"><?php echo count($pendingAppointments); ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="confirmed-tab" data-bs-toggle="tab" data-bs-target="#confirmed"
                        type="button" role="tab">
                        <i class="fas fa-check-circle me-2"></i>Đã xác nhận
                        <span class="badge bg-success ms-2"><?php echo count($confirmedAppointments); ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed"
                        type="button" role="tab">
                        <i class="fas fa-check-double me-2"></i>Hoàn thành
                        <span class="badge bg-info ms-2"><?php echo count($completedAppointments); ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled"
                        type="button" role="tab">
                        <i class="fas fa-times-circle me-2"></i>Đã hủy
                        <span class="badge bg-danger ms-2"><?php echo count($cancelledAppointments); ?></span>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content" id="appointmentTabContent">
        <!-- Pending Appointments -->
        <div class="tab-pane fade show active" id="pending" role="tabpanel">
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ngày giờ</th>
                                    <th>Bệnh nhân</th>
                                    <th>Số điện thoại</th>
                                    <th>Lý do khám</th>
                                    <th>Loại lịch</th>
                                    <th>Link tư vấn</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pendingAppointments)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-calendar-times fa-2x mb-2"></i><br>
                                        Không có lịch hẹn chờ xác nhận
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($pendingAppointments as $appointment): ?>
                                <tr>
                                    <td><?php echo formatDateTime($appointment['ngay_hen'], $appointment['gio_hen']); ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div
                                                class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <?php echo strtoupper(substr($appointment['ten_benh_nhan'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold">
                                                    <?php echo htmlspecialchars($appointment['ten_benh_nhan']); ?></div>
                                                <small
                                                    class="text-muted"><?php echo $appointment['gioi_tinh'] ?? 'N/A'; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($appointment['so_dien_thoai'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['ly_do'] ?? 'Không có'); ?></td>
                                    <td>
                                        <span
                                            class="badge bg-info"><?php echo htmlspecialchars($appointment['loai_lich'] ?? 'Trực tiếp'); ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($appointment['link_tu_van'])): ?>
                                        <a href="<?php echo htmlspecialchars($appointment['link_tu_van']); ?>"
                                            target="_blank" class="btn btn-sm btn-success">
                                            <i class="fas fa-video me-1"></i>Tham gia tư vấn
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted">Không có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-success btn-sm"
                                            onclick="updateAppointmentStatus(<?php echo $appointment['id']; ?>, 'Đã xác nhận')">
                                            <i class="fas fa-check"></i> Xác nhận
                                        </button>

                                        <button class="btn btn-sm btn-danger"
                                            onclick="cancelAppointment(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-times me-1"></i>Từ chối
                                        </button>
                                        <button class="btn btn-sm btn-info"
                                            onclick="viewAppointmentDetails(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-eye me-1"></i>Chi tiết
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmed Appointments -->
        <div class="tab-pane fade" id="confirmed" role="tabpanel">
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ngày giờ</th>
                                    <th>Bệnh nhân</th>
                                    <th>Số điện thoại</th>
                                    <th>Lý do khám</th>
                                    <th>Loại lịch</th>
                                    <th>Link tư vấn</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($confirmedAppointments)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                                        Không có lịch hẹn đã xác nhận
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($confirmedAppointments as $appointment): ?>
                                <tr>
                                    <td><?php echo formatDateTime($appointment['ngay_hen'], $appointment['gio_hen']); ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div
                                                class="avatar-sm bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <?php echo strtoupper(substr($appointment['ten_benh_nhan'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold">
                                                    <?php echo htmlspecialchars($appointment['ten_benh_nhan']); ?></div>
                                                <small
                                                    class="text-muted"><?php echo $appointment['gioi_tinh'] ?? 'N/A'; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($appointment['so_dien_thoai'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['ly_do'] ?? 'Không có'); ?></td>
                                    <td>
                                        <span
                                            class="badge bg-info"><?php echo htmlspecialchars($appointment['loai_lich'] ?? 'Trực tiếp'); ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($appointment['link_tu_van'])): ?>
                                        <a href="<?php echo htmlspecialchars($appointment['link_tu_van']); ?>"
                                            target="_blank" class="btn btn-sm btn-success">
                                            <i class="fas fa-video me-1"></i>Tham gia tư vấn
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted">Không có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary"
                                            onclick="completeAppointment(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-check-double me-1"></i>Hoàn thành
                                        </button>
                                        <button class="btn btn-sm btn-warning"
                                            onclick="cancelAppointment(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-times me-1"></i>Hủy
                                        </button>
                                        <button class="btn btn-sm btn-info"
                                            onclick="viewAppointmentDetails(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-eye me-1"></i>Chi tiết
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Appointments -->
        <div class="tab-pane fade" id="completed" role="tabpanel">
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ngày giờ</th>
                                    <th>Bệnh nhân</th>
                                    <th>Số điện thoại</th>
                                    <th>Lý do khám</th>
                                    <th>Kết quả</th>
                                    <th>Link tư vấn</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($completedAppointments)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-check-double fa-2x mb-2"></i><br>
                                        Không có lịch hẹn đã hoàn thành
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($completedAppointments as $appointment): ?>
                                <tr>
                                    <td><?php echo formatDateTime($appointment['ngay_hen'], $appointment['gio_hen']); ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div
                                                class="avatar-sm bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <?php echo strtoupper(substr($appointment['ten_benh_nhan'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold">
                                                    <?php echo htmlspecialchars($appointment['ten_benh_nhan']); ?></div>
                                                <small
                                                    class="text-muted"><?php echo $appointment['gioi_tinh'] ?? 'N/A'; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($appointment['so_dien_thoai'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['ly_do'] ?? 'Không có'); ?></td>
                                    <td><span class="badge bg-success">Hoàn thành</span></td>
                                    <td>
                                        <?php if (!empty($appointment['link_tu_van'])): ?>
                                        <a href="<?php echo htmlspecialchars($appointment['link_tu_van']); ?>"
                                            target="_blank" class="btn btn-sm btn-success">
                                            <i class="fas fa-video me-1"></i>Xem lại cuộc họp
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted">Không có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info"
                                            onclick="viewAppointmentDetails(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-eye me-1"></i>Chi tiết
                                        </button>
                                        <button class="btn btn-sm btn-primary"
                                            onclick="createMedicalRecord(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-file-medical me-1"></i>Hồ sơ
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancelled Appointments -->
        <div class="tab-pane fade" id="cancelled" role="tabpanel">
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ngày giờ</th>
                                    <th>Bệnh nhân</th>
                                    <th>Số điện thoại</th>
                                    <th>Lý do khám</th>
                                    <th>Lý do hủy</th>
                                    <th>Link tư vấn</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($cancelledAppointments)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-times-circle fa-2x mb-2"></i><br>
                                        Không có lịch hẹn đã hủy
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($cancelledAppointments as $appointment): ?>
                                <tr>
                                    <td><?php echo formatDateTime($appointment['ngay_hen'], $appointment['gio_hen']); ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div
                                                class="avatar-sm bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <?php echo strtoupper(substr($appointment['ten_benh_nhan'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold">
                                                    <?php echo htmlspecialchars($appointment['ten_benh_nhan']); ?></div>
                                                <small
                                                    class="text-muted"><?php echo $appointment['gioi_tinh'] ?? 'N/A'; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($appointment['so_dien_thoai'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['ly_do'] ?? 'Không có'); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['ghi_chu'] ?? 'Không có lý do'); ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($appointment['link_tu_van'])): ?>
                                        <a href="<?php echo htmlspecialchars($appointment['link_tu_van']); ?>"
                                            target="_blank" class="btn btn-sm btn-success">
                                            <i class="fas fa-video me-1"></i>Xem lại cuộc họp
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted">Không có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info"
                                            onclick="viewAppointmentDetails(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-eye me-1"></i>Chi tiết
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 35px;
    height: 35px;
    font-size: 14px;
    font-weight: 600;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}
</style>

<script>
function refreshAppointments() {
    location.reload();
}

function confirmAppointment(appointmentId) {
    if (confirm("Bạn có chắc chắn muốn xác nhận lịch hẹn này?")) {
        updateAppointmentStatus(appointmentId, 'Đã xác nhận');
    }
}

function completeAppointment(appointmentId) {
    if (confirm("Bạn có chắc chắn muốn đánh dấu lịch hẹn này là hoàn thành?")) {
        updateAppointmentStatus(appointmentId, 'Hoàn thành');
    }
}

function cancelAppointment(appointmentId) {
    const reason = prompt("Nhập lý do hủy lịch hẹn:");
    if (reason !== null && reason.trim() !== '') {
        updateAppointmentStatus(appointmentId, 'hủy', reason);
    }
}

function updateAppointmentStatus(appointmentId, status, note = '') {
    // Tạo form ẩn để submit
    var form = document.createElement("form");
    form.method = "POST";
    form.action = "./update_appointment_status";

    var appointmentIdInput = document.createElement("input");
    appointmentIdInput.type = "hidden";
    appointmentIdInput.name = "appointment_id";
    appointmentIdInput.value = appointmentId;

    var statusInput = document.createElement("input");
    statusInput.type = "hidden";
    statusInput.name = "status";
    statusInput.value = status;

    var noteInput = document.createElement("input");
    noteInput.type = "hidden";
    noteInput.name = "note";
    noteInput.value = note;

    form.appendChild(appointmentIdInput);
    form.appendChild(statusInput);
    form.appendChild(noteInput);
    document.body.appendChild(form);
    form.submit();
}

function viewAppointmentDetails(appointmentId) {
    // TODO: Implement view appointment details modal
    alert("Xem chi tiết lịch hẹn ID: " + appointmentId);
}

function createMedicalRecord(appointmentId) {
    // TODO: Implement create medical record
    alert("Tạo hồ sơ bệnh án cho lịch hẹn ID: " + appointmentId);
}

// Function to refresh appointments
function refreshAppointments() {
    console.log("Refreshing appointments...");
    window.location.reload();
}

// Listen for custom refresh event from socket client
document.addEventListener('appointmentRefresh', function() {
    console.log("Received appointmentRefresh event");
    refreshAppointments();
});

// Add refresh button if not exists
</script>

<script>
// Responsive tables: convert to stacked cards on small screens
(function() {
    function applyResponsiveTables(root) {
        try {
            var tables = root.querySelectorAll('table');
            tables.forEach(function(table) {
                var headers = Array.from(table.querySelectorAll('thead th')).map(function(th) {
                    return (th.textContent || '').trim();
                });
                table.querySelectorAll('tbody tr').forEach(function(tr) {
                    Array.from(tr.children).forEach(function(td, idx) {
                        if (!td.getAttribute('data-label') && headers[idx]) {
                            td.setAttribute('data-label', headers[idx]);
                        }
                    });
                });
            });
        } catch (e) {
            console.warn('Responsive table init error:', e);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            applyResponsiveTables(document);
        });
    } else {
        applyResponsiveTables(document);
    }
})();
</script>

<style>
/* Mobile-first stacked table for small screens */
@media (max-width: 576px) {
    table.table {
        border: 0 !important;
    }

    table.table thead {
        display: none;
    }

    table.table tbody tr {
        display: block;
        margin-bottom: 0.875rem;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 0.5rem;
        overflow: hidden;
    }

    table.table tbody tr td {
        display: grid;
        grid-template-columns: 40% 60%;
        gap: 0.25rem 0.75rem;
        text-align: left !important;
        border: 0 !important;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
        padding: 0.5rem 0.75rem !important;
    }

    table.table tbody tr td:last-child {
        border-bottom: 0 !important;
    }

    table.table tbody tr td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #6c757d;
    }
}
</style>