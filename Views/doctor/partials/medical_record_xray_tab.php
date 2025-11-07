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
                $ngayCapNhat = $xrayResult['ngay_cap_nhat'] ? new DateTime($xrayResult['ngay_cap_nhat']) : null;
                $ngayCapNhatStr = $ngayCapNhat ? $ngayCapNhat->format('d/m/Y') : '-';
                $gioCapNhatStr = $ngayCapNhat ? $ngayCapNhat->format('H:i') : '-';
                $today = new DateTime();
                $todayStr = "Ngày " . $today->format('d') . " tháng " . $today->format('m') . " năm " . $today->format('Y');
                ?>
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
                        <div class="border border-dark p-2">
                            <div class="text-center">
                                <div class="fw-bold">PHÒNG KHÁM ĐA KHOA THINHVIET</div>
                                <div class="fw-bold">KHOA CHUẨN ĐOÁN HÌNH ẢNH</div>
                                <div class="text-muted">Địa chỉ: Gò Vấp - Điện thoại: 0777871608</div>
                            </div>
                            <hr>
                            <div class="row g-2">
                                <div class="col-md-6">Họ và tên: <strong><?php echo escapeHtml($exam['ho_ten'] ?? '-'); ?></strong></div>
                                <div class="col-md-6">Giới tính: <strong><?php echo $exam['gioi_tinh'] ?? '-'; ?></strong></div>
                                <div class="col-md-6">Năm sinh: <strong><?php 
                                    $namSinh = '-';
                                    if (!empty($exam['ngay_sinh_benh_nhan'])) {
                                        try {
                                            $namSinh = (new DateTime($exam['ngay_sinh_benh_nhan']))->format('Y');
                                        } catch (Exception $e) {
                                            // Nếu parse lỗi, thử lấy từ nam_sinh
                                            $namSinh = $exam['nam_sinh'] ?? '-';
                                        }
                                    } elseif (!empty($exam['nam_sinh'])) {
                                        $namSinh = $exam['nam_sinh'];
                                    }
                                    echo $namSinh;
                                ?></strong></div>
                                <div class="col-md-6">Số phiếu chỉ định: <strong><?php echo $xrayResult['id_phieu_chup_xquang'] ?? '-'; ?></strong></div>
                                <div class="col-md-12">Địa chỉ: <strong><?php echo escapeHtml($exam['dia_chi'] ?? '-'); ?></strong></div>
                                <div class="col-md-6">Ngày chỉ định: <strong><?php echo $ngayCapNhatStr; ?></strong></div>
                                <div class="col-md-6">Giờ chỉ định: <strong><?php echo $gioCapNhatStr; ?></strong></div>
                            </div>
                            <hr>
                            <div>Chẩn đoán: <strong><?php echo escapeHtml($exam['chan_doan_vao_vien'] ?? '-'); ?></strong></div>
                            <div>Bác sĩ chỉ định: <strong><?php echo escapeHtml($exam['ten_bac_si'] ?? '-'); ?></strong></div>
                            <div class="mt-2">Nội dung: <strong><?php echo escapeHtml($xrayResult['noi_dung'] ?? '-'); ?></strong></div>
                            <div class="mt-3 fw-bold">KẾT QUẢ</div>
                            <div class="border p-2" style="min-height:80px"><?php echo escapeHtml($xrayResult['ket_qua'] ?? '-'); ?></div>
                            <div class="mt-3 fw-bold">KẾT LUẬN</div>
                            <div class="border p-2" style="min-height:80px"><?php echo escapeHtml($xrayResult['ket_luan'] ?? '-'); ?></div>
                            <div class="text-end mt-3">
                                <em><?php echo $todayStr; ?></em><br>
                                <strong>Bác sĩ X Quang</strong>
                                <div style="min-height:40px; border-top: 1px solid #000; padding-top: 5px;"><?php echo escapeHtml($xrayResult['bac_si_xquang'] ?? '-'); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($images)): ?>
                    <div class="tab-pane fade" id="xr_tabpane_images" role="tabpanel">
                        <div class="text-muted small mb-2">Danh sách hình ảnh X-Quang đã lưu</div>
                        <div class="row">
                            <?php foreach ($images as $img): ?>
                                <div class="col-md-3 mb-2">
                                    <img src="<?php echo escapeHtml($img['file_path'] ?? ''); ?>" class="img-fluid rounded" style="cursor: pointer;" onclick="zoomImage('<?php echo escapeHtml($img['file_path'] ?? ''); ?>')" onerror="this.src='assets/img/placeholder.jpg'">
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

