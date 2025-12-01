<?php
require_once 'Views/layouts/layout_helper.php';
require_once 'config/database.php';
require_once 'Models/Appointment.php';
require_once 'Models/Prescription.php';
require_once 'Models/Doctor.php';

$ctx = getCurrentUserContext();
$doctorName = htmlspecialchars(($ctx['name'] ?? ''), ENT_QUOTES, 'UTF-8');
$doctorId = $_SESSION['user_id'] ?? null;
$specialization = '';

// Lấy thông tin bác sĩ
try {
    if (($ctx['role'] ?? '') === 'doctor' || ($ctx['role'] ?? '') === 'xray_doctor' || ($ctx['role'] ?? '') === 'sieuam_doctor' || ($ctx['role'] ?? '') === 'xetnghiem_doctor') {
        $doctorModel = new Doctor();
        $doctor = $doctorModel->getById($doctorId);
        $specialization = $doctor['chuyen_khoa'] ?? '';
    }
} catch (Exception $e) {
    error_log("Error getting doctor info: " . $e->getMessage());
}

// Lấy dữ liệu thống kê
$stats = [
    'appointments_today' => 0,
    'examining_patients' => 0,
    'completed_today' => 0,
    'pending_today' => 0,
    'confirmed_today' => 0,
    'total_prescriptions' => 0,
    'today_appointments' => [],
    'next_patient' => null
];

try {
    $database = new Database();
    $db = $database->getConnection();
    $today = date('Y-m-d');

    // Lấy thống kê lịch hẹn hôm nay
    $appointmentModel = new Appointment();
    $todayStats = $appointmentModel->getTodayStatsByDoctor($doctorId);
    $stats['appointments_today'] = (int)($todayStats['total'] ?? 0);
    $stats['pending_today'] = (int)($todayStats['pending'] ?? 0);
    $stats['confirmed_today'] = (int)($todayStats['confirmed'] ?? 0);
    $stats['completed_today'] = (int)($todayStats['completed'] ?? 0);

    // Lấy lịch hẹn đang khám
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM lich_hen 
        WHERE bac_si_id = ? 
        AND ngay_hen = ? 
        AND trang_thai = 'Đang khám'
        AND loai_lich != 'Tư vấn'
    ");
    $stmt->execute([$doctorId, $today]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['examining_patients'] = (int)($result['total'] ?? 0);

    // Lấy danh sách lịch hẹn hôm nay
    $todayAppointments = $appointmentModel->getTodayAppointmentsByDoctor($doctorId);

    // Sắp xếp theo giờ hẹn
    usort($todayAppointments, function ($a, $b) {
        $timeA = $a['gio_hen'] ?? '';
        $timeB = $b['gio_hen'] ?? '';
        return strcmp($timeA, $timeB);
    });

    $stats['today_appointments'] = array_slice($todayAppointments, 0, 5);
    $stats['next_patient'] = !empty($todayAppointments) ? $todayAppointments[0] : null;

    // Đếm đơn thuốc đã kê (hôm nay)
    try {
        $prescriptionModel = new Prescription();
        $prescriptions = $prescriptionModel->getDoctorPrescriptions($doctorId, $today, $today);
        $stats['total_prescriptions'] = count($prescriptions);
    } catch (Exception $e) {
        error_log("Error getting prescriptions: " . $e->getMessage());
    }
} catch (Exception $e) {
    error_log("Error getting doctor dashboard stats: " . $e->getMessage());
}

// Helper function
function formatTime($time)
{
    if (empty($time)) return '';
    return date('H:i', strtotime($time));
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
                <i class="fas fa-user-md text-primary me-2"></i>
                Dashboard Bác sĩ
            </h1>
            <p class="mb-0 text-muted">Chào mừng bác sĩ ' . $doctorName . ' - ' . htmlspecialchars($specialization ?: 'Chuyên khoa') . '</p>
        </div>
        <div class="d-flex gap-2">
            <a href="./doctor_today_appointments" class="btn btn-primary">
                <i class="fas fa-calendar-check me-2"></i>Lịch hẹn hôm nay
            </a>
            <a href="./doctor_examination" class="btn btn-outline-primary">
                <i class="fas fa-stethoscope me-2"></i>Khám bệnh
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
                        <div class="card-label">Lịch hẹn hôm nay</div>
                        <div class="card-value">' . number_format($stats['appointments_today']) . '</div>
                        ' . ($stats['confirmed_today'] > 0 ? '<small class="text-muted"><i class="fas fa-check-circle text-success"></i> ' . $stats['confirmed_today'] . ' đã xác nhận</small>' : '') . '
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-circle bg-soft-info me-3">
                        <i class="fas fa-user-injured"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="card-label">Đang khám</div>
                        <div class="card-value">' . number_format($stats['examining_patients']) . '</div>
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
                        <div class="card-label">Đã khám hôm nay</div>
                        <div class="card-value">' . number_format($stats['completed_today']) . '</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-circle bg-soft-warning me-3">
                        <i class="fas fa-prescription"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="card-label">Đơn thuốc hôm nay</div>
                        <div class="card-value">' . number_format($stats['total_prescriptions']) . '</div>
                        ' . ($stats['pending_today'] > 0 ? '<small class="text-muted"><i class="fas fa-clock text-warning"></i> ' . $stats['pending_today'] . ' chờ xác nhận</small>' : '') . '
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Lịch hẹn hôm nay -->
        <div class="col-xl-8">
            <div class="card shadow-sm border-0">
                <div class="card-header border-0 d-flex justify-content-between align-items-center bg-white">
                    <div>
                        <h6 class="mb-0 fw-semibold">Lịch hẹn hôm nay</h6>
                        <small class="text-muted">Danh sách bệnh nhân đã đặt lịch khám</small>
                    </div>
                    <a href="./doctor_today_appointments" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-list me-1"></i>Xem tất cả
                    </a>
                </div>
                <div class="card-body">';

if (!empty($stats['today_appointments'])) {
    $content .= '<div class="list-group list-group-flush">';
    foreach ($stats['today_appointments'] as $apt) {
        $aptTime = $apt['gio_hen'] ?? '';
        $status = $apt['trang_thai'] ?? '';
        $patientName = $apt['ten_benh_nhan'] ?? 'Bệnh nhân';
        $patientPhone = $apt['so_dien_thoai'] ?? '';
        $formattedTime = formatTime($aptTime);
        $badgeClass = getStatusBadge($status);

        $content .= '
        <div class="list-group-item border-0 px-0 py-3">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <div class="bg-soft-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-user-injured text-primary"></i>
                    </div>
                </div>
                <div class="flex-grow-1 ms-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 fw-semibold">' . htmlspecialchars($patientName) . '</h6>
                            <p class="mb-1 text-muted small">
                                <i class="fas fa-phone me-1"></i>' . htmlspecialchars($patientPhone) . '
                            </p>
                            <p class="mb-0 text-muted small">
                                <i class="fas fa-clock me-1"></i>' . $formattedTime . '
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
        <p class="text-muted mb-0">Bạn không có lịch hẹn nào hôm nay</p>
        <a href="./doctor_schedule_management" class="btn btn-primary mt-3">
            <i class="fas fa-calendar-alt me-2"></i>Đăng ký lịch làm việc
        </a>
    </div>';
}

$content .= '
                </div>
            </div>
        </div>

        <!-- Next Patient & Quick Actions -->
        <div class="col-xl-4">
            <!-- Next Patient Card -->
            ' . (!empty($stats['next_patient']) ? '
            <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #0d6efd !important;">
                <div class="card-header border-0 bg-white">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-user-injured text-primary me-2"></i>Bệnh nhân tiếp theo
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-soft-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 64px; height: 64px;">
                            <i class="fas fa-user-injured fa-2x text-primary"></i>
                        </div>
                        <h5 class="mb-1">' . htmlspecialchars($stats['next_patient']['ten_benh_nhan'] ?? 'Bệnh nhân') . '</h5>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-phone me-1"></i>' . htmlspecialchars($stats['next_patient']['so_dien_thoai'] ?? '') . '
                        </p>
                    </div>
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><i class="fas fa-clock me-2"></i>Giờ hẹn:</span>
                            <strong>' . formatTime($stats['next_patient']['gio_hen'] ?? '') . '</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted"><i class="fas fa-info-circle me-2"></i>Trạng thái:</span>
                            <span class="badge ' . getStatusBadge($stats['next_patient']['trang_thai'] ?? '') . '">' . htmlspecialchars($stats['next_patient']['trang_thai'] ?? '') . '</span>
                        </div>
                        <div class="mt-3">
                            <a href="./doctor_examination?appointment_id=' . ($stats['next_patient']['id'] ?? '') . '" class="btn btn-primary w-100">
                                <i class="fas fa-stethoscope me-2"></i>Bắt đầu khám
                            </a>
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
                        <a href="./doctor_today_appointments" class="btn btn-outline-primary text-start">
                            <i class="fas fa-calendar-check me-2"></i>Lịch hẹn hôm nay
                        </a>
                        <a href="./doctor_examination" class="btn btn-outline-primary text-start">
                            <i class="fas fa-stethoscope me-2"></i>Khám bệnh
                        </a>
                        <a href="./doctor_medical_records" class="btn btn-outline-primary text-start">
                            <i class="fas fa-file-medical me-2"></i>Hồ sơ bệnh án
                        </a>
                        <a href="./doctor_schedule_management" class="btn btn-outline-primary text-start">
                            <i class="fas fa-calendar-alt me-2"></i>Lịch làm việc
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

renderLayout($content, 'Dashboard Bác sĩ - Hệ thống Quản lý Phòng khám');