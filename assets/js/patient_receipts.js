// Global variables
let currentPage = 1;
let currentLimit = 15;
let currentFilters = {
    selected_date: ''
};

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadReceipts();
    
    const dateInput = document.getElementById('selectedDate');
    if (dateInput) {
        dateInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchReceipts();
            }
        });
    }
});

function loadReceipts(page = 1) {
    currentPage = page;
    
    const filters = {
        selected_date: currentFilters.selected_date,
        receipt_code: currentFilters.receipt_code,
        page: currentPage,
        limit: currentLimit
    };

    // Show loading
    document.getElementById('loadingIndicator').style.display = 'block';
    document.getElementById('receiptsTable').style.display = 'none';
    document.getElementById('emptyState').style.display = 'none';

    fetch('./?action=get_patient_receipts', {
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
                // Ensure newest first if backend doesn't sort
                data.data.sort((a, b) => new Date(b.ngay_lap || b.ngay_hen || 0) - new Date(a.ngay_lap || a.ngay_hen || 0));
                renderReceipts(data.data);
                renderPagination(data.total_pages, data.page);
                updateStats(data.total, data.page, data.total_pages);
                document.getElementById('receiptsTable').style.display = 'block';
                document.getElementById('emptyState').style.display = 'none';
            } else {
                document.getElementById('emptyState').style.display = 'block';
                document.getElementById('receiptsTable').style.display = 'none';
                updateStats(data.total || 0, data.page || 1, data.total_pages || 1);
            }
        } else {
            console.error('API Error:', data.message);
            document.getElementById('emptyState').style.display = 'block';
            document.getElementById('receiptsTable').style.display = 'none';
            showAlert(data.message || 'Có lỗi xảy ra khi tải dữ liệu', 'danger');
        }
    })
    .catch(error => {
        console.error('Error loading receipts:', error);
        document.getElementById('loadingIndicator').style.display = 'none';
        document.getElementById('emptyState').style.display = 'block';
        document.getElementById('receiptsTable').style.display = 'none';
        showAlert('Lỗi khi tải dữ liệu: ' + error.message, 'danger');
    });
}

function renderReceipts(receipts) {
    const tbody = document.getElementById('receiptsTableBody');
    tbody.innerHTML = '';

    receipts.forEach((receipt, index) => {
        const row = document.createElement('tr');
        const stt = (currentPage - 1) * currentLimit + index + 1;
        const statusBadge = renderStatusBadge(receipt.trang_thai);
        
        row.innerHTML = `
            <td>${stt}</td>
            <td>${receipt.ngay_kham_formatted || '-'}</td>
            <td>${receipt.gio_kham_formatted || '-'}</td>
            <td><code>${escapeHtml(receipt.ma_bien_lai || '-')}</code></td>
            <td>${statusBadge}</td>
            <td style="text-align: center;">
                <button class="btn btn-sm btn-outline-primary" onclick="viewReceipt(${receipt.id})" title="Xem chi tiết">
                    <i class="fas fa-eye"></i> Xem
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function renderStatusBadge(status) {
    const s = (status || '').toLowerCase();
    let cls = 'secondary';
    if (s.includes('chưa')) cls = 'warning';
    if (s.includes('đã thanh toán')) cls = 'success';
    if (s.includes('hủy')) cls = 'danger';
    return `<span class="badge bg-${cls}">${escapeHtml(status || '-')}</span>`;
}

function renderPagination(totalPages, currentPage) {
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';

    if (totalPages <= 1) return;

    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<a class="page-link" href="#" onclick="loadReceipts(${currentPage - 1}); return false;">Trước</a>`;
    pagination.appendChild(prevLi);

    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    if (startPage > 1) {
        const firstLi = document.createElement('li');
        firstLi.className = 'page-item';
        firstLi.innerHTML = `<a class="page-link" href="#" onclick="loadReceipts(1); return false;">1</a>`;
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
        li.innerHTML = `<a class="page-link" href="#" onclick="loadReceipts(${i}); return false;">${i}</a>`;
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
        lastLi.innerHTML = `<a class="page-link" href="#" onclick="loadReceipts(${totalPages}); return false;">${totalPages}</a>`;
        pagination.appendChild(lastLi);
    }

    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = `<a class="page-link" href="#" onclick="loadReceipts(${currentPage + 1}); return false;">Sau</a>`;
    pagination.appendChild(nextLi);
}

function updateStats(total, page, totalPages) {
    document.getElementById('statTotal').textContent = total;
    document.getElementById('statPage').textContent = page;
    document.getElementById('statTotalPages').textContent = totalPages;
    document.getElementById('statLimit').textContent = currentLimit;
}

function searchReceipts() {
    const selectedDate = document.getElementById('selectedDate').value;
    const receiptCode = document.getElementById('receiptCode')?.value || '';

    currentFilters.selected_date = selectedDate;
    currentFilters.receipt_code = receiptCode;
    currentPage = 1;
    loadReceipts(currentPage);
}

function resetFilters() {
    document.getElementById('selectedDate').value = '';
    const receiptCodeInput = document.getElementById('receiptCode');
    if (receiptCodeInput) receiptCodeInput.value = '';
    currentFilters.selected_date = '';
    currentFilters.receipt_code = '';
    currentPage = 1;
    loadReceipts(currentPage);
}

function refreshReceipts() {
    loadReceipts(currentPage);
}

function showAlert(message, type = 'info') {
    const existingAlerts = document.querySelectorAll('.alert-dismissible');
    existingAlerts.forEach(alert => alert.remove());

    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    const container = document.querySelector('.container-fluid');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
}

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
 * View receipt detail in modal
 */
function viewReceipt(receiptId) {
    if (!receiptId) {
        showAlert('Không tìm thấy ID biên lai', 'danger');
        return;
    }

    const modalElement = document.getElementById('receiptDetailModal');
    if (!modalElement) return;

    const modalBody = document.getElementById('receiptDetailModalBody');
    if (modalBody) {
        modalBody.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
                <p class="mt-2 text-muted">Đang tải chi tiết biên lai...</p>
            </div>
        `;
    }

    const modalInstance = bootstrap.Modal ? new bootstrap.Modal(modalElement) : null;
    if (modalInstance) {
        modalInstance.show();
    }

    fetch(`./?action=render_patient_receipt_detail&id=${receiptId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.text();
        })
        .then(html => {
            if (modalBody) {
                modalBody.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Error loading receipt detail:', error);
            if (modalBody) {
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Lỗi khi tải chi tiết biên lai: ${escapeHtml(error.message)}
                    </div>
                `;
            }
        });
}


