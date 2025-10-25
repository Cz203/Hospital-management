<?php
// Payment Management Page for Reception
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-credit-card me-2"></i>Thanh toán biên lai
                </h2>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="loadReceipts()">
                        <i class="fas fa-sync-alt me-1"></i>Làm mới
                    </button>
                </div>
            </div>
            
            <!-- VNPAY Success/Error Messages -->
            <?php if (isset($_GET['vnpay_success']) && $_GET['vnpay_success'] == '1'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Thanh toán chuyển khoản thành công!</strong> Biên lai đã được cập nhật trạng thái "Đã thanh toán chuyển khoản".
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            
            <?php if (isset($_GET['vnpay_error']) && $_GET['vnpay_error'] == '1'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Thanh toán chuyển khoản thất bại!</strong> Vui lòng thử lại hoặc chọn phương thức thanh toán khác.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card search-filter-card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="searchKeyword" class="form-label">Tìm kiếm</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchKeyword" 
                                       placeholder="Mã biên lai, tên bệnh nhân, mã BN...">
                                <button class="btn btn-outline-secondary" type="button" onclick="searchReceipts()">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="dateFilter" class="form-label">Ngày</label>
                            <input type="date" class="form-control" id="dateFilter" onchange="filterByDate()">
                        </div>
                        <div class="col-md-2">
                            <label for="limitFilter" class="form-label">Số lượng</label>
                            <select class="form-select" id="limitFilter" onchange="filterReceipts()">
                                <option value="10">10</option>
                                <option value="20" selected>20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Thống kê</label>
                            <div class="stats-container-wrapper">
                                <div class="stats-container">
                                    <div class="stat-card">
                                        <div class="stat-number text-warning" id="unpaidCount">0</div>
                                        <div class="stat-label">Chưa thanh toán</div>
                                    </div>
                                    <div class="stat-card">
                                        <div class="stat-number text-success" id="paidCount">0</div>
                                        <div class="stat-label">Đã thanh toán</div>
                                    </div>
                                    <div class="stat-card">
                                        <div class="stat-number text-primary" id="totalRevenue">0 VNĐ</div>
                                        <div class="stat-label">Doanh thu</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipts Table -->
    <div class="row">
        <div class="col-12">
            <div class="card table-payment">
                <div class="card-header">
                    <h5 class="mb-0">Danh sách biên lai</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>STT</th>
                                    <th>Mã biên lai</th>
                                    <th>Bệnh nhân</th>
                                    <th>Mã BN</th>
                                    <th>Bác sĩ</th>
                                    <th>Ngày khám</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="receiptsTableBody">
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Đang tải...</span>
                                        </div>
                                        <div class="mt-2">Đang tải dữ liệu...</div>
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

<!-- Payment Modal -->
<div class="modal fade modal-payment" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">
                    <i class="fas fa-credit-card me-2"></i>Thanh toán biên lai
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Mã biên lai:</strong> <span id="modalReceiptCode" class="text-primary"></span>
                    </div>
                    <div class="col-6">
                        <strong>Mã bệnh nhân:</strong> <span id="modalPatientCode" class="text-primary"></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Bệnh nhân:</strong> <span id="modalPatientName" class="text-primary"></span>
                    </div>
                    <div class="col-6">
                        <strong>Số tiền thanh toán:</strong> <span id="modalAmount" class="h5 text-success"></span>
                    </div>
                </div>
                <form id="paymentForm">
                    <input type="hidden" id="paymentReceiptId" name="receipt_id">
                    <div class="mb-3">
                        <label class="form-label">Phương thức thanh toán <span class="text-danger">*</span></label>
                        <div class="payment-methods">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <button type="button" class="btn btn-outline-success payment-method-btn w-100" 
                                            data-method="Tiền mặt" onclick="selectPaymentMethod(this)">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                                            <span>Tiền mặt</span>
                                        </div>
                                    </button>
                                </div>
                                <div class="col-12 col-md-6">
                                    <button type="button" class="btn btn-outline-primary payment-method-btn w-100" 
                                            data-method="Thanh toán VNPAY" onclick="selectPaymentMethod(this)">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="fas fa-credit-card fa-2x mb-2"></i>
                                            <span>Thanh toán VNPAY</span>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="paymentMethod" name="payment_method" required>
                    </div>
                    
                    <!-- Cash Payment Fields (Hidden by default) -->
                    <div id="cashPaymentFields" class="cash-payment-section" style="display: none;">
                        <div class="text-center mb-4">
                            <h5 class="mb-0 text-primary">
                                <i class="fas fa-calculator me-2"></i>
                                Tính toán thanh toán tiền mặt
                            </h5>
                            <small class="text-muted">Nhập số tiền khách đưa để tính tiền thừa/thiếu</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-receipt me-2"></i>Tổng tiền
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-success text-white">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </span>
                                        <input type="text" class="form-control" id="totalAmount" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-hand-holding-usd me-2"></i>Khách đưa <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="fas fa-coins"></i>
                                        </span>
                                        <input type="text" class="form-control" id="customerAmount" placeholder="Nhập số tiền khách đưa">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-exchange-alt me-2"></i>Trả lại
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-info text-white">
                                            <i class="fas fa-undo"></i>
                                        </span>
                                        <input type="text" class="form-control" id="changeAmount" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Số tiền sẽ được tính toán tự động khi bạn nhập "Khách đưa"
                            </small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-success" id="confirmPaymentBtn" style="display: none;" onclick="confirmPayment()">
                    <i class="fas fa-check me-2"></i>Xác nhận thanh toán
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Details Modal -->
<div class="modal fade modal-receipt-detail" id="receiptDetailModal" tabindex="-1" aria-labelledby="receiptDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiptDetailModalLabel">
                    <i class="fas fa-receipt me-2"></i>Chi tiết biên lai
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Biên lai viện phí -->
                <div class="receipt-container">
                    <!-- Header -->
                    <div class="receipt-header">
                        <div class="receipt-title">
                            <h1 class="receipt-clinic-name">PHÒNG KHÁM ĐA KHOA THINHVIET</h1>
                            <h2 class="receipt-main-title">BIÊN LAI VIỆN PHÍ</h2>
                            <p class="receipt-subtitle">Viện phí</p>
                        </div>
                        <div class="receipt-stt">
                            <span class="stt-label">Mã BN:</span>
                            <span class="stt-number" id="detailPatientCode"></span>
                        </div>
                        <div class="receipt-stt">
                            <span class="stt-label">Số HD:</span>
                            <span class="stt-number" id="detailReceiptCode"></span>
                        </div>
                    </div>
                    
                    <!-- Body -->
                    <div class="receipt-body">
                        <!-- Patient Info -->
                        <div class="receipt-section">
                            <h6>Thông tin bệnh nhân</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Họ và Tên:</strong> <span id="detailPatientName"></span>
                                </div>
                                <div class="col-md-2">
                                    <strong>Tuổi:</strong> <span id="detailPatientAge"></span>
                                </div>
                                <div class="col-md-2">
                                    <strong>Giới tính:</strong> <span id="detailPatientGender"></span>
                                </div>
                                <div class="col-md-5">
                                    <strong>Địa chỉ:</strong> <span id="detailPatientAddress"></span>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <strong>Mã số BHYT:</strong> <span id="detailPatientInsurance"></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Nội Dung Thu:</strong> <span>Đề nghị thanh toán</span>
                                </div>
                            </div>
                        </div>

                        <!-- Services Table -->
                        <div class="receipt-section">
                            <h6>Chi tiết dịch vụ và thuốc</h6>
                            <table class="receipt-table">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Nội dung</th>
                                        <th>Số lượng</th>
                                        <th>Đơn giá (đồng)</th>
                                        <th>Thành tiền (đồng)</th>
                                        <th>Quỹ BHYT (đồng)</th>
                                        <th>Người Bệnh (đồng)</th>
                                    </tr>
                                </thead>
                                <tbody id="detailServicesTable">
                                    <!-- Services will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary -->
                        <div class="receipt-summary">
                            <div class="row">
                                <div class="col-12">
                                    <strong>Người bệnh trả:</strong> <span id="detailPatientAmountText">0</span>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <strong>Bằng chữ:</strong> <span id="detailAmountInWords"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="receipt-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Empty space on left -->
                            </div>
                            <div class="col-md-6">
                                <div class="signature-section text-center">
                                    <div class="signature-block">
                                        <div class="signature-date" id="detailCreatedDate">Ngày 25 tháng 10 năm 2025</div>
                                        <div class="signature-title"><strong>Người Lập Bảng Kê</strong></div>
                                        <div class="signature-instruction">(<u>Ký, ghi rõ họ tên</u>)</div>
                                        <div class="signature-name" id="detailCreatedBy">N/A</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Include Payment CSS -->
<link rel="stylesheet" href="assets/css/payment.css?v=<?php echo time(); ?>">

<!-- Include Payment JavaScript -->
<script src="assets/js/payment.js?v=<?php echo time(); ?>"></script>
