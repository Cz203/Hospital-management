<?php
require_once 'Views/layouts/layout_helper.php';

// Helper function để format thời gian
function formatTime($time)
{
    return date('H:i', strtotime($time));
}

// Helper function để tính tuổi
function calculateAge($birthDate)
{
    $today = new DateTime();
    $birth = new DateTime($birthDate);
    $age = $today->diff($birth);
    return $age->y;
}

// Helper function để lấy badge class theo trạng thái
function getStatusBadgeClass($status)
{
    switch ($status) {
        case 'Đã xác nhận':
            return 'bg-success';
        case 'Chờ xác nhận':
            return 'bg-warning';
        case 'Hoàn thành':
            return 'bg-info';
        case 'hủy':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

$content = '
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-stethoscope text-success me-2"></i>
                Khám bệnh
            </h1>
            <p class="text-muted">Danh sách lịch hẹn ngày ' . date('d/m/Y', strtotime($selectedDate)) . '</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" onclick="refreshExamination()">
                <i class="fas fa-sync-alt me-2"></i>Làm mới
            </button>
            <a href="./doctor_dashboard" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
        </div>
    </div>

    <!-- Date Picker -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <label for="datePicker" class="form-label fw-bold">
                                <i class="fas fa-calendar-alt me-2"></i>Chọn ngày xem lịch hẹn:
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="datePicker" 
                                   value="' . $selectedDate . '"
                                   min="' . date('Y-m-d', strtotime('-30 days')) . '"
                                   max="' . date('Y-m-d', strtotime('+30 days')) . '">
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary" onclick="goToToday()">
                                    <i class="fas fa-calendar-day me-1"></i>Hôm nay
                                </button>
                                <button class="btn btn-outline-info" onclick="goToYesterday()">
                                    <i class="fas fa-arrow-left me-1"></i>Hôm qua
                                </button>
                                <button class="btn btn-outline-info" onclick="goToTomorrow()">
                                    <i class="fas fa-arrow-right me-1"></i>Ngày mai
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 stats-card" data-filter="all" style="cursor: pointer;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng bệnh nhân hôm nay
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><span id="statTotal">' . ($stats['total_patients'] ?? 0) . '</span></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 stats-card" data-filter="examining" style="cursor: pointer;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Đang khám
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><span id="statExamining">' . ($stats['examining'] ?? 0) . '</span></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-stethoscope fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 stats-card" data-filter="completed" style="cursor: pointer;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Đã khám xong
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><span id="statCompleted">' . ($stats['completed'] ?? 0) . '</span></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-double fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Status -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex align-items-center">
                <span class="me-3 fw-bold">Lọc theo trạng thái:</span>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary filter-btn active" data-filter="all">
                        <i class="fas fa-list me-1"></i>Tất cả
                    </button>
                    <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="confirmed">
                        <i class="fas fa-user-clock me-1"></i>Bệnh nhân chưa khám
                    </button>
                    <button type="button" class="btn btn-outline-warning filter-btn" data-filter="examining">
                        <i class="fas fa-stethoscope me-1"></i>Đang khám
                    </button>
                    <button type="button" class="btn btn-outline-success filter-btn" data-filter="completed">
                        <i class="fas fa-check-double me-1"></i>Đã khám xong
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointments List -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-users text-primary me-2"></i>
                        Danh sách bệnh nhân
                    </h5>
                </div>
                <div class="card-body" id="appointmentsList">';

if (empty($appointments)) {
    $content .= '
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có bệnh nhân nào</h5>
                        <p class="text-muted">Chưa có lịch hẹn nào đã được xác nhận trong ngày này</p>
                    </div>';
} else {
    $content .= '<div class="row">';
    foreach ($appointments as $appointment) {
        $content .= '
                        <div class="col-lg-6 col-xl-4 mb-4 appointment-item" data-status="' . ($appointment['trang_thai'] === 'Đang khám' ? 'examining' : ($appointment['trang_thai'] === 'Hoàn thành' ? 'completed' : 'confirmed')) . '" data-appointment-id="' . $appointment['id'] . '" data-patient-name="' . htmlspecialchars($appointment['ten_benh_nhan'] ?? '') . '" data-phone="' . htmlspecialchars($appointment['so_dien_thoai'] ?? '') . '" data-dob="' . htmlspecialchars($appointment['ngay_sinh'] ?? '') . '" data-gender="' . htmlspecialchars($appointment['gioi_tinh'] ?? '') . '" data-address="' . htmlspecialchars($appointment['dia_chi'] ?? '') . '" data-patient-code="' . htmlspecialchars($appointment['benh_nhan_id'] ?? '') . '" data-bhyt="' . htmlspecialchars($appointment['bao_hiem_y_te'] ?? '') . '" data-bhyt-het-han="' . htmlspecialchars($appointment['ngay_het_han'] ?? '') . '">
                            <div class="card appointment-card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm ' . ($appointment['trang_thai'] === 'Đang khám' ? 'bg-warning' : 'bg-success') . ' text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                            <i class="fas ' . ($appointment['trang_thai'] === 'Đang khám' ? 'fa-stethoscope' : 'fa-check-circle') . '"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">' . formatTime($appointment['gio_hen']) . '</h6>
                                            <small class="text-muted">' . $appointment['trang_thai'] . '</small>
                                        </div>
                                    </div>
                                    <span class="badge ' . getStatusBadgeClass($appointment['trang_thai']) . '">
                                        ' . $appointment['trang_thai'] . '
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="patient-info mb-3">
                                        <h6 class="text-primary mb-1">' . htmlspecialchars($appointment['ten_benh_nhan']) . '</h6>
                                        <div class="row text-muted small">
                                            <div class="col-6">
                                                <i class="fas fa-phone me-1"></i>
                                                ' . htmlspecialchars($appointment['so_dien_thoai'] ?? 'N/A') . '
                                            </div>
                                            <div class="col-6">
                                                <i class="fas fa-birthday-cake me-1"></i>
                                                ' . calculateAge($appointment['ngay_sinh']) . ' tuổi
                                            </div>
                                        </div>
                                        <div class="row text-muted small mt-1">
                                            <div class="col-6">
                                                <i class="fas fa-venus-mars me-1"></i>
                                                ' . ($appointment['gioi_tinh'] ?? 'N/A') . '
                                            </div>
                                            <div class="col-6">
                                                <i class="fas fa-tint me-1"></i>
                                                ' . ($appointment['nhom_mau'] ?? 'N/A') . '
                                            </div>
                                        </div>
                                    </div>';

        if (!empty($appointment['ly_do'])) {
            $content .= '
                                    <div class="appointment-reason mb-3">
                                        <h6 class="text-dark mb-1">Lý do khám:</h6>
                                        <p class="text-muted small mb-0">' . htmlspecialchars($appointment['ly_do']) . '</p>
                                    </div>';
        }

        $content .= '
                                    <div class="appointment-actions">';

        if ($appointment['trang_thai'] === 'Đã xác nhận') {
            $content .= '
                                        <button class="btn btn-primary btn-sm me-2" onclick="startExamination(' . $appointment['id'] . ')">
                                            <i class="fas fa-stethoscope me-1"></i>Bắt đầu khám
                                        </button>
                                        ';
        } else if ($appointment['trang_thai'] === 'Đang khám') {
            $content .= '
                                        <button class="btn btn-primary btn-sm me-2" onclick="continueExamination(' . $appointment['id'] . ')">
                                            <i class="fas fa-play me-1"></i>Tiếp tục khám
                                        </button>
                                        ';
        }

        $content .= '
                                    </div>
                                </div>
                            </div>
                        </div>';
    }
    $content .= '</div>';
}

$content .= '
                </div>
            </div>
        </div>
    </div>
</div>
';

$content .= file_get_contents('Views/layouts/examination_modal.php');

$content .= '<link rel="stylesheet" href="assets/css/examination.css">';
$content .= '<script src="assets/js/examination.js"></script>';

// Auto open modal nếu có appointment_id
if (isset($autoOpenModal) && $autoOpenModal && $appointmentId) {
    $content .= '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Tìm appointment item và lấy thông tin
        var appointmentItem = document.querySelector(\'[data-appointment-id="' . $appointmentId . '"]\');
        if (appointmentItem) {
            // Lấy thông tin bệnh nhân từ data attributes
            var patientName = appointmentItem.getAttribute("data-patient-name") || "";
            var patientPhone = appointmentItem.getAttribute("data-phone") || "";
            var patientDob = appointmentItem.getAttribute("data-dob") || "";
            var patientGender = appointmentItem.getAttribute("data-gender") || "";
            var patientAddress = appointmentItem.getAttribute("data-address") || "";
            var patientCode = appointmentItem.getAttribute("data-patient-code") || "";
            var patientBHYT = appointmentItem.getAttribute("data-bhyt") || "";
            var patientBHYTHetHan = appointmentItem.getAttribute("data-bhyt-het-han") || "";
            
            // Điền thông tin vào form
            var pName = document.getElementById("patientName");
            var pPhone = document.getElementById("patientPhone");
            var pAge = document.getElementById("patientAge");
            var pGender = document.getElementById("patientGender");
            if (pName) pName.value = patientName;
            if (pPhone) pPhone.value = patientPhone;
            if (pAge) pAge.value = patientDob;
            if (pGender) pGender.value = patientGender;
            
            // Điền thông tin vào form khám bệnh
            var examHoTen = document.querySelector(\'[name="ho_ten"]\');
            var examGioiTinh = document.querySelector(\'[name="gioi_tinh"]\');
            var examNgaySinh = document.querySelector(\'[name="ngay_sinh"]\');
            var examThangSinh = document.querySelector(\'[name="thang_sinh"]\');
            var examNamSinh = document.querySelector(\'[name="nam_sinh"]\');
            var examTuoi = document.querySelector(\'[name="tuoi"]\');
            var examDiaChi = document.querySelector(\'[name="dia_chi"]\');
            var examDienThoai = document.querySelector(\'[name="dien_thoai_bao_tin"]\');
            
            if (examHoTen) examHoTen.value = patientName.toUpperCase();
            if (examGioiTinh) {
                if (patientGender === "Nam") {
                    document.getElementById("nam").checked = true;
                } else if (patientGender === "Nữ") {
                    document.getElementById("nu").checked = true;
                }
            }
            if (examDienThoai) examDienThoai.value = patientPhone;
            
            // Parse ngày sinh để điền vào các trường riêng biệt
            if (patientDob) {
                console.log("Patient DOB:", patientDob); // Debug log
                var dobParts = [];
                
                // Xử lý format YYYY-MM-DD (từ database)
                if (patientDob.includes("-")) {
                    dobParts = patientDob.split("-");
                    if (dobParts.length === 3) {
                        if (examNgaySinh) examNgaySinh.value = dobParts[2]; // Ngày
                        if (examThangSinh) examThangSinh.value = dobParts[1]; // Tháng
                        if (examNamSinh) examNamSinh.value = dobParts[0]; // Năm
                        
                        // Tính tuổi
                        var today = new Date();
                        var birthDate = new Date(dobParts[0], dobParts[1] - 1, dobParts[2]);
                        var age = today.getFullYear() - birthDate.getFullYear();
                        var monthDiff = today.getMonth() - birthDate.getMonth();
                        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                            age--;
                        }
                        if (examTuoi) examTuoi.value = age;
                    }
                }
                // Xử lý format DD/MM/YYYY (từ form)
                else if (patientDob.includes("/")) {
                    dobParts = patientDob.split("/");
                    if (dobParts.length === 3) {
                        if (examNgaySinh) examNgaySinh.value = dobParts[0];
                        if (examThangSinh) examThangSinh.value = dobParts[1];
                        if (examNamSinh) examNamSinh.value = dobParts[2];
                        
                        // Tính tuổi
                        var today = new Date();
                        var birthDate = new Date(dobParts[2], dobParts[1] - 1, dobParts[0]);
                        var age = today.getFullYear() - birthDate.getFullYear();
                        var monthDiff = today.getMonth() - birthDate.getMonth();
                        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                            age--;
                        }
                        if (examTuoi) examTuoi.value = age;
                    }
                }
            }
            
            // Điền địa chỉ
            if (examDiaChi && patientAddress) {
                examDiaChi.value = patientAddress;
            }
            
            // Tự động tích đối tượng dựa trên BHYT
            console.log("Patient BHYT:", patientBHYT); // Debug log
            console.log("Patient BHYT Het Han:", patientBHYTHetHan); // Debug log
            if (patientBHYT && patientBHYT.trim() !== "" && patientBHYT !== "null") {
                // Có BHYT -> tích vào BHYT
                console.log("Tích BHYT");
                document.getElementById("bhyt").checked = true;
                
                // Điền số thẻ BHYT
                var soTheBHYT = document.querySelector(\'[name="so_the_bhyt"]\');
                if (soTheBHYT) soTheBHYT.value = patientBHYT;
                
                // Điền ngày hết hạn BHYT
                if (patientBHYTHetHan && patientBHYTHetHan.trim() !== "" && patientBHYTHetHan !== "null") {
                    var bhytNgay = document.querySelector(\'[name="bhyt_ngay"]\');
                    var bhytThang = document.querySelector(\'[name="bhyt_thang"]\');
                    var bhytNam = document.querySelector(\'[name="bhyt_nam"]\');
                    
                    // Parse ngày hết hạn (format YYYY-MM-DD)
                    var hetHanParts = patientBHYTHetHan.split("-");
                    if (hetHanParts.length === 3) {
                        if (bhytNgay) bhytNgay.value = hetHanParts[2]; // Ngày
                        if (bhytThang) bhytThang.value = hetHanParts[1]; // Tháng
                        if (bhytNam) bhytNam.value = hetHanParts[0]; // Năm
                    }
                }
            } else {
                // Không có BHYT -> tích vào Thu phí
                console.log("Tích Thu phí");
                document.getElementById("thu_phi").checked = true;
            }
            
            // Điền thông tin vào phần history
            var hName = document.getElementById("historyPatientName");
            var hDob = document.getElementById("historyDob");
            var hCode = document.getElementById("historyCode");
            var hAddr = document.getElementById("historyAddress");
            var hPhone = document.getElementById("historyPhone");
            var hGender = document.getElementById("historyGender");
            if (hName) hName.value = patientName;
            if (hDob) hDob.value = patientDob;
            if (hCode) hCode.value = patientCode;
            if (hAddr) hAddr.value = patientAddress;
            if (hPhone) hPhone.value = patientPhone;
            if (hGender) hGender.value = patientGender;
            
            // Set appointment ID
            var hiddenId = document.getElementById("examinationAppointmentId");
            var historyId = document.getElementById("historyPatientId");
            if (hiddenId) hiddenId.value = "' . $appointmentId . '";
            if (historyId) historyId.value = patientCode;
            
            // Điền chuyên khoa bác sĩ vào buồng khám bệnh
            var buongKham = document.getElementById("buong_kham");
            if (buongKham) {
                buongKham.value = "' . (isset($doctor['chuyen_khoa']) && !empty($doctor['chuyen_khoa']) ? $doctor['chuyen_khoa'] : 'Chuyên khoa') . '";
            }
            
            // Tự động điền thời gian khám, ngày khám/ký (hiện tại) và tên bác sĩ
            var now = new Date();
            var pad = function(n){ return n.toString().padStart(2,"0"); };
            var setIf = function(selector, value){ var el = document.querySelector(selector); if (el) { el.value = value; } };

            // 12. Đến khám bệnh lúc
            // Bác sĩ sẽ tự nhập Giờ/Phút, hệ thống không tự điền
            setIf("[name=ngay_kham]", now.getDate());
            setIf("[name=thang_kham]", now.getMonth() + 1);
            setIf("[name=nam_kham]", now.getFullYear());

            // Footer ký ngày tháng năm
            setIf("[name=ngay_ky]", now.getDate());
            setIf("[name=thang_ky]", now.getMonth() + 1);
            setIf("[name=nam_ky]", now.getFullYear());

            // Tên bác sĩ khám
            setIf("[name=ten_bac_si]", "' . (isset($doctor['ten']) && !empty($doctor['ten']) ? addslashes($doctor['ten']) : 'Bác sĩ') . '");
            
            // Mở modal
            var modal = new bootstrap.Modal(document.getElementById("examinationModal"));
            modal.show();
            
            // Tải lại phiếu khám đã lưu (nếu có) theo appointment để tiếp tục khám
            try { if (typeof loadExaminationFormIfAny === "function") { loadExaminationFormIfAny(); } } catch (e) {}

            // Load allergy history nếu có patient code
            if (patientCode) {
                fetchAllergyHistory(patientCode);
            }

            // Khi chuyển sang tab Kết quả X-Quang thì tải kết quả (read-only)
            document.querySelectorAll(".exam-nav").forEach(function(a){
                a.addEventListener("click", function(){
                    if (this.getAttribute("href") === "#sec-xray-result") {
                        loadXrayResultReadonly();
                    }
                });
            });

            function loadXrayResultReadonly(){
                try{
                    var examIdEl = document.getElementById("xray_examination_id");
                    var examId = examIdEl ? examIdEl.value : "";
                    if(!examId || examId === ""){
                        // Thử lấy từ form đã lưu trong JS nếu có
                        if (typeof getCurrentExaminationId === "function") {
                            examId = getCurrentExaminationId();
                        }
                    }
                    if(!examId){ return; }
                    fetch("?action=get_xray_result_by_exam&exam_id=" + encodeURIComponent(examId))
                      .then(function(res){ return res.json(); })
                      .then(function(json){
                        var box = document.getElementById("xrayResultReadonly");
                        var empty = document.getElementById("xrayResultEmpty");
                        if(!json.success || !json.data){ if(box) box.style.display="none"; if(empty) empty.style.display="block"; return; }
                        var d = json.data; if(empty) empty.style.display="none"; if(box) box.style.display="block";
                        setText("xr_ro_name", d.ho_ten);
                        setText("xr_ro_gender", d.gioi_tinh);
                        setText("xr_ro_yob", d.nam_sinh);
                        setText("xr_ro_id", d.id);
                        setText("xr_ro_address", d.dia_chi);
                        var dt = d.ngay_cap_nhat || d.ngay_tao; var nd = dt ? new Date(dt) : new Date();
                        setText("xr_ro_date", nd.toLocaleDateString("vi-VN"));
                        setText("xr_ro_time", nd.toLocaleTimeString("vi-VN"));
                        setText("xr_ro_chandoan", d.chan_doan_vao_vien || "");
                        setText("xr_ro_bschidinh", d.bac_si_chi_dinh || "");
                        setText("xr_ro_noidung", d.noi_dung || "");
                        setHtml("xr_ro_ketqua", (d.noi_dung || ""));
                        setHtml("xr_ro_ketluan", (d.ket_luan || ""));
                        var today = new Date();
                        setText("xr_ro_today", "Ngày " + today.getDate() + " tháng " + (today.getMonth()+1) + " năm " + today.getFullYear());
                        setText("xr_ro_bsxq", d.bac_si_xquang || "");

                        // Save current px id for image tab
                        var infoBox = document.getElementById("xrayResultReadonly");
                        if(infoBox){ infoBox.setAttribute("data-pxid", d.id); }
                        // Preload images
                        loadXrayImagesForExam(d.id);
                      })
                      .catch(function(){});
                }catch(e){}
            }

            function setText(id, val){ var el = document.getElementById(id); if(el) el.textContent = (val ?? ""); }
            function setHtml(id, val){ var el = document.getElementById(id); if(el) el.innerHTML = (val ?? ""); }

            // Load images for px id (used by image tab)
            function loadXrayImagesForExam(pxId){
                if(!pxId) return;
                fetch("?action=get_saved_xray_images&id=" + encodeURIComponent(pxId))
                  .then(function(res){ return res.json(); })
                  .then(function(json){
                    var wrap = document.getElementById("xr_ro_gallery");
                    if(!wrap) return;
                    wrap.innerHTML = "";
                    if(!json.success || !json.data || json.data.length===0){
                        wrap.innerHTML = "<div class=\"text-muted\">Chưa có ảnh</div>";
                        return;
                    }
                    json.data.forEach(function(img){
                        var url = img.file_path || "";
                        if(url && !(url.startsWith("http")||url.startsWith("./")||url.startsWith("/"))){ url = "./" + url; }
                        var col = document.createElement("div");
                        col.className = "col-md-3 mb-2";
                        var html = `
                          <div class="border p-1">
                            <img src="${url}" class="img-fluid" style="cursor:pointer"
                                 onclick="zoomImage(&quot;${url}&quot;)"
                                 onerror="this.replaceWith(document.createTextNode(&quot;Không tải được ảnh&quot;))"/>
                          </div>`;
                        col.innerHTML = html;
                        wrap.appendChild(col);
                    });
                  })
                  .catch(function(){});
            }

            // When user clicks on the images tab, load gallery using saved px id
            document.addEventListener("click", function(e){
                var t = e.target;
                if(t && t.getAttribute && t.getAttribute("data-bs-target") === "#xr_tabpane_images"){
                    var pxid = document.getElementById("xrayResultReadonly")?.getAttribute("data-pxid") || "";
                    if(pxid){ loadXrayImagesForExam(pxid); }
                }
            });
//Zoom hình ảnh xquang
            // Provide zoomImage viewer if not already defined on the page
            if(!window.zoomImage){
              window.zoomImage = function(imageUrl){
                var modal = document.createElement("div");
                modal.className = "modal fade";
                modal.innerHTML = `
                  <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Xem ảnh X-Quang</h5>
                        <div class="btn-group me-2">
                          <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomOutBtn" title="Thu nhỏ"><i class="fas fa-minus"></i></button>
                          <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomInBtn" title="Phóng to"><i class="fas fa-plus"></i></button>
                          <button type="button" class="btn btn-sm btn-outline-secondary" id="resetZoomBtn" title="Reset"><i class="fas fa-expand-arrows-alt"></i></button>
                          <button type="button" class="btn btn-sm btn-outline-primary" id="fullscreenBtn" title="Toàn màn hình"><i class="fas fa-expand"></i></button>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body text-center" style="background:#f8f9fa; overflow:auto; max-height:80vh;">
                        <div id="imageContainer" style="position:relative; display:inline-block;">
                          <img src="${imageUrl}" id="zoomImage" style="max-width:100%; height:auto; cursor:grab; transition: transform 0.3s ease;" draggable="false"/>
                        </div>
                      </div>
                    </div>
                  </div>`;
                document.body.appendChild(modal);
                var bsModal = new bootstrap.Modal(modal);
                bsModal.show();

                var zoomLevel=1, isDragging=false, startX=0, startY=0, translateX=0, translateY=0;
                var img = modal.querySelector("#zoomImage");
                var container = modal.querySelector("#imageContainer");
                function update(){ img.style.transform = `scale(${zoomLevel}) translate(${translateX}px, ${translateY}px)`; }
                modal.querySelector("#zoomInBtn").addEventListener("click", function(){ zoomLevel=Math.min(zoomLevel*1.2,5); update(); });
                modal.querySelector("#zoomOutBtn").addEventListener("click", function(){ zoomLevel=Math.max(zoomLevel/1.2,0.1); update(); });
                modal.querySelector("#resetZoomBtn").addEventListener("click", function(){ zoomLevel=1; translateX=0; translateY=0; update(); });
                modal.querySelector("#fullscreenBtn").addEventListener("click", function(){
                  if(!document.fullscreenElement){ container.requestFullscreen().then(()=>{ modal.querySelector("#fullscreenBtn").innerHTML="<i class=\"fas fa-compress\"></i>"; }); }
                  else { document.exitFullscreen().then(()=>{ modal.querySelector("#fullscreenBtn").innerHTML="<i class=\"fas fa-expand\"></i>"; }); }
                });
                container.addEventListener("wheel", function(e){ e.preventDefault(); var d=e.deltaY>0?0.9:1.1; zoomLevel=Math.max(0.1, Math.min(5, zoomLevel*d)); update(); });
                img.addEventListener("mousedown", function(e){ isDragging=true; startX=e.clientX-translateX; startY=e.clientY-translateY; img.style.cursor="grabbing"; });
                document.addEventListener("mousemove", function(e){ if(isDragging){ translateX=e.clientX-startX; translateY=e.clientY-startY; update(); }});
                document.addEventListener("mouseup", function(){ isDragging=false; img.style.cursor="grab"; });
                var tX, tY; img.addEventListener("touchstart", function(e){ if(e.touches.length===1){ tX=e.touches[0].clientX; tY=e.touches[0].clientY; }});
                img.addEventListener("touchmove", function(e){ if(e.touches.length===1){ e.preventDefault(); translateX+=e.touches[0].clientX-tX; translateY+=e.touches[0].clientY-tY; tX=e.touches[0].clientX; tY=e.touches[0].clientY; update(); }});
                modal.addEventListener("hidden.bs.modal", function(){ document.body.removeChild(modal); });
              };
            }
        }
    });
    </script>';
}

renderLayout($content, 'Khám bệnh - Hệ thống Quản lý Bệnh viện');