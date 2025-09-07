<?php
// File này sẽ được include từ AdminController
// Không cần kiểm tra session ở đây vì đã được kiểm tra trong Controller
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-tachometer-alt text-primary me-2"></i>Dashboard Admin
            </h1>
            <p class="mb-0 text-muted">Chào mừng trở lại, <?php echo $_SESSION['user_name']; ?>!</p>
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
                                Tổng bác sĩ
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_doctors']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-md fa-2x text-gray-300"></i>
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
                                Tổng bệnh nhân
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_patients']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-injured fa-2x text-gray-300"></i>
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
                                Lịch hẹn hôm nay
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['total_appointments']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
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
                                Doanh thu tháng
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($stats['total_revenue']); ?> VNĐ</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thao tác nhanh</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="./admin_doctor_schedules" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-calendar-check me-2"></i>Quản lý lịch bác sĩ
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="#" class="btn btn-outline-success btn-block">
                                <i class="fas fa-user-md me-2"></i>Quản lý bác sĩ
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="#" class="btn btn-outline-info btn-block">
                                <i class="fas fa-user-injured me-2"></i>Quản lý bệnh nhân
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="#" class="btn btn-outline-warning btn-block">
                                <i class="fas fa-chart-bar me-2"></i>Báo cáo thống kê
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin hệ thống</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <i class="fas fa-hospital fa-3x text-primary mb-3"></i>
                        <h5>Hệ thống Quản lý Bệnh viện</h5>
                        <p class="text-muted">Phiên bản 1.0</p>
                        <hr>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Cập nhật lần cuối: <?php echo date('d/m/Y H:i:s'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>