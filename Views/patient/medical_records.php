<?php
require_once 'Views/layouts/layout_helper.php';

$content = '
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-file-medical text-primary me-2"></i>
                Hồ sơ bệnh án
            </h1>
            <p class="text-muted">Xem và quản lý hồ sơ bệnh án của bạn</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary">
                <i class="fas fa-download me-2"></i>Tải về tất cả
            </button>
        </div>
    </div>

    <!-- Medical Records List -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Hồ sơ bệnh án gần đây</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Ngày khám</th>
                            <th>Bác sĩ</th>
                            <th>Chẩn đoán</th>
                            <th>Điều trị</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>10/12/2024</td>
                            <td>Bác sĩ Nguyễn Thị A</td>
                            <td>Tăng huyết áp nhẹ</td>
                            <td>Thuốc hạ huyết áp, chế độ ăn</td>
                            <td><span class="badge bg-success">Đang điều trị</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">Xem chi tiết</button>
                                <button class="btn btn-sm btn-primary">Tải về</button>
                            </td>
                        </tr>
                        <tr>
                            <td>05/12/2024</td>
                            <td>Bác sĩ Lê Văn B</td>
                            <td>Đau đầu do căng thẳng</td>
                            <td>Thuốc giảm đau, nghỉ ngơi</td>
                            <td><span class="badge bg-success">Đã khỏi</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">Xem chi tiết</button>
                                <button class="btn btn-sm btn-primary">Tải về</button>
                            </td>
                        </tr>
                        <tr>
                            <td>28/11/2024</td>
                            <td>Bác sĩ Phạm Thị C</td>
                            <td>Khám sức khỏe định kỳ</td>
                            <td>Không cần điều trị</td>
                            <td><span class="badge bg-success">Hoàn thành</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">Xem chi tiết</button>
                                <button class="btn btn-sm btn-primary">Tải về</button>
                            </td>
                        </tr>
                        <tr>
                            <td>15/11/2024</td>
                            <td>Bác sĩ Hoàng Văn D</td>
                            <td>Viêm họng</td>
                            <td>Kháng sinh, thuốc ho</td>
                            <td><span class="badge bg-success">Đã khỏi</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">Xem chi tiết</button>
                                <button class="btn btn-sm btn-primary">Tải về</button>
                            </td>
                        </tr>
                        <tr>
                            <td>01/11/2024</td>
                            <td>Bác sĩ Trần Thị E</td>
                            <td>Khám sức khỏe định kỳ</td>
                            <td>Không cần điều trị</td>
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

    <!-- Health Summary -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tóm tắt sức khỏe</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <small class="text-muted">Nhóm máu</small>
                            <div class="fw-bold">A+</div>
                        </div>
                        <div class="col-6 mb-3">
                            <small class="text-muted">Chiều cao</small>
                            <div class="fw-bold">170 cm</div>
                        </div>
                        <div class="col-6 mb-3">
                            <small class="text-muted">Cân nặng</small>
                            <div class="fw-bold">65 kg</div>
                        </div>
                        <div class="col-6 mb-3">
                            <small class="text-muted">BMI</small>
                            <div class="fw-bold">22.5</div>
                        </div>
                        <div class="col-6 mb-3">
                            <small class="text-muted">Huyết áp</small>
                            <div class="fw-bold">120/80 mmHg</div>
                        </div>
                        <div class="col-6 mb-3">
                            <small class="text-muted">Nhịp tim</small>
                            <div class="fw-bold">72 bpm</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thống kê khám bệnh</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Tổng số lần khám</span>
                            <span>15</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-primary" style="width: 100%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Lần khám trong năm nay</span>
                            <span>8</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: 53%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Lần khám định kỳ</span>
                            <span>5</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-info" style="width: 33%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Lần khám cấp cứu</span>
                            <span>2</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" style="width: 13%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
';

renderLayout($content, 'Hồ sơ bệnh án - Hệ thống Quản lý Bệnh viện');
