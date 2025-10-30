// Receipt Form Functions
async function initializeReceiptForm() {
    // Debounce/lock to avoid duplicate renders when called multiple times quickly
    if (window._receiptInitInProgress) {
        console.log('Receipt init skipped: already in progress');
        return;
    }
    window._receiptInitInProgress = true;
    // Get current patient info from the examination form
    const patientCode = document.getElementById('exam_ma_benh_nhan')?.value || '';
    const patientName = document.getElementById('patientName')?.value || '';
    const patientAge = document.getElementById('patientAge')?.value || '';
    const patientGender = document.getElementById('patientGender')?.value || '';
    const patientAddress = document.getElementById('dia_chi')?.value || '';
    const patientBHYT = document.getElementById('prescription_bhyt')?.value || '-';
    const today = new Date();
    const day = today.getDate();
    const month = today.getMonth() + 1;
    const year = today.getFullYear();
    const currentDate = `Ngày ${day} tháng ${month} năm ${year}`;
    
    // Clear existing receipt data before reloading
    const tbody = document.querySelector('#receipt_services');
    if (tbody) {
        tbody.innerHTML = '';
        console.log('Cleared existing receipt data');
    }
    
    // Calculate age from birth date if patientAge contains date
    let displayAge = patientAge;
    if (patientAge && patientAge.includes('-')) {
        // If it's a date format (YYYY-MM-DD), calculate age
        const birthDate = new Date(patientAge);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        displayAge = age;
    }
    
    // Fill basic patient info
    document.getElementById('receipt_patient_code').textContent = patientCode;
    document.getElementById('receipt_patient_name').textContent = patientName;
    document.getElementById('receipt_patient_age').textContent = displayAge;
    document.getElementById('receipt_patient_gender').textContent = patientGender;
    document.getElementById('receipt_patient_address').textContent = patientAddress;
    document.getElementById('receipt_patient_bhyt').textContent = patientBHYT;
    document.getElementById('receipt_signature_date').textContent = currentDate;
    
    // Get exam ID from current appointment
    const examId = getCurrentExamId();
    
    // Load receipt code (Số HD)
    if (examId) {
        loadReceiptCode(examId);
    }
    
    // Retry a few times if exam ID isn't ready yet (modal may still be initializing)
    if (!examId) {
        window._receiptInitRetries = (window._receiptInitRetries || 0) + 1;
        if (window._receiptInitRetries <= 5) {
            console.log('Receipt init waiting for exam ID... retry #' + window._receiptInitRetries);
            setTimeout(initializeReceiptForm, 200);
            window._receiptInitInProgress = false;
            return;
        }
        console.warn('Không tìm thấy ID phiếu khám sau khi retry');
        // Show default receipt with only basic exam
        updateReceiptWithData([], patientBHYT, []);
        window._receiptInitInProgress = false;
        return;
    }
    // Reset retry counter on success
    window._receiptInitRetries = 0;
    
    try {
        // Fetch receipt data from API
        const response = await fetch(`./getReceiptData?exam_id=${examId}`);
        const result = await response.json();
        
        
        if (result.success) {
            updateReceiptWithData(result.data, patientBHYT, result.medications);
        } else {
            console.error('Lỗi lấy dữ liệu biên lai:', result.message);
            // Show default receipt with only basic exam
            updateReceiptWithData([], patientBHYT, []);
        }
    } catch (error) {
        console.error('Lỗi kết nối API:', error);
        // Show default receipt with only basic exam
        updateReceiptWithData([], patientBHYT, []);
    }
    
    // Set doctor name from API
    fetch('./get_current_doctor')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.doctor_name) {
                document.getElementById('receipt_doctor_name').textContent = data.doctor_name;
            } else {
                // Fallback to session or other sources
                const doctorName = document.getElementById('current_doctor_name')?.value || 
                                  document.getElementById('prescription_doctor')?.textContent ||
                                  document.getElementById('doctor_name')?.value || 
                                  document.getElementById('ten_bac_si')?.value || 
                                  'Bác sĩ';
                document.getElementById('receipt_doctor_name').textContent = doctorName;
            }
        })
        .catch(error => {
            console.error('Error fetching doctor name:', error);
            // Fallback to session or other sources
            const doctorName = document.getElementById('current_doctor_name')?.value || 
                              document.getElementById('prescription_doctor')?.textContent ||
                              document.getElementById('doctor_name')?.value || 
                              document.getElementById('ten_bac_si')?.value || 
                              'Bác sĩ';
            document.getElementById('receipt_doctor_name').textContent = doctorName;
        });
}

function getCurrentExamId() {
    // Try to get exam ID from various sources
    const examIdInput = document.getElementById('exam_id');
    if (examIdInput && examIdInput.value) return examIdInput.value;
    
    // Try to get from id_phieu_kham_benh (this is what examination.js uses)
    const phieuKhamBenhId = document.getElementById('id_phieu_kham_benh');
    if (phieuKhamBenhId && phieuKhamBenhId.value) return phieuKhamBenhId.value;
    
    
    // Try to get from appointment data
    const appointmentItem = document.querySelector('.appointment-item.active');
    if (appointmentItem) {
        const examId = appointmentItem.getAttribute('data-exam-id');
        if (examId) return examId;
    }
    
    // Try to get from current appointment data in examination form
    const currentAppointment = window.currentAppointment;
    if (currentAppointment && currentAppointment.exam_id) {
        return currentAppointment.exam_id;
    }
    
    // Try to get from window._lastExamFormId (same as examination.js)
    if (window._lastExamFormId) {
        return window._lastExamFormId;
    }
    
    // Try to get from hidden input in examination modal
    const examIdHidden = document.querySelector('input[name="exam_id"]');
    if (examIdHidden && examIdHidden.value) return examIdHidden.value;
    
    // Try to get from appointment ID and convert to exam ID
    const appointmentId = document.getElementById('appointment_id')?.value;
    if (appointmentId) {
        // In some cases, appointment_id might be the same as exam_id
        return appointmentId;
    }
    
    console.log('Available elements for exam ID:', {
        examIdInput: examIdInput?.value,
        phieuKhamBenhId: phieuKhamBenhId?.value,
        appointmentItem: appointmentItem?.getAttribute('data-exam-id'),
        currentAppointment: window.currentAppointment,
        examIdHidden: examIdHidden?.value,
        appointmentId: appointmentId
    });
    
    // Debug: Check all possible exam ID elements
    console.log('Debug - All exam ID related elements:');
    console.log('exam_id element:', document.getElementById('exam_id'));
    console.log('id_phieu_kham_benh element:', document.getElementById('id_phieu_kham_benh'));
    console.log('phieu_kham_id element:', document.getElementById('phieu_kham_id'));
    console.log('examination_id element:', document.getElementById('examination_id'));
    
    // Check all input elements with exam-related names
    const examInputs = document.querySelectorAll('input[name*="exam"], input[name*="phieu"], input[id*="exam"], input[id*="phieu"]');
    console.log('All exam/phieu related inputs:', examInputs);
    examInputs.forEach(input => {
        console.log(`Input: ${input.name || input.id} = ${input.value}`);
    });
    
    return null;
}

function updateReceiptWithData(requests, patientBHYT, medications) {
    // Store medications globally for use in updateReceiptTable
    window.receiptMedications = medications || [];
    
    const hasBHYT = patientBHYT && patientBHYT !== '-' && patientBHYT !== 'Thu phí';
    
    // Totals will be calculated in updateReceiptTable with correct price from database
    let totalBasePrice = 0;
    let totalBhytAmount = 0;
    let totalPatientAmount = 0;
    
    // Group requests by type (tab)
    const groupedRequests = {
        xet_nghiem: [],
        sieu_am: [],
        xquang: []
    };
    
    requests.forEach(request => {
        if (groupedRequests[request.type]) {
            groupedRequests[request.type].push(request);
        }
    });
    
    // Calculate payment for each group
    const calculatedGroups = [];
    
    // Xét nghiệm
    if (groupedRequests.xet_nghiem.length > 0) {
        const totalPrice = groupedRequests.xet_nghiem.reduce((sum, req) => sum + parseInt(req.price), 0);
        let bhytAmount = 0;
        let patientAmount = totalPrice;
        
        if (hasBHYT) {
            bhytAmount = Math.round(totalPrice * 0.8);
            patientAmount = totalPrice - bhytAmount;
        }
        
        totalBasePrice += totalPrice;
        totalBhytAmount += bhytAmount;
        totalPatientAmount += patientAmount;
        
        // Combine all xet_nghiem requests into one content
        const combinedContent = groupedRequests.xet_nghiem.map(req => req.content).join(', ');
        
        calculatedGroups.push({
            type: 'xet_nghiem',
            name: combinedContent,
            basePrice: totalPrice,
            bhytAmount,
            patientAmount,
            requests: groupedRequests.xet_nghiem
        });
    }
    
    // Siêu âm
    if (groupedRequests.sieu_am.length > 0) {
        const totalPrice = groupedRequests.sieu_am.reduce((sum, req) => sum + parseInt(req.price), 0);
        let bhytAmount = 0;
        let patientAmount = totalPrice;
        
        if (hasBHYT) {
            bhytAmount = Math.round(totalPrice * 0.8);
            patientAmount = totalPrice - bhytAmount;
        }
        
        totalBasePrice += totalPrice;
        totalBhytAmount += bhytAmount;
        totalPatientAmount += patientAmount;
        
        // Combine all sieu_am requests into one content
        const combinedContent = groupedRequests.sieu_am.map(req => req.content).join(', ');
        
        calculatedGroups.push({
            type: 'sieu_am',
            name: combinedContent,
            basePrice: totalPrice,
            bhytAmount,
            patientAmount,
            requests: groupedRequests.sieu_am
        });
    }
    
    // X-Quang
    if (groupedRequests.xquang.length > 0) {
        const totalPrice = groupedRequests.xquang.reduce((sum, req) => sum + parseInt(req.price), 0);
        let bhytAmount = 0;
        let patientAmount = totalPrice;
        
        if (hasBHYT) {
            bhytAmount = Math.round(totalPrice * 0.8);
            patientAmount = totalPrice - bhytAmount;
        }
        
        totalBasePrice += totalPrice;
        totalBhytAmount += bhytAmount;
        totalPatientAmount += patientAmount;
        
        // Combine all xquang requests into one content
        const combinedContent = groupedRequests.xquang.map(req => req.content).join(', ');
        
        calculatedGroups.push({
            type: 'xquang',
            name: combinedContent,
            basePrice: totalPrice,
            bhytAmount,
            patientAmount,
            requests: groupedRequests.xquang
        });
    }
    
    // Basic exam payment amounts will be updated in updateReceiptTable with correct price from database
    
    
    // Update the receipt table with grouped requests
    updateReceiptTable(calculatedGroups, totalBasePrice, totalBhytAmount, totalPatientAmount);
}

function updateReceiptTable(groups, totalBasePrice, totalBhytAmount, totalPatientAmount) {
    const tbody = document.getElementById('receipt_services');
    if (!tbody) {
        console.error('receipt_services tbody not found');
        return;
    }
    
    // Clear only the dynamic rows (keep the first row: "Khám bệnh lâm sàng")
    const existingRows = tbody.querySelectorAll('tr');
    for (let i = 1; i < existingRows.length; i++) {
        existingRows[i].remove();
    }
    
    // Update row 2: "Khám bệnh" (basic exam) - Get price from database
    const basicExamRow = document.createElement('tr');
    
    // Get basic exam price from database first, then add other services
    console.log("Fetching basic exam price from database...");
    fetch('./get_dich_vu_kham')
        .then(response => {
            console.log("get_dich_vu_kham response status:", response.status);
            return response.json();
        })
        .then(data => {
            console.log("get_dich_vu_kham response data:", data);
            if (data.success && data.don_gia) {
                const basicExamPrice = data.don_gia;
                console.log("Basic exam price from database:", basicExamPrice);
                const hasBHYT = document.getElementById('receipt_patient_bhyt')?.textContent !== '-' && 
                               document.getElementById('receipt_patient_bhyt')?.textContent !== 'Thu phí';
                const basicBhytAmount = hasBHYT ? Math.round(basicExamPrice * 0.8) : 0;
                const basicPatientAmount = basicExamPrice - basicBhytAmount;
                
                basicExamRow.innerHTML = `
                    <td>1</td>
                    <td>
                        <div>Khám bệnh</div>
                    </td>
                    <td>1</td>
                    <td class="text-end">${basicExamPrice.toLocaleString('vi-VN')}</td>
                    <td class="text-end">${basicExamPrice.toLocaleString('vi-VN')}</td>
                    <td class="text-end" id="receipt_bhyt_amount">${basicBhytAmount.toLocaleString('vi-VN')}</td>
                    <td class="text-end" id="receipt_patient_amount">${basicPatientAmount.toLocaleString('vi-VN')}</td>
                `;
                
                // Update totals (basic exam is the first item, so set totals instead of adding)
                totalBasePrice = basicExamPrice;
                totalBhytAmount = basicBhytAmount;
                totalPatientAmount = basicPatientAmount;
                
                
            } else {
                // Hiển thị lỗi thay vì fallback
                basicExamRow.innerHTML = `
                    <td>1</td>
                    <td>
                        <div>Khám bệnh</div>
                    </td>
                    <td>1</td>
                    <td class="text-end text-danger">LỖI</td>
                    <td class="text-end text-danger">LỖI</td>
                    <td class="text-end text-danger">LỖI</td>
                    <td class="text-end text-danger">LỖI</td>
                `;
                console.error('Lỗi lấy đơn giá:', data.message || 'Không tìm thấy đơn giá dịch vụ');
                alert('Lỗi: ' + (data.message || 'Không tìm thấy đơn giá dịch vụ "Khám bệnh" trong database'));
            }
        })
        .catch(error => {
            console.error('Error fetching dich vu kham:', error);
            console.log("API call failed, showing error in UI");
            // Hiển thị lỗi thay vì fallback
            basicExamRow.innerHTML = `
                <td>1</td>
                <td>
                    <div>Khám bệnh</div>
                </td>
                <td>1</td>
                <td class="text-end text-danger">LỖI</td>
                <td class="text-end text-danger">LỖI</td>
                <td class="text-end text-danger">LỖI</td>
                <td class="text-end text-danger">LỖI</td>
            `;
            alert('Lỗi kết nối: Không thể lấy đơn giá dịch vụ từ database');
        })
        .finally(() => {
            // Always add other services after basic exam is processed (success or error)
            console.log('Basic exam processed, adding other services...');
            addOtherServices();
            // Release init lock after render completes (will also be released below after final totals)
            // Note: kept here to avoid long lock if later code errors
            window._receiptInitInProgress = false;
        });
    
    tbody.appendChild(basicExamRow);
    
    // Function to add other services after basic exam price is loaded
    function addOtherServices() {
        // Add row 3: "Khám bệnh cận lâm sàng" (title row, no data)
    const cậnLâmSàngRow = document.createElement('tr');
    cậnLâmSàngRow.innerHTML = `
        <td colspan="7" class="text-start">
            <div class="fw-bold">Khám bệnh cận lâm sàng</div>
        </td>
    `;
    tbody.appendChild(cậnLâmSàngRow);
    
    // If no additional requests, show "không có chỉ định cận lâm sàng"
    if (groups.length === 0) {
        const noRequestRow = document.createElement('tr');
        noRequestRow.innerHTML = `
            <td colspan="7" class="text-center text-muted">
                <div>Không có chỉ định cận lâm sàng</div>
            </td>
        `;
        tbody.appendChild(noRequestRow);
    } else {
        // Add rows for each group (tab) - starting from row 4
        groups.forEach((group, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>
                    <div>${group.name}</div>
                </td>
                <td>1</td>
                <td class="text-end">${group.basePrice.toLocaleString('vi-VN')}</td>
                <td class="text-end">${group.basePrice.toLocaleString('vi-VN')}</td>
                <td class="text-end">${group.bhytAmount.toLocaleString('vi-VN')}</td>
                <td class="text-end">${group.patientAmount.toLocaleString('vi-VN')}</td>
            `;
            tbody.appendChild(row);
            
            // Add to totals
            totalBasePrice += group.basePrice;
            totalBhytAmount += group.bhytAmount;
            totalPatientAmount += group.patientAmount;
            
        });
    }
    
    // Add "Thuốc điều trị" row after all groups
    const thuốcĐiềuTrịRow = document.createElement('tr');
    thuốcĐiềuTrịRow.innerHTML = `
        <td colspan="7" class="text-start">
            <div class="fw-bold">Thuốc điều trị</div>
        </td>
    `;
    tbody.appendChild(thuốcĐiềuTrịRow);
    
    // Add medication rows if available
    if (window.receiptMedications && window.receiptMedications.length > 0) {
        window.receiptMedications.forEach((medication, index) => {
            const medicationRow = document.createElement('tr');
            const hasBHYT = document.getElementById('receipt_patient_bhyt')?.textContent !== '-' && 
                           document.getElementById('receipt_patient_bhyt')?.textContent !== 'Thu phí';
            const medicationPrice = parseFloat(medication.don_gia || 0);
            const baoHiem = parseInt(medication.bao_hiem || 0);
            
            // Chỉ giảm giá nếu: bệnh nhân có BHYT VÀ thuốc có BaoHiem = 1
            const canGetDiscount = hasBHYT && baoHiem === 1;
            
            const soLuong = parseInt(medication.so_luong || 1);
            const thanhTien = medicationPrice * soLuong;
            const thanhTienBhytAmount = canGetDiscount ? Math.round(thanhTien * 0.8) : 0;
            const thanhTienPatientAmount = thanhTien - thanhTienBhytAmount;
            
            medicationRow.innerHTML = `
                <td>${index + 1}</td>
                <td>
                    <div>${medication.ten_thuoc}</div>
                </td>
                <td>${soLuong}</td>
                <td class="text-end">${medicationPrice.toLocaleString('vi-VN')}</td>
                <td class="text-end">${thanhTien.toLocaleString('vi-VN')}</td>
                <td class="text-end">${thanhTienBhytAmount.toLocaleString('vi-VN')}</td>
                <td class="text-end">${thanhTienPatientAmount.toLocaleString('vi-VN')}</td>
            `;
            tbody.appendChild(medicationRow);
            
            // Update totals
            totalBasePrice += thanhTien;
            totalBhytAmount += thanhTienBhytAmount;
            totalPatientAmount += thanhTienPatientAmount;
            
        });
    } else {
        // Show "Không có thuốc" if no medications
        const noMedicationRow = document.createElement('tr');
        noMedicationRow.innerHTML = `
            <td colspan="7" class="text-center text-muted">
                <div>Không có thuốc</div>
            </td>
        `;
        tbody.appendChild(noMedicationRow);
    }
    
    // Update totals
    document.getElementById('receipt_total_base').textContent = totalBasePrice.toLocaleString('vi-VN');
    document.getElementById('receipt_total_bhyt').textContent = totalBhytAmount.toLocaleString('vi-VN');
    document.getElementById('receipt_total_patient').textContent = totalPatientAmount.toLocaleString('vi-VN');
    document.getElementById('receipt_total_amount').textContent = totalPatientAmount.toLocaleString('vi-VN') + ' đồng';
    
    // Convert number to words
    const numberToWords = (num) => {
        const ones = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
        const tens = ['', '', 'hai mươi', 'ba mươi', 'bốn mươi', 'năm mươi', 'sáu mươi', 'bảy mươi', 'tám mươi', 'chín mươi'];
        
        if (num === 0) return 'không';
        if (num < 10) return ones[num];
        if (num < 20) {
            if (num === 10) return 'mười';
            if (num === 11) return 'mười một';
            if (num === 12) return 'mười hai';
            if (num === 13) return 'mười ba';
            if (num === 14) return 'mười bốn';
            if (num === 15) return 'mười lăm';
            if (num === 16) return 'mười sáu';
            if (num === 17) return 'mười bảy';
            if (num === 18) return 'mười tám';
            if (num === 19) return 'mười chín';
            return 'mười ' + (ones[num - 10] || '');
        }
        if (num < 100) {
            const ten = Math.floor(num / 10);
            const one = num % 10;
            if (ten === 1) {
                return 'mười' + (one > 0 ? ' ' + ones[one] : '');
            }
            return tens[ten] + (one > 0 ? ' ' + ones[one] : '');
        }
        if (num < 1000) {
            const hundred = Math.floor(num / 100);
            const remainder = num % 100;
            return ones[hundred] + ' trăm' + (remainder > 0 ? ' ' + numberToWords(remainder) : '');
        }
        if (num < 1000000) {
            const thousand = Math.floor(num / 1000);
            const remainder = num % 1000;
            return numberToWords(thousand) + ' ngàn' + (remainder > 0 ? ' ' + numberToWords(remainder) : '');
        }
        if (num < 1000000000) {
            const million = Math.floor(num / 1000000);
            const remainder = num % 1000000;
            const millionText = numberToWords(million);
            return millionText + ' triệu' + (remainder > 0 ? ' ' + numberToWords(remainder) : '');
        }
        return num.toString();
    };
    
    document.getElementById('receipt_total_words').textContent = numberToWords(totalPatientAmount) + ' đồng';
    // Ensure lock released after full render as well
    window._receiptInitInProgress = false;
    } // End of addOtherServices function
}

// Initialize receipt form when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Handle receipt tab click
    const receiptTab = document.querySelector('a[href="#sec-result"]');
    if (receiptTab) {
        receiptTab.addEventListener('click', function() {
            initializeReceiptForm();

            // Hide all other buttons
            document.querySelectorAll(
                '#btnSaveExamForm, #btnPrintExamForm, #btnSaveExam, #saveXrayForm, #printXrayForm, #saveUltrasoundForm, #printUltrasoundForm, #saveLabForm, #printLabForm, #save-prescription-btn, #print-prescription-btn'
            ).forEach(btn => {
                btn.style.display = 'none';
            });
            
            // Show receipt buttons
            document.getElementById('save-receipt-btn').style.display = 'inline-block';
            document.getElementById('print-receipt-btn').style.display = 'inline-block';
        });
    }

    // Handle complete examination tab click
    const completeTab = document.querySelector('a[href="#sec-complete"]');
    if (completeTab) {
        completeTab.addEventListener('click', function() {
            // Hide all other buttons
            document.querySelectorAll(
                '#btnSaveExamForm, #btnPrintExamForm, #btnSaveExam, #saveXrayForm, #printXrayForm, #saveUltrasoundForm, #printUltrasoundForm, #saveLabForm, #printLabForm, #save-prescription-btn, #print-prescription-btn, #save-receipt-btn, #print-receipt-btn'
            ).forEach(btn => {
                btn.style.display = 'none';
            });
            
            // No footer button needed - only the content button is used
        });
    }
});

/**
 * Lưu biên lai viện phí
 */
function saveReceipt() {
    console.log("saveReceipt called");
    
    var examId = getCurrentExamId();
    if (!examId) {
        alert("Vui lòng lưu phiếu khám bệnh trước khi lưu biên lai!");
        return;
    }
    
    // Lấy dữ liệu từ receipt form
    var receiptData = collectReceiptData();
    if (!receiptData) {
        alert("Không thể thu thập dữ liệu biên lai!");
        return;
    }
    
    
    // Gửi dữ liệu lên server
    fetch("./?action=save_receipt", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(receiptData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.is_update) {
                alert("Cập nhật biên lai thành công! Mã biên lai: " + data.ma_bien_lai);
            } else {
                alert("Lưu biên lai thành công! Mã biên lai: " + data.ma_bien_lai);
            }
            
            // Reload tab Kê biên lai sau khi lưu thành công
            setTimeout(() => {
                reloadReceiptTab();
            }, 1000);
        } else {
            alert("Lỗi khi lưu biên lai: " + data.message);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Lỗi kết nối khi lưu biên lai");
    });
}

/**
 * Thu thập dữ liệu biên lai từ form
 */
function collectReceiptData() {
    try {
        var examId = getCurrentExamId();
        if (!examId) return null;
        
        // Lấy tổng tiền từ các element
        var totalBase = parseFloat(document.getElementById('receipt_total_base')?.textContent?.replace(/[^\d]/g, '') || 0);
        var totalBhyt = parseFloat(document.getElementById('receipt_total_bhyt')?.textContent?.replace(/[^\d]/g, '') || 0);
        var totalPatient = parseFloat(document.getElementById('receipt_total_patient')?.textContent?.replace(/[^\d]/g, '') || 0);
        
        // Thu thập chi tiết từ bảng receipt
        var chiTiet = [];
        var receiptTable = document.querySelector('#receipt_services');
        
        if (receiptTable) {
            var rows = receiptTable.querySelectorAll('tr');
            
            rows.forEach(function(row, index) {
                var cells = row.querySelectorAll('td');
                
                if (cells.length >= 6) {
                    var tenDichVu = cells[1]?.textContent?.trim() || '';
                    var soLuong = parseInt(cells[2]?.textContent?.trim() || '1');
                    var donGia = parseFloat(cells[3]?.textContent?.replace(/[^\d]/g, '') || '0');
                    var thanhTien = parseFloat(cells[4]?.textContent?.replace(/[^\d]/g, '') || '0');
                    var quyBhyt = parseFloat(cells[5]?.textContent?.replace(/[^\d]/g, '') || '0');
                    var nguoiBenh = parseFloat(cells[6]?.textContent?.replace(/[^\d]/g, '') || '0');
                    
                    // Bỏ qua các dòng tiêu đề hoặc trống
                    if (tenDichVu && tenDichVu !== 'Tên dịch vụ' && donGia > 0) {
                        chiTiet.push({
                            loai_dich_vu: getLoaiDichVu(tenDichVu),
                            ten_dich_vu: tenDichVu,
                            so_luong: soLuong,
                            don_gia: donGia,
                            thanh_tien: thanhTien,
                            quy_bhyt: quyBhyt,
                            nguoi_benh: nguoiBenh,
                            bao_hiem: quyBhyt > 0 ? 1 : 0,
                            ghi_chu: ''
                        });
                    }
                }
            });
        }
        
        return {
            id_phieu_kham_benh: examId,
            tong_tien_co_ban: totalBase,
            tong_quy_bhyt: totalBhyt,
            tong_nguoi_benh: totalPatient,
            ghi_chu: "Biên lai tự động tạo từ hệ thống",
            chi_tiet: chiTiet
        };
    } catch (error) {
        console.error("Error collecting receipt data:", error);
        return null;
    }
}

// Helper function để xác định loại dịch vụ
function getLoaiDichVu(tenDichVu) {
    if (tenDichVu.toLowerCase().includes('khám bệnh')) return 'Kham benh';
    if (tenDichVu.toLowerCase().includes('xét nghiệm') || tenDichVu.toLowerCase().includes('xet nghiem')) return 'Xet nghiem';
    if (tenDichVu.toLowerCase().includes('siêu âm') || tenDichVu.toLowerCase().includes('sieu am')) return 'Sieu am';
    if (tenDichVu.toLowerCase().includes('x-quang') || tenDichVu.toLowerCase().includes('xquang')) return 'X-Quang';
    if (tenDichVu.toLowerCase().includes('thuốc') || tenDichVu.toLowerCase().includes('thuoc')) return 'Thuoc';
    return 'Kham benh'; // Default
}

// Load receipt code (Số HD) from API
async function loadReceiptCode(examId) {
    try {
        const response = await fetch(`./?action=get_receipt_code&exam_id=${encodeURIComponent(examId)}`);
        const data = await response.json();
        
        const soHdElement = document.getElementById('receipt_so_hd');
        if (soHdElement) {
            if (data.success && data.ma_bien_lai) {
                soHdElement.textContent = data.ma_bien_lai;
            } else {
                soHdElement.textContent = '-';
            }
        }
    } catch (error) {
        console.error('Error loading receipt code:', error);
        const soHdElement = document.getElementById('receipt_so_hd');
        if (soHdElement) {
            soHdElement.textContent = '-';
        }
    }
}

// Reload tab Kê biên lai sau khi lưu thành công
function reloadReceiptTab() {
    console.log("Reloading receipt tab...");
    
    // Đảm bảo tab Kê biên lai vẫn active
    const receiptTab = document.querySelector('a[href="#sec-result"]');
    if (receiptTab) {
        // Trigger click để đảm bảo tab vẫn active và tự động gọi initializeReceiptForm()
        receiptTab.click();
    }
    
    console.log("Receipt tab reloaded successfully!");
}

// In biên lai viện phí
function printReceiptForm() {
    console.log("printReceiptForm called");
    
    const examId = getCurrentExamId();
    console.log("Current exam ID:", examId);
    console.log("Exam ID type:", typeof examId);
    console.log("Exam ID length:", examId ? examId.length : 'null');
    
    if (!examId) {
        console.log("No exam ID found, showing alert");
        alert("Vui lòng lưu phiếu khám bệnh trước khi in biên lai!");
        return;
    }
    
    console.log("Fetching receipt data for exam ID:", examId);
    
    // Lấy ID biên lai từ exam ID
    fetch(`./?action=get_receipt_code&exam_id=${encodeURIComponent(examId)}`)
        .then(response => {
            console.log("Response status:", response.status);
            return response.json();
        })
        .then(data => {
            console.log("Receipt code response:", data);
            if (data.success && data.ma_bien_lai) {
                console.log("Opening print window with receipt ID:", data.ma_bien_lai);
                // Mở cửa sổ in với ID biên lai
                const printUrl = `./?action=print_receipt_form&id=${data.ma_bien_lai}`;
                console.log("Print URL:", printUrl);
                window.open(printUrl, "_blank");
            } else {
                console.log("No receipt found:", data.message);
                alert("Chưa có biên lai để in. Vui lòng lưu biên lai trước.");
            }
        })
        .catch(error => {
            console.error("Error fetching receipt code:", error);
            alert("Lỗi kết nối khi lấy thông tin biên lai");
        });
}
