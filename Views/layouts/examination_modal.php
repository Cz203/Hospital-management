<div class="modal fade" id="examinationModal" tabindex="-1" aria-labelledby="examinationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xxl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="examinationModalLabel">
                    <i class="fas fa-stethoscope me-2"></i>Form Khám Bệnh
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Sidebar Navigation -->
                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="list-group position-sticky" style="top: 15px;">
                            <a href="#sec-patient" class="list-group-item list-group-item-action exam-nav active">
                                <i class="fas fa-user me-2"></i>Thông tin bệnh nhân
                            </a>
                            <a href="#sec-history" class="list-group-item list-group-item-action exam-nav">
                                <i class="fas fa-history me-2"></i>Tiền sử bệnh
                            </a>
                            <a href="#sec-exam" class="list-group-item list-group-item-action exam-nav">
                                <i class="fas fa-stethoscope me-2"></i>Khám bệnh
                            </a>
                            <a href="#sec-lab" class="list-group-item list-group-item-action exam-nav">
                                <i class="fas fa-vial me-2"></i>Xét nghiệm
                            </a>
                            <a href="#sec-ultrasound" class="list-group-item list-group-item-action exam-nav">
                                <i class="fas fa-wave-square me-2"></i>Siêu âm
                            </a>
                            <a href="#sec-ultrasound-result" class="list-group-item list-group-item-action exam-nav">
                                <i class="fas fa-file-medical-alt me-2"></i>Kết quả siêu âm
                            </a>
                            <a href="#sec-xray" class="list-group-item list-group-item-action exam-nav">
                                <i class="fas fa-x-ray me-2"></i>X-Quang
                            </a>
                            <a href="#sec-xray-result" class="list-group-item list-group-item-action exam-nav">
                                <i class="fas fa-file-medical-alt me-2"></i>Kết quả X-Quang
                            </a>
                            <a href="#sec-prescription" class="list-group-item list-group-item-action exam-nav">
                                <i class="fas fa-pills me-2"></i>Kê đơn thuốc
                            </a>
                            <a href="#sec-result" class="list-group-item list-group-item-action exam-nav">
                                <i class="fas fa-clipboard-check me-2"></i>Trả kết quả
                            </a>
                        </div>
                    </div>

                    <div class="col-md-9">
                        <form id="examinationForm">
                            <input type="hidden" id="examinationAppointmentId" name="appointment_id">
                            <input type="hidden" id="historyPatientId" name="history_patient_id">

                            <!-- Basic patient info to avoid null bindings -->
                            <div class="card mb-3 exam-section active" id="sec-patient">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Thông tin bệnh nhân</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Họ và tên</label>
                                            <input type="text" class="form-control" id="patientName" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Số điện thoại</label>
                                            <input type="text" class="form-control" id="patientPhone" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Tuổi</label>
                                            <input type="text" class="form-control" id="patientAge" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Giới tính</label>
                                            <input type="text" class="form-control" id="patientGender" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Phiếu yêu cầu Siêu âm -->
                            <div class="card mb-3 exam-section" id="sec-ultrasound">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-wave-square me-2"></i>Phiếu yêu cầu Siêu âm</h6>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" name="id_phieu_kham_benh" id="ultrasound_examination_id">

                                    <div class="text-center mb-3">
                                        <div class="fw-bold" style="font-size:18px">PHIẾU YÊU CẦU SIÊU ÂM</div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Tên phòng khám</label>
                                            <input type="text" class="form-control" name="ultrasound_clinic_name"
                                                id="ultrasound_clinic_name" value="Thịnh Việt" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Số điện thoại</label>
                                            <input type="text" class="form-control" name="ultrasound_phone"
                                                id="ultrasound_phone" value="0777871608">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Quận/Huyện</label>
                                            <input type="text" class="form-control" name="ultrasound_quan"
                                                id="ultrasound_quan" value="Gò Vấp">
                                        </div>
                                    </div>

                                    <div class="row g-3 mt-2">
                                        <div class="col-md-2">
                                            <label class="form-label fw-bold">Mã bệnh nhân</label>
                                            <input type="text" class="form-control" name="ultrasound_patient_code"
                                                id="ultrasound_patient_code" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Họ tên người bệnh</label>
                                            <input type="text" class="form-control" name="ultrasound_patient_name"
                                                id="ultrasound_patient_name" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-bold">Tuổi</label>
                                            <input type="text" class="form-control" name="ultrasound_patient_age"
                                                id="ultrasound_patient_age" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-bold">Nam/Nữ</label>
                                            <input type="text" class="form-control" name="ultrasound_patient_gender"
                                                id="ultrasound_patient_gender" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Địa chỉ</label>
                                            <input type="text" class="form-control" name="ultrasound_patient_address"
                                                id="ultrasound_patient_address">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Đối tượng</label>
                                            <input type="text" class="form-control" name="ultrasound_patient_type"
                                                id="ultrasound_patient_type" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Số thẻ BHYT</label>
                                            <input type="text" class="form-control" name="ultrasound_insurance_number"
                                                id="ultrasound_insurance_number" readonly>
                                        </div>
                                    </div>

                                    <!-- Chẩn đoán -->
                                    <div class="mt-3">
                                        <label class="form-label fw-bold">Chẩn đoán:</label>
                                        <input type="text" class="form-control" name="ultrasound_diagnosis"
                                            id="ultrasound_diagnosis" placeholder="Nhập chẩn đoán...">
                                    </div>

                                    <!-- Yêu cầu siêu âm -->
                                    <div class="mt-3 position-relative">
                                        <label class="form-label fw-bold text-center w-100 d-block"
                                            style="font-size:16px">YÊU CẦU SIÊU ÂM</label>
                                        <textarea class="form-control" name="ultrasound_request" id="ultrasound_request"
                                            rows="6" placeholder="Nhập yêu cầu siêu âm..."></textarea>
                                        <div id="ultrasound_suggestions"
                                            class="position-absolute bg-white border rounded shadow"
                                            style="display:none; z-index:1000; max-height:200px; overflow-y:auto; width:100%;">
                                        </div>
                                    </div>

                                    <!-- Chữ ký bác sĩ -->
                                    <div class="row mt-4">
                                        <div class="col-md-6"></div>
                                        <div class="col-md-6 text-center">
                                            <div class="mb-2 d-flex align-items-center justify-content-center gap-2">
                                                <span>Ngày</span>
                                                <input type="number" class="form-control text-center"
                                                    name="ultrasound_ngay" id="ultrasound_ngay" style="width:70px">
                                                <span>tháng</span>
                                                <input type="number" class="form-control text-center"
                                                    name="ultrasound_thang" id="ultrasound_thang" style="width:70px">
                                                <span>năm</span>
                                                <input type="number" class="form-control text-center"
                                                    name="ultrasound_nam" id="ultrasound_nam" style="width:90px">
                                            </div>
                                            <div class="fw-bold">BÁC SĨ ĐIỀU TRỊ</div>
                                            <div class="mt-2" id="ultrasound_doctor_display"
                                                style="min-height:40px; border-bottom: 1px solid #000; padding: 5px;">
                                                (Ký và ghi rõ họ tên)</div>
                                            <input type="hidden" name="ultrasound_doctor_name"
                                                id="ultrasound_doctor_name">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Phần khám bệnh -->
                            <div class="card mb-3 exam-section" id="sec-exam">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Phiếu khám bệnh vào viện
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <!-- Header form -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <strong>Sở Y tế:</strong> <input type="text"
                                                    class="form-control d-inline-block w-auto" name="so_y_te"
                                                    value="Thành Phố Hồ Chí Minh" readonly>
                                            </div>
                                            <div class="mb-2">
                                                <strong>BV:</strong> <input type="text"
                                                    class="form-control d-inline-block w-auto" name="benh_vien"
                                                    value="Thịnh Việt" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <div class="mb-2">
                                                <strong>Mã bệnh nhân:</strong> <input type="text"
                                                    class="form-control d-inline-block w-auto" id="exam_ma_benh_nhan"
                                                    name="ma_so" placeholder="Mã bệnh nhân" readonly>
                                            </div>
                                            <div class="mb-2">
                                                <strong>BUỒNG KHÁM BỆNH:</strong> <input type="text"
                                                    class="form-control d-inline-block w-auto" name="buong_kham"
                                                    id="buong_kham" readonly>
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
                                                <input type="text" class="form-control" name="ho_ten"
                                                    style="text-transform: uppercase;" readonly>
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label fw-bold">2. Sinh ngày:</label>
                                                <div class="row g-2">
                                                    <div class="col-3">
                                                        <input type="number" class="form-control" name="ngay_sinh"
                                                            placeholder="Ngày" min="1" max="31" readonly>
                                                    </div>
                                                    <div class="col-3">
                                                        <input type="number" class="form-control" name="thang_sinh"
                                                            placeholder="Tháng" min="1" max="12" readonly>
                                                    </div>
                                                    <div class="col-3">
                                                        <input type="number" class="form-control" name="nam_sinh"
                                                            placeholder="Năm" min="1900" max="2025" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Tuổi:</label>
                                                <input type="number" class="form-control" name="tuoi" placeholder="Tuổi"
                                                    readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">3. Giới:</label>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="gioi_tinh"
                                                        id="nam" value="Nam">
                                                    <label class="form-check-label" for="nam">1. Nam</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="gioi_tinh"
                                                        id="nu" value="Nữ">
                                                    <label class="form-check-label" for="nu">2. Nữ</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">4. Nghề nghiệp:</label>
                                                <input type="text" class="form-control" name="nghe_nghiep">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">5. Dân tộc:</label>
                                                <input type="text" class="form-control" name="dan_toc">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">6. Ngoại kiều:</label>
                                                <input type="text" class="form-control" name="ngoai_kieu">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">8. Nơi làm việc:</label>
                                                <input type="text" class="form-control" name="noi_lam_viec">
                                            </div>
                                        </div>

                                        <!-- Địa chỉ -->
                                        <div class="mt-3">
                                            <label class="form-label fw-bold">7. Địa chỉ:</label>
                                            <input type="text" class="form-control" name="dia_chi" id="dia_chi"
                                                placeholder="Địa chỉ đầy đủ">
                                        </div>

                                        <!-- Đối tượng -->
                                        <div class="mt-3">
                                            <label class="form-label fw-bold">9. Đối tượng:</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="doi_tuong[]"
                                                    id="bhyt" value="BHYT">
                                                <label class="form-check-label" for="bhyt">1. BHYT</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="doi_tuong[]"
                                                    id="thu_phi" value="Thu phí">
                                                <label class="form-check-label" for="thu_phi">2. Thu phí</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="doi_tuong[]"
                                                    id="mien" value="Miễn">
                                                <label class="form-check-label" for="mien">3. Miễn</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="doi_tuong[]"
                                                    id="khac" value="Khác">
                                                <label class="form-check-label" for="khac">4. Khác</label>
                                            </div>
                                        </div>

                                        <!-- BHYT -->
                                        <div class="row g-3 mt-2">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">10. BHYT giá trị đến ngày:</label>
                                                <div class="row g-2">
                                                    <div class="col-4">
                                                        <input type="number" class="form-control" name="bhyt_ngay"
                                                            placeholder="Ngày" min="1" max="31">
                                                    </div>
                                                    <div class="col-4">
                                                        <input type="number" class="form-control" name="bhyt_thang"
                                                            placeholder="Tháng" min="1" max="12">
                                                    </div>
                                                    <div class="col-4">
                                                        <input type="number" class="form-control" name="bhyt_nam"
                                                            placeholder="Năm" min="2024" max="2030">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Số thẻ BHYT:</label>
                                                <input type="text" class="form-control" name="so_the_bhyt" readonly>
                                            </div>
                                        </div>

                                        <!-- Thông tin liên hệ -->
                                        <div class="row g-3 mt-2">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">11. Điện thoại người báo tin:</label>
                                                <input type="text" class="form-control" name="dien_thoai_bao_tin">
                                            </div>
                                        </div>

                                        <!-- Thời gian khám -->
                                        <div class="row g-3 mt-2">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">13. Đến khám bệnh lúc:</label>
                                                <div class="row g-2">
                                                    <div class="col-auto">
                                                        <div class="input-group flex-nowrap">
                                                            <input type="number" class="form-control text-center"
                                                                name="gio_kham" placeholder="Giờ" min="0" max="23"
                                                                style="width:80px;">
                                                            <span class="input-group-text">Giờ</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <div class="input-group flex-nowrap">
                                                            <input type="number" class="form-control text-center"
                                                                name="phut_kham" placeholder="Phút" min="0" max="59"
                                                                style="width:80px;">
                                                            <span class="input-group-text">Phút</span>
                                                        </div>
                                                    </div>
                                                    <div class="w-100"></div>
                                                    <div class="col-12">
                                                        <div class="d-flex flex-nowrap gap-2">
                                                            <div class="input-group flex-nowrap" style="width:auto;">
                                                                <span class="input-group-text">Ngày</span>
                                                                <input type="number" class="form-control text-center"
                                                                    name="ngay_kham" placeholder="Ngày" min="1" max="31"
                                                                    style="width:80px;">
                                                            </div>
                                                            <div class="input-group flex-nowrap" style="width:auto;">
                                                                <span class="input-group-text">Tháng</span>
                                                                <input type="number" class="form-control text-center"
                                                                    name="thang_kham" placeholder="Tháng" min="1"
                                                                    max="12" style="width:80px;">
                                                            </div>
                                                            <div class="input-group flex-nowrap" style="width:auto;">
                                                                <span class="input-group-text">Năm</span>
                                                                <input type="number" class="form-control text-center"
                                                                    name="nam_kham" placeholder="Năm" min="2024"
                                                                    max="2030" style="width:110px;">
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
                                        <textarea class="form-control" name="ly_do_vao_vien" rows="3"
                                            placeholder="Ghi rõ lý do vào viện..."></textarea>
                                    </div>

                                    <!-- III. HỎI BỆNH -->
                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-3">III. HỎI BỆNH</h6>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">1. Quá trình bệnh lí:</label>
                                            <textarea class="form-control" name="qua_trinh_benh_li" rows="4"
                                                placeholder="Mô tả quá trình bệnh lý..."></textarea>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">2. Tiền sử bệnh - Bản thân:</label>
                                                <textarea class="form-control" name="tien_su_ban_than" rows="3"
                                                    placeholder="Tiền sử bệnh của bản thân..."></textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Tiền sử bệnh - Gia đình:</label>
                                                <textarea class="form-control" name="tien_su_gia_dinh" rows="3"
                                                    placeholder="Tiền sử bệnh của gia đình..."></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- IV. KHÁM XÉT -->
                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-3">IV. KHÁM XÉT</h6>
                                        <div class="row g-3">
                                            <div class="col-md-8">
                                                <label class="form-label fw-bold">1. Toàn thân:</label>
                                                <textarea class="form-control" name="kham_toan_than" rows="4"
                                                    placeholder="Kết quả khám toàn thân..."></textarea>
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
                                                                <input type="number" class="form-control" name="mach"
                                                                    placeholder="0">
                                                                <span class="input-group-text">lần/phút</span>
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label">Nhiệt độ:</label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control"
                                                                    name="nhiet_do" placeholder="0" step="0.1">
                                                                <span class="input-group-text">°C</span>
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label">Huyết áp:</label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control"
                                                                    name="huyet_ap_tam_thu" placeholder="0">
                                                                <span class="input-group-text">/</span>
                                                                <input type="number" class="form-control"
                                                                    name="huyet_ap_tam_truong" placeholder="0">
                                                                <span class="input-group-text">mmHg</span>
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label">Nhịp thở:</label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control"
                                                                    name="nhip_tho" placeholder="0">
                                                                <span class="input-group-text">lần/phút</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <label class="form-label fw-bold">2. Các bộ phận:</label>
                                            <textarea class="form-control" name="kham_cac_bo_phan" rows="4"
                                                placeholder="Kết quả khám các bộ phận..."></textarea>
                                        </div>
                                        <div class="mt-3">
                                            <label class="form-label fw-bold">3. Tóm tắt kết quả lâm sàng:</label>
                                            <textarea class="form-control" name="tom_tat_lam_sang" rows="3"
                                                placeholder="Tóm tắt kết quả lâm sàng..."></textarea>
                                        </div>
                                        <div class="mt-3">
                                            <label class="form-label fw-bold">4. Chẩn đoán vào viện:</label>
                                            <textarea class="form-control" name="chan_doan_vao_vien" rows="3"
                                                placeholder="Chẩn đoán vào viện..."></textarea>
                                        </div>
                                        <div class="mt-3">
                                            <label class="form-label fw-bold">5. Đã xử lí (thuốc, chăm sóc):</label>
                                            <textarea class="form-control" name="da_xu_li" rows="3"
                                                placeholder="Các biện pháp đã xử lý..."></textarea>
                                        </div>
                                        <div class="row g-3 mt-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">6. Cho vào điều trị tại khoa:</label>
                                                <input type="text" class="form-control" name="khoa_dieu_tri"
                                                    placeholder="Tên khoa điều trị">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">7. Chú ý:</label>
                                                <input type="text" class="form-control" name="chu_y"
                                                    placeholder="Các chú ý đặc biệt">
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
                                                <strong>Ngày</strong> <input type="number"
                                                    class="form-control d-inline-block w-auto" name="ngay_ky"
                                                    placeholder="Ngày" min="1" max="31">
                                                <strong>tháng</strong> <input type="number"
                                                    class="form-control d-inline-block w-auto" name="thang_ky"
                                                    placeholder="Tháng" min="1" max="12">
                                                <strong>năm</strong> <input type="number"
                                                    class="form-control d-inline-block w-auto" name="nam_ky"
                                                    placeholder="Năm" min="2024" max="2030">
                                            </div>
                                            <div class="fw-bold">BÁC SĨ KHÁM BỆNH</div>
                                            <div class="mt-2">
                                                <strong>Họ tên:</strong> <input type="text"
                                                    class="form-control d-inline-block w-auto" name="ten_bac_si"
                                                    placeholder="Tên bác sĩ">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kết quả siêu âm -->
                            <div class="card mb-3 exam-section" id="sec-ultrasound-result">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-file-medical-alt me-2"></i>Kết quả siêu âm</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="nav nav-tabs mb-2" id="us_result_tabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="us_tab_info" data-bs-toggle="tab"
                                                data-bs-target="#us_tabpane_info" type="button" role="tab">Thông
                                                tin</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="us_tab_images" data-bs-toggle="tab"
                                                data-bs-target="#us_tabpane_images" type="button" role="tab">Hình Ảnh
                                                Siêu âm</button>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="us_tabpane_info" role="tabpanel">
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
                                                    gap: 8px;
                                                    margin-bottom: 6px;
                                                    font-size: 15px;
                                                }

                                                .label {
                                                    min-width: 150px;
                                                    font-weight: bold;
                                                }

                                                .dots {
                                                    flex: 0 0 auto;
                                                }

                                                .value {
                                                    flex: 1;
                                                    border-bottom: 1px dotted #333;
                                                    min-height: 20px;
                                                }

                                                .section {
                                                    margin-top: 10px;
                                                    margin-bottom: 6px;
                                                    font-weight: bold;
                                                    text-transform: uppercase;
                                                    color: blue;
                                                }

                                                .signature {
                                                    min-width: 260px;
                                                }

                                                .conclusion {
                                                    font-weight: bold;
                                                    color: blue;
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

                                            <div id="ultrasoundResultReadonly" class="report" style="display:none"
                                                data-pxid="">
                                                <div class="text-center mb-3">
                                                    <div class="fw-bold" style="font-size: 18px; color: #333;">PHÒNG
                                                        KHÁM ĐA KHOA THINHVIET</div>
                                                    <div class="fw-bold" style="font-size: 16px; color: #666;">KHOA CHẨN
                                                        ĐOÁN HÌNH ẢNH</div>
                                                </div>
                                                <div class="title">KẾT QUẢ SIÊU ÂM</div>

                                                <div class="text-center mb-2">
                                                    <div class="fw-bold">Máy: Medison Sonoace X6</div>
                                                </div>

                                                <div class="row-line">
                                                    <div class="label">ID:</div>
                                                    <div class="dots">:</div>
                                                    <div class="value" id="us_ro_id">*0000000*</div>
                                                    <div class="label" style="margin-left: 20px;">Ngày ĐK:</div>
                                                    <div class="dots">:</div>
                                                    <div class="value" id="us_ro_date">01/01/2025</div>
                                                    <div class="dots">-</div>
                                                    <div class="value" id="us_ro_time">08:00</div>
                                                </div>

                                                <div class="hr"></div>

                                                <div class="row-line">
                                                    <div class="label">Họ tên:</div>
                                                    <div class="dots">:</div>
                                                    <div class="value" id="us_ro_ho_ten">-</div>
                                                </div>

                                                <div class="row-line">
                                                    <div class="label">Tuổi:</div>
                                                    <div class="dots">:</div>
                                                    <div class="value" id="us_ro_tuoi">-</div>
                                                    <div class="label" style="margin-left: 20px;">Giới:</div>
                                                    <div class="dots">:</div>
                                                    <div class="value" id="us_ro_gioi_tinh">-</div>
                                                </div>

                                                <div class="row-line">
                                                    <div class="label">Địa chỉ:</div>
                                                    <div class="dots">:</div>
                                                    <div class="value" id="us_ro_dia_chi">-</div>
                                                </div>

                                                <div class="row-line">
                                                    <div class="label">Chẩn đoán sơ bộ:</div>
                                                    <div class="dots">:</div>
                                                    <div class="value" id="us_ro_chan_doan">-</div>
                                                </div>

                                                <div class="row-line">
                                                    <div class="label">Bác sĩ chỉ định:</div>
                                                    <div class="dots">:</div>
                                                    <div class="value" id="us_ro_bac_si">-</div>
                                                </div>

                                                <div class="row-line">
                                                    <div class="label">Phiếu chỉ định:</div>
                                                    <div class="dots">:</div>
                                                    <div class="value" id="us_ro_phieu_chi_dinh">-</div>
                                                </div>

                                                <div class="hr"></div>

                                                <div class="row-line">
                                                    <div class="label">Vùng khảo sát:</div>
                                                    <div class="dots">:</div>
                                                    <div class="value" id="us_ro_vung_khao_sat">SIÊU ÂM BỤNG TỔNG QUÁT
                                                        MÀU</div>
                                                </div>

                                                <div class="hr"></div>

                                                <div class="section">KẾT QUẢ KHẢO SÁT:</div>
                                                <div class="result-content" id="us_ro_ket_qua_khao_sat">-</div>

                                                <!-- Hình ảnh siêu âm -->
                                                <div class="hr" style="margin: 20px 0;"></div>
                                                <div class="section">HÌNH ẢNH SIÊU ÂM:</div>
                                                <div class="row mb-3" id="us_ro_images_in_info"></div>

                                                <div class="section">KẾT LUẬN:</div>
                                                <div class="conclusion-content" id="us_ro_ket_luan">-</div>

                                                <div class="hr"></div>

                                                <div style="margin-top: 30px; text-align: right;">
                                                    <div class="mb-3">
                                                        <span>Ngày</span>
                                                        <input type="text" class="form-control d-inline-block"
                                                            id="us_ro_signature_date" style="width:60px; margin: 0 5px;"
                                                            readonly>
                                                        <span>tháng</span>
                                                        <input type="text" class="form-control d-inline-block"
                                                            id="us_ro_signature_month"
                                                            style="width:60px; margin: 0 5px;" readonly>
                                                        <span>năm</span>
                                                        <input type="text" class="form-control d-inline-block"
                                                            id="us_ro_signature_year" style="width:80px; margin: 0 5px;"
                                                            readonly>
                                                    </div>
                                                    <div style="margin-right: 20px;">
                                                        <div class="fw-bold">BÁC SĨ SIÊU ÂM</div>
                                                        <div class="mt-2" id="us_ro_signature_doctor"
                                                            style="margin-left: -20px;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="ultrasoundResultEmpty" class="text-muted">Chưa có kết quả siêu âm
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="us_tabpane_images" role="tabpanel">
                                            <div id="us_images_wrap">
                                                <div class="text-muted small mb-2">Danh sách hình ảnh siêu âm đã lưu
                                                </div>
                                                <div class="row" id="us_ro_gallery"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Phiếu chụp X-Quang -->
                            <div class="card mb-3 exam-section" id="sec-xray">
                                <div class="card-header bg-dark text-white">
                                    <h6 class="mb-0"><i class="fas fa-x-ray me-2"></i>Phiếu chụp X-Quang</h6>
                                </div>
                                <div class="card-body">
                                    <!-- Hidden input for examination ID -->
                                    <input type="hidden" name="id_phieu_kham_benh" id="xray_examination_id">

                                    <!-- Header phòng khám -->
                                    <div class="mb-3 text-center">
                                        <div class="fw-bold" style="font-size:18px" id="xray_clinic_name_display">PHIẾU
                                            CHỤP X – QUANG</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Cơ sở y tế</label>
                                            <input type="text" class="form-control" name="xray_clinic_name"
                                                id="xray_clinic_name" value="Thịnh Việt" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Điện thoại</label>
                                            <input type="text" class="form-control" name="xray_phone" id="xray_phone"
                                                value="0777871608">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-bold">Quận/Huyện</label>
                                            <input type="text" class="form-control" name="xray_quan" id="xray_quan"
                                                value="Gò Vấp">
                                        </div>
                                    </div>

                                    <!-- Thông tin bệnh nhân -->
                                    <div class="row g-3">
                                        <div class="col-md-2">
                                            <label class="form-label fw-bold">Mã bệnh nhân</label>
                                            <input type="text" class="form-control" name="xray_patient_code"
                                                id="xray_patient_code" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Họ tên người bệnh</label>
                                            <input type="text" class="form-control" name="xray_patient_name"
                                                id="xray_patient_name" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-bold">Tuổi</label>
                                            <input type="text" class="form-control" name="xray_patient_age"
                                                id="xray_patient_age" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-bold">Nam/Nữ</label>
                                            <input type="text" class="form-control" name="xray_patient_gender"
                                                id="xray_patient_gender" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Địa chỉ</label>
                                            <input type="text" class="form-control" name="xray_patient_address"
                                                id="xray_patient_address">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Đối tượng</label>
                                            <input type="text" class="form-control" name="xray_patient_type"
                                                id="xray_patient_type" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Số thẻ BHYT</label>
                                            <input type="text" class="form-control" name="xray_insurance_number"
                                                id="xray_insurance_number" readonly>
                                        </div>
                                    </div>

                                    <!-- Chuẩn đoán -->
                                    <div class="mt-3">
                                        <label class="form-label fw-bold">Chuẩn đoán:</label>
                                        <input type="text" class="form-control" name="xray_diagnosis"
                                            id="xray_diagnosis" placeholder="Nhập chuẩn đoán...">
                                    </div>

                                    <!-- Đối tượng (removed per request) -->
                                    <div class="row mt-3 d-none">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Đối tượng:</label>
                                            <div class="form-control-plaintext" id="xray_patient_type_display">
                                                <span class="badge bg-secondary">Chưa xác định</span>
                                            </div>
                                            <small class="text-muted"></small>
                                        </div>
                                    </div>

                                    <!-- Yêu cầu chụp -->
                                    <div class="mt-3 position-relative">
                                        <label class="form-label fw-bold text-center w-100 d-block"
                                            style="font-size:16px">YÊU CẦU CHỤP</label>
                                        <textarea class="form-control" name="xray_request" id="xray_request" rows="6"
                                            placeholder="Nhập yêu cầu chụp..."></textarea>
                                        <div id="xray_suggestions"
                                            class="position-absolute bg-white border rounded shadow"
                                            style="display:none; z-index:1000; max-height:200px; overflow-y:auto; width:100%;">
                                        </div>
                                    </div>

                                    <!-- Chữ ký -->
                                    <div class="row mt-4">
                                        <div class="col-md-6"></div>
                                        <div class="col-md-6 text-center">
                                            <div class="mb-2 d-flex align-items-center justify-content-center gap-2">
                                                <span>Ngày</span>
                                                <input type="number" class="form-control text-center" name="xray_ngay"
                                                    id="xray_ngay" style="width:70px">
                                                <span>tháng</span>
                                                <input type="number" class="form-control text-center" name="xray_thang"
                                                    id="xray_thang" style="width:70px">
                                                <span>năm</span>
                                                <input type="number" class="form-control text-center" name="xray_nam"
                                                    id="xray_nam" style="width:90px">
                                            </div>
                                            <div class="fw-bold">BÁC SĨ ĐIỀU TRỊ</div>
                                            <div class="mt-2" id="xray_doctor_display"
                                                style="min-height:40px; border-bottom: 1px solid #000; padding: 5px;">
                                                (Ký và ghi rõ họ tên)</div>
                                            <input type="hidden" name="xray_doctor_name" id="xray_doctor_name">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kết quả X-Quang -->
                            <div class="card mb-3 exam-section" id="sec-xray-result">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fas fa-file-medical-alt me-2"></i>Kết quả X-Quang</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="nav nav-tabs mb-2" id="xr_result_tabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="xr_tab_info" data-bs-toggle="tab"
                                                data-bs-target="#xr_tabpane_info" type="button" role="tab">Thông
                                                tin</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="xr_tab_images" data-bs-toggle="tab"
                                                data-bs-target="#xr_tabpane_images" type="button" role="tab">Hình Ảnh
                                                X-Quang</button>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="xr_tabpane_info" role="tabpanel">
                                            <div id="xrayResultReadonly" class="border border-dark p-2"
                                                style="display:none" data-pxid="">
                                                <div class="text-center">
                                                    <div class="fw-bold">PHÒNG KHÁM ĐA KHOA THINHVIET</div>
                                                    <div class="fw-bold">KHOA CHUẨN ĐOÁN HÌNH ẢNH</div>
                                                    <div class="text-muted">Địa chỉ: Gò Vấp - Điện thoại: 0777871608
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row g-2">
                                                    <div class="col-md-6">Họ và tên: <strong id="xr_ro_name"></strong>
                                                    </div>
                                                    <div class="col-md-6">Giới tính: <strong id="xr_ro_gender"></strong>
                                                    </div>
                                                    <div class="col-md-6">Năm sinh: <strong id="xr_ro_yob"></strong>
                                                    </div>
                                                    <div class="col-md-6">Số phiếu chỉ định: <strong
                                                            id="xr_ro_id"></strong></div>
                                                    <div class="col-md-12">Địa chỉ: <strong id="xr_ro_address"></strong>
                                                    </div>
                                                    <div class="col-md-6">Ngày chỉ định: <strong
                                                            id="xr_ro_date"></strong></div>
                                                    <div class="col-md-6">Giờ chỉ định: <strong
                                                            id="xr_ro_time"></strong></div>
                                                </div>
                                                <hr>
                                                <div>Chẩn đoán: <strong id="xr_ro_chandoan"></strong></div>
                                                <div>Bác sĩ chỉ định: <strong id="xr_ro_bschidinh"></strong></div>
                                                <div class="mt-2">Nội dung: <strong id="xr_ro_noidung"></strong></div>
                                                <div class="mt-3 fw-bold">KẾT QUẢ</div>
                                                <div class="border p-2" id="xr_ro_ketqua" style="min-height:80px"></div>
                                                <div class="mt-3 fw-bold">KẾT LUẬN</div>
                                                <div class="border p-2" id="xr_ro_ketluan" style="min-height:80px">
                                                </div>
                                                <div class="text-end mt-3">
                                                    <em id="xr_ro_today"></em><br>
                                                    <strong>Bác sĩ X Quang</strong>
                                                    <div id="xr_ro_bsxq" style="min-height:40px"></div>
                                                </div>
                                            </div>
                                            <div id="xrayResultEmpty" class="text-muted">Chưa có kết quả X-Quang</div>
                                        </div>
                                        <div class="tab-pane fade" id="xr_tabpane_images" role="tabpanel">
                                            <div id="xr_images_wrap">
                                                <div class="text-muted small mb-2">Danh sách hình ảnh X-Quang đã lưu
                                                </div>
                                                <div class="row" id="xr_ro_gallery"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Placeholder for other sections (có thể bổ sung sau) -->
                            <div class="card mb-3 exam-section" id="sec-history">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Phiếu khai thác tiền sử dị ứng
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Họ và tên bệnh nhân</label>
                                                <input type="text" class="form-control" id="historyPatientName"
                                                    placeholder="" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Ngày sinh</label>
                                                <input type="text" class="form-control" id="historyDob"
                                                    placeholder="dd/mm/yyyy" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Mã bệnh nhân</label>
                                                <input type="text" class="form-control" id="historyCode" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Địa chỉ</label>
                                                <input type="text" class="form-control" id="historyAddress" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Số điện thoại</label>
                                                <input type="text" class="form-control" id="historyPhone" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Giới tính</label>
                                                <input type="text" class="form-control" id="historyGender" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center" style="width:60px">STT</th>
                                                    <th>Nội dung</th>
                                                    <th style="width:220px">Tên thuốc, dị nguyên gây dị ứng</th>
                                                    <th class="text-center" style="width:120px">Có / Số lần</th>
                                                    <th class="text-center" style="width:100px">Không</th>
                                                    <th class="text-center" style="width:220px">Biểu hiện lâm sàng – xử
                                                        trí</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-center">1.</td>
                                                    <td>Loại thuốc hoặc dị nguyên nào đã gây dị ứng?</td>
                                                    <td><input type="text" class="form-control" name="allergy_drug">
                                                    </td>
                                                    <td class="text-center"><input type="text" class="form-control"
                                                            name="allergy_drug_times"></td>
                                                    <td class="text-center"><input type="checkbox"
                                                            name="allergy_drug_no"></td>
                                                    <td><input type="text" class="form-control"
                                                            name="allergy_drug_note"></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">2.</td>
                                                    <td>Dị ứng với loại côn trùng nào?</td>
                                                    <td><input type="text" class="form-control" name="allergy_insect">
                                                    </td>
                                                    <td class="text-center"><input type="text" class="form-control"
                                                            name="allergy_insect_times"></td>
                                                    <td class="text-center"><input type="checkbox"
                                                            name="allergy_insect_no"></td>
                                                    <td><input type="text" class="form-control"
                                                            name="allergy_insect_note"></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">3.</td>
                                                    <td>Dị ứng với loại thực phẩm nào?</td>
                                                    <td><input type="text" class="form-control" name="allergy_food">
                                                    </td>
                                                    <td class="text-center"><input type="text" class="form-control"
                                                            name="allergy_food_times"></td>
                                                    <td class="text-center"><input type="checkbox"
                                                            name="allergy_food_no"></td>
                                                    <td><input type="text" class="form-control"
                                                            name="allergy_food_note"></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">4.</td>
                                                    <td>Dị ứng với các tác nhân khác: phấn hoa, bụi nhà, hóa chất, mỹ
                                                        phẩm...?</td>
                                                    <td><input type="text" class="form-control" name="allergy_other">
                                                    </td>
                                                    <td class="text-center"><input type="text" class="form-control"
                                                            name="allergy_other_times"></td>
                                                    <td class="text-center"><input type="checkbox"
                                                            name="allergy_other_no"></td>
                                                    <td><input type="text" class="form-control"
                                                            name="allergy_other_note"></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">5.</td>
                                                    <td>Tiền sử cá nhân có bệnh dị ứng nào? (Viêm mũi dị ứng, hen phế
                                                        quản...)</td>
                                                    <td><input type="text" class="form-control" name="personal_history">
                                                    </td>
                                                    <td class="text-center"><input type="text" class="form-control"
                                                            name="personal_history_times"></td>
                                                    <td class="text-center"><input type="checkbox"
                                                            name="personal_history_no"></td>
                                                    <td><input type="text" class="form-control"
                                                            name="personal_history_note"></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">6.</td>
                                                    <td>Tiền sử gia đình có bệnh dị ứng nào? (Bố, mẹ, con, chị em ruột
                                                        có ai bị...)?</td>
                                                    <td><input type="text" class="form-control" name="family_history">
                                                    </td>
                                                    <td class="text-center"><input type="text" class="form-control"
                                                            name="family_history_times"></td>
                                                    <td class="text-center"><input type="checkbox"
                                                            name="family_history_no"></td>
                                                    <td><input type="text" class="form-control"
                                                            name="family_history_note"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row g-3 mt-3">
                                        <div class="col-md-4 ms-auto">
                                            <div class="text-muted small text-end">..., ngày ... tháng ... năm ...</div>
                                            <div class="fw-bold text-end">Đại diện người bệnh/gia đình người bệnh</div>
                                            <div class="fst-italic text-end">(Ký và ghi rõ họ tên)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Đóng
                </button>
                <button type="button" class="btn btn-outline-primary" id="btnSaveExamForm" style="display:none"
                    onclick="saveExaminationForm()">
                    <i class="fas fa-save me-1"></i>Lưu phiếu khám
                </button>
                <button type="button" class="btn btn-primary" id="btnPrintExamForm" style="display:none"
                    onclick="printExaminationForm()">
                    <i class="fas fa-print me-1"></i>In phiếu khám
                </button>
                <button type="button" class="btn btn-success" id="btnSaveExam" style="display:none"
                    onclick="saveAllergyHistory()">
                    <i class="fas fa-save me-1"></i>Lưu tiền sử
                </button>
                <button type="button" class="btn btn-success" id="saveXrayForm" style="display:none">
                    <i class="fas fa-save me-1"></i>Lưu phiếu chụp X-Quang
                </button>
                <button type="button" class="btn btn-primary" id="printXrayForm" style="display:none" disabled>
                    <i class="fas fa-print me-1"></i>In phiếu chụp X-Quang
                </button>
                <button type="button" class="btn btn-success" id="saveUltrasoundForm" style="display:none"
                    onclick="saveUltrasoundForm()">
                    <i class="fas fa-save me-1"></i>Lưu phiếu siêu âm
                </button>
                <button type="button" class="btn btn-primary" id="printUltrasoundForm" style="display:none"
                    onclick="printUltrasoundForm()">
                    <i class="fas fa-print me-1"></i>In phiếu siêu âm
                </button>
            </div>
        </div>
    </div>
</div>