<?php
/**
 * Tab: Phiếu yêu cầu siêu âm
 */
?>
<div class="tab-pane fade" id="pane-ultrasound-request" role="tabpanel">
    <div class="card">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0"><i class="fas fa-clipboard me-2"></i>Phiếu yêu cầu siêu âm</h6>
        </div>
        <div class="card-body">
            <?php if ($ultrasoundRequest): ?>
                <div class="text-center mb-3">
                    <div class="fw-bold" style="font-size:18px">PHIẾU YÊU CẦU SIÊU ÂM</div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tên phòng khám</label>
                        <input type="text" class="form-control" value="<?php echo escapeHtml($ultrasoundRequest['phong_kham'] ?? 'Thịnh Việt'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Số điện thoại</label>
                        <input type="text" class="form-control" value="0777871608" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Quận/Huyện</label>
                        <input type="text" class="form-control" value="Gò Vấp" readonly>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Mã bệnh nhân</label>
                        <input type="text" class="form-control" value="<?php echo escapeHtml($ultrasoundRequest['so_ho_so'] ?? $exam['ma_benh_nhan'] ?? '-'); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Họ tên người bệnh</label>
                        <input type="text" class="form-control" value="<?php echo escapeHtml($ultrasoundRequest['ho_ten'] ?? ($exam['ho_ten'] ?? '-')); ?>" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Tuổi</label>
                        <input type="text" class="form-control" value="<?php echo escapeHtml($exam['tuoi'] ?? '-'); ?>" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Nam/Nữ</label>
                        <input type="text" class="form-control" value="<?php echo escapeHtml($ultrasoundRequest['gioi_tinh'] ?? $exam['gioi_tinh'] ?? '-'); ?>" readonly>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Địa chỉ</label>
                        <input type="text" class="form-control" value="<?php echo escapeHtml($exam['dia_chi'] ?? '-'); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Đối tượng</label>
                        <input type="text" class="form-control" value="<?php echo escapeHtml($ultrasoundRequest['doi_tuong'] ?? '-'); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Số thẻ BHYT</label>
                        <input type="text" class="form-control" value="<?php echo escapeHtml($ultrasoundRequest['so_the_bhyt'] ?? $exam['so_the_bhyt'] ?? '-'); ?>" readonly>
                    </div>
                </div>

                <!-- Chẩn đoán -->
                <div class="mt-3">
                    <label class="form-label fw-bold">Chẩn đoán:</label>
                    <input type="text" class="form-control" value="<?php echo escapeHtml($ultrasoundRequest['chan_doan'] ?? $exam['chan_doan_vao_vien'] ?? '-'); ?>" readonly>
                </div>

                <!-- Yêu cầu siêu âm -->
                <div class="mt-3 position-relative">
                    <label class="form-label fw-bold text-center w-100 d-block" style="font-size:16px">YÊU CẦU SIÊU ÂM</label>
                    <textarea class="form-control" rows="6" readonly><?php echo escapeHtml($ultrasoundRequest['yeu_cau'] ?? '-'); ?></textarea>
                </div>

                <!-- Chữ ký bác sĩ -->
                <div class="row mt-4">
                    <div class="col-md-6"></div>
                    <div class="col-md-6 text-center">
                        <div class="mb-2 d-flex align-items-center justify-content-center gap-2">
                            <?php
                                $thoiGianYeuCau = !empty($ultrasoundRequest['thoi_gian_yeu_cau']) ? new DateTime($ultrasoundRequest['thoi_gian_yeu_cau']) : null;
                            ?>
                            <span>Ngày</span>
                            <input type="number" class="form-control text-center" value="<?php echo $thoiGianYeuCau ? $thoiGianYeuCau->format('d') : ''; ?>" readonly style="width:70px">
                            <span>tháng</span>
                            <input type="number" class="form-control text-center" value="<?php echo $thoiGianYeuCau ? $thoiGianYeuCau->format('m') : ''; ?>" readonly style="width:70px">
                            <span>năm</span>
                            <input type="number" class="form-control text-center" value="<?php echo $thoiGianYeuCau ? $thoiGianYeuCau->format('Y') : ''; ?>" readonly style="width:90px">
                        </div>
                        <div class="fw-bold">BÁC SĨ ĐIỀU TRỊ</div>
                        <div class="mt-2" style="min-height:40px; border-bottom: 1px solid #000; padding: 5px;">
                            <?php echo escapeHtml($ultrasoundRequest['bac_si_kham'] ?? $exam['ten_bac_si'] ?? '-'); ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-5">Chưa có phiếu yêu cầu siêu âm</p>
            <?php endif; ?>
        </div>
    </div>
</div>
