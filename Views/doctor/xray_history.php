<?php
require_once 'Views/layouts/layout_helper.php';
$content = '<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="fas fa-history text-success me-2"></i>Lịch sử chụp X-Quang</h3>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-2 align-items-end">
        <div class="col-auto">
          <label class="form-label mb-1">Ngày</label>
          <input type="date" class="form-control" id="hx_date">
        </div>
        <div class="col-auto">
          <label class="form-label mb-1">Số phiếu chỉ định</label>
          <input type="text" class="form-control" id="hx_request_id" placeholder="Nhập số phiếu chỉ định...">
        </div>
        <div class="col-auto">
          <label class="form-label mb-1">Mã Bệnh Nhân</label>
          <input type="text" class="form-control" id="hx_patient_code" placeholder="Nhập mã bệnh nhân...">
        </div>
        <div class="col-auto">
          <button type="button" class="btn btn-outline-secondary" id="hx_filter_btn"><i class="fas fa-filter me-1"></i>Lọc</button>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-bordered align-middle" id="hx_table">
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
</div>';

$content .= <<<'HTML'
<script>
document.addEventListener('DOMContentLoaded', function(){
  function formatToday(){
    var d=new Date();var m=String(d.getMonth()+1).padStart(2,'0');var day=String(d.getDate()).padStart(2,'0');
    return d.getFullYear()+'-'+m+'-'+day;
  }
  // Set default date to empty to show all records
  var dateEl=document.getElementById('hx_date');
  if(dateEl){ dateEl.value = ''; }

  function loadHistory(){
    var date = document.getElementById('hx_date')?.value || '';
    var requestId = document.getElementById('hx_request_id')?.value || '';
    var patientCode = document.getElementById('hx_patient_code')?.value || '';
    var url='?action=get_xray_history' + (date?('&date='+encodeURIComponent(date)):'') + (requestId?('&request_id='+encodeURIComponent(requestId)):'') + (patientCode?('&patient_code='+encodeURIComponent(patientCode)):'');
    fetch(url)
      .then(function(res){return res.json();})
      .then(function(json){
        var tbody=document.querySelector('#hx_table tbody');
        if(!tbody) return;
        tbody.innerHTML='';
        if(!json.success || !json.data || json.data.length===0){
          tbody.innerHTML='<tr><td colspan="9" class="text-center text-muted">Không có dữ liệu</td></tr>';
          return;
        }
        json.data.forEach(function(row){
          var tr=document.createElement('tr');
          tr.innerHTML = '<td class="text-center">'+row.id+'</td>'
            + '<td class="text-center">'+ (row.ma_benh_nhan||'') +'</td>'
            + '<td>'+ (row.ho_ten||'') +'</td>'
            + '<td class="text-center">'+ (row.gioi_tinh||'') +'</td>'
            + '<td class="text-center">'+ (row.tuoi||'') +'</td>'
            + '<td>'+ (row.ngay_tao||'') +'</td>'
            + '<td>'+ (row.trang_thai||'') +'</td>'
            + '<td>'+ (row.ket_luan||'') +'</td>'
            + '<td class="text-center">'
            + '<button type="button" class="btn btn-sm btn-outline-primary me-1 btn-view-form" data-id="'+row.id+'">Xem phiếu</button>'
            + '<button type="button" class="btn btn-sm btn-outline-success btn-view-images" data-id="'+row.id+'">Xem ảnh</button>'
            + '</td>';
          tbody.appendChild(tr);
        });
      })
      .catch(function(){
        var tbody=document.querySelector('#hx_table tbody');
        if(tbody) tbody.innerHTML='<tr><td colspan="9" class="text-center text-danger">Lỗi tải dữ liệu</td></tr>';
      });
  }

  loadHistory();
  var btn=document.getElementById('hx_filter_btn');
  if(btn) btn.addEventListener('click', loadHistory);

  // Delegate clicks for view buttons
  document.addEventListener('click', function(e){
    var t = e.target;
    if(t && t.classList.contains('btn-view-form')){
      var id = t.getAttribute('data-id');
      openResultModal(id);
    }
    if(t && t.classList.contains('btn-view-images')){
      var id2 = t.getAttribute('data-id');
      openImagesModal(id2);
    }
  });

  function normalizeUrl(u){
    if(!u) return '';
    if(u.startsWith('http://') || u.startsWith('https://') || u.startsWith('./') || u.startsWith('/')) return u;
    return './' + u.replace(/^\/+/, '');
  }

  function openImagesModal(xrayId){
    fetch('?action=get_saved_xray_images&id=' + encodeURIComponent(xrayId))
      .then(function(res){ return res.json(); })
      .then(function(json){
        var images = (json.success && json.data) ? json.data : [];
        var modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = '\n<div class="modal-dialog modal-xl">\n  <div class="modal-content">\n    <div class="modal-header">\n      <h5 class="modal-title">Hình ảnh X-Quang - ID ' + xrayId + '</h5>\n      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>\n    </div>\n    <div class="modal-body">\n      <div class="row" id="hx_gallery"></div>\n    </div>\n  </div>\n</div>';
        document.body.appendChild(modal);
        var bs = new bootstrap.Modal(modal);
        bs.show();
        var wrap = modal.querySelector('#hx_gallery');
        if(wrap){
          wrap.innerHTML = '';
          if(images.length === 0){
            wrap.innerHTML = '<div class="text-center text-muted">Chưa có ảnh</div>';
          } else {
            images.forEach(function(img){
              var url = normalizeUrl(img.file_path);
              var col = document.createElement('div');
              col.className = 'col-md-3 mb-2';
              col.innerHTML = '<div class="border p-1"><img src="' + url + '" class="img-fluid" style="cursor:pointer" onerror="this.replaceWith(document.createTextNode(\'Không tải được ảnh\'))" onclick="zoomImage(\'' + url + '\')"/></div>';
              wrap.appendChild(col);
            });
          }
        }
        modal.addEventListener('hidden.bs.modal', function(){ document.body.removeChild(modal); });
      })
      .catch(function(){ alert('Lỗi tải ảnh'); });
  }

  function openResultModal(xrayId){
    fetch('?action=get_xray_result_view&id=' + encodeURIComponent(xrayId))
      .then(function(res){ return res.json(); })
      .then(function(json){
        if(!json.success || !json.data){ alert(json.message || 'Không tải được phiếu'); return; }
        var d = json.data;
        var modal = document.createElement('div');
        modal.className = 'modal fade';
        var dateObj = d.ngay_cap_nhat ? new Date(d.ngay_cap_nhat) : (d.ngay_tao ? new Date(d.ngay_tao) : new Date());
        var dateStr = dateObj.toLocaleDateString('vi-VN');
        var timeStr = dateObj.toLocaleTimeString('vi-VN');
        var returnTimeStr = '-';
        if(d.ngay_doc){
          var returnDateObj = new Date(d.ngay_doc);
          if(!isNaN(returnDateObj.getTime())){
            var hours = String(returnDateObj.getHours()).padStart(2, '0');
            var minutes = String(returnDateObj.getMinutes()).padStart(2, '0');
            var seconds = String(returnDateObj.getSeconds()).padStart(2, '0');
            returnTimeStr = hours + ':' + minutes + ':' + seconds;
          }
        }
        modal.innerHTML = '\n<div class="modal-dialog modal-xl">\n  <div class="modal-content">\n    <style>\n      .report{font-family:\"Times New Roman\",serif;padding:18px}\n      .report .title{font-weight:bold;text-transform:uppercase;text-align:center;letter-spacing:.5px;font-size:18px;margin-bottom:6px}\n      .report .subtitle{text-align:center;margin-top:-4px;margin-bottom:8px}\n      .report .hr{border-top:2px solid #000;margin:10px 0}\n      .report .row-line{display:flex;gap:8px;margin-bottom:6px;font-size:15px}\n      .report .label{min-width:150px;font-weight:bold}\n      .report .dots{flex:0 0 auto}\n      .report .value{flex:1;border-bottom:1px dotted #333;min-height:20px}\n      .report .section{margin-top:10px;margin-bottom:6px;font-weight:bold;text-transform:uppercase}\n      .report .signature{min-width:260px}\n    </style>\n    <div class="modal-header">\n      <h5 class="modal-title">XEM PHIẾU CHỤP X-QUANG</h5>\n      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>\n    </div>\n    <div class="modal-body">\n      <div class="report border border-dark">\n        <div class="text-center mb-3">\n          <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:60px;object-fit:contain;margin-bottom:10px;">\n        </div>\n        <div class="title">PHÒNG KHÁM ĐA KHOA THINHVIET</div>\n        <div class="title">KHOA CHUẨN ĐOÁN HÌNH ẢNH</div>\n        <div class="subtitle">Địa chỉ: Gò Vấp - Điện thoại: 0777871608</div>\n        <div class="hr"></div>\n        <div class="row-line"><div class="label">Họ và tên</div><div class="dots">:</div><div class="value">' + (d.ho_ten||'') + '</div><div class="label" style="min-width:90px">Giới tính</div><div class="dots">:</div><div class="value">' + (d.gioi_tinh||'') + '</div></div>\n        <div class="row-line"><div class="label">Năm sinh</div><div class="dots">:</div><div class="value">' + (d.nam_sinh||'') + '</div><div class="label" style="min-width:150px">Số phiếu chỉ định</div><div class="dots">:</div><div class="value">' + d.id + '</div></div>\n        <div class="row-line"><div class="label">Địa chỉ</div><div class="dots">:</div><div class="value">' + (d.dia_chi||'') + '</div></div>\n        <div class="row-line"><div class="label">Ngày chỉ định</div><div class="dots">:</div><div class="value">' + dateStr + '</div><div class="label" style="min-width:120px">Giờ chỉ định</div><div class="dots">:</div><div class="value">' + timeStr + '</div></div>\n        <div class="row-line"><div class="label">Giờ nhận kết quả</div><div class="dots">:</div><div class="value">' + returnTimeStr + '</div></div>\n        <div class="hr"></div>\n        <div class="row-line"><div class="label">Chẩn đoán</div><div class="dots">:</div><div class="value">' + (d.chan_doan_vao_vien||'') + '</div></div>\n        <div class="row-line"><div class="label">Bác sĩ chỉ định</div><div class="dots">:</div><div class="value">' + (d.bac_si_chi_dinh||'') + '</div></div>\n        <div class="row-line"><div class="label">Nội dung</div><div class="dots">:</div><div class="value">' + (d.yeu_cau_chup ? ('Chụp X-Quang ' + d.yeu_cau_chup) : '') + '</div></div>\n        <div class="section">Kết quả</div>\n        <div class="value" style="border:1px dotted #333;min-height:80px;padding:8px">' + (d.noi_dung||'') + '</div>\n        <div class="section">Kết luận</div>\n        <div class="value" style="border:1px dotted #333;min-height:80px;padding:8px">' + (d.ket_luan||'') + '</div>\n        <div class="d-flex justify-content-end mt-3">\n          <div class="text-center signature">\n            <div class="mb-1"><em>Ngày ' + (new Date().getDate()) + ' tháng ' + (new Date().getMonth()+1) + ' năm ' + (new Date().getFullYear()) + '</em></div>\n            <div class="fw-bold">Bác sĩ X Quang</div>\n            <div class="mt-3" style="min-height:40px">' + (d.bac_si_xquang||'') + '</div>\n          </div>\n        </div>\n      </div>\n    </div>\n  </div>\n</div>';
        document.body.appendChild(modal);
        var bs = new bootstrap.Modal(modal);
        bs.show();
        modal.addEventListener('hidden.bs.modal', function(){ document.body.removeChild(modal); });
      })
      .catch(function(){ alert('Lỗi tải phiếu'); });
  }

  // Zoom image viewer with controls (reused if not already defined)
  if(!window.zoomImage){
    window.zoomImage = function(imageUrl){
      var modal = document.createElement('div');
      modal.className = 'modal fade';
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

      var zoomLevel = 1; var isDragging=false; var startX, startY, translateX=0, translateY=0;
      var img = modal.querySelector('#zoomImage');
      var container = modal.querySelector('#imageContainer');
      function updateTransform(){ img.style.transform = `scale(${zoomLevel}) translate(${translateX}px, ${translateY}px)`; }
      modal.querySelector('#zoomInBtn').addEventListener('click', function(){ zoomLevel = Math.min(zoomLevel*1.2, 5); updateTransform();});
      modal.querySelector('#zoomOutBtn').addEventListener('click', function(){ zoomLevel = Math.max(zoomLevel/1.2, 0.1); updateTransform();});
      modal.querySelector('#resetZoomBtn').addEventListener('click', function(){ zoomLevel=1; translateX=0; translateY=0; updateTransform();});
      modal.querySelector('#fullscreenBtn').addEventListener('click', function(){
        if(!document.fullscreenElement){ container.requestFullscreen().then(()=>{ modal.querySelector('#fullscreenBtn').innerHTML='<i class="fas fa-compress"></i>';}); }
        else { document.exitFullscreen().then(()=>{ modal.querySelector('#fullscreenBtn').innerHTML='<i class="fas fa-expand"></i>';}); }
      });
      container.addEventListener('wheel', function(e){ e.preventDefault(); var delta = e.deltaY>0?0.9:1.1; zoomLevel=Math.max(0.1, Math.min(5, zoomLevel*delta)); updateTransform();});
      img.addEventListener('mousedown', function(e){ isDragging=true; startX=e.clientX-translateX; startY=e.clientY-translateY; img.style.cursor='grabbing';});
      document.addEventListener('mousemove', function(e){ if(isDragging){ translateX=e.clientX-startX; translateY=e.clientY-startY; updateTransform(); }});
      document.addEventListener('mouseup', function(){ isDragging=false; img.style.cursor='grab';});
      var touchStartX, touchStartY; img.addEventListener('touchstart', function(e){ if(e.touches.length===1){ touchStartX=e.touches[0].clientX; touchStartY=e.touches[0].clientY; }});
      img.addEventListener('touchmove', function(e){ if(e.touches.length===1){ e.preventDefault(); translateX+=e.touches[0].clientX-touchStartX; translateY+=e.touches[0].clientY-touchStartY; touchStartX=e.touches[0].clientX; touchStartY=e.touches[0].clientY; updateTransform(); }});
      modal.addEventListener('hidden.bs.modal', function(){ document.body.removeChild(modal);});
    };
  }
});
</script>
HTML;

renderLayout($content, 'Lịch sử chụp X-Quang');
?>

