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
        <div class="col-xl-3 col-md-6">
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
                        <?php if (isset($stats['on_duty_doctors']) && $stats['on_duty_doctors'] > 0): ?>
                            <small class="text-muted">
                                <i class="fas fa-circle text-success" style="font-size: 6px;"></i>
                                <?php echo $stats['on_duty_doctors']; ?> đang trực
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
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
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
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
                        <small class="text-muted">
                            <i class="fas fa-calendar-day me-1"></i>
                            Hôm nay:
                            <strong><?php echo number_format($stats['total_appointments_today'] ?? 0); ?></strong>
                        </small>
                        <?php
                        $examining = $stats['examining_patients'] ?? 0;
                        $pending = $stats['pending_appointments'] ?? 0;
                        if ($examining > 0 || $pending > 0):
                        ?>
                            <small class="text-muted d-block mt-1">
                                <span class="text-warning"><?php echo $examining; ?> đang khám</span>
                                <?php if ($pending > 0): ?>
                                    | <span class="text-info"><?php echo $pending; ?> chờ xác nhận</span>
                                <?php endif; ?>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
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
                    <div class="icon-circle bg-soft-warning mx-auto mb-2">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="card-label">Chưa thanh toán</div>
                    <div class="card-value"><?php echo number_format($stats['unpaid_receipts'] ?? 0); ?></div>
                    <small class="text-muted">Biên lai</small>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="icon-circle bg-soft-secondary mx-auto mb-2">
                        <i class="fas fa-user-nurse"></i>
                    </div>
                    <div class="card-label">Lễ tân trực</div>
                    <div class="card-value"><?php echo number_format($stats['on_duty_receptions'] ?? 0); ?></div>
                    <small class="text-muted">Hôm nay</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & quick actions -->
    <div class="row g-3">
        <div class="col-xl-8">
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
                    <canvas id="appointmentsChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
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

            <div class="card shadow-sm mb-3">
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

            <div class="card shadow-sm">
                <div class="card-header border-0">
                    <h6 class="mb-0 fw-semibold">Thao tác nhanh</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="./doctor_schedules" class="btn btn-outline-primary btn-sm text-start">
                            <i class="fas fa-calendar-check me-2"></i>Quản lý lịch làm việc bác sĩ
                        </a>
                        <a href="./doctors_list" class="btn btn-outline-success btn-sm text-start">
                            <i class="fas fa-user-md me-2"></i>Quản lý bác sĩ
                        </a>
                        <a href="./patients" class="btn btn-outline-info btn-sm text-start">
                            <i class="fas fa-user-injured me-2"></i>Quản lý bệnh nhân
                        </a>
                        <a href="./admin_appointments" class="btn btn-outline-warning btn-sm text-start">
                            <i class="fas fa-calendar-alt me-2"></i>Quản lý lịch hẹn
                        </a>
                    </div>
                </div>
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
                                        const percentage = chart.percentages ? chart.percentages[index] : null;
                                        const countChange = chart.countChanges ? chart.countChanges[index] : null;

                                        let label = 'Lịch hẹn: ' + value + ' cuộc hẹn';

                                        if (percentage !== null && percentage !== undefined && !isNaN(percentage) &&
                                            countChange !== null && countChange !== undefined && !isNaN(countChange)) {
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
                                ticks: {
                                    stepSize: 1,
                                    font: {
                                        size: 11
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
                                        const percentage = chart.percentages ? chart.percentages[index] : null;
                                        const amountChange = chart.amountChanges ? chart.amountChanges[index] : null;

                                        let label = 'Doanh thu: ' + formatMoney(value) + ' VNĐ';

                                        if (percentage !== null && percentage !== undefined && !isNaN(percentage) &&
                                            amountChange !== null && amountChange !== undefined && !isNaN(amountChange)) {
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

                if (patientSubtitleEl) {
                    patientSubtitleEl.textContent = patientFilterLabels[filter] || '';
                }

                if (patientChart) {
                    patientChart.data.labels = labels;
                    patientChart.data.datasets[0].data = values;
                    patientChart.update();
                    return;
                }

                // Tạo biểu đồ Bar Chart - phù hợp cho dữ liệu đếm số lượng bệnh nhân mới
                patientChart = new Chart(patientCtx, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Bệnh nhân mới',
                            data: values,
                            backgroundColor: 'rgba(34, 197, 94, 0.75)',
                            borderColor: '#22c55e',
                            borderWidth: 2,
                            borderRadius: 6,
                            maxBarThickness: 40,
                            barPercentage: 0.7,
                            categoryPercentage: 0.8
                        }]
                    },
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
                                        return 'Bệnh nhân mới: ' + context.parsed.y + ' người';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
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
    })
    ();
</script>