<?php
/**
 * Tab: Biên lai viện phí
 * Template này chỉ cần làm 1 lần, mỗi lần gọi chỉ cần truyền data khác vào
 */
?>
<div class="tab-pane fade" id="pane-receipt" role="tabpanel">
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Biên lai viện phí</h6>
        </div>
        <div class="card-body">
            <?php if ($receipt): ?>
                <style>
                #pane-receipt .receipt-container {
                    font-family: 'Times New Roman', serif;
                    font-size: 14px;
                    line-height: 1.4;
                    background: #fff;
                    border: 1px solid #ddd;
                    padding: 20px;
                    border-radius: 6px;
                }
                #pane-receipt .receipt-header {
                    text-align: center;
                    margin-bottom: 30px;
                    position: relative;
                }
                #pane-receipt .receipt-clinic-name {
                    font-size: 20px;
                    font-weight: bold;
                    text-transform: uppercase;
                    margin-bottom: 10px;
                }
                #pane-receipt .receipt-main-title {
                    font-size: 18px;
                    font-weight: bold;
                    margin-bottom: 5px;
                }
                #pane-receipt .receipt-subtitle {
                    font-size: 14px;
                    color: #666;
                    margin-bottom: 15px;
                }
                #pane-receipt .receipt-stt {
                    position: absolute;
                    right: 0;
                    font-weight: bold;
                    text-align: right;
                }
                #pane-receipt .receipt-stt + .receipt-stt {
                    top: 28px;
                }
                #pane-receipt .patient-info {
                    margin-bottom: 20px;
                }
                #pane-receipt .info-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 8px;
                    gap: 20px;
                    flex-wrap: wrap;
                }
                #pane-receipt .info-item {
                    flex: 1;
                    min-width: 200px;
                    display: flex;
                    gap: 6px;
                }
                #pane-receipt .info-item .label {
                    font-weight: bold;
                    white-space: nowrap;
                }
                #pane-receipt .receipt-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 15px;
                }
                #pane-receipt .receipt-table th,
                #pane-receipt .receipt-table td {
                    border: 1px solid #000;
                    padding: 8px;
                    font-size: 12px;
                    text-align: center;
                }
                #pane-receipt .receipt-table th {
                    background: #f5f5f5;
                    font-weight: bold;
                }
                #pane-receipt .receipt-table .text-start {
                    text-align: left;
                }
                #pane-receipt .receipt-table .text-end {
                    text-align: right;
                }
                #pane-receipt .payment-info {
                    margin-bottom: 10px;
                    text-align: right;
                }
                #pane-receipt .payment-row {
                    display: flex;
                    justify-content: flex-end;
                    gap: 10px;
                    margin-bottom: 5px;
                }
                #pane-receipt .signature-section {
                    background-color: #f5f5f5;
                    border-radius: 8px;
                    padding: 20px;
                    max-width: 280px;
                    margin-left: auto;
                    text-align: center;
                }
                #pane-receipt .signature-date {
                    margin-bottom: 5px;
                }
                #pane-receipt .signature-title {
                    font-weight: bold;
                    margin-bottom: 5px;
                }
                #pane-receipt .signature-instruction {
                    font-style: italic;
                    text-decoration: underline;
                    margin-bottom: 8px;
                }
                </style>
                <?php
                    if (!function_exists('receiptNumberToWords')) {
                        function receiptNumberToWords($num) {
                            $ones = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
                            $tens = ['', '', 'hai mươi', 'ba mươi', 'bốn mươi', 'năm mươi', 'sáu mươi', 'bảy mươi', 'tám mươi', 'chín mươi'];

                            if ($num == 0) return 'không';
                            if ($num < 10) return $ones[$num];
                            if ($num < 20) {
                                $special = [10 => 'mười', 11 => 'mười một', 12 => 'mười hai', 13 => 'mười ba', 14 => 'mười bốn', 15 => 'mười lăm', 16 => 'mười sáu', 17 => 'mười bảy', 18 => 'mười tám', 19 => 'mười chín'];
                                return $special[$num];
                            }
                            if ($num < 100) {
                                $ten = floor($num / 10);
                                $one = $num % 10;
                                $result = $tens[$ten];
                                if ($one > 0) {
                                    if ($one == 1) {
                                        $result .= ' mốt';
                                    } elseif ($one == 4) {
                                        $result .= ' tư';
                                    } elseif ($one == 5) {
                                        $result .= ' lăm';
                                    } else {
                                        $result .= ' ' . $ones[$one];
                                    }
                                }
                                return $result;
                            }
                            if ($num < 1000) {
                                $hundreds = floor($num / 100);
                                $remainder = $num % 100;
                                $result = $ones[$hundreds] . ' trăm';
                                if ($remainder > 0) {
                                    if ($remainder < 10) {
                                        $result .= ' lẻ ' . receiptNumberToWords($remainder);
                                    } else {
                                        $result .= ' ' . receiptNumberToWords($remainder);
                                    }
                                }
                                return $result;
                            }
                            if ($num < 1000000) {
                                $thousands = floor($num / 1000);
                                $remainder = $num % 1000;
                                $result = receiptNumberToWords($thousands) . ' ngàn';
                                if ($remainder > 0) {
                                    if ($remainder < 100) {
                                        $result .= ' lẻ ' . receiptNumberToWords($remainder);
                                    } else {
                                        $result .= ' ' . receiptNumberToWords($remainder);
                                    }
                                }
                                return $result;
                            }
                            if ($num < 1000000000) {
                                $millions = floor($num / 1000000);
                                $remainder = $num % 1000000;
                                $result = receiptNumberToWords($millions) . ' triệu';
                                if ($remainder > 0) {
                                    if ($remainder < 100000) {
                                        $result .= ' ' . receiptNumberToWords($remainder);
                                    } else {
                                        $result .= ' ' . receiptNumberToWords($remainder);
                                    }
                                }
                                return $result;
                            }
                            return (string)$num;
                        }
                    }

                ?>
                <div class="receipt-container">
                    <!-- Header -->
                    <div class="receipt-header">
                        <div class="text-center mb-3">
                            <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:60px;object-fit:contain;margin-bottom:10px;">
                        </div>
                        <div class="receipt-clinic-name">PHÒNG KHÁM ĐA KHOA THINHVIET</div>
                        <div class="receipt-main-title">BIÊN LAI VIỆN PHÍ</div>
                        <div class="receipt-subtitle">Viện phí</div>
                        <div class="receipt-stt" style="top: 0;">
                            <span class="label">Mã BN:</span>
                            <span class="value"><?php echo escapeHtml($receipt['ma_benh_nhan'] ?? '-'); ?></span>
                        </div>
                        <div class="receipt-stt" style="top: 28px;">
                            <span class="label">Số HD:</span>
                            <span class="value"><?php echo escapeHtml($receipt['ma_bien_lai'] ?? '-'); ?></span>
                        </div>
                    </div>

                    <!-- Patient information -->
                    <div class="patient-info">
                        <div class="info-row">
                            <div class="info-item">
                                <span class="label">Họ và Tên:</span>
                                <span class="value"><?php echo escapeHtml($receipt['ho_ten'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Tuổi:</span>
                                <span class="value"><?php echo escapeHtml($receipt['tuoi'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Giới tính:</span>
                                <span class="value"><?php echo escapeHtml($receipt['gioi_tinh'] ?? '-'); ?></span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-item">
                                <span class="label">Địa chỉ:</span>
                                <span class="value"><?php echo escapeHtml($receipt['dia_chi'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Mã số BHYT (nếu có):</span>
                                <span class="value"><?php echo !empty($receipt['so_the_bhyt']) ? escapeHtml($receipt['so_the_bhyt']) : 'Thu phí'; ?></span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-item" style="flex: 1;">
                                <span class="label">Nội Dung Thu:</span>
                                <span class="value fw-bold">Đề nghị thanh toán</span>
                            </div>
                        </div>
                    </div>

                    <!-- Receipt table -->
                    <table class="receipt-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Nội dung</th>
                                <th>Số lượng</th>
                                <th>Đơn giá (đồng)</th>
                                <th>Thành tiền (đồng)</th>
                                <th>Quỹ BHYT (đồng)</th>
                                <th>Người Bệnh (đồng)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                // Khởi tạo biến tổng (cần khởi tạo trước để dùng trong phần footer)
                                $totalAmount = 0;
                                $totalBhyt = 0;
                                $totalPatient = 0;
                            ?>
                            <?php if (!empty($receiptDetails)): ?>
                                <?php 
                                    // Map loai_dich_vu to section groups và thứ tự sắp xếp
                                    // Các loại dịch vụ cận lâm sàng (Xet nghiem, Sieu am, X-Quang/Xray) đều thuộc cùng một group
                                    $sectionGroups = [
                                        'Kham benh' => ['group' => 'kham_benh', 'order' => 1],
                                        'Xet nghiem' => ['group' => 'can_lam_sang', 'order' => 2],
                                        'Sieu am' => ['group' => 'can_lam_sang', 'order' => 2],
                                        'X-Quang' => ['group' => 'can_lam_sang', 'order' => 2], // Database enum value
                                        'Xray' => ['group' => 'can_lam_sang', 'order' => 2], // Alternative value
                                        'Thuoc' => ['group' => 'thuoc', 'order' => 3]
                                    ];
                                    
                                    $sectionNames = [
                                        'kham_benh' => 'Khám bệnh lâm sàng',
                                        'can_lam_sang' => 'Khám bệnh cận lâm sàng',
                                        'thuoc' => 'Thuốc điều trị'
                                    ];
                                    
                                    // Sắp xếp lại các dịch vụ theo section group và thứ tự
                                    usort($receiptDetails, function($a, $b) use ($sectionGroups) {
                                        $loaiA = $a['loai_dich_vu'] ?? '';
                                        $loaiB = $b['loai_dich_vu'] ?? '';
                                        
                                        $groupA = $sectionGroups[$loaiA] ?? ['group' => 'other', 'order' => 999];
                                        $groupB = $sectionGroups[$loaiB] ?? ['group' => 'other', 'order' => 999];
                                        
                                        // Sắp xếp theo order trước
                                        if ($groupA['order'] !== $groupB['order']) {
                                            return $groupA['order'] - $groupB['order'];
                                        }
                                        
                                        // Nếu cùng group, giữ nguyên thứ tự (theo id)
                                        return ($a['id'] ?? 0) - ($b['id'] ?? 0);
                                    });
                                    
                                    $stt = 1;
                                    $currentSectionGroup = '';
                                ?>
                                <?php foreach ($receiptDetails as $detail): ?>
                                    <?php
                                        $loaiDichVu = trim($detail['loai_dich_vu'] ?? ''); // Trim để tránh lỗi do khoảng trắng
                                        $sectionInfo = $sectionGroups[$loaiDichVu] ?? ['group' => 'other', 'order' => 999];
                                        $sectionGroup = $sectionInfo['group'];
                                        
                                        // Chỉ hiển thị header khi section group thay đổi
                                        if ($sectionGroup !== $currentSectionGroup) {
                                            // Chỉ hiển thị header nếu là section hợp lệ (không phải 'other')
                                            if (isset($sectionNames[$sectionGroup])) {
                                                $currentSectionGroup = $sectionGroup;
                                                echo '<tr><td colspan="7" class="text-start"><strong>' . $sectionNames[$sectionGroup] . '</strong></td></tr>';
                                            } elseif ($sectionGroup === 'other') {
                                                // Nếu là 'other', vẫn cập nhật currentSectionGroup để tránh hiển thị lại
                                                $currentSectionGroup = $sectionGroup;
                                            }
                                        }
                                        
                                        $totalAmount += (float)($detail['thanh_tien'] ?? 0);
                                        $totalBhyt += (float)($detail['quy_bhyt'] ?? 0);
                                        $totalPatient += (float)($detail['nguoi_benh'] ?? 0);
                                    ?>
                                    <tr>
                                        <td><?php echo $stt++; ?></td>
                                        <td class="text-start"><?php echo escapeHtml($detail['ten_dich_vu'] ?? '-'); ?></td>
                                        <td><?php echo $detail['so_luong'] ?? 0; ?></td>
                                        <td class="text-end"><?php echo number_format((float)($detail['don_gia'] ?? 0)); ?></td>
                                        <td class="text-end"><?php echo number_format((float)($detail['thanh_tien'] ?? 0)); ?></td>
                                        <td class="text-end"><?php echo number_format((float)($detail['quy_bhyt'] ?? 0)); ?></td>
                                        <td class="text-end"><?php echo number_format((float)($detail['nguoi_benh'] ?? 0)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="fw-bold">
                                    <td colspan="4" class="text-start">Tổng Cộng:</td>
                                    <td class="text-end"><?php echo number_format($totalAmount); ?></td>
                                    <td class="text-end"><?php echo number_format($totalBhyt); ?></td>
                                    <td class="text-end"><?php echo number_format($totalPatient); ?></td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Chưa có chi tiết biên lai</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Footer -->
                    <div class="payment-info">
                        <div class="payment-row">
                            <span class="label">Người bệnh trả:</span>
                            <span class="value"><?php echo number_format($totalPatient); ?> đồng</span>
                        </div>
                        <div class="payment-row">
                            <span class="label">Bằng chữ:</span>
                            <span class="value"><?php echo ucfirst(receiptNumberToWords((int)$totalPatient)); ?> đồng</span>
                        </div>
                    </div>

                    <div class="signature-section">
                        <div class="signature-date">Ngày <?php echo date('d'); ?> tháng <?php echo date('m'); ?> năm <?php echo date('Y'); ?></div>
                        <div class="signature-title">Người Lập Bảng Kê</div>
                        <div class="signature-instruction">(Ký, ghi rõ họ tên)</div>
                        <div class="signature-name"><?php echo escapeHtml($receipt['ten_bac_si'] ?? $exam['ten_bac_si'] ?? 'Bác sĩ khám bệnh'); ?></div>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-5">Chưa có biên lai</p>
            <?php endif; ?>
        </div>
    </div>
</div>

