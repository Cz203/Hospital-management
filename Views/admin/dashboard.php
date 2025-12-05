<?php
// File này sẽ được include từ AdminController
// Không cần kiểm tra session ở đây vì đã được kiểm tra trong Controller
require_once __DIR__ . '/../layouts/layout_helper.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-tachometer-alt text-primary me-2"></i>Dashboard Admin
            </h1>
            <?php $ctx = getCurrentUserContext();
            $adminName = htmlspecialchars(($ctx['name'] ?? 'Admin'), ENT_QUOTES, 'UTF-8'); ?>
            <p class="mb-0 text-muted">Chào mừng trở lại, <?php echo $adminName; ?>!</p>
        </div>
    </div>

    <!-- Summary cards - Hàng 1: Chỉ số chính -->
    <div class="row g-3 mb-3">
        <div class="col-xl col-md-6 col-sm-12">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-circle bg-soft-primary me-3">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="card-label">Tổng bác sĩ</div>
                        <div class="card-value">
                            <?php echo number_format($stats['total_doctors'] ?? 0); ?>
                        </div>
                        <?php
                        $onDutyShifts = $stats['on_duty_doctors_shifts'] ?? ['Ca sáng' => 0, 'Ca chiều' => 0];
                        $totalOnDuty = ($onDutyShifts['Ca sáng'] ?? 0) + ($onDutyShifts['Ca chiều'] ?? 0);
                        $morningCount = $onDutyShifts['Ca sáng'] ?? 0;
                        $afternoonCount = $onDutyShifts['Ca chiều'] ?? 0;
                        if ($totalOnDuty > 0):
                            $tooltipContent = '';
                            if ($morningCount > 0) {
                                $tooltipContent .= '<span><i class="fas fa-sun text-warning me-1"></i>' . $morningCount . ' ca sáng</span>';
                            }
                            if ($afternoonCount > 0) {
                                if ($morningCount > 0) $tooltipContent .= '<br>';
                                $tooltipContent .= '<span><i class="fas fa-moon text-info me-1"></i>' . $afternoonCount . ' ca chiều</span>';
                            }
                        ?>
                        <small class="text-muted d-block mt-1 on-duty-tooltip" data-bs-toggle="tooltip"
                            data-bs-placement="top" data-bs-html="true"
                            data-bs-title="<?php echo htmlspecialchars($tooltipContent, ENT_QUOTES); ?>"
                            style="cursor: help;">
                            <i class="fas fa-circle text-success" style="font-size: 6px;"></i>
                            <?php echo $totalOnDuty; ?> đang trực
                        </small>
                        <?php endif; ?>
                        <?php
                        // Đếm số bác sĩ theo trạng thái truy cập
                        if (!empty($doctorsWithAccessStatus)) {
                            $activeCount = 0;
                            $inactiveCount = 0;
                            $blockedCount = 0;
                            $suspendedCount = 0;

                            foreach ($doctorsWithAccessStatus as $doctor) {
                                $status = $doctor['trang_thai_truy_cap'] ?? 'active';
                                switch ($status) {
                                    case 'active':
                                        $activeCount++;
                                        break;
                                    case 'inactive':
                                        $inactiveCount++;
                                        break;
                                    case 'blocked':
                                        $blockedCount++;
                                        break;
                                    case 'suspended':
                                        $suspendedCount++;
                                        break;
                                }
                            }

                            if ($activeCount > 0): ?>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-circle text-success" style="font-size: 6px;"></i>
                            <?php echo $activeCount; ?> hoạt động
                        </small>
                        <?php endif;
                            if ($blockedCount > 0): ?>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-circle text-danger" style="font-size: 6px;"></i>
                            <?php echo $blockedCount; ?> bị chặn
                        </small>
                        <?php endif;
                            if ($suspendedCount > 0): ?>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-circle text-warning" style="font-size: 6px;"></i>
                            <?php echo $suspendedCount; ?> tạm khóa
                        </small>
                        <?php endif;
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-6 col-sm-12">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-circle bg-soft-success me-3">
                        <i class="fas fa-user-injured"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="card-label">Tổng bệnh nhân</div>
                        <div class="card-value">
                            <?php echo number_format($stats['total_patients'] ?? 0); ?>
                        </div>
                        <?php if (isset($stats['new_patients_today']) && $stats['new_patients_today'] > 0): ?>
                        <small class="text-muted">
                            <i class="fas fa-plus-circle text-success"></i>
                            +<?php echo $stats['new_patients_today']; ?> mới hôm nay
                        </small>
                        <?php endif; ?>
                        <?php
                        // Đếm số bệnh nhân theo trạng thái truy cập
                        if (!empty($patientsWithAccessStatus)) {
                            $activeCount = 0;
                            $inactiveCount = 0;
                            $blockedCount = 0;
                            $suspendedCount = 0;

                            foreach ($patientsWithAccessStatus as $patient) {
                                $status = $patient['trang_thai_truy_cap'] ?? 'active';
                                switch ($status) {
                                    case 'active':
                                        $activeCount++;
                                        break;
                                    case 'inactive':
                                        $inactiveCount++;
                                        break;
                                    case 'blocked':
                                        $blockedCount++;
                                        break;
                                    case 'suspended':
                                        $suspendedCount++;
                                        break;
                                }
                            }

                            if ($activeCount > 0): ?>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-circle text-success" style="font-size: 6px;"></i>
                            <?php echo $activeCount; ?> hoạt động
                        </small>
                        <?php endif;
                            if ($blockedCount > 0): ?>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-circle text-danger" style="font-size: 6px;"></i>
                            <?php echo $blockedCount; ?> bị chặn
                        </small>
                        <?php endif;
                            if ($suspendedCount > 0): ?>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-circle text-warning" style="font-size: 6px;"></i>
                            <?php echo $suspendedCount; ?> tạm khóa
                        </small>
                        <?php endif;
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-6 col-sm-12">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-circle bg-soft-info me-3">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="card-label">Tổng lịch hẹn</div>
                        <div class="card-value">
                            <?php echo number_format($stats['total_appointments'] ?? 0); ?>
                        </div>
                        <?php
                        $appointmentsStatus = $stats['appointments_status'] ?? [];
                        $pending = $appointmentsStatus['pending'] ?? 0;
                        $confirmed = $appointmentsStatus['confirmed'] ?? 0;
                        $examining = $appointmentsStatus['examining'] ?? 0;
                        $completed = $appointmentsStatus['completed'] ?? 0;
                        $cancelled = $appointmentsStatus['cancelled'] ?? 0;
                        $totalToday = $stats['total_appointments_today'] ?? 0;

                        // Tạo nội dung tooltip
                        $tooltipContent = '';
                        if ($pending > 0) {
                            $tooltipContent .= '<span><i class="fas fa-clock text-info me-1"></i>' . $pending . ' chờ xác nhận</span>';
                        }
                        if ($confirmed > 0) {
                            if ($tooltipContent) $tooltipContent .= '<br>';
                            $tooltipContent .= '<span><i class="fas fa-check-circle text-success me-1"></i>' . $confirmed . ' đã xác nhận</span>';
                        }
                        if ($examining > 0) {
                            if ($tooltipContent) $tooltipContent .= '<br>';
                            $tooltipContent .= '<span><i class="fas fa-user-md text-warning me-1"></i>' . $examining . ' đang khám</span>';
                        }
                        if ($completed > 0) {
                            if ($tooltipContent) $tooltipContent .= '<br>';
                            $tooltipContent .= '<span><i class="fas fa-check-double text-primary me-1"></i>' . $completed . ' hoàn thành</span>';
                        }
                        if ($cancelled > 0) {
                            if ($tooltipContent) $tooltipContent .= '<br>';
                            $tooltipContent .= '<span><i class="fas fa-times-circle text-danger me-1"></i>' . $cancelled . ' đã hủy</span>';
                        }
                        ?>
                        <small class="text-muted appointment-tooltip" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-html="true"
                            data-bs-title="<?php echo htmlspecialchars($tooltipContent, ENT_QUOTES); ?>"
                            style="cursor: help;">
                            <i class="fas fa-calendar-day me-1"></i>
                            Hôm nay:
                            <strong><?php echo number_format($totalToday); ?></strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-6 col-sm-12">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-circle bg-soft-warning me-3">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="card-label">Tổng doanh thu</div>
                        <div class="card-value">
                            <?php echo number_format($stats['total_revenue_all'] ?? 0); ?> <span class="unit">VNĐ</span>
                        </div>
                        <?php if (isset($stats['revenue_today']) && $stats['revenue_today'] > 0): ?>
                        <small class="text-muted">
                            Hôm nay: <?php echo number_format($stats['revenue_today']); ?> VNĐ
                        </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-6 col-sm-12">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-circle bg-soft-secondary me-3">
                        <i class="fas fa-user-nurse"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="card-label">Tổng lễ tân</div>
                        <div class="card-value">
                            <?php echo number_format($stats['total_receptions'] ?? 0); ?>
                        </div>
                        <?php if (isset($stats['on_duty_receptions']) && $stats['on_duty_receptions'] > 0): ?>
                        <small class="text-muted ">
                            <i class="fas fa-circle text-success" style="font-size: 6px;"></i>
                            <?php echo $stats['on_duty_receptions']; ?> đang trực
                        </small>
                        <?php endif; ?>
                        <?php
                        // Đếm số lễ tân theo trạng thái truy cập
                        if (!empty($receptionsWithSchedule)) {
                            $activeCount = 0;
                            $inactiveCount = 0;
                            $blockedCount = 0;
                            $suspendedCount = 0;

                            foreach ($receptionsWithSchedule as $item) {
                                $reception = $item['reception'];
                                $status = $reception['trang_thai_truy_cap'] ?? 'active';
                                switch ($status) {
                                    case 'active':
                                        $activeCount++;
                                        break;
                                    case 'inactive':
                                        $inactiveCount++;
                                        break;
                                    case 'blocked':
                                        $blockedCount++;
                                        break;
                                    case 'suspended':
                                        $suspendedCount++;
                                        break;
                                }
                            }

                            if ($activeCount > 0): ?>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-circle text-success" style="font-size: 6px;"></i>
                            <?php echo $activeCount; ?> hoạt động
                        </small>
                        <?php endif;
                            if ($blockedCount > 0): ?>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-circle text-danger" style="font-size: 6px;"></i>
                            <?php echo $blockedCount; ?> bị chặn
                        </small>
                        <?php endif;
                            if ($suspendedCount > 0): ?>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-circle text-warning" style="font-size: 6px;"></i>
                            <?php echo $suspendedCount; ?> tạm khóa
                        </small>
                        <?php endif;
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary cards - Hàng 2: Chỉ số dịch vụ & hoạt động -->
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="icon-circle bg-soft-danger mx-auto mb-2">
                        <i class="fas fa-x-ray"></i>
                    </div>
                    <div class="card-label">X-Quang</div>
                    <div class="card-value"><?php echo number_format($stats['xray_today'] ?? 0); ?></div>
                    <small class="text-muted">Hôm nay</small>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="icon-circle bg-soft-info mx-auto mb-2">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <div class="card-label">Siêu âm</div>
                    <div class="card-value"><?php echo number_format($stats['ultrasound_today'] ?? 0); ?></div>
                    <small class="text-muted">Hôm nay</small>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="icon-circle bg-soft-primary mx-auto mb-2">
                        <i class="fas fa-vial"></i>
                    </div>
                    <div class="card-label">Xét nghiệm</div>
                    <div class="card-value"><?php echo number_format($stats['lab_today'] ?? 0); ?></div>
                    <small class="text-muted">Hôm nay</small>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="icon-circle bg-soft-success mx-auto mb-2">
                        <i class="fas fa-pills"></i>
                    </div>
                    <div class="card-label">Đơn thuốc</div>
                    <div class="card-value"><?php echo number_format($stats['prescriptions_today'] ?? 0); ?></div>
                    <small class="text-muted">Hôm nay</small>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="icon-circle bg-soft-success mx-auto mb-2">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="card-label">Đã thanh toán</div>
                    <div class="card-value"><?php echo number_format($stats['paid_receipts'] ?? 0); ?></div>
                    <small class="text-muted">Biên lai</small>
                    <?php if (isset($stats['unpaid_receipts']) && $stats['unpaid_receipts'] > 0): ?>
                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                        <i class="fas fa-circle text-warning" style="font-size: 4px;"></i>
                        <?php echo number_format($stats['unpaid_receipts']); ?> chưa thanh toán
                    </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="icon-circle bg-soft-info mx-auto mb-2">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="card-label">Bốc số</div>
                    <div class="card-value"><?php echo number_format($stats['total_tickets'] ?? 0); ?></div>
                    <small class="text-muted">Tổng số lần</small>
                </div>
            </div>
        </div>

    </div>

    <!-- Charts & quick actions -->
    <div class="row g-3">
        <div class="col-xl-6">
            <div class="card shadow-sm mb-3">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-semibold">Lịch hẹn</h6>
                        <small class="text-muted" id="appointmentChartSubtitle">12 tháng gần nhất</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <select id="appointmentFilter" class="form-select form-select-sm">
                            <option value="day">Ngày</option>
                            <option value="week">Tuần</option>
                            <option value="month" selected>Tháng</option>
                            <option value="year">Năm</option>
                        </select>
                        <i class="fas fa-calendar-alt text-muted"></i>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="appointmentsChart" height="150"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card shadow-sm mb-3">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-semibold">Doanh thu</h6>
                        <small class="text-muted" id="revenueChartSubtitle">12 tháng gần nhất</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <select id="revenueFilter" class="form-select form-select-sm">
                            <option value="day">Ngày</option>
                            <option value="week">Tuần</option>
                            <option value="month" selected>Tháng</option>
                            <option value="year">Năm</option>
                        </select>
                        <i class="fas fa-chart-line text-muted"></i>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="150"></canvas>
                </div>
            </div>
        </div>
        < </div>

            <!-- Biểu đồ tròn -->
            <div class="row g-3">
                <div class="col-xl-6">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header border-0">
                            <h6 class="mb-0 fw-semibold">Tỷ lệ trạng thái lịch hẹn</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="appointmentStatusChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header border-0">
                            <h6 class="mb-0 fw-semibold">Top chuyên khoa được đặt lịch</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="specialtyChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm mb-3 col-xl-12">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-semibold">Bệnh nhân mới</h6>
                        <small class="text-muted" id="patientChartSubtitle">12 tháng gần nhất</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <select id="patientFilter" class="form-select form-select-sm">
                            <option value="day">Ngày</option>
                            <option value="week">Tuần</option>
                            <option value="month" selected>Tháng</option>
                            <option value="year">Năm</option>
                        </select>
                        <i class="fas fa-user-injured text-muted"></i>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="patientChart" height="150"></canvas>
                </div>
            </div>

    </div>
</div>

<!-- Modern dashboard styles -->
<style>
.dashboard-card {
    border-radius: 16px;
    background: #ffffff;
}

.icon-circle {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.bg-soft-primary {
    background: rgba(59, 130, 246, 0.12);
    color: #1d4ed8;
}

.bg-soft-success {
    background: rgba(22, 163, 74, 0.12);
    color: #15803d;
}

.bg-soft-info {
    background: rgba(6, 182, 212, 0.12);
    color: #0e7490;
}

.bg-soft-warning {
    background: rgba(245, 158, 11, 0.12);
    color: #b45309;
}

.bg-soft-danger {
    background: rgba(239, 68, 68, 0.12);
    color: #dc2626;
}

.bg-soft-secondary {
    background: rgba(107, 114, 128, 0.12);
    color: #4b5563;
}

.card-label {
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #6b7280;
    margin-bottom: 2px;
}

.card-value {
    font-size: 1.25rem;
    font-weight: 600;
    color: #0f172a;
}

.card-value .unit {
    font-size: 0.75rem;
    font-weight: 500;
    color: #6b7280;
    margin-left: 4px;
}
</style>

<!-- Charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const stats = <?php echo json_encode($stats, JSON_UNESCAPED_UNICODE); ?>;

    // ========== BIỂU ĐỒ LỊCH HẸN (CÓ FILTER) ==========
    const apptCtx = document.getElementById('appointmentsChart');
    const appointmentFilterEl = document.getElementById('appointmentFilter');
    const appointmentSubtitleEl = document.getElementById('appointmentChartSubtitle');
    let appointmentChart = null;

    const appointmentFilterLabels = {
        day: '30 ngày gần nhất',
        week: '12 tuần gần nhất',
        month: '12 tháng gần nhất',
        year: '5 năm gần nhất'
    };

    async function loadAppointmentChart(filter = 'month') {
        if (!apptCtx) return;

        try {
            const res = await fetch(`./get_appointment_stats?filter=${encodeURIComponent(filter)}`);
            const json = await res.json();
            if (!json.success) return;

            const data = json.data || [];
            const labels = data.map(row => row.label || '');
            const values = data.map(row => Number(row.value || 0));

            // Tính toán tỷ lệ thay đổi và số lượng thay đổi so với kỳ trước
            const percentages = [];
            const countChanges = []; // Số lượng thay đổi (số tuyệt đối)
            const backgroundColors = [];
            for (let i = 0; i < values.length; i++) {
                if (i === 0 || values[i - 1] === 0) {
                    percentages.push(null);
                    countChanges.push(null);
                    backgroundColors.push('rgba(245, 158, 11, 0.75)'); // Màu vàng mặc định
                } else {
                    const changeCount = values[i] - values[i - 1]; // Số lượng thay đổi
                    const change = (changeCount / values[i - 1]) * 100; // Tỷ lệ phần trăm
                    percentages.push(change);
                    countChanges.push(changeCount);
                    // Màu xanh cho tăng, đỏ cho giảm, vàng cho không đổi
                    if (change > 0) {
                        backgroundColors.push('rgba(34, 197, 94, 0.75)'); // Xanh lá
                    } else if (change < 0) {
                        backgroundColors.push('rgba(239, 68, 68, 0.75)'); // Đỏ
                    } else {
                        backgroundColors.push('rgba(245, 158, 11, 0.75)'); // Vàng
                    }
                }
            }

            if (appointmentSubtitleEl) {
                appointmentSubtitleEl.textContent = appointmentFilterLabels[filter] || '';
            }

            if (appointmentChart) {
                appointmentChart.data.labels = labels;
                appointmentChart.data.datasets[0].data = values;
                appointmentChart.data.datasets[0].backgroundColor = backgroundColors;
                // Lưu percentages và countChanges để dùng trong tooltip
                appointmentChart.percentages = percentages;
                appointmentChart.countChanges = countChanges;
                appointmentChart.update();
                return;
            }

            // Tạo biểu đồ Bar Chart (phù hợp cho dữ liệu đếm số lượng)
            appointmentChart = new Chart(apptCtx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Số lượng lịch hẹn',
                        data: values,
                        backgroundColor: backgroundColors,
                        borderColor: '#0d9488',
                        borderWidth: 2,
                        borderRadius: 6,
                        maxBarThickness: 40,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8
                    }]
                },
                // Lưu percentages và countChanges để dùng trong tooltip
                percentages: percentages,
                countChanges: countChanges,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed.y;
                                    const index = context.dataIndex;
                                    const chart = context.chart;
                                    const percentage = chart.percentages ? chart.percentages[
                                        index] : null;
                                    const countChange = chart.countChanges ? chart.countChanges[
                                        index] : null;

                                    let label = 'Lịch hẹn: ' + value + ' cuộc hẹn';

                                    if (percentage !== null && percentage !== undefined && !isNaN(
                                            percentage) &&
                                        countChange !== null && countChange !== undefined && !isNaN(
                                            countChange)) {
                                        const changeText = percentage > 0 ?
                                            `↑ +${percentage.toFixed(1)}% (+${countChange} cuộc hẹn)` :
                                            percentage < 0 ?
                                            `↓ ${percentage.toFixed(1)}% (${countChange} cuộc hẹn)` :
                                            `→ 0% (${countChange} cuộc hẹn)`;
                                        label += '\nThay đổi: ' + changeText + ' so với kỳ trước';
                                    }

                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Thời gian',
                                font: {
                                    size: 5,
                                    weight: 'normal'
                                },
                                padding: {
                                    top: 5
                                }
                            },
                            ticks: {
                                font: {
                                    size: 5
                                },
                                maxRotation: 45,
                                minRotation: 0
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Số lượng lịch hẹn',
                                font: {
                                    size: 5,
                                    weight: 'normal'
                                },
                                padding: {
                                    right: 5
                                }
                            },
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 5
                                },
                                callback: function(value) {
                                    return value + ' cuộc';
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        }
                    }
                }
            });
        } catch (e) {
            console.error('Failed to load appointment stats', e);
        }
    }

    // Load biểu đồ lịch hẹn ban đầu
    loadAppointmentChart('month');

    // Xử lý thay đổi filter
    if (appointmentFilterEl) {
        appointmentFilterEl.addEventListener('change', function() {
            const val = this.value || 'month';
            loadAppointmentChart(val);
        });
    }

    // ========== BIỂU ĐỒ DOANH THU (CÓ FILTER) ==========
    const revCtx = document.getElementById('revenueChart');
    const revenueFilterEl = document.getElementById('revenueFilter');
    const revenueSubtitleEl = document.getElementById('revenueChartSubtitle');
    let revenueChart = null;

    // Hàm format số tiền (1.000.000 -> 1M)
    function formatMoney(value) {
        if (value >= 1000000000) return (value / 1000000000).toFixed(1) + 'B';
        if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
        if (value >= 1000) return (value / 1000).toFixed(1) + 'K';
        return value;
    }

    const filterLabels = {
        day: '30 ngày gần nhất',
        week: '12 tuần gần nhất',
        month: '12 tháng gần nhất',
        year: '5 năm gần nhất'
    };

    async function loadRevenueChart(filter = 'month') {
        if (!revCtx) return;

        try {
            const res = await fetch(`./get_revenue_stats?filter=${encodeURIComponent(filter)}`);
            const json = await res.json();
            if (!json.success) return;

            const data = json.data || [];
            const labels = data.map(row => row.label || '');
            const values = data.map(row => Number(row.value || 0));

            // Tính toán tỷ lệ thay đổi và số tiền thay đổi so với kỳ trước
            const percentages = [];
            const amountChanges = []; // Số tiền thay đổi (số tuyệt đối)
            const backgroundColors = [];
            for (let i = 0; i < values.length; i++) {
                if (i === 0 || values[i - 1] === 0) {
                    percentages.push(null);
                    amountChanges.push(null);
                    backgroundColors.push('rgba(245, 158, 11, 0.75)'); // Màu vàng mặc định
                } else {
                    const changeAmount = values[i] - values[i - 1]; // Số tiền thay đổi
                    const change = (changeAmount / values[i - 1]) * 100; // Tỷ lệ phần trăm
                    percentages.push(change);
                    amountChanges.push(changeAmount);
                    // Màu xanh cho tăng, đỏ cho giảm, vàng cho không đổi
                    if (change > 0) {
                        backgroundColors.push('rgba(34, 197, 94, 0.75)'); // Xanh lá
                    } else if (change < 0) {
                        backgroundColors.push('rgba(239, 68, 68, 0.75)'); // Đỏ
                    } else {
                        backgroundColors.push('rgba(245, 158, 11, 0.75)'); // Vàng
                    }
                }
            }

            const maxValue = values.length ?
                Math.max(...values.filter(v => v !== null && v !== undefined && !isNaN(v))) :
                0;
            const yAxisMax = maxValue > 0 ? maxValue * 1.3 : 1000;

            // Cập nhật subtitle
            if (revenueSubtitleEl) {
                revenueSubtitleEl.textContent = filterLabels[filter] || '';
            }

            // Nếu đã có chart thì update, không tạo mới
            if (revenueChart) {
                revenueChart.data.labels = labels;
                revenueChart.data.datasets[0].data = values;
                revenueChart.data.datasets[0].backgroundColor = backgroundColors;
                revenueChart.options.scales.y.max = yAxisMax;
                // Lưu percentages và amountChanges để dùng trong tooltip
                revenueChart.percentages = percentages;
                revenueChart.amountChanges = amountChanges;
                revenueChart.update();
                return;
            }

            revenueChart = new Chart(revCtx, {
                type: 'bar', // Bar chart phù hợp cho so sánh doanh thu theo kỳ
                data: {
                    labels,
                    datasets: [{
                        label: 'Doanh thu (VNĐ)',
                        data: values,
                        backgroundColor: backgroundColors,
                        borderRadius: 4,
                        maxBarThickness: 20,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    }]
                },
                // Lưu percentages và amountChanges để dùng trong tooltip
                percentages: percentages,
                amountChanges: amountChanges,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed.y;
                                    const index = context.dataIndex;
                                    const chart = context.chart;
                                    const percentage = chart.percentages ? chart.percentages[
                                        index] : null;
                                    const amountChange = chart.amountChanges ? chart.amountChanges[
                                        index] : null;

                                    let label = 'Doanh thu: ' + formatMoney(value) + ' VNĐ';

                                    if (percentage !== null && percentage !== undefined && !isNaN(
                                            percentage) &&
                                        amountChange !== null && amountChange !== undefined && !
                                        isNaN(amountChange)) {
                                        const changeText = percentage > 0 ?
                                            `↑ +${percentage.toFixed(1)}% (+${formatMoney(amountChange)} VNĐ)` :
                                            percentage < 0 ?
                                            `↓ ${percentage.toFixed(1)}% (${formatMoney(amountChange)} VNĐ)` :
                                            `→ 0% (${formatMoney(amountChange)} VNĐ)`;
                                        label += '\nThay đổi: ' + changeText + ' so với kỳ trước';
                                    }

                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Thời gian',
                                font: {
                                    size: 11,
                                    weight: 'normal'
                                },
                                padding: {
                                    top: 5
                                }
                            },
                            ticks: {
                                font: {
                                    size: 10
                                },
                                maxRotation: 45,
                                minRotation: 0
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            max: yAxisMax,
                            title: {
                                display: true,
                                text: 'Doanh thu (VNĐ)',
                                font: {
                                    size: 11,
                                    weight: 'normal'
                                },
                                padding: {
                                    right: 5
                                }
                            },
                            ticks: {
                                callback: formatMoney,
                                maxTicksLimit: 6
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        }
                    }
                }
            });
        } catch (e) {
            console.error('Failed to load revenue stats', e);
        }
    }

    // Khởi tạo biểu đồ với filter mặc định = month
    loadRevenueChart('month');

    // Lắng nghe thay đổi filter
    if (revenueFilterEl) {
        revenueFilterEl.addEventListener('change', function() {
            const val = this.value || 'month';
            loadRevenueChart(val);
        });
    }

    // ========== BIỂU ĐỒ BỆNH NHÂN MỚI (CÓ FILTER) ==========
    const patientCtx = document.getElementById('patientChart');
    const patientFilterEl = document.getElementById('patientFilter');
    const patientSubtitleEl = document.getElementById('patientChartSubtitle');
    let patientChart = null;

    const patientFilterLabels = {
        day: '30 ngày gần nhất',
        week: '12 tuần gần nhất',
        month: '12 tháng gần nhất',
        year: '5 năm gần nhất'
    };

    async function loadPatientChart(filter = 'month') {
        if (!patientCtx) return;

        try {
            const res = await fetch(`./get_patient_stats?filter=${encodeURIComponent(filter)}`);
            const json = await res.json();
            if (!json.success) return;

            const data = json.data || [];
            const labels = data.map(row => row.label || '');
            const values = data.map(row => Number(row.value || 0));

            // Tính toán tỷ lệ thay đổi và số lượng thay đổi so với kỳ trước
            const percentages = [];
            const countChanges = []; // Số lượng thay đổi (số tuyệt đối)
            for (let i = 0; i < values.length; i++) {
                if (i === 0 || values[i - 1] === 0) {
                    percentages.push(null);
                    countChanges.push(null);
                } else {
                    const changeCount = values[i] - values[i - 1]; // Số lượng thay đổi
                    const change = (changeCount / values[i - 1]) * 100; // Tỷ lệ phần trăm
                    percentages.push(change);
                    countChanges.push(changeCount);
                }
            }

            const maxValue = values.length ?
                Math.max(...values.filter(v => v !== null && v !== undefined && !isNaN(v))) :
                0;
            const yAxisMax = maxValue > 0 ? maxValue * 1.3 : 10;

            if (patientSubtitleEl) {
                patientSubtitleEl.textContent = patientFilterLabels[filter] || '';
            }

            if (patientChart) {
                patientChart.data.labels = labels;
                patientChart.data.datasets[0].data = values;
                patientChart.options.scales.y.max = yAxisMax;
                // Lưu percentages và countChanges để dùng trong tooltip
                patientChart.percentages = percentages;
                patientChart.countChanges = countChanges;
                patientChart.update();
                return;
            }

            // Tạo biểu đồ Line Chart - phù hợp cho theo dõi xu hướng tăng trưởng bệnh nhân mới
            patientChart = new Chart(patientCtx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Bệnh nhân mới',
                        data: values,
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4, // Đường cong mượt mà
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#22c55e',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointHoverBackgroundColor: '#16a34a',
                        pointHoverBorderColor: '#ffffff',
                        pointHoverBorderWidth: 3
                    }]
                },
                // Lưu percentages và countChanges để dùng trong tooltip
                percentages: percentages,
                countChanges: countChanges,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed.y;
                                    const index = context.dataIndex;
                                    const chart = context.chart;
                                    const percentage = chart.percentages ? chart.percentages[
                                        index] : null;
                                    const countChange = chart.countChanges ? chart.countChanges[
                                        index] : null;

                                    let label = 'Bệnh nhân mới: ' + value + ' người';

                                    if (percentage !== null && percentage !== undefined && !isNaN(
                                            percentage)) {
                                        const sign = countChange > 0 ? '+' : '';
                                        const color = percentage > 0 ? '🟢' : percentage < 0 ?
                                            '🔴' : '🟡';
                                        label +=
                                            `\n${color} Thay đổi: ${sign}${countChange} người (${sign}${percentage.toFixed(1)}%)`;
                                    }

                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Thời gian',
                                font: {
                                    size: 11,
                                    weight: 'normal'
                                },
                                padding: {
                                    top: 5
                                }
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                maxRotation: 45,
                                minRotation: 0
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            max: yAxisMax,
                            title: {
                                display: true,
                                text: 'Số lượng bệnh nhân',
                                font: {
                                    size: 11,
                                    weight: 'normal'
                                },
                                padding: {
                                    right: 5
                                }
                            },
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 11
                                },
                                callback: function(value) {
                                    return value + ' người';
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        }
                    }
                }
            });
        } catch (e) {
            console.error('Failed to load patient stats', e);
        }
    }

    // Load biểu đồ bệnh nhân ban đầu
    loadPatientChart('month');

    // Xử lý thay đổi filter bệnh nhân
    if (patientFilterEl) {
        patientFilterEl.addEventListener('change', function() {
            const val = this.value || 'month';
            loadPatientChart(val);
        });
    }

    // ========== BIỂU ĐỒ TRÒN: TỶ LỆ TRẠNG THÁI LỊCH HẸN ==========
    const statusCtx = document.getElementById('appointmentStatusChart');
    let statusChart = null;

    async function loadAppointmentStatusChart() {
        if (!statusCtx) return;

        try {
            const res = await fetch('./get_appointment_status_stats?filter=all');
            const json = await res.json();
            if (!json.success || !json.data || json.data.length === 0) return;

            const data = json.data || [];
            const labels = data.map(row => row.label || '');
            const values = data.map(row => Number(row.value || 0));

            const colors = [
                'rgba(75, 192, 192, 0.8)', // Xanh lá - Hoàn thành
                'rgba(255, 99, 132, 0.8)', // Đỏ - Đã hủy
            ];

            if (statusChart) {
                statusChart.data.labels = labels;
                statusChart.data.datasets[0].data = values;
                statusChart.data.datasets[0].backgroundColor = colors.slice(0, labels.length);
                statusChart.update();
                return;
            }

            statusChart = new Chart(statusCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Số lượng',
                        data: values,
                        backgroundColor: colors.slice(0, labels.length),
                        borderColor: colors.map(c => c.replace('0.8', '1')),
                        borderWidth: 2,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y', // Biểu đồ cột ngang
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed.x || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(
                                        1) : 0;
                                    return `${label}: ${value} lịch hẹn (${percentage}%)`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Số lượng lịch hẹn',
                                font: {
                                    size: 11,
                                    weight: 'normal'
                                },
                                padding: {
                                    top: 5
                                }
                            },
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 12
                                }
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Trạng thái',
                                font: {
                                    size: 11,
                                    weight: 'normal'
                                },
                                padding: {
                                    right: 5
                                }
                            },
                            ticks: {
                                font: {
                                    size: 12
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        } catch (e) {
            console.error('Failed to load appointment status stats', e);
        }
    }

    // ========== BIỂU ĐỒ TRÒN: TOP CHUYÊN KHOA ==========
    const specialtyCtx = document.getElementById('specialtyChart');
    let specialtyChart = null;

    async function loadSpecialtyChart() {
        if (!specialtyCtx) return;

        try {
            const res = await fetch('./get_specialty_stats?filter=all');
            const json = await res.json();
            if (!json.success || !json.data || json.data.length === 0) return;

            const data = json.data || [];
            const labels = data.map(row => row.label || '');
            const values = data.map(row => Number(row.value || 0));

            const colors = [
                'rgba(54, 162, 235, 0.8)', // Xanh dương
                'rgba(255, 99, 132, 0.8)', // Đỏ
                'rgba(75, 192, 192, 0.8)', // Xanh lá
                'rgba(255, 206, 86, 0.8)', // Vàng
                'rgba(153, 102, 255, 0.8)', // Tím
                'rgba(255, 159, 64, 0.8)', // Cam
                'rgba(199, 199, 199, 0.8)', // Xám
            ];

            if (specialtyChart) {
                specialtyChart.data.labels = labels;
                specialtyChart.data.datasets[0].data = values;
                specialtyChart.data.datasets[0].backgroundColor = colors.slice(0, labels.length);
                specialtyChart.update();
                return;
            }

            specialtyChart = new Chart(specialtyCtx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors.slice(0, labels.length),
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                },
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    if (data.labels.length && data.datasets.length) {
                                        const total = data.datasets[0].data.reduce((a, b) => a + b,
                                            0);
                                        return data.labels.map((label, i) => {
                                            const value = data.datasets[0].data[i];
                                            const percentage = total > 0 ? ((value /
                                                total) * 100).toFixed(1) : 0;
                                            return {
                                                text: `${label}: ${value} (${percentage}%)`,
                                                fillStyle: data.datasets[0].backgroundColor[
                                                    i],
                                                strokeStyle: data.datasets[0].borderColor,
                                                lineWidth: data.datasets[0].borderWidth,
                                                hidden: false,
                                                index: i
                                            };
                                        });
                                    }
                                    return [];
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(
                                        1) : 0;
                                    return `${label}: ${value} lịch hẹn (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        } catch (e) {
            console.error('Failed to load specialty stats', e);
        }
    }

    // Load biểu đồ tròn ban đầu (không có filter)
    loadAppointmentStatusChart();
    loadSpecialtyChart();

    // Khởi tạo tooltip cho "đang trực" và "lịch hẹn"
    function initTooltips() {
        const tooltipElements = document.querySelectorAll('.on-duty-tooltip, .appointment-tooltip');
        if (tooltipElements.length === 0) {
            return;
        }

        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            tooltipElements.forEach(function(element) {
                // Xóa tooltip cũ nếu có
                const existingTooltip = bootstrap.Tooltip.getInstance(element);
                if (existingTooltip) {
                    existingTooltip.dispose();
                }
                // Lấy title từ data-bs-title
                const title = element.getAttribute('data-bs-title') || element.getAttribute('title');
                if (!title) {
                    return;
                }
                // Tạo tooltip mới
                try {
                    new bootstrap.Tooltip(element, {
                        html: true,
                        placement: 'top',
                        trigger: 'hover focus',
                        title: title
                    });
                } catch (e) {
                    // Silent fail
                }
            });
        } else {
            // Nếu Bootstrap chưa sẵn sàng, thử lại sau 200ms
            setTimeout(initTooltips, 200);
        }
    }

    // Đảm bảo khởi tạo sau khi mọi thứ đã load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initTooltips, 500);
        });
    } else {
        // DOM đã sẵn sàng
        setTimeout(initTooltips, 500);
    }

    // Cũng thử khởi tạo khi window load xong
    window.addEventListener('load', function() {
        setTimeout(initTooltips, 200);
    });
})
();
</script>