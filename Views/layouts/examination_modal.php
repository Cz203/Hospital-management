<div class="modal fade" id="examinationModal" tabindex="-1" aria-labelledby="examinationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xxl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="examinationModalLabel">
                    <i class="fas fa-stethoscope me-2"></i>Form Khám Bệnh
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                            <a href="#sec-xray" class="list-group-item list-group-item-action exam-nav">
                                <i class="fas fa-x-ray me-2"></i>X-Quang
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

                            <!-- Phần khám bệnh -->
                            <div class="card mb-3 exam-section" id="sec-exam">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Phiếu khám bệnh vào viện</h6>
                                </div>
                                <div class="card-body">
                                    <!-- Header form -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <strong>Sở Y tế:</strong> <input type="text" class="form-control d-inline-block w-auto" name="so_y_te" value="Thành Phố Hồ Chí Minh" readonly>
                                            </div>
                                            <div class="mb-2">
                                                <strong>BV:</strong> <input type="text" class="form-control d-inline-block w-auto" name="benh_vien" value="Thịnh Việt" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <div class="mb-2">
                                                <strong>MS:</strong> <input type="text" class="form-control d-inline-block w-auto" name="ma_so" placeholder="42/BV-01">
                                            </div>
                                            <div class="mb-2">
                                                <strong>BUỒNG KHÁM BỆNH:</strong> <input type="text" class="form-control d-inline-block w-auto" name="buong_kham" id="buong_kham" readonly>
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
                                                <input type="text" class="form-control" name="ho_ten" style="text-transform: uppercase;">
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label fw-bold">2. Sinh ngày:</label>
                                                <div class="row g-2">
                                                    <div class="col-3">
                                                        <input type="number" class="form-control" name="ngay_sinh" placeholder="Ngày" min="1" max="31">
                                                    </div>
                                                    <div class="col-3">
                                                        <input type="number" class="form-control" name="thang_sinh" placeholder="Tháng" min="1" max="12">
                                                    </div>
                                                    <div class="col-3">
                                                        <input type="number" class="form-control" name="nam_sinh" placeholder="Năm" min="1900" max="2025">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Tuổi:</label>
                                                <input type="number" class="form-control" name="tuoi" placeholder="Tuổi" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">3. Giới:</label>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="gioi_tinh" id="nam" value="Nam">
                                                    <label class="form-check-label" for="nam">1. Nam</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="gioi_tinh" id="nu" value="Nữ">
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
                                            <input type="text" class="form-control" name="dia_chi" id="dia_chi" placeholder="Địa chỉ đầy đủ">
                                        </div>

                                        <!-- Đối tượng -->
                                        <div class="mt-3">
                                            <label class="form-label fw-bold">9. Đối tượng:</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="doi_tuong[]" id="bhyt" value="BHYT">
                                                <label class="form-check-label" for="bhyt">1. BHYT</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="doi_tuong[]" id="thu_phi" value="Thu phí">
                                                <label class="form-check-label" for="thu_phi">2. Thu phí</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="doi_tuong[]" id="mien" value="Miễn">
                                                <label class="form-check-label" for="mien">3. Miễn</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="doi_tuong[]" id="khac" value="Khác">
                                                <label class="form-check-label" for="khac">4. Khác</label>
                                            </div>
                                        </div>

                                        <!-- BHYT -->
                                        <div class="row g-3 mt-2">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">10. BHYT giá trị đến ngày:</label>
                                                <div class="row g-2">
                                                    <div class="col-4">
                                                        <input type="number" class="form-control" name="bhyt_ngay" placeholder="Ngày" min="1" max="31">
                                                    </div>
                                                    <div class="col-4">
                                                        <input type="number" class="form-control" name="bhyt_thang" placeholder="Tháng" min="1" max="12">
                                                    </div>
                                                    <div class="col-4">
                                                        <input type="number" class="form-control" name="bhyt_nam" placeholder="Năm" min="2024" max="2030">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Số thẻ BHYT:</label>
                                                <input type="text" class="form-control" name="so_the_bhyt">
                                            </div>
                                        </div>

                                        <!-- Thông tin liên hệ -->
                                        <div class="row g-3 mt-2">
                                            <div class="col-md-8">
                                                <label class="form-label fw-bold">11. Họ tên, địa chỉ người nhà khi cần báo tin:</label>
                                                <input type="text" class="form-control" name="nguoi_bao_tin">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Điện thoại số:</label>
                                                <input type="text" class="form-control" name="dien_thoai_bao_tin">
                                            </div>
                                        </div>

                                        <!-- Thời gian khám -->
                                        <div class="row g-3 mt-2">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">12. Đến khám bệnh lúc:</label>
                                                <div class="row g-2">
                                                    <div class="col-3">
                                                        <input type="number" class="form-control" name="gio_kham" placeholder="Giờ" min="0" max="23">
                                                    </div>
                                                    <div class="col-3">
                                                        <input type="number" class="form-control" name="phut_kham" placeholder="Phút" min="0" max="59">
                                                    </div>
                                                    <div class="col-2">
                                                        <input type="number" class="form-control" name="ngay_kham" placeholder="Ngày" min="1" max="31">
                                                    </div>
                                                    <div class="col-2">
                                                        <input type="number" class="form-control" name="thang_kham" placeholder="Tháng" min="1" max="12">
                                                    </div>
                                                    <div class="col-2">
                                                        <input type="number" class="form-control" name="nam_kham" placeholder="Năm" min="2024" max="2030">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">13. Chẩn đoán của nơi giới thiệu:</label>
                                                <input type="text" class="form-control" name="chan_doan_gioi_thieu">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- II. LÍ DO VÀO VIỆN -->
                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-3">II. LÍ DO VÀO VIỆN</h6>
                                        <textarea class="form-control" name="ly_do_vao_vien" rows="3" placeholder="Ghi rõ lý do vào viện..."></textarea>
                                    </div>

                                    <!-- III. HỎI BỆNH -->
                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-3">III. HỎI BỆNH</h6>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">1. Quá trình bệnh lí:</label>
                                            <textarea class="form-control" name="qua_trinh_benh_li" rows="4" placeholder="Mô tả quá trình bệnh lý..."></textarea>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">2. Tiền sử bệnh - Bản thân:</label>
                                                <textarea class="form-control" name="tien_su_ban_than" rows="3" placeholder="Tiền sử bệnh của bản thân..."></textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Tiền sử bệnh - Gia đình:</label>
                                                <textarea class="form-control" name="tien_su_gia_dinh" rows="3" placeholder="Tiền sử bệnh của gia đình..."></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- IV. KHÁM XÉT -->
                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-3">IV. KHÁM XÉT</h6>
                                        <div class="row g-3">
                                            <div class="col-md-8">
                                                <label class="form-label fw-bold">1. Toàn thân:</label>
                                                <textarea class="form-control" name="kham_toan_than" rows="4" placeholder="Kết quả khám toàn thân..."></textarea>
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
                                                                <input type="number" class="form-control" name="mach" placeholder="0">
                                                                <span class="input-group-text">lần/phút</span>
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label">Nhiệt độ:</label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" name="nhiet_do" placeholder="0" step="0.1">
                                                                <span class="input-group-text">°C</span>
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label">Huyết áp:</label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" name="huyet_ap_tam_thu" placeholder="0">
                                                                <span class="input-group-text">/</span>
                                                                <input type="number" class="form-control" name="huyet_ap_tam_truong" placeholder="0">
                                                                <span class="input-group-text">mmHg</span>
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label">Nhịp thở:</label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" name="nhip_tho" placeholder="0">
                                                                <span class="input-group-text">lần/phút</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <label class="form-label fw-bold">2. Các bộ phận:</label>
                                            <textarea class="form-control" name="kham_cac_bo_phan" rows="4" placeholder="Kết quả khám các bộ phận..."></textarea>
                                        </div>
                                        <div class="mt-3">
                                            <label class="form-label fw-bold">3. Tóm tắt kết quả lâm sàng:</label>
                                            <textarea class="form-control" name="tom_tat_lam_sang" rows="3" placeholder="Tóm tắt kết quả lâm sàng..."></textarea>
                                        </div>
                                        <div class="mt-3">
                                            <label class="form-label fw-bold">4. Chẩn đoán vào viện:</label>
                                            <textarea class="form-control" name="chan_doan_vao_vien" rows="3" placeholder="Chẩn đoán vào viện..."></textarea>
                                        </div>
                                        <div class="mt-3">
                                            <label class="form-label fw-bold">5. Đã xử lí (thuốc, chăm sóc):</label>
                                            <textarea class="form-control" name="da_xu_li" rows="3" placeholder="Các biện pháp đã xử lý..."></textarea>
                                        </div>
                                        <div class="row g-3 mt-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">6. Cho vào điều trị tại khoa:</label>
                                                <input type="text" class="form-control" name="khoa_dieu_tri" placeholder="Tên khoa điều trị">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">7. Chú ý:</label>
                                                <input type="text" class="form-control" name="chu_y" placeholder="Các chú ý đặc biệt">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Footer -->
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div class="text-muted small">Hướng dẫn: - In khổ A4 dọc, 1 mặt</div>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <div class="mb-2">
                                                <strong>Ngày</strong> <input type="number" class="form-control d-inline-block w-auto" name="ngay_ky" placeholder="Ngày" min="1" max="31">
                                                <strong>tháng</strong> <input type="number" class="form-control d-inline-block w-auto" name="thang_ky" placeholder="Tháng" min="1" max="12">
                                                <strong>năm</strong> <input type="number" class="form-control d-inline-block w-auto" name="nam_ky" placeholder="Năm" min="2024" max="2030">
                                            </div>
                                            <div class="fw-bold">BÁC SĨ KHÁM BỆNH</div>
                                            <div class="mt-2">
                                                <strong>Họ tên:</strong> <input type="text" class="form-control d-inline-block w-auto" name="ten_bac_si" placeholder="Tên bác sĩ">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Placeholder for other sections (có thể bổ sung sau) -->
                            <div class="card mb-3 exam-section" id="sec-history">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Phiếu khai thác tiền sử dị ứng</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Họ và tên bệnh nhân</label>
                                                <input type="text" class="form-control" id="historyPatientName" placeholder="" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Ngày sinh</label>
                                                <input type="text" class="form-control" id="historyDob" placeholder="dd/mm/yyyy" readonly>
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
                                                    <th class="text-center" style="width:220px">Biểu hiện lâm sàng – xử trí</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-center">1.</td>
                                                    <td>Loại thuốc hoặc dị nguyên nào đã gây dị ứng?</td>
                                                    <td><input type="text" class="form-control" name="allergy_drug"></td>
                                                    <td class="text-center"><input type="text" class="form-control" name="allergy_drug_times"></td>
                                                    <td class="text-center"><input type="checkbox" name="allergy_drug_no"></td>
                                                    <td><input type="text" class="form-control" name="allergy_drug_note"></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">2.</td>
                                                    <td>Dị ứng với loại côn trùng nào?</td>
                                                    <td><input type="text" class="form-control" name="allergy_insect"></td>
                                                    <td class="text-center"><input type="text" class="form-control" name="allergy_insect_times"></td>
                                                    <td class="text-center"><input type="checkbox" name="allergy_insect_no"></td>
                                                    <td><input type="text" class="form-control" name="allergy_insect_note"></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">3.</td>
                                                    <td>Dị ứng với loại thực phẩm nào?</td>
                                                    <td><input type="text" class="form-control" name="allergy_food"></td>
                                                    <td class="text-center"><input type="text" class="form-control" name="allergy_food_times"></td>
                                                    <td class="text-center"><input type="checkbox" name="allergy_food_no"></td>
                                                    <td><input type="text" class="form-control" name="allergy_food_note"></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">4.</td>
                                                    <td>Dị ứng với các tác nhân khác: phấn hoa, bụi nhà, hóa chất, mỹ phẩm...?</td>
                                                    <td><input type="text" class="form-control" name="allergy_other"></td>
                                                    <td class="text-center"><input type="text" class="form-control" name="allergy_other_times"></td>
                                                    <td class="text-center"><input type="checkbox" name="allergy_other_no"></td>
                                                    <td><input type="text" class="form-control" name="allergy_other_note"></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">5.</td>
                                                    <td>Tiền sử cá nhân có bệnh dị ứng nào? (Viêm mũi dị ứng, hen phế quản...)</td>
                                                    <td><input type="text" class="form-control" name="personal_history"></td>
                                                    <td class="text-center"><input type="text" class="form-control" name="personal_history_times"></td>
                                                    <td class="text-center"><input type="checkbox" name="personal_history_no"></td>
                                                    <td><input type="text" class="form-control" name="personal_history_note"></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">6.</td>
                                                    <td>Tiền sử gia đình có bệnh dị ứng nào? (Bố, mẹ, con, chị em ruột có ai bị...)?</td>
                                                    <td><input type="text" class="form-control" name="family_history"></td>
                                                    <td class="text-center"><input type="text" class="form-control" name="family_history_times"></td>
                                                    <td class="text-center"><input type="checkbox" name="family_history_no"></td>
                                                    <td><input type="text" class="form-control" name="family_history_note"></td>
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
                <button type="button" class="btn btn-success" id="btnSaveExam" style="display:none" onclick="saveAllergyHistory()">
                    <i class="fas fa-save me-1"></i>Lưu tiền sử
                </button>
            </div>
        </div>
    </div>
</div>


