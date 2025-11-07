// Global variables
let currentPage = 1;
let currentLimit = 15;
let currentFilters = {
    search: '',
    search_type: '',
    selected_date: ''
};

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadRecords();
    
    // Bind enter key on search input
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchRecords();
        }
    });
});

/**
 * Load records with current filters
 */
function loadRecords(page = 1) {
    currentPage = page;
    
    const filters = {
        search: currentFilters.search,
        search_type: currentFilters.search_type,
        selected_date: currentFilters.selected_date,
        page: currentPage,
        limit: currentLimit
    };

    // Show loading
    document.getElementById('loadingIndicator').style.display = 'block';
    document.getElementById('recordsTable').style.display = 'none';
    document.getElementById('emptyState').style.display = 'none';

    fetch('./?action=get_doctor_medical_records', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(filters)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        document.getElementById('loadingIndicator').style.display = 'none';
        console.log('Response data:', data);
        
        if (data.success) {
            if (data.data && data.data.length > 0) {
                renderRecords(data.data);
                renderPagination(data.total_pages, data.page);
                updateStats(data.total, data.page, data.total_pages);
                document.getElementById('recordsTable').style.display = 'block';
                document.getElementById('emptyState').style.display = 'none';
            } else {
                document.getElementById('emptyState').style.display = 'block';
                document.getElementById('recordsTable').style.display = 'none';
                updateStats(data.total || 0, data.page || 1, data.total_pages || 1);
            }
        } else {
            console.error('API Error:', data.message);
            document.getElementById('emptyState').style.display = 'block';
            document.getElementById('recordsTable').style.display = 'none';
            showAlert(data.message || 'Có lỗi xảy ra khi tải dữ liệu', 'danger');
        }
    })
    .catch(error => {
        console.error('Error loading records:', error);
        document.getElementById('loadingIndicator').style.display = 'none';
        document.getElementById('emptyState').style.display = 'block';
        document.getElementById('recordsTable').style.display = 'none';
        showAlert('Lỗi khi tải dữ liệu: ' + error.message, 'danger');
    });
}

/**
 * Render records table
 */
function renderRecords(records) {
    const tbody = document.getElementById('recordsTableBody');
    tbody.innerHTML = '';

    records.forEach((record, index) => {
        const row = document.createElement('tr');
        const stt = (currentPage - 1) * currentLimit + index + 1;
        
        row.innerHTML = `
            <td>${stt}</td>
            <td>${record.ngay_kham_formatted || '-'}</td>
            <td>${record.gio_kham_formatted || '-'}</td>
            <td><span class="badge bg-info">${record.ma_benh_nhan || '-'}</span></td>
            <td><strong>${escapeHtml(record.ten_benh_nhan || '-')}</strong></td>
            <td>${record.tuoi || '-'} tuổi</td>
            <td>${record.gioi_tinh || '-'}</td>
            <td>${record.so_dien_thoai || '-'}</td>
            <td>${record.cccd || '-'}</td>
            <td><small>${escapeHtml(record.chan_doan_vao_vien || record.tom_tat_lam_sang || '-')}</small></td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="viewDetail(${record.lich_hen_id || record.exam_id})" title="Xem chi tiết">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

/**
 * Render pagination
 */
function renderPagination(totalPages, currentPage) {
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';

    if (totalPages <= 1) return;

    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<a class="page-link" href="#" onclick="loadRecords(${currentPage - 1}); return false;">Trước</a>`;
    pagination.appendChild(prevLi);

    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    if (startPage > 1) {
        const firstLi = document.createElement('li');
        firstLi.className = 'page-item';
        firstLi.innerHTML = `<a class="page-link" href="#" onclick="loadRecords(1); return false;">1</a>`;
        pagination.appendChild(firstLi);
        
        if (startPage > 2) {
            const ellipsis = document.createElement('li');
            ellipsis.className = 'page-item disabled';
            ellipsis.innerHTML = '<span class="page-link">...</span>';
            pagination.appendChild(ellipsis);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === currentPage ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="#" onclick="loadRecords(${i}); return false;">${i}</a>`;
        pagination.appendChild(li);
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            const ellipsis = document.createElement('li');
            ellipsis.className = 'page-item disabled';
            ellipsis.innerHTML = '<span class="page-link">...</span>';
            pagination.appendChild(ellipsis);
        }
        
        const lastLi = document.createElement('li');
        lastLi.className = 'page-item';
        lastLi.innerHTML = `<a class="page-link" href="#" onclick="loadRecords(${totalPages}); return false;">${totalPages}</a>`;
        pagination.appendChild(lastLi);
    }

    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = `<a class="page-link" href="#" onclick="loadRecords(${currentPage + 1}); return false;">Sau</a>`;
    pagination.appendChild(nextLi);
}

/**
 * Update statistics
 */
function updateStats(total, page, totalPages) {
    document.getElementById('statTotal').textContent = total;
    document.getElementById('statPage').textContent = page;
    document.getElementById('statTotalPages').textContent = totalPages;
    document.getElementById('statLimit').textContent = currentLimit;
}

/**
 * Search records
 */
function searchRecords() {
    const searchType = document.getElementById('searchType').value;
    const searchInput = document.getElementById('searchInput').value.trim();
    const selectedDate = document.getElementById('selectedDate').value;

    // Validate search
    if (searchInput && !searchType) {
        showAlert('Vui lòng chọn loại tìm kiếm!', 'warning');
        return;
    }

    currentFilters = {
        search: searchInput,
        search_type: searchType,
        selected_date: selectedDate
    };

    loadRecords(1);
}

/**
 * Reset filters
 */
function resetFilters() {
    document.getElementById('searchType').value = '';
    document.getElementById('searchInput').value = '';
    document.getElementById('selectedDate').value = '';

    currentFilters = {
        search: '',
        search_type: '',
        selected_date: ''
    };

    loadRecords(1);
}

/**
 * Refresh records
 */
function refreshRecords() {
    loadRecords(currentPage);
}

/**
 * View detail of a medical record
 * Load view từ PHP thay vì render bằng JavaScript
 * Template chỉ cần làm 1 lần, mỗi lần gọi chỉ cần truyền data khác vào
 */
function viewDetail(lichHenId) {
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    const modalBody = document.getElementById('detailModalBody');
    
    modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Đang tải...</span></div></div>';
    modal.show();

    // Load view từ PHP thay vì render bằng JavaScript
    fetch(`./?action=render_doctor_medical_record_detail&exam_id=${lichHenId}`)
        .then(response => response.text())
        .then(html => {
            modalBody.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading detail:', error);
            modalBody.innerHTML = `<div class="alert alert-danger">Lỗi khi tải chi tiết: ${error.message}</div>`;
        });
}

/**
 * Render detail modal - Sử dụng tabs để xem từng phiếu riêng
 */
function renderDetail(data) {
    const modalBody = document.getElementById('detailModalBody');
    const exam = data.exam || {};
    
    // Lưu exam data vào window để các hàm render có thể truy cập
    window.currentExamData = exam;
    
    let html = `
        <ul class="nav nav-tabs mb-3" id="detailTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-exam" data-bs-toggle="tab" data-bs-target="#pane-exam" type="button" role="tab">
                    <i class="fas fa-stethoscope me-2"></i>Phiếu khám bệnh
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-prescription" data-bs-toggle="tab" data-bs-target="#pane-prescription" type="button" role="tab">
                    <i class="fas fa-pills me-2"></i>Đơn thuốc
                    ${data.prescription ? '<span class="badge bg-success ms-2">Có</span>' : '<span class="badge bg-secondary ms-2">Không có</span>'}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-lab" data-bs-toggle="tab" data-bs-target="#pane-lab" type="button" role="tab">
                    <i class="fas fa-vial me-2"></i>Kết quả xét nghiệm
                    ${data.lab_result ? '<span class="badge bg-success ms-2">Có</span>' : '<span class="badge bg-secondary ms-2">Không có</span>'}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-ultrasound" data-bs-toggle="tab" data-bs-target="#pane-ultrasound" type="button" role="tab">
                    <i class="fas fa-wave-square me-2"></i>Kết quả siêu âm
                    ${data.ultrasound_result ? '<span class="badge bg-success ms-2">Có</span>' : '<span class="badge bg-secondary ms-2">Không có</span>'}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-xray" data-bs-toggle="tab" data-bs-target="#pane-xray" type="button" role="tab">
                    <i class="fas fa-x-ray me-2"></i>Kết quả X-Quang
                    ${data.xray_result ? '<span class="badge bg-success ms-2">Có</span>' : '<span class="badge bg-secondary ms-2">Không có</span>'}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-receipt" data-bs-toggle="tab" data-bs-target="#pane-receipt" type="button" role="tab">
                    <i class="fas fa-receipt me-2"></i>Biên lai viện phí
                    ${data.receipt ? '<span class="badge bg-success ms-2">Có</span>' : '<span class="badge bg-secondary ms-2">Không có</span>'}
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="detailTabContent">
            <!-- Tab: Phiếu khám bệnh -->
            <div class="tab-pane fade show active" id="pane-exam" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Phiếu khám bệnh vào viện</h6>
                    </div>
                    <div class="card-body">
                        <!-- Header form -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>Sở Y tế:</strong> <input type="text" class="form-control d-inline-block w-auto" value="Thành Phố Hồ Chí Minh" readonly>
                                </div>
                                <div class="mb-2">
                                    <strong>BV:</strong> <input type="text" class="form-control d-inline-block w-auto" value="Thịnh Việt" readonly>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="mb-2">
                                    <strong>Mã bệnh nhân:</strong> <input type="text" class="form-control d-inline-block w-auto" value="${exam.ma_benh_nhan || '-'}" readonly>
                                </div>
                                <div class="mb-2">
                                    <strong>BUỒNG KHÁM BỆNH:</strong> <input type="text" class="form-control d-inline-block w-auto" value="${exam.buong_kham || '-'}" readonly>
                                </div>
                            </div>
                        </div>

                        <h5 class="text-center mb-3"><strong>PHIẾU KHÁM BỆNH VÀO VIỆN</strong></h5>

                        <!-- I. HÀNH CHÍNH -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">I. HÀNH CHÍNH</h6>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">1. Họ và tên (in hoa):</label>
                                    <input type="text" class="form-control" value="${escapeHtml((exam.ho_ten || '').toUpperCase())}" readonly style="text-transform: uppercase;">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">2. Sinh ngày:</label>
                                    <div class="row g-2">
                                        <div class="col-3">
                                            <input type="number" class="form-control" value="${exam.ngay_sinh || ''}" placeholder="Ngày" readonly>
                                        </div>
                                        <div class="col-3">
                                            <input type="number" class="form-control" value="${exam.thang_sinh || ''}" placeholder="Tháng" readonly>
                                        </div>
                                        <div class="col-3">
                                            <input type="number" class="form-control" value="${exam.nam_sinh || ''}" placeholder="Năm" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Tuổi:</label>
                                    <input type="number" class="form-control" value="${exam.tuoi || ''}" placeholder="Tuổi" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">3. Giới:</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" ${exam.gioi_tinh === 'Nam' ? 'checked' : ''} disabled>
                                        <label class="form-check-label">1. Nam</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" ${exam.gioi_tinh === 'Nữ' ? 'checked' : ''} disabled>
                                        <label class="form-check-label">2. Nữ</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">4. Nghề nghiệp:</label>
                                    <input type="text" class="form-control" value="${escapeHtml(exam.nghe_nghiep || '-')}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">5. Dân tộc:</label>
                                    <input type="text" class="form-control" value="${escapeHtml(exam.dan_toc || '-')}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">6. Ngoại kiều:</label>
                                    <input type="text" class="form-control" value="${escapeHtml(exam.ngoai_kieu || '-')}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">8. Nơi làm việc:</label>
                                    <input type="text" class="form-control" value="${escapeHtml(exam.noi_lam_viec || '-')}" readonly>
                                </div>
                            </div>

                            <!-- Địa chỉ -->
                            <div class="mt-3">
                                <label class="form-label fw-bold">7. Địa chỉ:</label>
                                <input type="text" class="form-control" value="${escapeHtml(exam.dia_chi || '-')}" readonly>
                            </div>

                            <!-- Đối tượng -->
                            <div class="mt-3">
                                <label class="form-label fw-bold">9. Đối tượng:</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" ${exam.doi_tuong_bhyt === 'BHYT' ? 'checked' : ''} disabled>
                                    <label class="form-check-label">1. BHYT</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" ${!exam.doi_tuong_bhyt || exam.doi_tuong_bhyt === 'Thu phí' ? 'checked' : ''} disabled>
                                    <label class="form-check-label">2. Thu phí</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" ${exam.doi_tuong_bhyt === 'Miễn' ? 'checked' : ''} disabled>
                                    <label class="form-check-label">3. Miễn</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" ${exam.doi_tuong_bhyt === 'Khác' ? 'checked' : ''} disabled>
                                    <label class="form-check-label">4. Khác</label>
                                </div>
                            </div>

                            <!-- BHYT -->
                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">10. BHYT giá trị đến ngày:</label>
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <input type="number" class="form-control" value="${exam.bhyt_ngay || ''}" placeholder="Ngày" readonly>
                                        </div>
                                        <div class="col-4">
                                            <input type="number" class="form-control" value="${exam.bhyt_thang || ''}" placeholder="Tháng" readonly>
                                        </div>
                                        <div class="col-4">
                                            <input type="number" class="form-control" value="${exam.bhyt_nam || ''}" placeholder="Năm" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Số thẻ BHYT:</label>
                                    <input type="text" class="form-control" value="${exam.so_the_bhyt || '-'}" readonly>
                                </div>
                            </div>

                            <!-- Thông tin liên hệ -->
                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">11. Điện thoại người báo tin:</label>
                                    <input type="text" class="form-control" value="${exam.dien_thoai_bao_tin || exam.so_dien_thoai || '-'}" readonly>
                                </div>
                            </div>

                            <!-- Thời gian khám -->
                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">13. Đến khám bệnh lúc:</label>
                                    <div class="row g-2">
                                        <div class="col-auto">
                                            <div class="input-group flex-nowrap">
                                                <input type="number" class="form-control text-center" value="${exam.gio_kham || ''}" placeholder="Giờ" readonly style="width:80px;">
                                                <span class="input-group-text">Giờ</span>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="input-group flex-nowrap">
                                                <input type="number" class="form-control text-center" value="${exam.phut_kham || ''}" placeholder="Phút" readonly style="width:80px;">
                                                <span class="input-group-text">Phút</span>
                                            </div>
                                        </div>
                                        <div class="w-100"></div>
                                        <div class="col-12">
                                            <div class="d-flex flex-nowrap gap-2">
                                                <div class="input-group flex-nowrap" style="width:auto;">
                                                    <span class="input-group-text">Ngày</span>
                                                    <input type="number" class="form-control text-center" value="${exam.ngay_kham || ''}" placeholder="Ngày" readonly style="width:80px;">
                                                </div>
                                                <div class="input-group flex-nowrap" style="width:auto;">
                                                    <span class="input-group-text">Tháng</span>
                                                    <input type="number" class="form-control text-center" value="${exam.thang_kham || ''}" placeholder="Tháng" readonly style="width:80px;">
                                                </div>
                                                <div class="input-group flex-nowrap" style="width:auto;">
                                                    <span class="input-group-text">Năm</span>
                                                    <input type="number" class="form-control text-center" value="${exam.nam_kham || ''}" placeholder="Năm" readonly style="width:110px;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- II. LÍ DO VÀO VIỆN -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">II. LÍ DO VÀO VIỆN</h6>
                            <textarea class="form-control" rows="3" readonly>${escapeHtml(exam.ly_do_vao_vien || exam.ly_do_kham || '-')}</textarea>
                        </div>

                        <!-- III. HỎI BỆNH -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">III. HỎI BỆNH</h6>
                            <div class="mb-3">
                                <label class="form-label fw-bold">1. Quá trình bệnh lí:</label>
                                <textarea class="form-control" rows="4" readonly>${escapeHtml(exam.qua_trinh_benh_li || '-')}</textarea>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">2. Tiền sử bệnh - Bản thân:</label>
                                    <textarea class="form-control" rows="3" readonly>${escapeHtml(exam.tien_su_ban_than || '-')}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Tiền sử bệnh - Gia đình:</label>
                                    <textarea class="form-control" rows="3" readonly>${escapeHtml(exam.tien_su_gia_dinh || '-')}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- IV. KHÁM XÉT -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">IV. KHÁM XÉT</h6>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">1. Toàn thân:</label>
                                    <textarea class="form-control" rows="4" readonly>${escapeHtml(exam.kham_toan_than || '-')}</textarea>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-header">
                                            <strong>Dấu hiệu sinh tồn</strong>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <label class="form-label">Mạch:</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" value="${exam.mach || ''}" placeholder="0" readonly>
                                                    <span class="input-group-text">lần/phút</span>
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Nhiệt độ:</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" value="${exam.nhiet_do || ''}" placeholder="0" step="0.1" readonly>
                                                    <span class="input-group-text">°C</span>
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Huyết áp:</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" value="${exam.huyet_ap_tam_thu || ''}" placeholder="0" readonly>
                                                    <span class="input-group-text">/</span>
                                                    <input type="number" class="form-control" value="${exam.huyet_ap_tam_truong || ''}" placeholder="0" readonly>
                                                    <span class="input-group-text">mmHg</span>
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Nhịp thở:</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" value="${exam.nhip_tho || ''}" placeholder="0" readonly>
                                                    <span class="input-group-text">lần/phút</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="form-label fw-bold">2. Các bộ phận:</label>
                                <textarea class="form-control" rows="4" readonly>${escapeHtml(exam.kham_cac_bo_phan || '-')}</textarea>
                            </div>
                            <div class="mt-3">
                                <label class="form-label fw-bold">3. Tóm tắt kết quả lâm sàng:</label>
                                <textarea class="form-control" rows="3" readonly>${escapeHtml(exam.tom_tat_lam_sang || '-')}</textarea>
                            </div>
                            <div class="mt-3">
                                <label class="form-label fw-bold">4. Chẩn đoán vào viện:</label>
                                <textarea class="form-control" rows="3" readonly>${escapeHtml(exam.chan_doan_vao_vien || '-')}</textarea>
                            </div>
                            <div class="mt-3">
                                <label class="form-label fw-bold">5. Đã xử lí (thuốc, chăm sóc):</label>
                                <textarea class="form-control" rows="3" readonly>${escapeHtml(exam.da_xu_li || '-')}</textarea>
                            </div>
                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">6. Cho vào điều trị tại khoa:</label>
                                    <input type="text" class="form-control" value="${escapeHtml(exam.khoa_dieu_tri || '-')}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">7. Chú ý:</label>
                                    <input type="text" class="form-control" value="${escapeHtml(exam.chu_y || '-')}" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="text-muted small"></div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="mb-2">
                                    <strong>Ngày</strong> <input type="number" class="form-control d-inline-block w-auto" value="${exam.ngay_ky || ''}" placeholder="Ngày" readonly style="width:70px;">
                                    <strong>tháng</strong> <input type="number" class="form-control d-inline-block w-auto" value="${exam.thang_ky || ''}" placeholder="Tháng" readonly style="width:70px;">
                                    <strong>năm</strong> <input type="number" class="form-control d-inline-block w-auto" value="${exam.nam_ky || ''}" placeholder="Năm" readonly style="width:90px;">
                                </div>
                                <div class="fw-bold">BÁC SĨ KHÁM BỆNH</div>
                                <div class="mt-2">
                                    <strong>Họ tên:</strong> <input type="text" class="form-control d-inline-block w-auto" value="${escapeHtml(exam.ten_bac_si || '-')}" placeholder="Tên bác sĩ" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Đơn thuốc -->
            <div class="tab-pane fade" id="pane-prescription" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-pills me-2"></i>Đơn thuốc</h6>
                    </div>
                    <div class="card-body">
                        ${data.prescription ? renderPrescription(data.prescription, data.prescription_details || []) : '<p class="text-muted text-center py-5">Chưa có đơn thuốc</p>'}
                    </div>
                </div>
            </div>

            <!-- Tab: Kết quả xét nghiệm -->
            <div class="tab-pane fade" id="pane-lab" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-vial me-2"></i>Kết quả xét nghiệm</h6>
                    </div>
                    <div class="card-body">
                        ${data.lab_result ? renderLabResult(data.lab_result) : '<p class="text-muted text-center py-5">Chưa có kết quả xét nghiệm</p>'}
                    </div>
                </div>
            </div>

            <!-- Tab: Kết quả siêu âm -->
            <div class="tab-pane fade" id="pane-ultrasound" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-wave-square me-2"></i>Kết quả siêu âm</h6>
                    </div>
                    <div class="card-body">
                        ${data.ultrasound_result ? renderUltrasoundResult(data.ultrasound_result) : '<p class="text-muted text-center py-5">Chưa có kết quả siêu âm</p>'}
                    </div>
                </div>
            </div>

            <!-- Tab: Kết quả X-Quang -->
            <div class="tab-pane fade" id="pane-xray" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-x-ray me-2"></i>Kết quả X-Quang</h6>
                    </div>
                    <div class="card-body">
                        ${data.xray_result ? renderXrayResult(data.xray_result) : '<p class="text-muted text-center py-5">Chưa có kết quả X-Quang</p>'}
                    </div>
                </div>
            </div>

            <!-- Tab: Biên lai viện phí -->
            <div class="tab-pane fade" id="pane-receipt" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Biên lai viện phí</h6>
                    </div>
                    <div class="card-body">
                        ${data.receipt ? renderReceipt(data.receipt, data.receipt_details || []) : '<p class="text-muted text-center py-5">Chưa có biên lai</p>'}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    modalBody.innerHTML = html;
}

/**
 * Render prescription - Format giống doctor_examination
 */
function renderPrescription(prescription, details) {
    const exam = window.currentExamData || {};
    const ngayKe = prescription.NgayKe ? new Date(prescription.NgayKe).toLocaleDateString('vi-VN') : '-';
    
    let html = `
        <style>
        .prescription-clinic-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .prescription-address {
            font-size: 14px;
            margin-bottom: 10px;
        }
        .prescription-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 20px 0;
        }
        .prescription-code-small {
            font-size: 14px;
            font-weight: bold;
        }
        .prescription-field {
            margin-bottom: 15px;
        }
        .prescription-field label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .prescription-field input,
        .prescription-field textarea {
            border: none;
            border-bottom: 1px solid #000;
            border-radius: 0;
            padding: 5px;
        }
        .signature-section {
            text-align: center;
            margin-top: 30px;
        }
        .date-value {
            font-size: 14px;
            margin-bottom: 10px;
        }
        .signature-label {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .signature-name {
            font-size: 14px;
            font-weight: bold;
            min-height: 30px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        </style>
        <div style="font-family: 'Times New Roman', serif; font-size: 14px;">
            <!-- Header Information -->
            <div class="text-center mb-3">
                <h4 class="prescription-clinic-name">PHÓNG KHÁM ĐA KHOA THINHVIET</h4>
                <p class="prescription-address">Địa chỉ: Gò vấp</p>
                <h5 class="prescription-title">ĐƠN THUỐC</h5>
            </div>

            <!-- Prescription Code -->
            <div class="text-end mb-4">
                <span class="form-label fw-bold text-muted me-2">Mã đơn thuốc:</span>
                <span class="prescription-code-small">${prescription.MaDonThuoc || '-'}</span>
            </div>

            <!-- Patient Information -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="prescription-field">
                        <label class="form-label fw-bold">Họ tên:</label>
                        <input type="text" class="form-control" value="${escapeHtml(exam.ho_ten || '-')}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="prescription-field">
                        <label class="form-label fw-bold">Ngày sinh:</label>
                        <input type="text" class="form-control" value="${exam.ngay_sinh || '-'}" readonly>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="prescription-field">
                        <label class="form-label fw-bold">Mã Bệnh nhân:</label>
                        <input type="text" class="form-control" value="${exam.ma_benh_nhan || '-'}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="prescription-field">
                        <label class="form-label fw-bold">Giới tính:</label>
                        <input type="text" class="form-control" value="${exam.gioi_tinh || '-'}" readonly>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="prescription-field">
                        <label class="form-label fw-bold">Mã số BHYT (nếu có):</label>
                        <input type="text" class="form-control" value="${exam.so_the_bhyt || 'Thu phí'}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="prescription-field">
                        <label class="form-label fw-bold">Số điện thoại:</label>
                        <input type="text" class="form-control" value="${exam.so_dien_thoai || '-'}" readonly>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <div class="prescription-field">
                        <label class="form-label fw-bold">Địa chỉ liên hệ:</label>
                        <textarea class="form-control" rows="2" readonly>${escapeHtml(exam.dia_chi || '-')}</textarea>
                    </div>
                </div>
            </div>

            <!-- Diagnosis Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="prescription-field">
                        <label class="form-label fw-bold">Chẩn đoán:</label>
                        <input type="text" class="form-control" value="${escapeHtml(exam.chan_doan_vao_vien || '-')}" readonly>
                    </div>
                </div>
            </div>

            <!-- Medication Table -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="mb-3 fw-bold">Thuốc điều trị</h6>
                    ${details.length > 0 ? `
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-success">
                                    <tr>
                                        <th width="5%" class="text-center">STT</th>
                                        <th width="30%" class="text-center">Tên Thuốc</th>
                                        <th width="25%" class="text-center">Hoạt chất</th>
                                        <th width="10%" class="text-center">ĐVT</th>
                                        <th width="10%" class="text-center">SL</th>
                                        <th width="20%" class="text-center">Cách dùng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${details.map((item, idx) => `
                                        <tr>
                                            <td class="text-center">${idx + 1}</td>
                                            <td>${escapeHtml(item.TenThuoc || '-')}</td>
                                            <td class="text-center">${escapeHtml(item.HoatChat || '-')}</td>
                                            <td class="text-center">${escapeHtml(item.DonViTinh || '-')}</td>
                                            <td class="text-center">${item.SoLuong || '-'}</td>
                                            <td class="text-center">${escapeHtml(item.LieuDung || '-')}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    ` : '<p class="text-muted">Chưa có chi tiết đơn thuốc</p>'}
                </div>
            </div>

            <!-- Footer Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="prescription-field">
                        <label class="form-label fw-bold">Lời dặn:</label>
                        <input type="text" class="form-control" value="${escapeHtml(prescription.LoiDan || '-')}" readonly>
                    </div>
                </div>
            </div>

            <!-- Signature and Date -->
            <div class="row">
                <div class="col-md-6"></div>
                <div class="col-md-6">
                    <div class="signature-section">
                        <div class="date-value">${ngayKe}</div>
                        <div class="signature-label">Bác sỹ ký tên</div>
                        <div class="signature-name">${escapeHtml(prescription.TenBacSi || exam.ten_bac_si || 'Bác sĩ')}</div>
                    </div>
                </div>
            </div>
        </div>
    `;
    return html;
}

/**
 * Render lab result - Format giống doctor_examination
 */
function renderLabResult(labResult) {
    const exam = window.currentExamData || {};
    const chiTiet = labResult.chi_tiet || [];
    const ngayTao = labResult.ngay_tao ? new Date(labResult.ngay_tao) : null;
    const ngayTaoStr = ngayTao ? ngayTao.toLocaleDateString('vi-VN') : '-';
    const gioTaoStr = ngayTao ? ngayTao.toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'}) : '-';
    const maBenhNhan = exam.ma_benh_nhan || '0000000';
    
    let html = `
        <div style="font-family: 'Times New Roman', serif; font-size: 12px;">
            <!-- Header -->
            <div class="text-center mb-4">
                <div class="fw-bold" style="font-size: 18px; color: #000;">PHÒNG KHÁM ĐA KHOA THINHVIET</div>
                <div class="fw-bold" style="font-size: 14px;">KHOA XÉT NGHIỆM</div>
                <div class="fw-bold" style="font-size: 16px; color: #dc3545;">KẾT QUẢ XÉT NGHIỆM</div>
                <div class="d-flex justify-content-center mt-2" style="gap: 10px;">
                    <span>Ngày ĐK: <span>${ngayTaoStr}</span></span>
                    <span>|</span>
                    <span>${gioTaoStr}</span>
                </div>
            </div>
            <hr>

            <!-- Patient Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="row mb-3">
                        <div class="col-4"><strong style="font-size: 14px;">Mã bệnh nhân:</strong></div>
                        <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;">*${maBenhNhan}*</span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4"><strong style="font-size: 14px;">Họ và tên:</strong></div>
                        <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;">${escapeHtml(exam.ho_ten || '-')}</span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4"><strong style="font-size: 14px;">Địa chỉ:</strong></div>
                        <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;">${escapeHtml(exam.dia_chi || '-')}</span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4"><strong style="font-size: 14px;">Chẩn đoán sơ bộ:</strong></div>
                        <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;">${escapeHtml(exam.chan_doan_vao_vien || '-')}</span></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row mb-3">
                        <div class="col-4"><strong style="font-size: 14px;">Tuổi:</strong></div>
                        <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;">${exam.tuoi || '-'}</span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4"><strong style="font-size: 14px;">Giới tính:</strong></div>
                        <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;">${exam.gioi_tinh || '-'}</span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4"><strong style="font-size: 14px;">BS yêu cầu:</strong></div>
                        <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;">${escapeHtml(exam.ten_bac_si || '-')}</span></div>
                    </div>
                </div>
            </div>

            <!-- Test Results Section -->
            <div class="text-center mb-3">
                <div class="fw-bold" style="font-size: 16px;">BẢNG KẾT QUẢ XÉT NGHIỆM</div>
            </div>

            ${chiTiet.length > 0 ? `
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
                            ${chiTiet.map((item, idx) => `
                                <tr>
                                    <td class="text-center">${item.stt || idx + 1}</td>
                                    <td>${escapeHtml(item.ten_xet_nghiem || '-')}</td>
                                    <td class="text-center">${escapeHtml(item.gia_tri_tham_chieu || '-')}</td>
                                    <td class="text-center" style="font-weight: bold;">${escapeHtml(item.ket_qua || '-')}</td>
                                    <td class="text-center">${escapeHtml(item.don_vi || '-')}</td>
                                    <td>${escapeHtml(item.may_qtkt || '-')}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            ` : '<p class="text-muted text-center">Chưa có kết quả xét nghiệm</p>'}

            <!-- Notes -->
            <div class="mt-3" style="font-size: 11px;">
                <div>Ghi chú: Kết quả in đậm là kết quả nằm ngoài khoảng tham chiếu.</div>
            </div>

            <!-- Footer -->
            <div class="mt-4">
                <div class="row">
                    <div class="col-6"></div>
                    <div class="col-6">
                        <div class="text-center" style="font-size: 16px; font-weight: bold; margin-bottom: 20px;">
                            Ngày ${ngayTao ? ngayTao.getDate() : '-'} tháng ${ngayTao ? ngayTao.getMonth() + 1 : '-'} năm ${ngayTao ? ngayTao.getFullYear() : '-'}
                        </div>
                        <div class="text-center">
                            <div class="fw-bold" style="font-size: 14px;">BÁC SĨ XÉT NGHIỆM</div>
                            <div class="mt-2" style="font-size: 14px;">${escapeHtml(labResult.bac_si_xet_nghiem || '-')}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    return html;
}

/**
 * Render ultrasound result - Format giống doctor_examination
 */
function renderUltrasoundResult(ultrasoundResult) {
    const exam = window.currentExamData || {};
    const images = ultrasoundResult.hinh_anh || [];
    const ngayTao = ultrasoundResult.ngay_tao ? new Date(ultrasoundResult.ngay_tao) : null;
    const ngayTaoStr = ngayTao ? ngayTao.toLocaleDateString('vi-VN') : '-';
    const gioTaoStr = ngayTao ? ngayTao.toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'}) : '-';
    const maBenhNhan = exam.ma_benh_nhan || '0000000';
    
    let html = `
        <style>
        .report {
            font-family: "Times New Roman", serif;
            padding: 18px;
        }
        .title {
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            letter-spacing: .5px;
            font-size: 18px;
            margin-bottom: 6px;
            color: red;
        }
        .hr {
            border-top: 2px solid #000;
            margin: 10px 0;
        }
        .row-line {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        .row-line .label {
            min-width: 120px;
            font-weight: bold;
        }
        .row-line .dots {
            margin: 0 5px;
        }
        .row-line .value {
            flex: 1;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
        }
        .result-content {
            border: 1px solid #333;
            padding: 10px;
            min-height: 100px;
            white-space: pre-wrap;
        }
        .conclusion-content {
            border: 1px solid #333;
            padding: 10px;
            min-height: 60px;
            white-space: pre-wrap;
        }
        </style>
        <div class="report">
            <div class="text-center mb-3">
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
                <div class="value">*${maBenhNhan}*</div>
                <div class="label" style="margin-left: 20px;">Ngày ĐK:</div>
                <div class="dots">:</div>
                <div class="value">${ngayTaoStr}</div>
                <div class="dots">-</div>
                <div class="value">${gioTaoStr}</div>
            </div>

            <div class="hr"></div>

            <div class="row-line">
                <div class="label">Họ tên:</div>
                <div class="dots">:</div>
                <div class="value">${escapeHtml(exam.ho_ten || '-')}</div>
            </div>

            <div class="row-line">
                <div class="label">Tuổi:</div>
                <div class="dots">:</div>
                <div class="value">${exam.tuoi || '-'}</div>
                <div class="label" style="margin-left: 20px;">Giới:</div>
                <div class="dots">:</div>
                <div class="value">${exam.gioi_tinh || '-'}</div>
            </div>

            <div class="row-line">
                <div class="label">Địa chỉ:</div>
                <div class="dots">:</div>
                <div class="value">${escapeHtml(exam.dia_chi || '-')}</div>
            </div>

            <div class="row-line">
                <div class="label">Chẩn đoán sơ bộ:</div>
                <div class="dots">:</div>
                <div class="value">${escapeHtml(exam.chan_doan_vao_vien || '-')}</div>
            </div>

            <div class="row-line">
                <div class="label">Bác sĩ chỉ định:</div>
                <div class="dots">:</div>
                <div class="value">${escapeHtml(exam.ten_bac_si || '-')}</div>
            </div>

            <div class="hr"></div>

            <div class="row-line">
                <div class="label">Vùng khảo sát:</div>
                <div class="dots">:</div>
                <div class="value">${escapeHtml(ultrasoundResult.vung_khao_sat || '-')}</div>
            </div>

            <div class="hr"></div>

            <div class="row-line">
                <div class="label">KẾT QUẢ:</div>
            </div>
            <div class="result-content">${escapeHtml(ultrasoundResult.ket_qua_khao_sat || '-')}</div>

            <div class="row-line" style="margin-top: 15px;">
                <div class="label">KẾT LUẬN:</div>
            </div>
            <div class="conclusion-content">${escapeHtml(ultrasoundResult.ket_luan || '-')}</div>

            ${images.length > 0 ? `
                <div class="mt-3">
                    <div class="fw-bold mb-2">Hình ảnh siêu âm:</div>
                    <div class="row">
                        ${images.map(img => `
                            <div class="col-md-3 mb-2">
                                <img src="${img.duong_dan || ''}" class="img-fluid rounded" style="cursor: pointer;" onclick="zoomImage('${img.duong_dan || ''}')" onerror="this.src='assets/img/placeholder.jpg'">
                            </div>
                        `).join('')}
                    </div>
                </div>
            ` : ''}

            <div class="text-end mt-3">
                <div style="font-size: 14px;">Ngày ${ngayTao ? ngayTao.getDate() : '-'} tháng ${ngayTao ? ngayTao.getMonth() + 1 : '-'} năm ${ngayTao ? ngayTao.getFullYear() : '-'}</div>
                <div class="fw-bold mt-2" style="font-size: 14px;">BÁC SĨ SIÊU ÂM</div>
                <div class="mt-2" style="font-size: 14px; min-height: 30px; border-top: 1px solid #000; padding-top: 5px;">${escapeHtml(ultrasoundResult.bac_si_sieu_am || '-')}</div>
            </div>
        </div>
    `;
    return html;
}

/**
 * Render X-ray result - Format giống doctor_examination
 */
function renderXrayResult(xrayResult) {
    const exam = window.currentExamData || {};
    const images = xrayResult.hinh_anh || [];
    const ngayCapNhat = xrayResult.ngay_cap_nhat ? new Date(xrayResult.ngay_cap_nhat) : null;
    const ngayCapNhatStr = ngayCapNhat ? ngayCapNhat.toLocaleDateString('vi-VN') : '-';
    const gioCapNhatStr = ngayCapNhat ? ngayCapNhat.toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'}) : '-';
    const today = new Date();
    const todayStr = `Ngày ${today.getDate()} tháng ${today.getMonth() + 1} năm ${today.getFullYear()}`;
    
    let html = `
        <ul class="nav nav-tabs mb-2" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="xr_tab_info" data-bs-toggle="tab" data-bs-target="#xr_tabpane_info" type="button" role="tab">Thông tin</button>
            </li>
            ${images.length > 0 ? `
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="xr_tab_images" data-bs-toggle="tab" data-bs-target="#xr_tabpane_images" type="button" role="tab">Hình Ảnh X-Quang</button>
            </li>
            ` : ''}
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="xr_tabpane_info" role="tabpanel">
                <div class="border border-dark p-2">
                    <div class="text-center">
                        <div class="fw-bold">PHÒNG KHÁM ĐA KHOA THINHVIET</div>
                        <div class="fw-bold">KHOA CHUẨN ĐOÁN HÌNH ẢNH</div>
                        <div class="text-muted">Địa chỉ: Gò Vấp - Điện thoại: 0777871608</div>
                    </div>
                    <hr>
                    <div class="row g-2">
                        <div class="col-md-6">Họ và tên: <strong>${escapeHtml(exam.ho_ten || '-')}</strong></div>
                        <div class="col-md-6">Giới tính: <strong>${exam.gioi_tinh || '-'}</strong></div>
                        <div class="col-md-6">Năm sinh: <strong>${exam.ngay_sinh ? new Date(exam.ngay_sinh).getFullYear() : '-'}</strong></div>
                        <div class="col-md-6">Số phiếu chỉ định: <strong>${xrayResult.id_phieu_chup_xquang || '-'}</strong></div>
                        <div class="col-md-12">Địa chỉ: <strong>${escapeHtml(exam.dia_chi || '-')}</strong></div>
                        <div class="col-md-6">Ngày chỉ định: <strong>${ngayCapNhatStr}</strong></div>
                        <div class="col-md-6">Giờ chỉ định: <strong>${gioCapNhatStr}</strong></div>
                    </div>
                    <hr>
                    <div>Chẩn đoán: <strong>${escapeHtml(exam.chan_doan_vao_vien || '-')}</strong></div>
                    <div>Bác sĩ chỉ định: <strong>${escapeHtml(exam.ten_bac_si || '-')}</strong></div>
                    <div class="mt-2">Nội dung: <strong>${escapeHtml(xrayResult.noi_dung || '-')}</strong></div>
                    <div class="mt-3 fw-bold">KẾT QUẢ</div>
                    <div class="border p-2" style="min-height:80px">${escapeHtml(xrayResult.ket_qua || '-')}</div>
                    <div class="mt-3 fw-bold">KẾT LUẬN</div>
                    <div class="border p-2" style="min-height:80px">${escapeHtml(xrayResult.ket_luan || '-')}</div>
                    <div class="text-end mt-3">
                        <em>${todayStr}</em><br>
                        <strong>Bác sĩ X Quang</strong>
                        <div style="min-height:40px; border-top: 1px solid #000; padding-top: 5px;">${escapeHtml(xrayResult.bac_si_xquang || '-')}</div>
                    </div>
                </div>
            </div>
            ${images.length > 0 ? `
            <div class="tab-pane fade" id="xr_tabpane_images" role="tabpanel">
                <div class="text-muted small mb-2">Danh sách hình ảnh X-Quang đã lưu</div>
                <div class="row">
                    ${images.map(img => `
                        <div class="col-md-3 mb-2">
                            <img src="${img.file_path || ''}" class="img-fluid rounded" style="cursor: pointer;" onclick="zoomImage('${img.file_path || ''}')" onerror="this.src='assets/img/placeholder.jpg'">
                        </div>
                    `).join('')}
                </div>
            </div>
            ` : ''}
        </div>
    `;
    return html;
}

/**
 * Render receipt
 */
function renderReceipt(receipt, details) {
    let html = `
        <div class="mb-3">
            <strong>Mã biên lai:</strong> <span class="badge bg-primary">${receipt.ma_bien_lai || '-'}</span>
        </div>
        <div class="mb-3">
            <strong>Ngày tạo:</strong> ${receipt.ngay_tao ? new Date(receipt.ngay_tao).toLocaleDateString('vi-VN') : '-'}
        </div>
        <div class="mb-3">
            <strong>Tổng tiền:</strong> <span class="text-danger fw-bold">${formatCurrency(receipt.tong_tien || 0)} VNĐ</span>
        </div>
        ${details.length > 0 ? `
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>STT</th>
                        <th>Loại dịch vụ</th>
                        <th>Tên dịch vụ</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    ${details.map((item, idx) => `
                        <tr>
                            <td>${idx + 1}</td>
                            <td>${escapeHtml(item.loai_dich_vu || '-')}</td>
                            <td>${escapeHtml(item.ten_dich_vu || '-')}</td>
                            <td>${item.so_luong || '-'}</td>
                            <td>${formatCurrency(item.don_gia || 0)}</td>
                            <td><strong>${formatCurrency(item.thanh_tien || 0)}</strong></td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        ` : '<p class="text-muted">Chưa có chi tiết biên lai</p>'}
    `;
    return html;
}

/**
 * Zoom image
 */
function zoomImage(src) {
    if (!src) return;
    
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content bg-dark">
                <div class="modal-header bg-dark border-0">
                    <h5 class="modal-title text-white">Xem hình ảnh</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body d-flex justify-content-center align-items-center" style="height: calc(100vh - 120px);">
                    <img src="${src}" class="img-fluid" style="max-width: 100%; max-height: 100%;">
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    modal.addEventListener('hidden.bs.modal', () => {
        document.body.removeChild(modal);
    });
}

/**
 * Utility functions
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount);
}

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
