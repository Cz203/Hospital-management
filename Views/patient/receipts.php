<?php
require_once 'Views/layouts/layout_helper.php';

$content = '
<div class="container-fluid">
	<!-- Page Header -->
	<div class="d-flex justify-content-between align-items-center mb-4">
		<div>
			<h1 class="h3 mb-0 fw-semibold">
				<i class="fas fa-receipt text-primary me-2"></i>
				Biên lai viện phí
			</h1>
			<p class="mb-0 text-muted">Xem và in các biên lai thanh toán của bạn</p>
		</div>
		<div class="d-flex gap-2">
			<button class="btn btn-primary" onclick="refreshReceipts()">
				<i class="fas fa-sync-alt me-2"></i>Làm mới
			</button>
			<a href="./patient_dashboard" class="btn btn-outline-primary">
				<i class="fas fa-arrow-left me-2"></i>Quay lại
			</a>
		</div>
	</div>

	<!-- Statistics Cards -->
	<div class="row g-3 mb-4">
		<div class="col-xl-3 col-md-6">
			<div class="card dashboard-card border-0 shadow-sm h-100">
				<div class="card-body d-flex align-items-center">
					<div class="icon-circle bg-soft-primary me-3">
						<i class="fas fa-receipt"></i>
					</div>
					<div class="flex-grow-1">
						<div class="card-label">Tổng số biên lai</div>
						<div class="card-value"><span id="statTotal">0</span></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-md-6">
			<div class="card dashboard-card border-0 shadow-sm h-100">
				<div class="card-body d-flex align-items-center">
					<div class="icon-circle bg-soft-info me-3">
						<i class="fas fa-list"></i>
					</div>
					<div class="flex-grow-1">
						<div class="card-label">Trang hiện tại</div>
						<div class="card-value"><span id="statPage">1</span> / <span id="statTotalPages">1</span></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-md-6">
			<div class="card dashboard-card border-0 shadow-sm h-100">
				<div class="card-body d-flex align-items-center">
					<div class="icon-circle bg-soft-success me-3">
						<i class="fas fa-table"></i>
					</div>
					<div class="flex-grow-1">
						<div class="card-label">Số bản ghi/trang</div>
						<div class="card-value"><span id="statLimit">15</span></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-md-6">
			<div class="card dashboard-card border-0 shadow-sm h-100">
				<div class="card-body d-flex align-items-center">
					<div class="icon-circle bg-soft-warning me-3">
						<i class="fas fa-filter"></i>
					</div>
					<div class="flex-grow-1">
						<div class="card-label">Bộ lọc</div>
						<div class="card-value"><span id="filterStatus">Tất cả</span></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Filter Section -->
	<div class="card shadow-sm border-0 mb-4">
		<div class="card-header border-0 bg-white">
			<h6 class="mb-0 fw-semibold">
				<i class="fas fa-filter text-primary me-2"></i>Bộ lọc tìm kiếm
			</h6>
		</div>
		<div class="card-body">
			<div class="row g-3 align-items-end">
				<div class="col-md-3">
					<label class="form-label fw-semibold mb-2">Lọc theo ngày:</label>
					<input type="date" class="form-control" id="selectedDate">
				</div>
				<div class="col-md-3">
					<label class="form-label fw-semibold mb-2">Mã biên lai:</label>
					<input type="text" id="receiptCode" class="form-control" placeholder="Nhập mã biên lai...">
				</div>
				<div class="col-md-6">
					<div class="d-flex gap-2">
						<button class="btn btn-primary" onclick="searchReceipts()">
							<i class="fas fa-search me-2"></i>Tìm kiếm
						</button>
						<button class="btn btn-outline-secondary" onclick="resetFilters()">
							<i class="fas fa-redo me-2"></i>Đặt lại
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Receipts Table -->
	<div class="card shadow-sm border-0">
		<div class="card-header border-0 d-flex justify-content-between align-items-center bg-white">
			<div>
				<h6 class="mb-0 fw-semibold">Danh sách biên lai</h6>
				<small class="text-muted">Tất cả biên lai thanh toán của bạn</small>
			</div>
		</div>
		<div class="card-body">
			<div id="loadingIndicator" class="text-center py-5" style="display: none;">
				<div class="spinner-border text-primary" role="status">
					<span class="visually-hidden">Đang tải...</span>
				</div>
				<p class="mt-2 text-muted">Đang tải dữ liệu...</p>
			</div>

			<div id="receiptsTable" style="display: none;">
				<div class="table-responsive">
					<table class="table table-hover align-middle">
						<thead class="table-light">
							<tr>
								<th style="width: 60px;" class="text-center">STT</th>
								<th style="width: 120px;">Ngày</th>
								<th style="width: 100px;">Giờ</th>
								<th>Mã biên lai</th>
								<th style="width: 150px;">Trạng thái</th>
								<th style="width: 150px;" class="text-center">Thao tác</th>
							</tr>
						</thead>
						<tbody id="receiptsTableBody">
							<!-- Data will be loaded here -->
						</tbody>
					</table>
				</div>

				<!-- Pagination -->
				<nav aria-label="Page navigation" class="mt-4">
					<ul class="pagination justify-content-center" id="pagination">
						<!-- Pagination will be generated here -->
					</ul>
				</nav>
			</div>

			<div id="emptyState" class="text-center py-5" style="display: none;">
				<i class="fas fa-receipt fa-3x text-muted mb-3"></i>
				<h5 class="text-muted">Chưa có biên lai nào</h5>
				<p class="text-muted mb-0">Hãy thử thay đổi bộ lọc tìm kiếm</p>
			</div>
		</div>
	</div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="receiptDetailModal" tabindex="-1" aria-labelledby="receiptDetailModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h5 class="modal-title" id="receiptDetailModalLabel">
					<i class="fas fa-receipt me-2"></i>Chi tiết biên lai
				</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" id="receiptDetailModalBody">
				<div class="text-center py-5">
					<div class="spinner-border text-primary" role="status">
						<span class="visually-hidden">Đang tải...</span>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
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

.table thead th {
	font-weight: 600;
	font-size: 0.875rem;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	color: #495057;
}

.table tbody tr {
	transition: background-color 0.2s ease;
}

.table tbody tr:hover {
	background-color: #f8f9fa;
}
</style>

<script src="assets/js/patient_receipts.js"></script>
';

renderLayout($content, 'Biên lai viện phí - Hệ thống Quản lý Bệnh viện');