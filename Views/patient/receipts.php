<?php
require_once 'Views/layouts/layout_helper.php';

$content = '
<div class="container-fluid">
	<!-- Page Header -->
	<div class="d-flex justify-content-between align-items-center mb-4">
		<div>
			<h1 class="h3 mb-0 text-gray-800">
				<i class="fas fa-receipt text-primary me-2"></i>
				Biên lai viện phí
			</h1>
			<p class="text-muted">Xem và in các biên lai thanh toán của bạn</p>
		</div>
		<div class="d-flex gap-2">
			<button class="btn btn-outline-primary" onclick="refreshReceipts()">
				<i class="fas fa-sync-alt me-2"></i>Tải lại
			</button>
			<a href="./patient_dashboard" class="btn btn-outline-secondary">
				<i class="fas fa-arrow-left me-2"></i>Quay lại
			</a>
		</div>
	</div>

	<!-- Filter Section -->
	<div class="card shadow mb-4">
		<div class="card-header bg-primary text-white">
			<h6 class="mb-0"><i class="fas fa-filter me-2"></i>Bộ lọc</h6>
		</div>
		<div class="card-body">
			<div class="row g-3 align-items-end">
				<div class="col-md-3">
					<label class="form-label fw-bold">Chọn ngày:</label>
					<input type="date" id="selectedDate" class="form-control">
				</div>
				<div class="col-md-3">
					<label class="form-label fw-bold">Mã biên lai:</label>
					<input type="text" id="receiptCode" class="form-control" placeholder="Nhập mã biên lai...">
				</div>
				<div class="col-md-6">
					<button class="btn btn-primary me-2" onclick="searchReceipts()">
						<i class="fas fa-search me-2"></i>Tìm kiếm
					</button>
					<button class="btn btn-outline-secondary" onclick="resetFilters()">
						<i class="fas fa-redo me-2"></i>Đặt lại
					</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Statistics Cards -->
	<div class="row mb-4">
		<div class="col-md-4">
			<div class="card border-left-primary shadow h-100 py-2">
				<div class="card-body">
					<div class="row no-gutters align-items-center">
						<div class="col mr-2">
							<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
								Tổng số biên lai
							</div>
							<div class="h5 mb-0 font-weight-bold text-gray-800"><span id="statTotal">0</span></div>
						</div>
						<div class="col-auto">
							<i class="fas fa-receipt fa-2x text-gray-300"></i>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card border-left-info shadow h-100 py-2">
				<div class="card-body">
					<div class="row no-gutters align-items-center">
						<div class="col mr-2">
							<div class="text-xs font-weight-bold text-info text-uppercase mb-1">
								Trang hiện tại
							</div>
							<div class="h5 mb-0 font-weight-bold text-gray-800"><span id="statPage">1</span> / <span id="statTotalPages">1</span></div>
						</div>
						<div class="col-auto">
							<i class="fas fa-list fa-2x text-gray-300"></i>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card border-left-success shadow h-100 py-2">
				<div class="card-body">
					<div class="row no-gutters align-items-center">
						<div class="col mr-2">
							<div class="text-xs font-weight-bold text-success text-uppercase mb-1">
								Số bản ghi/trang
							</div>
							<div class="h5 mb-0 font-weight-bold text-gray-800"><span id="statLimit">15</span></div>
						</div>
						<div class="col-auto">
							<i class="fas fa-table fa-2x text-gray-300"></i>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Receipts Table -->
	<div class="card shadow">
		<div class="card-header">
			<h5 class="mb-0">
				<i class="fas fa-list text-primary me-2"></i>
				Danh sách biên lai
			</h5>
		</div>
		<div class="card-body">
			<div id="loadingIndicator" class="text-center py-5" style="display:none;">
				<div class="spinner-border text-primary" role="status">
					<span class="visually-hidden">Đang tải...</span>
				</div>
				<p class="mt-2 text-muted">Đang tải biên lai...</p>
			</div>

			<div id="receiptsTable" style="display:none;">
				<div class="table-responsive">
					<table class="table table-hover table-bordered align-middle">
						<thead class="table-primary">
							<tr>
								<th style="width: 80px;">STT</th>
								<th style="width: 120px;">Ngày</th>
								<th style="width: 100px;">Giờ</th>
								<th>Mã biên lai</th>
								<th style="width: 180px;">Trạng thái</th>
								<th style="width: 180px; text-align:center;">Thao tác</th>
							</tr>
						</thead>
						<tbody id="receiptsTableBody"></tbody>
					</table>
				</div>

				<!-- Pagination -->
				<nav aria-label="Page navigation" class="mt-3">
					<ul class="pagination justify-content-center" id="pagination"></ul>
				</nav>
			</div>

			<div id="emptyState" class="text-center py-5" style="display:none;">
				<i class="fas fa-receipt fa-3x text-muted mb-3"></i>
				<h5 class="text-muted">Chưa có biên lai phù hợp</h5>
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

<script src="assets/js/patient_receipts.js"></script>
';

renderLayout($content, 'Biên lai viện phí - Hệ thống Quản lý Bệnh viện');
