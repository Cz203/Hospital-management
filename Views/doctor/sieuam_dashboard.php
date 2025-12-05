<?php
require_once 'Views/layouts/layout_helper.php';
$ctx = getCurrentUserContext();
$sieuamDoctorSessionName = ($ctx['role'] === 'sieuam_doctor') ? ($ctx['name'] ?? '') : '';

$content = '
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-stethoscope text-primary me-2"></i>
                Siêu âm Dashboard
            </h1>
            <p class="text-muted">Chào mừng bác sĩ Siêu âm ' . htmlspecialchars(($ctx['name'] ?? ''), ENT_QUOTES, 'UTF-8') . ' - Chẩn đoán siêu âm</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <button class="btn btn-primary">
                <i class="fas fa-calendar-plus me-2"></i>Lịch siêu âm
            </button>
            <button class="btn btn-success">
                <i class="fas fa-stethoscope me-2"></i>Siêu âm
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
                                Yêu cầu siêu âm hôm nay
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat_total_today">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-stethoscope fa-2x text-gray-300"></i>
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
                                Đã siêu âm xong
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
                                Đang chờ siêu âm
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
                    <h6 class="m-0 font-weight-bold text-primary">Thao tác nhanh</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="text-primary mb-2"><i class="fas fa-list me-2"></i>Yêu cầu siêu âm (Đã yêu cầu)</h5>
                        <div class="row g-2 align-items-end mb-2">
                          <div class="col-auto">
                            <label class="form-label mb-1">Ngày</label>
                            <input type="date" class="form-control" id="sieuam_date">
                          </div>
                          <div class="col-auto">
                            <label class="form-label mb-1">Tìm kiếm (Mã bệnh nhân)</label>
                            <input type="text" class="form-control" id="sieuam_name" placeholder="Nhập mã bệnh nhân...">
                          </div>
                          <div class="col-auto">
                            <button type="button" class="btn btn-outline-secondary" id="sieuam_filter_btn"><i class="fas fa-filter me-1"></i>Lọc</button>
                          </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="sieuamRequestedTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:80px" class="text-center">ID</th>
                                        <th style="width:120px" class="text-center">Mã Bệnh Nhân</th>
                                        <th>Họ tên</th>
                                        <th style="width:80px" class="text-center">Tuổi</th>
                                        <th style="width:90px" class="text-center">Giới tính</th>
                                        <th>Yêu cầu siêu âm</th>
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

// Modal for Ultrasound details (read-only, doctor-like layout)
$content .= '
<div class="modal fade" id="sieuamDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title"><i class="fas fa-stethoscope me-2"></i>Phiếu yêu cầu siêu âm (Xem)</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3 text-center">
          <div class="fw-bold" style="font-size:18px">PHIẾU YÊU CẦU SIÊU ÂM</div>
        </div>

        <div class="row mb-2">
          <div class="col-md-6">
            <label class="form-label fw-bold">Cơ sở y tế</label>
            <input type="text" class="form-control" id="vs_clinic_name" readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Điện thoại</label>
            <input type="text" class="form-control" id="vs_phone" readonly>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-bold">Quận</label>
            <input type="text" class="form-control" id="vs_quan" readonly>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label fw-bold">Họ tên người bệnh</label>
            <input type="text" class="form-control" id="vs_patient_name" readonly>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-bold">Tuổi</label>
            <input type="text" class="form-control" id="vs_patient_age" readonly>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-bold">Nam/Nữ</label>
            <input type="text" class="form-control" id="vs_patient_gender" readonly>
          </div>
          <div class="col-12">
            <label class="form-label fw-bold">Địa chỉ</label>
            <input type="text" class="form-control" id="vs_address" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Đối tượng</label>
            <input type="text" class="form-control" id="vs_patient_type" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Số thẻ BHYT</label>
            <input type="text" class="form-control" id="vs_insurance_number" readonly>
          </div>
        </div>

        <div class="mt-3">
          <label class="form-label fw-bold">Giờ chỉ định:</label>
          <input type="text" class="form-control" id="vs_order_time" readonly>
        </div>

        <div class="mt-3">
          <label class="form-label fw-bold">Chuẩn đoán:</label>
          <input type="text" class="form-control" id="vs_diagnosis" readonly>
        </div>

        <div class="mt-3 position-relative">
          <label class="form-label fw-bold text-center w-100 d-block" style="font-size:16px">YÊU CẦU SIÊU ÂM</label>
          <textarea class="form-control" id="vs_request" rows="6" readonly></textarea>
        </div>

        <div class="row mt-4">
          <div class="col-md-6"></div>
          <div class="col-md-6 text-center">
            <div class="mb-2 d-flex align-items-center justify-content-center gap-2">
              <span>Ngày</span>
              <input type="text" class="form-control" id="vs_day" style="width:60px" readonly>
              <span>Tháng</span>
              <input type="text" class="form-control" id="vs_month" style="width:60px" readonly>
              <span>Năm</span>
              <input type="text" class="form-control" id="vs_year" style="width:80px" readonly>
            </div>
            <div class="fw-bold">BÁC SĨ KHÁM</div>
            <input type="text" class="form-control mt-2" id="vs_doctor" readonly>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
';

// Modal for returning ultrasound results
$content .= '
<div class="modal fade" id="sieuamReturnModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title"><i class="fas fa-stethoscope me-2"></i>Trả kết quả siêu âm</h5>
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
        </style>
        
        <div class="report">
          <div class="text-center mb-3">
            <div class="fw-bold" style="font-size: 18px; color: #333;">PHÒNG KHÁM ĐA KHOA THINHVIET</div>
            <div class="fw-bold" style="font-size: 16px; color: #666;">KHOA SẢN</div>
          </div>
          <div class="title">KẾT QUẢ SIÊU ÂM</div>
          
          <div class="text-center mb-2">
            <div class="fw-bold">Máy: Medison Sonoace X6</div>
          </div>
          
          <div class="row-line">
            <div class="label">ID:</div>
            <div class="dots">:</div>
            <div class="value" id="ur_id">*8307791*</div>
            <div class="label" style="margin-left: 20px;">Ngày ĐK:</div>
            <div class="dots">:</div>
            <div class="value" id="ur_date">03/07/2025</div>
            <div class="dots">-</div>
            <div class="value" id="ur_time">08:26</div>
          </div>
          
          <div class="hr"></div>
          
          <div class="row-line">
            <div class="label">Họ và tên:</div>
            <div class="dots">:</div>
            <div class="value" id="ur_name"></div>
            <div class="label" style="margin-left: 50px;">Địa chỉ:</div>
            <div class="dots">:</div>
            <div class="value" id="ur_address"></div>
          </div>
          
          <div class="row-line">
            <div class="label">Chẩn đoán sơ bộ:</div>
            <div class="dots">:</div>
            <div class="value" id="ur_chan_doan"></div>
            <div class="label" style="margin-left: 50px;">BS chỉ định:</div>
            <div class="dots">:</div>
            <div class="value" id="ur_bs_chi_dinh"></div>
          </div>
          
          <div class="row-line">
            <div class="label">Tuổi:</div>
            <div class="dots">:</div>
            <div class="value" id="ur_age"></div>
            <div class="label" style="margin-left: 50px;">Giới tính:</div>
            <div class="dots">:</div>
            <div class="value" id="ur_gender"></div>
          </div>
          
          <div class="row-line">
            <div class="label">Phiếu chỉ định:</div>
            <div class="dots">:</div>
            <div class="value" id="ur_phieu_chi_dinh"></div>
          </div>
          
          <div class="hr"></div>
          
          <div class="section">VÙNG KHẢO SÁT : <span id="ur_vung_khao_sat">SIÊU ÂM BỤNG TỔNG QUÁT MÀU</span></div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">KẾT QUẢ KHẢO SÁT:</label>
            <textarea class="form-control" id="ur_ket_qua_khao_sat" rows="8" placeholder="Nhập kết quả khảo sát siêu âm chi tiết...&#10;Nhấn Enter để tự động thêm gạch đầu dòng"></textarea>
          </div>
          
          <!-- Phần tải ảnh siêu âm -->
          <div class="mb-3">
            <label class="form-label fw-bold">HÌNH ẢNH SIÊU ÂM:</label>
            <div class="border rounded p-3 bg-light">
              <div class="mb-2">
                <input type="file" class="form-control" id="ur_image_upload" accept="image/*" multiple>
                <small class="text-muted">Chọn nhiều ảnh cùng lúc (JPG, PNG, GIF)</small>
              </div>
              
              <!-- Gallery hiển thị ảnh đã tải -->
              <div id="ur_image_gallery" class="row g-2">
                <!-- Ảnh sẽ được hiển thị ở đây -->
              </div>
            </div>
          </div>
          
          <div class="hr"></div>
          
          <div class="section conclusion">KẾT LUẬN :</div>
          <textarea class="form-control" id="ur_ket_luan" rows="3" placeholder="Nhập kết luận siêu âm..."></textarea>
          
          <div style="margin-top: 30px; text-align: right;">
            <div class="mb-3">
              <span>Ngày</span>
              <input type="text" class="form-control d-inline-block" id="ur_day" style="width:60px; margin: 0 5px;" readonly>
              <span>tháng</span>
              <input type="text" class="form-control d-inline-block" id="ur_month" style="width:60px; margin: 0 5px;" readonly>
              <span>năm</span>
              <input type="text" class="form-control d-inline-block" id="ur_year" style="width:80px; margin: 0 5px;" readonly>
            </div>
            <div style="margin-right: 20px;">
              <div class="fw-bold">BÁC SĨ SIÊU ÂM</div>
              <div class="mt-2" id="ur_bac_si_doc" style="margin-left: -20px;"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
        <button type="button" class="btn btn-primary" onclick="saveUltrasoundResult()">
          <i class="fas fa-save me-1"></i>Lưu
        </button>
        <button type="button" class="btn btn-success" onclick="printUltrasoundResult()">
          <i class="fas fa-print me-1"></i>In
        </button>
        <button type="button" class="btn btn-warning" onclick="completeUltrasoundResult()">
          <i class="fas fa-check-circle me-1"></i>Hoàn thành
        </button>
      </div>
    </div>
  </div>
</div>
';

// Include sidebar
include 'Views/layouts/sieuam_sidebar.php';

// Render page
renderLayout($content, 'Siêu âm Dashboard - Hệ thống Quản lý Bệnh viện');

?>

<script>
    // Global variables
    let currentUltrasoundId = null;

    // Load statistics
    function loadStats() {
        const date = document.getElementById('sieuam_date').value || new Date().toISOString().split('T')[0];

        fetch(`./?action=get_ultrasound_stats&date=${date}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('stat_total_today').textContent = data.stats.total_today || 0;
                    document.getElementById('stat_completed_today').textContent = data.stats.completed_today || 0;
                    document.getElementById('stat_waiting').textContent = data.stats.pending || 0;
                }
            })
            .catch(error => {
                console.error('Error loading stats:', error);
            });
    }

    // Load ultrasound requests
    function loadUltrasoundRequests() {
        const date = document.getElementById('sieuam_date').value || new Date().toISOString().split('T')[0];
        const name = document.getElementById('sieuam_name').value;

        fetch(`./?action=get_ultrasound_requests&date=${date}&name=${encodeURIComponent(name)}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('#sieuamRequestedTable tbody');

                if (data.success && data.requests && data.requests.length > 0) {
                    tbody.innerHTML = data.requests.map(req => `
                    <tr>
                        <td class="text-center">${req.id}</td>
                        <td class="text-center">${req.ma_benh_nhan || ''}</td>
                        <td>${req.ho_ten || ''}</td>
                        <td class="text-center">${req.tuoi || ''}</td>
                        <td class="text-center">${req.gioi_tinh || ''}</td>
                        <td>${req.yeu_cau || ''}</td>
                        <td>${formatDateTime(req.ngay_cap_nhat || req.ngay_tao)}</td>
                        <td class="text-center">
                            <span class="badge ${getStatusBadge(req.trang_thai)}">
                                ${req.trang_thai || 'Đã yêu cầu'}
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-info btn-sm" onclick="viewUltrasoundDetail(${req.id})">
                                <i class="fas fa-eye me-1"></i>Xem
                            </button>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-success btn-sm" onclick="returnUltrasoundResult(${req.id})">
                                <i class="fas fa-reply me-1"></i>Trả kết quả
                            </button>
                        </td>
                    </tr>
                `).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">Không có dữ liệu</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading ultrasound requests:', error);
                document.querySelector('#sieuamRequestedTable tbody').innerHTML =
                    '<tr><td colspan="10" class="text-center text-danger">Lỗi tải dữ liệu</td></tr>';
            });
    }

    // View ultrasound detail
    function viewUltrasoundDetail(id) {
        fetch(`./?action=get_ultrasound_result&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.result) {
                    const result = data.result;
                    document.getElementById('vs_clinic_name').value = 'PHÒNG KHÁM ĐA KHOA THINHVIET';
                    document.getElementById('vs_phone').value = '0777871608';
                    document.getElementById('vs_quan').value = 'Gò Vấp';
                    document.getElementById('vs_patient_name').value = result.ho_ten || '';
                    document.getElementById('vs_patient_age').value = result.tuoi || '';
                    document.getElementById('vs_patient_gender').value = result.gioi_tinh || '';
                    document.getElementById('vs_address').value = result.dia_chi || 'Gò Vấp';
                    document.getElementById('vs_patient_type').value = result.doi_tuong || '';
                    document.getElementById('vs_insurance_number').value = result.so_the_bhyt || '';
                    document.getElementById('vs_diagnosis').value = result.chan_doan || '';
                    document.getElementById('vs_request').value = result.yeu_cau || '';

                    // Set date
                    const date = new Date(result.ngay_cap_nhat || result.ngay_tao || Date.now());
                    const timeStr = date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    const vsOrderTime = document.getElementById('vs_order_time');
                    if (vsOrderTime) vsOrderTime.value = timeStr;
                    document.getElementById('vs_day').value = date.getDate().toString().padStart(2, '0');
                    document.getElementById('vs_month').value = (date.getMonth() + 1).toString().padStart(2, '0');
                    document.getElementById('vs_year').value = date.getFullYear();
                    document.getElementById('vs_doctor').value = result.bac_si_kham || '';

                    new bootstrap.Modal(document.getElementById('sieuamDetailModal')).show();
                } else {
                    alert('Không tìm thấy thông tin siêu âm');
                }
            })
            .catch(error => {
                console.error('Error loading ultrasound detail:', error);
                alert('Lỗi tải dữ liệu');
            });
    }

    // Return ultrasound result
    function returnUltrasoundResult(id) {
        currentUltrasoundId = id;

        fetch(`./?action=get_ultrasound_result&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.result) {
                    const result = data.result;

                    // Fill patient information
                    document.getElementById('ur_id').textContent = result.ma_benh_nhan || '*8307791*';
                    document.getElementById('ur_name').textContent = result.ho_ten || '';
                    document.getElementById('ur_age').textContent = result.tuoi || '';
                    document.getElementById('ur_gender').textContent = result.gioi_tinh || '';
                    document.getElementById('ur_address').textContent = result.dia_chi || 'Gò Vấp';
                    document.getElementById('ur_chan_doan').textContent = result.chan_doan || '';
                    document.getElementById('ur_bs_chi_dinh').textContent = result.bac_si_kham || '';
                    document.getElementById('ur_phieu_chi_dinh').textContent = result.id || '';

                    // Set examination area from ultrasound request
                    document.getElementById('ur_vung_khao_sat').textContent = result.yeu_cau ||
                        'SIÊU ÂM BỤNG TỔNG QUÁT MÀU';

                    // Parse date
                    const now = new Date(result.ngay_cap_nhat || result.ngay_tao || new Date());
                    document.getElementById('ur_date').textContent = now.getDate().toString().padStart(2, '0') + '/' +
                        (now.getMonth() + 1).toString().padStart(2, '0') + '/' +
                        now.getFullYear();
                    document.getElementById('ur_time').textContent = now.getHours().toString().padStart(2, '0') + ':' +
                        now.getMinutes().toString().padStart(2, '0');

                    // Set doctor name and current date
                    document.getElementById('ur_bac_si_doc').textContent =
                        <?php echo json_encode($sieuamDoctorSessionName); ?>;
                    const today = new Date();
                    document.getElementById('ur_day').value = today.getDate().toString().padStart(2, '0');
                    document.getElementById('ur_month').value = (today.getMonth() + 1).toString().padStart(2, '0');
                    document.getElementById('ur_year').value = today.getFullYear();

                    // Don't clear data here - let loadSavedUltrasoundResult handle it

                    // Setup auto bullet functionality
                    setTimeout(() => {
                        setupAutoBullet();
                    }, 100);

                    // Setup image upload functionality
                    setupImageUpload();

                    // Load saved data if exists
                    console.log('Loading saved data for currentUltrasoundId:', currentUltrasoundId);
                    console.log('About to call loadSavedUltrasoundResult with:', currentUltrasoundId);
                    loadSavedUltrasoundResult(currentUltrasoundId);

                    new bootstrap.Modal(document.getElementById('sieuamReturnModal')).show();
                } else {
                    alert('Không tìm thấy thông tin siêu âm');
                }
            })
            .catch(error => {
                console.error('Error loading ultrasound result:', error);
                alert('Lỗi tải dữ liệu');
            });
    }

    // Save ultrasound result
    function saveUltrasoundResult() {
        if (!currentUltrasoundId) {
            alert('Không tìm thấy ID siêu âm');
            return;
        }

        const ketQuaKhaoSat = document.getElementById('ur_ket_qua_khao_sat').value;
        const ketLuan = document.getElementById('ur_ket_luan').value;

        if (!ketQuaKhaoSat.trim() || !ketLuan.trim()) {
            alert('Vui lòng nhập đầy đủ kết quả khảo sát và kết luận');
            return;
        }

        const formData = new URLSearchParams({
            phieu_id: currentUltrasoundId,
            ket_qua_khao_sat: ketQuaKhaoSat,
            ket_luan: ketLuan
        });

        fetch('./?action=save_sieu_am_result', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Lưu ket_qua_id để upload ảnh
                    window.currentKetQuaId = data.ket_qua_id;
                    // Không đóng modal để có thể upload ảnh hoặc tiếp tục chỉnh sửa
                    loadUltrasoundRequests();
                    loadStats();
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể lưu kết quả'));
                }
            })
            .catch(error => {
                console.error('Error saving ultrasound result:', error);
                alert('Lỗi kết nối');
            });
    }

    // Print ultrasound result
    function printUltrasoundResult() {
        // Create a copy of the report content and replace input values with text
        const reportElement = document.querySelector('.report').cloneNode(true);
        console.log('Original report element:', document.querySelector('.report'));
        console.log('Cloned report element:', reportElement);

        // Hide file input and upload-related elements
        const fileInput = reportElement.querySelector('#ur_image_upload');
        if (fileInput) {
            fileInput.parentNode.style.display = 'none';
        }

        const helpText = reportElement.querySelector('.text-muted');
        if (helpText) {
            helpText.parentNode.style.display = 'none';
        }

        // Hide the entire upload section label
        const uploadLabel = reportElement.querySelector('label[for="ur_image_upload"]');
        if (uploadLabel) {
            uploadLabel.style.display = 'none';
        }

        // Hide all upload buttons and badges
        const uploadButtons = reportElement.querySelectorAll('button[onclick*="uploadSingleImage"]');
        uploadButtons.forEach(button => {
            button.parentNode.style.display = 'none';
        });

        const badges = reportElement.querySelectorAll('.badge');
        badges.forEach(badge => {
            badge.style.display = 'none';
        });

        // Hide delete buttons
        const deleteButtons = reportElement.querySelectorAll('button[onclick*="removeImage"]');
        deleteButtons.forEach(button => {
            button.style.display = 'none';
        });

        // Hide filename displays
        const filenameDisplays = reportElement.querySelectorAll('.position-absolute.bottom-0');
        filenameDisplays.forEach(display => {
            display.style.display = 'none';
        });

        // Hide all card bodies that contain buttons
        const cardBodies = reportElement.querySelectorAll('.card-body');
        cardBodies.forEach(body => {
            body.style.display = 'none';
        });

        // Show only image containers that have actual images
        const imageContainers = reportElement.querySelectorAll('.col-md-3, .col-sm-4, .col-6');
        console.log('Found image containers:', imageContainers.length); // Debug

        // Debug: Check all images in the cloned element
        const allImages = reportElement.querySelectorAll('img');
        console.log('All images in cloned element:', allImages.length);
        allImages.forEach((img, index) => {
            console.log(`Image ${index}:`, img.src, img.style.display);
            // Force show image with inline styles
            img.style.display = 'block';
            img.style.visibility = 'visible';
            img.style.opacity = '1';
            img.style.maxWidth = '100%';
            img.style.height = 'auto';
        });

        imageContainers.forEach((container, index) => {
            const hasImage = container.querySelector('img[src*="uploads"]');
            console.log(`Container ${index}:`, hasImage ? 'HAS IMAGE' : 'NO IMAGE'); // Debug
            if (hasImage) {
                // Force show container with image
                container.style.display = 'block';
                container.style.visibility = 'visible';
                container.style.opacity = '1';
            } else {
                container.style.display = 'none';
            }
        });

        // Hide only the upload controls, not the entire section
        const fileInputs = reportElement.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            // Hide the file input and its immediate container
            const container = input.closest('.mb-2');
            if (container) {
                container.style.display = 'none';
            }
            // Also hide the input itself
            input.style.display = 'none';
        });

        // Hide help text for file uploads
        const helpTexts = reportElement.querySelectorAll('small.text-muted');
        helpTexts.forEach(text => {
            const textContent = text.textContent.toLowerCase();
            if (textContent.includes('chọn nhiều') ||
                textContent.includes('jpg') ||
                textContent.includes('png') ||
                textContent.includes('gif')) {
                text.style.display = 'none';
            }
        });

        // Debug: Check if "HÌNH ẢNH SIÊU ÂM" label is still visible
        const imageLabels = reportElement.querySelectorAll('label');
        imageLabels.forEach((label, index) => {
            if (label.textContent.includes('HÌNH ẢNH SIÊU ÂM')) {
                console.log(`Found HÌNH ẢNH SIÊU ÂM label at index ${index}:`, label.textContent);
                console.log('Label display style:', label.style.display);
            }
        });

        // Force show important section labels
        const importantLabels = reportElement.querySelectorAll('label.fw-bold');
        importantLabels.forEach(label => {
            label.style.display = 'block';
            label.style.visibility = 'visible';
            label.style.opacity = '1';
            console.log('Force showing label:', label.textContent);
        });



        // Replace input values with their text content for printing
        const inputs = reportElement.querySelectorAll('input[type="text"]');
        inputs.forEach(input => {
            const textNode = document.createTextNode(input.value || '');
            input.parentNode.replaceChild(textNode, input);
        });

        // Replace textareas with their content (preserve line breaks)
        const textareas = reportElement.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            const value = textarea.value || '';
            console.log('Textarea value:', value); // Debug log
            const div = document.createElement('div');
            div.style.whiteSpace = 'pre-wrap'; // Preserve line breaks
            div.style.minHeight = '20px';
            div.style.borderBottom = '1px dotted #333';
            div.textContent = value;
            textarea.parentNode.replaceChild(div, textarea);
        });

        const printContent = reportElement.innerHTML;
        console.log('Final print content:', printContent); // Debug
        console.log('Final print content length:', printContent.length); // Debug

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
        <html>
            <head>
                <title>KẾT QUẢ SIÊU ÂM MÀU</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .report { font-family: "Times New Roman", serif; padding: 18px; }
                    .title { font-weight: bold; text-transform: uppercase; text-align: center; letter-spacing: .5px; font-size: 18px; margin-bottom: 6px; color: red; }
                    .hr { border-top: 2px solid #000; margin: 10px 0; }
                    .row-line { display: flex; gap: 8px; margin-bottom: 6px; font-size: 15px; }
                    .label { min-width: 150px; font-weight: bold; }
                    .dots { flex: 0 0 auto; }
                    .value { flex: 1; border-bottom: 1px dotted #333; min-height: 20px; }
                    /* Preserve line breaks in text content */
                    div[style*="white-space: pre-wrap"] { 
                        white-space: pre-wrap !important; 
                        border-bottom: 1px dotted #333; 
                        min-height: 20px; 
                    }
                    .section { margin-top: 10px; margin-bottom: 6px; font-weight: bold; text-transform: uppercase; color: blue; }
                    .signature { min-width: 260px; }
                    .conclusion { font-weight: bold; color: blue; }
                    .ultrasound-image { width: 100%; max-width: 400px; height: 300px; border: 1px solid #ccc; margin: 10px 0; }
                    .text-center { text-align: center; }
                    .text-right { text-align: right; }
                    .mb-2 { margin-bottom: 0.5rem; }
                    .mb-3 { margin-bottom: 1rem; }
                    .mt-2 { margin-top: 0.5rem; }
                    .mt-3 { margin-top: 1rem; }
                    .d-inline-block { display: inline-block; }
                    .d-flex { display: flex; }
                    .justify-content-between { justify-content: space-between; }
                    .align-items-end { align-items: flex-end; }
                    /* Print-specific styles for images */
                    .row { display: flex; flex-wrap: wrap; margin: -5px; }
                    .col-md-3, .col-sm-4, .col-6 { flex: 0 0 25%; padding: 5px; box-sizing: border-box; }
                    .card { border: 1px solid #ddd; margin: 0; }
                    .card-img-top { width: 100%; height: 150px; object-fit: cover; display: block !important; }
                    .card-body { display: none !important; }
                    .position-relative { position: relative; }
                    /* Ensure images are visible in print */
                    img { display: block !important; max-width: 100%; height: auto; }
                    .ultrasound-image { display: block !important; }
                    .position-absolute { display: none !important; }
                    /* Hide all buttons, badges, and UI elements in print - but NOT images and important labels */
                    button, .btn, .badge, input[type="file"], .text-muted, small { display: none !important; }
                    /* Show important section labels */
                    label.fw-bold { display: block !important; }
                    /* Hide other labels */
                    label:not(.fw-bold) { display: none !important; }
                    /* Hide filename overlays */
                    .position-absolute.bottom-0, .position-absolute.top-0 { display: none !important; }
                    /* Force show images in print - override all other rules */
                    .card-img-top, img[src*="uploads"], .ultrasound-image { 
                        display: block !important; 
                        visibility: visible !important;
                        opacity: 1 !important;
                        max-width: 100% !important;
                        height: auto !important;
                    }
                    /* Show image containers */
                    .col-md-3, .col-sm-4, .col-6 { display: block !important; }
                    /* Show image cards but hide their bodies */
                    .card { display: block !important; }
                    .card-body { display: none !important; }
                    /* Show only the image part of cards */
                    .card-img-top { display: block !important; }
                    /* Hide upload sections - handled by JavaScript */
                    /* .mb-2:has(input[type="file"]), .mb-3:has(input[type="file"]) { display: none !important; } */
                    /* Show image gallery rows */
                    .row { display: flex !important; }
                    /* Hide checkbox-like elements */
                    input[type="checkbox"], .form-check-input { display: none !important; }
                </style>
            </head>
            <body>
                <div class="report">
                    ${printContent}
                </div>
            </body>
        </html>
    `);
        printWindow.document.close();
        printWindow.print();
    }

    // Auto bullet point functionality
    function setupAutoBullet() {
        const textarea = document.getElementById('ur_ket_qua_khao_sat');
        if (textarea) {
            // Remove existing event listeners
            textarea.removeEventListener('keydown', handleBulletKeydown);
            textarea.addEventListener('keydown', handleBulletKeydown);
        }
    }

    function handleBulletKeydown(e) {
        if (e.key === 'Enter') {
            e.preventDefault();

            const start = this.selectionStart;
            const end = this.selectionEnd;
            const value = this.value;

            // Get current line
            const beforeCursor = value.substring(0, start);
            const lines = beforeCursor.split('\n');
            const currentLine = lines[lines.length - 1];

            // Check if current line starts with bullet point
            const hasBullet = currentLine.trim().startsWith('-');

            // If first line and no bullet (except default "- "), add bullet to current line first
            if (lines.length === 1 && !hasBullet && currentLine.trim() !== '' && currentLine.trim() !== '-') {
                const newText = '- ' + value.substring(0, start) + '\n- ' + value.substring(end);
                this.value = newText;
                const newCursorPos = start + 3; // After "- " + original content + "\n- "
                this.setSelectionRange(newCursorPos, newCursorPos);
            } else {
                // Insert new line with bullet point
                const newText = value.substring(0, start) + '\n- ' + value.substring(end);
                this.value = newText;
                const newCursorPos = start + 3; // After "\n- "
                this.setSelectionRange(newCursorPos, newCursorPos);
            }
        }
    }

    // Image upload functionality
    function setupImageUpload() {
        const fileInput = document.getElementById('ur_image_upload');
        if (fileInput) {
            fileInput.addEventListener('change', handleImageUpload);
        }
    }

    // Handle image upload
    function handleImageUpload(event) {
        const files = event.target.files;
        const gallery = document.getElementById('ur_image_gallery');

        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    addImageToGallery(e.target.result, file.name, file);
                };
                reader.readAsDataURL(file);
            }
        });

        // Clear the input
        event.target.value = '';
    }

    // Add image to gallery
    function addImageToGallery(src, filename, fileObject = null) {
        const gallery = document.getElementById('ur_image_gallery');
        const imageId = 'img_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

        // Lưu file object vào data attribute nếu có
        const fileDataAttr = fileObject ? `data-file='${JSON.stringify({
        name: fileObject.name,
        size: fileObject.size,
        type: fileObject.type
    })}'` : '';

        const imageHtml = `
        <div class="col-md-3 col-sm-4 col-6" id="${imageId}_container">
            <div class="card">
                <div class="position-relative">
                    <img src="${src}" class="card-img-top" style="height: 150px; object-fit: cover; cursor: pointer;" 
                         onclick="zoomImage('${imageId}', '${src}')" alt="${filename}">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" 
                            onclick="removeImage('${imageId}')" style="padding: 2px 6px;">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="position-absolute bottom-0 start-0 bg-dark bg-opacity-75 text-white p-1" style="font-size: 10px;">
                        ${filename.length > 15 ? filename.substring(0, 15) + '...' : filename}
                    </div>
                    ${fileObject ? `
                    <div class="position-absolute top-0 start-0 m-1">
                        <span class="badge bg-warning">Chưa upload</span>
                    </div>
                    ` : ''}
                </div>
                ${fileObject ? `
                <div class="card-body p-2">
                    <button type="button" class="btn btn-success btn-sm w-100" onclick="uploadSingleImage('${imageId}', this)">
                        <i class="fas fa-upload me-1"></i>Upload
                    </button>
                </div>
                ` : ''}
            </div>
        </div>
    `;

        gallery.insertAdjacentHTML('beforeend', imageHtml);

        // Lưu file object vào global storage
        if (fileObject) {
            window.pendingImages = window.pendingImages || {};
            window.pendingImages[imageId] = fileObject;
        }
    }

    // Remove image from gallery
    function removeImage(imageId) {
        const container = document.getElementById(`${imageId}_container`);
        if (container) {
            container.remove();
        }

        // Xóa khỏi pending images
        if (window.pendingImages && window.pendingImages[imageId]) {
            delete window.pendingImages[imageId];
        }
    }

    // Upload single image
    function uploadSingleImage(imageId, buttonElement) {
        if (!window.pendingImages || !window.pendingImages[imageId]) {
            alert('Không tìm thấy file để upload');
            return;
        }

        const file = window.pendingImages[imageId];

        // Disable button và hiển thị loading
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang upload...';

        // Nếu chưa có ket_qua_id, tạo kết quả siêu âm trước
        if (!window.currentKetQuaId) {
            createSieuAmResultFirst(imageId, file, buttonElement);
        } else {
            uploadImageDirect(imageId, file, buttonElement);
        }
    }

    // Tạo kết quả siêu âm trước khi upload ảnh
    function createSieuAmResultFirst(imageId, file, buttonElement) {
        if (!currentUltrasoundId) {
            alert('Không tìm thấy ID phiếu yêu cầu siêu âm');
            buttonElement.disabled = false;
            buttonElement.innerHTML = '<i class="fas fa-upload me-1"></i>Upload';
            return;
        }

        const ketQuaKhaoSat = document.getElementById('ur_ket_qua_khao_sat').value || 'Chưa nhập kết quả khảo sát';
        const ketLuan = document.getElementById('ur_ket_luan').value || 'Chưa nhập kết luận';

        const formData = new URLSearchParams({
            phieu_id: currentUltrasoundId,
            ket_qua_khao_sat: ketQuaKhaoSat,
            ket_luan: ketLuan
        });

        fetch('./?action=save_sieu_am_result', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.currentKetQuaId = data.ket_qua_id;
                    uploadImageDirect(imageId, file, buttonElement);
                } else {
                    alert('Lỗi tạo kết quả: ' + data.message);
                    buttonElement.disabled = false;
                    buttonElement.innerHTML = '<i class="fas fa-upload me-1"></i>Upload';
                }
            })
            .catch(error => {
                console.error('Error creating result:', error);
                alert('Lỗi kết nối');
                buttonElement.disabled = false;
                buttonElement.innerHTML = '<i class="fas fa-upload me-1"></i>Upload';
            });
    }

    // Upload ảnh trực tiếp
    function uploadImageDirect(imageId, file, buttonElement) {
        const formData = new FormData();
        formData.append('ket_qua_id', window.currentKetQuaId);
        formData.append('images[]', file);

        fetch('./?action=upload_sieu_am_images', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Thay đổi badge thành "Đã upload"
                    const badge = document.querySelector(`#${imageId}_container .badge`);
                    if (badge) {
                        badge.className = 'badge bg-success';
                        badge.textContent = 'Đã upload';
                    }

                    // Ẩn nút upload
                    buttonElement.style.display = 'none';

                    // Xóa khỏi pending images
                    delete window.pendingImages[imageId];

                    alert('Upload ảnh thành công');
                } else {
                    alert('Lỗi upload: ' + data.message);
                    buttonElement.disabled = false;
                    buttonElement.innerHTML = '<i class="fas fa-upload me-1"></i>Upload';
                }
            })
            .catch(error => {
                console.error('Error uploading image:', error);
                alert('Lỗi kết nối');
                buttonElement.disabled = false;
                buttonElement.innerHTML = '<i class="fas fa-upload me-1"></i>Upload';
            });
    }

    // Load saved ultrasound result
    function loadSavedUltrasoundResult(phieuId) {
        console.log('loadSavedUltrasoundResult called with phieuId:', phieuId);
        if (!phieuId) {
            // Clear data if no phieuId (new result)
            document.getElementById('ur_ket_qua_khao_sat').value = '- ';
            document.getElementById('ur_ket_luan').value = '';
            document.getElementById('ur_image_gallery').innerHTML = '';
            window.currentKetQuaId = null;
            return;
        }

        console.log('Fetching data for phieuId:', phieuId);
        fetch(`./?action=get_sieu_am_result&phieu_id=${phieuId}`)
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Loaded ultrasound result:', data);
                if (data.success && data.result) {
                    // Load saved result data
                    document.getElementById('ur_ket_qua_khao_sat').value = data.result.ket_qua_khao_sat || '- ';
                    document.getElementById('ur_ket_luan').value = data.result.ket_luan || '';

                    // Set doctor name from database
                    if (data.result.bac_si_sieu_am) {
                        document.getElementById('ur_bac_si_doc').textContent = data.result.bac_si_sieu_am;
                    }

                    // Set ket_qua_id for future uploads
                    window.currentKetQuaId = data.result.id;

                    // Clear existing images first
                    document.getElementById('ur_image_gallery').innerHTML = '';

                    // Load saved images if any
                    if (data.images && data.images.length > 0) {
                        console.log('Loading saved images:', data.images);
                        loadSavedUltrasoundImages(data.images);
                    } else {
                        console.log('No saved images found');
                    }
                } else {
                    // No saved data found, use defaults
                    document.getElementById('ur_ket_qua_khao_sat').value = '- ';
                    document.getElementById('ur_ket_luan').value = '';
                    document.getElementById('ur_image_gallery').innerHTML = '';
                    window.currentKetQuaId = null;
                }
            })
            .catch(error => {
                console.error('Error loading saved ultrasound result:', error);
                // On error, use defaults
                document.getElementById('ur_ket_qua_khao_sat').value = '- ';
                document.getElementById('ur_ket_luan').value = '';
                document.getElementById('ur_image_gallery').innerHTML = '';
                window.currentKetQuaId = null;
            });
    }

    // Load saved ultrasound images
    function loadSavedUltrasoundImages(images) {
        const gallery = document.getElementById('ur_image_gallery');

        images.forEach(image => {
            const imageId = 'saved_img_' + image.id;

            const imageHtml = `
            <div class="col-md-3 col-sm-4 col-6" id="${imageId}_container">
                <div class="card">
                    <div class="position-relative">
                        <img src="${image.duong_dan}" class="card-img-top" style="height: 150px; object-fit: cover; cursor: pointer;" 
                             onclick="zoomImage('${imageId}', '${image.duong_dan}')" alt="${image.ten_file}">
                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" 
                                onclick="removeSavedImage('${image.id}', '${imageId}')" style="padding: 2px 6px;">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="position-absolute bottom-0 start-0 bg-dark bg-opacity-75 text-white p-1" style="font-size: 10px;">
                            ${image.ten_file.length > 15 ? image.ten_file.substring(0, 15) + '...' : image.ten_file}
                        </div>
                        <div class="position-absolute top-0 start-0 m-1">
                            <span class="badge bg-success">Đã lưu</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

            gallery.insertAdjacentHTML('beforeend', imageHtml);
        });
    }

    // Remove saved image
    function removeSavedImage(imageId, containerId) {
        if (!confirm('Bạn có chắc chắn muốn xóa ảnh này?')) {
            return;
        }

        const formData = new URLSearchParams({
            image_id: imageId
        });

        fetch('./?action=delete_sieu_am_image', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove from gallery
                    const container = document.getElementById(`${containerId}_container`);
                    if (container) {
                        container.remove();
                    }
                    alert('Xóa ảnh thành công');
                } else {
                    alert('Lỗi xóa ảnh: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error deleting image:', error);
                alert('Lỗi kết nối');
            });
    }

    // Complete ultrasound result
    function completeUltrasoundResult() {
        if (!currentUltrasoundId) {
            alert('Không tìm thấy ID siêu âm');
            return;
        }

        // Xác nhận trước khi hoàn thành
        if (!confirm('Bạn có chắc chắn muốn hoàn thành phiếu siêu âm này?')) {
            return;
        }

        const formData = new URLSearchParams({
            phieu_id: currentUltrasoundId
        });

        fetch('./?action=complete_sieu_am_result', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Hoàn thành phiếu siêu âm thành công');
                    bootstrap.Modal.getInstance(document.getElementById('sieuamReturnModal')).hide();
                    loadUltrasoundRequests();
                    loadStats();
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể hoàn thành phiếu'));
                }
            })
            .catch(error => {
                console.error('Error completing ultrasound result:', error);
                alert('Lỗi kết nối');
            });
    }

    // Zoom image functionality
    function zoomImage(imageId, src) {
        // Remove existing zoom modal if any
        const existingModal = document.getElementById('imageZoomModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Create zoom modal
        const modalHtml = `
        <div class="modal fade" id="imageZoomModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Xem ảnh siêu âm</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center p-0">
                        <div id="imageContainer" style="position: relative; overflow: hidden; max-height: 80vh;">
                            <img id="zoomImage" src="${src}" style="max-width: 100%; max-height: 100%; transition: transform 0.3s ease;">
                        </div>
                        <div class="position-absolute top-0 end-0 m-3">
                            <div class="btn-group-vertical">
                                <button type="button" class="btn btn-light btn-sm" onclick="zoomIn()" title="Phóng to">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button type="button" class="btn btn-light btn-sm" onclick="zoomOut()" title="Thu nhỏ">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-light btn-sm" onclick="resetZoom()" title="Đặt lại">
                                    <i class="fas fa-expand-arrows-alt"></i>
                                </button>
                                <button type="button" class="btn btn-light btn-sm" onclick="toggleFullscreen()" title="Toàn màn hình">
                                    <i class="fas fa-expand"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);

        const modal = new bootstrap.Modal(document.getElementById('imageZoomModal'));
        modal.show();

        // Setup zoom functionality
        setupZoomControls();
    }

    // Setup zoom controls
    function setupZoomControls() {
        let scale = 1;
        let isPanning = false;
        let startPoint = {
            x: 0,
            y: 0
        };
        let endPoint = {
            x: 0,
            y: 0
        };

        const image = document.getElementById('zoomImage');
        const container = document.getElementById('imageContainer');

        if (!image || !container) return;

        // Mouse wheel zoom
        container.addEventListener('wheel', function(e) {
            e.preventDefault();
            const delta = e.deltaY > 0 ? 0.9 : 1.1;
            scale *= delta;
            scale = Math.max(0.1, Math.min(5, scale));
            image.style.transform = `scale(${scale})`;
        });

        // Mouse drag to pan
        container.addEventListener('mousedown', function(e) {
            if (scale > 1) {
                isPanning = true;
                startPoint = {
                    x: e.clientX,
                    y: e.clientY
                };
                container.style.cursor = 'grabbing';
            }
        });

        document.addEventListener('mousemove', function(e) {
            if (isPanning && scale > 1) {
                endPoint = {
                    x: e.clientX,
                    y: e.clientY
                };
                const deltaX = endPoint.x - startPoint.x;
                const deltaY = endPoint.y - startPoint.y;

                const currentTransform = image.style.transform || 'scale(1)';
                const currentTranslate = currentTransform.match(/translate\(([^)]+)\)/) || ['', '0px, 0px'];
                const translateValues = currentTranslate[1].split(',').map(v => parseFloat(v.trim()));

                image.style.transform =
                    `translate(${translateValues[0] + deltaX}px, ${translateValues[1] + deltaY}px) scale(${scale})`;
                startPoint = endPoint;
            }
        });

        document.addEventListener('mouseup', function() {
            isPanning = false;
            container.style.cursor = scale > 1 ? 'grab' : 'default';
        });

        // Touch support for mobile
        container.addEventListener('touchstart', function(e) {
            if (e.touches.length === 2) {
                // Pinch to zoom
                const touch1 = e.touches[0];
                const touch2 = e.touches[1];
                const distance = Math.sqrt(Math.pow(touch2.clientX - touch1.clientX, 2) + Math.pow(touch2.clientY -
                    touch1.clientY, 2));
                container.dataset.initialDistance = distance;
                container.dataset.initialScale = scale;
            }
        });

        container.addEventListener('touchmove', function(e) {
            e.preventDefault();
            if (e.touches.length === 2) {
                const touch1 = e.touches[0];
                const touch2 = e.touches[1];
                const distance = Math.sqrt(Math.pow(touch2.clientX - touch1.clientX, 2) + Math.pow(touch2.clientY -
                    touch1.clientY, 2));
                const initialDistance = parseFloat(container.dataset.initialDistance);
                const initialScale = parseFloat(container.dataset.initialScale);

                scale = initialScale * (distance / initialDistance);
                scale = Math.max(0.1, Math.min(5, scale));
                image.style.transform = `scale(${scale})`;
            }
        });

        // Global zoom functions
        window.zoomIn = function() {
            scale *= 1.2;
            scale = Math.min(5, scale);
            image.style.transform = `scale(${scale})`;
        };

        window.zoomOut = function() {
            scale *= 0.8;
            scale = Math.max(0.1, scale);
            image.style.transform = `scale(${scale})`;
        };

        window.resetZoom = function() {
            scale = 1;
            image.style.transform = 'scale(1) translate(0px, 0px)';
        };

        window.toggleFullscreen = function() {
            if (!document.fullscreenElement) {
                container.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        };
    }

    // Helper functions
    function formatDateTime(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleString('vi-VN');
    }

    function formatDate(date) {
        return date.toLocaleDateString('vi-VN');
    }

    function formatTime(date) {
        return date.toLocaleTimeString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function getStatusBadge(status) {
        switch (status) {
            case 'Đã yêu cầu':
                return 'bg-warning';
            case 'Hoàn thành':
                return 'bg-success';
            case 'Đã thanh toán':
                return 'bg-info';
            default:
                return 'bg-secondary';
        }
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Set default date to today
        document.getElementById('sieuam_date').value = new Date().toISOString().split('T')[0];

        // Load initial data
        loadStats();
        loadUltrasoundRequests();

        // Filter button
        document.getElementById('sieuam_filter_btn').addEventListener('click', function() {
            loadUltrasoundRequests();
            loadStats();
        });

        // Enter key on search
        document.getElementById('sieuam_name').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                loadUltrasoundRequests();
                loadStats();
            }
        });

        // Auto refresh every 30 seconds
        setInterval(() => {
            loadStats();
            loadUltrasoundRequests();
        }, 30000);
    });
</script>