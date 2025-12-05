<?php
require_once 'Views/layouts/layout_helper.php';
$content = '<div class="container-fluid">
  <!-- Hidden element to store doctor name -->
  <div id="sieuam_doctor_name" data-name="Bác sĩ siêu âm" style="display: none;"></div>
  
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="fas fa-history text-primary me-2"></i>Lịch sử siêu âm</h3>
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
            <th>Kết luận</th>
            <th style="width:180px" class="text-center">Hình ảnh</th>
          </tr>
        </thead>
        <tbody>
          <tr><td colspan="9" class="text-center text-muted">Đang tải...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal xem chi tiết kết quả siêu âm -->
<div class="modal fade" id="sieuamResultViewModal" tabindex="-1" aria-labelledby="sieuamResultViewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Chi tiết kết quả siêu âm</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <style>
          .report { font-family: "Times New Roman", serif; padding: 18px; }
          .title { font-weight: bold; text-transform: uppercase; text-align: center; letter-spacing: .5px; font-size: 18px; margin-bottom: 6px; color: red; }
          .hr { border-top: 2px solid #000; margin: 10px 0; }
          .row-line { display: flex; gap: 8px; margin-bottom: 6px; font-size: 15px; }
          .label { min-width: 150px; font-weight: bold; }
          .dots { flex: 0 0 auto; }
          .value { flex: 1; border-bottom: 1px dotted #333; min-height: 20px; }
          .section { margin-top: 10px; margin-bottom: 6px; font-weight: bold; text-transform: uppercase; color: blue; }
          .signature { min-width: 260px; }
          .conclusion { font-weight: bold; color: blue; }
          .result-content { border: 1px solid #333; padding: 10px; min-height: 100px; white-space: pre-wrap; }
          .conclusion-content { border: 1px solid #333; padding: 10px; min-height: 60px; white-space: pre-wrap; }
          .image-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 10px; }
          .image-item { border: 1px solid #ccc; padding: 5px; text-align: center; }
          .image-item img { max-width: 100%; height: 150px; object-fit: cover; cursor: pointer; }
          .image-caption { font-size: 12px; margin-top: 5px; color: #666; }
        </style>
        
        <div class="report">
          <div class="text-center mb-3">
            <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:60px;object-fit:contain;margin-bottom:10px;">
            <div class="fw-bold" style="font-size: 18px; color: #333;">PHÒNG KHÁM ĐA KHOA THINHVIET</div>
            <div class="fw-bold" style="font-size: 16px; color: #666;">KHOA CHẨN ĐOÁN HÌNH ẢNH</div>
          </div>
          <div class="title">KẾT QUẢ SIÊU ÂM</div>
          
          <div class="text-center mb-2">
            <div class="fw-bold">Máy: Medison Sonoace X6</div>
          </div>
          
          <div class="row-line">
            <div class="label">ID:</div>
            <div class="dots">:</div>
            <div class="value" id="sv_id">*8307791*</div>
            <div class="label" style="margin-left: 20px;">Ngày ĐK:</div>
            <div class="dots">:</div>
            <div class="value" id="sv_date">03/07/2025</div>
            <div class="dots">-</div>
            <div class="value" id="sv_time">08:26</div>
          </div>
          
          <div class="hr"></div>
          
          <div class="row-line">
            <div class="label">Họ và tên:</div>
            <div class="dots">:</div>
            <div class="value" id="sv_name">-</div>
            <div class="label" style="margin-left: 50px;">Địa chỉ:</div>
            <div class="dots">:</div>
            <div class="value" id="sv_address">-</div>
          </div>
          
          <div class="row-line">
            <div class="label">Chẩn đoán sơ bộ:</div>
            <div class="dots">:</div>
            <div class="value" id="sv_chan_doan">-</div>
            <div class="label" style="margin-left: 50px;">BS chỉ định:</div>
            <div class="dots">:</div>
            <div class="value" id="sv_bs_chi_dinh">-</div>
          </div>
          
          <div class="row-line">
            <div class="label">Tuổi:</div>
            <div class="dots">:</div>
            <div class="value" id="sv_age">-</div>
            <div class="label" style="margin-left: 50px;">Giới tính:</div>
            <div class="dots">:</div>
            <div class="value" id="sv_gender">-</div>
          </div>
          
          <div class="row-line">
            <div class="label">Phiếu chỉ định:</div>
            <div class="dots">:</div>
            <div class="value" id="sv_phieu_chi_dinh">-</div>
          </div>
          
          <div class="row-line">
            <div class="label">Giờ nhận kết quả:</div>
            <div class="dots">:</div>
            <div class="value" id="sv_return_time">-</div>
          </div>
          
          <div class="hr"></div>
          
          <div class="section">VÙNG KHẢO SÁT : <span id="sv_vung_khao_sat">SIÊU ÂM BỤNG TỔNG QUÁT MÀU</span></div>
          
          <div class="section">KẾT QUẢ KHẢO SÁT:</div>
          <div class="result-content" id="sv_ket_qua_khao_sat">-</div>
          
          <div class="section">KẾT LUẬN:</div>
          <div class="conclusion-content" id="sv_ket_luan">-</div>
          
          <div class="section">HÌNH ẢNH SIÊU ÂM:</div>
          <div id="sv_image_gallery" class="image-grid">
            <!-- Hình ảnh sẽ được hiển thị ở đây -->
          </div>
          
          <div style="margin-top: 30px; text-align: right;">
            <div class="mb-3">
              <span>Ngày</span>
              <input type="text" class="form-control d-inline-block" id="sv_signature_date" style="width:60px; margin: 0 5px;" readonly>
              <span>tháng</span>
              <input type="text" class="form-control d-inline-block" id="sv_signature_month" style="width:60px; margin: 0 5px;" readonly>
              <span>năm</span>
              <input type="text" class="form-control d-inline-block" id="sv_signature_year" style="width:80px; margin: 0 5px;" readonly>
            </div>
            <div style="margin-right: 20px;">
              <div class="fw-bold">BÁC SĨ SIÊU ÂM</div>
              <div class="mt-2" id="sv_signature_doctor" style="margin-left: -20px;"></div>
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

<script>
let currentSieuamResultId = null;
function getSieuamDoctorName() {
    // This will be set by the fillSieuamResultModal function
    return window.currentSieuamDoctorName || "Dr. Ultrasound";
}

// Load history data
function loadSieuamHistory() {
    const date = document.getElementById("hs_date").value;
    const requestId = document.getElementById("hs_request_id").value;
    const patientCode = document.getElementById("hs_patient_code").value;
    
    let url = "./?action=get_sieuam_history";
    const params = new URLSearchParams();
    if (date) params.append("date", date);
    if (requestId) params.append("request_id", requestId);
    if (patientCode) params.append("patient_code", patientCode);
    
    if (params.toString()) {
        url += "&" + params.toString();
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            console.log("Sieuam history data:", data);
            if (data.success) {
                renderSieuamHistoryTable(data.history);
            } else {
                console.error("Error loading sieuam history:", data.message);
                document.getElementById("hs_table").getElementsByTagName("tbody")[0].innerHTML = 
                    "<tr><td colspan=\"9\" class=\"text-center text-danger\">Lỗi: " + data.message + "</td></tr>";
            }
        })
        .catch(error => {
            console.error("Error:", error);
            document.getElementById("hs_table").getElementsByTagName("tbody")[0].innerHTML = 
                "<tr><td colspan=\"9\" class=\"text-center text-danger\">Lỗi kết nối</td></tr>";
        });
}

// Render history table
function renderSieuamHistoryTable(history) {
    const tbody = document.getElementById("hs_table").getElementsByTagName("tbody")[0];
    
    if (!history || history.length === 0) {
        tbody.innerHTML = "<tr><td colspan=\"9\" class=\"text-center text-muted\">Không có dữ liệu</td></tr>";
        return;
    }
    
    tbody.innerHTML = "";
    history.forEach(item => {
        const row = tbody.insertRow();
        row.innerHTML = 
            "<td class=\"text-center\">" + item.id + "</td>" +
            "<td class=\"text-center\">" + (item.ma_benh_nhan || "-") + "</td>" +
            "<td>" + item.ho_ten + "</td>" +
            "<td class=\"text-center\">" + item.gioi_tinh + "</td>" +
            "<td class=\"text-center\">" + item.tuoi + "</td>" +
            "<td>" + formatDateTime(item.ngay_tao) + "</td>" +
            "<td><span class=\"badge bg-success\">Hoàn thành</span></td>" +
            "<td>" + (item.ket_luan ? (item.ket_luan.length > 50 ? item.ket_luan.substring(0, 50) + "..." : item.ket_luan) : "-") + "</td>" +
            "<td class=\"text-center\">" +
                "<button class=\"btn btn-sm btn-outline-primary me-1\" onclick=\"viewSieuamResult(" + item.id + ")\">Xem phiếu</button>" +
                "<button class=\"btn btn-sm btn-outline-secondary\" onclick=\"viewSieuamImages(" + item.id + ")\">Xem ảnh</button>" +
            "</td>";
    });
}

// View sieuam result details
function viewSieuamResult(resultId) {
    currentSieuamResultId = resultId;
    
    fetch(`./?action=get_sieuam_result_view&result_id=${resultId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fillSieuamResultModal(data.result);
                new bootstrap.Modal(document.getElementById("sieuamResultViewModal")).show();
            } else {
                alert("Lỗi: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Lỗi kết nối");
        });
}

// Fill sieuam result modal
function fillSieuamResultModal(result) {
    // Parse date and time
    const date = new Date(result.ngay_tao);
    const dateStr = date.toLocaleDateString("vi-VN");
    const timeStr = date.toLocaleTimeString("vi-VN", {hour: "2-digit", minute: "2-digit"});
    
    // Basic info
    document.getElementById("sv_id").textContent = "*" + (result.ma_benh_nhan || "0000000") + "*";
    document.getElementById("sv_date").textContent = dateStr;
    document.getElementById("sv_time").textContent = timeStr;
    
    // Patient info
    document.getElementById("sv_name").textContent = result.ho_ten || "-";
    document.getElementById("sv_address").textContent = result.dia_chi || "-";
    document.getElementById("sv_chan_doan").textContent = result.chan_doan || "-";
    document.getElementById("sv_bs_chi_dinh").textContent = result.ten_bac_si || "-";
    document.getElementById("sv_age").textContent = result.tuoi || "-";
    document.getElementById("sv_gender").textContent = result.gioi_tinh || "-";
    document.getElementById("sv_phieu_chi_dinh").textContent = result.phieu_id || "-";
    
    // Request type
    document.getElementById("sv_vung_khao_sat").textContent = result.yeu_cau_sieu_am || "SIÊU ÂM BỤNG TỔNG QUÁT MÀU";
    
    // Results
    document.getElementById("sv_ket_qua_khao_sat").textContent = result.ket_qua_khao_sat || "-";
    document.getElementById("sv_ket_luan").textContent = result.ket_luan || "-";
    
    // Signature
    document.getElementById("sv_signature_date").value = date.getDate();
    document.getElementById("sv_signature_month").value = date.getMonth() + 1;
    document.getElementById("sv_signature_year").value = date.getFullYear();
    
    // Set doctor name from database
    window.currentSieuamDoctorName = result.bac_si_sieu_am || "Dr. Ultrasound";
    document.getElementById("sv_signature_doctor").textContent = window.currentSieuamDoctorName;
    
    // Hiển thị Giờ nhận kết quả từ ngay_cap_nhat (format: H:i:s)
    var returnTimeEl = document.getElementById("sv_return_time");
    if (returnTimeEl && result.ngay_cap_nhat) {
        var returnDate = new Date(result.ngay_cap_nhat);
        if (!isNaN(returnDate.getTime())) {
            var hours = String(returnDate.getHours()).padStart(2, "0");
            var minutes = String(returnDate.getMinutes()).padStart(2, "0");
            var seconds = String(returnDate.getSeconds()).padStart(2, "0");
            returnTimeEl.textContent = hours + ":" + minutes + ":" + seconds;
        } else {
            returnTimeEl.textContent = "-";
        }
    } else if (returnTimeEl) {
        returnTimeEl.textContent = "-";
    }
    
    // Load images
    loadSieuamResultImages(result.id);
}

// Load sieuam result images
function loadSieuamResultImages(resultId) {
    fetch(`./?action=get_sieuam_images&result_id=${resultId}`)
        .then(response => response.json())
        .then(data => {
            const gallery = document.getElementById("sv_image_gallery");
            if (data.success && data.images && data.images.length > 0) {
                gallery.innerHTML = "";
                data.images.forEach(imgData => {
                    const div = document.createElement("div");
                    div.className = "image-item";
                    const img = document.createElement("img");
                    img.src = imgData.duong_dan;
                    img.onclick = function() { zoomImage(imgData.duong_dan); };
                    
                    const caption = document.createElement("div");
                    caption.className = "image-caption";
                    caption.textContent = imgData.ten_file;
                    
                    div.appendChild(img);
                    div.appendChild(caption);
                    gallery.appendChild(div);
                });
            } else {
                gallery.innerHTML = "<div class=\"image-item\"><p class=\"text-muted\">Không có hình ảnh</p></div>";
            }
        })
        .catch(error => {
            console.error("Error loading images:", error);
            document.getElementById("sv_image_gallery").innerHTML = "<div class=\"image-item\"><p class=\"text-danger\">Lỗi tải hình ảnh</p></div>";
        });
}

// View sieuam images only
function viewSieuamImages(resultId) {
    currentSieuamResultId = resultId;
    
    // Clear previous images
    const modalBody = document.getElementById("sieuamImageModalBody");
    if (!modalBody) {
        // Create modal if not exists
        createImageModal();
    }
    
    document.getElementById("sieuamImageModalBody").innerHTML = "<div class=\"text-center\"><div class=\"spinner-border\" role=\"status\"><span class=\"visually-hidden\">Loading...</span></div></div>";
    
    // Show modal
    new bootstrap.Modal(document.getElementById("sieuamImageModal")).show();
    
    // Load images
    const url = "./?action=get_sieu_am_images&result_id=" + resultId;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.images && data.images.length > 0) {
                loadSieuamImagesForView(data.images);
            } else {
                document.getElementById("sieuamImageModalBody").innerHTML = "<div class=\"text-center text-muted\"><p>Không có hình ảnh nào</p></div>";
            }
        })
        .catch(error => {
            console.error("Error loading images:", error);
            document.getElementById("sieuamImageModalBody").innerHTML = "<div class=\"text-center text-danger\"><p>Lỗi tải hình ảnh</p></div>";
        });
}

// Create image modal if not exists
function createImageModal() {
    const modalHTML = `
        <div class="modal fade" id="sieuamImageModal" tabindex="-1" aria-labelledby="sieuamImageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Hình ảnh siêu âm</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="sieuamImageModalBody">
                        <!-- Images will be loaded here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML("beforeend", modalHTML);
}

// Load images for view modal
function loadSieuamImagesForView(images) {
    const container = document.getElementById("sieuamImageModalBody");
    
    if (!images || images.length === 0) {
        container.innerHTML = "<div class=\"text-center text-muted\"><p>Không có hình ảnh nào</p></div>";
        return;
    }
    
    // Clear container
    container.innerHTML = "";
    
    // Create row container
    const rowDiv = document.createElement("div");
    rowDiv.className = "row g-3";
    
    images.forEach(image => {
        // Create column div
        const colDiv = document.createElement("div");
        colDiv.className = "col-md-4 col-lg-3";
        
        // Create card div
        const cardDiv = document.createElement("div");
        cardDiv.className = "card h-100";
        
        // Create image
        const img = document.createElement("img");
        img.src = image.duong_dan;
        img.className = "card-img-top";
        img.style.height = "200px";
        img.style.objectFit = "cover";
        img.style.cursor = "pointer";
        img.alt = "Hình ảnh siêu âm";
        img.onclick = function() { zoomImage(image.duong_dan); };
        
        // Create card body
        const cardBody = document.createElement("div");
        cardBody.className = "card-body p-2";
        
        const small = document.createElement("small");
        small.className = "text-muted";
        small.textContent = image.ten_file || "Hình ảnh";
        
        // Append elements
        cardBody.appendChild(small);
        cardDiv.appendChild(img);
        cardDiv.appendChild(cardBody);
        colDiv.appendChild(cardDiv);
        rowDiv.appendChild(colDiv);
    });
    
    container.appendChild(rowDiv);
}


// Format date time
function formatDateTime(dateString) {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return date.toLocaleDateString("vi-VN") + " " + date.toLocaleTimeString("vi-VN", {hour: "2-digit", minute: "2-digit"});
}

// Enhanced zoom image function with zoom controls and fullscreen
function zoomImage(src) {
    const modal = document.createElement("div");
    modal.className = "modal fade";
    modal.id = "imageZoomModal";
    modal.innerHTML = `
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content bg-dark">
                <div class="modal-header bg-dark border-0">
                    <h5 class="modal-title text-white">Hình ảnh siêu âm</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-light btn-sm" onclick="zoomIn()" id="zoomInBtn">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button type="button" class="btn btn-outline-light btn-sm" onclick="zoomOut()" id="zoomOutBtn">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-outline-light btn-sm" onclick="resetZoom()" id="resetZoomBtn">
                            <i class="fas fa-expand-arrows-alt"></i>
                        </button>
                        <button type="button" class="btn btn-outline-light btn-sm" onclick="toggleFullscreen()" id="fullscreenBtn">
                            <i class="fas fa-expand"></i>
                        </button>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body p-0 d-flex justify-content-center align-items-center" style="height: calc(100vh - 120px); overflow: hidden;">
                    <img src="${src}" class="img-fluid" id="zoomImage" style="max-width: 100%; max-height: 100%; cursor: grab; transition: transform 0.3s ease;">
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Setup zoom functionality
    setupZoomControls();
    
    modal.addEventListener("hidden.bs.modal", () => {
        document.body.removeChild(modal);
    });
}

// Zoom controls
let currentZoom = 1;
const minZoom = 0.5;
const maxZoom = 5;
let isDragging = false;
let startX, startY, scrollLeft, scrollTop;

function setupZoomControls() {
    const img = document.getElementById("zoomImage");
    if (!img) return;
    
    // Mouse wheel zoom
    img.addEventListener("wheel", (e) => {
        e.preventDefault();
        const delta = e.deltaY > 0 ? -0.1 : 0.1;
        currentZoom = Math.max(minZoom, Math.min(maxZoom, currentZoom + delta));
        updateZoom();
    });
    
    // Drag to pan
    img.addEventListener("mousedown", (e) => {
        if (currentZoom > 1) {
            isDragging = true;
            img.style.cursor = "grabbing";
            startX = e.pageX - img.offsetLeft;
            startY = e.pageY - img.offsetTop;
        }
    });
    
    document.addEventListener("mousemove", (e) => {
        if (!isDragging) return;
        e.preventDefault();
        img.style.left = (e.pageX - startX) + "px";
        img.style.top = (e.pageY - startY) + "px";
        img.style.position = "relative";
    });
    
    document.addEventListener("mouseup", () => {
        isDragging = false;
        img.style.cursor = currentZoom > 1 ? "grab" : "default";
    });
}

function zoomIn() {
    currentZoom = Math.min(maxZoom, currentZoom + 0.2);
    updateZoom();
}

function zoomOut() {
    currentZoom = Math.max(minZoom, currentZoom - 0.2);
    updateZoom();
}

function resetZoom() {
    currentZoom = 1;
    updateZoom();
    const img = document.getElementById("zoomImage");
    if (img) {
        img.style.left = "auto";
        img.style.top = "auto";
        img.style.position = "static";
    }
}

function updateZoom() {
    const img = document.getElementById("zoomImage");
    if (img) {
        img.style.transform = "scale(" + currentZoom + ")";
        img.style.cursor = currentZoom > 1 ? "grab" : "default";
    }
}

function toggleFullscreen() {
    const modal = document.getElementById("imageZoomModal");
    if (!document.fullscreenElement) {
        modal.requestFullscreen().catch((err) => {
            console.log("Error attempting to enable fullscreen:", err);
        });
    } else {
        document.exitFullscreen();
    }
}

// Event listeners
document.addEventListener("DOMContentLoaded", function() {
    // Set default date to today
    document.getElementById("hs_date").value = new Date().toISOString().split("T")[0];
    
    // Load initial data
    loadSieuamHistory();
    
    // Filter button
    document.getElementById("hs_filter_btn").addEventListener("click", loadSieuamHistory);
    
    // Enter key in filter inputs
    ["hs_date", "hs_request_id", "hs_patient_code"].forEach(id => {
        document.getElementById(id).addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                loadSieuamHistory();
            }
        });
    });
});
</script>';

renderLayout($content, "Lịch sử siêu âm");
?>
