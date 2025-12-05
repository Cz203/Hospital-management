// Global variables
var savedExamFormId = null;
var savedXrayFormId = null;

// Examination interactions
(function init() {
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", onReady);
  } else {
    onReady();
  }

  function onReady() {
    bindDatePicker();
    bindSidebar();
    bindFilterCards();
    bindFilterButtons();
    loadReferenceRanges(); // Load reference ranges for lab result validation
    var btnSave = document.getElementById("btnSaveExam");
    if (btnSave) btnSave.style.display = "none";
    
    // Bind complete examination button
    var completeBtn = document.getElementById("complete-examination-btn");
    if (completeBtn) {
      completeBtn.addEventListener("click", completeExamination);
    }
  }

  function bindDatePicker() {
    var dp = document.getElementById("datePicker");
    if (!dp) return;
    dp.addEventListener("change", function () {
      var v = this.value;
      if (!v) return;
      window.location.href = "./doctor_examination?date=" + v;
    });
  }

  function bindSidebar() {
    var links = document.querySelectorAll(".exam-nav");
    var secs = document.querySelectorAll(".exam-section");
    var btnSave = document.getElementById("btnSaveExam");
    if (!links.length || !secs.length) return;
    secs.forEach(function (s) {
      s.classList.remove("active");
    });
    var def = document.querySelector("#sec-patient");
    if (def) def.classList.add("active");
    links.forEach(function (l) {
      l.classList.remove("active");
    });
    var defL = document.querySelector('.exam-nav[href="#sec-patient"]');
    if (defL) defL.classList.add("active");
    links.forEach(function (a) {
      a.addEventListener("click", function (e) {
        e.preventDefault();
        var id = this.getAttribute("href");
        secs.forEach(function (s) {
          s.classList.remove("active");
        });
        var t = document.querySelector(id);
        if (t) t.classList.add("active");
        links.forEach(function (l) {
          l.classList.remove("active");
        });
        this.classList.add("active");
        if (btnSave)
          btnSave.style.display =
            id === "#sec-history" ? "inline-block" : "none";

        // Hiện/ẩn nút Lưu phiếu khám và In phiếu khám chỉ ở tab "Khám bệnh"
        var btnSaveExamForm = document.getElementById("btnSaveExamForm");
        var btnPrintExamForm = document.getElementById("btnPrintExamForm");
        if (btnSaveExamForm)
          btnSaveExamForm.style.display =
            id === "#sec-exam" ? "inline-block" : "none";
        if (btnPrintExamForm)
          btnPrintExamForm.style.display =
            id === "#sec-exam" ? "inline-block" : "none";

        // Hiện/ẩn nút X-Ray chỉ ở tab "X-Quang"
        var saveXrayBtn = document.getElementById("saveXrayForm");
        var printXrayBtn = document.getElementById("printXrayForm");
        if (saveXrayBtn)
          saveXrayBtn.style.display =
            id === "#sec-xray" ? "inline-block" : "none";
        if (printXrayBtn)
          printXrayBtn.style.display =
            id === "#sec-xray" ? "inline-block" : "none";

        // Hiện/ẩn nút Siêu âm chỉ ở tab "Siêu âm"
        var saveUltrasoundBtn = document.getElementById("saveUltrasoundForm");
        var printUltrasoundBtn = document.getElementById("printUltrasoundForm");
        if (saveUltrasoundBtn)
          saveUltrasoundBtn.style.display =
            id === "#sec-ultrasound" ? "inline-block" : "none";
        if (printUltrasoundBtn)
          printUltrasoundBtn.style.display =
            id === "#sec-ultrasound" ? "inline-block" : "none";

        // Hiện/ẩn nút Xét nghiệm chỉ ở tab "Xét nghiệm"
        var saveLabBtn = document.getElementById("saveLabForm");
        var printLabBtn = document.getElementById("printLabForm");
        if (saveLabBtn)
          saveLabBtn.style.display =
            id === "#sec-lab" ? "inline-block" : "none";
        if (printLabBtn)
          printLabBtn.style.display =
            id === "#sec-lab" ? "inline-block" : "none";

            // Hiện/ẩn nút Đơn thuốc chỉ ở tab "Kê đơn thuốc"
            var savePrescriptionBtn = document.getElementById("save-prescription-btn");
            var printPrescriptionBtn = document.getElementById("print-prescription-btn");
            if (savePrescriptionBtn)
              savePrescriptionBtn.style.display =
                id === "#sec-prescription" ? "inline-block" : "none";
            if (printPrescriptionBtn)
              printPrescriptionBtn.style.display =
                id === "#sec-prescription" ? "inline-block" : "none";

            // Ẩn nút Kê biên lai khi không ở tab Kê biên lai
            var saveReceiptBtn = document.getElementById("save-receipt-btn");
            var printReceiptBtn = document.getElementById("print-receipt-btn");
            if (saveReceiptBtn && id !== "#sec-result") saveReceiptBtn.style.display = "none";
            if (printReceiptBtn && id !== "#sec-result") printReceiptBtn.style.display = "none";

        // Khi chuyển sang tab X-Quang, tự đổ dữ liệu bệnh nhân và mặc định
        if (id === "#sec-xray") {
          prefillXRaySection();
          initXRaySuggestions();

          // Load dữ liệu X-Ray đã lưu (nếu có)
          var examId = getCurrentExaminationId();
          if (examId) {
            checkExistingXrayForm(examId);
          }
        }

        // Khi chuyển sang tab Siêu âm, hiện nút và load dữ liệu đã lưu
        if (id === "#sec-ultrasound") {
          // Delay prefill to ensure DOM is ready
          setTimeout(function () {
            prefillUltrasoundSection();
          }, 100);

          // Load dữ liệu Siêu âm đã lưu (nếu có)
          var examId = getCurrentExaminationId();
          if (examId) {
            loadUltrasoundFormData(examId);
          }

          // Delay initialization to ensure DOM is ready and data is loaded
          setTimeout(function () {
            initUltrasoundSuggestions();
          }, 200);
        }

        // Hiện/ẩn nút Lưu Biên Lai chỉ ở tab "Kê biên lai"
        var saveReceiptBtn = document.getElementById("save-receipt-btn");
        if (saveReceiptBtn)
          saveReceiptBtn.style.display =
            id === "#sec-result" ? "inline-block" : "none";

        // Khi chuyển sang tab Xét nghiệm, tự đổ dữ liệu bệnh nhân và mặc định
        if (id === "#sec-lab") {
          prefillLabSection();

          // Load dữ liệu Xét nghiệm đã lưu (nếu có) - chỉ khi có examination ID
          var examId = getCurrentExaminationId();
          if (examId) {
            loadLabFormData(examId);
          }

          // Delay initialization to ensure DOM is ready and data is loaded
          setTimeout(function () {
            initLabSuggestions();
          }, 200);
        }

        // Khi chuyển sang tab Kết quả xét nghiệm, load dữ liệu kết quả
        if (id === "#sec-lab-result") {
          var examId = getCurrentExaminationId();
          if (examId) {
            loadLabResultData(examId);
          } else {
            showLabResultEmpty();
          }
        }
      });
    });
  }

  function bindFilterCards() {
    var cards = document.querySelectorAll(".stats-card");
    cards.forEach(function (c) {
      c.addEventListener("click", function () {
        var f = c.getAttribute("data-filter");
        filterAppointments(f);
        updateActiveFilter(f);
      });
    });
  }

  function bindFilterButtons() {
    var btns = document.querySelectorAll(".filter-btn");
    btns.forEach(function (b) {
      b.addEventListener("click", function () {
        var f = this.getAttribute("data-filter");
        filterAppointments(f);
        updateActiveFilter(f);
      });
    });
  }
})();

// Utility functions used by inline handlers
function goToToday() {
  var d = new Date().toISOString().split("T")[0];
  window.location.href = "./doctor_examination?date=" + d;
}
function goToYesterday() {
  var d = new Date();
  d.setDate(d.getDate() - 1);
  window.location.href =
    "./doctor_examination?date=" + d.toISOString().split("T")[0];
}
function goToTomorrow() {
  var d = new Date();
  d.setDate(d.getDate() + 1);
  window.location.href =
    "./doctor_examination?date=" + d.toISOString().split("T")[0];
}
function refreshExamination() {
  location.reload();
}

function confirmAppointment(id) {
  if (confirm("Bạn có chắc chắn muốn xác nhận lịch hẹn này?")) {
    updateAppointmentStatus(id, "Đã xác nhận");
  }
}
function cancelAppointment(id) {
  var reason = prompt("Nhập lý do hủy/từ chối lịch hẹn:");
  if (reason && reason.trim() !== "")
    updateAppointmentStatus(id, "hủy", reason);
}

function startExamination(id) {
  if (!confirm("Bắt đầu khám bệnh cho lịch hẹn này?")) return;

  // Clear X-Ray form when starting new examination
  clearXrayForm();

  // Chuyển hướng đến trang examination với appointment_id để tự động mở modal
  var currentDate = document.getElementById("datePicker")
    ? document.getElementById("datePicker").value
    : "";
  var url = "./doctor_examination?date=" + currentDate + "&start_exam=" + id;
  window.location.href = url;
}
function viewPatientDetails(id) {
  alert("Xem chi tiết bệnh nhân cho lịch hẹn ID: " + id);
}
function completeExamination(id) {
  if (!confirm("Hoàn thành khám bệnh cho lịch hẹn này?")) return;

  // Clear X-Ray form when completing examination
  clearXrayForm();

  var f = document.createElement("form");
  f.method = "POST";
  f.action = "./complete_examination";
  var a = document.createElement("input");
  a.type = "hidden";
  a.name = "appointment_id";
  a.value = id;
  var b = document.createElement("input");
  b.type = "hidden";
  b.name = "selected_date";
  b.value = (document.getElementById("datePicker") || {}).value || "";
  f.appendChild(a);
  f.appendChild(b);
  document.body.appendChild(f);
  f.submit();
}

function continueExamination(id) {
  // Chuyển hướng đến trang examination với appointment_id để tự động mở modal
  var currentDate = document.getElementById("datePicker")
    ? document.getElementById("datePicker").value
    : "";
  var url = "./doctor_examination?date=" + currentDate + "&continue_exam=" + id;
  window.location.href = url;
}

// ===== Save & Print Examination Form =====
function saveExaminationForm() {
  var form = document.getElementById("examinationForm");
  if (!form) {
    alert("Không tìm thấy form khám bệnh");
    return;
  }
  var appointmentId = document.getElementById("examinationAppointmentId")
    ? document.getElementById("examinationAppointmentId").value
    : "";
  var patientId = document.getElementById("historyPatientId")
    ? document.getElementById("historyPatientId").value
    : "";
  if (!patientId) {
    alert("Thiếu mã bệnh nhân");
    return;
  }
  // Require diagnosis before saving (Chẩn đoán vào viện)
  var diagnosisField = document.querySelector('[name="chan_doan_vao_vien"]');
  if (!diagnosisField || !diagnosisField.value || diagnosisField.value.trim() === "") {
    alert("Vui lòng nhập 'Chẩn đoán vào viện' trước khi lưu phiếu khám!");
    if (diagnosisField) diagnosisField.focus();
    return;
  }
  var fd = new FormData(form);
  fd.append("patient_id", patientId);
  fd.append("appointment_id", appointmentId);
  fetch("./save_examination_form", { method: "POST", body: fd })
    .then(function (r) {
      if (!r.ok) {
        console.error("HTTP Error:", r.status, r.statusText);
        throw new Error("HTTP " + r.status + ": " + r.statusText);
      }
      return r.json();
    })
    .then(function (d) {
      console.log("Server response:", d);
      if (d && d.success) {
        alert("Lưu phiếu khám thành công! Mã số: " + d.id);
        window._lastExamFormId = d.id;
      // Set the hidden input value
      var examIdInput = document.getElementById('id_phieu_kham_benh');
      if (examIdInput) {
        examIdInput.value = d.id;
      }
      } else {
        var errorMsg = d && d.message ? d.message : "Không rõ lý do";
        console.error("Save failed:", errorMsg);
        alert("Không thể lưu phiếu khám: " + errorMsg);
      }
    })
    .catch(function (err) {
      console.error("Error saving examination form:", err);
      alert("Lỗi khi lưu phiếu khám: " + err.message);
    });
}

function printExaminationForm() {
  var id = window._lastExamFormId;
  if (!id) {
    alert("Vui lòng lưu phiếu trước khi in.");
    return;
  }
  window.open(
    "./print_examination_form?id=" + encodeURIComponent(id),
    "_blank"
  );
}

// Load existing examination form by appointment when modal opens
function loadExaminationFormIfAny() {
  var appointmentIdEl = document.getElementById("examinationAppointmentId");
  if (!appointmentIdEl || !appointmentIdEl.value) {
    return;
  }
  var fd = new FormData();
  fd.append("appointment_id", appointmentIdEl.value);
  fetch("./get_examination_form", { method: "POST", body: fd })
    .then(function (r) {
      return r.json();
    })
    .then(function (d) {
      if (!d || !d.success || !d.data) {
        return;
      }
      var x = d.data;
      var set = function (name, val) {
        var el = document.querySelector('[name="' + name + '"]');
        if (el != null && val != null) {
          el.value = val;
        }
      };
      set("so_y_te", x.so_y_te);
      set("benh_vien", x.benh_vien);
      set("buong_kham", x.buong_kham);
      set("ho_ten", x.ho_ten);
      set("ngay_sinh", x.ngay_sinh);
      set("thang_sinh", x.thang_sinh);
      set("nam_sinh", x.nam_sinh);
      set("tuoi", x.tuoi);
      if (x.gioi_tinh === "Nam" && document.getElementById("nam"))
        document.getElementById("nam").checked = true;
      if (
        (x.gioi_tinh === "Nu" || x.gioi_tinh === "Nữ") &&
        document.getElementById("nu")
      )
        document.getElementById("nu").checked = true;
      set("nghe_nghiep", x.nghe_nghiep);
      set("dan_toc", x.dan_toc);
      set("ngoai_kieu", x.ngoai_kieu);
      set("noi_lam_viec", x.noi_lam_viec);
      set("dia_chi", x.dia_chi);
      if (document.getElementById("bhyt"))
        document.getElementById("bhyt").checked = x.doi_tuong_bhyt == 1;
      if (document.getElementById("thu_phi"))
        document.getElementById("thu_phi").checked = x.doi_tuong_thu_phi == 1;
      if (document.getElementById("mien"))
        document.getElementById("mien").checked = x.doi_tuong_mien == 1;
      if (document.getElementById("khac"))
        document.getElementById("khac").checked = x.doi_tuong_khac == 1;
      set("bhyt_ngay", x.bhyt_ngay);
      set("bhyt_thang", x.bhyt_thang);
      set("bhyt_nam", x.bhyt_nam);
      set("so_the_bhyt", x.so_the_bhyt);
      set("dien_thoai_bao_tin", x.dien_thoai_bao_tin);
      set("gio_kham", x.gio_kham);
      set("phut_kham", x.phut_kham);
      set("ngay_kham", x.ngay_kham);
      set("thang_kham", x.thang_kham);
      set("nam_kham", x.nam_kham);
      set("chan_doan_gioi_thieu", x.chan_doan_gioi_thieu);
      set("qua_trinh_benh_li", x.qua_trinh_benh_li);
      set("tien_su_ban_than", x.tien_su_ban_than);
      set("tien_su_gia_dinh", x.tien_su_gia_dinh);
      set("kham_toan_than", x.kham_toan_than);
      set("mach", x.mach);
      set("nhiet_do", x.nhiet_do);
      set("huyet_ap_tam_thu", x.huyet_ap_tam_thu);
      set("huyet_ap_tam_truong", x.huyet_ap_tam_truong);
      set("nhip_tho", x.nhip_tho);
      set("kham_cac_bo_phan", x.kham_cac_bo_phan);
      set("tom_tat_lam_sang", x.tom_tat_lam_sang);
      set("chan_doan_vao_vien", x.chan_doan_vao_vien);
      set("da_xu_li", x.da_xu_li);
      set("khoa_dieu_tri", x.khoa_dieu_tri);
      set("chu_y", x.chu_y);
      set("ngay_ky", x.ngay_ky);
      set("thang_ky", x.thang_ky);
      set("nam_ky", x.nam_ky);
      set("ten_bac_si", x.ten_bac_si);
      window._lastExamFormId = x.id;
      // Set the hidden input value
      var examIdInput = document.getElementById('id_phieu_kham_benh');
      if (examIdInput) {
        examIdInput.value = x.id;
      }

      // Set examination ID for both X-Ray and Ultrasound
      var examId = getCurrentExaminationId();
      var xrayExamId = document.getElementById("xray_examination_id");
      var ultrasoundExamId = document.getElementById(
        "ultrasound_examination_id"
      );

      if (xrayExamId && examId) xrayExamId.value = examId;
      if (ultrasoundExamId && examId) ultrasoundExamId.value = examId;

      // Load dữ liệu Siêu âm đã lưu (nếu có)
      loadUltrasoundFormData(x.id);

      // Load dữ liệu Xét nghiệm đã lưu (nếu có) ngay khi mở modal
      // (trước đây chỉ load khi click vào tab Xét nghiệm)
      if (x.id) {
        loadLabFormData(x.id);
      }

      // Khởi tạo tab Kê biên lai ngay khi mở modal (nếu có hàm)
      if (typeof initializeReceiptForm === 'function') {
        try { initializeReceiptForm(); } catch (e) { console.error('Init receipt error:', e); }
      }

      // Khởi tạo tab Kê đơn thuốc ngay khi mở modal (nếu có manager)
      try {
        if (window.prescriptionManager && typeof window.prescriptionManager.initializeForm === 'function') {
          window.prescriptionManager.initializeForm();
          // Thử load đơn đã lưu khi examId sẵn sàng, tránh phải bấm thêm lần nữa
          if (typeof window.prescriptionManager.tryLoadSavedPrescription === 'function') {
            let tries = 0;
            const maxTries = 15; // ~3s tổng cộng
            const tryLoad = function() {
              const examIdEl = document.getElementById('id_phieu_kham_benh');
              const examIdVal = examIdEl && examIdEl.value ? examIdEl.value : (window._lastExamFormId || window._currentExaminationId || '');
              if (examIdVal) {
                // Reset theo examId: nếu là examId khác thì cho phép load lại
                const lastLoadedExamId = window._prescriptionLoadedExamId;
                if (lastLoadedExamId !== examIdVal) {
                  window._prescriptionLoadedExamId = examIdVal;
                  try { window.prescriptionManager.tryLoadSavedPrescription(); } catch(_) {}
                }
                return;
              }
              tries++;
              if (tries < maxTries) {
                setTimeout(tryLoad, 200);
              }
            };
            // chạy lần đầu
            setTimeout(tryLoad, 100);
          }
        }
      } catch (e) { console.error('Init prescription error:', e); }
    })
    .catch(function () {});

  // Load X-Ray form data when modal opens
  loadXrayFormOnModalOpen();
  
  // Check if there's actual X-Ray result to show the result tab
  console.log("About to call checkXrayResultExists()");
  // Add delay to ensure examId is loaded
  setTimeout(function() {
    var examId = getCurrentExaminationId();
    console.log("Delayed check - examId:", examId);
    if (examId) {
      checkXrayResultExists();
    } else {
      console.log("Still no examId, will retry...");
      // Retry after longer delay
      setTimeout(function() {
        var retryExamId = getCurrentExaminationId();
        console.log("Retry check - examId:", retryExamId);
        if (retryExamId) {
          checkXrayResultExists();
        }
      }, 500);
    }
  }, 200);
}

// Check if there's X-Ray request to show the result tab (giống logic siêu âm)
function checkXrayResultExists() {
  var examId = getCurrentExaminationId();
  console.log("checkXrayResultExists called, examId:", examId);
  if (!examId) {
    console.log("No examId found, hiding X-Ray result tab");
    hideXrayResultTab();
    return;
  }

  // Check if there's any X-Ray request first
  console.log("Fetching X-Ray form data for examId:", examId);
  fetch("./get_xray_form_by_exam_id?exam_id=" + examId)
    .then(response => {
      console.log("X-Ray request response status:", response.status);
      return response.json();
    })
    .then(data => {
      console.log("X-Ray request check response:", data);
      console.log("Data success:", data.success);
      console.log("Data data:", data.data);
      if (data.success && data.data) {
        console.log("X-Ray request found, showing tab");
        // Show tab if there's any X-Ray request (giống siêu âm)
        showXrayResultTab();
        
        // Then check if there's actual result content
        checkXrayResultContent();
      } else {
        console.log("No X-Ray request found, hiding tab");
        console.log("Reason: success=" + data.success + ", data=" + (data.data ? "exists" : "null"));
        hideXrayResultTab();
      }
    })
    .catch(error => {
      console.error("Error checking X-Ray request:", error);
      hideXrayResultTab();
    });
}

// Check if there's actual X-Ray result content
function checkXrayResultContent() {
  var examId = getCurrentExaminationId();
  if (!examId) return;

  fetch("./?action=get_xray_result_by_exam&exam_id=" + examId)
    .then(response => response.json())
    .then(data => {
      console.log("X-Ray result content check response:", data);
      if (data.success && data.data) {
        // Check if result has actual content (not just request)
        // API returns data from both phieu_chup_xquang and ket_qua_xquang tables
        var result = data.data;
        var hasActualResult = result.noi_dung && result.noi_dung.trim() !== '' && 
                             result.ket_luan && result.ket_luan.trim() !== '';
        
        if (hasActualResult) {
          // Show actual result content
          showXrayResultContent();
        } else {
          // Show "Chưa có kết quả" message
          showXrayNoResultMessage();
        }
      } else {
        // Show "Chưa có kết quả" message
        showXrayNoResultMessage();
      }
    })
    .catch(error => {
      console.error("Error checking X-Ray result content:", error);
      showXrayNoResultMessage();
    });
}

// Show X-Ray result tab
function showXrayResultTab() {
  var xrayResultTab = document.getElementById("xray-result-tab");
  console.log("showXrayResultTab called, xrayResultTab element:", xrayResultTab);
  console.log("Current display style:", xrayResultTab ? xrayResultTab.style.display : "element not found");
  if (xrayResultTab) {
    xrayResultTab.style.display = 'block';
    console.log("X-Ray result tab shown, new display style:", xrayResultTab.style.display);
  } else {
    console.error("X-Ray result tab element not found!");
  }
}

// Hide X-Ray result tab
function hideXrayResultTab() {
  var xrayResultTab = document.getElementById("xray-result-tab");
  if (xrayResultTab) {
    xrayResultTab.style.display = 'none';
    console.log("X-Ray result tab hidden");
  }
}

// Show X-Ray result content (when there's actual result)
function showXrayResultContent() {
  // Hide "Chưa có kết quả" message
  var xrayResultSection = document.getElementById("sec-xray-result");
  if (xrayResultSection) {
    var cardBody = xrayResultSection.querySelector('.card-body');
    if (cardBody) {
      // Remove no-result message
      var noResultMessage = cardBody.querySelector('.xray-no-result-message');
      if (noResultMessage) {
        noResultMessage.remove();
        console.log("X-Ray no result message hidden");
      }
      
      // Show tab content
      var tabContent = cardBody.querySelector('.tab-content');
      if (tabContent) {
        tabContent.style.display = 'block';
        console.log("X-Ray tab content shown");
      }
    }
  }
  
  // Show actual result content
  var xrayResultReadonly = document.getElementById("xrayResultReadonly");
  if (xrayResultReadonly) {
    xrayResultReadonly.style.display = 'block';
    console.log("X-Ray result content shown");
  }
}

// Show "Chưa có kết quả" message (ẩn toàn bộ giao diện kết quả)
function showXrayNoResultMessage() {
  // Hide actual result content
  var xrayResultReadonly = document.getElementById("xrayResultReadonly");
  if (xrayResultReadonly) {
    xrayResultReadonly.style.display = 'none';
    console.log("X-Ray result content hidden");
  }
  
  // Hide all tab content in X-Ray result section
  var xrayResultSection = document.getElementById("sec-xray-result");
  if (xrayResultSection) {
    var cardBody = xrayResultSection.querySelector('.card-body');
    if (cardBody) {
      // Hide all existing content
      var tabContent = cardBody.querySelector('.tab-content');
      if (tabContent) {
        tabContent.style.display = 'none';
        console.log("X-Ray tab content hidden");
      }
      
      // Remove existing no-result message if any
      var existingNoResult = cardBody.querySelector('.xray-no-result-message');
      if (existingNoResult) {
        existingNoResult.remove();
      }
      
      // Create new no-result message
      var noResultDiv = document.createElement('div');
      noResultDiv.className = 'xray-no-result-message text-center p-4';
      noResultDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>Chưa có kết quả X-Quang. Vui lòng chờ bác sĩ chẩn đoán hình ảnh trả kết quả.</div>';
      cardBody.appendChild(noResultDiv);
      console.log("X-Ray no result message shown");
    }
  }
}

// Prefill X-Ray form with patient and defaults
function prefillXRaySection() {
  // Reset Giờ chỉ định khi mở modal mới (chỉ hiển thị sau khi lưu)
  var xrayGioChiDinh = document.getElementById("xray_gio_chi_dinh");
  if (xrayGioChiDinh) {
    xrayGioChiDinh.value = "";
  }

  var pName = document.getElementById("patientName")
    ? document.getElementById("patientName").value
    : "";
  var pDob = document.getElementById("patientAge")
    ? document.getElementById("patientAge").value
    : ""; // already formatted in page
  var pGender = document.getElementById("patientGender")
    ? document.getElementById("patientGender").value
    : "";
  var pAddr = document.getElementById("dia_chi")
    ? document.getElementById("dia_chi").value
    : "";
  var pCode = document.getElementById("exam_ma_benh_nhan")
    ? document.getElementById("exam_ma_benh_nhan").value
    : "";

  // Map patient info
  var xCode = document.getElementById("xray_patient_code");
  if (xCode) xCode.value = pCode;
  var xName = document.getElementById("xray_patient_name");
  if (xName) xName.value = pName;
  var xAge = document.getElementById("xray_patient_age");
  if (xAge) xAge.value = pDob;
  var xGen = document.getElementById("xray_patient_gender");
  if (xGen) xGen.value = pGender;
  var xAddr = document.getElementById("xray_patient_address");
  if (xAddr) xAddr.value = pAddr || "Gò Vấp";

  // Get patient BHYT status from database
  var examId = getCurrentExaminationId();

  if (examId) {
    // Get patient BHYT status from database
    fetch("./get_patient_bhyt_status?exam_id=" + examId)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          var hasBhyt = data.hasBhyt || false;
          var pType = hasBhyt ? "BHYT" : "Thu phí";
          var xType = document.getElementById("xray_patient_type");
          var xInsurance = document.getElementById("xray_insurance_number");

          if (xType) {
            xType.value = pType;
          }

          if (xInsurance) {
            // Get actual BHYT number from database
            xInsurance.value = hasBhyt ? data.soTheBhyt || "" : "";
          }
        } else {
          // Default to Thu phí if no data
          var xType = document.getElementById("xray_patient_type");
          if (xType) xType.value = "Thu phí";
        }
      })
      .catch((error) => {
        console.error("Error getting patient BHYT status:", error);
        var xType = document.getElementById("xray_patient_type");
        if (xType) xType.value = "Thu phí";
      });
  } else {
    var xType = document.getElementById("xray_patient_type");
    if (xType) xType.value = "Thu phí";
  }

  // Defaults for clinic
  var phone = document.getElementById("xray_phone");
  if (phone && !phone.value) phone.value = "0777871608";
  var quan = document.getElementById("xray_quan");
  if (quan && !quan.value) quan.value = "Gò Vấp";

  // Date today
  var now = new Date();
  var d = now.getDate(),
    m = now.getMonth() + 1,
    y = now.getFullYear();
  var xd = document.getElementById("xray_ngay");
  if (xd && !xd.value) xd.value = d;
  var xm = document.getElementById("xray_thang");
  if (xm && !xm.value) xm.value = m;
  var xy = document.getElementById("xray_nam");
  if (xy && !xy.value) xy.value = y;

  // Doctor name from examination form
  var doctorName = document.querySelector('[name="ten_bac_si"]')
    ? document.querySelector('[name="ten_bac_si"]').value
    : "";
  var xrayDoctor = document.getElementById("xray_doctor_name");
  var xrayDoctorDisplay = document.getElementById("xray_doctor_display");
  if (xrayDoctor && doctorName) {
    xrayDoctor.value = doctorName;
    if (xrayDoctorDisplay) xrayDoctorDisplay.textContent = doctorName;
  }

  // Prefill X-Ray diagnosis from Examination diagnosis (chan_doan_vao_vien)
  // Only set if xray diagnosis is currently empty
  var examDiagnosisEl = document.querySelector('[name="chan_doan_vao_vien"]');
  var xrayDiagnosisEl = document.getElementById("xray_diagnosis");
  if (
    xrayDiagnosisEl &&
    (!xrayDiagnosisEl.value || xrayDiagnosisEl.value.trim() === "") &&
    examDiagnosisEl &&
    examDiagnosisEl.value
  ) {
    xrayDiagnosisEl.value = examDiagnosisEl.value;
  }
}

function prefillUltrasoundSection() {
  var pName = document.getElementById("patientName")
    ? document.getElementById("patientName").value
    : "";
  var pDob = document.getElementById("patientAge")
    ? document.getElementById("patientAge").value
    : "";
  var pGender = document.getElementById("patientGender")
    ? document.getElementById("patientGender").value
    : "";
  var pAddr = document.getElementById("dia_chi")
    ? document.getElementById("dia_chi").value
    : "";
  var pCode = document.getElementById("exam_ma_benh_nhan")
    ? document.getElementById("exam_ma_benh_nhan").value
    : "";

  // Reset Giờ chỉ định khi mở modal mới (chỉ hiển thị sau khi lưu)
  var ultrasoundGioChiDinh = document.getElementById("ultrasound_gio_chi_dinh");
  if (ultrasoundGioChiDinh) {
    ultrasoundGioChiDinh.value = "";
  }

  // Map patient info
  var uCode = document.getElementById("ultrasound_patient_code");
  if (uCode) uCode.value = pCode;
  var uName = document.getElementById("ultrasound_patient_name");
  if (uName) uName.value = pName;
  var uAge = document.getElementById("ultrasound_patient_age");
  if (uAge) {
    // Tính tuổi từ ngày sinh
    if (pDob) {
      const today = new Date();
      const birthDate = new Date(pDob);
      let age = today.getFullYear() - birthDate.getFullYear();
      const monthDiff = today.getMonth() - birthDate.getMonth();
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
      }
      uAge.value = age;
    } else {
      uAge.value = '';
    }
  }
  var uGen = document.getElementById("ultrasound_patient_gender");
  if (uGen) uGen.value = pGender;
  var uAddr = document.getElementById("ultrasound_patient_address");
  if (uAddr) uAddr.value = pAddr || "Gò Vấp";

  // Get patient BHYT status from database
  var examId = getCurrentExaminationId();

  if (examId) {
    // Get patient BHYT status from database
    fetch("./get_patient_bhyt_status?exam_id=" + examId)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          var hasBhyt = data.hasBhyt || false;
          var pType = hasBhyt ? "BHYT" : "Thu phí";
          var uType = document.getElementById("ultrasound_patient_type");
          var uInsurance = document.getElementById(
            "ultrasound_insurance_number"
          );

          if (uType) {
            uType.value = pType;
          }

          if (uInsurance) {
            // Get actual BHYT number from database
            uInsurance.value = hasBhyt ? data.soTheBhyt || "" : "";
          }
        } else {
          // Default to Thu phí if no data
          var uType = document.getElementById("ultrasound_patient_type");
          if (uType) uType.value = "Thu phí";
        }
      })
      .catch((error) => {
        console.error("Error getting patient BHYT status:", error);
        // Default to Thu phí on error
        var uType = document.getElementById("ultrasound_patient_type");
        if (uType) uType.value = "Thu phí";
      });
  } else {
    // Default to Thu phí if no exam ID
    var uType = document.getElementById("ultrasound_patient_type");
    if (uType) uType.value = "Thu phí";
  }

  // Defaults for clinic
  var phone = document.getElementById("ultrasound_phone");
  if (phone && !phone.value) phone.value = "0777871608";
  var quan = document.getElementById("ultrasound_quan");
  if (quan && !quan.value) quan.value = "Gò Vấp";

  // Date today
  var now = new Date();
  var d = now.getDate(),
    m = now.getMonth() + 1,
    y = now.getFullYear();
  var ud = document.getElementById("ultrasound_ngay");
  if (ud && !ud.value) ud.value = d;
  var um = document.getElementById("ultrasound_thang");
  if (um && !um.value) um.value = m;
  var uy = document.getElementById("ultrasound_nam");
  if (uy && !uy.value) uy.value = y;

  // Doctor name from examination form
  var doctorName = document.querySelector('[name="ten_bac_si"]')
    ? document.querySelector('[name="ten_bac_si"]').value
    : "";
  var ultrasoundDoctor = document.getElementById("ultrasound_doctor_name");
  var ultrasoundDoctorDisplay = document.getElementById(
    "ultrasound_doctor_display"
  );
  if (ultrasoundDoctor && doctorName) {
    ultrasoundDoctor.value = doctorName;
    if (ultrasoundDoctorDisplay)
      ultrasoundDoctorDisplay.textContent = doctorName;
  }

  // Prefill Ultrasound diagnosis from Examination diagnosis (chan_doan_vao_vien)
  // Only set if ultrasound diagnosis is currently empty
  var examDiagnosisEl = document.querySelector('[name="chan_doan_vao_vien"]');
  var ultrasoundDiagnosisEl = document.getElementById("ultrasound_diagnosis");
  if (
    ultrasoundDiagnosisEl &&
    (!ultrasoundDiagnosisEl.value || ultrasoundDiagnosisEl.value.trim() === "") &&
    examDiagnosisEl &&
    examDiagnosisEl.value
  ) {
    ultrasoundDiagnosisEl.value = examDiagnosisEl.value;
  }

  // Get patient type from database (check BHYT status from benh_nhan table)
  var patientType = "thu_phi"; // Default to thu_phi
  var examId = getCurrentExaminationId();

  if (examId) {
    // Get patient BHYT status from database
    fetch("./get_patient_bhyt_status?exam_id=" + examId)
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (data.success && data.hasBhyt) {
          patientType = "bhyt";
        } else {
          patientType = "thu_phi";
        }

        // Set patient type in X-Ray form
        setXrayPatientType(patientType);
      })
      .catch(function (error) {
        console.error("Error getting BHYT status:", error);
        // Default to Thu phí
        var xType = document.getElementById("xray_patient_type");
        if (xType) xType.value = "Thu phí";
      });
  } else {
    var xType = document.getElementById("xray_patient_type");
    if (xType) xType.value = "Thu phí";
  }

  function setXrayPatientType(patientType) {
    var xType = document.getElementById("xray_patient_type");
    if (xType) {
      xType.value = patientType === "bhyt" ? "BHYT" : "Thu phí";
    }
  }

  // Set examination ID in hidden input
  var examId = getCurrentExaminationId();
  var xrayExamId = document.getElementById("xray_examination_id");
  var ultrasoundExamId = document.getElementById("ultrasound_examination_id");

  if (xrayExamId && examId) xrayExamId.value = examId;
  if (ultrasoundExamId && examId) ultrasoundExamId.value = examId;

  // Check if there's already a saved X-Ray form for this examination
  if (examId) {
    checkExistingXrayForm(examId);
  }
}

// Load X-Ray form data when modal opens (called from examination modal)
function loadXrayFormOnModalOpen() {
  // Get examination ID
  var examId = getCurrentExaminationId();

  if (examId) {
    // Check if there's already a saved X-Ray form for this examination
    checkExistingXrayForm(examId);
  } else {
    clearXrayForm();
  }
}

// Check if there's already a saved X-Ray form for this examination
function checkExistingXrayForm(examId) {
  console.log("Checking for existing X-Ray form for exam ID:", examId);

  fetch("./get_xray_form_by_exam_id?exam_id=" + examId)
    .then(function (response) {
      console.log("Check existing X-Ray response status:", response.status);
      return response.json();
    })
    .then(function (data) {
      console.log("Check existing X-Ray response:", data);
      if (data.success && data.data) {
        console.log("Found existing X-Ray form, loading data...");
        console.log("X-Ray form data:", data.data);
        displayXrayFormData(data.data);
        savedXrayFormId = data.data.id;
        console.log("Set savedXrayFormId:", savedXrayFormId);

        // Show X-Ray result tab since we have a request
        console.log("X-Ray form found, showing result tab");
        showXrayResultTab();
        checkXrayResultContent();

        // Enable print button
        var printBtn = document.getElementById("printXrayForm");
        if (printBtn) {
          printBtn.disabled = false;
          console.log("Enabled print button");
        }
      } else {
        console.log("No existing X-Ray form found for exam ID:", examId);
        console.log("Response data:", data);
      }
    })
    .catch(function (error) {
      console.error("Error checking existing X-Ray form:", error);
      console.error("Error details:", error.message);
    });
}

// Initialize X-Ray suggestions from database
function initXRaySuggestions() {
  var textarea = document.getElementById("xray_request");
  var suggestions = document.getElementById("xray_suggestions");
  if (!textarea || !suggestions) {
    console.error("X-Ray elements not found!");
    return;
  }

  console.log("Initializing X-Ray suggestions...");

  // Load and show suggestions
  function loadAndShowSuggestions(keyword) {
    console.log("Loading suggestions for keyword:", keyword);

    // Try database first, fallback to hardcoded
    fetch("./get_xray_suggestions", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "keyword=" + encodeURIComponent(keyword || ""),
    })
      .then(function (response) {
        console.log("Suggestions response status:", response.status);
        return response.json();
      })
      .then(function (data) {
        console.log("Suggestions data:", data);
        if (data.success && data.data && data.data.length > 0) {
          showSuggestions(data.data, keyword);
        } else {
          console.error("No suggestions found in database");
          suggestions.style.display = "none";
        }
      })
      .catch(function (error) {
        console.error("Error loading suggestions:", error);
        suggestions.style.display = "none";
      });
  }

  function showSuggestions(suggestionsData, keyword) {
    console.log("Showing suggestions:", suggestionsData.length);

    var fullValue = textarea.value;
    var parts = fullValue.split(",");
    var currentPart = parts[parts.length - 1].trim().toLowerCase();

    console.log("Current part:", currentPart);

    if (currentPart.length < 1) {
      suggestions.style.display = "none";
      return;
    }

    var matches = suggestionsData.filter(function (suggestion) {
      return suggestion.ten_goi_y.toLowerCase().includes(currentPart);
    });

    console.log("Matches found:", matches.length);

    if (matches.length === 0) {
      suggestions.style.display = "none";
      return;
    }

    suggestions.innerHTML = "";
    matches.forEach(function (suggestion) {
      var div = document.createElement("div");
      div.className =
        "p-2 border-bottom cursor-pointer d-flex justify-content-between align-items-center";
      div.style.cursor = "pointer";

      var nameSpan = document.createElement("span");
      nameSpan.textContent = suggestion.ten_goi_y;

      div.appendChild(nameSpan);

      div.addEventListener("click", function () {
        // Thay thế phần cuối cùng bằng gợi ý được chọn
        var newParts = parts.slice(0, -1);
        newParts.push(suggestion.ten_goi_y);
        textarea.value = newParts.join(", ");
        suggestions.style.display = "none";
        textarea.focus();
      });

      div.addEventListener("mouseenter", function () {
        this.style.backgroundColor = "#f8f9fa";
      });
      div.addEventListener("mouseleave", function () {
        this.style.backgroundColor = "";
      });
      suggestions.appendChild(div);
    });

    suggestions.style.display = "block";
    console.log("Suggestions displayed");
  }

  textarea.addEventListener("input", function () {
    var fullValue = this.value;
    var parts = fullValue.split(",");
    var currentPart = parts[parts.length - 1].trim();

    if (currentPart.length < 1) {
      suggestions.style.display = "none";
      // Tính lại giá tiền khi xóa hết
      calculateXrayPrice();
      return;
    }

    // Load suggestions with keyword
    loadAndShowSuggestions(currentPart);

    // Tính lại giá tiền khi có thay đổi
    calculateXrayPrice();
  });

  textarea.addEventListener("blur", function () {
    setTimeout(function () {
      suggestions.style.display = "none";
    }, 200);
  });

  textarea.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      suggestions.style.display = "none";
    }
  });

  // Load initial suggestions
  loadAndShowSuggestions("");

  // Add event listeners for save and print buttons
  var saveBtn = document.getElementById("saveXrayForm");
  var printBtn = document.getElementById("printXrayForm");

  if (saveBtn) {
    saveBtn.addEventListener("click", saveXrayForm);
  }
  if (printBtn) {
    printBtn.addEventListener("click", printXrayForm);
  }
}

// ===== X-Ray Form Save & Print =====
var savedXrayFormId = null;

// Save X-Ray form
function saveXrayForm() {
  console.log("Saving X-Ray form...");

  // Get form data - chỉ cần id_phieu_kham_benh
  var examId = document.getElementById("xray_examination_id")
    ? document.getElementById("xray_examination_id").value
    : "";

  // Fallback if hidden input is empty
  if (!examId) examId = getCurrentExaminationId();

  // kiểm tra khi lưu phiếu chụp X-Quang
  var diagnosis = document.getElementById("xray_diagnosis");
  if (!diagnosis || !diagnosis.value.trim()) {
    alert("Vui lòng nhập Chẩn đoán trước khi lưu!");
    if (diagnosis) diagnosis.focus();
    return;
  }

  // Validation: Check if request is filled
  var request = document.getElementById("xray_request");
  if (!request || !request.value.trim()) {
    alert("Vui lòng nhập Yêu cầu chụp X-Quang!");
    if (request) request.focus();
    return;
  }

  // Validation: Check for duplicate items within the same request
  var requestValue = request.value.trim();
  var items = requestValue.split(',').map(item => item.trim()).filter(item => item.length > 0);
  var uniqueItems = [...new Set(items)];
  
  if (items.length !== uniqueItems.length) {
    alert("Yêu cầu chụp X-Quang có các mục trùng lặp! Vui lòng kiểm tra lại.");
    if (request) request.focus();
    return;
  }

  var formData = {
    id_phieu_kham_benh: examId,
    so_dien_thoai: document.getElementById("xray_phone")
      ? document.getElementById("xray_phone").value
      : "0777871608",
    quan: document.getElementById("xray_quan")
      ? document.getElementById("xray_quan").value
      : "Gò Vấp",
    yeu_cau_chup: document.getElementById("xray_request")
      ? document.getElementById("xray_request").value
      : "",
    bac_si_kham: document.getElementById("xray_doctor_name")
      ? document.getElementById("xray_doctor_name").value
      : "",
    chan_doan_vao_vien: document.getElementById("xray_diagnosis")
      ? document.getElementById("xray_diagnosis").value
      : "",
  };

  console.log("X-Ray form data:", formData);

  // Validate required fields
  if (!formData.id_phieu_kham_benh || !formData.yeu_cau_chup) {
    alert(
      "Vui lòng điền đầy đủ thông tin bắt buộc\n" +
        "Exam ID: " +
        (formData.id_phieu_kham_benh || "MISSING") +
        "\n" +
        "Request: " +
        (formData.yeu_cau_chup || "MISSING")
    );
    return;
  }

  // Show loading
  var saveBtn = document.getElementById("saveXrayForm");
  if (saveBtn) {
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
  }

  // Send to server
  fetch("./save_xray_form", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams(formData),
  })
    .then(function (response) {
      console.log("Save response status:", response.status);
      return response.json();
    })
    .then(function (data) {
      console.log("Save response:", data);
      if (data.success) {
        savedXrayFormId = data.id;
        alert(data.message + "\nID phiếu chụp X-Quang: " + data.id);

        // Enable print button
        var printBtn = document.getElementById("printXrayForm");
        if (printBtn) {
          printBtn.disabled = false;
        }

        // Load saved data to display
        loadXrayFormData(data.id);
        
        // Show X-Ray result tab since we now have a request
        console.log("X-Ray form saved, showing result tab");
        showXrayResultTab();
        checkXrayResultContent();
      } else {
        alert("Lỗi: " + (data.message || "Không thể lưu phiếu chụp X-Quang"));
      }
    })
    .catch(function (error) {
      console.error("Save error:", error);
      alert("Lỗi kết nối: " + error.message);
    })
    .finally(function () {
      // Reset button
      if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.innerHTML =
          '<i class="fas fa-save"></i> Lưu phiếu chụp X-Quang';
      }
    });
}

// Make it globally accessible
window.initXRaySuggestions = initXRaySuggestions;

function initUltrasoundSuggestions() {
  var textarea = document.getElementById("ultrasound_request");
  var suggestions = document.getElementById("ultrasound_suggestions");
  if (!textarea || !suggestions) {
    console.error("Ultrasound elements not found!");
    return;
  }

  console.log("Initializing Ultrasound suggestions...");

  // Load and show suggestions
  function loadAndShowSuggestions(keyword) {
    console.log("Loading suggestions for keyword:", keyword);

    // Try database first, fallback to hardcoded
    fetch("./get_ultrasound_suggestions", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "keyword=" + encodeURIComponent(keyword || ""),
    })
      .then(function (response) {
        console.log("Suggestions response status:", response.status);
        return response.json();
      })
      .then(function (data) {
        console.log("Suggestions data:", data);
        if (data.success && data.data && data.data.length > 0) {
          showSuggestions(data.data, keyword);
        } else {
          console.error("No suggestions found in database");
          suggestions.style.display = "none";
        }
      })
      .catch(function (error) {
        console.error("Error loading suggestions:", error);
        suggestions.style.display = "none";
      });
  }

  function showSuggestions(suggestionsData, keyword) {
    console.log("Showing suggestions:", suggestionsData);
    suggestions.innerHTML = "";

    suggestionsData.forEach(function (item) {
      var div = document.createElement("div");
      div.className = "suggestion-item p-2 border-bottom";
      div.style.cursor = "pointer";
      div.textContent = item.ten_goi_y;

      div.addEventListener("click", function () {
        appendSuggestion(item.ten_goi_y, textarea);
        suggestions.style.display = "none";
      });

      div.addEventListener("mouseenter", function () {
        this.style.backgroundColor = "#f8f9fa";
      });

      div.addEventListener("mouseleave", function () {
        this.style.backgroundColor = "";
      });

      suggestions.appendChild(div);
    });

    suggestions.style.display = suggestionsData.length > 0 ? "block" : "none";
  }

  function appendSuggestion(suggestion, textarea) {
    var currentText = textarea.value;
    var cursorPos = textarea.selectionStart;

    // Find the current part being typed (after last comma)
    var beforeCursor = currentText.substring(0, cursorPos);
    var afterCursor = currentText.substring(cursorPos);

    var lastCommaIndex = beforeCursor.lastIndexOf(",");
    var startOfCurrentPart = lastCommaIndex >= 0 ? lastCommaIndex + 1 : 0;
    
    // Skip space after comma if exists
    if (startOfCurrentPart > 0 && currentText.charAt(startOfCurrentPart) === ' ') {
      startOfCurrentPart++;
    }

    // Replace current part with suggestion
    var newText =
      currentText.substring(0, startOfCurrentPart) +
      suggestion.trim() +
      currentText.substring(cursorPos);

    textarea.value = newText;
    textarea.focus();

    // Position cursor after the suggestion
    var newCursorPos = startOfCurrentPart + suggestion.trim().length;
    textarea.setSelectionRange(newCursorPos, newCursorPos);
  }

  var timeout;
  textarea.addEventListener("input", function (e) {
    clearTimeout(timeout);
    timeout = setTimeout(function () {
      var cursorPos = textarea.selectionStart;
      var currentText = textarea.value.substring(0, cursorPos);

      // Find the current part being typed (after last comma)
      var lastCommaIndex = currentText.lastIndexOf(",");
      var currentPart =
        lastCommaIndex >= 0
          ? currentText.substring(lastCommaIndex + 1).trim()
          : currentText.trim();

      if (currentPart.length >= 1) {
        loadAndShowSuggestions(currentPart);
      } else {
        suggestions.style.display = "none";
      }
    }, 200);
  });

  // Hide suggestions when clicking outside
  document.addEventListener("click", function (e) {
    if (!textarea.contains(e.target) && !suggestions.contains(e.target)) {
      suggestions.style.display = "none";
    }
  });
}

// Make it globally accessible
window.initUltrasoundSuggestions = initUltrasoundSuggestions;

// Load X-Ray form data after saving
function loadXrayFormData(formId) {
  console.log("Loading X-Ray form data for ID:", formId);

  fetch("./get_xray_form_data?id=" + formId)
    .then(function (response) {
      console.log("Load response status:", response.status);
      return response.json();
    })
    .then(function (data) {
      console.log("Load response data:", data);
      if (data.success && data.data) {
        displayXrayFormData(data.data);
      } else {
        console.error("Failed to load X-Ray form data:", data.message);
      }
    })
    .catch(function (error) {
      console.error("Error loading X-Ray form data:", error);
    });
}

// Display loaded X-Ray form data
function displayXrayFormData(data) {
  console.log("Displaying X-Ray form data:", data);

  // Fill form fields with saved data
  if (data.yeu_cau_chup) {
    var requestField = document.getElementById("xray_request");
    if (requestField) {
      requestField.value = data.yeu_cau_chup;
    }
  }

  // Hiển thị Giờ chỉ định từ ngay_cap_nhat (format: H:i:s)
  var gioChiDinhEl = document.getElementById("xray_gio_chi_dinh");
  if (gioChiDinhEl) {
    if (data.ngay_cap_nhat) {
      var date = new Date(data.ngay_cap_nhat);
      if (!isNaN(date.getTime())) {
        var hours = String(date.getHours()).padStart(2, '0');
        var minutes = String(date.getMinutes()).padStart(2, '0');
        var seconds = String(date.getSeconds()).padStart(2, '0');
        gioChiDinhEl.value = hours + ":" + minutes + ":" + seconds;
      } else {
        gioChiDinhEl.value = "";
      }
    } else {
      gioChiDinhEl.value = "";
    }
  }

  // Fill diagnosis field
  if (data.chan_doan_vao_vien) {
    var diagnosisField = document.getElementById("xray_diagnosis");
    if (diagnosisField) {
      diagnosisField.value = data.chan_doan_vao_vien;
    }
  }

  // Ensure patient code is filled from main form
  var pCode = document.getElementById("exam_ma_benh_nhan")
    ? document.getElementById("exam_ma_benh_nhan").value
    : "";
  var xCode = document.getElementById("xray_patient_code");
  if (xCode && pCode) {
    xCode.value = pCode;
  }

  // Fill patient type and insurance from database
  var examId = getCurrentExaminationId();
  if (examId) {
    fetch("./get_patient_bhyt_status?exam_id=" + examId)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          var hasBhyt = data.hasBhyt || false;
          var pType = hasBhyt ? "BHYT" : "Thu phí";
          var xType = document.getElementById("xray_patient_type");
          var xInsurance = document.getElementById("xray_insurance_number");
          if (xType) xType.value = pType;
          if (xInsurance) {
            xInsurance.value = hasBhyt ? data.soTheBhyt || "" : "";
          }
        }
      })
      .catch((error) => {
        console.error(
          "Error getting patient BHYT status in displayXrayFormData:",
          error
        );
      });
  }

  if (data.bac_si_kham) {
    var doctorNameField = document.getElementById("xray_doctor_name");
    var doctorDisplayField = document.getElementById("xray_doctor_display");
    if (doctorNameField) {
      doctorNameField.value = data.bac_si_kham;
    }
    if (doctorDisplayField) {
      doctorDisplayField.textContent = data.bac_si_kham;
    }
  }

  // Set patient type based on payment rate
  if (data.ty_le_thanh_toan === 20) {
    var bhytRadio = document.getElementById("xray_bhyt");
    var thuPhiRadio = document.getElementById("xray_thu_phi");
    if (bhytRadio) {
      bhytRadio.checked = true;
      console.log("Set xray_bhyt to checked");
    }
    if (thuPhiRadio) {
      thuPhiRadio.checked = false;
      console.log("Set xray_thu_phi to unchecked");
    }
  } else {
    var thuPhiRadio = document.getElementById("xray_thu_phi");
    var bhytRadio = document.getElementById("xray_bhyt");
    if (thuPhiRadio) {
      thuPhiRadio.checked = true;
      console.log("Set xray_thu_phi to checked");
    }
    if (bhytRadio) {
      bhytRadio.checked = false;
      console.log("Set xray_bhyt to unchecked");
    }
  }

  console.log("X-Ray form data displayed successfully");
}

// Clear X-Ray form when switching to new patient
function clearXrayForm() {
  console.log("Clearing X-Ray form...");

  // Clear form fields
  var requestField = document.getElementById("xray_request");
  if (requestField) requestField.value = "";

  // Reset patient type to default
  var xType = document.getElementById("xray_patient_type");
  var xInsurance = document.getElementById("xray_insurance_number");
  if (xType) xType.value = "Thu phí";
  if (xInsurance) xInsurance.value = "";

  // Reset saved form ID
  savedXrayFormId = null;

  // Disable print button
  var printBtn = document.getElementById("printXrayForm");
  if (printBtn) {
    printBtn.disabled = true;
  }

  console.log("X-Ray form cleared");
}

// Print X-Ray form
function printXrayForm() {
  if (!savedXrayFormId) {
    alert("Vui lòng lưu phiếu trước khi in");
    return;
  }

  console.log("Printing X-Ray form ID:", savedXrayFormId);

  // Open print window
  var printWindow = window.open(
    "./print_xray_form?id=" + savedXrayFormId,
    "_blank"
  );
  if (!printWindow) {
    alert("Không thể mở cửa sổ in. Vui lòng kiểm tra popup blocker.");
  }
}

// Helper functions to get current IDs
function getCurrentPatientId() {
  // Try to get from various sources
  var patientId = document.getElementById("patientId")
    ? document.getElementById("patientId").value
    : "";
  if (!patientId) {
    patientId = document.querySelector('[name="benh_nhan_id"]')
      ? document.querySelector('[name="benh_nhan_id"]').value
      : "";
  }
  if (!patientId) {
    patientId = document.querySelector('input[name="id_benh_nhan"]')
      ? document.querySelector('input[name="id_benh_nhan"]').value
      : "";
  }
  if (!patientId) {
    patientId = document.querySelector("#patientId")
      ? document.querySelector("#patientId").value
      : "";
  }
  console.log("Patient ID found:", patientId);
  return patientId;
}

function getCurrentDoctorId() {
  // Try to get from various sources
  var doctorId = document.getElementById("doctorId")
    ? document.getElementById("doctorId").value
    : "";
  if (!doctorId) {
    doctorId = document.querySelector('[name="bac_si_id"]')
      ? document.querySelector('[name="bac_si_id"]').value
      : "";
  }
  if (!doctorId) {
    doctorId = document.querySelector('input[name="id_bac_si"]')
      ? document.querySelector('input[name="id_bac_si"]').value
      : "";
  }
  if (!doctorId) {
    doctorId = document.querySelector("#doctorId")
      ? document.querySelector("#doctorId").value
      : "";
  }
  console.log("Doctor ID found:", doctorId);
  return doctorId;
}

function getCurrentExaminationId() {
  console.log("Searching for examination ID...");

  // Try to get from id_phieu_kham_benh first (most reliable)
  var examId = document.getElementById("id_phieu_kham_benh")
    ? document.getElementById("id_phieu_kham_benh").value
    : "";
  console.log(
    "id_phieu_kham_benh element:",
    !!document.getElementById("id_phieu_kham_benh"),
    "value:",
    examId
  );

  if (!examId) {
    examId = document.getElementById("examinationId")
      ? document.getElementById("examinationId").value
      : "";
    console.log(
      "examinationId element:",
      !!document.getElementById("examinationId"),
      "value:",
      examId
    );
  }

  if (!examId) {
    examId = document.querySelector('[name="phieu_kham_id"]')
      ? document.querySelector('[name="phieu_kham_id"]').value
      : "";
    console.log(
      "phieu_kham_id element:",
      !!document.querySelector('[name="phieu_kham_id"]'),
      "value:",
      examId
    );
  }
  if (!examId) {
    examId = document.querySelector("#examinationId")
      ? document.querySelector("#examinationId").value
      : "";
    console.log(
      "#examinationId element:",
      !!document.querySelector("#examinationId"),
      "value:",
      examId
    );
  }
  if (!examId) {
    // Try to get from window variable (set when saving examination form)
    examId = window._lastExamFormId || "";
    console.log("window._lastExamFormId:", examId);
  }
  if (!examId) {
    // Try to get from appointment ID (convert to examination ID)
    var appointmentId = document.getElementById("examinationAppointmentId")
      ? document.getElementById("examinationAppointmentId").value
      : "";
    console.log(
      "examinationAppointmentId element:",
      !!document.getElementById("examinationAppointmentId"),
      "value:",
      appointmentId
    );
    if (appointmentId) {
      // NOTE: Appointment ID is NOT the same as Examination ID
      // We need to find the actual examination ID, not use appointment ID
      console.log(
        "WARNING: Using appointment ID as examination ID may cause foreign key errors"
      );
      console.log(
        "Appointment ID:",
        appointmentId,
        "should not be used as examination ID"
      );
      // For now, don't use appointment ID as examination ID
      // examId = appointmentId;
    }
  }

  // If still no examId, try to get from window variable set when saving examination form
  if (!examId) {
    examId = window._currentExaminationId || "";
    console.log("window._currentExaminationId:", examId);
  }
  console.log("Final Examination ID found:", examId);
  return examId;
}

// ===== Allergy history upsert & fetch =====
function fetchAllergyHistory(patientId) {
  fetch("./get_allergy_history", {
    method: "POST",
    body: formData({ patient_id: patientId }),
  })
    .then(function (r) {
      return r.ok ? r.json() : Promise.reject();
    })
    .then(function (d) {
      if (d && d.success && d.data) {
        fillAllergyForm(d.data);
      }
    })
    .catch(function () {
      /* ignore */
    });
}

function saveAllergyHistory() {
  var form = document.getElementById("examinationForm");
  if (!form) return;
  var btn = document.getElementById("btnSaveExam");
  var t = btn ? btn.innerHTML : "";
  var patientId = document.getElementById("historyPatientId")
    ? document.getElementById("historyPatientId").value
    : "";
  if (!patientId) {
    alert("Thiếu mã bệnh nhân, vui lòng mở lại từ thẻ lịch hẹn!");
    return;
  }
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang lưu...';
  }
  var payload = collectAllergyForm();
  console.log("Payload to save:", payload); // Debug log
  fetch("./save_allergy_history", {
    method: "POST",
    headers: {},
    body: formData(payload),
  })
    .then(function (r) {
      return r.json();
    })
    .then(function (d) {
      if (d && d.success) {
        alert("Lưu tiền sử dị ứng thành công!");
      } else {
        alert("Không thể lưu tiền sử dị ứng!");
        console.error("save_allergy_history failed:", d);
      }
    })
    .catch(function () {
      alert("Có lỗi khi lưu tiền sử dị ứng!");
    })
    .finally(function () {
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = t;
      }
    });
}

function fillAllergyForm(data) {
  setVal("allergy_drug", data.thuoc_hoac_di_nguyen);
  setVal("allergy_drug_times", data.so_lan_thuoc);
  setChecked("allergy_drug_no", data.khong_thuoc == 1);
  setVal("allergy_drug_note", data.ghi_chu_thuoc);

  setVal("allergy_insect", data.con_trung);
  setVal("allergy_insect_times", data.so_lan_con_trung);
  setChecked("allergy_insect_no", data.khong_con_trung == 1);
  setVal("allergy_insect_note", data.ghi_chu_con_trung);

  setVal("allergy_food", data.thuc_pham);
  setVal("allergy_food_times", data.so_lan_thuc_pham);
  setChecked("allergy_food_no", data.khong_thuc_pham == 1);
  setVal("allergy_food_note", data.ghi_chu_thuc_pham);

  setVal("allergy_other", data.tac_nhan_khac);
  setVal("allergy_other_times", data.so_lan_tac_nhan_khac);
  setChecked("allergy_other_no", data.khong_tac_nhan_khac == 1);
  setVal("allergy_other_note", data.ghi_chu_tac_nhan_khac);

  setVal("personal_history", data.tien_su_ca_nhan);
  setVal("personal_history_times", data.so_lan_tien_su_ca_nhan);
  setChecked("personal_history_no", data.khong_tien_su_ca_nhan == 1);
  setVal("personal_history_note", data.ghi_chu_tien_su_ca_nhan);

  setVal("family_history", data.tien_su_gia_dinh);
  setVal("family_history_times", data.so_lan_tien_su_gia_dinh);
  setChecked("family_history_no", data.khong_tien_su_gia_dinh == 1);
  setVal("family_history_note", data.ghi_chu_tien_su_gia_dinh);
}

function clearAllergyForm() {
  setVal("allergy_drug", "");
  setVal("allergy_drug_times", "");
  setChecked("allergy_drug_no", false);
  setVal("allergy_drug_note", "");

  setVal("allergy_insect", "");
  setVal("allergy_insect_times", "");
  setChecked("allergy_insect_no", false);
  setVal("allergy_insect_note", "");

  setVal("allergy_food", "");
  setVal("allergy_food_times", "");
  setChecked("allergy_food_no", false);
  setVal("allergy_food_note", "");

  setVal("allergy_other", "");
  setVal("allergy_other_times", "");
  setChecked("allergy_other_no", false);
  setVal("allergy_other_note", "");

  setVal("personal_history", "");
  setVal("personal_history_times", "");
  setChecked("personal_history_no", false);
  setVal("personal_history_note", "");

  setVal("family_history", "");
  setVal("family_history_times", "");
  setChecked("family_history_no", false);
  setVal("family_history_note", "");
}

function collectAllergyForm() {
  return {
    patient_id: valById("historyPatientId"),
    allergy_drug: valByName("allergy_drug"),
    allergy_drug_times: valByName("allergy_drug_times"),
    allergy_drug_no: checkedByName("allergy_drug_no"),
    allergy_drug_note: valByName("allergy_drug_note"),
    allergy_insect: valByName("allergy_insect"),
    allergy_insect_times: valByName("allergy_insect_times"),
    allergy_insect_no: checkedByName("allergy_insect_no"),
    allergy_insect_note: valByName("allergy_insect_note"),
    allergy_food: valByName("allergy_food"),
    allergy_food_times: valByName("allergy_food_times"),
    allergy_food_no: checkedByName("allergy_food_no"),
    allergy_food_note: valByName("allergy_food_note"),
    allergy_other: valByName("allergy_other"),
    allergy_other_times: valByName("allergy_other_times"),
    allergy_other_no: checkedByName("allergy_other_no"),
    allergy_other_note: valByName("allergy_other_note"),
    personal_history: valByName("personal_history"),
    personal_history_times: valByName("personal_history_times"),
    personal_history_no: checkedByName("personal_history_no"),
    personal_history_note: valByName("personal_history_note"),
    family_history: valByName("family_history"),
    family_history_times: valByName("family_history_times"),
    family_history_no: checkedByName("family_history_no"),
    family_history_note: valByName("family_history_note"),
  };
}

function formData(obj) {
  var fd = new FormData();
  Object.keys(obj).forEach(function (k) {
    fd.append(k, obj[k]);
  });
  return fd;
}
function valById(id) {
  var el = document.getElementById(id);
  return el ? el.value : "";
}
function valByName(name) {
  var el = document.querySelector('[name="' + name + '"]');
  return el ? el.value : "";
}
function setVal(name, v) {
  var el = document.querySelector('[name="' + name + '"]');
  if (el) el.value = v || "";
}
function checkedByName(name) {
  var el = document.querySelector('[name="' + name + '"]');
  return el ? (el.checked ? 1 : 0) : 0;
}
function setChecked(name, on) {
  var el = document.querySelector('[name="' + name + '"]');
  if (el) el.checked = !!on;
}

function updateAppointmentStatus(id, status, note) {
  var f = document.createElement("form");
  f.method = "POST";
  f.action = "./update_appointment_status";
  var a = document.createElement("input");
  a.type = "hidden";
  a.name = "appointment_id";
  a.value = id;
  var b = document.createElement("input");
  b.type = "hidden";
  b.name = "status";
  b.value = status;
  var c = document.createElement("input");
  c.type = "hidden";
  c.name = "note";
  c.value = note || "";
  f.appendChild(a);
  f.appendChild(b);
  f.appendChild(c);
  document.body.appendChild(f);
  f.submit();
}

function filterAppointments(filter) {
  var items = document.querySelectorAll(".appointment-item");
  items.forEach(function (it) {
    var s = it.getAttribute("data-status");
    var show =
      filter === "all" ||
      (filter === "confirmed" && s === "confirmed") ||
      (filter === "examining" && s === "examining") ||
      (filter === "completed" && s === "completed");
    it.style.display = show ? "block" : "none";
    it.classList.toggle("fade-in", show);
  });
}
function updateActiveFilter(filter) {
  var cards = document.querySelectorAll(".stats-card");
  cards.forEach(function (c) {
    c.classList.toggle("active", c.getAttribute("data-filter") === filter);
  });
  var btns = document.querySelectorAll(".filter-btn");
  btns.forEach(function (b) {
    b.classList.toggle("active", b.getAttribute("data-filter") === filter);
  });
}

// ===== Ultrasound Form Functions =====

/**
 * Lưu phiếu yêu cầu siêu âm
 */
function saveUltrasoundForm() {
  console.log("saveUltrasoundForm called");
  var examId = getCurrentExaminationId();
  console.log("Exam ID:", examId);
  if (!examId) {
    alert("Vui lòng lưu phiếu khám bệnh trước khi lưu phiếu siêu âm!");
    return;
  }

  // kiểm tra trước khi lưu phiếu siêu âm
  var diagnosis = document.getElementById("ultrasound_diagnosis");
  if (!diagnosis || !diagnosis.value.trim()) {
    alert("Vui lòng nhập Chẩn đoán trước khi lưu!");
    if (diagnosis) diagnosis.focus();
    return;
  }

  // Validation: Check if request is filled
  var request = document.getElementById("ultrasound_request");
  if (!request || !request.value.trim()) {
    alert("Vui lòng nhập Yêu cầu siêu âm!");
    if (request) request.focus();
    return;
  }

  // Validation: Check for duplicate items within the same request
  var requestValue = request.value.trim();
  var items = requestValue.split(',').map(item => item.trim()).filter(item => item.length > 0);
  var uniqueItems = [...new Set(items)];
  
  if (items.length !== uniqueItems.length) {
    alert("Yêu cầu siêu âm có các mục trùng lặp! Vui lòng kiểm tra lại.");
    if (request) request.focus();
    return;
  }

  // Thu thập dữ liệu từ form
  console.log("Collecting form data...");
  var formData = {
    exam_id: examId,
    ma_benh_nhan:
      document.getElementById("ultrasound_patient_code")?.value || "",
    ho_ten: document.getElementById("ultrasound_patient_name")?.value || "",
    gioi_tinh:
      document.getElementById("ultrasound_patient_gender")?.value || "",
    dia_chi: document.getElementById("ultrasound_patient_address")?.value || "",
    doi_tuong: document.getElementById("ultrasound_patient_type")?.value || "",
    so_the_bhyt:
      document.getElementById("ultrasound_insurance_number")?.value || "",
    phong_kham: document.getElementById("ultrasound_clinic_name")?.value || "",
    so_dien_thoai: document.getElementById("ultrasound_phone")?.value || "",
    quan_huyen: document.getElementById("ultrasound_quan")?.value || "",
    chan_doan: document.getElementById("ultrasound_diagnosis")?.value || "",
    yeu_cau: document.getElementById("ultrasound_request")?.value || "",
    bac_si_kham: document.getElementById("ultrasound_doctor_name")?.value || "",
    ngay: document.getElementById("ultrasound_ngay")?.value || "",
    thang: document.getElementById("ultrasound_thang")?.value || "",
    nam: document.getElementById("ultrasound_nam")?.value || "",
  };

  console.log("Form data:", formData);

  // Gửi dữ liệu
  fetch("./?action=save_ultrasound_form", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams(formData),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert(data.message);
        // Load lại dữ liệu đã lưu
        loadUltrasoundFormData(examId);
      } else {
        alert("Lỗi: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Lỗi kết nối");
    });
}

/**
 * In phiếu yêu cầu siêu âm
 */
function printUltrasoundForm() {
  var examId = getCurrentExaminationId();
  if (!examId) {
    alert("Vui lòng lưu phiếu khám bệnh trước khi in phiếu siêu âm!");
    return;
  }

  // Lấy ID phiếu siêu âm
  fetch("./?action=get_ultrasound_form_data&exam_id=" + examId)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.data) {
        // Mở cửa sổ in với ID phiếu siêu âm
        window.open(
          "./?action=print_ultrasound_form&id=" + data.data.id,
          "_blank"
        );
      } else {
        alert("Chưa có phiếu siêu âm để in. Vui lòng lưu phiếu trước.");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Lỗi kết nối");
    });
}

/**
 * Load dữ liệu phiếu siêu âm đã lưu
 */
function loadUltrasoundFormData(examId) {
  fetch("./?action=get_ultrasound_form_data&exam_id=" + examId)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.data) {
        displayUltrasoundFormData(data.data);
      }
    })
    .catch((error) => {
      console.error("Error loading ultrasound form data:", error);
    });
}

/**
 * Hiển thị dữ liệu phiếu siêu âm đã lưu
 */
function displayUltrasoundFormData(data) {
  console.log("Displaying Ultrasound form data:", data);

  // Fill form fields with saved data
  if (data.yeu_cau) {
    var requestField = document.getElementById("ultrasound_request");
    if (requestField) {
      requestField.value = data.yeu_cau;
    }
  }

  // Hiển thị Giờ chỉ định từ ngay_cap_nhat (format: H:i:s)
  var gioChiDinhEl = document.getElementById("ultrasound_gio_chi_dinh");
  if (gioChiDinhEl) {
    if (data.ngay_cap_nhat) {
      var date = new Date(data.ngay_cap_nhat);
      if (!isNaN(date.getTime())) {
        var hours = String(date.getHours()).padStart(2, '0');
        var minutes = String(date.getMinutes()).padStart(2, '0');
        var seconds = String(date.getSeconds()).padStart(2, '0');
        gioChiDinhEl.value = hours + ":" + minutes + ":" + seconds;
      } else {
        gioChiDinhEl.value = "";
      }
    } else {
      gioChiDinhEl.value = "";
    }
  }

  // Fill diagnosis field
  if (data.chan_doan) {
    var diagnosisField = document.getElementById("ultrasound_diagnosis");
    if (diagnosisField) {
      diagnosisField.value = data.chan_doan;
    }
  }

  // Ensure patient code is filled from main form
  var pCode = document.getElementById("exam_ma_benh_nhan")
    ? document.getElementById("exam_ma_benh_nhan").value
    : "";
  var uCode = document.getElementById("ultrasound_patient_code");
  if (uCode && pCode) {
    uCode.value = pCode;
  }

  // Fill patient type and insurance from database
  var examId = getCurrentExaminationId();
  if (examId) {
    fetch("./get_patient_bhyt_status?exam_id=" + examId)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          var hasBhyt = data.hasBhyt || false;
          var pType = hasBhyt ? "BHYT" : "Thu phí";
          var uType = document.getElementById("ultrasound_patient_type");
          var uInsurance = document.getElementById(
            "ultrasound_insurance_number"
          );
          if (uType) uType.value = pType;
          if (uInsurance) {
            uInsurance.value = hasBhyt ? data.soTheBhyt || "" : "";
          }
        }
      })
      .catch((error) => {
        console.error(
          "Error getting patient BHYT status in displayUltrasoundFormData:",
          error
        );
      });
  }
}

// ===== LAB FUNCTIONS =====

// Pre-fill lab section with patient data
function prefillLabSection() {
  var pName = document.getElementById("patientName")
    ? document.getElementById("patientName").value
    : "";
  var pDob = document.getElementById("patientAge")
    ? document.getElementById("patientAge").value
    : "";
  var pGender = document.getElementById("patientGender")
    ? document.getElementById("patientGender").value
    : "";
  var pAddr = document.getElementById("dia_chi")
    ? document.getElementById("dia_chi").value
    : "";
  var pCode = document.getElementById("exam_ma_benh_nhan")
    ? document.getElementById("exam_ma_benh_nhan").value
    : "";

  // Set examination ID
  var examId = getCurrentExaminationId();
  console.log("getCurrentExaminationId() returned:", examId);
  console.log(
    "examId type:",
    typeof examId,
    "examId length:",
    examId ? examId.length : 0
  );
  var lExamId = document.getElementById("lab_examination_id");
  if (lExamId && examId) {
    lExamId.value = examId;
    console.log("Set lab_examination_id to:", examId);
    console.log("lab_examination_id value after setting:", lExamId.value);
  } else {
    console.log(
      "Cannot set lab_examination_id - examId:",
      examId,
      "lExamId element:",
      !!lExamId
    );
  }

  // Calculate age from birth year
  var currentYear = new Date().getFullYear();
  var birthYear = parseInt(pDob);
  var calculatedAge = isNaN(birthYear) ? "" : currentYear - birthYear;

  // Map patient info
  var lCode = document.getElementById("lab_patient_code");
  if (lCode) lCode.value = pCode;
  var lName = document.getElementById("lab_patient_name");
  if (lName) lName.value = pName;
  var lAge = document.getElementById("lab_patient_age");
  if (lAge) lAge.value = calculatedAge;
  var lGen = document.getElementById("lab_patient_gender");
  if (lGen) lGen.value = pGender;
  var lAddr = document.getElementById("lab_patient_address");
  if (lAddr) lAddr.value = pAddr || "Gò Vấp";

  // Set current date
  var now = new Date();
  var d = now.getDate();
  var m = now.getMonth() + 1;
  var y = now.getFullYear();

  console.log("Setting lab date:", d, m, y);

  var ld = document.getElementById("lab_ngay");
  var lm = document.getElementById("lab_thang");
  var ly = document.getElementById("lab_nam");

  if (ld) {
    ld.value = d;
    console.log("Set lab_ngay to:", ld.value);
  }
  if (lm) {
    lm.value = m;
    console.log("Set lab_thang to:", lm.value);
  }
  if (ly) {
    ly.value = y;
    console.log("Set lab_nam to:", ly.value);
  }

  // Doctor name from examination form
  var doctorName = document.querySelector('[name="ten_bac_si"]')
    ? document.querySelector('[name="ten_bac_si"]').value
    : "";
  var labDoctor = document.getElementById("lab_doctor_name");
  var labDoctorDisplay = document.getElementById("lab_doctor_display");
  if (labDoctor && doctorName) {
    labDoctor.value = doctorName;
    if (labDoctorDisplay) labDoctorDisplay.textContent = doctorName;
  }

  // Reset Giờ chỉ định khi mở modal mới (chỉ hiển thị sau khi lưu)
  var labGioChiDinh = document.getElementById("lab_gio_chi_dinh");
  if (labGioChiDinh) {
    labGioChiDinh.value = "";
  }

  // Prefill Lab diagnosis from Examination diagnosis (chan_doan_vao_vien)
  // Only set if lab diagnosis is currently empty
  var examDiagnosisEl = document.querySelector('[name="chan_doan_vao_vien"]');
  var labDiagnosisEl = document.getElementById("lab_diagnosis");
  if (
    labDiagnosisEl &&
    (!labDiagnosisEl.value || labDiagnosisEl.value.trim() === "") &&
    examDiagnosisEl &&
    examDiagnosisEl.value
  ) {
    labDiagnosisEl.value = examDiagnosisEl.value;
  }

  // Get patient BHYT status from database
  var examId = getCurrentExaminationId();

  if (examId) {
    // Get patient BHYT status from database
    fetch("./get_patient_bhyt_status?exam_id=" + examId)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          var hasBhyt = data.hasBhyt || false;
          var pType = hasBhyt ? "BHYT" : "Thu phí";
          var lType = document.getElementById("lab_patient_type");
          var lInsurance = document.getElementById("lab_insurance_number");

          if (lType) {
            lType.value = pType;
          }

          if (lInsurance) {
            // Get actual BHYT number from database
            lInsurance.value = hasBhyt ? data.soTheBhyt || "" : "";
          }
        } else {
          // Default to Thu phí if no data
          var lType = document.getElementById("lab_patient_type");
          if (lType) lType.value = "Thu phí";
        }
      })
      .catch((error) => {
        console.error("Error getting patient BHYT status:", error);
        // Default to Thu phí on error
        var lType = document.getElementById("lab_patient_type");
        if (lType) lType.value = "Thu phí";
      });
  } else {
    // Default to Thu phí if no exam ID
    var lType = document.getElementById("lab_patient_type");
    if (lType) lType.value = "Thu phí";
  }
}

// Gợi ý xét nghiệm
function initLabSuggestions() {
  var ta = document.getElementById("lab_request");
  var box = document.getElementById("lab_suggestions");
  if (!ta || !box) return;

  var fetchSug = function (q) {
    console.log("Fetching lab suggestions for:", q);
    if (!q || q.length < 1) {
      console.log("Query too short, hiding suggestions");
      box.style.display = "none";
      return;
    }

    fetch("./?action=get_lab_suggestions", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "keyword=" + encodeURIComponent(q),
    })
      .then((r) => {
        console.log("Response status:", r.status);
        return r.json();
      })
      .then((d) => {
        console.log("Lab suggestions response:", d);
        if (d.success && d.data && d.data.length > 0) {
          console.log("Showing", d.data.length, "suggestions");

          // Filter suggestions based on current part
          var fullValue = ta.value;
          var parts = fullValue.split(",");
          var currentPart = parts[parts.length - 1].trim().toLowerCase();

          var matches = d.data.filter(function (suggestion) {
            return suggestion.ten_goi_y.toLowerCase().includes(currentPart);
          });

          if (matches.length === 0) {
            box.style.display = "none";
            return;
          }

          // Show only name, like ultrasound
          box.innerHTML = "";
          matches.forEach(function (suggestion) {
            var div = document.createElement("div");
            div.className =
              "p-2 border-bottom cursor-pointer d-flex justify-content-between align-items-center";
            div.style.cursor = "pointer";
            div.textContent = suggestion.ten_goi_y;

            div.addEventListener("click", function () {
              // Replace current part with suggestion
              var newParts = parts.slice(0, -1);
              newParts.push(suggestion.ten_goi_y);
              ta.value = newParts.join(", ");
              box.style.display = "none";
              ta.focus();
            });

            div.addEventListener("mouseenter", function () {
              this.style.backgroundColor = "#f8f9fa";
            });
            div.addEventListener("mouseleave", function () {
              this.style.backgroundColor = "";
            });

            box.appendChild(div);
          });

          box.style.display = "block";
        } else {
          console.log("No suggestions found");
          box.style.display = "none";
        }
      })
      .catch((e) => {
        console.error("Error loading lab suggestions:", e);
        box.style.display = "none";
      });
  };

  var render = function () {
    var v = ta.value;
    console.log("Lab textarea value:", v);
    if (!v) {
      console.log("Empty value, hiding suggestions");
      box.style.display = "none";
      return;
    }

    // Split by comma to get current part being typed
    var parts = v.split(",");
    var currentPart = parts[parts.length - 1].trim();
    console.log("Current part after comma:", currentPart);

    if (currentPart.length >= 1) {
      fetchSug(currentPart);
    } else {
      console.log("Current part too short, hiding suggestions");
      box.style.display = "none";
    }
  };

  ta.addEventListener("input", render);
  ta.addEventListener("focus", render);

  // Click handlers are now integrated in the suggestion creation above

  // Hide suggestions when clicking outside
  document.addEventListener("click", function (e) {
    if (!ta.contains(e.target) && !box.contains(e.target)) {
      box.style.display = "none";
    }
  });
}

// Save lab form
function saveLabForm() {
  console.log("saveLabForm called");

  // Get examination ID first
  var examIdValue = getCurrentExaminationId();
  console.log("getCurrentExaminationId() returned:", examIdValue);

  if (!examIdValue) {
    alert("Vui lòng lưu phiếu khám bệnh trước khi lưu phiếu xét nghiệm!");
    return;
  }

  // Validation: Check if diagnosis is filled
  var diagnosis = document.getElementById("lab_diagnosis");
  if (!diagnosis || !diagnosis.value.trim()) {
    alert("Vui lòng nhập Chẩn đoán trước khi lưu!");
    if (diagnosis) diagnosis.focus();
    return;
  }

  // Validation: Check if request is filled
  var request = document.getElementById("lab_request");
  if (!request || !request.value.trim()) {
    alert("Vui lòng nhập Yêu cầu xét nghiệm!");
    if (request) request.focus();
    return;
  }

  // Validation: Check for duplicate items within the same request
  var requestValue = request.value.trim();
  var items = requestValue.split(',').map(item => item.trim()).filter(item => item.length > 0);
  var uniqueItems = [...new Set(items)];
  
  if (items.length !== uniqueItems.length) {
    alert("Yêu cầu xét nghiệm có các mục trùng lặp! Vui lòng kiểm tra lại.");
    if (request) request.focus();
    return;
  }

  // Check if all required elements exist
  var examId = document.getElementById("lab_examination_id");
  var patientCode = document.getElementById("lab_patient_code");
  var patientName = document.getElementById("lab_patient_name");
  var patientAge = document.getElementById("lab_patient_age");
  var patientGender = document.getElementById("lab_patient_gender");
  var patientType = document.getElementById("lab_patient_type");
  var insuranceNumber = document.getElementById("lab_insurance_number");
  var clinicName = document.getElementById("lab_clinic_name");
  var diagnosis = document.getElementById("lab_diagnosis");
  var request = document.getElementById("lab_request");
  var doctorName = document.getElementById("lab_doctor_name");
  var day = document.getElementById("lab_ngay");
  var month = document.getElementById("lab_thang");
  var year = document.getElementById("lab_nam");

  console.log("Elements found:", {
    examId: !!examId,
    patientCode: !!patientCode,
    patientName: !!patientName,
    patientAge: !!patientAge,
    patientGender: !!patientGender,
    patientType: !!patientType,
    insuranceNumber: !!insuranceNumber,
    clinicName: !!clinicName,
    diagnosis: !!diagnosis,
    request: !!request,
    doctorName: !!doctorName,
    day: !!day,
    month: !!month,
    year: !!year,
  });

  // Create form data object like ultrasound form
  var formData = {
    exam_id: examIdValue,
    so_ho_so: patientCode ? patientCode.value : "",
    ho_ten: patientName ? patientName.value : "",
    tuoi: patientAge ? patientAge.value : "",
    gioi_tinh: patientGender ? patientGender.value : "",
    doi_tuong: patientType ? patientType.value : "",
    so_the_bhyt: insuranceNumber ? insuranceNumber.value : "",
    phong_kham: clinicName ? clinicName.value : "",
    chan_doan: diagnosis ? diagnosis.value : "",
    yeu_cau: request ? request.value : "",
    bac_si_kham: doctorName ? doctorName.value : "",
    ngay: day ? day.value : "",
    thang: month ? month.value : "",
    nam: year ? year.value : "",
  };

  console.log("Form data being sent:", {
    id_phieu_kham_benh: examId ? examId.value : "",
    so_ho_so: patientCode ? patientCode.value : "",
    ho_ten: patientName ? patientName.value : "",
    yeu_cau: request ? request.value : "",
  });

  // Debug: Check if examination ID is valid
  var examIdValue = examId ? examId.value : "";
  console.log(
    "Examination ID being sent:",
    examIdValue,
    "Type:",
    typeof examIdValue
  );
  if (!examIdValue || examIdValue === "") {
    console.error("ERROR: No examination ID found!");
    alert("Lỗi: Không tìm thấy ID phiếu khám. Vui lòng thử lại.");
    return;
  }

  // Proceed with saving (duplicate check already done above)
  saveLabFormData(formData);
}

// Separate function to handle the actual saving
function saveLabFormData(formData) {
  fetch("./?action=save_lab_form", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams(formData),
  })
    .then((response) => {
      console.log("Response status:", response.status);
      return response.json();
    })
    .then((data) => {
      console.log("Response data:", data);
      if (data.success) {
        alert(data.message);
        // Load lại dữ liệu đã lưu
        var examId = getCurrentExaminationId();
        if (examId) {
          loadLabFormData(examId);
        }
      } else {
        alert("Lỗi: " + (data.message || "Không thể lưu phiếu xét nghiệm"));
      }
    })
    .catch((error) => {
      console.error("Error saving lab form:", error);
      alert("Lỗi hệ thống khi lưu phiếu xét nghiệm: " + error.message);
    });
}

// Print lab form
function printLabForm() {
  var examId = getCurrentExaminationId();
  if (!examId) {
    alert("Vui lòng lưu phiếu khám bệnh trước khi in phiếu xét nghiệm!");
    return;
  }

  // Lấy ID phiếu xét nghiệm
  fetch("./?action=get_lab_form_data&exam_id=" + examId)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.form_data) {
        // Mở cửa sổ in với ID phiếu xét nghiệm
        window.open(
          "./?action=print_lab_form&id=" + data.form_data.id,
          "_blank"
        );
      } else {
        alert("Chưa có phiếu xét nghiệm để in. Vui lòng lưu phiếu trước.");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Lỗi kết nối");
    });
}

// Load lab form data
function loadLabFormData(examId) {
  if (!examId) return;

  fetch("./?action=get_lab_form_data&exam_id=" + examId)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.form_data) {
        displayLabFormData(data.form_data);
      }
    })
    .catch((error) => {
      console.error("Error loading lab form data:", error);
    });
}

// Display lab form data
function displayLabFormData(data) {
  document.getElementById("lab_diagnosis").value = data.chan_doan || "";
  document.getElementById("lab_request").value = data.yeu_cau || "";
  document.getElementById("lab_doctor_name").value = data.bac_si_kham || "";

  // Only set date if data exists, otherwise keep current values
  if (data.ngay) document.getElementById("lab_ngay").value = data.ngay;
  if (data.thang) document.getElementById("lab_thang").value = data.thang;
  if (data.nam) document.getElementById("lab_nam").value = data.nam;

  // Hiển thị Giờ chỉ định từ ngay_cap_nhat (format: H:i:s)
  var gioChiDinhEl = document.getElementById("lab_gio_chi_dinh");
  if (gioChiDinhEl) {
    if (data.ngay_cap_nhat) {
      // Parse timestamp và format thành H:i:s
      var date = new Date(data.ngay_cap_nhat);
      if (!isNaN(date.getTime())) {
        var hours = String(date.getHours()).padStart(2, '0');
        var minutes = String(date.getMinutes()).padStart(2, '0');
        var seconds = String(date.getSeconds()).padStart(2, '0');
        gioChiDinhEl.value = hours + ":" + minutes + ":" + seconds;
      } else {
        gioChiDinhEl.value = "";
      }
    } else {
      gioChiDinhEl.value = "";
    }
  }

  // Update doctor display
  var doctorDisplay = document.getElementById("lab_doctor_display");
  if (doctorDisplay && data.bac_si_kham) {
    doctorDisplay.textContent = data.bac_si_kham;
  }

  // Re-fetch BHYT status to ensure correct patient type and insurance number
  fetch("./?action=get_patient_bhyt_status&patient_id=" + getCurrentPatientId())
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        var hasBhyt = data.hasBhyt;
        var pType = hasBhyt ? "BHYT" : "Thu phí";
        var lType = document.getElementById("lab_patient_type");
        var lInsurance = document.getElementById("lab_insurance_number");
        if (lType) lType.value = pType;
        if (lInsurance) {
          lInsurance.value = hasBhyt ? data.soTheBhyt || "" : "";
        }
      }
    })
    .catch((error) => {
      console.error(
        "Error getting patient BHYT status in displayLabFormData:",
        error
      );
    });
}

// Load lab result readonly
function loadLabResultReadonly() {
  var examId = getCurrentExaminationId();
  if (!examId) return;

  fetch("./?action=get_lab_result_by_exam&exam_id=" + examId)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.result) {
        showLabResultData(data.result, data.testDetails || []);
      } else {
        showLabResultEmpty();
      }
    })
    .catch((error) => {
      console.error("Error loading lab result:", error);
      showLabResultEmpty();
    });
}

/**
 * Load dữ liệu kết quả xét nghiệm
 */
function loadLabResultData(examId) {
  fetch("./?action=get_lab_result_by_exam&exam_id=" + examId)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.result) {
        showLabResultData(data.result, data.testDetails || []);
      } else {
        showLabResultEmpty();
      }
    })
    .catch((error) => {
      console.error("Error loading lab result data:", error);
      showLabResultEmpty();
    });
}

/**
 * Hiển thị dữ liệu kết quả xét nghiệm
 */
function showLabResultData(result, testDetails) {
  // Hide empty message
  var emptyDiv = document.getElementById("labResultEmpty");
  if (emptyDiv) emptyDiv.style.display = "none";

  // Show result div
  var resultDiv = document.getElementById("labResultReadonly");
  if (resultDiv) resultDiv.style.display = "block";

  // Fill patient info
  var idEl = document.getElementById("lab_ro_id");
  if (idEl) idEl.textContent = result.ma_benh_nhan || "-";

  var hoTenEl = document.getElementById("lab_ro_ho_ten");
  if (hoTenEl) hoTenEl.textContent = result.ho_ten || "-";

  var tuoiEl = document.getElementById("lab_ro_tuoi");
  if (tuoiEl) tuoiEl.textContent = result.tuoi || "-";

  var gioiTinhEl = document.getElementById("lab_ro_gioi_tinh");
  if (gioiTinhEl) gioiTinhEl.textContent = result.gioi_tinh || "-";

  var diaChiEl = document.getElementById("lab_ro_dia_chi");
  if (diaChiEl) diaChiEl.textContent = result.dia_chi || "-";

  var chanDoanEl = document.getElementById("lab_ro_chan_doan");
  if (chanDoanEl) chanDoanEl.textContent = result.chan_doan || "-";

  var bacSiEl = document.getElementById("lab_ro_bac_si");
  if (bacSiEl) bacSiEl.textContent = result.bac_si_yeu_cau || "-";

  var tinhTrangMauEl = document.getElementById("lab_ro_tinh_trang_mau");
  if (tinhTrangMauEl) tinhTrangMauEl.textContent = result.tinh_trang_mau || "-";

  var viTriLayMauEl = document.getElementById("lab_ro_vi_tri_lay_mau");
  if (viTriLayMauEl) viTriLayMauEl.textContent = result.vi_tri_lay_mau || "-";

  var yeuCauEl = document.getElementById("lab_ro_yeu_cau");
  if (yeuCauEl)
    yeuCauEl.textContent = result.yeu_cau || "CHƯA CÓ YÊU CẦU XÉT NGHIỆM";

  // Date and time
  if (result.ngay_tao) {
    var date = new Date(result.ngay_tao);
    var day = String(date.getDate()).padStart(2, "0");
    var month = String(date.getMonth() + 1).padStart(2, "0");
    var year = date.getFullYear();
    var hours = String(date.getHours()).padStart(2, "0");
    var minutes = String(date.getMinutes()).padStart(2, "0");

    var dateEl = document.getElementById("lab_ro_date");
    if (dateEl) dateEl.textContent = day + "/" + month + "/" + year;

    var timeEl = document.getElementById("lab_ro_time");
    if (timeEl) timeEl.textContent = hours + ":" + minutes;
  }

  // Fill test results table
  var tbody = document.getElementById("lab_ro_results_table");
  if (tbody && testDetails && testDetails.length > 0) {
    tbody.innerHTML = "";
    
    // Determine form type from yeu_cau
    var yeuCauLower = (result.yeu_cau || "").toLowerCase();
    var isMauToanPhan = yeuCauLower.indexOf("máu toàn phần") !== -1 || 
                        yeuCauLower.indexOf("cong thuc mau") !== -1 || 
                        yeuCauLower.indexOf("công thức máu") !== -1;
    var isMauNuocTieu = !isMauToanPhan && (
                        yeuCauLower.indexOf("máu") !== -1 || 
                        yeuCauLower.indexOf("nước tiểu") !== -1 || 
                        yeuCauLower.indexOf("nuoc tieu") !== -1);
    
    // Add header rows for "mau_toan_phan" form
    if (isMauToanPhan) {
      var headerRow1 = document.createElement("tr");
      headerRow1.className = "fw-bold";
      headerRow1.style.backgroundColor = "#f8f9fa";
      headerRow1.innerHTML = 
        "<td class=\"fw-bold text-start\" colspan=\"6\" style=\"text-align: left;\">" +
          "<span>XN Huyết học</span>" +
        "</td>";
      tbody.appendChild(headerRow1);
      
      var headerRow2 = document.createElement("tr");
      headerRow2.className = "fw-bold";
      headerRow2.style.backgroundColor = "#f8f9fa";
      headerRow2.innerHTML = 
        "<td class=\"fw-bold text-start\" colspan=\"6\" style=\"text-align: left;\">" +
          "<span>TPT tế bào máu(máy đếm larser)</span>" +
        "</td>";
      tbody.appendChild(headerRow2);
    }
    
    // Add header row for "mau_nuoc_tieu" form
    if (isMauNuocTieu) {
      var headerRow = document.createElement("tr");
      headerRow.className = "fw-bold";
      headerRow.style.backgroundColor = "#f8f9fa";
      headerRow.innerHTML = 
        "<td class=\"fw-bold text-start\" colspan=\"6\" style=\"text-align: left;\">" +
          "<span>Sinh Hóa</span>" +
        "</td>";
      tbody.appendChild(headerRow);
    }
    
    testDetails.forEach(function (test, index) {
      var row = document.createElement("tr");
      
      // Check if result is out of range
      var isOutOfRange = checkIfLabResultOutOfRange(test.ten_xet_nghiem, test.ket_qua);
      // Check if result is "Dương tính" - make it bold and right-aligned
      var ketQua = (test.ket_qua || "").trim().toLowerCase();
      var isDuongTinh = ketQua.indexOf("dương tính") !== -1 || ketQua.indexOf("duong tinh") !== -1;
      var resultStyle = "";
      var resultClass = "";
      if (isDuongTinh) {
        resultStyle = "font-weight: bold; color: #dc3545; text-align: right;";
        resultClass = "fw-bold text-end";
      } else if (isOutOfRange) {
        resultStyle = "font-weight: bold; color: #dc3545; text-align: right;";
        resultClass = "text-end";
      } else {
        resultStyle = "font-weight: normal;";
        resultClass = "text-center";
      }
      
      // STT: Use test.stt from database if available, otherwise use index + 1
      // (STT already starts from 1 in database, header rows are separate)
      var stt = test.stt || (index + 1);
      
      row.innerHTML = `
        <td class="text-center">${stt}</td>
        <td>${test.ten_xet_nghiem || ""}</td>
        <td class="text-center">${test.gia_tri_tham_chieu || ""}</td>
        <td class="${resultClass}" style="${resultStyle}">${
          test.ket_qua || ""
        }</td>
        <td class="text-center">${test.don_vi || ""}</td>
        <td>${test.may_qtkt || ""}</td>
      `;
      tbody.appendChild(row);
      
      // Add header row "Miễn dịch" after STT 12 for "mau_nuoc_tieu" form
      if (isMauNuocTieu && stt == 12) {
        var mienDichHeaderRow = document.createElement("tr");
        mienDichHeaderRow.className = "fw-bold";
        mienDichHeaderRow.style.backgroundColor = "#f8f9fa";
        mienDichHeaderRow.innerHTML = 
          "<td class=\"fw-bold text-start\" colspan=\"6\" style=\"text-align: left;\">" +
            "<span>Miễn dịch</span>" +
          "</td>";
        tbody.appendChild(mienDichHeaderRow);
      }
      
      // Add header rows "Nước tiểu" and "Nước tiểu 10 thông số" after STT 14 for "mau_nuoc_tieu" form
      if (isMauNuocTieu && stt == 14) {
        // Row 1: Nước tiểu
        var nuocTieuHeaderRow1 = document.createElement("tr");
        nuocTieuHeaderRow1.className = "fw-bold";
        nuocTieuHeaderRow1.style.backgroundColor = "#f8f9fa";
        nuocTieuHeaderRow1.innerHTML = 
          "<td class=\"fw-bold text-start\" colspan=\"6\" style=\"text-align: left;\">" +
            "<span>Nước tiểu</span>" +
          "</td>";
        tbody.appendChild(nuocTieuHeaderRow1);
        
        // Row 2: Nước tiểu 10 thông số
        var nuocTieuHeaderRow2 = document.createElement("tr");
        nuocTieuHeaderRow2.className = "fw-bold";
        nuocTieuHeaderRow2.style.backgroundColor = "#f8f9fa";
        nuocTieuHeaderRow2.innerHTML = 
          "<td class=\"fw-bold text-start\" colspan=\"6\" style=\"text-align: left;\">" +
            "<span>Nước tiểu 10 thông số</span>" +
          "</td>";
        tbody.appendChild(nuocTieuHeaderRow2);
      }
    });
  } else {
    tbody.innerHTML =
      '<tr><td colspan="6" class="text-center text-muted">Chưa có kết quả xét nghiệm</td></tr>';
  }

  // Signature date
  var now = new Date();
  var signatureDateEl = document.getElementById("lab_ro_signature_date");
  if (signatureDateEl) signatureDateEl.textContent = now.getDate();

  var signatureMonthEl = document.getElementById("lab_ro_signature_month");
  if (signatureMonthEl) signatureMonthEl.textContent = now.getMonth() + 1;

  var signatureYearEl = document.getElementById("lab_ro_signature_year");
  if (signatureYearEl) signatureYearEl.textContent = now.getFullYear();

  // Doctor signature
  var signatureDoctorEl = document.getElementById("lab_ro_signature_doctor");
  if (signatureDoctorEl)
    signatureDoctorEl.textContent = result.bac_si_xet_nghiem || "-";
}

/**
 * Hiển thị thông báo chưa có kết quả
 */
function showLabResultEmpty() {
  // Hide result div
  var resultDiv = document.getElementById("labResultReadonly");
  if (resultDiv) resultDiv.style.display = "none";

  // Show empty message
  var emptyDiv = document.getElementById("labResultEmpty");
  if (emptyDiv) emptyDiv.style.display = "block";
}

/**
 * Kiểm tra kết quả xét nghiệm có vượt ngưỡng không
 */
function checkIfLabResultOutOfRange(testName, resultValue) {
  if (!testName || !resultValue) return false;
  
  // Try to parse as number
  var resultNum = parseFloat(resultValue);
  if (isNaN(resultNum)) return false;
  
  // Only use reference ranges from database
  if (window.referenceRanges && window.referenceRanges[testName]) {
    var range = window.referenceRanges[testName];
    return resultNum < range.min || resultNum > range.max;
  }
  
  // No fallback - return false if no database data
  return false;
}

/**
 * Load reference ranges from database
 */
function loadReferenceRanges() {
  fetch("./?action=get_chi_so_xet_nghiem")
    .then(response => response.json())
    .then(data => {
      if (data.success && data.chi_so) {
        window.referenceRanges = {};
        data.chi_so.forEach(function(item) {
          if (item.chi_so_tu !== null && item.chi_so_den !== null) {
            window.referenceRanges[item.ten_chi_so] = {
              min: parseFloat(item.chi_so_tu),
              max: parseFloat(item.chi_so_den)
            };
          }
        });
        console.log("Reference ranges loaded:", window.referenceRanges);
      }
    })
    .catch(function(error) {
      console.error("Error loading reference ranges:", error);
    });
}

/**
 * Hoàn thành khám bệnh
 */
function completeExamination() {
    const appointmentId = document.getElementById('examinationAppointmentId').value;
    
    if (!appointmentId) {
        alert('Không tìm thấy ID lịch hẹn!');
        return;
    }

    // Kiểm tra điều kiện trước khi hoàn thành
    checkExaminationCompletion(appointmentId);
}

/**
 * Kiểm tra điều kiện hoàn thành khám bệnh
 */
function checkExaminationCompletion(appointmentId) {
    console.log('checkExaminationCompletion called with appointmentId:', appointmentId);
    
    fetch('./?action=check_examination_completion', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            appointment_id: appointmentId
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            if (data.can_complete) {
                // Xác nhận trước khi hoàn thành
                if (confirm('Bạn có chắc chắn muốn hoàn thành khám bệnh cho bệnh nhân này?')) {
                    proceedWithCompletion(appointmentId);
                }
            } else {
                // Hiển thị thông báo về các mục cần lưu
                alert('Không thể hoàn thành khám bệnh!\n\n' + 
                      'Cần lưu các mục sau:\n' + 
                      data.missing_items.join('\n') + 
                      '\n\nVui lòng lưu tất cả các mục trên trước khi hoàn thành khám bệnh.');
            }
        } else {
            console.error('API Error:', data);
            alert('Lỗi kiểm tra điều kiện:\n' + data.message);
        }
    })
    .catch(error => {
        console.error('Network Error:', error);
        alert('Lỗi kết nối khi kiểm tra điều kiện hoàn thành:\n' + error.message);
    });
}

/**
 * Tiến hành hoàn thành khám bệnh
 */
function proceedWithCompletion(appointmentId) {
    fetch('./?action=complete_examination', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            appointment_id: appointmentId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Đã hoàn thành khám bệnh thành công!');
            // Đóng modal sau khi hoàn thành
            const modal = bootstrap.Modal.getInstance(document.getElementById('examinationModal'));
            if (modal) {
                modal.hide();
            }
            // Reload trang để cập nhật danh sách lịch hẹn
            location.reload();
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Lỗi kết nối khi hoàn thành khám bệnh');
    });
}


