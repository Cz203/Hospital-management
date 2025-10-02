// Global variables
var savedExamFormId = null;
var savedXrayFormId = null;

// Examination interactions
(function init() {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', onReady);
  } else {
    onReady();
  }

  function onReady() {
    bindDatePicker();
    bindSidebar();
    bindFilterCards();
    bindFilterButtons();
    var btnSave = document.getElementById('btnSaveExam');
    if (btnSave) btnSave.style.display = 'none';
  }

  function bindDatePicker() {
    var dp = document.getElementById('datePicker');
    if (!dp) return;
    dp.addEventListener('change', function () {
      var v = this.value; if (!v) return;
      window.location.href = './doctor_examination?date=' + v;
    });
  }

  function bindSidebar() {
    var links = document.querySelectorAll('.exam-nav');
    var secs = document.querySelectorAll('.exam-section');
    var btnSave = document.getElementById('btnSaveExam');
    if (!links.length || !secs.length) return;
    secs.forEach(function (s) { s.classList.remove('active'); });
    var def = document.querySelector('#sec-patient');
    if (def) def.classList.add('active');
    links.forEach(function (l) { l.classList.remove('active'); });
    var defL = document.querySelector('.exam-nav[href="#sec-patient"]');
    if (defL) defL.classList.add('active');
    links.forEach(function (a) {
      a.addEventListener('click', function (e) {
        e.preventDefault();
        var id = this.getAttribute('href');
        secs.forEach(function (s) { s.classList.remove('active'); });
        var t = document.querySelector(id); if (t) t.classList.add('active');
        links.forEach(function (l) { l.classList.remove('active'); });
        this.classList.add('active');
        if (btnSave) btnSave.style.display = (id === '#sec-history') ? 'inline-block' : 'none';
        
        // Hiện/ẩn nút Lưu phiếu khám và In phiếu khám chỉ ở tab "Khám bệnh"
        var btnSaveExamForm = document.getElementById('btnSaveExamForm');
        var btnPrintExamForm = document.getElementById('btnPrintExamForm');
        if (btnSaveExamForm) btnSaveExamForm.style.display = (id === '#sec-exam') ? 'inline-block' : 'none';
        if (btnPrintExamForm) btnPrintExamForm.style.display = (id === '#sec-exam') ? 'inline-block' : 'none';
        
        // Hiện/ẩn nút X-Ray chỉ ở tab "X-Quang"
        var saveXrayBtn = document.getElementById('saveXrayForm');
        var printXrayBtn = document.getElementById('printXrayForm');
        if (saveXrayBtn) saveXrayBtn.style.display = (id === '#sec-xray') ? 'inline-block' : 'none';
        if (printXrayBtn) printXrayBtn.style.display = (id === '#sec-xray') ? 'inline-block' : 'none';
        
        // Khi chuyển sang tab X-Quang, tự đổ dữ liệu bệnh nhân và mặc định
        if (id === '#sec-xray') {
          prefillXRaySection();
          initXRaySuggestions();
          
          // Load dữ liệu X-Ray đã lưu (nếu có)
          var examId = getCurrentExaminationId();
          if (examId) {
            checkExistingXrayForm(examId);
          }
        }
      });
    });
  }

  function bindFilterCards() {
    var cards = document.querySelectorAll('.stats-card');
    cards.forEach(function (c) {
      c.addEventListener('click', function () {
        var f = c.getAttribute('data-filter');
        filterAppointments(f);
        updateActiveFilter(f);
      });
    });
  }

  function bindFilterButtons() {
    var btns = document.querySelectorAll('.filter-btn');
    btns.forEach(function (b) {
      b.addEventListener('click', function () {
        var f = this.getAttribute('data-filter');
        filterAppointments(f);
        updateActiveFilter(f);
      });
    });
  }
})();

// Utility functions used by inline handlers
function goToToday() {
  var d = new Date().toISOString().split('T')[0];
  window.location.href = './doctor_examination?date=' + d;
}
function goToYesterday() {
  var d = new Date(); d.setDate(d.getDate() - 1);
  window.location.href = './doctor_examination?date=' + d.toISOString().split('T')[0];
}
function goToTomorrow() {
  var d = new Date(); d.setDate(d.getDate() + 1);
  window.location.href = './doctor_examination?date=' + d.toISOString().split('T')[0];
}
function refreshExamination() { location.reload(); }

function confirmAppointment(id) {
  if (confirm('Bạn có chắc chắn muốn xác nhận lịch hẹn này?')) {
    updateAppointmentStatus(id, 'Đã xác nhận');
  }
}
function cancelAppointment(id) {
  var reason = prompt('Nhập lý do hủy/từ chối lịch hẹn:');
  if (reason && reason.trim() !== '') updateAppointmentStatus(id, 'hủy', reason);
}

function startExamination(id) {
  if (!confirm('Bắt đầu khám bệnh cho lịch hẹn này?')) return;
  
  // Clear X-Ray form when starting new examination
  clearXrayForm();
  
  // Chuyển hướng đến trang examination với appointment_id để tự động mở modal
  var currentDate = document.getElementById('datePicker') ? document.getElementById('datePicker').value : '';
  var url = './doctor_examination?date=' + currentDate + '&start_exam=' + id;
  window.location.href = url;
}
function viewPatientDetails(id) { alert('Xem chi tiết bệnh nhân cho lịch hẹn ID: ' + id); }
function completeExamination(id) {
  if (!confirm('Hoàn thành khám bệnh cho lịch hẹn này?')) return;
  
  // Clear X-Ray form when completing examination
  clearXrayForm();
  
  var f = document.createElement('form'); f.method='POST'; f.action='./complete_examination';
  var a=document.createElement('input'); a.type='hidden'; a.name='appointment_id'; a.value=id;
  var b=document.createElement('input'); b.type='hidden'; b.name='selected_date'; b.value=(document.getElementById('datePicker')||{}).value||'';
  f.appendChild(a); f.appendChild(b); document.body.appendChild(f); f.submit();
}

function continueExamination(id){
  // Chuyển hướng đến trang examination với appointment_id để tự động mở modal
  var currentDate = document.getElementById('datePicker') ? document.getElementById('datePicker').value : '';
  var url = './doctor_examination?date=' + currentDate + '&continue_exam=' + id;
  window.location.href = url;
}

// ===== Save & Print Examination Form =====
function saveExaminationForm(){
  var form = document.getElementById('examinationForm');
  if(!form){ alert('Không tìm thấy form khám bệnh'); return; }
  var appointmentId = document.getElementById('examinationAppointmentId') ? document.getElementById('examinationAppointmentId').value : '';
  var patientId = document.getElementById('historyPatientId') ? document.getElementById('historyPatientId').value : '';
  if(!patientId){ alert('Thiếu mã bệnh nhân'); return; }
  var fd = new FormData(form);
  fd.append('patient_id', patientId);
  fd.append('appointment_id', appointmentId);
  fetch('./save_examination_form', { method:'POST', body: fd })
    .then(function(r){ return r.json(); })
    .then(function(d){ if(d&&d.success){ alert('Lưu phiếu khám thành công! Mã số: '+ d.id); window._lastExamFormId = d.id; } else { alert('Không thể lưu phiếu khám'); } })
    .catch(function(){ alert('Lỗi khi lưu phiếu khám'); });
}

function printExaminationForm(){
  var id = window._lastExamFormId;
  if(!id){ alert('Vui lòng lưu phiếu trước khi in.'); return; }
  window.open('./print_examination_form?id=' + encodeURIComponent(id), '_blank');
}

// Load existing examination form by appointment when modal opens
function loadExaminationFormIfAny(){
  var appointmentIdEl = document.getElementById('examinationAppointmentId');
  if(!appointmentIdEl || !appointmentIdEl.value){ return; }
  var fd = new FormData();
  fd.append('appointment_id', appointmentIdEl.value);
  fetch('./get_examination_form', { method: 'POST', body: fd })
    .then(function(r){ return r.json(); })
    .then(function(d){
      if(!d || !d.success || !d.data){ return; }
      var x = d.data;
      var set = function(name, val){ var el = document.querySelector('[name="'+name+'"]'); if(el!=null && val!=null){ el.value = val; } };
      set('so_y_te', x.so_y_te); set('benh_vien', x.benh_vien); set('buong_kham', x.buong_kham);
      set('ho_ten', x.ho_ten); set('ngay_sinh', x.ngay_sinh); set('thang_sinh', x.thang_sinh); set('nam_sinh', x.nam_sinh); set('tuoi', x.tuoi);
      if(x.gioi_tinh==='Nam' && document.getElementById('nam')) document.getElementById('nam').checked = true;
      if((x.gioi_tinh==='Nu'||x.gioi_tinh==='Nữ') && document.getElementById('nu')) document.getElementById('nu').checked = true;
      set('nghe_nghiep', x.nghe_nghiep); set('dan_toc', x.dan_toc); set('ngoai_kieu', x.ngoai_kieu); set('noi_lam_viec', x.noi_lam_viec); set('dia_chi', x.dia_chi);
      if(document.getElementById('bhyt')) document.getElementById('bhyt').checked = x.doi_tuong_bhyt==1;
      if(document.getElementById('thu_phi')) document.getElementById('thu_phi').checked = x.doi_tuong_thu_phi==1;
      if(document.getElementById('mien')) document.getElementById('mien').checked = x.doi_tuong_mien==1;
      if(document.getElementById('khac')) document.getElementById('khac').checked = x.doi_tuong_khac==1;
      set('bhyt_ngay', x.bhyt_ngay); set('bhyt_thang', x.bhyt_thang); set('bhyt_nam', x.bhyt_nam); set('so_the_bhyt', x.so_the_bhyt);
      set('dien_thoai_bao_tin', x.dien_thoai_bao_tin);
      set('gio_kham', x.gio_kham); set('phut_kham', x.phut_kham); set('ngay_kham', x.ngay_kham); set('thang_kham', x.thang_kham); set('nam_kham', x.nam_kham);
      set('chan_doan_gioi_thieu', x.chan_doan_gioi_thieu);
      set('qua_trinh_benh_li', x.qua_trinh_benh_li); set('tien_su_ban_than', x.tien_su_ban_than); set('tien_su_gia_dinh', x.tien_su_gia_dinh);
      set('kham_toan_than', x.kham_toan_than); set('mach', x.mach); set('nhiet_do', x.nhiet_do); set('huyet_ap_tam_thu', x.huyet_ap_tam_thu); set('huyet_ap_tam_truong', x.huyet_ap_tam_truong); set('nhip_tho', x.nhip_tho);
      set('kham_cac_bo_phan', x.kham_cac_bo_phan); set('tom_tat_lam_sang', x.tom_tat_lam_sang); set('chan_doan_vao_vien', x.chan_doan_vao_vien); set('da_xu_li', x.da_xu_li); set('khoa_dieu_tri', x.khoa_dieu_tri); set('chu_y', x.chu_y);
      set('ngay_ky', x.ngay_ky); set('thang_ky', x.thang_ky); set('nam_ky', x.nam_ky); set('ten_bac_si', x.ten_bac_si);
      window._lastExamFormId = x.id;
    })
    .catch(function(){});
    
  // Load X-Ray form data when modal opens
  loadXrayFormOnModalOpen();
}

// Prefill X-Ray form with patient and defaults
function prefillXRaySection(){
  var pName = document.getElementById('patientName') ? document.getElementById('patientName').value : '';
  var pDob = document.getElementById('patientAge') ? document.getElementById('patientAge').value : ''; // already formatted in page
  var pGender = document.getElementById('patientGender') ? document.getElementById('patientGender').value : '';
  var pAddr = document.getElementById('dia_chi') ? document.getElementById('dia_chi').value : '';
  
  // Map patient info
  var xName = document.getElementById('xray_patient_name'); if(xName) xName.value = pName;
  var xAge = document.getElementById('xray_patient_age'); if(xAge) xAge.value = pDob;
  var xGen = document.getElementById('xray_patient_gender'); if(xGen) xGen.value = pGender;
  var xAddr = document.getElementById('xray_patient_address'); if(xAddr) xAddr.value = pAddr || 'Gò Vấp';
  
  // Defaults for clinic
  var phone = document.getElementById('xray_phone'); if(phone && !phone.value) phone.value = '0777871608';
  var quan = document.getElementById('xray_quan'); if(quan && !quan.value) quan.value = 'Gò Vấp';
  
  // Date today
  var now = new Date();
  var d=now.getDate(), m=now.getMonth()+1, y=now.getFullYear();
  var xd=document.getElementById('xray_ngay'); if(xd && !xd.value) xd.value=d;
  var xm=document.getElementById('xray_thang'); if(xm && !xm.value) xm.value=m;
  var xy=document.getElementById('xray_nam'); if(xy && !xy.value) xy.value=y;
  
  // Doctor name from examination form
  var doctorName = document.querySelector('[name="ten_bac_si"]') ? document.querySelector('[name="ten_bac_si"]').value : '';
  var xrayDoctor = document.getElementById('xray_doctor_name');
  var xrayDoctorDisplay = document.getElementById('xray_doctor_display');
  if(xrayDoctor && doctorName) {
    xrayDoctor.value = doctorName;
    if(xrayDoctorDisplay) xrayDoctorDisplay.textContent = doctorName;
  }
  
  // Get diagnosis from examination form
  var diagnosis = document.querySelector('[name="chan_doan_vao_vien"]') ? document.querySelector('[name="chan_doan_vao_vien"]').value : '';
  var xrayDiagnosis = document.getElementById('xray_diagnosis');
  if(xrayDiagnosis && diagnosis) {
    xrayDiagnosis.value = diagnosis;
  }
  
  // Get patient type from database (check BHYT status from benh_nhan table)
  var patientType = 'thu_phi'; // Default to thu_phi
  var examId = getCurrentExaminationId();
  
  console.log('Getting patient BHYT status from database for exam ID:', examId);
  
  if (examId) {
    // Get patient BHYT status from database
    fetch('./get_patient_bhyt_status?exam_id=' + examId)
      .then(function(response) {
        console.log('BHYT status response status:', response.status);
        return response.json();
      })
      .then(function(data) {
        console.log('Patient type response:', data);
        console.log('Patient type - success:', data.success);
        console.log('Patient type - hasBhyt:', data.hasBhyt);
        console.log('Patient type - doi_tuong_bhyt:', data.doiTuongBhyt);
        console.log('Patient type - doi_tuong_thu_phi:', data.doiTuongThuPhi);
        console.log('Patient type - doi_tuong_mien:', data.doiTuongMien);
        console.log('Patient type - doi_tuong_khac:', data.doiTuongKhac);
        console.log('Patient type - benhNhanId:', data.benhNhanId);
        console.log('Patient type - phieuKhamId:', data.phieuKhamId);
        
        if (data.success && data.hasBhyt) {
          patientType = 'bhyt';
          console.log('Patient has BHYT from examination form - setting to BHYT');
        } else {
          patientType = 'thu_phi';
          console.log('Patient does not have BHYT from examination form - setting to Thu phí');
        }
        
        // Set patient type in X-Ray form
        setXrayPatientType(patientType);
        
        // Display patient type as text
        displayXrayPatientType(patientType);
      })
      .catch(function(error) {
        console.error('Error getting BHYT status:', error);
        // Fallback to form checkboxes
        getPatientTypeFromForm();
      });
  } else {
    console.log('No exam ID found, using form checkboxes');
    getPatientTypeFromForm();
  }
  
  function getPatientTypeFromForm() {
    // Fallback: Get patient type from examination form checkboxes
    var doiTuongBhyt = document.getElementById('bhyt');
    var doiTuongThuPhi = document.getElementById('thu_phi');
    
    console.log('Checking patient type from form - BHYT checkbox:', doiTuongBhyt ? doiTuongBhyt.checked : 'not found');
    console.log('Checking patient type from form - Thu phí checkbox:', doiTuongThuPhi ? doiTuongThuPhi.checked : 'not found');
    
    if(doiTuongBhyt && doiTuongBhyt.checked) {
      patientType = 'bhyt';
      console.log('Patient type from form: BHYT');
    } else if(doiTuongThuPhi && doiTuongThuPhi.checked) {
      patientType = 'thu_phi';
      console.log('Patient type from form: Thu phí');
    } else {
      patientType = 'thu_phi';
      console.log('Patient type from form: Default to Thu phí');
    }
    
    setXrayPatientType(patientType);
  }
  
        function setXrayPatientType(patientType) {
          console.log('Setting X-Ray form patient type to:', patientType);
          
          if(patientType === 'bhyt') {
            var bhytRadio = document.getElementById('xray_bhyt');
            var thuPhiRadio = document.getElementById('xray_thu_phi');
            if(bhytRadio) {
              bhytRadio.checked = true;
              console.log('Set X-Ray form to BHYT (checked)');
            } else {
              console.log('xray_bhyt radio button not found');
            }
            if(thuPhiRadio) {
              thuPhiRadio.checked = false;
              console.log('Set X-Ray form Thu phí to unchecked');
            } else {
              console.log('xray_thu_phi radio button not found');
            }
            updatePaymentRate();
          } else {
            var thuPhiRadio = document.getElementById('xray_thu_phi');
            var bhytRadio = document.getElementById('xray_bhyt');
            if(thuPhiRadio) {
              thuPhiRadio.checked = true;
              console.log('Set X-Ray form to Thu phí (checked)');
            } else {
              console.log('xray_thu_phi radio button not found');
            }
            if(bhytRadio) {
              bhytRadio.checked = false;
              console.log('Set X-Ray form BHYT to unchecked');
            } else {
              console.log('xray_bhyt radio button not found');
            }
            updatePaymentRate();
          }
        }
        
        function displayXrayPatientType(patientType) {
          console.log('Displaying X-Ray patient type as text:', patientType);
          
          var displayElement = document.getElementById('xray_patient_type_display');
          if(displayElement) {
            if(patientType === 'bhyt') {
              displayElement.innerHTML = '<span class="badge bg-success">BHYT (20%)</span>';
              console.log('Displayed BHYT badge');
            } else {
              displayElement.innerHTML = '<span class="badge bg-danger">Thu phí (100%)</span>';
              console.log('Displayed Thu phí badge');
            }
            
            // Recalculate price when patient type changes
            var requestField = document.getElementById('xray_request');
            if (requestField && requestField.value.trim()) {
              console.log('Recalculating price after patient type change');
              calculateXrayPrice();
            }
          } else {
            console.log('xray_patient_type_display element not found');
          }
        }
  
  
  // Set examination ID in hidden input
  var examId = getCurrentExaminationId();
  var xrayExamId = document.getElementById('xray_examination_id');
  
  if(xrayExamId && examId) xrayExamId.value = examId;
  
  console.log('Set X-Ray Exam ID:', examId);
  
  // Check if there's already a saved X-Ray form for this examination
  if (examId) {
    checkExistingXrayForm(examId);
  }
}

// Load X-Ray form data when modal opens (called from examination modal)
function loadXrayFormOnModalOpen() {
  console.log('Loading X-Ray form data on modal open...');
  
  // Get examination ID
  var examId = getCurrentExaminationId();
  console.log('Modal open - Exam ID:', examId);
  
  if (examId) {
    // Check if there's already a saved X-Ray form for this examination
    console.log('Exam ID found, checking existing X-Ray form...');
    checkExistingXrayForm(examId);
  } else {
    console.log('No examination ID found, clearing X-Ray form');
    clearXrayForm();
  }
}

// Check if there's already a saved X-Ray form for this examination
function checkExistingXrayForm(examId) {
  console.log('Checking for existing X-Ray form for exam ID:', examId);
  
  fetch('./get_xray_form_by_exam_id?exam_id=' + examId)
    .then(function(response) {
      console.log('Check existing X-Ray response status:', response.status);
      return response.json();
    })
    .then(function(data) {
      console.log('Check existing X-Ray response:', data);
      if (data.success && data.data) {
        console.log('Found existing X-Ray form, loading data...');
        console.log('X-Ray form data:', data.data);
        displayXrayFormData(data.data);
        savedXrayFormId = data.data.id;
        console.log('Set savedXrayFormId:', savedXrayFormId);
        
        // Enable print button
        var printBtn = document.getElementById('printXrayForm');
        if(printBtn) {
          printBtn.disabled = false;
          console.log('Enabled print button');
        }
      } else {
        console.log('No existing X-Ray form found for exam ID:', examId);
        console.log('Response data:', data);
      }
    })
    .catch(function(error) {
      console.error('Error checking existing X-Ray form:', error);
      console.error('Error details:', error.message);
    });
}

// Update payment rate based on patient type
function updatePaymentRate() {
  var thuPhiRadio = document.getElementById('xray_thu_phi');
  var bhytRadio = document.getElementById('xray_bhyt');
  var paymentRate = document.getElementById('xray_payment_rate');
  
  if (bhytRadio && bhytRadio.checked) {
    if(paymentRate) paymentRate.value = '20%';
    if(paymentRate) paymentRate.style.color = '#28a745';
  } else {
    if(paymentRate) paymentRate.value = '100%';
    if(paymentRate) paymentRate.style.color = '#dc3545';
  }
  
  // Recalculate price when payment rate changes
  calculateXrayPrice();
}

// Initialize X-Ray suggestions from database
function initXRaySuggestions(){
  var textarea = document.getElementById('xray_request');
  var suggestions = document.getElementById('xray_suggestions');
  if(!textarea || !suggestions) {
    console.error('X-Ray elements not found!');
    return;
  }
  
  console.log('Initializing X-Ray suggestions...');
  
  // Load and show suggestions
  function loadAndShowSuggestions(keyword) {
    console.log('Loading suggestions for keyword:', keyword);
    
    // Try database first, fallback to hardcoded
    fetch('./get_xray_suggestions', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'keyword=' + encodeURIComponent(keyword || '')
    })
    .then(function(response) { 
      console.log('Suggestions response status:', response.status);
      return response.json(); 
    })
    .then(function(data) {
      console.log('Suggestions data:', data);
      if (data.success && data.data && data.data.length > 0) {
        showSuggestions(data.data, keyword);
      } else {
        console.error('No suggestions found in database');
        suggestions.style.display = 'none';
      }
    })
    .catch(function(error) {
      console.error('Error loading suggestions:', error);
      suggestions.style.display = 'none';
    });
  }
  
  function showSuggestions(suggestionsData, keyword) {
    console.log('Showing suggestions:', suggestionsData.length);
    
    var fullValue = textarea.value;
    var parts = fullValue.split(',');
    var currentPart = parts[parts.length - 1].trim().toLowerCase();
    
    console.log('Current part:', currentPart);
    
    if(currentPart.length < 1){
      suggestions.style.display = 'none';
      return;
    }
    
    var matches = suggestionsData.filter(function(suggestion){
      return suggestion.ten_goi_y.toLowerCase().includes(currentPart);
    });
    
    console.log('Matches found:', matches.length);
    
    if(matches.length === 0){
      suggestions.style.display = 'none';
      return;
    }
    
    suggestions.innerHTML = '';
    matches.forEach(function(suggestion){
      var div = document.createElement('div');
      div.className = 'p-2 border-bottom cursor-pointer d-flex justify-content-between align-items-center';
      div.style.cursor = 'pointer';
      
      var nameSpan = document.createElement('span');
      nameSpan.textContent = suggestion.ten_goi_y;
      
      var priceSpan = document.createElement('span');
      priceSpan.className = 'badge bg-success';
      priceSpan.textContent = formatPrice(suggestion.gia_tien);
      
      div.appendChild(nameSpan);
      div.appendChild(priceSpan);
      
      div.addEventListener('click', function(){
        // Thay thế phần cuối cùng bằng gợi ý được chọn
        var newParts = parts.slice(0, -1);
        newParts.push(suggestion.ten_goi_y);
        textarea.value = newParts.join(', ');
        suggestions.style.display = 'none';
        textarea.focus();
        
        // Tính lại giá tiền
        calculateXrayPrice();
      });
      
      div.addEventListener('mouseenter', function(){
        this.style.backgroundColor = '#f8f9fa';
      });
      div.addEventListener('mouseleave', function(){
        this.style.backgroundColor = '';
      });
      suggestions.appendChild(div);
    });
    
    suggestions.style.display = 'block';
    console.log('Suggestions displayed');
  }
  
  textarea.addEventListener('input', function(){
    var fullValue = this.value;
    var parts = fullValue.split(',');
    var currentPart = parts[parts.length - 1].trim();
    
    if(currentPart.length < 1){
      suggestions.style.display = 'none';
      // Tính lại giá tiền khi xóa hết
      calculateXrayPrice();
      return;
    }
    
    // Load suggestions with keyword
    loadAndShowSuggestions(currentPart);
    
    // Tính lại giá tiền khi có thay đổi
    calculateXrayPrice();
  });
  
  textarea.addEventListener('blur', function(){
    setTimeout(function(){
      suggestions.style.display = 'none';
    }, 200);
  });
  
  textarea.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){
      suggestions.style.display = 'none';
    }
  });
  
        // Load initial suggestions
        loadAndShowSuggestions('');
        
        // Add event listeners for save and print buttons
        var saveBtn = document.getElementById('saveXrayForm');
        var printBtn = document.getElementById('printXrayForm');
        
        if(saveBtn) {
          saveBtn.addEventListener('click', saveXrayForm);
        }
        if(printBtn) {
          printBtn.addEventListener('click', printXrayForm);
        }
      }

// Calculate X-Ray price with debounce
var priceCalculationTimeout;
function calculateXrayPrice() {
  var textarea = document.getElementById('xray_request');
  if (!textarea) return;
  
  // Clear previous timeout
  if (priceCalculationTimeout) {
    clearTimeout(priceCalculationTimeout);
  }
  
  // Debounce calculation
  priceCalculationTimeout = setTimeout(function() {
    var suggestions = textarea.value;
    if (!suggestions.trim()) {
      updatePriceDisplay(0, []);
      return;
    }
    
    console.log('Calculating price for:', suggestions);
    
    // Try database calculation first
    fetch('./calculate_xray_price', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'suggestions=' + encodeURIComponent(suggestions)
    })
    .then(function(response) { 
      console.log('Response status:', response.status);
      return response.json(); 
    })
    .then(function(data) {
      console.log('Price calculation result:', data);
      if (data.success) {
        updatePriceDisplay(data.total_price, data.details);
      } else {
        console.error('Price calculation failed:', data.message);
        updatePriceDisplay(0, []);
      }
    })
    .catch(function(error) {
      console.error('Error calculating price:', error);
      updatePriceDisplay(0, []);
    });
  }, 500); // 500ms debounce
}

// Update price display
function updatePriceDisplay(totalPrice, details) {
  console.log('Updating price display - Total:', totalPrice, 'Details:', details);
  
  var originalElement = document.getElementById('xray_original_price');
  var totalElement = document.getElementById('xray_total_price');
  var detailsElement = document.getElementById('xray_price_details');
  
  console.log('Original element found:', !!originalElement);
  console.log('Total element found:', !!totalElement);
  console.log('Details element found:', !!detailsElement);
  
  // Calculate payment rate based on current patient type
  var paymentRate = getCurrentPaymentRate();
  
  // Update original price (100%)
  if (originalElement) {
    originalElement.value = formatPrice(totalPrice) + ' VNĐ';
    console.log('Updated original price:', originalElement.value);
  }
  
  // Update total price (after discount)
  if (totalElement) {
    var finalPrice = totalPrice * paymentRate;
    totalElement.value = formatPrice(finalPrice) + ' VNĐ';
    console.log('Updated total price:', totalElement.value, 'Rate:', paymentRate);
  }
  
  if (detailsElement) {
    if (details.length === 0) {
      detailsElement.innerHTML = '<div class="text-muted text-center">Chưa có yêu cầu chụp</div>';
      console.log('No details, showing empty message');
    } else {
      var html = '';
      details.forEach(function(detail) {
        html += '<div class="d-flex justify-content-between align-items-center mb-1">';
        html += '<span>' + detail.ten_goi_y + '</span>';
        html += '<span class="badge bg-success">' + formatPrice(detail.gia_tien) + ' VNĐ</span>';
        html += '</div>';
      });
      detailsElement.innerHTML = html;
      console.log('Updated details HTML:', html);
    }
  }
}


// Format price
// Get current payment rate based on patient type
function getCurrentPaymentRate() {
  // Check if we have patient type from database
  var displayElement = document.getElementById('xray_patient_type_display');
  if (displayElement) {
    var displayText = displayElement.textContent || displayElement.innerText;
    console.log('Current patient type display:', displayText);
    
    if (displayText.includes('BHYT')) {
      console.log('Patient type is BHYT - 20% payment rate');
      return 0.2; // 20% for BHYT
    } else if (displayText.includes('Thu phí')) {
      console.log('Patient type is Thu phí - 100% payment rate');
      return 1.0; // 100% for Thu phí
    }
  }
  
  // Fallback to radio buttons if display not available
  var bhytRadio = document.getElementById('xray_bhyt');
  if (bhytRadio && bhytRadio.checked) {
    console.log('Fallback: BHYT radio checked - 20% payment rate');
    return 0.2;
  }
  
  console.log('Default: Thu phí - 100% payment rate');
  return 1.0; // Default to 100% for Thu phí
}

function formatPrice(price) {
  return new Intl.NumberFormat('vi-VN').format(price);
}

// ===== X-Ray Form Save & Print =====
var savedXrayFormId = null;

// Save X-Ray form
function saveXrayForm() {
  console.log('Saving X-Ray form...');
  
  // Get form data - chỉ cần id_phieu_kham_benh
  var examId = document.getElementById('xray_examination_id') ? document.getElementById('xray_examination_id').value : '';
  
  // Fallback if hidden input is empty
  if (!examId) examId = getCurrentExaminationId();
  
  var formData = {
    id_phieu_kham_benh: examId,
    so_dien_thoai: document.getElementById('xray_phone') ? document.getElementById('xray_phone').value : '0777871608',
    quan: document.getElementById('xray_quan') ? document.getElementById('xray_quan').value : 'Gò Vấp',
    yeu_cau_chup: document.getElementById('xray_request') ? document.getElementById('xray_request').value : '',
    bac_si_kham: document.getElementById('xray_doctor_name') ? document.getElementById('xray_doctor_name').value : ''
  };
  
  console.log('X-Ray form data:', formData);
  
  // Debug each field
  console.log('Exam ID:', formData.id_phieu_kham_benh, 'Type:', typeof formData.id_phieu_kham_benh);
  console.log('Request:', formData.yeu_cau_chup, 'Type:', typeof formData.yeu_cau_chup);
  
  // Validate required fields
  if (!formData.id_phieu_kham_benh || !formData.yeu_cau_chup) {
    alert('Vui lòng điền đầy đủ thông tin bắt buộc\n' +
          'Exam ID: ' + (formData.id_phieu_kham_benh || 'MISSING') + '\n' +
          'Request: ' + (formData.yeu_cau_chup || 'MISSING'));
    return;
  }
  
  // Show loading
  var saveBtn = document.getElementById('saveXrayForm');
  if(saveBtn) {
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
  }
  
  // Send to server
  fetch('./save_xray_form', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(formData)
  })
  .then(function(response) { 
    console.log('Save response status:', response.status);
    return response.json(); 
  })
  .then(function(data) {
    console.log('Save response:', data);
    if (data.success) {
      savedXrayFormId = data.id;
      alert(data.message + '\nID phiếu chụp X-Quang: ' + data.id);
      
      // Enable print button
      var printBtn = document.getElementById('printXrayForm');
      if(printBtn) {
        printBtn.disabled = false;
      }
      
      // Load saved data to display
      loadXrayFormData(data.id);
    } else {
      alert('Lỗi: ' + (data.message || 'Không thể lưu phiếu chụp X-Quang'));
    }
  })
  .catch(function(error) {
    console.error('Save error:', error);
    alert('Lỗi kết nối: ' + error.message);
  })
  .finally(function() {
    // Reset button
    if(saveBtn) {
      saveBtn.disabled = false;
      saveBtn.innerHTML = '<i class="fas fa-save"></i> Lưu phiếu chụp X-Quang';
    }
  });
}

// Load X-Ray form data after saving
function loadXrayFormData(formId) {
  console.log('Loading X-Ray form data for ID:', formId);
  
  fetch('./get_xray_form_data?id=' + formId)
    .then(function(response) {
      console.log('Load response status:', response.status);
      return response.json();
    })
    .then(function(data) {
      console.log('Load response data:', data);
      if (data.success && data.data) {
        displayXrayFormData(data.data);
      } else {
        console.error('Failed to load X-Ray form data:', data.message);
      }
    })
    .catch(function(error) {
      console.error('Error loading X-Ray form data:', error);
    });
}

// Display loaded X-Ray form data
function displayXrayFormData(data) {
  console.log('Displaying X-Ray form data:', data);
  
  // Fill form fields with saved data
  if (data.yeu_cau_chup) {
    var requestField = document.getElementById('xray_request');
    if (requestField) {
      requestField.value = data.yeu_cau_chup;
      console.log('Set xray_request to:', data.yeu_cau_chup);
    } else {
      console.log('xray_request field not found');
    }
  }
  
  if (data.gia_goc) {
    var originalPriceField = document.getElementById('xray_original_price');
    if (originalPriceField) {
      originalPriceField.value = formatPrice(data.gia_goc) + ' VNĐ';
      console.log('Set xray_original_price to:', formatPrice(data.gia_goc) + ' VNĐ');
    } else {
      console.log('xray_original_price field not found');
    }
  }
  
  if (data.ty_le_thanh_toan) {
    var paymentRateField = document.getElementById('xray_payment_rate');
    if (paymentRateField) {
      paymentRateField.value = data.ty_le_thanh_toan + '%';
      if (data.ty_le_thanh_toan === 20) {
        paymentRateField.style.color = '#28a745';
      } else {
        paymentRateField.style.color = '#dc3545';
      }
      console.log('Set xray_payment_rate to:', data.ty_le_thanh_toan + '%');
    } else {
      console.log('xray_payment_rate field not found');
    }
  }
  
  if (data.gia_thanh_toan) {
    var totalPriceField = document.getElementById('xray_total_price');
    if (totalPriceField) {
      totalPriceField.value = formatPrice(data.gia_thanh_toan) + ' VNĐ';
      console.log('Set xray_total_price to:', formatPrice(data.gia_thanh_toan) + ' VNĐ');
    } else {
      console.log('xray_total_price field not found');
    }
  }
  
  if (data.bac_si_kham) {
    var doctorNameField = document.getElementById('xray_doctor_name');
    var doctorDisplayField = document.getElementById('xray_doctor_display');
    if (doctorNameField) {
      doctorNameField.value = data.bac_si_kham;
      console.log('Set xray_doctor_name to:', data.bac_si_kham);
    }
    if (doctorDisplayField) {
      doctorDisplayField.textContent = data.bac_si_kham;
      console.log('Set xray_doctor_display to:', data.bac_si_kham);
    }
  }
  
        // Set patient type based on payment rate
        if (data.ty_le_thanh_toan === 20) {
          var bhytRadio = document.getElementById('xray_bhyt');
          var thuPhiRadio = document.getElementById('xray_thu_phi');
          if (bhytRadio) {
            bhytRadio.checked = true;
            console.log('Set xray_bhyt to checked');
          }
          if (thuPhiRadio) {
            thuPhiRadio.checked = false;
            console.log('Set xray_thu_phi to unchecked');
          }
          // Display as text
          displayXrayPatientType('bhyt');
        } else {
          var thuPhiRadio = document.getElementById('xray_thu_phi');
          var bhytRadio = document.getElementById('xray_bhyt');
          if (thuPhiRadio) {
            thuPhiRadio.checked = true;
            console.log('Set xray_thu_phi to checked');
          }
          if (bhytRadio) {
            bhytRadio.checked = false;
            console.log('Set xray_bhyt to unchecked');
          }
          // Display as text
          displayXrayPatientType('thu_phi');
        }
  
  // Update price details
  if (data.yeu_cau_chup) {
    console.log('Calling calculateXrayPrice()');
    calculateXrayPrice();
  }
  
  console.log('X-Ray form data displayed successfully');
}

// Format price helper function
function formatPrice(price) {
  if (!price || price === 0) return '0';
  return new Intl.NumberFormat('vi-VN').format(price);
}

// Clear X-Ray form when switching to new patient
function clearXrayForm() {
  console.log('Clearing X-Ray form...');
  
  // Clear form fields
  var requestField = document.getElementById('xray_request');
  if (requestField) requestField.value = '';
  
  var originalPriceField = document.getElementById('xray_original_price');
  if (originalPriceField) originalPriceField.value = '0 VNĐ';
  
  var totalPriceField = document.getElementById('xray_total_price');
  if (totalPriceField) totalPriceField.value = '0 VNĐ';
  
  var paymentRateField = document.getElementById('xray_payment_rate');
  if (paymentRateField) paymentRateField.value = '100%';
  
  // Clear price details
  var priceDetails = document.getElementById('xray_price_details');
  if (priceDetails) {
    priceDetails.innerHTML = '<div class="text-muted text-center">Chưa có yêu cầu chụp</div>';
  }
  
  // Reset patient type to default
  var thuPhiRadio = document.getElementById('xray_thu_phi');
  var bhytRadio = document.getElementById('xray_bhyt');
  if (thuPhiRadio) thuPhiRadio.checked = true;
  if (bhytRadio) bhytRadio.checked = false;
  
  // Reset patient type display
  var displayElement = document.getElementById('xray_patient_type_display');
  if (displayElement) {
    displayElement.innerHTML = '<span class="badge bg-secondary">Chưa xác định</span>';
  }
  
  // Reset saved form ID
  savedXrayFormId = null;
  
  // Disable print button
  var printBtn = document.getElementById('printXrayForm');
  if (printBtn) {
    printBtn.disabled = true;
  }
  
  console.log('X-Ray form cleared');
}

// Print X-Ray form
function printXrayForm() {
  if (!savedXrayFormId) {
    alert('Vui lòng lưu phiếu trước khi in');
    return;
  }
  
  console.log('Printing X-Ray form ID:', savedXrayFormId);
  
  // Open print window
  var printWindow = window.open('./print_xray_form?id=' + savedXrayFormId, '_blank');
  if (!printWindow) {
    alert('Không thể mở cửa sổ in. Vui lòng kiểm tra popup blocker.');
  }
}

// Helper functions to get current IDs
function getCurrentPatientId() {
  // Try to get from various sources
  var patientId = document.getElementById('patientId') ? document.getElementById('patientId').value : '';
  if (!patientId) {
    patientId = document.querySelector('[name="benh_nhan_id"]') ? document.querySelector('[name="benh_nhan_id"]').value : '';
  }
  if (!patientId) {
    patientId = document.querySelector('input[name="id_benh_nhan"]') ? document.querySelector('input[name="id_benh_nhan"]').value : '';
  }
  if (!patientId) {
    patientId = document.querySelector('#patientId') ? document.querySelector('#patientId').value : '';
  }
  console.log('Patient ID found:', patientId);
  return patientId;
}

function getCurrentDoctorId() {
  // Try to get from various sources
  var doctorId = document.getElementById('doctorId') ? document.getElementById('doctorId').value : '';
  if (!doctorId) {
    doctorId = document.querySelector('[name="bac_si_id"]') ? document.querySelector('[name="bac_si_id"]').value : '';
  }
  if (!doctorId) {
    doctorId = document.querySelector('input[name="id_bac_si"]') ? document.querySelector('input[name="id_bac_si"]').value : '';
  }
  if (!doctorId) {
    doctorId = document.querySelector('#doctorId') ? document.querySelector('#doctorId').value : '';
  }
  console.log('Doctor ID found:', doctorId);
  return doctorId;
}

function getCurrentExaminationId() {
  console.log('Searching for examination ID...');
  
  // Try to get from various sources
  var examId = document.getElementById('examinationId') ? document.getElementById('examinationId').value : '';
  console.log('examinationId element:', !!document.getElementById('examinationId'), 'value:', examId);
  
  if (!examId) {
    examId = document.querySelector('[name="phieu_kham_id"]') ? document.querySelector('[name="phieu_kham_id"]').value : '';
    console.log('phieu_kham_id element:', !!document.querySelector('[name="phieu_kham_id"]'), 'value:', examId);
  }
  if (!examId) {
    examId = document.querySelector('input[name="id_phieu_kham_benh"]') ? document.querySelector('input[name="id_phieu_kham_benh"]').value : '';
    console.log('id_phieu_kham_benh element:', !!document.querySelector('input[name="id_phieu_kham_benh"]'), 'value:', examId);
  }
  if (!examId) {
    examId = document.querySelector('#examinationId') ? document.querySelector('#examinationId').value : '';
    console.log('#examinationId element:', !!document.querySelector('#examinationId'), 'value:', examId);
  }
  if (!examId) {
    // Try to get from window variable (set when saving examination form)
    examId = window._lastExamFormId || '';
    console.log('window._lastExamFormId:', examId);
  }
  if (!examId) {
    // Try to get from appointment ID (convert to examination ID)
    var appointmentId = document.getElementById('examinationAppointmentId') ? document.getElementById('examinationAppointmentId').value : '';
    console.log('examinationAppointmentId element:', !!document.getElementById('examinationAppointmentId'), 'value:', appointmentId);
    if (appointmentId) {
      // For now, use appointment ID as examination ID (you may need to adjust this logic)
      examId = appointmentId;
    }
  }
  console.log('Final Examination ID found:', examId);
  return examId;
}

// ===== Allergy history upsert & fetch =====
function fetchAllergyHistory(patientId){
  fetch('./get_allergy_history', { method:'POST', body: formData({ patient_id: patientId }) })
    .then(function(r){ return r.ok ? r.json() : Promise.reject(); })
    .then(function(d){ if(d&&d.success&&d.data){ fillAllergyForm(d.data); } })
    .catch(function(){ /* ignore */ });
}

function saveAllergyHistory(){
  var form = document.getElementById('examinationForm'); if (!form) return;
  var btn = document.getElementById('btnSaveExam'); var t = btn ? btn.innerHTML : '';
  var patientId = document.getElementById('historyPatientId') ? document.getElementById('historyPatientId').value : '';
  if (!patientId) { alert('Thiếu mã bệnh nhân, vui lòng mở lại từ thẻ lịch hẹn!'); return; }
  if (btn) { btn.disabled=true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang lưu...'; }
  var payload = collectAllergyForm();
  console.log('Payload to save:', payload); // Debug log
  fetch('./save_allergy_history', { method:'POST', headers: { }, body: formData(payload) })
    .then(function(r){ return r.json(); })
    .then(function(d){ if(d&&d.success){ alert('Lưu tiền sử dị ứng thành công!'); } else { alert('Không thể lưu tiền sử dị ứng!'); console.error('save_allergy_history failed:', d); } })
    .catch(function(){ alert('Có lỗi khi lưu tiền sử dị ứng!'); })
    .finally(function(){ if(btn){ btn.disabled=false; btn.innerHTML=t; } });
}

function fillAllergyForm(data){
  setVal('allergy_drug', data.thuoc_hoac_di_nguyen);
  setVal('allergy_drug_times', data.so_lan_thuoc);
  setChecked('allergy_drug_no', data.khong_thuoc==1);
  setVal('allergy_drug_note', data.ghi_chu_thuoc);

  setVal('allergy_insect', data.con_trung);
  setVal('allergy_insect_times', data.so_lan_con_trung);
  setChecked('allergy_insect_no', data.khong_con_trung==1);
  setVal('allergy_insect_note', data.ghi_chu_con_trung);

  setVal('allergy_food', data.thuc_pham);
  setVal('allergy_food_times', data.so_lan_thuc_pham);
  setChecked('allergy_food_no', data.khong_thuc_pham==1);
  setVal('allergy_food_note', data.ghi_chu_thuc_pham);

  setVal('allergy_other', data.tac_nhan_khac);
  setVal('allergy_other_times', data.so_lan_tac_nhan_khac);
  setChecked('allergy_other_no', data.khong_tac_nhan_khac==1);
  setVal('allergy_other_note', data.ghi_chu_tac_nhan_khac);

  setVal('personal_history', data.tien_su_ca_nhan);
  setVal('personal_history_times', data.so_lan_tien_su_ca_nhan);
  setChecked('personal_history_no', data.khong_tien_su_ca_nhan==1);
  setVal('personal_history_note', data.ghi_chu_tien_su_ca_nhan);

  setVal('family_history', data.tien_su_gia_dinh);
  setVal('family_history_times', data.so_lan_tien_su_gia_dinh);
  setChecked('family_history_no', data.khong_tien_su_gia_dinh==1);
  setVal('family_history_note', data.ghi_chu_tien_su_gia_dinh);
}

function clearAllergyForm(){
  setVal('allergy_drug', '');
  setVal('allergy_drug_times', '');
  setChecked('allergy_drug_no', false);
  setVal('allergy_drug_note', '');

  setVal('allergy_insect', '');
  setVal('allergy_insect_times', '');
  setChecked('allergy_insect_no', false);
  setVal('allergy_insect_note', '');

  setVal('allergy_food', '');
  setVal('allergy_food_times', '');
  setChecked('allergy_food_no', false);
  setVal('allergy_food_note', '');

  setVal('allergy_other', '');
  setVal('allergy_other_times', '');
  setChecked('allergy_other_no', false);
  setVal('allergy_other_note', '');

  setVal('personal_history', '');
  setVal('personal_history_times', '');
  setChecked('personal_history_no', false);
  setVal('personal_history_note', '');

  setVal('family_history', '');
  setVal('family_history_times', '');
  setChecked('family_history_no', false);
  setVal('family_history_note', '');
}


function collectAllergyForm(){
  return {
    patient_id: valById('historyPatientId'),
    allergy_drug: valByName('allergy_drug'),
    allergy_drug_times: valByName('allergy_drug_times'),
    allergy_drug_no: checkedByName('allergy_drug_no'),
    allergy_drug_note: valByName('allergy_drug_note'),
    allergy_insect: valByName('allergy_insect'),
    allergy_insect_times: valByName('allergy_insect_times'),
    allergy_insect_no: checkedByName('allergy_insect_no'),
    allergy_insect_note: valByName('allergy_insect_note'),
    allergy_food: valByName('allergy_food'),
    allergy_food_times: valByName('allergy_food_times'),
    allergy_food_no: checkedByName('allergy_food_no'),
    allergy_food_note: valByName('allergy_food_note'),
    allergy_other: valByName('allergy_other'),
    allergy_other_times: valByName('allergy_other_times'),
    allergy_other_no: checkedByName('allergy_other_no'),
    allergy_other_note: valByName('allergy_other_note'),
    personal_history: valByName('personal_history'),
    personal_history_times: valByName('personal_history_times'),
    personal_history_no: checkedByName('personal_history_no'),
    personal_history_note: valByName('personal_history_note'),
    family_history: valByName('family_history'),
    family_history_times: valByName('family_history_times'),
    family_history_no: checkedByName('family_history_no'),
    family_history_note: valByName('family_history_note')
  };
}

function formData(obj){ var fd=new FormData(); Object.keys(obj).forEach(function(k){ fd.append(k, obj[k]); }); return fd; }
function valById(id){ var el=document.getElementById(id); return el?el.value:''; }
function valByName(name){ var el=document.querySelector('[name="'+name+'"]'); return el?el.value:''; }
function setVal(name, v){ var el=document.querySelector('[name="'+name+'"]'); if(el) el.value = v||''; }
function checkedByName(name){ var el=document.querySelector('[name="'+name+'"]'); return el? (el.checked?1:0):0; }
function setChecked(name, on){ var el=document.querySelector('[name="'+name+'"]'); if(el) el.checked = !!on; }

function updateAppointmentStatus(id, status, note){
  var f=document.createElement('form'); f.method='POST'; f.action='./update_appointment_status';
  var a=document.createElement('input'); a.type='hidden'; a.name='appointment_id'; a.value=id;
  var b=document.createElement('input'); b.type='hidden'; b.name='status'; b.value=status;
  var c=document.createElement('input'); c.type='hidden'; c.name='note'; c.value=note||'';
  f.appendChild(a); f.appendChild(b); f.appendChild(c); document.body.appendChild(f); f.submit();
}

function filterAppointments(filter){
  var items=document.querySelectorAll('.appointment-item');
  items.forEach(function(it){
    var s=it.getAttribute('data-status');
    var show=(filter==='all')
      ||(filter==='confirmed'&&s==='confirmed')
      ||(filter==='examining'&&s==='examining')
      ||(filter==='completed'&&s==='completed');
    it.style.display=show?'block':'none';
    it.classList.toggle('fade-in',show);
  });
}
function updateActiveFilter(filter){
  var cards=document.querySelectorAll('.stats-card'); cards.forEach(function(c){ c.classList.toggle('active', c.getAttribute('data-filter')===filter); });
  var btns=document.querySelectorAll('.filter-btn'); btns.forEach(function(b){ b.classList.toggle('active', b.getAttribute('data-filter')===filter); });
}
