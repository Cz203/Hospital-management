<?php
require_once 'Views/layouts/layout_helper.php';
$ctx = getCurrentUserContext();
$doctorName = htmlspecialchars(($ctx['name'] ?? ''), ENT_QUOTES, 'UTF-8');
$specialization = '';
try {
    if (($ctx['role'] ?? '') === 'doctor' || ($ctx['role'] ?? '') === 'xray_doctor' || ($ctx['role'] ?? '') === 'sieuam_doctor') {
        require_once 'Models/Doctor.php';
        $dm = new Doctor();
        $d = $dm->getById($ctx['id']);
        $specialization = $d['chuyen_khoa'] ?? '';
    }
} catch (Exception $e) {
}

$content = '
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user-md text-success me-2"></i>
                Doctor Dashboard
            </h1>
            <p class="text-muted">Chào mừng bác sĩ ' . $doctorName . ' - ' . ($specialization ?: 'Chuyên khoa') . '</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <!-- Socket Connection Status -->
            <div class="me-3">
                <span id="socket-status" class="badge bg-secondary">Đang kết nối...</span>
                <div id="connection-stats" class="small text-muted"></div>
            </div>
            
            <button class="btn btn-success">
                <i class="fas fa-calendar-plus me-2"></i>Lịch làm việc
            </button>
            <button class="btn btn-primary">
                <i class="fas fa-stethoscope me-2"></i>Khám bệnh
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Lịch hẹn hôm nay
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">12</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Bệnh nhân đã khám
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">8</div>
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
                                Đơn thuốc đã kê
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">15</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-prescription fa-2x text-gray-300"></i>
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
                                Bệnh nhân chờ khám
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">4</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Thao tác nhanh</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="#" class="btn btn-outline-success w-100">
                                <i class="fas fa-stethoscope fa-2x mb-2"></i><br>
                                Bắt đầu khám
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="#" class="btn btn-outline-primary w-100">
                                <i class="fas fa-prescription fa-2x mb-2"></i><br>
                                Kê đơn thuốc
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="#" class="btn btn-outline-info w-100">
                                <i class="fas fa-file-medical fa-2x mb-2"></i><br>
                                Xem hồ sơ
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="#" class="btn btn-outline-warning w-100">
                                <i class="fas fa-calendar-alt fa-2x mb-2"></i><br>
                                Lịch làm việc
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
';

renderLayout($content, 'Doctor Dashboard - Hệ thống Quản lý Bệnh viện');
