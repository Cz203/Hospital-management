<?php
require_once 'Views/layouts/layout_helper.php';
$ctx = getCurrentUserContext();
$xrayDoctorSessionName = ($ctx['role'] === 'xray_doctor') ? ($ctx['name'] ?? '') : '';

$content = '
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-x-ray text-success me-2"></i>
                X-Ray Dashboard
            </h1>
            <p class="text-muted">Chào mừng bác sĩ X-Quang ' . htmlspecialchars(($ctx['name'] ?? ''), ENT_QUOTES, 'UTF-8') . ' - Chẩn đoán hình ảnh</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <button class="btn btn-success">
                <i class="fas fa-calendar-plus me-2"></i>Lịch chụp X-Quang
            </button>
            <button class="btn btn-primary">
                <i class="fas fa-x-ray me-2"></i>Chụp X-Quang
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Yêu cầu chụp hôm nay
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat_total_today">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-x-ray fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Đã chụp xong
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat_completed_today">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Đang chờ chụp
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat_waiting">0</div>
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
                    <h6 class="m-0 font-weight-bold text-success">Thao tác nhanh</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="text-success mb-2"><i class="fas fa-list me-2"></i>Yêu cầu chụp (Đã yêu cầu)</h5>
                        <div class="row g-2 align-items-end mb-2">
                          <div class="col-auto">
                            <label class="form-label mb-1">Ngày</label>
                            <input type="date" class="form-control" id="xray_date">
                          </div>
                          <div class="col-auto">
                            <label class="form-label mb-1">Tìm kiếm (Mã bệnh nhân)</label>
                            <input type="text" class="form-control" id="xray_name" placeholder="Nhập mã bệnh nhân...">
                          </div>
                          <div class="col-auto">
                            <button type="button" class="btn btn-outline-secondary" id="xray_filter_btn"><i class="fas fa-filter me-1"></i>Lọc</button>
                          </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="xrayRequestedTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:80px" class="text-center">ID</th>
                                        <th style="width:120px" class="text-center">Mã Bệnh Nhân</th>
                                        <th>Họ tên</th>
                                        <th style="width:80px" class="text-center">Tuổi</th>
                                        <th style="width:90px" class="text-center">Giới tính</th>
                                        <th>Yêu cầu chụp</th>
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
';

// Modal for X-Ray details (read-only, doctor-like layout)
$content .= '
<div class="modal fade" id="xrayDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title"><i class="fas fa-x-ray me-2"></i>Phiếu chụp X – Quang (Xem)</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3 text-center">
          <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:60px;object-fit:contain;margin-bottom:10px;">
          <div class="fw-bold text-center" style="font-size:22px;text-align:center;">PHIẾU CHỤP X – QUANG</div>
        </div>

        <div class="row mb-2">
          <div class="col-md-6">
            <label class="form-label fw-bold">Cơ sở y tế</label>
            <input type="text" class="form-control" id="vx_clinic_name" readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Điện thoại</label>
            <input type="text" class="form-control" id="vx_phone" readonly>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-bold">Quận</label>
            <input type="text" class="form-control" id="vx_quan" readonly>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-md-2">
            <label class="form-label fw-bold">Mã bệnh nhân</label>
            <input type="text" class="form-control" id="vx_patient_code" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Họ tên người bệnh</label>
            <input type="text" class="form-control" id="vx_patient_name" readonly>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-bold">Tuổi</label>
            <input type="text" class="form-control" id="vx_patient_age" readonly>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-bold">Nam/Nữ</label>
            <input type="text" class="form-control" id="vx_patient_gender" readonly>
          </div>
          <div class="col-12">
            <label class="form-label fw-bold">Địa chỉ</label>
            <input type="text" class="form-control" id="vx_address" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Đối tượng</label>
            <input type="text" class="form-control" id="vx_patient_type" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Số thẻ BHYT</label>
            <input type="text" class="form-control" id="vx_insurance_number" readonly>
          </div>
        </div>

        <div class="mt-3">
          <label class="form-label fw-bold">Giờ chỉ định:</label>
          <input type="text" class="form-control" id="vx_order_time" readonly>
        </div>

        <div class="mt-3">
          <label class="form-label fw-bold">Chuẩn đoán:</label>
          <input type="text" class="form-control" id="vx_diagnosis" readonly>
        </div>

        <div class="mt-3 position-relative">
          <label class="form-label fw-bold text-center w-100 d-block" style="font-size:16px">YÊU CẦU CHỤP</label>
          <textarea class="form-control" id="vx_request" rows="6" readonly></textarea>
        </div>

        <div class="row mt-4">
          <div class="col-md-6"></div>
          <div class="col-md-6 text-center">
            <div class="mb-2 d-flex align-items-center justify-content-center gap-2">
              <span>Ngày</span>
              <input type="number" class="form-control text-center" id="vx_ngay" style="width:70px" readonly>
              <span>tháng</span>
              <input type="number" class="form-control text-center" id="vx_thang" style="width:70px" readonly>
              <span>năm</span>
              <input type="number" class="form-control text-center" id="vx_nam" style="width:90px" readonly>
            </div>
            <div class="fw-bold">BÁC SĨ ĐIỀU TRỊ</div>
            <div class="mt-2" id="vx_doctor" style="min-height:40px; border-bottom: 1px solid #000; padding: 5px;"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>';

// Modal for returning X-Ray result (beautified)
$content .= '
<div class="modal fade" id="xrayReturnModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <style>
        .report{font-family:"Times New Roman",serif;padding:18px}
        .report .title{font-weight:bold;text-transform:uppercase;text-align:center;letter-spacing:.5px;font-size:18px;margin-bottom:6px}
        .report .hr{border-top:2px solid #000;margin:10px 0}
        .report .row-line{display:flex;gap:8px;margin-bottom:6px;font-size:15px}
        .report .label{min-width:150px;font-weight:bold}
        .report .dots{flex:0 0 auto}
        .report .value{flex:1;border-bottom:1px dotted #333;min-height:20px}
        .report .section{margin-top:10px;margin-bottom:6px;font-weight:bold;text-transform:uppercase}
        .report .signature{min-width:260px}
      </style>
      <div class="modal-body">
        <ul class="nav nav-tabs" id="xrayReturnTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-info-tab" data-bs-toggle="tab" data-bs-target="#tab-info" type="button" role="tab">Thông tin</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-images-tab" data-bs-toggle="tab" data-bs-target="#tab-images" type="button" role="tab">Hình Ảnh X-Quang</button>
          </li>
        </ul>
          <div class="tab-content border border-top-0 p-2">
          <div class="tab-pane fade show active" id="tab-info" role="tabpanel">
            <div class="report border border-dark">
          <div class="text-center mb-3">
            <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:60px;object-fit:contain;margin-bottom:10px;">
          </div>
          <div class="title">PHÒNG KHÁM ĐA KHOA THINHVIET<br>KHOA CHUẨN ĐOÁN HÌNH ẢNH</div>
          <div class="text-center" style="font-size:14px">Địa chỉ: Gò Vấp - Điện thoại: 0777871608</div>
          <div class="hr"></div>
          <div class="row-line"><div class="label">Họ và tên</div><div class="dots">:</div><div class="value" id="rx_name"></div></div>
          <div class="row-line">
            <div class="label">Năm sinh</div><div class="dots">:</div><div class="value" id="rx_year_of_birth"></div>
            <div class="label" style="min-width:90px">Giới tính</div><div class="dots">:</div><div class="value" id="rx_gender"></div>
          </div>
          <div class="row-line"><div class="label">Địa chỉ</div><div class="dots">:</div><div class="value" id="rx_address"></div></div>
          <div class="row-line">
            <div class="label">Khoa chỉ định</div><div class="dots">:</div><div class="value" id="rx_khoa_chi_dinh"></div>
            <div class="label" style="min-width:120px">Số phiếu chỉ định</div><div class="dots">:</div><div class="value" id="rx_id"></div>
          </div>
          <div class="row-line">
            <div class="label">Ngày chỉ định</div><div class="dots">:</div><div class="value" id="rx_date"></div>
            <div class="label" style="min-width:120px">Giờ chỉ định</div><div class="dots">:</div><div class="value" id="rx_time"></div>
          </div>
          <div class="row-line">
            <div class="label">Giờ nhận kết quả</div><div class="dots">:</div><div class="value" id="rx_return_time">-</div>
          </div>
          <div class="hr"></div>
          <div class="row-line"><div class="label">Chẩn đoán</div><div class="dots">:</div><div class="value" id="rx_chuan_doan"></div></div>
          <div class="row-line"><div class="label">Bác sĩ chỉ định</div><div class="dots">:</div><div class="value" id="rx_bac_si_chi_dinh"></div></div>
          <div class="row-line"><div class="label">Nội dung</div><div class="dots">:</div><div class="value" id="rx_noi_dung"></div></div>
          <div class="section">Kết quả</div>
          <textarea class="form-control mb-2" rows="2" id="rx_suggestion_input"></textarea>
          <div class="section">KẾT LUẬN</div>
          <textarea class="form-control" rows="2" id="rx_ket_luan_input"></textarea>
          <div class="d-flex justify-content-end mt-3">
            <div class="text-center signature">
              <div class="mb-1"><em>Ngày <span id="rx_day"></span> tháng <span id="rx_month"></span> năm <span id="rx_year"></span></em></div>
              <div class="fw-bold">Bác sĩ X Quang</div>
              <div class="mt-3" id="rx_doctor" style="min-height:40px"></div>
            </div>
          </div>
            </div>
          </div>
          <div class="tab-pane fade" id="tab-images" role="tabpanel">
            <div class="mb-2">Tải ảnh X-Quang (jpg, png), cho phép nhiều ảnh:</div>
            <input type="file" id="rx_image_input" class="form-control mb-2" accept="image/*" multiple>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-primary" id="rx_upload_btn">Tải lên</button>
              <div class="text-muted" id="rx_upload_status"></div>
            </div>
            <div class="row mt-3" id="rx_image_gallery"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary me-2" id="rx_save_btn">Lưu</button>
        <button type="button" class="btn btn-info me-2" id="rx_print_btn">In</button>
        <button type="button" class="btn btn-success me-2" id="rx_complete_btn">Hoàn thành</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>';

// Inject X-Ray doctor name from session for use in JS
$content .= "\n<script>const XRAY_DOCTOR_NAME = " . json_encode($xrayDoctorSessionName) . ";</script>\n";

// Inline script to load requested list
$content .= <<<'HTML'
<script>
document.addEventListener("DOMContentLoaded", function() {
  // Load top statistics
  function loadStats(){
    fetch('?action=get_xray_stats_today')
      .then(function(res){ return res.json(); })
      .then(function(json){
        if(!json.success || !json.data) return;
        var d = json.data;
        var el1 = document.getElementById('stat_total_today');
        var el2 = document.getElementById('stat_completed_today');
        var el3 = document.getElementById('stat_waiting');
        if(el1) el1.textContent = d.total_today ?? 0;
        if(el2) el2.textContent = d.completed_today ?? 0;
        if(el3) el3.textContent = d.waiting_count ?? 0;
      })
      .catch(function(){});
  }
  function formatToday(){
    var d = new Date();
    var m = String(d.getMonth()+1).padStart(2,'0');
    var day = String(d.getDate()).padStart(2,'0');
    return d.getFullYear() + '-' + m + '-' + day;
  }

  function loadRequested(){
    var dateInput = document.getElementById('xray_date');
    var date = dateInput ? (dateInput.value || formatToday()) : formatToday();
    var nameInput = document.getElementById('xray_name');
    var name = nameInput ? nameInput.value.trim() : '';
    if(dateInput && !dateInput.value) dateInput.value = date; // default today
    var url = './get_requested_xray?date=' + encodeURIComponent(date) + (name ? ('&name=' + encodeURIComponent(name)) : '');
    fetch(url)
      .then(function(res){ return res.json(); })
      .then(function(json){
        var tbody = document.querySelector('#xrayRequestedTable tbody');
        if(!tbody) return;
        tbody.innerHTML = '';
        if(!json.success || !json.data || json.data.length === 0){
          tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">Không có dữ liệu</td></tr>';
          return;
        }
        json.data.forEach(function(row){
          var tr = document.createElement('tr');
          tr.innerHTML = '<td class="text-center">' + row.id + '</td>'
            + '<td class="text-center">' + (row.ma_benh_nhan || '') + '</td>'
            + '<td>' + (row.ho_ten || '') + '</td>'
            + '<td class="text-center">' + (row.tuoi || '') + '</td>'
            + '<td class="text-center">' + (row.gioi_tinh || '') + '</td>'
            + '<td>' + (row.yeu_cau_chup || '') + '</td>'
            + '<td>' + (row.ngay_tao || '') + '</td>'
            + '<td class="text-center"><span class="badge bg-info text-dark">' + (row.trang_thai || '') + '</span></td>'
          + '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-primary btn-view-xray" data-id="' + row.id + '"><i class="fas fa-eye me-1"></i>Xem</button></td>'
          + '<td class="text-center"><button type="button" class="btn btn-sm btn-success btn-result-xray" data-id="' + row.id + '"><i class="fas fa-file-signature me-1"></i>Trả kết quả</button></td>';
          tbody.appendChild(tr);
        });
      })
      .catch(function(){
        var tbody = document.querySelector('#xrayRequestedTable tbody');
        if(tbody) tbody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Lỗi tải dữ liệu</td></tr>';
      });
  }

  // initial load and bind filter
  loadStats();
  loadRequested();
  var filterBtn = document.getElementById('xray_filter_btn');
  if(filterBtn){ filterBtn.addEventListener('click', function(){ loadRequested(); fetchStatsByFilter(); }); }

  function fetchStatsByFilter(){
    var dateInput = document.getElementById('xray_date');
    var date = dateInput ? (dateInput.value || formatToday()) : formatToday();
    fetch('?action=get_xray_stats_today&date=' + encodeURIComponent(date))
      .then(function(res){ return res.json(); })
      .then(function(json){
        if(!json.success || !json.data) return;
        var d = json.data;
        var el1 = document.getElementById('stat_total_today');
        var el2 = document.getElementById('stat_completed_today');
        var el3 = document.getElementById('stat_waiting');
        if(el1) el1.textContent = d.total_today ?? 0;
        if(el2) el2.textContent = d.completed_today ?? 0;
        if(el3) el3.textContent = d.waiting_count ?? 0;
      })
      .catch(function(){});
  }
  // Delegate click for view buttons to open modal
  document.addEventListener('click', function(e){
    if(e.target && (e.target.classList.contains('btn-view-xray') || e.target.closest('.btn-view-xray'))){
      var btn = e.target.closest('.btn-view-xray');
      var id = btn.getAttribute('data-id');
      fetch('./get_xray_form_data?id=' + id)
        .then(function(res){ return res.json(); })
        .then(function(json){
          if(!json.success || !json.data){
            alert('Không tải được dữ liệu phiếu');
            return;
          }
          fillXrayDetailModal(json.data);
          var modal = new bootstrap.Modal(document.getElementById('xrayDetailModal'));
          modal.show();
        })
        .catch(function(){ alert('Lỗi kết nối'); });
    }
    if(e.target && (e.target.classList.contains('btn-result-xray') || e.target.closest('.btn-result-xray'))){
      var btn2 = e.target.closest('.btn-result-xray');
      var id2 = btn2.getAttribute('data-id');
      fetch('?action=get_xray_result_data&id=' + encodeURIComponent(id2))
        .then(function(res){
          var ctype = res.headers.get('content-type') || '';
          console.log('result status', res.status, 'ctype', ctype);
          return res.text().then(function(txt){
            console.log('result text preview:', txt.slice(0,200));
            if(!res.ok){
              throw new Error('HTTP_' + res.status + ': ' + txt.slice(0,200));
            }
            if(ctype.indexOf('application/json') === -1){
              try { return JSON.parse(txt); } catch(e){
                throw new Error('INVALID_JSON: ' + txt.slice(0,200));
              }
            }
            try { return JSON.parse(txt); } catch(e){
              throw new Error('INVALID_JSON: ' + txt.slice(0,200));
            }
          });
        })
        .then(function(json){
          console.log('result json', json);
          if(!json.success || !json.data){ alert(json.message || 'Không tải được dữ liệu'); return; }
          fillReturnModal(json.data);
          // Load saved result data if exists
          loadSavedXrayResult(id2);
          // Load saved images if exists
          loadSavedXrayImages(id2);
          var modal2 = new bootstrap.Modal(document.getElementById('xrayReturnModal'));
          modal2.show();
        })
        .catch(function(err){ console.error('get_xray_result_data error', err); alert('Lỗi kết nối: ' + err.message); });
    }
  });

  // Upload images handlers
  document.addEventListener('click', function(e){
    if(e.target && e.target.id === 'rx_upload_btn'){
      var statusEl = document.getElementById('rx_upload_status');
      var files = document.getElementById('rx_image_input')?.files;
      var idText = document.getElementById('rx_id')?.textContent || '';
      var xrayId = idText.trim();
      if(!xrayId){ alert('Thiếu Số phiếu chỉ định'); return; }
      if(!files || files.length === 0){ alert('Chọn ít nhất 1 ảnh'); return; }
      var form = new FormData();
      form.append('id', xrayId);
      for(var i=0;i<files.length;i++){ form.append('images[]', files[i]); }
      statusEl.textContent = 'Đang tải lên...';
      fetch('?action=upload_xray_images', { method:'POST', body: form })
        .then(function(res){ return res.json(); })
        .then(function(json){
          if(!json.success){ statusEl.textContent = json.message || 'Tải lên thất bại'; return; }
          statusEl.textContent = 'Đã tải ' + (json.count || 0) + ' ảnh';
          // Add to global array
          json.files.forEach(function(url){ window.uploadedImages.push({url: url}); });
          renderGallery(window.uploadedImages);
        })
        .catch(function(){ statusEl.textContent = 'Lỗi kết nối'; });
    }
  });

  function renderGallery(files){
    var wrap = document.getElementById('rx_image_gallery');
    if(!wrap) return;
    wrap.innerHTML = '';
    files.forEach(function(file, index){
      var col = document.createElement('div');
      col.className = 'col-md-3 mb-2';
      col.innerHTML = '<div class="border p-1 position-relative">' +
        '<img src="' + file.url + '" class="img-fluid" style="cursor:pointer" onclick="zoomImage(\'' + file.url + '\')"/>' +
        '<button type="button" class="btn btn-sm btn-danger position-absolute" style="top:5px;right:5px" onclick="removeImage(' + index + ')">×</button>' +
        '</div>';
      wrap.appendChild(col);
    });
  }

  // Global array to store uploaded images
  window.uploadedImages = [];

  // Zoom image function with fullscreen and zoom controls
  window.zoomImage = function(imageUrl) {
    var modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Xem ảnh X-Quang</h5>
            <div class="btn-group me-2">
              <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomOutBtn" title="Thu nhỏ">
                <i class="fas fa-minus"></i>
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomInBtn" title="Phóng to">
                <i class="fas fa-plus"></i>
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary" id="resetZoomBtn" title="Reset">
                <i class="fas fa-expand-arrows-alt"></i>
              </button>
              <button type="button" class="btn btn-sm btn-outline-primary" id="fullscreenBtn" title="Toàn màn hình">
                <i class="fas fa-expand"></i>
              </button>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center" style="background: #f8f9fa; overflow: auto; max-height: 80vh;">
            <div id="imageContainer" style="position: relative; display: inline-block;">
              <img src="${imageUrl}" id="zoomImage" style="max-width: 100%; height: auto; cursor: grab; transition: transform 0.3s ease;" draggable="false"/>
            </div>
          </div>
        </div>
      </div>
    `;
    
    document.body.appendChild(modal);
    var bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Initialize zoom functionality
    var zoomLevel = 1;
    var isDragging = false;
    var startX, startY, translateX = 0, translateY = 0;
    var img = modal.querySelector('#zoomImage');
    var container = modal.querySelector('#imageContainer');
    
    function updateTransform() {
      img.style.transform = `scale(${zoomLevel}) translate(${translateX}px, ${translateY}px)`;
    }
    
    // Zoom controls
    modal.querySelector('#zoomInBtn').addEventListener('click', function() {
      zoomLevel = Math.min(zoomLevel * 1.2, 5);
      updateTransform();
    });
    
    modal.querySelector('#zoomOutBtn').addEventListener('click', function() {
      zoomLevel = Math.max(zoomLevel / 1.2, 0.1);
      updateTransform();
    });
    
    modal.querySelector('#resetZoomBtn').addEventListener('click', function() {
      zoomLevel = 1;
      translateX = 0;
      translateY = 0;
      updateTransform();
    });
    
    // Fullscreen functionality
    modal.querySelector('#fullscreenBtn').addEventListener('click', function() {
      if (!document.fullscreenElement) {
        container.requestFullscreen().then(function() {
          modal.querySelector('#fullscreenBtn').innerHTML = '<i class="fas fa-compress"></i>';
        });
      } else {
        document.exitFullscreen().then(function() {
          modal.querySelector('#fullscreenBtn').innerHTML = '<i class="fas fa-expand"></i>';
        });
      }
    });
    
    // Mouse wheel zoom
    container.addEventListener('wheel', function(e) {
      e.preventDefault();
      var delta = e.deltaY > 0 ? 0.9 : 1.1;
      zoomLevel = Math.max(0.1, Math.min(5, zoomLevel * delta));
      updateTransform();
    });
    
    // Mouse drag
    img.addEventListener('mousedown', function(e) {
      isDragging = true;
      startX = e.clientX - translateX;
      startY = e.clientY - translateY;
      img.style.cursor = 'grabbing';
    });
    
    document.addEventListener('mousemove', function(e) {
      if (isDragging) {
        translateX = e.clientX - startX;
        translateY = e.clientY - startY;
        updateTransform();
      }
    });
    
    document.addEventListener('mouseup', function() {
      isDragging = false;
      img.style.cursor = 'grab';
    });
    
    // Touch support for mobile
    var touchStartX, touchStartY;
    img.addEventListener('touchstart', function(e) {
      if (e.touches.length === 1) {
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
      }
    });
    
    img.addEventListener('touchmove', function(e) {
      if (e.touches.length === 1) {
        e.preventDefault();
        translateX += e.touches[0].clientX - touchStartX;
        translateY += e.touches[0].clientY - touchStartY;
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
        updateTransform();
      }
    });
    
    // Cleanup
    modal.addEventListener('hidden.bs.modal', function() {
      document.body.removeChild(modal);
    });
  };

  // Remove image function
  window.removeImage = function(index) {
    if(confirm('Xóa ảnh này?')) {
      window.uploadedImages.splice(index, 1);
      renderGallery(window.uploadedImages);
    }
  };

  // Load saved X-Ray result data
  function loadSavedXrayResult(xrayId){
    fetch('?action=get_saved_xray_result&id=' + encodeURIComponent(xrayId))
      .then(function(res){ return res.json(); })
      .then(function(json){
        if(json.success && json.data){
          // Fill the form with saved data
          document.getElementById('rx_suggestion_input').value = json.data.noi_dung || '';
          document.getElementById('rx_ket_luan_input').value = json.data.ket_luan || '';
          
          // Hiển thị Giờ nhận kết quả từ ngay_cap_nhat hoặc ngay_doc (format: H:i:s)
          var returnTimeEl = document.getElementById('rx_return_time');
          if(returnTimeEl){
            var timeStr = '-';
            if(json.data.ngay_cap_nhat){
              var date = new Date(json.data.ngay_cap_nhat);
              if(!isNaN(date.getTime())){
                var hours = String(date.getHours()).padStart(2, '0');
                var minutes = String(date.getMinutes()).padStart(2, '0');
                var seconds = String(date.getSeconds()).padStart(2, '0');
                timeStr = hours + ':' + minutes + ':' + seconds;
              }
            } else if(json.data.ngay_doc){
              var date = new Date(json.data.ngay_doc);
              if(!isNaN(date.getTime())){
                var hours = String(date.getHours()).padStart(2, '0');
                var minutes = String(date.getMinutes()).padStart(2, '0');
                var seconds = String(date.getSeconds()).padStart(2, '0');
                timeStr = hours + ':' + minutes + ':' + seconds;
              }
            }
            returnTimeEl.textContent = timeStr;
          }
        }
      })
      .catch(function(err){ console.log('No saved result data:', err); });
  }

  // Load saved X-Ray images
  function loadSavedXrayImages(xrayId){
    fetch('?action=get_saved_xray_images&id=' + encodeURIComponent(xrayId))
      .then(function(res){ return res.json(); })
      .then(function(json){
        if(json.success && json.data){
          // Clear and populate global array
          window.uploadedImages = json.data.map(function(img){ return {url: img.file_path}; });
          renderGallery(window.uploadedImages);
        }
      })
      .catch(function(err){ console.log('No saved images:', err); });
  }

  // Save result button
  document.addEventListener('click', function(e){
    if(e.target && e.target.id === 'rx_save_btn'){
      var idText = document.getElementById('rx_id')?.textContent || '';
      var xrayId = idText.trim();
      if(!xrayId){ alert('Thiếu Số phiếu chỉ định'); return; }
      
      // Validation: Check if Kết quả and KẾT LUẬN are filled
      var ketQua = document.getElementById('rx_suggestion_input')?.value?.trim() || '';
      var ketLuan = document.getElementById('rx_ket_luan_input')?.value?.trim() || '';
      
      if(!ketQua){
        alert('Vui lòng nhập Kết quả trước khi lưu!');
        document.getElementById('rx_suggestion_input')?.focus();
        return;
      }
      
      if(!ketLuan){
        alert('Vui lòng nhập KẾT LUẬN trước khi lưu!');
        document.getElementById('rx_ket_luan_input')?.focus();
        return;
      }
      
      var data = {
        id_phieu_chup_xquang: xrayId,
        chuan_doan: document.getElementById('rx_chuan_doan')?.textContent || '',
        noi_dung: ketQua,
        ket_luan: ketLuan,
        bac_si_xquang: (typeof XRAY_DOCTOR_NAME !== 'undefined' && XRAY_DOCTOR_NAME) ? XRAY_DOCTOR_NAME : ''
      };
      fetch('?action=save_xray_result', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data) })
        .then(function(res){ return res.json(); })
        .then(function(json){
          if(!json.success){ alert(json.message || 'Lưu thất bại'); return; }
          alert('Đã lưu kết quả X-Quang');
          // Reload dữ liệu kết quả để hiển thị Giờ nhận kết quả
          loadSavedXrayResult(xrayId);
        })
        .catch(function(){ alert('Lỗi kết nối'); });
    }
  });

  // Print result button
  document.addEventListener('click', function(e){
    if(e.target && e.target.id === 'rx_print_btn'){
      var idText = document.getElementById('rx_id')?.textContent || '';
      var xrayId = idText.trim();
      if(!xrayId){ alert('Thiếu Số phiếu chỉ định'); return; }
      
      // Validation: Check if Kết quả and KẾT LUẬN are filled
      var ketQua = document.getElementById('rx_suggestion_input')?.value?.trim() || '';
      var ketLuan = document.getElementById('rx_ket_luan_input')?.value?.trim() || '';
      
      if(!ketQua){
        alert('Vui lòng nhập Kết quả trước khi in!');
        document.getElementById('rx_suggestion_input')?.focus();
        return;
      }
      
      if(!ketLuan){
        alert('Vui lòng nhập KẾT LUẬN trước khi in!');
        document.getElementById('rx_ket_luan_input')?.focus();
        return;
      }
      
      window.open('?action=print_xray_result&id=' + encodeURIComponent(xrayId), '_blank');
    }
  });

  // Complete button
  document.addEventListener('click', function(e){
    if(e.target && e.target.id === 'rx_complete_btn'){
      var idText = document.getElementById('rx_id')?.textContent || '';
      var xrayId = idText.trim();
      if(!xrayId){ alert('Thiếu Số phiếu chỉ định'); return; }
      
      // Validation: Check if Kết quả and KẾT LUẬN are filled
      var ketQua = document.getElementById('rx_suggestion_input')?.value?.trim() || '';
      var ketLuan = document.getElementById('rx_ket_luan_input')?.value?.trim() || '';
      
      if(!ketQua){
        alert('Vui lòng nhập Kết quả trước khi hoàn thành!');
        document.getElementById('rx_suggestion_input')?.focus();
        return;
      }
      
      if(!ketLuan){
        alert('Vui lòng nhập KẾT LUẬN trước khi hoàn thành!');
        document.getElementById('rx_ket_luan_input')?.focus();
        return;
      }
      
      // Validation: Check if at least one image is uploaded
      if(window.uploadedImages.length === 0){
        alert('Vui lòng tải ít nhất 1 ảnh X-Quang trước khi hoàn thành!');
        // Switch to images tab
        var imagesTab = document.getElementById('tab-images-tab');
        if(imagesTab) {
          var tab = new bootstrap.Tab(imagesTab);
          tab.show();
        }
        return;
      }
      
      if(!confirm('Xác nhận hoàn thành kết quả X-Quang?')) return;
      
      // Save images to database first
      if(window.uploadedImages.length > 0) {
        var imageData = {
          id_phieu_chup_xquang: xrayId,
          images: window.uploadedImages.map(function(img){ return img.url; })
        };
        
        fetch('?action=save_xray_images', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(imageData) })
          .then(function(res){ return res.json(); })
          .then(function(json){
            if(!json.success){ alert(json.message || 'Lưu ảnh thất bại'); return; }
            // Then complete the result
            completeXrayResult(xrayId);
          })
          .catch(function(){ alert('Lỗi kết nối khi lưu ảnh'); });
      } else {
        // No images, just complete
        completeXrayResult(xrayId);
      }
    }
  });

  function completeXrayResult(xrayId) {
    fetch('?action=complete_xray_result', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id: xrayId}) })
      .then(function(res){ return res.json(); })
      .then(function(json){
        if(!json.success){ alert(json.message || 'Cập nhật thất bại'); return; }
        alert('Đã hoàn thành kết quả X-Quang');
        // Đóng modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('xrayReturnModal'));
        if(modal) {
          modal.hide();
        }
        // Reload the requested list to update status
        loadRequested();
      })
      .catch(function(){ alert('Lỗi kết nối'); });
  }

  function parseDateParts(ts){
    try{
      var d = new Date(ts);
      if(!isNaN(d.getTime())) return {day: d.getDate(), month: d.getMonth()+1, year: d.getFullYear()};
    }catch(e){}
    var now = new Date();
    return {day: now.getDate(), month: now.getMonth()+1, year: now.getFullYear()};
  }

  function fillXrayDetailModal(data){
    document.getElementById('vx_clinic_name').value = 'Thịnh Việt';
    document.getElementById('vx_phone').value = data.so_dien_thoai || '0777871608';
    document.getElementById('vx_quan').value = data.quan || 'Gò Vấp';
    document.getElementById('vx_patient_code').value = data.ma_benh_nhan || '';
    document.getElementById('vx_patient_name').value = data.ho_ten || '';
    document.getElementById('vx_patient_age').value = data.tuoi || '';
    document.getElementById('vx_patient_gender').value = data.gioi_tinh || '';
    document.getElementById('vx_address').value = data.dia_chi || '';
    document.getElementById('vx_patient_type').value = data.doi_tuong || '';
    document.getElementById('vx_insurance_number').value = data.so_the_bhyt || '';
    document.getElementById('vx_diagnosis').value = data.chan_doan_vao_vien || '';
    document.getElementById('vx_request').value = data.yeu_cau_chup || '';
    // Giờ chỉ định
    var timeStr = '';
    var tsRaw = data.ngay_cap_nhat || data.ngay_tao || '';
    if (tsRaw) {
      var dTime = new Date(String(tsRaw).replace(' ', 'T'));
      if (!isNaN(dTime.getTime())) {
        timeStr = dTime.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
      }
    }
    var vxOrderTime = document.getElementById('vx_order_time');
    if (vxOrderTime) vxOrderTime.value = timeStr;
    var p = parseDateParts(data.ngay_tao);
    document.getElementById('vx_ngay').value = p.day;
    document.getElementById('vx_thang').value = p.month;
    document.getElementById('vx_nam').value = p.year;
    document.getElementById('vx_doctor').textContent = data.bac_si_kham || '';
  }

  function fillReturnModal(data){
    console.log('fillReturnModal data:', data);
    function setText(id, val){ var el=document.getElementById(id); if(el) el.textContent = (val ?? ''); }
    function setValue(id, val){ var el=document.getElementById(id); if(el) el.value = (val ?? ''); }
    setText('rx_id', data.id);
    setText('rx_name', data.ho_ten);
    setText('rx_age', data.tuoi);
    setText('rx_gender', data.gioi_tinh);
    setText('rx_address', data.dia_chi);
    setText('rx_year_of_birth', data.nam_sinh);
    setText('rx_khoa_chi_dinh', data.khoa_chi_dinh);
    // Parse MySQL TIMESTAMP/DATETIME (e.g., "2025-10-01 14:35:22") safely
    function parseMysqlTs(mysqlTs){
      if(!mysqlTs) return new Date();
      try{
        // Replace space with 'T' to improve cross-browser parsing
        var iso = String(mysqlTs).replace(' ', 'T');
        var d = new Date(iso);
        if(!isNaN(d.getTime())) return d;
      }catch(e){}
      return new Date();
    }
    var ts = data.ngay_cap_nhat || data.ngay_tao || null;
    var dt = parseMysqlTs(ts);
    if (isNaN(dt.getTime())) { dt = new Date(); }
    document.getElementById('rx_date').textContent = dt.toLocaleDateString('vi-VN');
    document.getElementById('rx_time').textContent = dt.toLocaleTimeString('vi-VN');
    document.getElementById('rx_day').textContent = String(dt.getDate()).padStart(2,'0');
    document.getElementById('rx_month').textContent = String(dt.getMonth()+1).padStart(2,'0');
    document.getElementById('rx_year').textContent = dt.getFullYear();
    var cd = data.chan_doan_vao_vien || '';
    setText('rx_chuan_doan', cd);
    setText('rx_bac_si_chi_dinh', data.bac_si_chi_dinh || '');
    var ndVal = data.yeu_cau_chup ? ('Chụp X-Quang ' + data.yeu_cau_chup) : '';
    setText('rx_noi_dung', ndVal);
    setValue('rx_suggestion_input', '');
    setValue('rx_ket_luan_input', '');
    // Reset Giờ nhận kết quả khi mở modal mới
    setText('rx_return_time', '-');
    var xrayDoctorName = (typeof XRAY_DOCTOR_NAME !== 'undefined' && XRAY_DOCTOR_NAME) ? XRAY_DOCTOR_NAME : (data.bac_si_xquang || '');
    setText('rx_doctor', xrayDoctorName);
  }
});
</script>
HTML;

renderLayout($content, 'X-Ray Dashboard - Hệ thống Quản lý Bệnh viện');
