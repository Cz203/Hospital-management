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
        
        // Print prescription button
        document.getElementById('print-prescription-btn')?.addEventListener('click', () => {
            this.printPrescription();
        });
        
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
    }
    
    setupDefaultMedicationRow() {
        // Setup autocomplete for the default medication row
        const defaultRow = document.querySelector('#medication-tbody tr.medication-row');
        if (defaultRow) {
            // Add event listeners giống như trong addMedicationRow
            const nameInput = defaultRow.querySelector('.medication-name-input');
            const suggestionDropdown = defaultRow.querySelector('.medication-suggestion-dropdown');
            
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
        return {
            ma_don_thuoc: document.getElementById('ma_don_thuoc')?.value || '',
            ho_ten_benh_nhan: document.getElementById('prescription_patient_name')?.value || '',
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
            medications: this.collectMedicationData()
        };
    }

    collectMedicationData() {
        const medications = [];
        const medicationRows = document.querySelectorAll('.medication-row');
        
        medicationRows.forEach(row => {
            const medication = {
                stt: row.querySelector('td:first-child span')?.textContent || '',
                ten_thuoc: row.querySelector('.medication-name-input')?.value || '',
                hoạt_chất: row.querySelector('.medication-ingredient')?.value || '',
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

    printPrescription() {
        // Create a new window for printing
        const printWindow = window.open('', '_blank');
        const prescriptionContent = document.getElementById('sec-prescription').innerHTML;
        
        printWindow.document.write(`
            <html>
                <head>
                    <title>Đơn thuốc</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .btn { display: none !important; }
                        .table { border-collapse: collapse; }
                        .table th, .table td { border: 1px solid #000; padding: 0.25rem; }
                        .medication-row input, .medication-row select { border: none; background: transparent; }
                    </style>
                </head>
                <body>
                    ${prescriptionContent}
                </body>
            </html>
        `);
        
        printWindow.document.close();
        printWindow.print();
    }

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
                         data-ten-thuoc="${medication.TenThuoc}" 
                         data-hoat-chat="${medication.HoatChatChinh || ""}" 
                         data-don-vi="${medication.DonViTinh || ""}" 
                         data-lieu-dung="${medication.LieuDung || ""}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-primary">${medication.TenThuoc}</strong>
                                ${medication.HoatChatChinh ? `<br><small class="text-muted">${medication.HoatChatChinh} - ${medication.DonViTinh}</small>` : ""}
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
                        
                        
                        const tenThuoc = this.getAttribute('data-ten-thuoc');
                        const hoatChat = this.getAttribute('data-hoat-chat');
                        const donVi = this.getAttribute('data-don-vi');
                        const lieuDung = this.getAttribute('data-lieu-dung');
                        
                        
                        // Fill the form
                        const nameInput = row.querySelector('.medication-name-input');
                        const ingredientInput = row.querySelector('.medication-ingredient');
                        const unitInput = row.querySelector('.medication-unit');
                        const usageInput = row.querySelector('.medication-usage');
                        
                        if (nameInput) {
                            nameInput.value = tenThuoc;
                            nameInput.focus(); // Keep focus on input
                        }
                        if (ingredientInput) ingredientInput.value = hoatChat;
                        if (unitInput) unitInput.value = donVi;
                        if (usageInput) usageInput.value = lieuDung;
                        
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

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.prescriptionManager = new PrescriptionManager();
    
});
