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
                        <div class="card-label">Lịch hẹn hôm nay</div>
                        <div class="card-value">
                            <?php echo number_format($stats['total_appointments_today'] ?? 0); ?>
                        </div>
                        <?php
                        $examining = $stats['examining_patients'] ?? 0;
                        $pending = $stats['pending_appointments'] ?? 0;
                        if ($examining > 0 || $pending > 0):
                        ?>
                        <small class="text-muted">
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
                        <div class="card-label">Doanh thu tháng này</div>
                        <div class="card-value">
                            <?php echo number_format($stats['total_revenue_month'] ?? 0); ?> <span
                                class="unit">VNĐ</span>
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
                        <h6 class="mb-0 fw-semibold">Lịch hẹn theo tháng</h6>
                        <small class="text-muted">Số lượng lịch hẹn trong 12 tháng gần nhất</small>
                    </div>
                    <i class="fas fa-calendar-alt text-muted"></i>
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
                    <i class="fas fa-chart-line text-muted"></i>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="150"></canvas>
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

    // Chuẩn hóa dữ liệu lịch hẹn theo tháng
    const apptData = stats.appointments_by_month || [];
    const apptLabels = apptData.map(row => row.ym);
    const apptCounts = apptData.map(row => Number(row.c || 0));

    // Dữ liệu doanh thu theo tháng
    const revData = stats.revenue_by_month || [];
    const revLabels = revData.map(row => row.ym);
    const revValues = revData.map(row => Number(row.s || 0));

    const apptCtx = document.getElementById('appointmentsChart');
    if (apptCtx) {
        new Chart(apptCtx, {
            type: 'line',
            data: {
                labels: apptLabels,
                datasets: [{
                    label: 'Lịch hẹn',
                    data: apptCounts,
                    borderColor: '#0d9488',
                    backgroundColor: 'rgba(13,148,136,0.12)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // ========== BIỂU ĐỒ DOANH THU ==========
    const revCtx = document.getElementById('revenueChart');

    if (revCtx) {
        // Lấy dữ liệu từ PHP (12 tháng gần nhất)
        const revData = stats.revenue_by_month || [];
        const revLabels = revData.map(row => row.ym || '');
        const revValues = revData.map(row => Number(row.s || 0));

        // Tính max value để giới hạn chiều cao thanh bar
        const maxValue = Math.max(...revValues.filter(v => v !== null && v !== undefined && !isNaN(v)));
        const yAxisMax = maxValue > 0 ? maxValue * 1.3 : 1000;

        // Hàm format số tiền (1.000.000 -> 1M)
        function formatMoney(value) {
            if (value >= 1000000000) return (value / 1000000000).toFixed(1) + 'B';
            if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
            if (value >= 1000) return (value / 1000).toFixed(1) + 'K';
            return value;
        }

        // Tạo biểu đồ
        new Chart(revCtx, {
            type: 'bar',
            data: {
                labels: revLabels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: revValues,
                    backgroundColor: 'rgba(245, 158, 11, 0.75)',
                    borderRadius: 4,
                    maxBarThickness: 20,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
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
    }
})
();
</script>