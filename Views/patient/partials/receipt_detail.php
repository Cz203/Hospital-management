<?php
/**
 * Partial: Patient receipt detail (rendered inside modal)
 * Expects variables:
 * - $receipt: thông tin biên lai (array)
 * - $receiptDetails: danh sách chi tiết (array)
 */

if (!function_exists('escapeHtml')) {
    function escapeHtml($text)
    {
        return htmlspecialchars((string)($text ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('receiptDetailNumberToWords')) {
    function receiptDetailNumberToWords($num)
    {
        $num = (int)$num;
        $ones = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
        $tens = ['', '', 'hai mươi', 'ba mươi', 'bốn mươi', 'năm mươi', 'sáu mươi', 'bảy mươi', 'tám mươi', 'chín mươi'];

        if ($num === 0) return 'không';
        if ($num < 10) return $ones[$num];
        if ($num < 20) {
            $special = [
                10 => 'mười', 11 => 'mười một', 12 => 'mười hai', 13 => 'mười ba', 14 => 'mười bốn',
                15 => 'mười lăm', 16 => 'mười sáu', 17 => 'mười bảy', 18 => 'mười tám', 19 => 'mười chín'
            ];
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
            $hundred = floor($num / 100);
            $remainder = $num % 100;
            $result = $ones[$hundred] . ' trăm';
            if ($remainder > 0) {
                if ($remainder < 10) {
                    $result .= ' lẻ ' . receiptDetailNumberToWords($remainder);
                } else {
                    $result .= ' ' . receiptDetailNumberToWords($remainder);
                }
            }
            return $result;
        }
        if ($num < 1000000) {
            $thousand = floor($num / 1000);
            $remainder = $num % 1000;
            $result = receiptDetailNumberToWords($thousand) . ' ngàn';
            if ($remainder > 0) {
                if ($remainder < 100) {
                    $result .= ' lẻ ' . receiptDetailNumberToWords($remainder);
                } else {
                    $result .= ' ' . receiptDetailNumberToWords($remainder);
                }
            }
            return $result;
        }
        if ($num < 1000000000) {
            $million = floor($num / 1000000);
            $remainder = $num % 1000000;
            $result = receiptDetailNumberToWords($million) . ' triệu';
            if ($remainder > 0) {
                if ($remainder < 100000) {
                    $result .= ' ' . receiptDetailNumberToWords($remainder);
                } else {
                    $result .= ' ' . receiptDetailNumberToWords($remainder);
                }
            }
            return $result;
        }
        return (string)$num;
    }
}
?>
<div class="receipt-detail-container">
    <style>
        .receipt-detail-container {
            font-family: 'Times New Roman', serif;
            font-size: 14px;
            line-height: 1.4;
        }
        .receipt-detail-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 24px;
        }
        .receipt-detail-header {
            text-align: center;
            margin-bottom: 24px;
            position: relative;
        }
        .receipt-status {
            position: absolute;
            left: 0;
            top: 0;
            padding: 6px 12px;
            border: 2px solid #999;
            border-radius: 8px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: .5px;
            background: #fff;
        }
        .receipt-status.status-unpaid {
            color: #b54708;
            border-color: #facc15;
            background: #fffbe6;
        }
        .receipt-status.status-paid-transfer {
            color: #065f46;
            border-color: #34d399;
            background: #ecfdf5;
        }
        .receipt-status.status-paid-cash {
            color: #166534;
            border-color: #86efac;
            background: #f0fdf4;
        }
        .receipt-detail-header .clinic-name {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .receipt-detail-header .main-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 6px;
        }
        .receipt-detail-header .subtitle {
            color: #666;
            margin-top: 4px;
        }
        .receipt-detail-header .meta {
            position: absolute;
            right: 0;
            text-align: right;
        }
        .receipt-detail-header .meta span {
            display: block;
        }
        .receipt-detail-section {
            margin-bottom: 20px;
        }
        .receipt-detail-section .row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 6px;
        }
        .receipt-detail-section .row .item {
            flex: 1;
            min-width: 180px;
            display: flex;
            gap: 6px;
        }
        .receipt-detail-section .row .label {
            font-weight: bold;
            white-space: nowrap;
        }
        .receipt-detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .receipt-detail-table th,
        .receipt-detail-table td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 12px;
            text-align: center;
        }
        .receipt-detail-table th {
            background: #f5f5f5;
            font-weight: bold;
        }
        .receipt-detail-table .text-start {
            text-align: left;
        }
        .receipt-detail-table .text-end {
            text-align: right;
        }
        .receipt-detail-footer {
            text-align: right;
            margin-top: 12px;
        }
        .receipt-detail-footer .footer-line {
            margin-bottom: 4px;
        }
        .receipt-detail-signature {
            margin-top: 20px;
            background: #f5f5f5;
            border-radius: 10px;
            padding: 18px;
            max-width: 320px;
            margin-left: auto;
            text-align: center;
        }
        .receipt-detail-signature .signature-date {
            margin-bottom: 6px;
        }
        .receipt-detail-signature .signature-title {
            font-weight: bold;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .receipt-detail-signature .signature-instruction {
            font-style: italic;
            text-decoration: underline;
            margin-bottom: 8px;
        }
        .receipt-detail-signature .signature-name {
            font-weight: 500;
        }
    </style>

    <div class="receipt-detail-card">
        <?php if ($receipt): ?>
            <div class="receipt-detail-header">
                <?php
                    $statusText = trim($receipt['trang_thai'] ?? '');
                    $statusClass = 'status-unpaid';
                    if ($statusText === 'Đã thanh toán chuyển khoản') $statusClass = 'status-paid-transfer';
                    elseif ($statusText === 'Đã thanh toán tiền mặt') $statusClass = 'status-paid-cash';
                    elseif ($statusText === 'Chưa thanh toán') $statusClass = 'status-unpaid';
                    // Fallback friendly text
                    if ($statusText === '') $statusText = 'Chưa xác định';
                ?>
                <div class="receipt-status <?php echo $statusClass; ?>">
                    <?php echo escapeHtml($statusText); ?>
                </div>
                <div class="clinic-name">Phòng khám đa khoa ThinhViet</div>
                <div class="main-title">BIÊN LAI VIỆN PHÍ</div>
                <div class="subtitle">Bản xem chi tiết</div>
                <div class="meta">
                    <span><strong>Mã BN:</strong> <?php echo escapeHtml($receipt['ma_benh_nhan'] ?? '-'); ?></span>
                    <span><strong>Số BL:</strong> <?php echo escapeHtml($receipt['ma_bien_lai'] ?? '-'); ?></span>
                </div>
            </div>

            <div class="receipt-detail-section">
                <div class="row">
                    <div class="item">
                        <span class="label">Họ và tên:</span>
                        <span><?php echo escapeHtml($receipt['ho_ten'] ?? '-'); ?></span>
                    </div>
                    <div class="item">
                        <span class="label">Tuổi:</span>
                        <span><?php echo escapeHtml($receipt['tuoi'] ?? '-'); ?></span>
                    </div>
                    <div class="item">
                        <span class="label">Giới tính:</span>
                        <span><?php echo escapeHtml($receipt['gioi_tinh'] ?? '-'); ?></span>
                    </div>
                </div>
                <div class="row">
                    <div class="item">
                        <span class="label">Địa chỉ:</span>
                        <span><?php echo escapeHtml($receipt['dia_chi'] ?? '-'); ?></span>
                    </div>
                    <div class="item">
                        <span class="label">Mã BHYT:</span>
                        <span><?php echo !empty($receipt['so_the_bhyt']) ? escapeHtml($receipt['so_the_bhyt']) : 'Thu phí'; ?></span>
                    </div>
                </div>
            </div>

            <table class="receipt-detail-table">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Nội dung</th>
                        <th>Số lượng</th>
                        <th>Đơn giá (đ)</th>
                        <th>Thành tiền (đ)</th>
                        <th>Quỹ BHYT (đ)</th>
                        <th>Người bệnh (đ)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $totalAmount = 0;
                        $totalBhyt = 0;
                        $totalPatient = 0;
                        $stt = 1;

                        $sectionGroups = [
                            'Kham benh' => ['group' => 'kham_benh', 'order' => 1],
                            'Xet nghiem' => ['group' => 'can_lam_sang', 'order' => 2],
                            'Sieu am' => ['group' => 'can_lam_sang', 'order' => 2],
                            'X-Quang' => ['group' => 'can_lam_sang', 'order' => 2],
                            'Xray' => ['group' => 'can_lam_sang', 'order' => 2],
                            'Thuoc' => ['group' => 'thuoc', 'order' => 3]
                        ];
                        $sectionNames = [
                            'kham_benh' => 'Khám bệnh lâm sàng',
                            'can_lam_sang' => 'Khám bệnh cận lâm sàng',
                            'thuoc' => 'Thuốc điều trị'
                        ];

                        if (!empty($receiptDetails)) {
                            usort($receiptDetails, function ($a, $b) use ($sectionGroups) {
                                $infoA = $sectionGroups[$a['loai_dich_vu'] ?? ''] ?? ['group' => 'other', 'order' => 999];
                                $infoB = $sectionGroups[$b['loai_dich_vu'] ?? ''] ?? ['group' => 'other', 'order' => 999];
                                if ($infoA['order'] !== $infoB['order']) {
                                    return $infoA['order'] - $infoB['order'];
                                }
                                return ($a['id'] ?? 0) - ($b['id'] ?? 0);
                            });

                            $currentGroup = '';
                            foreach ($receiptDetails as $detail) {
                                $loai = $detail['loai_dich_vu'] ?? '';
                                $info = $sectionGroups[$loai] ?? ['group' => 'other', 'order' => 999];
                                $group = $info['group'];

                                if ($group !== $currentGroup && isset($sectionNames[$group])) {
                                    $currentGroup = $group;
                                    echo '<tr><td colspan="7" class="text-start"><strong>' . $sectionNames[$group] . '</strong></td></tr>';
                                } elseif ($group !== $currentGroup) {
                                    $currentGroup = $group;
                                }

                                $totalAmount += (float)($detail['thanh_tien'] ?? 0);
                                $totalBhyt += (float)($detail['quy_bhyt'] ?? 0);
                                $totalPatient += (float)($detail['nguoi_benh'] ?? 0);
                                ?>
                                <tr>
                                    <td><?php echo $stt++; ?></td>
                                    <td class="text-start"><?php echo escapeHtml($detail['ten_dich_vu'] ?? '-'); ?></td>
                                    <td><?php echo (int)($detail['so_luong'] ?? 0); ?></td>
                                    <td class="text-end"><?php echo number_format((float)($detail['don_gia'] ?? 0)); ?></td>
                                    <td class="text-end"><?php echo number_format((float)($detail['thanh_tien'] ?? 0)); ?></td>
                                    <td class="text-end"><?php echo number_format((float)($detail['quy_bhyt'] ?? 0)); ?></td>
                                    <td class="text-end"><?php echo number_format((float)($detail['nguoi_benh'] ?? 0)); ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="7" class="text-center text-muted">Chưa có chi tiết biên lai</td></tr>';
                        }
                    ?>
                    <tr class="fw-bold">
                        <td colspan="4" class="text-start">Tổng cộng:</td>
                        <td class="text-end"><?php echo number_format($totalAmount); ?></td>
                        <td class="text-end"><?php echo number_format($totalBhyt); ?></td>
                        <td class="text-end"><?php echo number_format($totalPatient); ?></td>
                    </tr>
                </tbody>
            </table>

             <?php $totalPatientWords = ucfirst(receiptDetailNumberToWords((int)$totalPatient)); ?>
             <div class="receipt-detail-footer">
                 <div class="footer-line">
                     <span class="label"><strong>Người bệnh trả:</strong></span>
                     <span><?php echo number_format($totalPatient); ?> đồng</span>
                 </div>
                 <div class="footer-line">
                     <span class="label"><strong>Bằng chữ:</strong></span>
                     <span><?php echo $totalPatientWords; ?> đồng</span>
                 </div>
             </div>

            <div class="receipt-detail-signature">
                 <div class="signature-date">Ngày <?php echo date('d'); ?> tháng <?php echo date('m'); ?> năm <?php echo date('Y'); ?></div>
                 <div class="signature-title">Người Lập Bảng Kê</div>
                 <div class="signature-instruction">(Ký, ghi rõ họ tên)</div>
                 <div class="signature-name"><?php echo escapeHtml($receipt['ten_bac_si'] ?? $receipt['nguoi_lap'] ?? ''); ?></div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">Chưa có dữ liệu biên lai.</div>
        <?php endif; ?>
    </div>
</div>

