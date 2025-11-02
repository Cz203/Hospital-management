<?php
// Admin Appointment Management View
?>

<link rel="stylesheet" href="./assets/css/admin_appointments.css">

<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-calendar-alt me-3"></i>
                    Quản lý lịch hẹn
                </h1>
                <p class="page-subtitle mb-0">Quản lý tất cả lịch hẹn trong hệ thống</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline-primary" onclick="exportAppointments()">
                    <i class="fas fa-file-export me-2"></i>Xuất Excel
                </button>
                <button class="btn btn-primary" onclick="refreshData()">
                    <i class="fas fa-sync-alt me-2"></i>Làm mới
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon bg-primary">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number"><?php echo number_format($stats['total']); ?></h3>
                    <p class="stats-label">Tổng lịch hẹn</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon bg-info">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number"><?php echo number_format($stats['today']); ?></h3>
                    <p class="stats-label">Hôm nay</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon bg-warning">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number"><?php echo number_format($stats['pending']); ?></h3>
                    <p class="stats-label">Chờ xác nhận</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number"><?php echo number_format($stats['confirmed']); ?></h3>
                    <p class="stats-label">Đã xác nhận</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon bg-secondary">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number"><?php echo number_format($stats['completed']); ?></h3>
                    <p class="stats-label">Hoàn thành</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon bg-danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number"><?php echo number_format($stats['cancelled']); ?></h3>
                    <p class="stats-label">Đã hủy</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card mb-4">
        <form method="GET" action="./admin_appointments" id="filterForm">
            <input type="hidden" name="action" value="admin_appointments">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tìm kiếm</label>
                    <input type="text" class="form-control" name="search" placeholder="Tên BN, SĐT..."
                        value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Trạng thái</label>
                    <select class="form-select" name="trang_thai">
                        <option value="">Tất cả</option>
                        <option value="Chờ xác nhận"
                            <?php echo ($_GET['trang_thai'] ?? '') === 'Chờ xác nhận' ? 'selected' : ''; ?>>Chờ xác nhận
                        </option>
                        <option value="Đã xác nhận"
                            <?php echo ($_GET['trang_thai'] ?? '') === 'Đã xác nhận' ? 'selected' : ''; ?>>Đã xác nhận
                        </option>
                        <option value="Đang khám"
                            <?php echo ($_GET['trang_thai'] ?? '') === 'Đang khám' ? 'selected' : ''; ?>>Đang khám
                        </option>
                        <option value="Hoàn thành"
                            <?php echo ($_GET['trang_thai'] ?? '') === 'Hoàn thành' ? 'selected' : ''; ?>>Hoàn thành
                        </option>
                        <option value="Đã hủy"
                            <?php echo ($_GET['trang_thai'] ?? '') === 'Đã hủy' ? 'selected' : ''; ?>>Đã hủy</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Loại lịch</label>
                    <select class="form-select" name="loai_lich">
                        <option value="">Tất cả</option>
                        <option value="Trực tiếp"
                            <?php echo ($_GET['loai_lich'] ?? '') === 'Trực tiếp' ? 'selected' : ''; ?>>Trực tiếp
                        </option>
                        <option value="Tư vấn" <?php echo ($_GET['loai_lich'] ?? '') === 'Tư vấn' ? 'selected' : ''; ?>>
                            Tư vấn</option>
                        <option value="Tại nhà"
                            <?php echo ($_GET['loai_lich'] ?? '') === 'Tại nhà' ? 'selected' : ''; ?>>Tại nhà</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Ngày hẹn</label>
                    <input type="date" class="form-control" name="ngay_hen"
                        value="<?php echo htmlspecialchars($_GET['ngay_hen'] ?? ''); ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Bác sĩ</label>
                    <select class="form-select" name="bac_si_id">
                        <option value="">Tất cả</option>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?php echo $doctor['id']; ?>"
                                <?php echo ($_GET['bac_si_id'] ?? '') == $doctor['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($doctor['ten']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Appointments Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="15%">Bệnh nhân</th>
                        <th width="15%">Bác sĩ</th>
                        <th width="10%">Ngày hẹn</th>
                        <th width="8%">Giờ hẹn</th>
                        <th width="10%">Loại lịch</th>
                        <th width="12%">Trạng thái</th>
                        <th width="10%">Ngày tạo</th>
                        <th width="15%" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted">Không có lịch hẹn nào</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><span class="badge bg-secondary">#<?php echo $appointment['id']; ?></span></td>
                                <td>
                                    <div class="patient-info">
                                        <strong><?php echo htmlspecialchars($appointment['ten_benh_nhan']); ?></strong>
                                        <small
                                            class="d-block text-muted"><?php echo htmlspecialchars($appointment['so_dien_thoai']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="doctor-info">
                                        <strong><?php echo htmlspecialchars($appointment['ten_bac_si']); ?></strong>
                                        <small
                                            class="d-block text-muted"><?php echo htmlspecialchars($appointment['chuyen_khoa']); ?></small>
                                    </div>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($appointment['ngay_hen'])); ?></td>
                                <td><span
                                        class="time-badge"><?php echo date('H:i', strtotime($appointment['gio_hen'])); ?></span>
                                </td>
                                <td>
                                    <?php
                                    $typeClass = match ($appointment['loai_lich']) {
                                        'Trực tiếp' => 'primary',
                                        'Tư vấn' => 'info',
                                        'Tại nhà' => 'warning',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?php echo $typeClass; ?>">
                                        <?php echo htmlspecialchars($appointment['loai_lich']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = match ($appointment['trang_thai']) {
                                        'Chờ xác nhận' => 'warning',
                                        'Đã xác nhận' => 'success',
                                        'Đang khám' => 'info',
                                        'Hoàn thành' => 'secondary',
                                        'Đã hủy' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($appointment['trang_thai']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($appointment['ngay_tao'])); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info"
                                        onclick="viewAppointment(<?php echo $appointment['id']; ?>)" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if (in_array($appointment['trang_thai'], ['Chờ xác nhận', 'Đã xác nhận'])): ?>
                                        <button class="btn btn-sm btn-danger"
                                            onclick="cancelAppointment(<?php echo $appointment['id']; ?>)" title="Hủy lịch">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination-wrapper">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php
                        $currentFilters = $_GET;
                        unset($currentFilters['page']);
                        $queryString = http_build_query($currentFilters);
                        ?>

                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?<?php echo $queryString; ?>&page=<?php echo $page - 1; ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="?<?php echo $queryString; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?<?php echo $queryString; ?>&page=<?php echo $page + 1; ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <div class="pagination-info text-center mt-2">
                    Trang <?php echo $page; ?> / <?php echo $totalPages; ?> (Tổng: <?php echo number_format($total); ?> lịch
                    hẹn)
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Xem chi tiết -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết lịch hẹn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="./assets/js/admin_appointments.js"></script>