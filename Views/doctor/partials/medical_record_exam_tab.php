<?php

/**
 * Tab: Phiếu khám bệnh
 * Template này chỉ cần làm 1 lần, mỗi lần gọi chỉ cần truyền data khác vào
 */
?>
<div class="tab-pane fade show active" id="pane-exam" role="tabpanel">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Phiếu khám bệnh vào viện</h6>
        </div>
        <div class="card-body">
            <!-- Header form -->
            <div class="text-center mb-3">
                <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo"
                    style="height:60px;object-fit:contain;margin-bottom:10px;">
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="mb-2">
                        <strong>Sở Y tế:</strong> <input type="text" class="form-control d-inline-block w-auto"
                            value="Thành Phố Hồ Chí Minh" readonly>
                    </div>
                    <div class="mb-2">
                        <strong>BV:</strong> <input type="text" class="form-control d-inline-block w-auto"
                            value="Thịnh Việt" readonly>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="mb-2">
                        <strong>Mã bệnh nhân:</strong> <input type="text" class="form-control d-inline-block w-auto"
                            value="<?php echo escapeHtml($exam['ma_benh_nhan'] ?? '-'); ?>" readonly>
                    </div>
                    <div class="mb-2">
                        <strong>BUỒNG KHÁM BỆNH:</strong> <input type="text" class="form-control d-inline-block w-auto"
                            value="<?php echo escapeHtml($exam['buong_kham'] ?? '-'); ?>" readonly>
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
                        <input type="text" class="form-control"
                            value="<?php echo escapeHtml(strtoupper($exam['ho_ten'] ?? '')); ?>" readonly
                            style="text-transform: uppercase;">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-bold">2. Sinh ngày:</label>
                        <div class="row g-2">
                            <div class="col-3">
                                <input type="number" class="form-control"
                                    value="<?php echo $exam['ngay_sinh'] ?? ''; ?>" placeholder="Ngày" readonly>
                            </div>
                            <div class="col-3">
                                <input type="number" class="form-control"
                                    value="<?php echo $exam['thang_sinh'] ?? ''; ?>" placeholder="Tháng" readonly>
                            </div>
                            <div class="col-3">
                                <input type="number" class="form-control" value="<?php echo $exam['nam_sinh'] ?? ''; ?>"
                                    placeholder="Năm" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tuổi:</label>
                        <input type="number" class="form-control" value="<?php echo $exam['tuoi'] ?? ''; ?>"
                            placeholder="Tuổi" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">3. Giới:</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio"
                                <?php echo ($exam['gioi_tinh'] ?? '') === 'Nam' ? 'checked' : ''; ?> disabled>
                            <label class="form-check-label">1. Nam</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio"
                                <?php echo ($exam['gioi_tinh'] ?? '') === 'Nữ' ? 'checked' : ''; ?> disabled>
                            <label class="form-check-label">2. Nữ</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">4. Nghề nghiệp:</label>
                        <input type="text" class="form-control"
                            value="<?php echo escapeHtml($exam['nghe_nghiep'] ?? '-'); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">5. Dân tộc:</label>
                        <input type="text" class="form-control"
                            value="<?php echo escapeHtml($exam['dan_toc'] ?? '-'); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">6. Ngoại kiều:</label>
                        <input type="text" class="form-control"
                            value="<?php echo escapeHtml($exam['ngoai_kieu'] ?? '-'); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">8. Nơi làm việc:</label>
                        <input type="text" class="form-control"
                            value="<?php echo escapeHtml($exam['noi_lam_viec'] ?? '-'); ?>" readonly>
                    </div>
                </div>

                <!-- Địa chỉ -->
                <div class="mt-3">
                    <label class="form-label fw-bold">7. Địa chỉ:</label>
                    <input type="text" class="form-control" value="<?php echo escapeHtml($exam['dia_chi'] ?? '-'); ?>"
                        readonly>
                </div>

                <!-- Đối tượng -->
                <div class="mt-3">
                    <label class="form-label fw-bold">9. Đối tượng:</label>
                    <?php
                    // Helper function để kiểm tra giá trị đối tượng
                    // PDO có thể trả về string "1", "0", integer 1, 0, hoặc boolean true/false
                    function isDoiTuongChecked($value)
                    {
                        if ($value === null || $value === '') return false;
                        // Convert sang string để so sánh
                        $strVal = trim((string)$value);
                        // Kiểm tra nếu là "1", 1, hoặc true
                        return ($strVal === '1' || $strVal === 'true' || $value === true || $value === 1);
                    }

                    $checkedBhyt = isDoiTuongChecked($exam['doi_tuong_bhyt'] ?? null);
                    $checkedThuPhi = isDoiTuongChecked($exam['doi_tuong_thu_phi'] ?? null);
                    $checkedMien = isDoiTuongChecked($exam['doi_tuong_mien'] ?? null);
                    $checkedKhac = isDoiTuongChecked($exam['doi_tuong_khac'] ?? null);
                    ?>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" <?php echo $checkedBhyt ? 'checked' : ''; ?>
                            disabled>
                        <label class="form-check-label">1. BHYT</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" <?php echo $checkedThuPhi ? 'checked' : ''; ?>
                            disabled>
                        <label class="form-check-label">2. Thu phí</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" <?php echo $checkedMien ? 'checked' : ''; ?>
                            disabled>
                        <label class="form-check-label">3. Miễn</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" <?php echo $checkedKhac ? 'checked' : ''; ?>
                            disabled>
                        <label class="form-check-label">4. Khác</label>
                    </div>
                </div>

                <!-- BHYT -->
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">10. BHYT giá trị đến ngày:</label>
                        <div class="row g-2">
                            <div class="col-4">
                                <input type="number" class="form-control"
                                    value="<?php echo $exam['bhyt_ngay'] ?? ''; ?>" placeholder="Ngày" readonly>
                            </div>
                            <div class="col-4">
                                <input type="number" class="form-control"
                                    value="<?php echo $exam['bhyt_thang'] ?? ''; ?>" placeholder="Tháng" readonly>
                            </div>
                            <div class="col-4">
                                <input type="number" class="form-control" value="<?php echo $exam['bhyt_nam'] ?? ''; ?>"
                                    placeholder="Năm" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Số thẻ BHYT:</label>
                        <input type="text" class="form-control"
                            value="<?php echo escapeHtml($exam['so_the_bhyt'] ?? '-'); ?>" readonly>
                    </div>
                </div>

                <!-- Thông tin liên hệ -->
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">11. Điện thoại người báo tin:</label>
                        <input type="text" class="form-control"
                            value="<?php echo escapeHtml($exam['dien_thoai_bao_tin'] ?? $exam['so_dien_thoai'] ?? '-'); ?>"
                            readonly>
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
                                        value="<?php echo $exam['gio_kham'] ?? ''; ?>" placeholder="Giờ" readonly
                                        style="width:80px;">
                                    <span class="input-group-text">Giờ</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="input-group flex-nowrap">
                                    <input type="number" class="form-control text-center"
                                        value="<?php echo $exam['phut_kham'] ?? ''; ?>" placeholder="Phút" readonly
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
                                            value="<?php echo $exam['ngay_kham'] ?? ''; ?>" placeholder="Ngày" readonly
                                            style="width:80px;">
                                    </div>
                                    <div class="input-group flex-nowrap" style="width:auto;">
                                        <span class="input-group-text">Tháng</span>
                                        <input type="number" class="form-control text-center"
                                            value="<?php echo $exam['thang_kham'] ?? ''; ?>" placeholder="Tháng"
                                            readonly style="width:80px;">
                                    </div>
                                    <div class="input-group flex-nowrap" style="width:auto;">
                                        <span class="input-group-text">Năm</span>
                                        <input type="number" class="form-control text-center"
                                            value="<?php echo $exam['nam_kham'] ?? ''; ?>" placeholder="Năm" readonly
                                            style="width:110px;">
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
                <textarea class="form-control" rows="3"
                    readonly><?php echo escapeHtml($exam['ly_do_vao_vien'] ?? $exam['ly_do_kham'] ?? '-'); ?></textarea>
            </div>

            <!-- III. HỎI BỆNH -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">III. HỎI BỆNH</h6>
                <div class="mb-3">
                    <label class="form-label fw-bold">1. Quá trình bệnh lí:</label>
                    <textarea class="form-control" rows="4"
                        readonly><?php echo escapeHtml($exam['qua_trinh_benh_li'] ?? '-'); ?></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">2. Tiền sử bệnh - Bản thân:</label>
                        <textarea class="form-control" rows="3"
                            readonly><?php echo escapeHtml($exam['tien_su_ban_than'] ?? '-'); ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tiền sử bệnh - Gia đình:</label>
                        <textarea class="form-control" rows="3"
                            readonly><?php echo escapeHtml($exam['tien_su_gia_dinh'] ?? '-'); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- IV. KHÁM XÉT -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">IV. KHÁM XÉT</h6>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">1. Toàn thân:</label>
                        <textarea class="form-control" rows="4"
                            readonly><?php echo escapeHtml($exam['kham_toan_than'] ?? '-'); ?></textarea>
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
                                        <input type="number" class="form-control"
                                            value="<?php echo $exam['mach'] ?? ''; ?>" placeholder="0" readonly>
                                        <span class="input-group-text">lần/phút</span>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Nhiệt độ:</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control"
                                            value="<?php echo $exam['nhiet_do'] ?? ''; ?>" placeholder="0" step="0.1"
                                            readonly>
                                        <span class="input-group-text">°C</span>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Huyết áp:</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control"
                                            value="<?php echo $exam['huyet_ap_tam_thu'] ?? ''; ?>" placeholder="0"
                                            readonly>
                                        <span class="input-group-text">/</span>
                                        <input type="number" class="form-control"
                                            value="<?php echo $exam['huyet_ap_tam_truong'] ?? ''; ?>" placeholder="0"
                                            readonly>
                                        <span class="input-group-text">mmHg</span>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Nhịp thở:</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control"
                                            value="<?php echo $exam['nhip_tho'] ?? ''; ?>" placeholder="0" readonly>
                                        <span class="input-group-text">lần/phút</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-bold">2. Các bộ phận:</label>
                    <textarea class="form-control" rows="4"
                        readonly><?php echo escapeHtml($exam['kham_cac_bo_phan'] ?? '-'); ?></textarea>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-bold">3. Tóm tắt kết quả lâm sàng:</label>
                    <textarea class="form-control" rows="3"
                        readonly><?php echo escapeHtml($exam['tom_tat_lam_sang'] ?? '-'); ?></textarea>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-bold">4. Chẩn đoán vào viện:</label>
                    <textarea class="form-control" rows="3"
                        readonly><?php echo escapeHtml($exam['chan_doan_vao_vien'] ?? '-'); ?></textarea>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-bold">5. Đã xử lí (thuốc, chăm sóc):</label>
                    <textarea class="form-control" rows="3"
                        readonly><?php echo escapeHtml($exam['da_xu_li'] ?? '-'); ?></textarea>
                </div>
                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">6. Hướng xử trí:</label>
                        <input type="text" class="form-control"
                            value="<?php echo escapeHtml($exam['khoa_dieu_tri'] ?? '-'); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">7. Chú ý:</label>
                        <input type="text" class="form-control" value="<?php echo escapeHtml($exam['chu_y'] ?? '-'); ?>"
                            readonly>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="text-muted small"></div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex flex-column align-items-center">
                        <div class="mb-2">
                            <strong>Ngày</strong> <input type="number" class="form-control d-inline-block text-center"
                                value="<?php echo $exam['ngay_ky'] ?? ''; ?>" placeholder="Ngày" readonly
                                style="width:70px;">
                            <strong>tháng</strong> <input type="number" class="form-control d-inline-block text-center"
                                value="<?php echo $exam['thang_ky'] ?? ''; ?>" placeholder="Tháng" readonly
                                style="width:70px;">
                            <strong>năm</strong> <input type="number" class="form-control d-inline-block text-center"
                                value="<?php echo $exam['nam_ky'] ?? ''; ?>" placeholder="Năm" readonly
                                style="width:90px;">
                        </div>
                        <div class="fw-bold mb-2">BÁC SĨ KHÁM BỆNH</div>
                        <div class="d-flex align-items-center gap-2 justify-content-center">
                            <strong class="text-nowrap">Họ tên:</strong> <input type="text"
                                class="form-control d-inline-block"
                                value="<?php echo escapeHtml($exam['ten_bac_si'] ?? '-'); ?>" placeholder="Tên bác sĩ"
                                readonly style="min-width: 200px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>