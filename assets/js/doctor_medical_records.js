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
            <td>${escapeHtml(record.ma_benh_nhan || '-')}</td>
            <td>${escapeHtml(record.ten_benh_nhan || '-')}</td>
            <td>${record.tuoi || '-'}</td>
            <td>${record.gioi_tinh || '-'}</td>
            <td>${escapeHtml(record.so_dien_thoai || '-')}</td>
            <td>${escapeHtml(record.cccd || '-')}</td>
            <td>${escapeHtml(record.chan_doan_vao_vien || record.ly_do || '-')}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="viewDetail(${record.lich_hen_id})">
                    <i class="fas fa-eye me-1"></i>Xem chi tiết
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
    pagination.innerHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadRecords(${currentPage - 1}); return false;">Trước</a>
        </li>
    `;

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            pagination.innerHTML += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadRecords(${i}); return false;">${i}</a>
                </li>
            `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            pagination.innerHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    // Next button
    pagination.innerHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadRecords(${currentPage + 1}); return false;">Sau</a>
        </li>
    `;
}

/**
 * Update statistics
 */
function updateStats(total, page, totalPages) {
    document.getElementById('statTotal').textContent = total;
    document.getElementById('statPage').textContent = page;
    document.getElementById('statTotalPages').textContent = totalPages;
}

/**
 * Search records
 */
function searchRecords() {
    const searchType = document.getElementById('searchType').value;
    const searchInput = document.getElementById('searchInput').value.trim();
    const selectedDate = document.getElementById('selectedDate').value;

    if (searchType && !searchInput) {
        showAlert('Vui lòng nhập từ khóa tìm kiếm!', 'warning');
        return;
    }

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
 * Zoom image with controls
 */
let currentZoom = 1;
const minZoom = 0.5;
const maxZoom = 5;
let isDragging = false;
let startX, startY;
let currentImageElement = null;
// Event handlers for zoom controls
let wheelHandler = null;
let mousedownHandler = null;
let mousemoveHandler = null;
let mouseupHandler = null;

function zoomImage(src) {
    if (!src) return;
    
    // Reset zoom state
    currentZoom = 1;
    isDragging = false;
    currentImageElement = null;
    
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'imageZoomModal';
    modal.innerHTML = `
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content bg-dark">
                <div class="modal-header bg-dark border-0">
                    <h5 class="modal-title text-white">Xem hình ảnh</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-light btn-sm" onclick="zoomImageIn()" id="zoomInBtn" title="Phóng to">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button type="button" class="btn btn-outline-light btn-sm" onclick="zoomImageOut()" id="zoomOutBtn" title="Thu nhỏ">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-outline-light btn-sm" onclick="zoomImageReset()" id="resetZoomBtn" title="Reset">
                            <i class="fas fa-expand-arrows-alt"></i>
                        </button>
                        <button type="button" class="btn btn-outline-light btn-sm" onclick="zoomImageFullscreen()" id="fullscreenBtn" title="Toàn màn hình">
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
    
    // Setup zoom functionality after modal is shown
    setTimeout(() => {
        setupZoomControls();
    }, 100);
    
    modal.addEventListener('hidden.bs.modal', () => {
        // Clean up event listeners
        const img = document.getElementById('zoomImage');
        if (img) {
            if (wheelHandler) img.removeEventListener('wheel', wheelHandler);
            if (mousedownHandler) img.removeEventListener('mousedown', mousedownHandler);
        }
        if (mousemoveHandler) document.removeEventListener('mousemove', mousemoveHandler);
        if (mouseupHandler) document.removeEventListener('mouseup', mouseupHandler);
        
        wheelHandler = null;
        mousedownHandler = null;
        mousemoveHandler = null;
        mouseupHandler = null;
        
        document.body.removeChild(modal);
        currentZoom = 1;
        isDragging = false;
        currentImageElement = null;
    });
}

function setupZoomControls() {
    const img = document.getElementById('zoomImage');
    if (!img) return;
    
    currentImageElement = img;
    
    // Remove old handlers if they exist
    if (wheelHandler) {
        img.removeEventListener('wheel', wheelHandler);
    }
    if (mousedownHandler) {
        img.removeEventListener('mousedown', mousedownHandler);
    }
    if (mousemoveHandler) {
        document.removeEventListener('mousemove', mousemoveHandler);
    }
    if (mouseupHandler) {
        document.removeEventListener('mouseup', mouseupHandler);
    }
    
    // Mouse wheel zoom
    wheelHandler = (e) => {
        e.preventDefault();
        const delta = e.deltaY > 0 ? -0.1 : 0.1;
        currentZoom = Math.max(minZoom, Math.min(maxZoom, currentZoom + delta));
        updateImageZoom();
    };
    img.addEventListener('wheel', wheelHandler);
    
    // Drag to pan
    mousedownHandler = (e) => {
        if (currentZoom > 1) {
            isDragging = true;
            img.style.cursor = 'grabbing';
            startX = e.pageX - img.offsetLeft;
            startY = e.pageY - img.offsetTop;
        }
    };
    img.addEventListener('mousedown', mousedownHandler);
    
    mousemoveHandler = (e) => {
        if (!isDragging || !img) return;
        e.preventDefault();
        img.style.left = (e.pageX - startX) + 'px';
        img.style.top = (e.pageY - startY) + 'px';
        img.style.position = 'relative';
    };
    document.addEventListener('mousemove', mousemoveHandler);
    
    mouseupHandler = () => {
        isDragging = false;
        if (img) {
            img.style.cursor = currentZoom > 1 ? 'grab' : 'default';
        }
    };
    document.addEventListener('mouseup', mouseupHandler);
}

function zoomImageIn() {
    currentZoom = Math.min(maxZoom, currentZoom + 0.2);
    updateImageZoom();
}

function zoomImageOut() {
    currentZoom = Math.max(minZoom, currentZoom - 0.2);
    updateImageZoom();
}

function zoomImageReset() {
    currentZoom = 1;
    updateImageZoom();
    const img = document.getElementById('zoomImage');
    if (img) {
        img.style.left = 'auto';
        img.style.top = 'auto';
        img.style.position = 'static';
    }
}

function updateImageZoom() {
    const img = document.getElementById('zoomImage');
    if (img) {
        img.style.transform = 'scale(' + currentZoom + ')';
        img.style.cursor = currentZoom > 1 ? 'grab' : 'default';
    }
}

function zoomImageFullscreen() {
    const modal = document.getElementById('imageZoomModal');
    if (!modal) return;
    
    if (!document.fullscreenElement) {
        modal.requestFullscreen().catch((err) => {
            console.log('Error attempting to enable fullscreen:', err);
        });
    } else {
        document.exitFullscreen();
    }
}

/**
 * Utility functions
 */
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, m => map[m]);
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

