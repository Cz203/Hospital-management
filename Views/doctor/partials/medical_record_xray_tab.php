<?php
/**
 * Tab: Kết quả X-Quang
 * Template này chỉ cần làm 1 lần, mỗi lần gọi chỉ cần truyền data khác vào
 */
?>
<div class="tab-pane fade" id="pane-xray" role="tabpanel">
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h6 class="mb-0"><i class="fas fa-x-ray me-2"></i>Kết quả X-Quang</h6>
        </div>
        <div class="card-body">
            <?php if ($xrayResult): ?>
                <?php
                $images = $xrayResult['hinh_anh'] ?? [];
                
                // Format dates
                $ngayChiDinh = null;
                $gioChiDinh = '-';
                if ($xrayRequest && !empty($xrayRequest['ngay_cap_nhat'])) {
                    try {
                        $ngayChiDinh = new DateTime($xrayRequest['ngay_cap_nhat']);
                        $gioChiDinh = $ngayChiDinh->format('H:i:s');
                    } catch (Exception $e) {
                        $ngayChiDinh = null;
                    }
                } elseif ($xrayRequest && !empty($xrayRequest['ngay_tao'])) {
                    try {
                        $ngayChiDinh = new DateTime($xrayRequest['ngay_tao']);
                        $gioChiDinh = $ngayChiDinh->format('H:i:s');
                    } catch (Exception $e) {
                        $ngayChiDinh = null;
                    }
                }
                $ngayChiDinhStr = $ngayChiDinh ? $ngayChiDinh->format('d/m/Y') : '-';
                
                // Giờ nhận kết quả
                $gioNhanKetQua = '-';
                $ngayNhanKetQua = null;
                if (!empty($xrayResult['ngay_doc'])) {
                    try {
                        $ngayNhanKetQua = new DateTime($xrayResult['ngay_doc']);
                        $gioNhanKetQua = $ngayNhanKetQua->format('H:i:s');
                    } catch (Exception $e) {
                        $ngayNhanKetQua = null;
                    }
                } elseif (!empty($xrayResult['ngay_cap_nhat'])) {
                    try {
                        $ngayNhanKetQua = new DateTime($xrayResult['ngay_cap_nhat']);
                        $gioNhanKetQua = $ngayNhanKetQua->format('H:i:s');
                    } catch (Exception $e) {
                        $ngayNhanKetQua = null;
                    }
                }
                
                // Năm sinh
                $namSinh = '-';
                if (!empty($exam['ngay_sinh_benh_nhan'])) {
                    try {
                        $namSinh = (new DateTime($exam['ngay_sinh_benh_nhan']))->format('Y');
                    } catch (Exception $e) {
                        $namSinh = $exam['nam_sinh'] ?? '-';
                    }
                } elseif (!empty($exam['nam_sinh'])) {
                    $namSinh = $exam['nam_sinh'];
                }
                
                // Signature date
                $signatureDate = $ngayNhanKetQua ?: new DateTime();
                ?>
                <style>
                .xray-report {
                    font-family: "Times New Roman", serif;
                    padding: 18px;
                }

                .xray-report .title {
                    font-weight: bold;
                    text-transform: uppercase;
                    text-align: center;
                    letter-spacing: .5px;
                    font-size: 18px;
                    margin-bottom: 6px;
                    color: red;
                }

                .xray-report .hr {
                    border-top: 2px solid #000;
                    margin: 10px 0;
                }

                .xray-report .row-line {
                    display: flex;
                    gap: 8px;
                    margin-bottom: 6px;
                    font-size: 15px;
                }

                .xray-report .label {
                    min-width: 150px;
                    font-weight: bold;
                }

                .xray-report .dots {
                    flex: 0 0 auto;
                }

                .xray-report .value {
                    flex: 1;
                    border-bottom: 1px dotted #333;
                    min-height: 20px;
                }

                .xray-report .section {
                    margin-top: 10px;
                    margin-bottom: 6px;
                    font-weight: bold;
                    text-transform: uppercase;
                    color: blue;
                }

                .xray-report .result-content {
                    border: 1px solid #333;
                    padding: 10px;
                    min-height: 100px;
                    white-space: pre-wrap;
                }

                .xray-report .conclusion-content {
                    border: 1px solid #333;
                    padding: 10px;
                    min-height: 60px;
                    white-space: pre-wrap;
                }
                </style>

                <ul class="nav nav-tabs mb-2" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="xr_tab_info" data-bs-toggle="tab" data-bs-target="#xr_tabpane_info" type="button" role="tab">Thông tin</button>
                    </li>
                    <?php if (!empty($images)): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="xr_tab_images" data-bs-toggle="tab" data-bs-target="#xr_tabpane_images" type="button" role="tab">Hình Ảnh X-Quang</button>
                    </li>
                    <?php endif; ?>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="xr_tabpane_info" role="tabpanel">
                        <div class="xray-report">
                            <div class="text-center mb-3">
                                <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:60px;object-fit:contain;margin-bottom:10px;">
                                <div class="fw-bold" style="font-size: 18px; color: #333;">PHÒNG
                                    KHÁM ĐA KHOA THINHVIET</div>
                                <div class="fw-bold" style="font-size: 16px; color: #666;">KHOA CHUẨN
                                    ĐOÁN HÌNH ẢNH</div>
                            </div>
                            <div class="title">KẾT QUẢ X-QUANG</div>

                            <div class="text-center mb-2">
                                <div class="fw-bold">Địa chỉ: Gò Vấp - Điện thoại: 0777871608</div>
                            </div>

                            <div class="row-line">
                                <div class="label">Họ và tên</div>
                                <div class="dots">:</div>
                                <div class="value"><?php echo escapeHtml($exam['ho_ten'] ?? '-'); ?></div>
                            </div>

                            <div class="row-line">
                                <div class="label">Năm sinh</div>
                                <div class="dots">:</div>
                                <div class="value"><?php echo escapeHtml($namSinh); ?></div>
                                <div class="label" style="min-width:90px; margin-left: 20px;">Giới tính</div>
                                <div class="dots">:</div>
                                <div class="value"><?php echo escapeHtml($exam['gioi_tinh'] ?? '-'); ?></div>
                            </div>

                            <div class="row-line">
                                <div class="label">Địa chỉ</div>
                                <div class="dots">:</div>
                                <div class="value"><?php echo escapeHtml($exam['dia_chi'] ?? '-'); ?></div>
                            </div>

                            <div class="row-line">
                                <div class="label">Khoa chỉ định</div>
                                <div class="dots">:</div>
                                <div class="value">KHOA CHUẨN ĐOÁN HÌNH ẢNH</div>
                                <div class="label" style="min-width:120px; margin-left: 20px;">Số phiếu chỉ định</div>
                                <div class="dots">:</div>
                                <div class="value"><?php echo escapeHtml($xrayResult['id_phieu_chup_xquang'] ?? $xrayRequest['id'] ?? '-'); ?></div>
                            </div>

                            <div class="row-line">
                                <div class="label">Ngày chỉ định</div>
                                <div class="dots">:</div>
                                <div class="value"><?php echo escapeHtml($ngayChiDinhStr); ?></div>
                                <div class="label" style="min-width:120px; margin-left: 20px;">Giờ chỉ định</div>
                                <div class="dots">:</div>
                                <div class="value"><?php echo escapeHtml($gioChiDinh); ?></div>
                            </div>

                            <div class="row-line">
                                <div class="label">Giờ nhận kết quả</div>
                                <div class="dots">:</div>
                                <div class="value"><?php echo escapeHtml($gioNhanKetQua); ?></div>
                            </div>

                            <div class="hr"></div>

                            <div class="row-line">
                                <div class="label">Chẩn đoán</div>
                                <div class="dots">:</div>
                                <div class="value"><?php echo escapeHtml($exam['chan_doan_vao_vien'] ?? '-'); ?></div>
                            </div>

                            <div class="row-line">
                                <div class="label">Bác sĩ chỉ định</div>
                                <div class="dots">:</div>
                                <div class="value"><?php echo escapeHtml($exam['ten_bac_si'] ?? '-'); ?></div>
                            </div>

                            <div class="row-line">
                                <div class="label">Nội dung</div>
                                <div class="dots">:</div>
                                <div class="value"><?php 
                                    $noiDung = '-';
                                    if (!empty($xrayRequest['yeu_cau_chup'])) {
                                        $noiDung = 'Chụp X-Quang ' . $xrayRequest['yeu_cau_chup'];
                                    }
                                    echo escapeHtml($noiDung);
                                ?></div>
                            </div>

                            <div class="hr"></div>

                            <div class="section">KẾT QUẢ:</div>
                            <div class="result-content"><?php echo escapeHtml($xrayResult['noi_dung'] ?? '-'); ?></div>

                            <div class="section">KẾT LUẬN:</div>
                            <div class="conclusion-content"><?php echo escapeHtml($xrayResult['ket_luan'] ?? '-'); ?></div>

                            <div class="hr"></div>

                            <div style="margin-top: 30px; text-align: right;">
                                <div class="mb-3">
                                    <span>Ngày</span>
                                    <input type="text" class="form-control d-inline-block"
                                        value="<?php echo $signatureDate->format('d'); ?>" readonly style="width:60px; margin: 0 5px;">
                                    <span>tháng</span>
                                    <input type="text" class="form-control d-inline-block"
                                        value="<?php echo $signatureDate->format('m'); ?>" readonly style="width:60px; margin: 0 5px;">
                                    <span>năm</span>
                                    <input type="text" class="form-control d-inline-block"
                                        value="<?php echo $signatureDate->format('Y'); ?>" readonly style="width:80px; margin: 0 5px;">
                                </div>
                                <div style="margin-right: 20px;">
                                    <div class="fw-bold">BÁC SĨ X QUANG</div>
                                    <div class="mt-2" style="margin-left: -20px;"><?php echo escapeHtml($xrayResult['bac_si_xquang'] ?? '-'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($images)): ?>
                    <div class="tab-pane fade" id="xr_tabpane_images" role="tabpanel">
                        <div class="text-muted small mb-2">Danh sách hình ảnh X-Quang đã lưu</div>
                        <div class="row">
                            <?php foreach ($images as $img): ?>
                                <?php
                                $imgUrl = $img['file_path'] ?? '';
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
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-5">Chưa có kết quả X-Quang</p>
            <?php endif; ?>
        </div>
    </div>
</div>

