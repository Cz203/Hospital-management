<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h3 class="mb-3">Tra cứu bệnh nhân theo số điện thoại</h3>
        </div>
        <div class="col-md-6">
            <form id="reception-search-form" onsubmit="return false;" class="card p-3 shadow-sm">
                <div class="mb-3">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" class="form-control" id="reception-phone"
                        placeholder="VD: 0912345678 hoặc 84912345678" />
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" onclick="receptionSearchPatient()">
                        <i class="fas fa-search me-1"></i> Tra cứu
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="receptionReset()">Xóa</button>
                </div>
            </form>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title mb-3">Kết quả</h6>
                    <div id="reception-result" class="text-muted">Chưa có dữ liệu</div>
                    <div id="reception-actions" class="mt-3" style="display:none;">
                        <button class="btn btn-warning" type="button" onclick="openEditModal()">
                            <i class="fas fa-edit me-1"></i> Sửa / Xóa trường
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal sửa/xóa trường -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sửa / Xóa trường hồ sơ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-form" onsubmit="return false;">
                    <input type="hidden" name="csrf_token"
                        value="<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>">
                    <input type="hidden" name="patient_id" id="m-patient-id" value="">
                    <div class="mb-2">
                        <label class="form-label">Mã BHYT</label>
                        <input type="text" class="form-control" name="bao_hiem_y_te" id="m-bhyt"
                            placeholder="Nhập mã BHYT hoặc để trống để xóa">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="m-email"
                            placeholder="@gmail.com (để trống để xóa)">
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" class="form-control" name="ngay_sinh" id="m-dob">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giới tính</label>
                            <select class="form-select" name="gioi_tinh" id="m-gender">
                                <option value="">--</option>
                                <option value="Nam">Nam</option>
                                <option value="Nu">Nữ</option>
                                <option value="Khac">Khác</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-12">
                            <label class="form-label">
                                <i class="fas fa-id-card me-1"></i>CCCD
                            </label>
                            <input type="text" class="form-control" name="cccd" id="m-cccd" maxlength="12"
                                pattern="\d{12}" placeholder="Nhập 12 số CCCD để tự động điền thông tin">
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i> Nhập CCCD để tự động điền thông tin
                            </div>
                            <div id="m-cccd-verification-result" class="mt-2"></div>
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-12">
                            <label class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Địa chỉ
                            </label>
                            <input type="text" class="form-control" name="dia_chi" id="m-address"
                                placeholder="Địa chỉ... (để trống để xóa)">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="submitEditModal()"><i
                        class="fas fa-save me-1"></i> Lưu</button>
            </div>
        </div>
    </div>
</div>

<script>
    function receptionReset() {
        document.getElementById('reception-phone').value = '';
        document.getElementById('reception-result').innerHTML = 'Chưa có dữ liệu';
    }

    function receptionSearchPatient() {
        var phone = (document.getElementById('reception-phone').value || '').trim();
        if (!phone) {
            document.getElementById('reception-result').innerHTML =
                '<span class="text-danger">Vui lòng nhập số điện thoại</span>';
            return;
        }
        var xhr = new XMLHttpRequest();
        xhr.open('POST', './reception_find_patient', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp && resp.success && resp.data) {
                        var p = resp.data;
                        var html = '' +
                            '<div class="table-responsive">' +
                            '<table class="table table-sm">' +
                            '<tr><th>Mã Bảo hiểm y tế</th><td>' + (p.bao_hiem_y_te || '') + '</td></tr>' +
                            '<tr><th>Tên</th><td>' + (p.ten || '') + '</td></tr>' +
                            '<tr><th>Email</th><td>' + (p.email || '') + '</td></tr>' +
                            '<tr><th>SĐT</th><td>' + (p.so_dien_thoai || '') + '</td></tr>' +
                            '<tr><th>Ngày sinh</th><td>' + (p.ngay_sinh || '') + '</td></tr>' +
                            '<tr><th>Giới tính</th><td>' + (p.gioi_tinh || '') + '</td></tr>' +
                            '<tr><th>Địa chỉ</th><td>' + (p.dia_chi || '') + '</td></tr>' +
                            '<tr><th>CCCD</th><td>' + (p.cccd || '') + '</td></tr>' +
                            '</table>' +
                            '</div>';
                        document.getElementById('reception-result').innerHTML = html;
                        document.getElementById('reception-actions').style.display = 'block';
                        window.__currentPatient = p;
                    } else {
                        document.getElementById('reception-result').innerHTML = '<span class="text-warning">' + (resp
                            .message || 'Không tìm thấy bệnh nhân') + '</span>';
                        document.getElementById('reception-actions').style.display = 'none';
                    }
                } catch (e) {
                    document.getElementById('reception-result').innerHTML =
                        '<span class="text-danger">Lỗi xử lý kết quả</span>';
                    document.getElementById('reception-actions').style.display = 'none';
                }
            }
        };
        xhr.send('phone=' + encodeURIComponent(phone));
    }

    function openEditModal() {
        var p = window.__currentPatient || {};
        document.getElementById('m-patient-id').value = p.id || '';
        document.getElementById('m-bhyt').value = p.bao_hiem_y_te || '';
        document.getElementById('m-email').value = p.email || '';
        document.getElementById('m-dob').value = (p.ngay_sinh || '').substring(0, 10);
        document.getElementById('m-gender').value = p.gioi_tinh || '';
        document.getElementById('m-cccd').value = p.cccd || '';
        document.getElementById('m-address').value = p.dia_chi || '';
        var modal = new bootstrap.Modal(document.getElementById('editModal'));
        modal.show();
    }

    function submitEditModal() {
        var form = document.getElementById('edit-form');
        var data = new FormData(form);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', './reception_complete_patient', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp && resp.success) {
                        var modalEl = document.getElementById('editModal');
                        var modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                        receptionSearchPatient();
                    } else {
                        alert(resp.message || 'Không thể cập nhật');
                    }
                } catch (e) {
                    alert('Lỗi máy chủ');
                }
            }
        };
        xhr.send(new URLSearchParams(data).toString());
    }

    // CCCD verification for edit modal
    document.addEventListener('DOMContentLoaded', function() {
        const cccdInput = document.getElementById('m-cccd');

        if (cccdInput) {
            // Format CCCD - only numbers
            cccdInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/\D/g, '');

                // Unlock all auto-filled fields when CCCD changes
                unlockModalCCCDFields();
            });

            // Verify CCCD on blur
            cccdInput.addEventListener('blur', async function() {
                const cccdValue = this.value.trim();

                if (!cccdValue || cccdValue.length !== 12) {
                    if (cccdValue && cccdValue.length !== 12) {
                        showModalCCCDResult('warning',
                            '<i class="fas fa-exclamation-triangle"></i> CCCD phải có đúng 12 số');
                    }
                    return;
                }

                showModalCCCDResult('info',
                    '<i class="fas fa-spinner fa-spin"></i> Đang xác thực CCCD...');

                const ten = document.getElementById('m-name')?.value.trim() || '';
                const ngaySinh = document.getElementById('m-dob')?.value || '';

                try {
                    const response = await fetch('./verify_cccd', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            cccd: cccdValue,
                            ten: ten,
                            ngay_sinh: ngaySinh,
                        }),
                    });

                    const result = await response.json();

                    if (result.success && result.verified) {
                        showModalCCCDResult('success',
                            '<i class="fas fa-check-circle"></i> ' + result.message);

                        // Auto fill if available
                        if (result.cccd_data) {
                            if (result.cccd_data.ngay_sinh) {
                                const dobInput = document.getElementById('m-dob');
                                if (dobInput && !dobInput.value) {
                                    dobInput.value = result.cccd_data.ngay_sinh;
                                }
                                // Lock field
                                if (dobInput) {
                                    dobInput.readOnly = true;
                                    dobInput.classList.add('cccd-locked');
                                }
                            }

                            if (result.cccd_data.gioi_tinh) {
                                const genderSelect = document.getElementById('m-gender');
                                if (genderSelect && !genderSelect.value) {
                                    genderSelect.value = result.cccd_data.gioi_tinh;
                                }
                                // Lock field (disabled for select)
                                if (genderSelect) {
                                    genderSelect.disabled = true;
                                    genderSelect.classList.add('cccd-locked');
                                }
                            }

                            if (result.cccd_data.dia_chi) {
                                const addressInput = document.getElementById('m-address');
                                if (addressInput && !addressInput.value) {
                                    addressInput.value = result.cccd_data.dia_chi;
                                }
                                // Lock field
                                if (addressInput) {
                                    addressInput.readOnly = true;
                                    addressInput.classList.add('cccd-locked');
                                }
                            }

                            showModalCCCDResult('success',
                                '<i class="fas fa-check-circle"></i> Xác thực thành công! <i class="fas fa-lock ms-1"></i> Các thông tin từ CCCD đã được khóa'
                            );
                        }
                    } else {
                        let errorMsg = result.message || 'CCCD không hợp lệ';
                        if (result.suggestion) {
                            errorMsg += '<br><small>' + result.suggestion + '</small>';
                        }
                        showModalCCCDResult('danger',
                            '<i class="fas fa-times-circle"></i> ' + errorMsg);
                    }
                } catch (error) {
                    console.error('Error verifying CCCD:', error);
                    showModalCCCDResult('danger',
                        '<i class="fas fa-times-circle"></i> Lỗi khi xác thực CCCD. Vui lòng thử lại.'
                    );
                }
            });
        }
    });

    function showModalCCCDResult(type, message) {
        const resultDiv = document.getElementById('m-cccd-verification-result');
        if (!resultDiv) return;

        const alertClass = 'alert alert-' + type + ' alert-dismissible fade show';
        resultDiv.innerHTML =
            '<div class="' + alertClass + '" role="alert">' +
            message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
            '</div>';
    }

    // Function to unlock CCCD-filled fields in modal
    function unlockModalCCCDFields() {
        const dobInput = document.getElementById('m-dob');
        const genderSelect = document.getElementById('m-gender');
        const addressInput = document.getElementById('m-address');

        if (dobInput) {
            dobInput.readOnly = false;
            dobInput.classList.remove('cccd-locked');
        }
        if (genderSelect) {
            genderSelect.disabled = false;
            genderSelect.classList.remove('cccd-locked');
        }
        if (addressInput) {
            addressInput.readOnly = false;
            addressInput.classList.remove('cccd-locked');
        }
    }
</script>