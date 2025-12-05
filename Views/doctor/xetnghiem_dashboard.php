<?php
require_once 'Views/layouts/layout_helper.php';

$xetnghiemDoctorSessionName = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'xetnghiem_doctor') ? $_SESSION['user_name'] : '';

$content = '
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-flask text-primary me-2"></i>
                Xét nghiệm Dashboard
            </h1>
            <p class="text-muted">Chào mừng bác sĩ Xét nghiệm ' . $_SESSION['user_name'] . ' - Chẩn đoán xét nghiệm</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <button class="btn btn-primary">
                <i class="fas fa-calendar-plus me-2"></i>Lịch xét nghiệm
            </button>
            <button class="btn btn-success">
                <i class="fas fa-flask me-2"></i>Xét nghiệm
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Yêu cầu xét nghiệm hôm nay
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat_total_today">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-flask fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Đã hoàn thành
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat_completed">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Đang chờ xử lý
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat_pending">0</div>
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
                    <h6 class="m-0 font-weight-bold text-primary">Thao tác nhanh</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="text-primary mb-2"><i class="fas fa-list me-2"></i>Yêu cầu xét nghiệm (Đã yêu cầu)</h5>
                        <div class="row g-2 align-items-end mb-2">
                          <div class="col-auto">
                            <label class="form-label mb-1">Ngày (Mặc định: Hôm nay)</label>
                            <input type="date" class="form-control" id="xetnghiem_date">
                          </div>
                          <div class="col-auto">
                            <label class="form-label mb-1">Tìm kiếm (Mã bệnh nhân)</label>
                            <input type="text" class="form-control" id="xetnghiem_name" placeholder="Nhập mã bệnh nhân...">
                          </div>
                          <div class="col-auto">
                            <button type="button" class="btn btn-outline-secondary" id="xetnghiem_filter_btn"><i class="fas fa-filter me-1"></i>Lọc</button>
                          </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="xetnghiemRequestedTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:80px" class="text-center">ID</th>
                                        <th style="width:120px" class="text-center">Mã Bệnh Nhân</th>
                                        <th>Họ tên</th>
                                        <th style="width:80px" class="text-center">Tuổi</th>
                                        <th style="width:90px" class="text-center">Giới tính</th>
                                        <th>Yêu cầu xét nghiệm</th>
                                        <th style="width:160px">Ngày giờ</th>
                                        <th style="width:120px" class="text-center">Trạng thái</th>
                                        <th style="width:120px" class="text-center">Xem</th>
                                        <th style="width:140px" class="text-center">Trả kết quả</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td colspan="10" class="text-center text-muted">Đang tải...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Xetnghiem details (read-only, doctor-like layout) -->
<div class="modal fade" id="xetnghiemDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title"><i class="fas fa-flask me-2"></i>Phiếu yêu cầu xét nghiệm (Xem)</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3 text-center">
          <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:60px;object-fit:contain;margin-bottom:10px;">
          <div class="fw-bold" style="font-size:18px">PHIẾU YÊU CẦU XÉT NGHIỆM</div>
        </div>

        <div class="row mb-2">
          <div class="col-md-6">
            <label class="form-label fw-bold">Cơ sở y tế</label>
            <input type="text" class="form-control" id="xn_clinic_name" readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Điện thoại</label>
            <input type="text" class="form-control" id="xn_phone" readonly>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-bold">Quận</label>
            <input type="text" class="form-control" id="xn_quan" readonly>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label fw-bold">Họ tên người bệnh</label>
            <input type="text" class="form-control" id="xn_patient_name" readonly>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-bold">Tuổi</label>
            <input type="text" class="form-control" id="xn_patient_age" readonly>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-bold">Nam/Nữ</label>
            <input type="text" class="form-control" id="xn_patient_gender" readonly>
          </div>
          <div class="col-12">
            <label class="form-label fw-bold">Địa chỉ</label>
            <input type="text" class="form-control" id="xn_address" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Đối tượng</label>
            <input type="text" class="form-control" id="xn_patient_type" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Số thẻ BHYT</label>
            <input type="text" class="form-control" id="xn_insurance_number" readonly>
          </div>
        </div>

        <div class="mt-3">
          <label class="form-label fw-bold">Giờ chỉ định:</label>
          <input type="text" class="form-control" id="xn_order_time" readonly>
        </div>

        <div class="mt-3">
          <label class="form-label fw-bold">Chuẩn đoán:</label>
          <input type="text" class="form-control" id="xn_diagnosis" readonly>
        </div>

        <div class="mt-3 position-relative">
          <label class="form-label fw-bold text-center w-100 d-block" style="font-size:16px">YÊU CẦU XÉT NGHIỆM</label>
          <textarea class="form-control" id="xn_request" rows="6" readonly></textarea>
        </div>

        <div class="row mt-4">
          <div class="col-md-6"></div>
          <div class="col-md-6 text-center">
            <div class="mb-2 d-flex align-items-center justify-content-center gap-2">
              <span>Ngày</span>
              <input type="text" class="form-control" id="xn_day" style="width:60px" readonly>
              <span>Tháng</span>
              <input type="text" class="form-control" id="xn_month" style="width:60px" readonly>
              <span>Năm</span>
              <input type="text" class="form-control" id="xn_year" style="width:80px" readonly>
            </div>
            <div class="fw-bold">BÁC SĨ KHÁM</div>
            <input type="text" class="form-control mt-2" id="xn_doctor" readonly>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Return Xetnghiem Result -->
<div class="modal fade" id="returnXetnghiemResultModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-flask me-2"></i>Trả kết quả xét nghiệm</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Header -->
        <div class="text-center mb-4">
          <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:60px;object-fit:contain;margin-bottom:10px;">
          <div class="fw-bold" style="font-size: 20px; color: #dc3545;">PHÒNG KHÁM ĐA KHOA THINHVIET</div>
          <div class="fw-bold" style="font-size: 16px;">KHOA XÉT NGHIỆM</div>
          <div class="fw-bold" style="font-size: 18px; color: #dc3545;">KẾT QUẢ XÉT NGHIỆM</div>
          <div class="d-flex justify-content-center mt-2" style="gap: 10px;">
            <span>Ngày ĐK: <span id="result_reg_date"></span></span>
            <span>|</span>
            <span id="result_reg_time"></span>
          </div>
        </div>
        <hr>

        <!-- Patient Information -->
        <div class="text-center mb-3">
          <div class="fw-bold" style="font-size: 16px;">THÔNG TIN CHUNG</div>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <div class="row mb-2">
              <div class="col-4"><strong>Mã bệnh nhân:</strong></div>
              <div class="col-8"><input type="text" class="form-control form-control-sm" id="result_patient_id" readonly></div>
            </div>
            <div class="row mb-2">
              <div class="col-4"><strong>Họ và tên:</strong></div>
              <div class="col-8"><input type="text" class="form-control form-control-sm" id="result_patient_name" readonly></div>
            </div>
            <div class="row mb-2">
              <div class="col-4"><strong>Địa chỉ:</strong></div>
              <div class="col-8"><input type="text" class="form-control form-control-sm" id="result_patient_address" readonly></div>
            </div>
            <div class="row mb-2">
              <div class="col-4"><strong>Chẩn đoán sơ bộ:</strong></div>
              <div class="col-8"><input type="text" class="form-control form-control-sm" id="result_diagnosis" readonly></div>
            </div>
            <div class="row mb-2">
              <div class="col-4"><strong>Chất lượng mẫu:</strong></div>
              <div class="col-8"><input type="text" class="form-control form-control-sm" id="result_sample_status" placeholder="Nhập chất lượng mẫu..."></div>
            </div>
            <div class="row mb-2">
              <div class="col-4"><strong>Vị trí lấy mẫu:</strong></div>
              <div class="col-8"><input type="text" class="form-control form-control-sm" id="result_sample_location" placeholder="Nhập vị trí lấy mẫu..."></div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="row mb-2">
              <div class="col-4"><strong>Tuổi:</strong></div>
              <div class="col-8"><input type="text" class="form-control form-control-sm" id="result_patient_age" readonly></div>
            </div>
            <div class="row mb-2">
              <div class="col-4"><strong>Giới tính:</strong></div>
              <div class="col-8"><input type="text" class="form-control form-control-sm" id="result_patient_gender" readonly></div>
            </div>
            <div class="row mb-2">
              <div class="col-4"><strong>BS yêu cầu:</strong></div>
              <div class="col-8"><input type="text" class="form-control form-control-sm" id="result_requesting_doctor" readonly></div>
            </div>
            <div class="row mb-2">
              <div class="col-4"><strong>Giờ nhận kết quả:</strong></div>
              <div class="col-8"><input type="text" class="form-control form-control-sm" id="result_return_time" readonly placeholder="Giờ:phút:giây"></div>
            </div>
          </div>
        </div>
        <hr>

        <!-- Test Results Section -->
        <div class="text-center mb-3">
          <div class="fw-bold" style="font-size: 16px;" id="test_section_title">XÉT NGHIỆM MÁU - NƯỚC TIỂU - PHÂN</div>
          <div class="fw-bold" style="font-size: 14px;">BẢNG CHỈ SỐ KẾT QUẢ XÉT NGHIỆM</div>
        </div>

        <!-- Container for form templates -->
        <div id="testFormsContainer">
          <!-- Form: Máu toàn phần -->
          <div id="formMauToanPhan" class="test-form-template" style="display: none;">
        <div class="table-responsive">
              <table class="table table-bordered">
            <thead class="table-light">
              <tr>
                <th style="width: 60px;" class="text-center fw-bold">STT</th>
                <th class="fw-bold">Xét nghiệm</th>
                <th class="fw-bold">Giá trị tham chiếu</th>
                <th class="fw-bold">Kết quả</th>
                <th style="width: 80px;" class="fw-bold">Đơn vị</th>
                <th class="fw-bold">Máy/QTKT</th>
              </tr>
            </thead>
                <tbody id="testResultsBodyMauToanPhan">
                  <!-- Rows will be generated by JavaScript -->
            </tbody>
          </table>
            </div>
        </div>

          <!-- Form: Máu/Nước tiểu -->
          <div id="formMauNuocTieu" class="test-form-template" style="display: none;">
        <div class="table-responsive">
              <table class="table table-bordered">
            <thead class="table-light">
              <tr>
                <th style="width: 60px;" class="text-center fw-bold">STT</th>
                <th class="fw-bold">Xét nghiệm</th>
                <th class="fw-bold">Giá trị tham chiếu</th>
                <th class="fw-bold">Kết quả</th>
                <th style="width: 80px;" class="fw-bold">Đơn vị</th>
                <th class="fw-bold">Máy/QTKT</th>
              </tr>
            </thead>
                <tbody id="testResultsBodyMauNuocTieu">
                  <!-- Rows will be generated by JavaScript -->
            </tbody>
          </table>
            </div>
        </div>

          <!-- Form: Other (fallback) -->
          <div id="formOther" class="test-form-template" style="display: none;">
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead class="table-light">
                  <tr>
                    <th style="width: 60px;" class="text-center fw-bold">STT</th>
                    <th class="fw-bold">Xét nghiệm</th>
                    <th class="fw-bold">Giá trị tham chiếu</th>
                    <th class="fw-bold">Kết quả</th>
                    <th style="width: 80px;" class="fw-bold">Đơn vị</th>
                    <th class="fw-bold">Máy/QTKT</th>
                  </tr>
                </thead>
                <tbody id="testResultsBodyOther">
                  <!-- Rows will be generated by JavaScript -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="mb-3">
          <small class="text-muted">
            <strong>Ghi chú:</strong> Kết quả in đậm là kết quả nằm ngoài khoảng tham chiếu.<br>
            Xét nghiệm đánh dấu (*) là xét nghiệm được thực hiện bởi PXN chuyển gửi.
          </small>
        </div>

        <!-- Footer -->
        <div class="row mt-4">
          <div class="col-md-6"></div>
          <div class="col-md-6 text-center">
            <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
              <span>Ngày</span>
              <input type="text" class="form-control" id="result_day" style="width: 60px;" value="">
              <span>tháng</span>
              <input type="text" class="form-control" id="result_month" style="width: 60px;" value="">
              <span>năm</span>
              <input type="text" class="form-control" id="result_year" style="width: 80px;" value="">
            </div>
            <div class="fw-bold">BÁC SĨ XÉT NGHIỆM</div>
            <input type="text" class="form-control mt-2" id="result_examining_doctor" value="' . $_SESSION['user_name'] . '" readonly>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="saveTestResult">
          <i class="fas fa-save me-1"></i>Lưu kết quả
        </button>
        <button type="button" class="btn btn-primary" id="printTestResult">
          <i class="fas fa-print me-1"></i>In kết quả
        </button>
        <button type="button" class="btn btn-warning" id="completeTestResult">
          <i class="fas fa-check-circle me-1"></i>Hoàn thành
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>

<!-- Include CSS for result validation -->
<link rel="stylesheet" href="assets/css/xetnghiem_result.css">

<!-- Include JavaScript for dashboard functionality -->
<script src="assets/js/xetnghiem_dashboard.js"></script>
<script src="assets/js/xetnghiem_result_validation.js"></script>
<script>
// Initialize validator when page loads
document.addEventListener("DOMContentLoaded", function() {
    if (typeof XetNghiemResultValidator !== "undefined") {
        window.xetNghiemValidator = new XetNghiemResultValidator();
        console.log("XetNghiemResultValidator initialized");
    } else {
        console.error("XetNghiemResultValidator not found");
    }
});
</script>';

// Render page using layout helper
renderLayout($content, 'Xét nghiệm Dashboard - Hệ thống Quản lý Bệnh viện');
?>
          </div>

        </div>

      </div>

    </div>

  </div>

</div>



<!-- Modal for Return Xetnghiem Result -->

<div class="modal fade" id="returnXetnghiemResultModal" tabindex="-1" aria-hidden="true">

  <div class="modal-dialog modal-xl">

    <div class="modal-content">

      <div class="modal-header bg-success text-white">

        <h5 class="modal-title"><i class="fas fa-flask me-2"></i>Trả kết quả xét nghiệm</h5>

        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>

      </div>

      <div class="modal-body">

        <!-- Header -->

        <div class="text-center mb-4">

          <div class="fw-bold" style="font-size: 20px; color: #dc3545;">PHÒNG KHÁM ĐA KHOA THINHVIET</div>

          <div class="fw-bold" style="font-size: 16px;">KHOA XÉT NGHIỆM</div>

          <div class="fw-bold" style="font-size: 18px; color: #dc3545;">KẾT QUẢ XÉT NGHIỆM</div>

          <div class="d-flex justify-content-center mt-2" style="gap: 10px;">

            <span>Ngày ĐK: <span id="result_reg_date"></span></span>

            <span>|</span>

            <span id="result_reg_time"></span>

          </div>

        </div>

        <hr>



        <!-- Patient Information -->

        <div class="text-center mb-3">
          <div class="fw-bold" style="font-size: 16px;">THÔNG TIN CHUNG</div>
        </div>
        <div class="row mb-3">

          <div class="col-md-6">

            <div class="row mb-2">

              <div class="col-4"><strong>Mã bệnh nhân:</strong></div>
              <div class="col-8"><input type="text" class="form-control form-control-sm" id="result_patient_id" readonly></div>

            </div>

            <div class="row mb-2">

              <div class="col-4"><strong>Họ và tên:</strong></div>

              <div class="col-8"><input type="text" class="form-control form-control-sm" id="result_patient_name" readonly></div>

            </div>

            <div class="row mb-2">

              <div class="col-4"><strong>Địa chỉ:</strong></div>

              <div class="col-8"><input type="text" class="form-control form-control-sm" id="result_patient_address" readonly></div>

            </div>

            <div class="row mb-2">

              <div class="col-4"><strong>Chẩn đoán sơ bộ:</strong></div>

              <div class="col-8"><input type="text" class="form-control form-control-sm" id="result_diagnosis" readonly></div>

            </div>

            <div class="row mb-2">

              <div class="col-4"><strong>Tình trạng mẫu:</strong></div>

              <div class="col-8"><input type="text" class="form-control form-control-sm" id="result_sample_status" placeholder="Nhập tình trạng mẫu..."></div>

            </div>

            <div class="row mb-2">
              <div class="col-4"><strong>Vị trí lấy mẫu:</strong></div>
              <div class="col-8"><input type="text" class="form-control form-control-sm" id="result_sample_location" placeholder="Nhập vị trí lấy mẫu..."></div>
            </div>

          </div>

          <div class="col-md-6">

            <div class="row mb-2">

              <div class="col-4"><strong>Tuổi:</strong></div>

              <div class="col-8"><input type="text" class="form-control form-control-sm" id="result_patient_age" readonly></div>

            </div>

            <div class="row mb-2">

              <div class="col-4"><strong>Giới tính:</strong></div>

              <div class="col-8"><input type="text" class="form-control form-control-sm" id="result_patient_gender" readonly></div>

            </div>

            <div class="row mb-2">

              <div class="col-4"><strong>BS yêu cầu:</strong></div>

              <div class="col-8"><input type="text" class="form-control form-control-sm" id="result_requesting_doctor" readonly></div>

            </div>

          </div>

        </div>

        <hr>



        <!-- Test Results Section -->

        <div class="text-center mb-3">

          <div class="fw-bold" style="font-size: 16px;" id="test_section_title">XÉT NGHIỆM MÁU - NƯỚC TIỂU - PHÂN</div>

          <div class="fw-bold" style="font-size: 14px;">BẢNG CHỈ SỐ KẾT QUẢ XÉT NGHIỆM</div>

        </div>



        <!-- Container for form templates -->
        <div id="testFormsContainer">
          <!-- Form: Máu toàn phần -->
          <div id="formMauToanPhan" class="test-form-template" style="display: none;">
        <div class="table-responsive">
              <table class="table table-bordered">
            <thead class="table-light">
              <tr>
                <th style="width: 60px;" class="text-center fw-bold">STT</th>
                <th class="fw-bold">Xét nghiệm</th>
                <th class="fw-bold">Giá trị tham chiếu</th>
                <th class="fw-bold">Kết quả</th>
                <th style="width: 80px;" class="fw-bold">Đơn vị</th>
                <th class="fw-bold">Máy/QTKT</th>
              </tr>
            </thead>
                <tbody id="testResultsBodyMauToanPhan">
                  <!-- Rows will be generated by JavaScript -->
            </tbody>
          </table>
            </div>
        </div>

          <!-- Form: Máu/Nước tiểu -->
          <div id="formMauNuocTieu" class="test-form-template" style="display: none;">
        <div class="table-responsive">

              <table class="table table-bordered">
            <thead class="table-light">

              <tr>

                <th style="width: 60px;" class="text-center fw-bold">STT</th>

                <th class="fw-bold">Xét nghiệm</th>

                <th class="fw-bold">Giá trị tham chiếu</th>

                <th class="fw-bold">Kết quả</th>

                <th style="width: 80px;" class="fw-bold">Đơn vị</th>

                <th class="fw-bold">Máy/QTKT</th>

              </tr>

            </thead>

                <tbody id="testResultsBodyMauNuocTieu">
                  <!-- Rows will be generated by JavaScript -->
            </tbody>

          </table>

            </div>
        </div>



          <!-- Form: Other (fallback) -->
          <div id="formOther" class="test-form-template" style="display: none;">
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead class="table-light">
                  <tr>
                    <th style="width: 60px;" class="text-center fw-bold">STT</th>
                    <th class="fw-bold">Xét nghiệm</th>
                    <th class="fw-bold">Giá trị tham chiếu</th>
                    <th class="fw-bold">Kết quả</th>
                    <th style="width: 80px;" class="fw-bold">Đơn vị</th>
                    <th class="fw-bold">Máy/QTKT</th>
                  </tr>
                </thead>
                <tbody id="testResultsBodyOther">
                  <!-- Rows will be generated by JavaScript -->
                </tbody>
              </table>
            </div>
          </div>
        </div>



        <!-- Notes -->

        <div class="mb-3">

          <small class="text-muted">

            <strong>Ghi chú:</strong> Kết quả in đậm là kết quả nằm ngoài khoảng tham chiếu.<br>

            Xét nghiệm đánh dấu (*) là xét nghiệm được thực hiện bởi PXN chuyển gửi.

          </small>

        </div>



        <!-- Footer -->

        <div class="row mt-4">

          <div class="col-md-6"></div>

          <div class="col-md-6 text-center">

            <div class="d-flex align-items-center justify-content-center gap-2 mb-2">

              <span>Ngày</span>

              <input type="text" class="form-control" id="result_day" style="width: 60px;" value="">

              <span>tháng</span>

              <input type="text" class="form-control" id="result_month" style="width: 60px;" value="">

              <span>năm</span>

              <input type="text" class="form-control" id="result_year" style="width: 80px;" value="">

            </div>

            <div class="fw-bold">BÁC SĨ XÉT NGHIỆM</div>

            <input type="text" class="form-control mt-2" id="result_examining_doctor" value="' . $_SESSION['user_name'] . '" readonly>

          </div>

        </div>

      </div>

      <div class="modal-footer">

        <button type="button" class="btn btn-success" id="saveTestResult">

          <i class="fas fa-save me-1"></i>Lưu kết quả

        </button>

        <button type="button" class="btn btn-primary" id="printTestResult">

          <i class="fas fa-print me-1"></i>In kết quả

        </button>

        <button type="button" class="btn btn-warning" id="completeTestResult">

          <i class="fas fa-check-circle me-1"></i>Hoàn thành

        </button>

        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>

      </div>

    </div>

  </div>

</div>



<!-- Include CSS for result validation -->

<link rel="stylesheet" href="assets/css/xetnghiem_result.css">



<!-- Include JavaScript for dashboard functionality -->
<script src="assets/js/xetnghiem_dashboard.js"></script>
<script src="assets/js/xetnghiem_result_validation.js"></script>

<script>

// Initialize validator when page loads

document.addEventListener("DOMContentLoaded", function() {

    if (typeof XetNghiemResultValidator !== "undefined") {

        window.xetNghiemValidator = new XetNghiemResultValidator();

        console.log("XetNghiemResultValidator initialized");

    } else {

        console.error("XetNghiemResultValidator not found");

    }

});

</script>';



// Render page using layout helper

renderLayout($content, 'Xét nghiệm Dashboard - Hệ thống Quản lý Bệnh viện');

?>
