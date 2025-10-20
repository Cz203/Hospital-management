// Prescription Form JavaScript
class PrescriptionManager {
    constructor() {
        this.medications = [];
        this.prescriptionData = {};
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadMedications();
    }

    bindEvents() {
        // Tab click event
        document.querySelector('a[href="#sec-prescription"]')?.addEventListener('click', () => {
            this.initializeForm();
        });
        
        // Add medication button
        document.getElementById('add-medication-btn')?.addEventListener('click', () => {
            this.addMedicationRow();
        });
        
        // Save prescription button
        document.getElementById('save-prescription-btn')?.addEventListener('click', () => {
            this.savePrescription();
        });
        
        // Print functionality removed
        
        // Clear prescription button
        document.getElementById('clear-prescription-btn')?.addEventListener('click', () => {
            this.clearPrescription();
        });
    }

    initializeForm() {
        this.autoFillPatientInfo();
        this.generatePrescriptionCode();
        this.setCurrentDateTime();
        this.setupDefaultMedicationRow();
        // Try load saved prescription
        this.tryLoadSavedPrescription();
    }
    
    setupDefaultMedicationRow() {
        // Setup autocomplete for the default medication row
        const defaultRow = document.querySelector('#medication-tbody tr.medication-row');
        if (defaultRow) {
            // Add event listeners giống như trong addMedicationRow
            const nameInput = defaultRow.querySelector('.medication-name-input');
            const suggestionDropdown = defaultRow.querySelector('.medication-suggestion-dropdown');
            
            // Add quantity validation for default row
            const quantityInput = defaultRow.querySelector('.medication-quantity');
            if (quantityInput) {
                // Remove existing listeners first to avoid duplicates
                quantityInput.removeEventListener('input', validateQuantity);
                quantityInput.removeEventListener('blur', validateQuantity);
                
                quantityInput.addEventListener('input', function() {
                    console.log('Default row input event triggered for quantity:', this.value);
                    validateQuantity(this);
                });
                
                quantityInput.addEventListener('blur', function() {
                    console.log('Default row blur event triggered for quantity:', this.value);
                    validateQuantity(this);
                });
            }
            
            if (nameInput && suggestionDropdown) {
                // Handle input events
                nameInput.addEventListener('input', function() {
                    const keyword = this.value.trim();
                    if (keyword.length >= 1) {
                        // Remove existing dropdown if any
                        const existingDropdown = document.querySelector('.medication-suggestion-dropdown');
                        if (existingDropdown) {
                            existingDropdown.remove();
                        }
                        
                        // Create new dropdown
                        const newDropdown = document.createElement('div');
                        newDropdown.className = 'medication-suggestion-dropdown';
                        newDropdown.style.cssText = `
                            position: fixed !important;
                            z-index: 99999 !important;
                            background: #ffffff !important;
                            border: 1px solid #dee2e6 !important;
                            border-radius: 8px !important;
                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
                            max-height: 200px !important;
                            overflow-y: auto !important;
                            width: 300px !important;
                            font-size: 14px !important;
                            font-weight: 500 !important;
                            opacity: 1 !important;
                            visibility: visible !important;
                            pointer-events: auto !important;
                        `;
                        
                        // Append to body instead of row
                        document.body.appendChild(newDropdown);
                        
                        loadMedicationSuggestions(keyword, newDropdown, defaultRow);
                    } else {
                        // Remove all dropdowns
                        document.querySelectorAll('.medication-suggestion-dropdown').forEach(dropdown => {
                            dropdown.remove();
                        });
                    }
                });
                
                // Handle focus events
                nameInput.addEventListener('focus', function() {
                    const keyword = this.value.trim();
                    if (keyword.length >= 1) {
                        // Remove existing dropdown if any
                        const existingDropdown = document.querySelector('.medication-suggestion-dropdown');
                        if (existingDropdown) {
                            existingDropdown.remove();
                        }
                        
                        // Create new dropdown
                        const newDropdown = document.createElement('div');
                        newDropdown.className = 'medication-suggestion-dropdown';
                        newDropdown.style.cssText = `
                            position: fixed !important;
                            z-index: 99999 !important;
                            background: #ffffff !important;
                            border: 1px solid #dee2e6 !important;
                            border-radius: 8px !important;
                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
                            max-height: 200px !important;
                            overflow-y: auto !important;
                            width: 300px !important;
                            font-size: 14px !important;
                            font-weight: 500 !important;
                            opacity: 1 !important;
                            visibility: visible !important;
                            pointer-events: auto !important;
                        `;
                        
                        // Append to body instead of row
                        document.body.appendChild(newDropdown);
                        
                        loadMedicationSuggestions(keyword, newDropdown, defaultRow);
                    }
                });
            }
        }
    }

    async tryLoadSavedPrescription() {
        try {
            const examId = document.getElementById('prescription_examination_id')?.value || document.getElementById('examinationAppointmentId')?.value || '';
            if (!examId) return;
            const resp = await fetch(`./?action=get_prescription_by_exam&exam_id=${encodeURIComponent(examId)}`);
            const data = await resp.json();
            if (!data.success || !data.prescription) return;
            const p = data.prescription;
            // Fill header fields
            document.getElementById('ma_don_thuoc') && (document.getElementById('ma_don_thuoc').value = p.MaDonThuoc || '');
            document.getElementById('prescription-code-display-value') && (document.getElementById('prescription-code-display-value').textContent = p.MaDonThuoc || '');
            document.getElementById('prescription_patient_name') && (document.getElementById('prescription_patient_name').value = p.ten_benh_nhan || document.getElementById('prescription_patient_name').value);
            document.getElementById('diagnosis_description') && (document.getElementById('diagnosis_description').value = p.ChanDoan || '');
            // Fill instructions (Lời dặn) from GhiChu
            document.getElementById('prescription_instructions') && (document.getElementById('prescription_instructions').value = p.GhiChu || '');
            // Clear current medication rows
            const tbody = document.getElementById('medication-tbody');
            if (tbody) {
                tbody.innerHTML = '';
                // Render medications
                (p.medications || []).forEach((m, idx) => {
                    const row = document.createElement('tr');
                    row.className = 'medication-row';
                    row.setAttribute('data-ma-thuoc', m.MaThuoc || '');
                    row.innerHTML = `
                        <td class="text-center"><span>${idx + 1}</span></td>
                        <td class="text-center" style="position: relative;">
                            <input type="text" class="form-control form-control-sm medication-name-input" value="${m.TenThuoc || ''}" autocomplete="off" />
                            <div class="medication-suggestion-dropdown" style="display: none; position: absolute; z-index: 1000; background: white; border: 1px solid #ccc; max-height: 200px; overflow-y: auto; width: 100%;"></div>
                        </td>
                        <td class="text-center">
                            <input type="text" class="form-control form-control-sm medication-ingredient" value="${m.HoatChatChinh || m.HoatChat || ''}" readonly>
                        </td>
                        <td class="text-center">
                            <input type="text" class="form-control form-control-sm medication-unit" value="${m.DonViTinh || m.DonViTinhThuoc || ''}" readonly>
                        </td>
                        <td class="text-center">
                            <input type="number" class="form-control form-control-sm medication-quantity" value="${m.SoLuong || 1}" min="1">
                        </td>
                        <td class="text-center">
                            <input type="text" class="form-control form-control-sm medication-usage" value="${m.LieuDung || m.LieuDungThuoc || ''}">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeMedicationRow(this)"><i class="fas fa-minus"></i></button>
                        </td>`;
                    tbody.appendChild(row);
                });
            }
        } catch (e) {
            console.error('Error loading saved prescription:', e);
        }
    }

    autoFillPatientInfo() {
        // Get patient info from examination form
        const patientName = document.getElementById('patientName')?.value || '';
        const patientPhone = document.getElementById('patientPhone')?.value || '';
        const patientDob = document.getElementById('patientAge')?.value || '';
        const patientGender = document.querySelector('input[name="gioi_tinh"]:checked')?.value || '';
        const patientAddress = document.querySelector('[name="dia_chi"]')?.value || '';
        
        // Fill prescription form
        this.setValue('prescription_patient_name', patientName);
        this.setValue('prescription_phone', patientPhone);
        this.setValue('prescription_dob', patientDob);
        this.setValue('prescription_gender', patientGender);
        this.setValue('prescription_address', patientAddress);
    }

    setValue(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.value = value;
        }
    }

    generatePrescriptionCode() {
        const now = new Date();
        const timestamp = now.getTime();
        const random = Math.floor(Math.random() * 1000);
        const code = 'P' + timestamp.toString().slice(-6) + '-' + random.toString().padStart(2, '0');
        
        // Cập nhật hidden input
        const hiddenInput = document.getElementById('ma_don_thuoc');
        if (hiddenInput) {
            hiddenInput.value = code;
        }
        
        // Cập nhật display value
        const displayValue = document.getElementById('prescription-code-display-value');
        if (displayValue) {
            displayValue.textContent = code;
        }
        
    }

    setCurrentDateTime() {
        const now = new Date();
        const dateStr = now.getDate().toString().padStart(2, '0') + '/' + 
                       (now.getMonth() + 1).toString().padStart(2, '0') + '/' + 
                       now.getFullYear();
        
        this.setValue('prescription_date', dateStr);
        this.setValue('prescription_doctor', 'Bác sĩ'); // Will be filled from doctor info
    }

    async loadMedications() {
        try {
            const response = await fetch('./?action=get_medications');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const text = await response.text();
            
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    this.medications = data.medications;
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Response text:', text);
            }
        } catch (error) {
            console.error('Error loading medications:', error);
        }
    }


    addMedicationRow() {
        const tbody = document.getElementById('medication-tbody');
        
        if (!tbody) return;
        
        // Tạo row bằng createElement giống như xetnghiem_dashboard
        const row = document.createElement('tr');
        row.className = 'medication-row';
        row.innerHTML = `
            <td class="text-center">
                <span>1</span>
            </td>
            <td class="text-center" style="position: relative;">
                <input type="text" class="form-control form-control-sm medication-name-input" placeholder="Nhập tên thuốc..." autocomplete="off" />
                <div class="medication-suggestion-dropdown" style="display: none; position: absolute; z-index: 1000; background: white; border: 1px solid #ccc; max-height: 200px; overflow-y: auto; width: 100%;"></div>
            </td>
            <td class="text-center">
                <input type="text" class="form-control form-control-sm medication-ingredient" placeholder="Hoạt chất" readonly>
            </td>
            <td class="text-center">
                <input type="text" class="form-control form-control-sm medication-unit" placeholder="ĐVT" readonly>
            </td>
            <td class="text-center">
                <input type="number" class="form-control form-control-sm medication-quantity" placeholder="SL" min="1" value="1">
            </td>
            <td class="text-center">
                <input type="text" class="form-control form-control-sm medication-usage" placeholder="Cách dùng">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeMedicationRow(this)">
                    <i class="fas fa-minus"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(row);
        
        // Add event listeners giống như xetnghiem_dashboard
        const nameInput = row.querySelector('.medication-name-input');
        const suggestionDropdown = row.querySelector('.medication-suggestion-dropdown');
        
        // Handle input events
        nameInput.addEventListener('input', function() {
            const keyword = this.value.trim();
            if (keyword.length >= 1) {
                // Remove existing dropdown if any
                const existingDropdown = row.querySelector('.medication-suggestion-dropdown');
                if (existingDropdown) {
                    existingDropdown.remove();
                }
                
                // Create new dropdown
                const newDropdown = document.createElement('div');
                newDropdown.className = 'medication-suggestion-dropdown';
                newDropdown.style.cssText = `
                    position: fixed !important;
                    z-index: 99999 !important;
                    background: #ffffff !important;
                    border: 1px solid #dee2e6 !important;
                    border-radius: 8px !important;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
                    max-height: 200px !important;
                    overflow-y: auto !important;
                    width: 300px !important;
                    font-size: 14px !important;
                    font-weight: 500 !important;
                    opacity: 1 !important;
                    visibility: visible !important;
                    pointer-events: auto !important;
                `;
                
                // Append to body instead of row
                document.body.appendChild(newDropdown);
                
                loadMedicationSuggestions(keyword, newDropdown, row);
            } else {
                // Remove all dropdowns
                document.querySelectorAll('.medication-suggestion-dropdown').forEach(dropdown => {
                    dropdown.remove();
                });
            }
        });
        
        // Handle focus events
        nameInput.addEventListener('focus', function() {
            const keyword = this.value.trim();
            if (keyword.length >= 1) {
                // Remove existing dropdown if any
                const existingDropdown = document.querySelector('.medication-suggestion-dropdown');
                if (existingDropdown) {
                    existingDropdown.remove();
                }
                
                // Create new dropdown
                const newDropdown = document.createElement('div');
                newDropdown.className = 'medication-suggestion-dropdown';
                newDropdown.style.cssText = `
                    position: fixed !important;
                    z-index: 99999 !important;
                    background: #ffffff !important;
                    border: 1px solid #dee2e6 !important;
                    border-radius: 8px !important;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
                    max-height: 200px !important;
                    overflow-y: auto !important;
                    width: 300px !important;
                    font-size: 14px !important;
                    font-weight: 500 !important;
                    opacity: 1 !important;
                    visibility: visible !important;
                    pointer-events: auto !important;
                `;
                
                // Append to body instead of row
                document.body.appendChild(newDropdown);
                
                loadMedicationSuggestions(keyword, newDropdown, row);
            }
        });
        
        // Handle keyboard navigation
        nameInput.addEventListener('keydown', function(e) {
            const suggestions = suggestionDropdown.querySelectorAll('.suggestion-item');
            const activeSuggestion = suggestionDropdown.querySelector('.suggestion-item.active');
            let currentIndex = -1;
            
            if (activeSuggestion) {
                currentIndex = Array.from(suggestions).indexOf(activeSuggestion);
            }
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (suggestions.length > 0) {
                    // Remove active class from current
                    suggestions.forEach(item => item.classList.remove('active'));
                    
                    // Add active class to next item
                    const nextIndex = (currentIndex + 1) % suggestions.length;
                    suggestions[nextIndex].classList.add('active');
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (suggestions.length > 0) {
                    // Remove active class from current
                    suggestions.forEach(item => item.classList.remove('active'));
                    
                    // Add active class to previous item
                    const prevIndex = currentIndex <= 0 ? suggestions.length - 1 : currentIndex - 1;
                    suggestions[prevIndex].classList.add('active');
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeSuggestion) {
                    activeSuggestion.click();
                    // Also hide dropdown after selection
                    suggestionDropdown.style.display = 'none';
                    suggestionDropdown.classList.remove('show');
                    suggestionDropdown.remove();
                }
            } else if (e.key === 'Escape') {
                suggestionDropdown.style.display = 'none';
                suggestionDropdown.classList.remove('show');
                suggestionDropdown.remove();
                nameInput.blur();
            }
        });
        
        // Simple global click listener to hide dropdowns
        const hideAllDropdowns = () => {
            document.querySelectorAll('.medication-suggestion-dropdown').forEach(dropdown => {
                dropdown.remove();
            });
        };
        
        // Add single global listener
        if (!window.medicationDropdownListenerAdded) {
            document.addEventListener('click', function(e) {
                // Only hide if not clicking on input or dropdown
                if (!e.target.closest('.medication-name-input') && !e.target.closest('.medication-suggestion-dropdown')) {
                    hideAllDropdowns();
                }
            });
            
            window.addEventListener('scroll', hideAllDropdowns, true);
            window.addEventListener('resize', hideAllDropdowns);
            
            window.medicationDropdownListenerAdded = true;
        }
        
        // Cập nhật lại STT cho tất cả các hàng (bao gồm hàng mới)
        this.updateMedicationSTT();
    }

    updateMedicationSTT() {
        // Tìm tất cả hàng trong tbody của bảng thuốc
        const tbody = document.getElementById('medication-tbody');
        if (!tbody) return;
        
        const rows = tbody.querySelectorAll('tr'); // Sử dụng "tr" thay vì ".medication-row"
        
        rows.forEach((row, index) => {
            const sttSpan = row.querySelector('td:first-child span'); // Tìm span trong cột đầu tiên
            if (sttSpan) {
                sttSpan.textContent = index + 1;
            }
        });
    }

    removeMedicationRow(button) {
        const row = button.closest('.medication-row');
        const tbody = document.getElementById('medication-tbody');
        
        if (row && tbody) {
            // Remove the row
            tbody.removeChild(row);
            
            // Update STT for remaining rows
            const remainingRows = tbody.querySelectorAll('tr');
            
            remainingRows.forEach((row, index) => {
                const sttCell = row.querySelector('td:first-child span');
                if (sttCell) {
                    sttCell.textContent = index + 1;
                }
            });
        }
    }

    collectPrescriptionData() {
        const maBenhNhan = document.getElementById('prescription_ma_benh_nhan')?.value || '';
        // Resolve doctor id if available in a hidden input populated server-side
        const maBacSi = document.getElementById('current_doctor_id')?.value || '';
        const appointmentId = document.getElementById('examinationAppointmentId')?.value || '';
        const phieuKhamId = document.getElementById('prescription_examination_id')?.value || '';
        return {
            ma_don_thuoc: document.getElementById('ma_don_thuoc')?.value || '',
            ho_ten_benh_nhan: document.getElementById('prescription_patient_name')?.value || '',
            ma_benh_nhan: maBenhNhan,
            ma_bac_si: maBacSi,
            ma_bhyt: document.getElementById('prescription_bhyt')?.value || '',
            ma_dinh_danh: document.getElementById('prescription_citizen_id')?.value || '',
            ngay_sinh: document.getElementById('prescription_dob')?.value || '',
            cmt_cccd: document.getElementById('prescription_id_card')?.value || '',
            can_nang: document.getElementById('prescription_weight')?.value || '',
            gioi_tinh: document.getElementById('prescription_gender')?.value || '',
            ma_dinh_danh_y_te: document.getElementById('prescription_medical_id')?.value || '',
            so_dien_thoai: document.getElementById('prescription_phone')?.value || '',
            dia_chi_lien_he: document.getElementById('prescription_address')?.value || '',
            ma_chan_doan: document.getElementById('diagnosis_code')?.value || '',
            chan_doan: document.getElementById('diagnosis_description')?.value || '',
            luu_y: document.getElementById('prescription_notes')?.value || '',
            hinh_thuc_dieu_tri: document.querySelector('input[name="hinh_thuc_dieu_tri"]:checked')?.value || '',
            ket_luan: document.getElementById('prescription_conclusion')?.value || '',
            loi_dan: document.getElementById('prescription_instructions')?.value || '',
            lich_tai_kham: document.getElementById('prescription_followup_days')?.value || '',
            ten_nguoi_dua: document.getElementById('prescription_guardian')?.value || '',
            ngay_ky: document.getElementById('prescription_date')?.value || '',
            ten_bac_si: document.getElementById('prescription_doctor')?.value || '',
            id_phieu_kham_benh: phieuKhamId || appointmentId,
            medications: this.collectMedicationData()
        };
    }

    collectMedicationData() {
        const medications = [];
        const medicationRows = document.querySelectorAll('.medication-row');
        
        medicationRows.forEach(row => {
            const medication = {
                ma_thuoc: row.getAttribute('data-ma-thuoc') || '',
                stt: row.querySelector('td:first-child span')?.textContent || '',
                ten_thuoc: row.querySelector('.medication-name-input')?.value || '',
                hoạt_chất: row.querySelector('.medication-ingredient')?.value || '',
                hoat_chat: row.querySelector('.medication-ingredient')?.value || '',
                don_vi_tinh: row.querySelector('.medication-unit')?.value || '',
                so_luong: row.querySelector('.medication-quantity')?.value || '1',
                cach_dung: row.querySelector('.medication-usage')?.value || ''
            };
            
            if (medication.ten_thuoc) {
                medications.push(medication);
            }
        });
        
        return medications;
    }

    async savePrescription() {
        try {
            const prescriptionData = this.collectPrescriptionData();
            
            // Validate required fields
            if (!prescriptionData.ho_ten_benh_nhan) {
                alert('Vui lòng nhập tên bệnh nhân');
                return;
            }
            
            if (prescriptionData.medications.length === 0) {
                alert('Vui lòng thêm ít nhất một loại thuốc');
                return;
            }
            
            // Show loading state
            this.setLoadingState(true);
            
            const response = await fetch('./?action=save_prescription', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(prescriptionData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('Lưu đơn thuốc thành công!');
                this.prescriptionData = prescriptionData;
                // Reload just-saved prescription back into the form so it persists when reopening
                await this.tryLoadSavedPrescription();
            } else {
                alert('Lỗi khi lưu đơn thuốc: ' + (data.message || 'Không xác định'));
            }
            
        } catch (error) {
            console.error('Error:', error);
            alert('Lỗi khi lưu đơn thuốc');
        } finally {
            this.setLoadingState(false);
        }
    }

    setLoadingState(loading) {
        const buttons = document.querySelectorAll('#sec-prescription button');
        buttons.forEach(btn => {
            btn.disabled = loading;
        });
        
        if (loading) {
            document.getElementById('sec-prescription').classList.add('loading');
        } else {
            document.getElementById('sec-prescription').classList.remove('loading');
        }
    }

    // print functionality removed

    clearPrescription() {
        if (confirm('Bạn có chắc muốn xóa đơn thuốc?')) {
            // Clear all form fields
            const form = document.getElementById('sec-prescription');
            if (form) {
                form.querySelectorAll('input, textarea, select').forEach(field => {
                    if (field.type !== 'hidden' && field.id !== 'ma_don_thuoc') {
                        field.value = '';
                    }
                });
                
                // Clear medication table
                const tbody = document.getElementById('medication-tbody');
                if (tbody) {
                    tbody.innerHTML = '';
                }
                
                // Reset prescription code
                this.generatePrescriptionCode();
            }
        }
    }

    // Search medications
    async searchMedications(keyword) {
        try {
            const response = await fetch(`./?action=search_medications_public&keyword=${encodeURIComponent(keyword)}`);
            const data = await response.json();
            
            if (data.success) {
                this.medications = data.medications;
                this.populateMedicationOptions();
            }
        } catch (error) {
            console.error('Error searching medications:', error);
        }
    }

    // Get prescription history
    async getPrescriptionHistory(patientId) {
        try {
            const response = await fetch(`./?action=get_prescription_history&patient_id=${patientId}`);
            const data = await response.json();
            
            if (data.success) {
                return data.prescriptions;
            }
        } catch (error) {
            console.error('Error getting prescription history:', error);
        }
        return [];
    }
}

// Global function giống như trong xetnghiem_dashboard
function loadMedicationSuggestions(keyword, dropdown, row) {
    
    // Sử dụng test API không cần authentication
    fetch(`./?action=search_medications_public&keyword=${encodeURIComponent(keyword)}`)
        .then(response => {
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                return data;
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                throw e;
            }
        })
        .then(data => {
            
            if (data.success && data.medications && data.medications.length > 0) {
                dropdown.innerHTML = data.medications.map(medication => `
                    <div class="suggestion-item" 
                         data-ma-thuoc="${medication.MaThuoc}"
                         data-ten-thuoc="${medication.TenThuoc}" 
                         data-hoat-chat="${medication.HoatChatChinh || ""}" 
                         data-don-vi="${medication.DonViTinh || ""}" 
                         data-lieu-dung="${medication.LieuDung || ""}"
                         data-so-luong-ton="${medication.SoLuongTon || 0}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-primary">${medication.TenThuoc}</strong>
                                ${medication.HoatChatChinh ? `<br><small class="text-muted">${medication.HoatChatChinh} - ${medication.DonViTinh}</small>` : ""}
                                <br><small class="text-info">Tồn kho: ${medication.SoLuongTon || 0}</small>
                            </div>
                            <i class="fas fa-arrow-right text-muted"></i>
                        </div>
                    </div>
                `).join("");
                
                dropdown.style.display = 'block';
                dropdown.classList.add('show');
                
                // Smart positioning - calculate position relative to input
                const input = row.querySelector('.medication-name-input');
                const inputRect = input.getBoundingClientRect();
                const viewportHeight = window.innerHeight;
                const viewportWidth = window.innerWidth;
                const dropdownHeight = Math.min(200, data.medications.length * 60 + 20); // More accurate height estimate
                
                // Calculate available space
                const spaceBelow = viewportHeight - inputRect.bottom - 20;
                const spaceAbove = inputRect.top - 20;
                
                let top, left;
                
                // Determine best position (above or below)
                if (spaceBelow >= dropdownHeight || spaceBelow > spaceAbove) {
                    // Show below input
                    top = inputRect.bottom + 5;
                } else {
                    // Show above input
                    top = inputRect.top - dropdownHeight - 5;
                }
                
                // Calculate horizontal position
                left = inputRect.left;
                
                // Ensure dropdown doesn't go off right side
                if (left + 300 > viewportWidth - 20) {
                    left = viewportWidth - 320; // 300px width + 20px margin
                }
                
                // Ensure dropdown doesn't go off left side
                if (left < 20) {
                    left = 20;
                }
                
                // Ensure dropdown doesn't go off top
                if (top < 20) {
                    top = 20;
                }
                
                // Apply positioning with important flags to override any CSS
                dropdown.style.cssText = `
                    position: fixed !important;
                    top: ${top}px !important;
                    left: ${left}px !important;
                    z-index: 99999 !important;
                    display: block !important;
                    width: 300px !important;
                    max-height: 200px !important;
                `;
                
                
                // Add click handlers with better event handling
                dropdown.querySelectorAll('.suggestion-item').forEach((item, index) => {
                    // Add hover effect for better UX
                    item.addEventListener('mouseenter', function() {
                        // Remove active class from all items
                        dropdown.querySelectorAll('.suggestion-item').forEach(i => i.classList.remove('active'));
                        // Add active class to current item
                        this.classList.add('active');
                    });
                    
                    // Add click handler with event prevention
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        
                        const maThuoc = this.getAttribute('data-ma-thuoc');
                        const tenThuoc = this.getAttribute('data-ten-thuoc');
                        const hoatChat = this.getAttribute('data-hoat-chat');
                        const donVi = this.getAttribute('data-don-vi');
                        const lieuDung = this.getAttribute('data-lieu-dung');
                        const soLuongTon = this.getAttribute('data-so-luong-ton');
                        console.log('Retrieved soLuongTon from suggestion:', soLuongTon, 'type:', typeof soLuongTon);
                        
                        
                        // Fill the form
                        const nameInput = row.querySelector('.medication-name-input');
                        const ingredientInput = row.querySelector('.medication-ingredient');
                        const unitInput = row.querySelector('.medication-unit');
                        const usageInput = row.querySelector('.medication-usage');
                        
                        // store ma_thuoc at row level for saving
                        row.setAttribute('data-ma-thuoc', maThuoc || '');

                        if (nameInput) {
                            nameInput.value = tenThuoc;
                            nameInput.focus(); // Keep focus on input
                        }
                        if (ingredientInput) ingredientInput.value = hoatChat;
                        if (unitInput) unitInput.value = donVi;
                        if (usageInput) usageInput.value = lieuDung;
                        
                        // Store stock quantity for validation
                        const quantityInput = row.querySelector('.medication-quantity');
                        if (quantityInput) {
                            console.log('Setting stock quantity:', soLuongTon, 'type:', typeof soLuongTon);
                            quantityInput.setAttribute('data-stock-quantity', soLuongTon);
                            quantityInput.setAttribute('max', soLuongTon);
                            console.log('After setting - data-stock-quantity:', quantityInput.getAttribute('data-stock-quantity'));
                            
                            // Remove existing listeners first to avoid duplicates
                            quantityInput.removeEventListener('input', validateQuantity);
                            quantityInput.removeEventListener('blur', validateQuantity);
                            
                            // Add validation event listener
                            quantityInput.addEventListener('input', function() {
                                console.log('Input event triggered for quantity:', this.value);
                                validateQuantity(this);
                            });
                            
                            quantityInput.addEventListener('blur', function() {
                                console.log('Blur event triggered for quantity:', this.value);
                                validateQuantity(this);
                            });
                        }
                        
                        // Hide dropdown completely
                        dropdown.style.display = 'none';
                        dropdown.classList.remove('show');
                        dropdown.remove(); // Remove from DOM completely
                        
                    });
                    
                    // Add mousedown handler as backup
                    item.addEventListener('mousedown', function(e) {
                        e.preventDefault();
                        this.click(); // Trigger click
                        // Also hide dropdown on mousedown
                        dropdown.style.display = 'none';
                        dropdown.classList.remove('show');
                        dropdown.remove();
                    });
                });
            } else {
                dropdown.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error loading medication suggestions:', error);
            dropdown.style.display = 'none';
        });
}

// Global functions for backward compatibility
function selectMedication(selectElement) {
    window.prescriptionManager.selectMedication(selectElement);
}

function removeMedicationRow(button) {
    window.prescriptionManager.removeMedicationRow(button);
}

// Function to validate quantity input
function validateQuantity(input) {
    const enteredQuantity = parseInt(input.value) || 0;
    const stockQuantity = parseInt(input.getAttribute('data-stock-quantity')) || 0;
    
    // Debug log
    console.log('Validation:', {
        enteredQuantity: enteredQuantity,
        stockQuantity: stockQuantity,
        inputValue: input.value,
        dataStock: input.getAttribute('data-stock-quantity')
    });
    
    // Log individual values for easier debugging
    console.log('enteredQuantity:', enteredQuantity, 'type:', typeof enteredQuantity);
    console.log('stockQuantity:', stockQuantity, 'type:', typeof stockQuantity);
    console.log('data-stock-quantity attribute:', input.getAttribute('data-stock-quantity'));
    
    // Remove existing error styling
    input.classList.remove('is-invalid');
    input.classList.remove('border-danger');
    
    // Remove existing error message
    const existingError = input.parentNode.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }
    
    // Check if medication name is filled (indicating medication was selected)
    const medicationNameInput = input.closest('tr').querySelector('.medication-name-input');
    const medicationName = medicationNameInput ? medicationNameInput.value.trim() : '';
    
    console.log('Validation conditions:', {
        stockQuantity: stockQuantity,
        enteredQuantity: enteredQuantity,
        medicationName: medicationName,
        stockQuantity_gt_0: stockQuantity > 0,
        enteredQuantity_gt_0: enteredQuantity > 0,
        hasMedicationName: !!medicationName,
        entered_gt_stock: enteredQuantity > stockQuantity,
        allConditions: stockQuantity > 0 && enteredQuantity > 0 && medicationName && enteredQuantity > stockQuantity
    });
    
    // Log each condition separately
    console.log('Condition 1 - stockQuantity > 0:', stockQuantity > 0, '(stockQuantity:', stockQuantity, ')');
    console.log('Condition 2 - enteredQuantity > 0:', enteredQuantity > 0, '(enteredQuantity:', enteredQuantity, ')');
    console.log('Condition 3 - medicationName exists:', !!medicationName, '(medicationName:', medicationName, ')');
    console.log('Condition 4 - enteredQuantity > stockQuantity:', enteredQuantity > stockQuantity, '(entered:', enteredQuantity, '> stock:', stockQuantity, ')');
    console.log('All conditions met:', stockQuantity > 0 && enteredQuantity > 0 && medicationName && enteredQuantity > stockQuantity);
    
    // If no stock quantity data, keep as 0 so validation only works with DB-provided stock
    // User must pick a medication from suggestions to get real SoLuongTon from database
    
    // Only validate if we have stock quantity data and entered quantity > 0
    if (stockQuantity > 0 && enteredQuantity > 0 && medicationName && enteredQuantity > stockQuantity) {
        // Add error styling
        input.classList.add('is-invalid');
        input.classList.add('border-danger');
        
        // Add error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = `Số lượng tồn kho không đủ! Chỉ còn ${stockQuantity} trong kho.`;
        input.parentNode.appendChild(errorDiv);
        
        // Focus on input
        input.focus();
        
        return false;
    }
    
    return true;
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.prescriptionManager = new PrescriptionManager();
    
});
