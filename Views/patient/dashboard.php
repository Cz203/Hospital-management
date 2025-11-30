<?php
require_once 'Views/layouts/layout_helper.php';
require_once 'config/database.php';
require_once 'Models/Appointment.php';
require_once 'Models/MedicalRecord.php';
require_once 'Models/BienLai.php';

$ctx = getCurrentUserContext();
$displayName = htmlspecialchars($ctx['name'] ?? 'Bệnh nhân', ENT_QUOTES, 'UTF-8');
$patientId = $_SESSION['user_id'] ?? null;

// Lấy dữ liệu thống kê
$stats = [
    'upcoming_appointments' => 0,
    'completed_appointments' => 0,
    'pending_appointments' => 0,
    'total_medical_records' => 0,
    'total_receipts' => 0,
    'unpaid_receipts' => 0,
    'next_appointment' => null,
    'recent_appointments' => []
];

try {
    $database = new Database();
    $db = $database->getConnection();
    $today = date('Y-m-d');

    // Lấy tất cả lịch hẹn của bệnh nhân
    $appointmentModel = new Appointment();
    $allAppointments = $appointmentModel->getByPatientId($patientId) ?: [];

    // Phân loại lịch hẹn
    foreach ($allAppointments as $apt) {
        $aptDate = $apt['ngay_hen'] ?? '';
        $status = $apt['trang_thai'] ?? '';

        if ($status === 'Hoàn thành') {
            $stats['completed_appointments']++;
        } elseif (in_array($status, ['Chờ xác nhận', 'Đã xác nhận', 'Đang khám'])) {
            if ($aptDate >= $today) {
                $stats['upcoming_appointments']++;
                if ($status === 'Chờ xác nhận') {
                    $stats['pending_appointments']++;
                }
            }
        }
    }

    // Lịch hẹn sắp tới (5 lịch gần nhất)
    $upcomingList = array_filter($allAppointments, function ($apt) use ($today) {
        $aptDate = $apt['ngay_hen'] ?? '';
        $status = $apt['trang_thai'] ?? '';
        return $aptDate >= $today && in_array($status, ['Chờ xác nhận', 'Đã xác nhận', 'Đang khám']);
    });
    usort($upcomingList, function ($a, $b) {
        $dateA = ($a['ngay_hen'] ?? '') . ' ' . ($a['gio_hen'] ?? '');
        $dateB = ($b['ngay_hen'] ?? '') . ' ' . ($b['gio_hen'] ?? '');
        return strcmp($dateA, $dateB);
    });
    $stats['recent_appointments'] = array_slice($upcomingList, 0, 5);
    $stats['next_appointment'] = !empty($upcomingList) ? $upcomingList[0] : null;

    // Đếm hồ sơ bệnh án
    $medicalRecordModel = new MedicalRecord();
    $records = $medicalRecordModel->getRecordsByPatient($patientId, []) ?? [];
    $stats['total_medical_records'] = count($records['data'] ?? []);

    // Đếm biên lai
    $bienLaiModel = new BienLai();
    $receipts = $bienLaiModel->getReceiptsByPatient($patientId, 1000, 0) ?? [];
    $stats['total_receipts'] = count($receipts);
    $stats['unpaid_receipts'] = count(array_filter($receipts, function ($r) {
        return !in_array($r['trang_thai'] ?? '', ['Đã thanh toán tiền mặt', 'Đã thanh toán chuyển khoản']);
    }));
} catch (Exception $e) {
    error_log("Error getting patient dashboard stats: " . $e->getMessage());
}

// Helper function
function formatDateTime($date, $time)
{
    if (empty($date)) return '';
    $dateTime = trim($date . ' ' . ($time ?? ''));
    return date('d/m/Y H:i', strtotime($dateTime));
}

function getStatusBadge($status)
{
    $badges = [
        'Đã xác nhận' => 'bg-success',
        'Chờ xác nhận' => 'bg-warning text-dark',
        'Đang khám' => 'bg-info',
        'Hoàn thành' => 'bg-primary',
        'hủy' => 'bg-danger'
    ];
    return $badges[$status] ?? 'bg-secondary';
}

$content = '
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-semibold">
                <i class="fas fa-user-injured text-primary me-2"></i>
                Dashboard Bệnh nhân
            </h1>
            <p class="mb-0 text-muted">Chào mừng ' . $displayName . ' - Chăm sóc sức khỏe của bạn</p>
        </div>
        <div class="d-flex gap-2">
            <a href="./doctor_team" class="btn btn-primary">
                <i class="fas fa-calendar-plus me-2"></i>Đặt lịch hẹn
            </a>
            <a href="./patient_medical_records" class="btn btn-outline-primary">
                <i class="fas fa-file-medical me-2"></i>Hồ sơ bệnh án
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-circle bg-soft-primary me-3">
                        <i class="fas fa-calendar-check"></i>
                            </div>
                    <div class="flex-grow-1">
                        <div class="card-label">Lịch hẹn sắp tới</div>
                        <div class="card-value">' . number_format($stats['upcoming_appointments']) . '</div>
                        ' . ($stats['pending_appointments'] > 0 ? '<small class="text-muted"><i class="fas fa-clock text-warning"></i> ' . $stats['pending_appointments'] . ' chờ xác nhận</small>' : '') . '
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-circle bg-soft-success me-3">
                        <i class="fas fa-check-circle"></i>
                            </div>
                    <div class="flex-grow-1">
                        <div class="card-label">Lịch hẹn đã hoàn thành</div>
                        <div class="card-value">' . number_format($stats['completed_appointments']) . '</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-circle bg-soft-info me-3">
                        <i class="fas fa-file-medical"></i>
                            </div>
                    <div class="flex-grow-1">
                        <div class="card-label">Hồ sơ bệnh án</div>
                        <div class="card-value">' . number_format($stats['total_medical_records']) . '</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-circle bg-soft-warning me-3">
                        <i class="fas fa-receipt"></i>
                            </div>
                    <div class="flex-grow-1">
                        <div class="card-label">Biên lai chưa thanh toán</div>
                        <div class="card-value">' . number_format($stats['unpaid_receipts']) . '</div>
                        ' . ($stats['total_receipts'] > 0 ? '<small class="text-muted">Tổng: ' . $stats['total_receipts'] . ' biên lai</small>' : '') . '
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Lịch hẹn sắp tới -->
        <div class="col-xl-8">
            <div class="card shadow-sm border-0">
                <div class="card-header border-0 d-flex justify-content-between align-items-center bg-white">
                    <div>
                        <h6 class="mb-0 fw-semibold">Lịch hẹn sắp tới</h6>
                        <small class="text-muted">Danh sách lịch hẹn khám bệnh của bạn</small>
                    </div>
                    <a href="./patient_appointments" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-list me-1"></i>Xem tất cả
                    </a>
                </div>
                <div class="card-body">';

if (!empty($stats['recent_appointments'])) {
    $content .= '<div class="list-group list-group-flush">';
    foreach ($stats['recent_appointments'] as $apt) {
        $aptDate = $apt['ngay_hen'] ?? '';
        $aptTime = $apt['gio_hen'] ?? '';
        $status = $apt['trang_thai'] ?? '';
        $doctorName = $apt['ten_bac_si'] ?? 'Bác sĩ';
        $specialty = $apt['chuyen_khoa'] ?? '';
        $formattedDate = formatDateTime($aptDate, $aptTime);
        $badgeClass = getStatusBadge($status);

        $content .= '
        <div class="list-group-item border-0 px-0 py-3">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <div class="bg-soft-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-calendar-alt text-primary"></i>
                    </div>
                </div>
                <div class="flex-grow-1 ms-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 fw-semibold">' . htmlspecialchars($doctorName) . '</h6>
                            <p class="mb-1 text-muted small">
                                <i class="fas fa-stethoscope me-1"></i>' . htmlspecialchars($specialty) . '
                            </p>
                            <p class="mb-0 text-muted small">
                                <i class="fas fa-clock me-1"></i>' . $formattedDate . '
                            </p>
                        </div>
                        <span class="badge ' . $badgeClass . '">' . htmlspecialchars($status) . '</span>
                    </div>
                </div>
            </div>
        </div>';
    }
    $content .= '</div>';
} else {
    $content .= '
    <div class="text-center py-5">
        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
        <p class="text-muted mb-0">Bạn chưa có lịch hẹn nào sắp tới</p>
        <a href="./doctor_team" class="btn btn-primary mt-3">
            <i class="fas fa-calendar-plus me-2"></i>Đặt lịch hẹn ngay
        </a>
    </div>';
}

$content .= '
                </div>
            </div>
        </div>

        <!-- Quick Actions & Next Appointment -->
        <div class="col-xl-4">
            <!-- Next Appointment Card -->
            ' . (!empty($stats['next_appointment']) ? '
            <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #0d6efd !important;">
                <div class="card-header border-0 bg-white">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-calendar-check text-primary me-2"></i>Lịch hẹn tiếp theo
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-soft-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 64px; height: 64px;">
                            <i class="fas fa-user-md fa-2x text-primary"></i>
                        </div>
                        <h5 class="mb-1">' . htmlspecialchars($stats['next_appointment']['ten_bac_si'] ?? 'Bác sĩ') . '</h5>
                        <p class="text-muted small mb-2">' . htmlspecialchars($stats['next_appointment']['chuyen_khoa'] ?? '') . '</p>
                    </div>
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><i class="fas fa-calendar me-2"></i>Ngày:</span>
                            <strong>' . date('d/m/Y', strtotime($stats['next_appointment']['ngay_hen'] ?? '')) . '</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><i class="fas fa-clock me-2"></i>Giờ:</span>
                            <strong>' . htmlspecialchars($stats['next_appointment']['gio_hen'] ?? '') . '</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted"><i class="fas fa-info-circle me-2"></i>Trạng thái:</span>
                            <span class="badge ' . getStatusBadge($stats['next_appointment']['trang_thai'] ?? '') . '">' . htmlspecialchars($stats['next_appointment']['trang_thai'] ?? '') . '</span>
                        </div>
                    </div>
                </div>
            </div>
            ' : '') . '

            <!-- Quick Actions -->
            <div class="card shadow-sm border-0">
                <div class="card-header border-0 bg-white">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-bolt text-primary me-2"></i>Thao tác nhanh
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="./doctor_team" class="btn btn-outline-primary text-start">
                            <i class="fas fa-calendar-plus me-2"></i>Đặt lịch hẹn mới
                        </a>
                        <a href="./patient_appointments" class="btn btn-outline-primary text-start">
                            <i class="fas fa-list me-2"></i>Xem lịch hẹn của tôi
                        </a>
                        <a href="./patient_medical_records" class="btn btn-outline-primary text-start">
                            <i class="fas fa-file-medical me-2"></i>Hồ sơ bệnh án
                        </a>
                        <a href="./patient_receipts" class="btn btn-outline-primary text-start">
                            <i class="fas fa-receipt me-2"></i>Biên lai viện phí
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.dashboard-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
}

.icon-circle {
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.5rem;
}

.bg-soft-primary {
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}

.bg-soft-success {
    background-color: rgba(25, 135, 84, 0.1);
    color: #198754;
}

.bg-soft-info {
    background-color: rgba(13, 202, 240, 0.1);
    color: #0dcaf0;
}

.bg-soft-warning {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.card-label {
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.card-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #212529;
    line-height: 1.2;
}

.list-group-item {
    transition: background-color 0.2s ease;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}
</style>
';

renderLayout($content, 'Dashboard Bệnh nhân - Hệ thống Quản lý Phòng khám');