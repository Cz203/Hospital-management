<?php
// Lấy dữ liệu lịch hẹn từ controller
$upcomingAppointments = $upcomingAppointments ?? [];
$completedAppointments = $completedAppointments ?? [];
$cancelledAppointments = $cancelledAppointments ?? [];


// Include layout helper
require_once 'Views/layouts/layout_helper.php';

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
            return 'bg-success';
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
                Lịch hẹn của tôi
            </h1>
            <p class="text-muted">Quản lý lịch hẹn khám bệnh</p>
        </div>
        <div class="d-flex gap-2">
            <a href="./doctor_team" class="btn btn-primary">
                <i class="fas fa-calendar-plus me-2"></i>Đặt lịch hẹn mới
            </a>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <ul class="nav nav-tabs" id="appointmentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming"
                        type="button" role="tab">
                        <i class="fas fa-clock me-2"></i>Lịch hẹn sắp tới
                        <span class="badge bg-primary ms-2"><?php echo count($upcomingAppointments); ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed"
                        type="button" role="tab">
                        <i class="fas fa-check-circle me-2"></i>Đã hoàn thành
                        <span class="badge bg-success ms-2"><?php echo count($completedAppointments); ?></span>
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
        <!-- Upcoming Appointments -->
        <div class="tab-pane fade show active" id="upcoming" role="tabpanel">
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ngày giờ</th>
                                    <th>Bác sĩ</th>
                                    <th>Chuyên khoa</th>
                                    <th>Lý do khám</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($upcomingAppointments)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-calendar-times fa-2x mb-2"></i><br>
                                            Chưa có lịch hẹn sắp tới
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($upcomingAppointments as $appointment): ?>
                                        <tr>
                                            <td><?php echo formatDateTime($appointment['ngay_hen'], $appointment['gio_hen']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($appointment['ten_bac_si'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['chuyen_khoa'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['ly_do'] ?? 'Không có'); ?></td>
                                            <td><span
                                                    class="badge <?php echo getStatusBadgeClass($appointment['trang_thai']); ?>"><?php echo getStatusText($appointment['trang_thai']); ?></span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info"
                                                    onclick="viewAppointmentDetails(<?php echo $appointment['id']; ?>)">Xem chi
                                                    tiết</button>
                                                <?php if (in_array($appointment['trang_thai'], ['Chờ xác nhận', 'Đã xác nhận'])): ?>
                                                    <button class="btn btn-sm btn-warning"
                                                        onclick="cancelAppointment(<?php echo $appointment['id']; ?>)">Hủy
                                                        lịch</button>
                                                <?php endif; ?>
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
                                    <th>Bác sĩ</th>
                                    <th>Chuyên khoa</th>
                                    <th>Lý do khám</th>
                                    <th>Kết quả</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($completedAppointments)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                                            Chưa có lịch hẹn đã hoàn thành
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($completedAppointments as $appointment): ?>
                                        <tr>
                                            <td><?php echo formatDateTime($appointment['ngay_hen'], $appointment['gio_hen']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($appointment['ten_bac_si'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['chuyen_khoa'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['ly_do'] ?? 'Không có'); ?></td>
                                            <td><span class="badge bg-success">Hoàn thành</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-info"
                                                    onclick="viewAppointmentDetails(<?php echo $appointment['id']; ?>)">Xem chi
                                                    tiết</button>
                                                <button class="btn btn-sm btn-primary"
                                                    onclick="downloadReport(<?php echo $appointment['id']; ?>)">Tải về</button>
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
                                    <th>Bác sĩ</th>
                                    <th>Chuyên khoa</th>
                                    <th>Lý do khám</th>
                                    <th>Lý do hủy</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($cancelledAppointments)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-times-circle fa-2x mb-2"></i><br>
                                            Chưa có lịch hẹn đã hủy
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($cancelledAppointments as $appointment): ?>
                                        <tr>
                                            <td><?php echo formatDateTime($appointment['ngay_hen'], $appointment['gio_hen']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($appointment['ten_bac_si'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['chuyen_khoa'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['ly_do'] ?? 'Không có'); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['ghi_chu'] ?? 'Không có lý do'); ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info"
                                                    onclick="viewAppointmentDetails(<?php echo $appointment['id']; ?>)">Xem chi
                                                    tiết</button>
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
<?php
// Get content from output buffer
$content = ob_get_clean();

// Render layout với content
renderLayout($content, 'Lịch hẹn của tôi - Hệ thống Quản lý Bệnh viện');
?>

<script>
    // Mark this page so socket client can reliably detect patient appointments page
    document.addEventListener('DOMContentLoaded', function() {
        if (document && document.body) {
            document.body.dataset.page = 'patient_appointments';
        }
    });

    function viewAppointmentDetails(appointmentId) {
        // TODO: Implement view appointment details modal
        alert("Xem chi tiết lịch hẹn ID: " + appointmentId);
    }

    function cancelAppointment(appointmentId) {
        if (confirm("Bạn có chắc chắn muốn hủy lịch hẹn này?")) {
            // Tạo form ẩn để submit
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
    }

    function downloadReport(appointmentId) {
        // TODO: Implement download report
        alert("Tải báo cáo lịch hẹn ID: " + appointmentId);
    }
</script>