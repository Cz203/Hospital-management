<?php
require_once 'Views/layouts/layout_helper.php';

$content = '
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-tachometer-alt text-primary me-2"></i>
                Admin Dashboard
            </h1>
            <p class="text-muted">Tổng quan hệ thống quản lý bệnh viện</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary">
                <i class="fas fa-download me-2"></i>Xuất báo cáo
            </button>
            <button class="btn btn-success">
                <i class="fas fa-plus me-2"></i>Thêm mới
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
                                Tổng số bệnh nhân
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">1,250</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-injured fa-2x text-gray-300"></i>
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
                                Tổng số bác sĩ
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">45</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-md fa-2x text-gray-300"></i>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">89</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$125,000</div>
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
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thao tác nhanh</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="#" class="btn btn-outline-primary w-100">
                                <i class="fas fa-user-plus fa-2x mb-2"></i><br>
                                Thêm bệnh nhân
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="#" class="btn btn-outline-success w-100">
                                <i class="fas fa-user-md fa-2x mb-2"></i><br>
                                Thêm bác sĩ
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="#" class="btn btn-outline-info w-100">
                                <i class="fas fa-calendar-plus fa-2x mb-2"></i><br>
                                Tạo lịch hẹn
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="#" class="btn btn-outline-warning w-100">
                                <i class="fas fa-chart-bar fa-2x mb-2"></i><br>
                                Xem báo cáo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
';

renderLayout($content, 'Admin Dashboard - Hệ thống Quản lý Bệnh viện');