<?php
/**
 * Tab: Kết quả xét nghiệm
 * Template này chỉ cần làm 1 lần, mỗi lần gọi chỉ cần truyền data khác vào
 */

if (!function_exists('lab_extract_numeric')) {
    function lab_extract_numeric($value)
    {
        if ($value === null) {
            return null;
        }
        if (is_numeric($value)) {
            return (float)$value;
        }
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }
        $normalized = str_replace([' ', ','], ['', '.'], $value);
        $normalized = preg_replace('/[^0-9\.-]/', '', $normalized);
        if ($normalized === '' || $normalized === '-' || $normalized === '.') {
            return null;
        }
        if (is_numeric($normalized)) {
            return (float)$normalized;
        }
        if (preg_match('/-?\d+(?:\.\d+)?/', $normalized, $matches)) {
            return (float)$matches[0];
        }
        return null;
    }
}

if (!function_exists('lab_is_out_of_range')) {
    function lab_is_out_of_range($testName, $resultValue)
    {
        static $pdo = null;
        static $cache = [];

        $numeric = lab_extract_numeric($resultValue);
        if ($numeric === null || empty($testName)) {
            return false;
        }

        if ($pdo === null) {
            require_once 'config/database.php';
            $database = new Database();
            $pdo = $database->getConnection();
        }

        if (!isset($cache[$testName])) {
            try {
                $sql = "SELECT chi_so_tu, chi_so_den FROM chi_so_xet_nghiem WHERE xet_nghiem = :name AND chi_so_tu IS NOT NULL AND chi_so_den IS NOT NULL LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':name' => $testName]);
                $cache[$testName] = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            } catch (Exception $e) {
                error_log('lab_is_out_of_range error: ' . $e->getMessage());
                $cache[$testName] = null;
            }
        }

        $range = $cache[$testName];
        if (!$range) {
            return false;
        }

        $lower = (float)$range['chi_so_tu'];
        $upper = (float)$range['chi_so_den'];
        return ($numeric < $lower || $numeric > $upper);
    }
}
?>
<div class="tab-pane fade" id="pane-lab" role="tabpanel">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="fas fa-vial me-2"></i>Kết quả xét nghiệm</h6>
        </div>
        <div class="card-body">
            <?php if ($labResult): ?>
                <?php
                $chiTiet = $labResult['chi_tiet'] ?? [];
                $ngayTao = $labResult['ngay_tao'] ? new DateTime($labResult['ngay_tao']) : null;
                $ngayTaoStr = $ngayTao ? $ngayTao->format('d/m/Y') : '-';
                $gioTaoStr = $ngayTao ? $ngayTao->format('H:i') : '-';
                $maBenhNhan = $exam['ma_benh_nhan'] ?? '0000000';
                ?>
                <div style="font-family: 'Times New Roman', serif; font-size: 12px;">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:60px;object-fit:contain;margin-bottom:10px;">
                        <div class="fw-bold" style="font-size: 18px; color: #000;">PHÒNG KHÁM ĐA KHOA THINHVIET</div>
                        <div class="fw-bold" style="font-size: 14px;">KHOA XÉT NGHIỆM</div>
                        <div class="fw-bold" style="font-size: 16px; color: #dc3545;">KẾT QUẢ XÉT NGHIỆM</div>
                        <div class="d-flex justify-content-center mt-2" style="gap: 10px;">
                            <span>Ngày ĐK: <span><?php echo $ngayTaoStr; ?></span></span>
                            <span>|</span>
                            <span><?php echo $gioTaoStr; ?></span>
                        </div>
                    </div>
                    <hr>

                    <!-- Patient Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="row mb-3">
                                <div class="col-4"><strong style="font-size: 14px;">Mã bệnh nhân:</strong></div>
                                <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;">*<?php echo escapeHtml($maBenhNhan); ?>*</span></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-4"><strong style="font-size: 14px;">Họ và tên:</strong></div>
                                <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;"><?php echo escapeHtml($exam['ho_ten'] ?? '-'); ?></span></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-4"><strong style="font-size: 14px;">Địa chỉ:</strong></div>
                                <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;"><?php echo escapeHtml($exam['dia_chi'] ?? '-'); ?></span></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-4"><strong style="font-size: 14px;">Chẩn đoán sơ bộ:</strong></div>
                                <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;"><?php echo escapeHtml($exam['chan_doan_vao_vien'] ?? '-'); ?></span></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row mb-3">
                                <div class="col-4"><strong style="font-size: 14px;">Tuổi:</strong></div>
                                <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;"><?php echo $exam['tuoi'] ?? '-'; ?></span></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-4"><strong style="font-size: 14px;">Giới tính:</strong></div>
                                <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;"><?php echo $exam['gioi_tinh'] ?? '-'; ?></span></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-4"><strong style="font-size: 14px;">BS yêu cầu:</strong></div>
                                <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;"><?php echo escapeHtml($labResult['bac_si_yeu_cau'] ?? $labResult['bac_si_kham'] ?? $exam['ten_bac_si'] ?? '-'); ?></span></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-4"><strong style="font-size: 14px;">Giờ nhận kết quả:</strong></div>
                                <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;"><?php 
                                    $gioNhanKetQua = '-';
                                    if (!empty($labResult['ngay_cap_nhat'])) {
                                        try {
                                            $date = new DateTime($labResult['ngay_cap_nhat']);
                                            $gioNhanKetQua = $date->format('H:i:s');
                                        } catch (Exception $e) {
                                            $gioNhanKetQua = '-';
                                        }
                                    }
                                    echo escapeHtml($gioNhanKetQua);
                                ?></span></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-4"><strong style="font-size: 14px;">Chất lượng mẫu:</strong></div>
                                <div class="col-8"><span style="border-bottom: 1px solid #000; padding-bottom: 3px; font-size: 14px; min-height: 20px; display: inline-block; width: 100%;"><?php echo escapeHtml($labResult['tinh_trang_mau'] ?? '-'); ?></span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Test Results Section -->
                    <div class="text-center mb-3">
                        <div class="fw-bold" style="font-size: 16px;">BẢNG KẾT QUẢ XÉT NGHIỆM</div>
                    </div>

                    <?php if (!empty($chiTiet)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" style="font-size: 12px;">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 60px;">STT</th>
                                        <th>Xét nghiệm</th>
                                        <th class="text-center">Giá trị tham chiếu</th>
                                        <th class="text-center" style="font-weight: bold;">Kết quả</th>
                                        <th class="text-center">Đơn vị</th>
                                        <th>Máy/QTKT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($chiTiet as $idx => $item): ?>
                                        <?php
                                            $tenXetNghiem = $item['ten_xet_nghiem'] ?? '';
                                            $ketQua = trim($item['ket_qua'] ?? '');
                                            $isOutOfRange = lab_is_out_of_range($tenXetNghiem, $ketQua);
                                            $isPositive = stripos($ketQua, 'dương tính') !== false || stripos($ketQua, 'duong tinh') !== false;
                                            $resultClasses = ['text-center'];
                                            if ($isOutOfRange || $isPositive) {
                                                $resultClasses = ['text-end', 'fw-bold', 'text-danger'];
                                            }
                                        ?>
                                        <tr>
                                            <td class="text-center"><?php echo $item['stt'] ?? ($idx + 1); ?></td>
                                            <td><?php echo escapeHtml($tenXetNghiem ?: '-'); ?></td>
                                            <td class="text-center"><?php echo escapeHtml($item['gia_tri_tham_chieu'] ?? '-'); ?></td>
                                            <td class="<?php echo implode(' ', $resultClasses); ?>"><?php echo escapeHtml($ketQua ?: '-'); ?></td>
                                            <td class="text-center"><?php echo escapeHtml($item['don_vi'] ?? '-'); ?></td>
                                            <td><?php echo escapeHtml($item['may_qtkt'] ?? '-'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">Chưa có kết quả xét nghiệm</p>
                    <?php endif; ?>

                    <!-- Notes -->
                    <div class="mt-3" style="font-size: 11px;">
                        <div>Ghi chú: Kết quả in đậm là kết quả nằm ngoài khoảng tham chiếu.</div>
                        <div>Xét nghiệm đánh dấu (*) là xét nghiệm được thực hiện bởi PXN chuyển gửi.</div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-4">
                        <div class="row">
                            <div class="col-6"></div>
                            <div class="col-6">
                                <div class="text-center" style="font-size: 16px; font-weight: bold; margin-bottom: 20px;">
                                    Ngày <?php echo $ngayTao ? $ngayTao->format('d') : '-'; ?> tháng <?php echo $ngayTao ? $ngayTao->format('m') : '-'; ?> năm <?php echo $ngayTao ? $ngayTao->format('Y') : '-'; ?>
                                </div>
                                <div class="text-center">
                                    <div class="fw-bold" style="font-size: 14px;">BÁC SĨ XÉT NGHIỆM</div>
                                    <div class="mt-2" style="font-size: 14px; min-height: 20px;">
                                        <?php echo escapeHtml($labResult['bac_si_xet_nghiem'] ?? '-'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-5">Chưa có kết quả xét nghiệm</p>
            <?php endif; ?>
        </div>
    </div>
</div>

