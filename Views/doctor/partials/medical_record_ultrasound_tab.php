<?php
/**
 * Tab: Kết quả siêu âm
 * Template này chỉ cần làm 1 lần, mỗi lần gọi chỉ cần truyền data khác vào
 */
?>
<div class="tab-pane fade" id="pane-ultrasound" role="tabpanel">
    <div class="card">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0"><i class="fas fa-wave-square me-2"></i>Kết quả siêu âm</h6>
        </div>
        <div class="card-body">
            <?php if ($ultrasoundResult): ?>
                <?php
                $images = $ultrasoundResult['hinh_anh'] ?? [];
                $ngayTao = $ultrasoundResult['ngay_tao'] ? new DateTime($ultrasoundResult['ngay_tao']) : null;
                $ngayTaoStr = $ngayTao ? $ngayTao->format('d/m/Y') : '-';
                $gioTaoStr = $ngayTao ? $ngayTao->format('H:i') : '-';
                $maBenhNhan = $exam['ma_benh_nhan'] ?? '0000000';
                ?>
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
                    align-items: center;
                    margin-bottom: 8px;
                }
                .row-line .label {
                    min-width: 120px;
                    font-weight: bold;
                }
                .row-line .dots {
                    margin: 0 5px;
                }
                .row-line .value {
                    flex: 1;
                    border-bottom: 1px solid #000;
                    padding-bottom: 3px;
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
                <div class="report">
                    <div class="text-center mb-3">
                        <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:60px;object-fit:contain;margin-bottom:10px;">
                        <div class="fw-bold" style="font-size: 18px; color: #333;">PHÒNG KHÁM ĐA KHOA THINHVIET</div>
                        <div class="fw-bold" style="font-size: 16px; color: #666;">KHOA CHẨN ĐOÁN HÌNH ẢNH</div>
                    </div>
                    <div class="title">KẾT QUẢ SIÊU ÂM</div>

                    <div class="text-center mb-2">
                        <div class="fw-bold">Máy: Medison Sonoace X6</div>
                    </div>

                    <div class="row-line">
                        <div class="label">ID:</div>
                        <div class="dots">:</div>
                        <div class="value">*<?php echo escapeHtml($maBenhNhan); ?>*</div>
                        <div class="label" style="margin-left: 20px;">Ngày ĐK:</div>
                        <div class="dots">:</div>
                        <div class="value"><?php echo $ngayTaoStr; ?></div>
                        <div class="dots">-</div>
                        <div class="value"><?php echo $gioTaoStr; ?></div>
                    </div>

                    <div class="hr"></div>

                    <div class="row-line">
                        <div class="label">Họ tên:</div>
                        <div class="dots">:</div>
                        <div class="value"><?php echo escapeHtml($exam['ho_ten'] ?? '-'); ?></div>
                    </div>

                    <div class="row-line">
                        <div class="label">Tuổi:</div>
                        <div class="dots">:</div>
                        <div class="value"><?php echo $exam['tuoi'] ?? '-'; ?></div>
                        <div class="label" style="margin-left: 20px;">Giới:</div>
                        <div class="dots">:</div>
                        <div class="value"><?php echo $exam['gioi_tinh'] ?? '-'; ?></div>
                    </div>

                    <div class="row-line">
                        <div class="label">Địa chỉ:</div>
                        <div class="dots">:</div>
                        <div class="value"><?php echo escapeHtml($exam['dia_chi'] ?? '-'); ?></div>
                    </div>

                    <div class="row-line">
                        <div class="label">Chẩn đoán sơ bộ:</div>
                        <div class="dots">:</div>
                        <div class="value"><?php echo escapeHtml($exam['chan_doan_vao_vien'] ?? '-'); ?></div>
                    </div>

                    <div class="row-line">
                        <div class="label">Bác sĩ chỉ định:</div>
                        <div class="dots">:</div>
                        <div class="value"><?php echo escapeHtml($exam['ten_bac_si'] ?? '-'); ?></div>
                    </div>

                    <div class="row-line">
                        <div class="label">Giờ nhận kết quả:</div>
                        <div class="dots">:</div>
                        <div class="value"><?php 
                            $gioNhanKetQua = '-';
                            if (!empty($ultrasoundResult['ngay_cap_nhat'])) {
                                try {
                                    $date = new DateTime($ultrasoundResult['ngay_cap_nhat']);
                                    $gioNhanKetQua = $date->format('H:i:s');
                                } catch (Exception $e) {
                                    $gioNhanKetQua = '-';
                                }
                            }
                            echo escapeHtml($gioNhanKetQua);
                        ?></div>
                    </div>

                    <div class="hr"></div>

                    <div class="row-line">
                        <div class="label">Vùng khảo sát:</div>
                        <div class="dots">:</div>
                        <div class="value"><?php echo escapeHtml($ultrasoundResult['vung_khao_sat'] ?? '-'); ?></div>
                    </div>

                    <div class="hr"></div>

                    <div class="row-line">
                        <div class="label">KẾT QUẢ:</div>
                    </div>
                    <div class="result-content"><?php echo escapeHtml($ultrasoundResult['ket_qua_khao_sat'] ?? '-'); ?></div>

                    <div class="row-line" style="margin-top: 15px;">
                        <div class="label">KẾT LUẬN:</div>
                    </div>
                    <div class="conclusion-content"><?php echo escapeHtml($ultrasoundResult['ket_luan'] ?? '-'); ?></div>

                    <?php if (!empty($images)): ?>
                        <div class="mt-3">
                            <div class="fw-bold mb-2">Hình ảnh siêu âm:</div>
                            <div class="row">
                                <?php foreach ($images as $img): ?>
                                    <?php
                                    $imgUrl = $img['duong_dan'] ?? '';
                                    // Normalize URL: thêm ./ nếu chưa có prefix
                                    if ($imgUrl && !preg_match('/^(https?:\/\/|\.\/|\/)/', $imgUrl)) {
                                        $imgUrl = './' . $imgUrl;
                                    }
                                    ?>
                                    <div class="col-md-3 mb-2">
                                        <img src="<?php echo escapeHtml($imgUrl); ?>" class="img-fluid rounded" style="cursor: pointer;" onclick="zoomImage('<?php echo escapeHtml($imgUrl); ?>')" onerror="this.src='assets/img/placeholder.jpg'">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="text-end mt-3">
                        <div style="font-size: 14px;">Ngày <?php echo $ngayTao ? $ngayTao->format('d') : '-'; ?> tháng <?php echo $ngayTao ? $ngayTao->format('m') : '-'; ?> năm <?php echo $ngayTao ? $ngayTao->format('Y') : '-'; ?></div>
                        <div class="fw-bold mt-2" style="font-size: 14px;">BÁC SĨ SIÊU ÂM</div>
                        <div class="mt-2" style="font-size: 14px; min-height: 30px; border-top: 1px solid #000; padding-top: 5px;"><?php echo escapeHtml($ultrasoundResult['bac_si_sieu_am'] ?? '-'); ?></div>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-5">Chưa có kết quả siêu âm</p>
            <?php endif; ?>
        </div>
    </div>
</div>

