// Payment Management JavaScript
let currentPage = 1;
let currentLimit = 20;
let currentKeyword = '';
let currentDate = '';

// Load receipts on page load
document.addEventListener('DOMContentLoaded', function() {
    
    // Set default date to today
    setDefaultDate();
    
    loadReceipts();
    loadStatistics(); // Load statistics separately
    
    // Search on Enter key
    const searchInput = document.getElementById('searchKeyword');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchReceipts();
            }
        });
    }
});

function setDefaultDate() {
    const dateInput = document.getElementById('dateFilter');
    if (dateInput) {
        // Get today's date in YYYY-MM-DD format
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        const todayString = `${year}-${month}-${day}`;
        
        // Set the date input value
        dateInput.value = todayString;
        currentDate = todayString;
        
    }
}

function loadStatistics() {
    let url = './?action=reception_get_all_receipts&limit=1000';
    if (currentDate) {
        url += '&date=' + currentDate;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatistics(data.data);
            } else {
                updateStatistics([]);
            }
        })
        .catch(error => {
            updateStatistics([]);
        });
}

function filterByDate() {
    const dateInput = document.getElementById('dateFilter');
    currentDate = dateInput.value;
    
    // Reset to first page when filtering
    currentPage = 1;
    
    // Load receipts and statistics with new date filter
    loadReceipts();
    loadStatistics();
}

function loadReceipts() {
    const tbody = document.getElementById('receiptsTableBody');
    if (!tbody) {
        return;
    }
    
    tbody.innerHTML = `
        <tr>
            <td colspan="9" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
                <div class="mt-2">Đang tải dữ liệu...</div>
            </td>
        </tr>
    `;

    const params = new URLSearchParams({
        page: currentPage,
        limit: currentLimit
    });

    if (currentKeyword) {
        params.append('keyword', currentKeyword);
    }
    
    if (currentDate) {
        params.append('date', currentDate);
    }

    // Determine which API to call based on whether we have a keyword
    const apiAction = currentKeyword ? 'reception_search_receipts' : 'reception_get_unpaid_receipts';
    const url = `./?action=${apiAction}&${params}`;
    
    
    fetch(url)
        .then(response => response.text())
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    renderReceipts(data.data);
                    // Don't update statistics here - it's handled separately by loadStatistics()
                } else {
                    showError('Lỗi khi tải danh sách biên lai: ' + data.message);
                    showEmptyState('Lỗi khi tải dữ liệu', data.message);
                }
            } catch (e) {
                showError('Lỗi khi phân tích dữ liệu từ server');
                showEmptyState('Lỗi phân tích dữ liệu', 'Server trả về dữ liệu không hợp lệ');
            }
        })
        .catch(error => {
            showError('Lỗi khi tải danh sách biên lai');
            showEmptyState('Lỗi kết nối', 'Vui lòng kiểm tra kết nối mạng');
        });
}

function renderReceipts(receipts) {
    const tbody = document.getElementById('receiptsTableBody');
    
    if (!receipts || receipts.length === 0) {
        showEmptyState('Không có biên lai nào', '');
        return;
    }

    try {
        tbody.innerHTML = receipts.map((receipt, index) => {
            const statusClass = receipt.trang_thai === 'Đã thanh toán' ? 'success' : 'warning';
            const statusIcon = receipt.trang_thai === 'Đã thanh toán' ? 'check-circle' : 'clock';
            
            return `
                <tr>
                    <td>${(currentPage - 1) * currentLimit + index + 1}</td>
                    <td><code>${receipt.ma_bien_lai}</code></td>
                    <td>
                        <div class="fw-bold">${receipt.ho_ten || 'N/A'}</div>
                        <small class="text-muted">${receipt.tuoi || 'N/A'} tuổi, ${receipt.gioi_tinh || 'N/A'}</small>
                    </td>
                    <td><code>${receipt.ma_benh_nhan || 'N/A'}</code></td>
                    <td>${receipt.ten_bac_si || 'N/A'}</td>
                    <td>
                        <div>${formatDate(receipt.ngay_hen)}</div>
                        <small class="text-muted">${receipt.gio_hen || ''}</small>
                    </td>
                    <td class="text-end">
                        <div class="fw-bold">${formatCurrency(receipt.tong_nguoi_benh)}</div>
                        <small class="text-muted">BHYT: ${formatCurrency(receipt.tong_quy_bhyt)}</small>
                    </td>
                    <td>
                        <span class="badge badge-payment bg-${statusClass}">
                            <i class="fas fa-${statusIcon} me-1"></i>${receipt.trang_thai}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-info btn-payment" onclick="viewReceiptDetails(${receipt.id})" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${receipt.trang_thai === 'Chưa thanh toán' ? `
                                <button class="btn btn-outline-success btn-payment" onclick="openPaymentModal(${receipt.id})" title="Thanh toán">
                                    <i class="fas fa-credit-card"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    } catch (error) {
        showEmptyState('Lỗi khi hiển thị dữ liệu', error.message);
    }
}

function showEmptyState(title, message) {
    const tbody = document.getElementById('receiptsTableBody');
    const icon = message.includes('Lỗi') ? 'exclamation-triangle' : 'inbox';
    const color = message.includes('Lỗi') ? 'danger' : 'muted';
    
    tbody.innerHTML = `
        <tr>
            <td colspan="9" class="empty-state">
                <i class="fas fa-${icon} fa-3x text-${color} mb-3"></i>
                <div>${title}</div>
                ${message ? `<small class="text-muted">${message}</small>` : ''}
            </td>
        </tr>
    `;
}

function updateStatistics(receipts) {
    try {
        
        const unpaidCount = receipts.filter(r => r.trang_thai === 'Chưa thanh toán').length;
        const paidCount = receipts.filter(r => 
            r.trang_thai === 'Đã thanh toán chuyển khoản' || 
            r.trang_thai === 'Đã thanh toán tiền mặt'
        ).length;
        const totalRevenue = receipts
            .filter(r => 
                r.trang_thai === 'Đã thanh toán chuyển khoản' || 
                r.trang_thai === 'Đã thanh toán tiền mặt'
            )
            .reduce((sum, r) => sum + parseFloat(r.tong_nguoi_benh || 0), 0);
        
        
        const unpaidCountEl = document.getElementById('unpaidCount');
        const paidCountEl = document.getElementById('paidCount');
        const totalRevenueEl = document.getElementById('totalRevenue');
        
        if (unpaidCountEl) {
            unpaidCountEl.textContent = unpaidCount;
        }
        if (paidCountEl) {
            paidCountEl.textContent = paidCount;
        }
        if (totalRevenueEl) {
            totalRevenueEl.textContent = formatCurrency(totalRevenue);
        }
    } catch (error) {
    }
}

function searchReceipts() {
    const searchInput = document.getElementById('searchKeyword');
    if (searchInput) {
        currentKeyword = searchInput.value.trim();
    }
    currentPage = 1;
    loadReceipts();
}

function filterReceipts() {
    const limitFilter = document.getElementById('limitFilter');
    if (limitFilter) {
        currentLimit = parseInt(limitFilter.value);
    }
    currentPage = 1;
    loadReceipts();
}

function openPaymentModal(receiptId) {
    
    // Reset payment buttons and fields before opening modal
    resetPaymentButtons();
    hideCashPaymentFields();
    
    // Get receipt details first
    fetch(`./?action=reception_get_receipt_details&id=${receiptId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const receipt = data.data.receipt;
                
                // Fill modal with receipt data
                const receiptIdInput = document.getElementById('paymentReceiptId');
                const modalReceiptCode = document.getElementById('modalReceiptCode');
                const modalPatientCode = document.getElementById('modalPatientCode');
                const modalPatientName = document.getElementById('modalPatientName');
                const modalAmount = document.getElementById('modalAmount');
                
                if (receiptIdInput) receiptIdInput.value = receiptId;
                if (modalReceiptCode) modalReceiptCode.textContent = receipt.ma_bien_lai;
                if (modalPatientCode) modalPatientCode.textContent = receipt.ma_benh_nhan || 'N/A';
                if (modalPatientName) modalPatientName.textContent = receipt.ho_ten;
                if (modalAmount) {
                    modalAmount.textContent = formatCurrency(receipt.tong_nguoi_benh);
                }
                
                // Show modal
                const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
                paymentModal.show();
            } else {
                showError('Không thể lấy thông tin biên lai: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching receipt details:', error);
            showError('Lỗi khi lấy thông tin biên lai');
        });
}

function selectPaymentMethod(button) {
    // Remove active class from all buttons
    document.querySelectorAll('.payment-method-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.classList.add('btn-outline-success', 'btn-outline-primary', 'btn-outline-info');
        btn.classList.remove('btn-success', 'btn-primary', 'btn-info');
    });
    
    // Add active class to selected button
    button.classList.add('active');
    button.classList.remove('btn-outline-success', 'btn-outline-primary', 'btn-outline-info');
    
    // Set the payment method value
    const method = button.getAttribute('data-method');
    document.getElementById('paymentMethod').value = method;
    
    // Update button style based on method
    if (method === 'Tiền mặt') {
        button.classList.add('btn-success');
        // Add small delay to ensure modal is fully rendered
        setTimeout(() => {
            showCashPaymentFields();
        }, 100);
    } else if (method === 'Thanh toán VNPAY') {
        button.classList.add('btn-primary');
        hideCashPaymentFields();
        processVNPayPayment();
    }
}

function showCashPaymentFields() {
    const cashFields = document.getElementById('cashPaymentFields');
    const confirmBtn = document.getElementById('confirmPaymentBtn');
    const totalAmount = document.getElementById('totalAmount');
    const modalAmount = document.getElementById('modalAmount');
    
    if (cashFields && confirmBtn && totalAmount && modalAmount) {
        cashFields.style.display = 'block';
        confirmBtn.style.display = 'inline-block';
        
        // Get total amount from modal (which comes from database via receipt.tong_nguoi_benh)
        const amountText = modalAmount.textContent;
        const amount = parseFloat(amountText.replace(/[^\d]/g, ''));
        const formattedAmount = formatCurrency(amount);
        
        totalAmount.value = formattedAmount;
        
        // Add event listener for customer amount input
        const customerAmount = document.getElementById('customerAmount');
        if (customerAmount) {
            customerAmount.addEventListener('input', function() {
                formatNumberInput(this);
                calculateChange();
            });
            
            // Prevent non-numeric input
            customerAmount.addEventListener('keypress', function(e) {
                // Allow: backspace, delete, tab, escape, enter
                if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                    // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                    (e.keyCode === 65 && e.ctrlKey === true) ||
                    (e.keyCode === 67 && e.ctrlKey === true) ||
                    (e.keyCode === 86 && e.ctrlKey === true) ||
                    (e.keyCode === 88 && e.ctrlKey === true)) {
                    return;
                }
                // Ensure that it is a number and stop the keypress
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });
        }
    }
}

function hideCashPaymentFields() {
    const cashFields = document.getElementById('cashPaymentFields');
    const confirmBtn = document.getElementById('confirmPaymentBtn');
    
    if (cashFields && confirmBtn) {
        cashFields.style.display = 'none';
        confirmBtn.style.display = 'none';
    }
}

function calculateChange() {
    const totalAmount = document.getElementById('totalAmount');
    const customerAmount = document.getElementById('customerAmount');
    const changeAmount = document.getElementById('changeAmount');
    
    
    if (totalAmount && customerAmount && changeAmount) {
        // Remove currency symbol and dots, then parse
        const total = parseFloat(totalAmount.value.replace(/[^\d]/g, ''));
        const customer = parseFloat(customerAmount.value.replace(/[^\d]/g, '') || 0);
        const change = customer - total;
        
        
        if (change >= 0) {
            changeAmount.value = formatCurrency(change);
            changeAmount.classList.remove('text-danger');
            changeAmount.classList.add('text-success');
        } else {
            changeAmount.value = formatCurrency(Math.abs(change)) + ' (Thiếu)';
            changeAmount.classList.remove('text-success');
            changeAmount.classList.add('text-danger');
        }
    } else {
    }
}

function confirmPayment() {
    const customerAmount = document.getElementById('customerAmount');
    const totalAmount = document.getElementById('totalAmount');
    
    if (!customerAmount || !customerAmount.value) {
        alert('Vui lòng nhập số tiền khách đưa');
        return;
    }
    
    // Remove currency symbol and dots, then parse
    const total = parseFloat(totalAmount.value.replace(/[^\d]/g, ''));
    const customer = parseFloat(customerAmount.value.replace(/[^\d]/g, ''));
    
    if (customer < total) {
        alert('Số tiền khách đưa không đủ để thanh toán');
        return;
    }
    
    // Process payment
    processPayment();
}

function processPayment() {
    const receiptId = document.getElementById('paymentReceiptId').value;
    const paymentMethod = document.getElementById('paymentMethod').value;
    
    if (!receiptId) {
        showError('Không tìm thấy ID biên lai');
        return;
    }
    
    if (!paymentMethod) {
        showError('Vui lòng chọn phương thức thanh toán');
        return;
    }
    
    
    // Disable all payment method buttons during processing
    document.querySelectorAll('.payment-method-btn').forEach(btn => {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...';
    });
    
    // Prepare payment note with cash details
    let paymentNote = '';
    if (paymentMethod === 'Tiền mặt') {
        const customerAmount = document.getElementById('customerAmount');
        const changeAmount = document.getElementById('changeAmount');
        if (customerAmount && changeAmount) {
            const customer = customerAmount.value;
            const change = changeAmount.value;
            paymentNote = `Khách đưa: ${customer} | Trả lại: ${change}`;
        }
    }
    
    const formData = new FormData();
    formData.append('receipt_id', receiptId);
    formData.append('payment_method', paymentMethod);
    formData.append('payment_note', paymentNote);
    
    
    fetch('./?action=reception_process_payment', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Thanh toán thành công!');
            
            // Reset payment buttons before closing modal
            resetPaymentButtons();
            
            // Close modal
            const paymentModal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
            if (paymentModal) {
                paymentModal.hide();
            }
            
            // Reload receipts
            loadReceipts();
        } else {
            showError('Lỗi khi xử lý thanh toán: ' + data.message);
            // Re-enable buttons on error
            resetPaymentButtons();
        }
    })
    .catch(error => {
        console.error('Error processing payment:', error);
        showError('Lỗi khi xử lý thanh toán');
        // Re-enable buttons on error
        resetPaymentButtons();
    });
}

function processVNPayPayment() {
    const receiptId = document.getElementById('paymentReceiptId').value;
    const modalAmount = document.getElementById('modalAmount');
    
    if (!receiptId) {
        showError('Không tìm thấy ID biên lai');
        return;
    }
    
    if (!modalAmount) {
        showError('Không tìm thấy số tiền thanh toán');
        return;
    }
    
    // Get amount from modal
    const amountText = modalAmount.textContent;
    const amount = parseFloat(amountText.replace(/[^\d]/g, ''));
    
    if (amount <= 0) {
        showError('Số tiền thanh toán không hợp lệ');
        return;
    }
    
    
    // Disable all payment method buttons during processing
    document.querySelectorAll('.payment-method-btn').forEach(btn => {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang tạo thanh toán...';
    });
    
    const formData = new FormData();
    formData.append('receipt_id', receiptId);
    formData.append('amount', amount);
    
    fetch('./?action=reception_create_vnpay_url', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.vnpay_url) {
            // Redirect to VNPAY in same tab
            window.location.href = data.vnpay_url;
        } else {
            showError('Lỗi khi tạo thanh toán VNPAY: ' + data.message);
            resetPaymentButtons();
        }
    })
    .catch(error => {
        console.error('Error creating VNPAY payment:', error);
        showError('Lỗi khi tạo thanh toán VNPAY');
        resetPaymentButtons();
    });
}

function resetPaymentButtons() {
    document.querySelectorAll('.payment-method-btn').forEach(btn => {
        btn.disabled = false;
        btn.classList.remove('active', 'btn-success', 'btn-primary', 'btn-info');
        btn.classList.add('btn-outline-success', 'btn-outline-primary', 'btn-outline-info');
        
        const method = btn.getAttribute('data-method');
        if (method === 'Tiền mặt') {
            btn.innerHTML = `
                <div class="d-flex flex-column align-items-center">
                    <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                    <span>Tiền mặt</span>
                </div>
            `;
        } else if (method === 'Thanh toán VNPAY') {
            btn.innerHTML = `
                <div class="d-flex flex-column align-items-center">
                    <i class="fas fa-credit-card fa-2x mb-2"></i>
                    <span>Thanh toán VNPAY</span>
                </div>
            `;
        }
    });
    
    // Reset payment method selection
    const paymentMethodInput = document.getElementById('paymentMethod');
    if (paymentMethodInput) {
        paymentMethodInput.value = '';
    }
    
    // Reset cash payment fields
    const customerAmount = document.getElementById('customerAmount');
    const changeAmount = document.getElementById('changeAmount');
    if (customerAmount) customerAmount.value = '';
    if (changeAmount) {
        changeAmount.value = '';
        changeAmount.classList.remove('text-success', 'text-danger');
    }
}

function viewReceiptDetails(receiptId) {
    
    fetch(`./?action=reception_get_receipt_details&id=${receiptId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const receipt = data.data.receipt;
                const details = data.data.details;
                
                // Fill modal with receipt data
                document.getElementById('detailPatientCode').textContent = receipt.ma_benh_nhan || 'N/A';
                document.getElementById('detailReceiptCode').textContent = receipt.ma_bien_lai;
                document.getElementById('detailPatientName').textContent = receipt.ho_ten || 'N/A';
                document.getElementById('detailPatientAge').textContent = receipt.tuoi || 'N/A';
                document.getElementById('detailPatientGender').textContent = receipt.gioi_tinh || 'N/A';
                document.getElementById('detailPatientAddress').textContent = receipt.dia_chi || 'N/A';
                document.getElementById('detailPatientInsurance').textContent = receipt.so_the_bhyt || 'Thu phí';
                
                // Format amounts for summary section
                document.getElementById('detailPatientAmountText').textContent = formatCurrency(receipt.tong_nguoi_benh);
                
                // Amount in words
                document.getElementById('detailAmountInWords').textContent = convertNumberToWords(receipt.tong_nguoi_benh);
                
                // Created date and creator
                document.getElementById('detailCreatedDate').textContent = formatDate(receipt.ngay_lap);
                document.getElementById('detailCreatedBy').textContent = receipt.ten_bac_si || receipt.nguoi_lap || 'N/A';
                
                // Populate services table
                populateServicesTable(details);
                
                // Show modal
                const detailModal = new bootstrap.Modal(document.getElementById('receiptDetailModal'));
                detailModal.show();
            } else {
                showError('Không thể lấy thông tin biên lai: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching receipt details:', error);
            showError('Lỗi khi lấy thông tin biên lai');
        });
}

function populateServicesTable(details) {
    const tbody = document.getElementById('detailServicesTable');
    
    if (!details || details.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center">Không có dịch vụ nào</td>
            </tr>
        `;
        return;
    }
    
    // Group services by category
    const groupedServices = groupServicesByCategory(details);
    let html = '';
    let itemNumber = 1;
    
    // Add each category group
    Object.keys(groupedServices).forEach(category => {
        const services = groupedServices[category];
        
        // Add category header row
        html += `
            <tr class="category-row">
                <td colspan="7">${category}</td>
            </tr>
        `;
        
        // Add service rows for this category
        services.forEach(service => {
            html += `
                <tr class="service-row">
                    <td>${itemNumber}</td>
                    <td>${service.ten_dich_vu}</td>
                    <td>${service.so_luong}</td>
                    <td>${formatNumber(service.don_gia)}</td>
                    <td>${formatNumber(service.thanh_tien)}</td>
                    <td>${formatNumber(service.quy_bhyt || 0)}</td>
                    <td>${formatNumber(service.nguoi_benh || 0)}</td>
                </tr>
            `;
            itemNumber++;
        });
    });
    
    // Add total row
    const totalAmount = details.reduce((sum, detail) => sum + parseFloat(detail.thanh_tien || 0), 0);
    const totalInsurance = details.reduce((sum, detail) => sum + parseFloat(detail.quy_bhyt || 0), 0);
    const totalPatient = details.reduce((sum, detail) => sum + parseFloat(detail.nguoi_benh || 0), 0);
    
    html += `
        <tr class="total-row">
            <td colspan="2"><strong>Tổng Cộng</strong></td>
            <td></td>
            <td></td>
            <td><strong>${formatNumber(totalAmount)}</strong></td>
            <td><strong>${formatNumber(totalInsurance)}</strong></td>
            <td><strong>${formatNumber(totalPatient)}</strong></td>
        </tr>
    `;
    
    tbody.innerHTML = html;
}

function groupServicesByCategory(details) {
    const groups = {};
    
    details.forEach(detail => {
        let category = 'Dịch vụ khác';
        
        // Determine category based on service name
        const serviceName = detail.ten_dich_vu.toLowerCase();
        
        if (serviceName.includes('khám') && serviceName.includes('bệnh')) {
            category = 'Khám bệnh lâm sàng';
        } else if (serviceName.includes('xét nghiệm') || serviceName.includes('máu') || serviceName.includes('nước tiểu')) {
            category = 'Khám bệnh cận lâm sàng';
        } else if (serviceName.includes('siêu âm') || serviceName.includes('x-quang') || serviceName.includes('xray')) {
            category = 'Khám bệnh cận lâm sàng';
        } else if (serviceName.includes('xương') || serviceName.includes('cánh tay') || serviceName.includes('chân')) {
            category = 'Khám bệnh lâm sàng';
        } else if (serviceName.includes('amoxicillin') || serviceName.includes('aspirin') || 
                   serviceName.includes('paracetamol') || serviceName.includes('salbutamol') ||
                   serviceName.includes('mg') || serviceName.includes('mcg')) {
            category = 'Thuốc';
        }
        
        if (!groups[category]) {
            groups[category] = [];
        }
        groups[category].push(detail);
    });
    
    return groups;
}

function convertNumberToWords(amount) {
    if (!amount || amount === 0) return 'không đồng';
    
    const ones = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
    const tens = ['', '', 'hai mươi', 'ba mươi', 'bốn mươi', 'năm mươi', 'sáu mươi', 'bảy mươi', 'tám mươi', 'chín mươi'];
    const hundreds = ['', 'một trăm', 'hai trăm', 'ba trăm', 'bốn trăm', 'năm trăm', 'sáu trăm', 'bảy trăm', 'tám trăm', 'chín trăm'];
    
    const millions = Math.floor(amount / 1000000);
    const thousands = Math.floor((amount % 1000000) / 1000);
    const remainder = amount % 1000;
    
    let result = '';
    
    if (millions > 0) {
        result += convertHundreds(millions) + ' triệu ';
    }
    
    if (thousands > 0) {
        result += convertHundreds(thousands) + ' nghìn ';
    }
    
    if (remainder > 0) {
        result += convertHundreds(remainder);
    }
    
    return result.trim() + ' đồng';
}

function convertHundreds(num) {
    const ones = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
    const tens = ['', '', 'hai mươi', 'ba mươi', 'bốn mươi', 'năm mươi', 'sáu mươi', 'bảy mươi', 'tám mươi', 'chín mươi'];
    const hundreds = ['', 'một trăm', 'hai trăm', 'ba trăm', 'bốn trăm', 'năm trăm', 'sáu trăm', 'bảy trăm', 'tám trăm', 'chín trăm'];
    
    if (num === 0) return '';
    
    const h = Math.floor(num / 100);
    const t = Math.floor((num % 100) / 10);
    const o = num % 10;
    
    let result = '';
    
    if (h > 0) {
        result += hundreds[h] + ' ';
    }
    
    if (t > 1) {
        result += tens[t] + ' ';
        if (o > 0) {
            result += ones[o];
        }
    } else if (t === 1) {
        result += 'mười ';
        if (o > 0) {
            result += ones[o];
        }
    } else if (o > 0) {
        result += ones[o];
    }
    
    return result.trim();
}


// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount || 0);
}

function formatNumber(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount || 0);
}

function formatNumberInput(input) {
    
    // Get current cursor position
    const cursorPosition = input.selectionStart;
    
    // Remove all non-numeric characters and currency symbol
    const numericValue = input.value.replace(/[^\d]/g, '');
    
    // Format with dots as thousands separators
    const formattedValue = numericValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    
    // Add currency symbol
    const finalValue = formattedValue + ' ₫';
    
    // Update input value
    input.value = finalValue;
    
    // Restore cursor position (adjust for added dots and currency symbol) - only for text inputs
    if (input.type === 'text') {
        const dotsAdded = formattedValue.length - numericValue.length;
        const currencyLength = 2; // " ₫"
        const newCursorPosition = Math.min(cursorPosition + dotsAdded, formattedValue.length);
        input.setSelectionRange(newCursorPosition, newCursorPosition);
    }
    
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN');
}

function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString('vi-VN');
}

function showError(message) {
    // You can implement a toast notification or alert here
    alert('Lỗi: ' + message);
}

function showSuccess(message) {
    // You can implement a toast notification or alert here
    alert('Thành công: ' + message);
}
