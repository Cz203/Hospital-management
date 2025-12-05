// Global variables
let currentPage = 1;
let currentLimit = 15;
let currentFilters = {
    selected_date: ''
};

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadRecords();
    
    // Bind enter key on date input
    document.getElementById('selectedDate').addEventListener('keypress', function(e) {
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
        selected_date: currentFilters.selected_date,
        page: currentPage,
        limit: currentLimit
    };

    // Show loading
    document.getElementById('loadingIndicator').style.display = 'block';
    document.getElementById('recordsTable').style.display = 'none';
    document.getElementById('emptyState').style.display = 'none';

    fetch('./?action=get_patient_medical_records', {
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
            <td>${escapeHtml(record.ten_bac_si || '-')}</td>
            <td><small>${escapeHtml(record.chan_doan_vao_vien || record.tom_tat_lam_sang || '-')}</small></td>
            <td style="text-align: center;">
                <button class="btn btn-sm btn-primary" onclick="viewDetail(${record.lich_hen_id || record.exam_id})" title="Xem chi tiết">
                    <i class="fas fa-eye"></i> Xem chi tiết
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
    const selectedDate = document.getElementById('selectedDate').value;

    currentFilters.selected_date = selectedDate;
    currentPage = 1;
    loadRecords(currentPage);
}

/**
 * Reset filters
 */
function resetFilters() {
    document.getElementById('selectedDate').value = '';
    currentFilters.selected_date = '';
    currentPage = 1;
    loadRecords(currentPage);
}

/**
 * Refresh records
 */
function refreshRecords() {
    loadRecords(currentPage);
}

/**
 * View detail - Open in new tab instead of navigating
 */
function viewDetail(lichHenId) {
    if (!lichHenId) {
        showAlert('Không tìm thấy ID lịch hẹn', 'danger');
        return;
    }

    // Open detail page in new tab
    window.open(`./?action=render_patient_medical_record_detail&exam_id=${lichHenId}&lich_hen_id=${lichHenId}`, '_blank');
}

/**
 * Show alert
 */
function showAlert(message, type = 'info') {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert-dismissible');
    existingAlerts.forEach(alert => alert.remove());

    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    // Insert at the top of container
    const container = document.querySelector('.container-fluid');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
}

/**
 * Escape HTML
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
