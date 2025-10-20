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
            <label class="form-label fw-bold">Quận/Huyện</label>
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
        <div class="row mb-3">
          <div class="col-md-6">
            <div class="row mb-2">
              <div class="col-4"><strong>ID:</strong></div>
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

        <div class="table-responsive">
          <table class="table table-bordered" id="testResultsTable">
            <thead class="table-light">
              <tr>
                <th style="width: 60px;" class="text-center fw-bold">STT</th>
                <th class="fw-bold">Xét nghiệm</th>
                <th class="fw-bold">Giá trị tham chiếu</th>
                <th class="fw-bold">Kết quả</th>
                <th style="width: 80px;" class="fw-bold">Đơn vị</th>
                <th class="fw-bold">Máy/QTKT</th>
                <th style="width: 60px;" class="text-center fw-bold">Xóa</th>
              </tr>
            </thead>
            <tbody id="testResultsBody">
              <!-- Dynamic rows will be added here -->
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end mb-3">
          <button type="button" class="btn btn-outline-primary btn-sm" id="addTestRow">
            <i class="fas fa-plus me-1"></i>Thêm dòng
          </button>
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

<script>
// Load dashboard data
document.addEventListener("DOMContentLoaded", function() {
    loadLabDashboardData();
    
    // Set default date to today
    const today = new Date().toISOString().split("T")[0];
    document.getElementById("xetnghiem_date").value = today;
    
    // Load xetnghiem requests for today on page load
    loadXetnghiemRequests();
    
    // Add event listener for filter button
    document.getElementById("xetnghiem_filter_btn").addEventListener("click", loadXetnghiemRequests);
});

function loadLabDashboardData(selectedDate = null) {
    // Use selected date or current date
    const date = selectedDate || new Date().toISOString().split("T")[0];
    
    // Load stats with date parameter
    fetch(`./?action=get_lab_dashboard_stats&date=${date}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById("stat_total_today").textContent = data.stats.today || 0;
                document.getElementById("stat_completed").textContent = data.stats.completed || 0;
                document.getElementById("stat_pending").textContent = data.stats.pending || 0;
            }
        })
        .catch(error => console.error("Error loading stats:", error));
}

// Load xetnghiem requests (Đã yêu cầu)
function loadXetnghiemRequests() {
    const today = new Date().toISOString().split("T")[0];
    const date = document.getElementById("xetnghiem_date").value || today;
    const name = document.getElementById("xetnghiem_name").value;
    
    fetch(`./?action=get_xetnghiem_requests&date=${date}&name=${encodeURIComponent(name)}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector("#xetnghiemRequestedTable tbody");
            
            if (data.success && data.requests && data.requests.length > 0) {
                tbody.innerHTML = data.requests.map(req => `
                    <tr>
                        <td class="text-center">${req.id}</td>
                        <td class="text-center">${req.ma_benh_nhan || ""}</td>
                        <td>${req.ho_ten || ""}</td>
                        <td class="text-center">${req.tuoi || ""}</td>
                        <td class="text-center">${req.gioi_tinh || ""}</td>
                        <td>${req.yeu_cau || ""}</td>
                        <td>${formatDateTime(req.ngay_cap_nhat || req.ngay_tao)}</td>
                        <td class="text-center">
                            <span class="badge ${getXetnghiemStatusBadge(req.trang_thai)}">
                                ${req.trang_thai || "Đã yêu cầu"}
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-info btn-sm" onclick="viewXetnghiemDetail(${req.id})">
                                <i class="fas fa-eye me-1"></i>Xem
                            </button>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-success btn-sm" onclick="returnXetnghiemResult(${req.id})">
                                <i class="fas fa-reply me-1"></i>Trả kết quả
                            </button>
                        </td>
                    </tr>
                `).join("");
            } else {
                tbody.innerHTML = "<tr><td colspan=\"10\" class=\"text-center text-muted\">Không có dữ liệu</td></tr>";
            }
        })
        .catch(error => {
            console.error("Error loading xetnghiem requests:", error);
            document.querySelector("#xetnghiemRequestedTable tbody").innerHTML = 
                "<tr><td colspan=\"10\" class=\"text-center text-danger\">Lỗi tải dữ liệu</td></tr>";
        })
        .finally(() => {
            // Update dashboard stats with the selected date
            loadLabDashboardData(date);
        });
}

function getXetnghiemStatusBadge(status) {
    switch(status) {
        case "Đã yêu cầu": return "bg-warning";
        case "Hoàn thành": return "bg-success";
        case "Đã thanh toán": return "bg-info";
        default: return "bg-secondary";
    }
}

function viewXetnghiemDetail(id) {
    fetch(`./?action=get_xetnghiem_detail&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.result) {
                const result = data.result;
                
                // Fill modal fields similar to sieuam
                document.getElementById("xn_clinic_name").value = "PHÒNG KHÁM ĐA KHOA THINHVIET";
                document.getElementById("xn_phone").value = "0777871608";
                document.getElementById("xn_quan").value = "Gò Vấp";
                document.getElementById("xn_patient_name").value = result.ho_ten || "";
                document.getElementById("xn_patient_age").value = result.tuoi || "";
                document.getElementById("xn_patient_gender").value = result.gioi_tinh || "";
                document.getElementById("xn_address").value = result.dia_chi || "Gò Vấp";
                document.getElementById("xn_patient_type").value = result.doi_tuong || "";
                document.getElementById("xn_insurance_number").value = result.so_the_bhyt || "";
                document.getElementById("xn_diagnosis").value = result.chan_doan || "";
                document.getElementById("xn_request").value = result.yeu_cau || "";
                
                // Set date
                const date = new Date(result.ngay_cap_nhat || result.ngay_tao || Date.now());
                document.getElementById("xn_day").value = date.getDate().toString().padStart(2, "0");
                document.getElementById("xn_month").value = (date.getMonth() + 1).toString().padStart(2, "0");
                document.getElementById("xn_year").value = date.getFullYear();
                document.getElementById("xn_doctor").value = result.bac_si_kham || "";
                
                new bootstrap.Modal(document.getElementById("xetnghiemDetailModal")).show();
            } else {
                alert("Không tìm thấy thông tin xét nghiệm");
            }
        })
        .catch(error => {
            console.error("Error loading xetnghiem detail:", error);
            alert("Lỗi tải dữ liệu");
        });
}

function returnXetnghiemResult(id) {
    // Load patient data and show return result modal
    fetch(`./?action=get_xetnghiem_result&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.result) {
                const result = data.result;
                
                // Fill patient information
                document.getElementById("result_patient_id").value = result.ma_benh_nhan || "";
                document.getElementById("result_patient_name").value = result.ho_ten || "";
                document.getElementById("result_patient_address").value = result.dia_chi || "";
                document.getElementById("result_diagnosis").value = result.chan_doan || "";
                document.getElementById("result_patient_age").value = result.tuoi || "";
                document.getElementById("result_patient_gender").value = result.gioi_tinh || "";
                document.getElementById("result_requesting_doctor").value = result.bac_si_yeu_cau || result.bac_si_kham || "";
                
                // Set registration date and time
                const date = new Date(result.ngay_cap_nhat || result.ngay_tao || Date.now());
                document.getElementById("result_reg_date").textContent = date.toLocaleDateString("vi-VN");
                document.getElementById("result_reg_time").textContent = date.toLocaleTimeString("vi-VN", {hour: "2-digit", minute: "2-digit"});
                
                // Set current date for result
                const today = new Date();
                document.getElementById("result_day").value = today.getDate().toString().padStart(2, "0");
                document.getElementById("result_month").value = (today.getMonth() + 1).toString().padStart(2, "0");
                document.getElementById("result_year").value = today.getFullYear();
                
                // Fill sample status if saved
                document.getElementById("result_sample_status").value = result.tinh_trang_mau || "";
                
                // Set test section title from yeu_cau (convert to uppercase)
                const yeuCauText = result.yeu_cau || "XÉT NGHIỆM MÁU - NƯỚC TIỂU - PHÂN";
                document.getElementById("test_section_title").textContent = yeuCauText.toUpperCase();
                
                // Clear previous test results
                document.getElementById("testResultsBody").innerHTML = "";
                
                // Load saved test results if available
                if (data.is_saved && data.test_details && data.test_details.length > 0) {
                    data.test_details.forEach((test, index) => {
                        addTestRow();
                        const row = document.querySelector("#testResultsBody tr:last-child");
                        const inputs = row.querySelectorAll("input");
                        inputs[0].value = test.ten_xet_nghiem || "";
                        inputs[1].value = test.gia_tri_tham_chieu || "";
                        inputs[2].value = test.ket_qua || "";
                        inputs[3].value = test.don_vi || "";
                        inputs[4].value = test.may_qtkt || "";
                    });
                } else {
                    addTestRow(); // Add initial row
                }
                
                // Store the request ID for saving
                document.getElementById("returnXetnghiemResultModal").setAttribute("data-request-id", id);
                
                new bootstrap.Modal(document.getElementById("returnXetnghiemResultModal")).show();
            } else {
                alert("Không tìm thấy thông tin yêu cầu xét nghiệm");
            }
        })
        .catch(error => {
            console.error("Error loading xetnghiem request:", error);
            alert("Lỗi tải dữ liệu");
        });
}

function formatDateTime(dateString) {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleDateString("vi-VN") + " " + date.toLocaleTimeString("vi-VN", {hour: "2-digit", minute: "2-digit"});
}

// Test Results Management
let testRowCounter = 0;

function addTestRow() {
    const tbody = document.getElementById("testResultsBody");
    const currentRows = tbody.querySelectorAll("tr").length;
    testRowCounter = currentRows + 1;
    
    const row = document.createElement("tr");
    row.innerHTML = `
        <td class="text-center">
            <span>${testRowCounter}</span>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm test-name-input" placeholder="Nhập chỉ số xét nghiệm..." autocomplete="off" />
            <div class="suggestion-dropdown" style="display: none; position: absolute; z-index: 1000; background: white; border: 1px solid #ccc; max-height: 200px; overflow-y: auto; width: 100%;"></div>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm" />
        </td>
        <td>
            <input type="text" class="form-control form-control-sm" />
        </td>
        <td>
            <input type="text" class="form-control form-control-sm" />
        </td>
        <td>
            <input type="text" class="form-control form-control-sm" />
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeTestRow(this)">
                <i class="fas fa-minus"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
    
    // Add event listeners for suggestion
    const testNameInput = row.querySelector(".test-name-input");
    const suggestionDropdown = row.querySelector(".suggestion-dropdown");
    
    // Handle input events
    testNameInput.addEventListener("input", function() {
        const keyword = this.value.trim();
        if (keyword.length >= 1) {
            loadTestSuggestions(keyword, suggestionDropdown, row);
        } else {
            suggestionDropdown.style.display = "none";
        }
    });
    
    // Handle focus events
    testNameInput.addEventListener("focus", function() {
        const keyword = this.value.trim();
        if (keyword.length >= 1) {
            loadTestSuggestions(keyword, suggestionDropdown, row);
        }
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener("click", function(e) {
        if (!row.contains(e.target)) {
            suggestionDropdown.style.display = "none";
        }
    });
}

function loadTestSuggestions(keyword, dropdown, row) {
    fetch(`./?action=get_test_suggestions&keyword=${encodeURIComponent(keyword)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.suggestions && data.suggestions.length > 0) {
                dropdown.innerHTML = data.suggestions.map(suggestion => `
                    <div class="suggestion-item" style="padding: 8px; cursor: pointer; border-bottom: 1px solid #eee;" 
                         data-xet-nghiem="${suggestion.xet_nghiem}" 
                         data-gia-tri="${suggestion.gia_tri_tham_chieu || ""}" 
                         data-don-vi="${suggestion.don_vi || ""}" 
                         data-may="${suggestion.may_qtkt || ""}">
                        <strong>${suggestion.xet_nghiem}</strong>
                        ${suggestion.gia_tri_tham_chieu ? `<br><small class="text-muted">${suggestion.gia_tri_tham_chieu}</small>` : ""}
                    </div>
                `).join("");
                
                dropdown.style.display = "block";
                
                // Add click handlers
                dropdown.querySelectorAll(".suggestion-item").forEach(item => {
                    item.addEventListener("click", function() {
                        const xetNghiem = this.getAttribute("data-xet-nghiem");
                        const giaTri = this.getAttribute("data-gia-tri");
                        const donVi = this.getAttribute("data-don-vi");
                        const may = this.getAttribute("data-may");
                        
                        // Fill the form
                        const inputs = row.querySelectorAll("input");
                        inputs[0].value = xetNghiem;
                        inputs[1].value = giaTri;
                        inputs[3].value = donVi;
                        inputs[4].value = may;
                        
                        // Hide dropdown
                        dropdown.style.display = "none";
                    });
                });
            } else {
                dropdown.style.display = "none";
            }
        })
        .catch(error => {
            console.error("Error loading test suggestions:", error);
            dropdown.style.display = "none";
        });
}

function removeTestRow(button) {
    const row = button.closest("tr");
    const tbody = document.getElementById("testResultsBody");
    
    // Remove the row
    tbody.removeChild(row);
    
    // Update STT for remaining rows
    const remainingRows = tbody.querySelectorAll("tr");
    remainingRows.forEach((row, index) => {
        const sttCell = row.querySelector("td:first-child span");
        if (sttCell) {
            sttCell.textContent = index + 1;
        }
    });
    
    // Update counter
    testRowCounter = remainingRows.length;
}

function saveTestResult() {
    const requestId = document.getElementById("returnXetnghiemResultModal").getAttribute("data-request-id");
    if (!requestId) {
        alert("Không tìm thấy ID yêu cầu xét nghiệm");
        return;
    }

    // Collect test results
    const testResults = [];
    const rows = document.querySelectorAll("#testResultsBody tr");
    
    rows.forEach((row, index) => {
        const inputs = row.querySelectorAll("input");
        if (inputs.length >= 5) {
            const testName = inputs[0].value.trim();
            const referenceValue = inputs[1].value.trim();
            const result = inputs[2].value.trim();
            const unit = inputs[3].value.trim();
            const machine = inputs[4].value.trim();
            
            if (testName || result) {
                testResults.push({
                    stt: index + 1,
                    test_name: testName,
                    reference_value: referenceValue,
                    result: result,
                    unit: unit,
                    machine: machine
                });
            }
        }
    });

    console.log("Test results collected:", testResults);
    
    if (testResults.length === 0) {
        alert("Vui lòng nhập ít nhất một kết quả xét nghiệm");
        return;
    }

    // Prepare form data
    const formData = new URLSearchParams();
    formData.append("request_id", requestId);
    formData.append("test_results", JSON.stringify(testResults));
    formData.append("examining_doctor", document.getElementById("result_examining_doctor").value);
    formData.append("sample_status", document.getElementById("result_sample_status").value);
    formData.append("result_date", `${document.getElementById("result_year").value}-${document.getElementById("result_month").value}-${document.getElementById("result_day").value}`);

    // Save test result
    fetch("./?action=save_xetnghiem_result", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Lưu kết quả xét nghiệm thành công!");
            loadXetnghiemRequests(); // Refresh the list
        } else {
            alert("Lỗi: " + (data.message || "Không thể lưu kết quả"));
        }
    })
    .catch(error => {
        console.error("Error saving test result:", error);
        alert("Lỗi hệ thống khi lưu kết quả");
    });
}

function printTestResult() {
    const requestId = document.getElementById("returnXetnghiemResultModal").getAttribute("data-request-id");
    if (!requestId) {
        alert("Không tìm thấy ID yêu cầu xét nghiệm");
        return;
    }
    
    // Open print window
    window.open("./?action=print_xetnghiem_result&id=" + requestId, "_blank");
}

function completeTestResult() {
    const requestId = document.getElementById("returnXetnghiemResultModal").getAttribute("data-request-id");
    if (!requestId) {
        alert("Không tìm thấy ID yêu cầu xét nghiệm");
        return;
    }
    
    if (!confirm("Bạn có chắc chắn muốn hoàn thành yêu cầu xét nghiệm này?")) {
        return;
    }
    
    const formData = new URLSearchParams();
    formData.append("request_id", requestId);
    
    fetch("./?action=complete_xetnghiem_request", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Hoàn thành yêu cầu xét nghiệm thành công!");
            loadXetnghiemRequests(); // Refresh the list
            bootstrap.Modal.getInstance(document.getElementById("returnXetnghiemResultModal")).hide(); // Close modal
        } else {
            alert("Lỗi: " + (data.message || "Không thể hoàn thành yêu cầu"));
        }
    })
    .catch(error => {
        console.error("Error completing test request:", error);
        alert("Lỗi hệ thống khi hoàn thành yêu cầu");
    });
}

// Event listeners for test result modal
document.addEventListener("DOMContentLoaded", function() {
    // Add test row button
    document.getElementById("addTestRow").addEventListener("click", addTestRow);
    
    // Save test result button
    document.getElementById("saveTestResult").addEventListener("click", saveTestResult);
    
    // Print test result button
    document.getElementById("printTestResult").addEventListener("click", printTestResult);
    
    // Complete test result button
    document.getElementById("completeTestResult").addEventListener("click", completeTestResult);
});
</script>';

// Render page using layout helper
renderLayout($content, 'Xét nghiệm Dashboard - Hệ thống Quản lý Bệnh viện');
?>