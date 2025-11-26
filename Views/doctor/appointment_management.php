<?php
// Helper function để format ngày giờ
function formatDateTime($date, $time)
{
    $dateTime = trim($date . ' ' . ($time ?? ''));
    return date('d/m/Y H:i', strtotime($dateTime));
}

// Helper function để lấy badge class theo trạng thái
function getStatusBadgeClass($status)
{
    switch ($status) {
        case 'Đã xác nhận':
            return 'bg-success';
        case 'Chờ xác nhận':
            return 'bg-warning text-dark';
        case 'Hoàn thành':
            return 'bg-info';
        case 'Đang khám':
            return 'bg-warning text-dark';
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
        case 'Đang khám':
            return 'Đang khám';
        case 'hủy':
            return 'Đã hủy';
        default:
            return $status;
    }
}

// Thông tin phân trang
$currentPage = $currentPage ?? ($_GET['page'] ?? 1);
$totalPages  = $totalPages  ?? 1;

// Pagination helper (dùng chung)
require_once 'Views/layouts/pagination_helper.php';

// Start output buffering
ob_start();
?>
<div class="container-fluid">
    <!-- Page Header hiện đại, tương tự patient appointments -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 d-flex align-items-center">
                <span
                    class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center me-2"
                    style="width:36px;height:36px;">
                    <i class="fas fa-calendar-check text-primary"></i>
                </span>
                Quản lý lịch hẹn
            </h1>
            <p class="text-muted mb-0 small">
                Theo dõi, duyệt và cập nhật lịch hẹn khám bệnh của bệnh nhân.
            </p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" onclick="refreshAppointments()">
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

    <!-- Tabs trong card, giống style patient appointments -->
    <div class="card shadow-sm border-0">
        <div class="card-header border-0 pb-0 bg-white">
            <ul class="nav nav-tabs card-header-tabs" id="appointmentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active d-flex align-items-center" id="pending-tab" data-bs-toggle="tab"
                        data-bs-target="#pending" type="button" role="tab">
                        <i class="fas fa-clock me-2"></i>
                        <span>Chờ xác nhận</span>
                        <span class="badge rounded-pill bg-warning text-dark ms-2">
                            <?php echo count($pendingAppointments); ?>
                        </span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link d-flex align-items-center" id="confirmed-tab" data-bs-toggle="tab"
                        data-bs-target="#confirmed" type="button" role="tab">
                        <i class="fas fa-check-circle me-2"></i>
                        <span>Đã xác nhận</span>
                        <span class="badge rounded-pill bg-success ms-2">
                            <?php echo count($confirmedAppointments); ?>
                        </span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link d-flex align-items-center" id="examining-tab" data-bs-toggle="tab"
                        data-bs-target="#examining" type="button" role="tab">
                        <i class="fas fa-stethoscope me-2"></i>
                        <span>Đang khám</span>
                        <span class="badge rounded-pill bg-warning text-dark ms-2">
                            <?php echo isset($examiningAppointments) ? count($examiningAppointments) : 0; ?>
                        </span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link d-flex align-items-center" id="completed-tab" data-bs-toggle="tab"
                        data-bs-target="#completed" type="button" role="tab">
                        <i class="fas fa-check-double me-2"></i>
                        <span>Hoàn thành</span>
                        <span class="badge rounded-pill bg-info ms-2">
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

    <!-- Tab Content -->
    <div class="tab-content" id="appointmentTabContent">
        <!-- Pending Appointments -->
                <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" width="100%">
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
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <span
                                            class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3"
                                            style="width:64px;height:64px;">
                                            <i class="fas fa-calendar-times text-warning fa-lg"></i>
                                        </span>
                                        <div class="fw-semibold">Không có lịch hẹn chờ xác nhận</div>
                                        <div class="text-muted small">Các lịch mới đặt sẽ hiển thị tại đây.</div>
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
                                                    <?php echo htmlspecialchars($appointment['ten_benh_nhan']); ?>
                                                </div>
                                                <small
                                                    class="text-muted"><?php echo $appointment['gioi_tinh'] ?? 'N/A'; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($appointment['so_dien_thoai'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 220px;">
                                            <?php echo htmlspecialchars($appointment['ly_do'] ?? 'Không có'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-info"><?php echo htmlspecialchars($appointment['loai_lich'] ?? 'Trực tiếp'); ?></span>
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
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-success"
                                            onclick="updateAppointmentStatus(<?php echo $appointment['id']; ?>, 'Đã xác nhận')">
                                                <i class="fas fa-check me-1"></i>Xác nhận
                                        </button>
                                            <button class="btn btn-outline-danger"
                                            onclick="cancelAppointment(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-times me-1"></i>Từ chối
                                        </button>
                                            <button class="btn btn-outline-secondary"
                                            onclick="viewAppointmentDetails(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-eye me-1"></i>Chi tiết
                                        </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
            </div>
        </div>

        <!-- Confirmed Appointments -->
                <div class="tab-pane fade" id="confirmed" role="tabpanel" aria-labelledby="confirmed-tab">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" width="100%">
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
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <span
                                            class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3"
                                            style="width:64px;height:64px;">
                                            <i class="fas fa-check-circle text-success fa-lg"></i>
                                        </span>
                                        <div class="fw-semibold">Không có lịch hẹn đã xác nhận</div>
                                        <div class="text-muted small">Khi bạn xác nhận lịch, chúng sẽ hiển thị tại đây.
                                        </div>
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
                                                    <?php echo htmlspecialchars($appointment['ten_benh_nhan']); ?>
                                                </div>
                                                <small
                                                    class="text-muted"><?php echo $appointment['gioi_tinh'] ?? 'N/A'; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($appointment['so_dien_thoai'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 220px;">
                                            <?php echo htmlspecialchars($appointment['ly_do'] ?? 'Không có'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-info"><?php echo htmlspecialchars($appointment['loai_lich'] ?? 'Trực tiếp'); ?></span>
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
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-primary"
                                            onclick="completeAppointment(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-check-double me-1"></i>Hoàn thành
                                        </button>
                                            <button class="btn btn-outline-warning"
                                            onclick="cancelAppointment(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-times me-1"></i>Hủy
                                        </button>
                                            <button class="btn btn-outline-secondary"
                                            onclick="viewAppointmentDetails(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-eye me-1"></i>Chi tiết
                                        </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
            </div>
        </div>

        <!-- Examining Appointments -->
                <div class="tab-pane fade" id="examining" role="tabpanel" aria-labelledby="examining-tab">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" width="100%">
                            <thead>
                                <tr>
                                    <th>Ngày giờ</th>
                                    <th>Bệnh nhân</th>
                                    <th>Số điện thoại</th>
                                    <th>Lý do khám</th>
                                    <th>Loại lịch</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($examiningAppointments ?? [])): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <span
                                            class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3"
                                            style="width:64px;height:64px;">
                                            <i class="fas fa-user-md text-info fa-lg"></i>
                                        </span>
                                        <div class="fw-semibold">Không có lịch đang khám</div>
                                    </td>
                                </tr>
                                <?php else: foreach (($examiningAppointments ?? []) as $appointment): ?>
                                <tr>
                                    <td><?php echo formatDateTime($appointment['ngay_hen'], $appointment['gio_hen']); ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div
                                                class="avatar-sm bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <?php echo strtoupper(substr($appointment['ten_benh_nhan'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold">
                                                    <?php echo htmlspecialchars($appointment['ten_benh_nhan']); ?>
                                                </div>
                                                <small
                                                    class="text-muted"><?php echo $appointment['gioi_tinh'] ?? 'N/A'; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($appointment['so_dien_thoai'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 220px;">
                                            <?php echo htmlspecialchars($appointment['ly_do'] ?? ''); ?>
                                        </span>
                                    </td>
                                    <td><span
                                            class="badge bg-info"><?php echo htmlspecialchars($appointment['loai_lich'] ?? 'Trực tiếp'); ?></span>
                                    </td>
                                    <td><span class="badge bg-warning text-dark">Đang khám</span></td>
                                </tr>
                                <?php endforeach;
                                endif; ?>
                            </tbody>
                        </table>
            </div>
        </div>

        <!-- Completed Appointments -->
                <div class="tab-pane fade" id="completed" role="tabpanel" aria-labelledby="completed-tab">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" width="100%">
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
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <span
                                            class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3"
                                            style="width:64px;height:64px;">
                                            <i class="fas fa-check-double text-success fa-lg"></i>
                                        </span>
                                        <div class="fw-semibold">Không có lịch hẹn đã hoàn thành</div>
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
                                                    <?php echo htmlspecialchars($appointment['ten_benh_nhan']); ?>
                                                </div>
                                                <small
                                                    class="text-muted"><?php echo $appointment['gioi_tinh'] ?? 'N/A'; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($appointment['so_dien_thoai'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 220px;">
                                            <?php echo htmlspecialchars($appointment['ly_do'] ?? 'Không có'); ?>
                                        </span>
                                    </td>
                                    <td><span class="badge bg-success">Hoàn thành</span></td>
                                    <td>
                                        <?php if (!empty($appointment['link_tu_van'])): ?>
                                        <a href="<?php echo htmlspecialchars($appointment['link_tu_van']); ?>"
                                            target="_blank" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-video me-1"></i>Xem lại cuộc họp
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted small">Không có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-secondary"
                                            onclick="viewAppointmentDetails(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-eye me-1"></i>Chi tiết
                                        </button>
                                            <button class="btn btn-primary"
                                            onclick="createMedicalRecord(<?php echo $appointment['id']; ?>)">
                                            <i class="fas fa-file-medical me-1"></i>Hồ sơ
                                        </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
            </div>
        </div>

        <!-- Cancelled Appointments -->
                <div class="tab-pane fade" id="cancelled" role="tabpanel" aria-labelledby="cancelled-tab">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" width="100%">
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
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <span
                                            class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3"
                                            style="width:64px;height:64px;">
                                            <i class="fas fa-times-circle text-danger fa-lg"></i>
                                        </span>
                                        <div class="fw-semibold">Không có lịch hẹn đã hủy</div>
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
                                                    <?php echo htmlspecialchars($appointment['ten_benh_nhan']); ?>
                                                </div>
                                                <small
                                                    class="text-muted"><?php echo $appointment['gioi_tinh'] ?? 'N/A'; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($appointment['so_dien_thoai'] ?? 'N/A'); ?></td>
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
                                            <i class="fas fa-video me-1"></i>Xem lại cuộc họp
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted small">Không có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary"
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
        <div class="card-footer bg-white border-0 pt-0">
            <?php
            renderPagination(
                (int) $currentPage,
                (int) $totalPages,
                './doctor_appointment_management'
            );
            ?>
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
                '<div class="mb-2"><strong>Bệnh nhân:</strong> ' + (d.patient_name || '') + '</div>' +
                '<div class="mb-2"><strong>Email:</strong> ' + (d.patient_email || '') + '</div>' +
                '<div class="mb-2"><strong>Số điện thoại:</strong> ' + (d.patient_phone || '') + '</div>' +
                '<div class="mb-2"><strong>Giới tính:</strong> ' + (d.patient_gender || '') + '</div>' +
                '<div class="mb-2"><strong>Địa chỉ:</strong> ' + (d.patient_address || '') + '</div>' +
                '<div class="mb-2"><strong>Ngày hẹn:</strong> ' + (d.ngay_hen || '') + '</div>' +
                '<div class="mb-2"><strong>Giờ hẹn:</strong> ' + (d.gio_hen || '') + '</div>' +
                '<div class="mb-2"><strong>Ngày tạo:</strong> ' + (d.ngay_tao || '') + '</div>';
            showAppointmentModal('Chi tiết lịch hẹn', html);
        })
        .catch(function() {
            alert('Lỗi tải chi tiết lịch hẹn');
        });
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

// Lưu/khôi phục tab đang mở (giống giao diện patient appointments)
document.addEventListener('DOMContentLoaded', function() {
    try {
        var TAB_KEY = 'doctorAppointmentsActiveTab';
        var tabsRoot = document.getElementById('appointmentTabs');
        if (!tabsRoot) return;

        var tabButtons = tabsRoot.querySelectorAll('button[data-bs-target]');

        // Lưu tab khi người dùng chuyển
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

        // Khôi phục tab đã lưu khi F5
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
                        tabButtons.forEach(function(b) {
                            b.classList.remove('active');
                        });
                        savedBtn.classList.add('active');
                        ['pending', 'confirmed', 'examining', 'completed', 'cancelled'].forEach(function(id) {
                            var pane = document.getElementById(id);
                            if (!pane) return;
                            pane.classList.remove('show', 'active');
                        });
                        var activePane = document.getElementById(savedPane);
                        if (activePane) {
                            activePane.classList.add('show', 'active');
                        }
                    }
                } catch (e) {}
            }
        }
    } catch (e) {
        // Nếu localStorage không khả dụng thì bỏ qua
    }
});

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

<script>
function showAppointmentModal(title, bodyHtml) {
    try {
        var old = document.getElementById('appt-detail-modal');
        if (old && old.parentNode) old.parentNode.removeChild(old);
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