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
  
  // Chuyển hướng đến trang examination với appointment_id để tự động mở modal
  var currentDate = document.getElementById('datePicker') ? document.getElementById('datePicker').value : '';
  var url = './doctor_examination?date=' + currentDate + '&start_exam=' + id;
  window.location.href = url;
}
function viewPatientDetails(id) { alert('Xem chi tiết bệnh nhân cho lịch hẹn ID: ' + id); }
function completeExamination(id) {
  if (!confirm('Hoàn thành khám bệnh cho lịch hẹn này?')) return;
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

// Đã gỡ tính năng lưu kết quả khám bệnh theo yêu cầu

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
