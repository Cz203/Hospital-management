<?php
require_once 'Views/layouts/layout_helper.php';
$content = '<div class="container-fluid">
  <!-- Hidden element to store doctor name -->
  <div id="xetnghiem_doctor_name" data-name="Bác sĩ xét nghiệm" style="display: none;"></div>
  
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="fas fa-history text-primary me-2"></i>Lịch sử xét nghiệm</h3>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-2 align-items-end">
        <div class="col-auto">
          <label class="form-label mb-1">Ngày</label>
          <input type="date" class="form-control" id="hs_date">
        </div>
        <div class="col-auto">
          <label class="form-label mb-1">Số phiếu chỉ định</label>
          <input type="text" class="form-control" id="hs_request_id" placeholder="Nhập số phiếu chỉ định...">
        </div>
        <div class="col-auto">
          <label class="form-label mb-1">Mã Bệnh Nhân</label>
          <input type="text" class="form-control" id="hs_patient_code" placeholder="Nhập mã bệnh nhân...">
        </div>
        <div class="col-auto">
          <button type="button" class="btn btn-outline-primary" id="hs_filter_btn"><i class="fas fa-filter me-1"></i>Lọc</button>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-bordered align-middle" id="hs_table">
        <thead class="table-light">
          <tr>
            <th style="width:80px" class="text-center">ID</th>
            <th style="width:120px" class="text-center">Mã Bệnh Nhân</th>
            <th>Họ tên</th>
            <th style="width:90px" class="text-center">Giới tính</th>
            <th style="width:80px" class="text-center">Tuổi</th>
            <th>Ngày tạo</th>
            <th>Trạng thái</th>
            <th>Xét nghiệm</th>
            <th style="width:180px" class="text-center">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr><td colspan="9" class="text-center text-muted">Đang tải...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
// Load history data
function loadHistory() {
  const date = document.getElementById("hs_date").value;
  const requestId = document.getElementById("hs_request_id").value;
  const patientCode = document.getElementById("hs_patient_code").value;
  
  const params = new URLSearchParams();
  if (date) params.append("date", date);
  if (requestId) params.append("request_id", requestId);
  if (patientCode) params.append("patient_code", patientCode);
  
  fetch("./?action=get_xetnghiem_history&" + params.toString())
    .then(response => response.json())
    .then(data => {
      const tbody = document.querySelector("#hs_table tbody");
      if (data.success && data.history && data.history.length > 0) {
        tbody.innerHTML = data.history.map(item => `
          <tr>
            <td class="text-center">${item.id}</td>
            <td class="text-center">${item.ma_benh_nhan || ""}</td>
            <td>${item.ho_ten || ""}</td>
            <td class="text-center">${item.gioi_tinh || ""}</td>
            <td class="text-center">${item.tuoi || ""}</td>
            <td>${formatDateTime(item.ngay_tao)}</td>
            <td class="text-center">
              <span class="badge ${getStatusBadge(item.trang_thai)}">${item.trang_thai || "Đã yêu cầu"}</span>
            </td>
            <td>${item.ket_qua || "Chưa có kết quả"}</td>
            <td class="text-center">
              <button class="btn btn-info btn-sm" onclick="viewHistoryDetail(${item.id})">
                <i class="fas fa-eye"></i> Xem
              </button>
            </td>
          </tr>
        `).join("");
      } else {
        tbody.innerHTML = "<tr><td colspan=\"9\" class=\"text-center text-muted\">Không có dữ liệu</td></tr>";
      }
    })
    .catch(error => {
      console.error("Error loading history:", error);
      document.querySelector("#hs_table tbody").innerHTML = 
        "<tr><td colspan=\"9\" class=\"text-center text-danger\">Lỗi tải dữ liệu</td></tr>";
    });
}

// Get status badge class
function getStatusBadge(status) {
  switch(status) {
    case "Đã yêu cầu": return "bg-warning";
    case "Hoàn thành": return "bg-success";
    case "Đang xử lý": return "bg-info";
    default: return "bg-secondary";
  }
}

// Format date time
function formatDateTime(dateTime) {
  if (!dateTime) return "";
  const date = new Date(dateTime);
  return date.toLocaleDateString("vi-VN") + " " + date.toLocaleTimeString("vi-VN", {hour: "2-digit", minute: "2-digit"});
}

// View history detail
function viewHistoryDetail(id) {
  fetch("./?action=get_xetnghiem_history_detail&id=" + id)
    .then(response => response.json())
    .then(data => {
      if (data.success && data.detail) {
        const detail = data.detail;
        const testDetails = data.testDetails || [];
        
        // Create modal content similar to print form
        const modalContent = `
          <div class="modal fade" id="viewDetailModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
              <div class="modal-content">
                <div class="modal-header bg-info text-white">
                  <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Chi tiết kết quả xét nghiệm</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="font-family: \'Times New Roman\', serif; font-size: 12px;">
                  <!-- Header -->
                  <div class="text-center mb-4">
                    <div class="fw-bold" style="font-size: 18px; color: #000;">PHÒNG KHÁM ĐA KHOA THINHVIET</div>
                    <div class="fw-bold" style="font-size: 14px;">KHOA XÉT NGHIỆM</div>
                    <div class="fw-bold" style="font-size: 16px; color: #dc3545;">KẾT QUẢ XÉT NGHIỆM</div>
                    <div class="d-flex justify-content-center mt-2" style="gap: 10px;">
                      <span>Ngày ĐK: <span>${formatDate(detail.ngay_tao)}</span></span>
                      <span>|</span>
                      <span>${formatTime(detail.ngay_tao)}</span>
                    </div>
                  </div>
                  <hr>

                  <!-- Patient Information -->
                  <div class="row mb-4">
                    <div class="col-md-6">
                      <div class="row mb-3">
                        <div class="col-4"><strong style="font-size: 14px;">ID:</strong></div>
                        <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;">${detail.ma_benh_nhan || ""}</span></div>
                      </div>
                      <div class="row mb-3">
                        <div class="col-4"><strong style="font-size: 14px;">Họ và tên:</strong></div>
                        <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;">${detail.ho_ten || ""}</span></div>
                      </div>
                      <div class="row mb-3">
                        <div class="col-4"><strong style="font-size: 14px;">Địa chỉ:</strong></div>
                        <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;">${detail.dia_chi || ""}</span></div>
                      </div>
                      <div class="row mb-3">
                        <div class="col-4"><strong style="font-size: 14px;">Chẩn đoán sơ bộ:</strong></div>
                        <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;">${detail.chan_doan || ""}</span></div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="row mb-3">
                        <div class="col-4"><strong style="font-size: 14px;">Tuổi:</strong></div>
                        <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;">${detail.tuoi || ""}</span></div>
                      </div>
                      <div class="row mb-3">
                        <div class="col-4"><strong style="font-size: 14px;">Giới tính:</strong></div>
                        <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;">${detail.gioi_tinh || ""}</span></div>
                      </div>
                      <div class="row mb-3">
                        <div class="col-4"><strong style="font-size: 14px;">BS yêu cầu:</strong></div>
                        <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;">${detail.bac_si_yeu_cau || ""}</span></div>
                      </div>
                      <div class="row mb-3">
                        <div class="col-4"><strong style="font-size: 14px;">Tình trạng mẫu:</strong></div>
                        <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;">${detail.tinh_trang_mau || ""}</span></div>
                      </div>
                    </div>
                  </div>

                  <!-- Test Results Section -->
                  <div class="text-center mb-3">
                    <div class="fw-bold" style="font-size: 16px;">${detail.yeu_cau ? detail.yeu_cau.toUpperCase() : "CHƯA CÓ YÊU CẦU XÉT NGHIỆM"}</div>
                    <div class="fw-bold" style="font-size: 14px;">BẢNG KẾT QUẢ XÉT NGHIỆM</div>
                  </div>

                  <div class="table-responsive">
                    <table class="table table-bordered" style="font-size: 12px;">
                      <thead>
                        <tr>
                          <th class="text-center" style="width: 60px;">STT</th>
                          <th>Xét nghiệm</th>
                          <th class="text-center">Giá trị tham chiếu</th>
                          <th class="text-center" style="font-weight: bold;">Kết quả</th>
                          <th class="text-center">Đơn vị</th>
                          <th>Máy/QTKT</th>
                        </tr>
                      </thead>
                      <tbody>
                        ${testDetails.length > 0 ? testDetails.map((test, index) => `
                          <tr>
                            <td class="text-center">${test.stt || index + 1}</td>
                            <td>${test.ten_xet_nghiem || ""}</td>
                            <td class="text-center">${test.gia_tri_tham_chieu || ""}</td>
                            <td class="text-center" style="font-weight: bold;">${test.ket_qua || ""}</td>
                            <td class="text-center">${test.don_vi || ""}</td>
                            <td>${test.may_qtkt || ""}</td>
                          </tr>
                        `).join("") : `
                          <tr>
                            <td colspan="6" class="text-center text-muted">Chưa có kết quả xét nghiệm</td>
                          </tr>
                        `}
                      </tbody>
                    </table>
                  </div>

                  <!-- Notes -->
                  <div class="mt-3" style="font-size: 11px;">
                    <div>Ghi chú: Kết quả in đậm là kết quả nằm ngoài khoảng tham chiếu.</div>
                    <div>Xét nghiệm đánh dấu (*) là xét nghiệm được thực hiện bởi PXN chuyển gửi.</div>
                  </div>

                  <!-- Footer -->
                  <div class="mt-4">
                    <div class="row">
                      <div class="col-6">
                        <!-- Trống -->
                      </div>
                      <div class="col-6">
                        <div class="text-center" style="font-size: 16px; font-weight: bold; margin-bottom: 20px;">
                          Ngày ${new Date().getDate()} tháng ${new Date().getMonth() + 1} năm ${new Date().getFullYear()}
                        </div>
                        <div class="text-center">
                          <div class="fw-bold" style="font-size: 14px;">BÁC SĨ XÉT NGHIỆM</div>
                          <div class="mt-2" style="font-size: 14px;">${detail.bac_si_xet_nghiem || ""}</div>
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
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById("viewDetailModal");
        if (existingModal) {
          existingModal.remove();
        }
        
        // Add modal to body
        document.body.insertAdjacentHTML("beforeend", modalContent);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById("viewDetailModal"));
        modal.show();
        
      } else {
        alert("Không tìm thấy chi tiết");
      }
    })
    .catch(error => {
      console.error("Error loading detail:", error);
      alert("Lỗi tải chi tiết");
    });
}

// Helper functions for date formatting
function formatDate(dateString) {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toLocaleDateString("vi-VN");
}

function formatTime(dateString) {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toLocaleTimeString("vi-VN", {hour: "2-digit", minute: "2-digit"});
}


// Event listeners
document.addEventListener("DOMContentLoaded", function() {
  // Set default date to today
  document.getElementById("hs_date").value = new Date().toISOString().split("T")[0];
  
  // Load initial data
  loadHistory();
  
  // Filter button
  document.getElementById("hs_filter_btn").addEventListener("click", loadHistory);
  
  // Enter key in inputs
  document.getElementById("hs_date").addEventListener("keypress", function(e) {
    if (e.key === "Enter") loadHistory();
  });
  document.getElementById("hs_request_id").addEventListener("keypress", function(e) {
    if (e.key === "Enter") loadHistory();
  });
  document.getElementById("hs_patient_code").addEventListener("keypress", function(e) {
    if (e.key === "Enter") loadHistory();
  });
});
</script>';

// Render page using layout helper
renderLayout($content, 'Lịch sử xét nghiệm - Hệ thống Quản lý Bệnh viện');
?>
