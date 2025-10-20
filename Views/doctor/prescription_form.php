<?php
// Prescription Form View - Giao diện kê đơn thuốc
?>

<!-- Prescription Form Section -->
<div class="card mb-3 exam-section" id="sec-prescription">
    <div class="card-header bg-success text-white">
        <h6 class="mb-0"><i class="fas fa-pills me-2"></i>Kê đơn thuốc</h6>
    </div>
    <div class="card-body">
        <!-- Header Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Tên đơn vị:</label>
                    <input type="text" class="form-control" name="ten_don_vi" value="TRUNG TÂM Y TẾ HUYỆN THANH THỦY" readonly>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Địa chỉ:</label>
                    <input type="text" class="form-control" name="dia_chi_don_vi" placeholder="Nhập địa chỉ đơn vị">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Mã đơn thuốc:</label>
                    <input type="text" class="form-control" name="ma_don_thuoc" id="ma_don_thuoc" readonly>
                </div>
            </div>
        </div>

        <!-- Patient Information -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Họ tên:</label>
                    <input type="text" class="form-control" name="ho_ten_benh_nhan" id="prescription_patient_name" readonly>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Mã số BHYT (nếu có):</label>
                    <input type="text" class="form-control" name="ma_bhyt" id="prescription_bhyt">
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Mã định danh công dân:</label>
                    <input type="text" class="form-control" name="ma_dinh_danh" id="prescription_citizen_id">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Ngày sinh:</label>
                    <input type="date" class="form-control" name="ngay_sinh" id="prescription_dob" readonly>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">CMT/CCCD (nếu có):</label>
                    <input type="text" class="form-control" name="cmt_cccd" id="prescription_id_card">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Cân nặng:</label>
                    <input type="number" class="form-control" name="can_nang" id="prescription_weight" placeholder="kg">
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Giới tính:</label>
                    <input type="text" class="form-control" name="gioi_tinh" id="prescription_gender" readonly>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Mã định danh y tế:</label>
                    <input type="text" class="form-control" name="ma_dinh_danh_y_te" id="prescription_medical_id">
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Số điện thoại:</label>
                    <input type="text" class="form-control" name="so_dien_thoai" id="prescription_phone" readonly>
                </div>
            </div>
        </div>

        <!-- Address -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Địa chỉ liên hệ:</label>
                    <input type="text" class="form-control" name="dia_chi_lien_he" id="prescription_address" readonly>
                </div>
            </div>
        </div>

        <!-- Diagnosis Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Chẩn đoán:</label>
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="ma_chan_doan" placeholder="Mã" id="diagnosis_code">
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="chan_doan" placeholder="Chẩn đoán" id="diagnosis_description">
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Lưu ý:</label>
                    <input type="text" class="form-control" name="luu_y" placeholder="Không" id="prescription_notes">
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Hình thức điều trị:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="hinh_thuc_dieu_tri" id="inpatient" value="Nội trú">
                        <label class="form-check-label" for="inpatient">Nội trú</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="hinh_thuc_dieu_tri" id="outpatient" value="Ngoại trú" checked>
                        <label class="form-check-label" for="outpatient">Ngoại trú</label>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Kết luận:</label>
                    <textarea class="form-control" name="ket_luan" rows="6" id="prescription_conclusion" placeholder="Nhập kết luận..."></textarea>
                </div>
            </div>
        </div>

        <!-- Medication Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">Thuốc điều trị</h6>
                    <button type="button" class="btn btn-primary btn-sm" id="add-medication-btn">
                        <i class="fas fa-plus me-1"></i>Thêm thuốc
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="medication-table">
                        <thead class="table-success">
                            <tr>
                                <th width="10%">Mã thuốc</th>
                                <th width="20%">Hoạt chất</th>
                                <th width="25%">Tên thuốc</th>
                                <th width="8%">ĐVT</th>
                                <th width="8%">SL</th>
                                <th width="20%">Cách dùng</th>
                                <th width="9%">Đã bán</th>
                                <th width="5%">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="medication-tbody">
                            <!-- Medication rows will be added here dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Lời dặn:</label>
                    <input type="text" class="form-control" name="loi_dan" id="prescription_instructions" placeholder="Bất thường đến khám lại">
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Lịch tái khám sau:</label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="lich_tai_kham" id="prescription_followup_days" placeholder="7">
                        <span class="input-group-text">ngày hoặc khi có biểu hiện bất thường</span>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Khám lại mang theo đơn này</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Tên bố hoặc mẹ của trẻ hoặc người đưa trẻ đến khám chữa bệnh:</label>
                    <input type="text" class="form-control" name="ten_nguoi_dua" id="prescription_guardian">
                </div>
            </div>
        </div>

        <!-- Signature and Date -->
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Ngày:</label>
                    <input type="text" class="form-control" name="ngay_ky" id="prescription_date" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Bác sỹ:</label>
                    <input type="text" class="form-control" name="ten_bac_si" id="prescription_doctor" readonly>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-4">
            <div class="col-12 text-end">
                <button type="button" class="btn btn-success me-2" id="save-prescription-btn">
                    <i class="fas fa-save me-1"></i>Lưu đơn thuốc
                </button>
                <button type="button" class="btn btn-primary me-2" id="print-prescription-btn">
                    <i class="fas fa-print me-1"></i>In đơn thuốc
                </button>
                <button type="button" class="btn btn-secondary" id="clear-prescription-btn">
                    <i class="fas fa-trash me-1"></i>Xóa đơn
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Medication Row Template (Hidden) -->
<template id="medication-row-template">
    <tr class="medication-row">
        <td>
            <input type="text" class="form-control form-control-sm medication-code" placeholder="Mã thuốc" readonly>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm medication-ingredient" placeholder="Hoạt chất" readonly>
        </td>
        <td>
            <select class="form-control form-control-sm medication-select" onchange="selectMedication(this)">
                <option value="">Chọn thuốc...</option>
            </select>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm medication-unit" placeholder="ĐVT" readonly>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm medication-quantity" placeholder="SL" min="1" value="1">
        </td>
        <td>
            <input type="text" class="form-control form-control-sm medication-usage" placeholder="Cách dùng">
        </td>
        <td>
            <input type="checkbox" class="form-check-input medication-sold">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeMedicationRow(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<!-- CSS Styles -->
<style>
.medication-row input, .medication-row select {
    border: none;
    background: transparent;
}

.medication-row input:focus, .medication-row select:focus {
    border: 1px solid #007bff;
    background: white;
}

#medication-table tbody tr:hover {
    background-color: #f8f9fa;
}

.form-label.fw-bold {
    color: #495057;
    font-size: 0.9rem;
}

.table-success th {
    background-color: #d1e7dd;
    color: #0f5132;
    font-weight: bold;
    font-size: 0.85rem;
}

.exam-section {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

.card-header.bg-success {
    background-color: #198754 !important;
}
</style>

<!-- JavaScript for Prescription Form -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize prescription form
    initializePrescriptionForm();
    
    // Add medication button
    document.getElementById('add-medication-btn').addEventListener('click', addMedicationRow);
    
    // Save prescription button
    document.getElementById('save-prescription-btn').addEventListener('click', savePrescription);
    
    // Print prescription button
    document.getElementById('print-prescription-btn').addEventListener('click', printPrescription);
    
    // Clear prescription button
    document.getElementById('clear-prescription-btn').addEventListener('click', clearPrescription);
    
    // Generate prescription code
    generatePrescriptionCode();
});

function initializePrescriptionForm() {
    // Auto-fill patient information from examination form
    const patientName = document.getElementById('patientName')?.value || '';
    const patientPhone = document.getElementById('patientPhone')?.value || '';
    const patientDob = document.getElementById('patientAge')?.value || '';
    const patientGender = document.querySelector('input[name="gioi_tinh"]:checked')?.value || '';
    const patientAddress = document.querySelector('[name="dia_chi"]?.value || '';
    
    // Fill prescription form
    document.getElementById('prescription_patient_name').value = patientName;
    document.getElementById('prescription_phone').value = patientPhone;
    document.getElementById('prescription_dob').value = patientDob;
    document.getElementById('prescription_gender').value = patientGender;
    document.getElementById('prescription_address').value = patientAddress;
    
    // Set current date and doctor name
    const now = new Date();
    const dateStr = now.getDate().toString().padStart(2, '0') + '/' + 
                   (now.getMonth() + 1).toString().padStart(2, '0') + '/' + 
                   now.getFullYear() + ' ' +
                   now.getHours().toString().padStart(2, '0') + ':' +
                   now.getMinutes().toString().padStart(2, '0') + ':' +
                   now.getSeconds().toString().padStart(2, '0');
    
    document.getElementById('prescription_date').value = dateStr;
    document.getElementById('prescription_doctor').value = 'Bác sĩ'; // Will be filled from doctor info
}

function generatePrescriptionCode() {
    const now = new Date();
    const timestamp = now.getTime();
    const random = Math.floor(Math.random() * 1000);
    const code = 'P' + timestamp.toString().slice(-6) + '-' + random.toString().padStart(3, '0');
    document.getElementById('ma_don_thuoc').value = code;
}

function addMedicationRow() {
    const tbody = document.getElementById('medication-tbody');
    const template = document.getElementById('medication-row-template');
    const clone = template.content.cloneNode(true);
    
    tbody.appendChild(clone);
    
    // Load medication options
    loadMedicationOptions();
}

function loadMedicationOptions() {
    // This will be implemented to load medications from database
    fetch('./?action=get_medications')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const selects = document.querySelectorAll('.medication-select');
                selects.forEach(select => {
                    if (select.children.length === 1) { // Only has default option
                        data.medications.forEach(med => {
                            const option = document.createElement('option');
                            option.value = med.MaThuoc;
                            option.textContent = med.TenThuoc;
                            option.dataset.ingredient = med.HoatChatChinh;
                            option.dataset.unit = med.DonViTinh;
                            option.dataset.usage = med.LieuDung;
                            select.appendChild(option);
                        });
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error loading medications:', error);
        });
}

function selectMedication(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const row = selectElement.closest('tr');
    
    if (selectedOption.value) {
        row.querySelector('.medication-code').value = selectedOption.value;
        row.querySelector('.medication-ingredient').value = selectedOption.dataset.ingredient || '';
        row.querySelector('.medication-unit').value = selectedOption.dataset.unit || '';
        row.querySelector('.medication-usage').value = selectedOption.dataset.usage || '';
    }
}

function removeMedicationRow(button) {
    const row = button.closest('tr');
    row.remove();
}

function savePrescription() {
    // Collect prescription data
    const prescriptionData = {
        ma_don_thuoc: document.getElementById('ma_don_thuoc').value,
        ho_ten_benh_nhan: document.getElementById('prescription_patient_name').value,
        ma_bhyt: document.getElementById('prescription_bhyt').value,
        ma_dinh_danh: document.getElementById('prescription_citizen_id').value,
        ngay_sinh: document.getElementById('prescription_dob').value,
        cmt_cccd: document.getElementById('prescription_id_card').value,
        can_nang: document.getElementById('prescription_weight').value,
        gioi_tinh: document.getElementById('prescription_gender').value,
        ma_dinh_danh_y_te: document.getElementById('prescription_medical_id').value,
        so_dien_thoai: document.getElementById('prescription_phone').value,
        dia_chi_lien_he: document.getElementById('prescription_address').value,
        ma_chan_doan: document.getElementById('diagnosis_code').value,
        chan_doan: document.getElementById('diagnosis_description').value,
        luu_y: document.getElementById('prescription_notes').value,
        hinh_thuc_dieu_tri: document.querySelector('input[name="hinh_thuc_dieu_tri"]:checked')?.value,
        ket_luan: document.getElementById('prescription_conclusion').value,
        loi_dan: document.getElementById('prescription_instructions').value,
        lich_tai_kham: document.getElementById('prescription_followup_days').value,
        ten_nguoi_dua: document.getElementById('prescription_guardian').value,
        ngay_ky: document.getElementById('prescription_date').value,
        ten_bac_si: document.getElementById('prescription_doctor').value,
        medications: []
    };
    
    // Collect medication data
    const medicationRows = document.querySelectorAll('.medication-row');
    medicationRows.forEach(row => {
        const medication = {
            ma_thuoc: row.querySelector('.medication-code').value,
            hoạt_chất: row.querySelector('.medication-ingredient').value,
            ten_thuoc: row.querySelector('.medication-select').selectedOptions[0]?.textContent || '',
            don_vi_tinh: row.querySelector('.medication-unit').value,
            so_luong: row.querySelector('.medication-quantity').value,
            cach_dung: row.querySelector('.medication-usage').value,
            da_ban: row.querySelector('.medication-sold').checked
        };
        prescriptionData.medications.push(medication);
    });
    
    // Send to server
    fetch('./?action=save_prescription', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(prescriptionData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Lưu đơn thuốc thành công!');
        } else {
            alert('Lỗi khi lưu đơn thuốc: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Lỗi khi lưu đơn thuốc');
    });
}

function printPrescription() {
    // Implementation for printing prescription
    window.print();
}

function clearPrescription() {
    if (confirm('Bạn có chắc muốn xóa đơn thuốc?')) {
        // Clear all form fields
        document.getElementById('sec-prescription').querySelectorAll('input, textarea, select').forEach(field => {
            if (field.type !== 'hidden') {
                field.value = '';
            }
        });
        
        // Clear medication table
        document.getElementById('medication-tbody').innerHTML = '';
        
        // Reset prescription code
        generatePrescriptionCode();
    }
}
</script>
