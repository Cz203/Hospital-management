<?php
/**
 * Tab: Phiếu yêu cầu X-Quang
 */
?>
<div class="tab-pane fade" id="pane-xray-request" role="tabpanel">
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h6 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Phiếu yêu cầu X-Quang</h6>
        </div>
        <div class="card-body">
            <?php if ($xrayRequest): ?>
                <!-- Header phòng khám -->
                <div class="mb-3 text-center">
                    <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:60px;object-fit:contain;margin-bottom:10px;">
                    <div class="fw-bold" style="font-size:18px">PHIẾU CHỤP X – QUANG</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Cơ sở y tế</label>
                        <input type="text" class="form-control" value="Thịnh Việt" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Điện thoại</label>
                        <input type="text" class="form-control" value="<?php echo escapeHtml($xrayRequest['so_dien_thoai'] ?? '0777871608'); ?>" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Quận</label>
                        <input type="text" class="form-control" value="<?php echo escapeHtml($xrayRequest['quan'] ?? 'Gò Vấp'); ?>" readonly>
                    </div>
                </div>

                <!-- Thông tin bệnh nhân -->
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Mã bệnh nhân</label>
                        <input type="text" class="form-control" value="<?php echo escapeHtml($exam['ma_benh_nhan'] ?? '-'); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Họ tên người bệnh</label>
                        <input type="text" class="form-control" value="<?php echo escapeHtml($exam['ho_ten'] ?? '-'); ?>" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Tuổi</label>
                        <input type="text" class="form-control" value="<?php echo escapeHtml($exam['tuoi'] ?? '-'); ?>" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Nam/Nữ</label>
                        <input type="text" class="form-control" value="<?php echo escapeHtml($exam['gioi_tinh'] ?? '-'); ?>" readonly>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Địa chỉ</label>
                        <input type="text" class="form-control" value="<?php echo escapeHtml($exam['dia_chi'] ?? '-'); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Đối tượng</label>
                        <input type="text" class="form-control" value="<?php 
                            if (!empty($exam['doi_tuong_bhyt']) && $exam['doi_tuong_bhyt'] == 1) {
                                echo 'BHYT';
                            } elseif (!empty($exam['doi_tuong_thu_phi']) && $exam['doi_tuong_thu_phi'] == 1) {
                                echo 'Thu phí';
                            } elseif (!empty($exam['doi_tuong_mien']) && $exam['doi_tuong_mien'] == 1) {
                                echo 'Miễn';
                            } else {
                                echo 'Khác';
                            }
                        ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Số thẻ BHYT</label>
                        <input type="text" class="form-control" value="<?php echo escapeHtml($exam['so_the_bhyt'] ?? '-'); ?>" readonly>
                    </div>
                </div>

                <!-- Giờ chỉ định -->
                <div class="mt-3">
                    <label class="form-label fw-bold">Giờ chỉ định:</label>
                    <input type="text" class="form-control" value="<?php 
                        $gioChiDinh = '-';
                        if (!empty($xrayRequest['ngay_cap_nhat'])) {
                            try {
                                $date = new DateTime($xrayRequest['ngay_cap_nhat']);
                                $gioChiDinh = $date->format('H:i:s');
                            } catch (Exception $e) {
                                $gioChiDinh = '-';
                            }
                        } elseif (!empty($xrayRequest['ngay_tao'])) {
                            try {
                                $date = new DateTime($xrayRequest['ngay_tao']);
                                $gioChiDinh = $date->format('H:i:s');
                            } catch (Exception $e) {
                                $gioChiDinh = '-';
                            }
                        }
                        echo escapeHtml($gioChiDinh);
                    ?>" readonly>
                </div>

                <!-- Chuẩn đoán -->
                <div class="mt-3">
                    <label class="form-label fw-bold">Chuẩn đoán:</label>
                    <input type="text" class="form-control" value="<?php echo escapeHtml($xrayRequest['chan_doan_vao_vien'] ?? $exam['chan_doan_vao_vien'] ?? '-'); ?>" readonly>
                </div>

                <!-- Yêu cầu chụp -->
                <div class="mt-3 position-relative">
                    <label class="form-label fw-bold text-center w-100 d-block" style="font-size:16px">YÊU CẦU CHỤP</label>
                    <textarea class="form-control" rows="6" readonly><?php echo escapeHtml($xrayRequest['yeu_cau_chup'] ?? '-'); ?></textarea>
                </div>

                <!-- Chữ ký -->
                <div class="row mt-4">
                    <div class="col-md-6"></div>
                    <div class="col-md-6 text-center">
                        <div class="mb-2 d-flex align-items-center justify-content-center gap-2">
                            <?php
                                $ngayTao = !empty($xrayRequest['ngay_tao']) ? new DateTime($xrayRequest['ngay_tao']) : null;
                            ?>
                            <span>Ngày</span>
                            <input type="number" class="form-control text-center" value="<?php echo $ngayTao ? $ngayTao->format('d') : ''; ?>" readonly style="width:70px">
                            <span>tháng</span>
                            <input type="number" class="form-control text-center" value="<?php echo $ngayTao ? $ngayTao->format('m') : ''; ?>" readonly style="width:70px">
                            <span>năm</span>
                            <input type="number" class="form-control text-center" value="<?php echo $ngayTao ? $ngayTao->format('Y') : ''; ?>" readonly style="width:90px">
                        </div>
                        <div class="fw-bold">BÁC SĨ ĐIỀU TRỊ</div>
                        <div class="mt-2" style="min-height:40px; border-bottom: 1px solid #000; padding: 5px;">
                            <?php echo escapeHtml($xrayRequest['bac_si_kham'] ?? $exam['ten_bac_si'] ?? '-'); ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-5">Chưa có phiếu yêu cầu X-Quang</p>
            <?php endif; ?>
        </div>
    </div>
</div>
