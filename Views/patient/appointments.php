<?php
require_once 'Views/layouts/layout_helper.php';

$content = '
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
            <button class="btn btn-primary">
                <i class="fas fa-calendar-plus me-2"></i>Đặt lịch hẹn mới
            </button>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <ul class="nav nav-tabs" id="appointmentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab">
                        <i class="fas fa-clock me-2"></i>Lịch hẹn sắp tới
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                        <i class="fas fa-check-circle me-2"></i>Đã hoàn thành
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled" type="button" role="tab">
                        <i class="fas fa-times-circle me-2"></i>Đã hủy
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
                                <tr>
                                    <td>16/12/2024 09:00</td>
                                    <td>Bác sĩ Nguyễn Thị A</td>
                                    <td>Tim mạch</td>
                                    <td>Tái khám định kỳ</td>
                                    <td><span class="badge bg-success">Đã xác nhận</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info">Xem chi tiết</button>
                                        <button class="btn btn-sm btn-warning">Hủy lịch</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>18/12/2024 14:30</td>
                                    <td>Bác sĩ Lê Văn B</td>
                                    <td>Thần kinh</td>
                                    <td>Khám đau đầu</td>
                                    <td><span class="badge bg-warning">Chờ xác nhận</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info">Xem chi tiết</button>
                                        <button class="btn btn-sm btn-warning">Hủy lịch</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>20/12/2024 10:00</td>
                                    <td>Bác sĩ Phạm Thị C</td>
                                    <td>Da liễu</td>
                                    <td>Khám mụn</td>
                                    <td><span class="badge bg-secondary">Chờ xác nhận</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info">Xem chi tiết</button>
                                        <button class="btn btn-sm btn-warning">Hủy lịch</button>
                                    </td>
                                </tr>
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
                                <tr>
                                    <td>10/12/2024 09:00</td>
                                    <td>Bác sĩ Nguyễn Thị A</td>
                                    <td>Tim mạch</td>
                                    <td>Tái khám định kỳ</td>
                                    <td><span class="badge bg-success">Hoàn thành</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info">Xem chi tiết</button>
                                        <button class="btn btn-sm btn-primary">Tải về</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>05/12/2024 14:30</td>
                                    <td>Bác sĩ Lê Văn B</td>
                                    <td>Thần kinh</td>
                                    <td>Khám đau đầu</td>
                                    <td><span class="badge bg-success">Hoàn thành</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info">Xem chi tiết</button>
                                        <button class="btn btn-sm btn-primary">Tải về</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>28/11/2024 10:00</td>
                                    <td>Bác sĩ Phạm Thị C</td>
                                    <td>Da liễu</td>
                                    <td>Khám sức khỏe định kỳ</td>
                                    <td><span class="badge bg-success">Hoàn thành</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info">Xem chi tiết</button>
                                        <button class="btn btn-sm btn-primary">Tải về</button>
                                    </td>
                                </tr>
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
                                <tr>
                                    <td>15/11/2024 09:00</td>
                                    <td>Bác sĩ Nguyễn Thị A</td>
                                    <td>Tim mạch</td>
                                    <td>Tái khám định kỳ</td>
                                    <td>Bệnh nhân yêu cầu</td>
                                    <td>
                                        <button class="btn btn-sm btn-info">Xem chi tiết</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
';

renderLayout($content, 'Lịch hẹn của tôi - Hệ thống Quản lý Bệnh viện');
