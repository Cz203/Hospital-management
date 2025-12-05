<?php
require_once 'config/database.php';
require_once 'Models/BienLai.php';

// Get receipt ID from URL
$receiptId = $_GET['id'] ?? '';

if (empty($receiptId)) {
    echo "ID biên lai không hợp lệ";
    exit;
}

// Get receipt data
$database = new Database();
$db = $database->getConnection();
$bienLaiModel = new BienLai($db);

// Try to get by ID first (for backward compatibility)
$receiptData = $bienLaiModel->getById($receiptId);

// If not found by ID, try to get by ma_bien_lai
if (!$receiptData) {
    $receiptData = $bienLaiModel->getByMaBienLai($receiptId);
}

if (!$receiptData) {
    echo "Không tìm thấy biên lai với ID/Mã: " . $receiptId;
    exit;
}

// Get receipt details using the actual ID from receipt data
$receiptDetails = $bienLaiModel->getDetails($receiptData['id']);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Biên Lai Viện Phí</title>
    <style>
    @media print {
        body {
            margin: 0;
        }

        .no-print {
            display: none !important;
        }
    }

    body {
        font-family: 'Times New Roman', serif;
        font-size: 14px;
        line-height: 1.4;
        margin: 0;
        padding: 20px;
        background: white;
    }

    .receipt-container {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        padding: 20px;
        border: 1px solid #ddd;
    }

    .receipt-header {
        text-align: center;
        margin-bottom: 30px;
        position: relative;
    }

    .receipt-clinic-name {
        font-size: 20px;
        font-weight: bold;
        margin: 0 0 10px 0;
        color: #333;
        text-transform: uppercase;
    }

    .receipt-main-title {
        font-size: 18px;
        font-weight: bold;
        margin: 0 0 5px 0;
        color: #333;
    }

    .receipt-subtitle {
        font-size: 14px;
        margin: 0 0 20px 0;
        color: #666;
    }

    .receipt-stt {
        position: absolute;
        top: 0;
        right: 0;
        text-align: right;
        font-size: 16px;
        font-weight: bold;
    }

    .receipt-stt:first-of-type {
        top: 0;
    }

    .receipt-stt:last-of-type {
        top: 30px;
    }

    .patient-info {
        margin-bottom: 20px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .info-item {
        flex: 1;
        margin-right: 20px;
        display: flex;
        align-items: baseline;
    }

    .info-item:last-child {
        margin-right: 0;
    }

    .label {
        font-weight: bold;
        display: inline;
        white-space: nowrap;
    }

    .value {
        min-width: 150px;
        display: inline;
        margin-left: 5px;
        white-space: nowrap;
    }

    .full-width {
        flex: 100%;
    }

    .receipt-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .receipt-table th,
    .receipt-table td {
        border: 1px solid #000;
        padding: 8px;
        text-align: center;
        font-size: 12px;
    }

    .receipt-table th {
        background-color: #f5f5f5;
        font-weight: bold;
    }

    .receipt-table .text-start {
        text-align: left;
    }

    .receipt-table .text-end {
        text-align: right;
    }

    .receipt-table .fw-bold {
        font-weight: bold;
    }

    .receipt-footer {
        margin-top: 30px;
    }

    .payment-info {
        margin-bottom: 5px;
        text-align: right;
    }

    .payment-row {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 5px;
        gap: 10px;
    }

    .signature-section {
        text-align: center;
        margin-top: -10px !important;
        background-color: #f5f5f5;
        border-radius: 8px;
        padding: 20px;
        max-width: 300px;
        margin-left: auto;
        margin-right: 0;
    }

    .signature-date {
        margin-bottom: 5px;
        font-weight: normal;
    }

    .signature-title {
        margin-bottom: 5px;
        font-weight: bold;
    }

    .signature-instruction {
        margin-bottom: 5px;
        font-style: italic;
        text-decoration: underline;
    }

    .signature-name {
        margin-top: 5px;
        font-weight: normal;
    }

    .print-button {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #007bff;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
    }

    .print-button:hover {
        background: #0056b3;
    }
    </style>
</head>

<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> In Biên Lai
    </button>

    <div class="receipt-container">
        <!-- Header -->
        <div class="receipt-header">
            <div style="margin-bottom:10px;">
                <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:70px;object-fit:contain;">
            </div>
            <div class="receipt-title">
                <h1 class="receipt-clinic-name">PHÒNG KHÁM ĐA KHOA THINHVIET</h1>
                <h2 class="receipt-main-title">BIÊN LAI VIỆN PHÍ</h2>
                <p class="receipt-subtitle">Viện phí</p>
            </div>
            <div class="receipt-stt">
                <span class="stt-label">Mã BN:</span>
                <span class="stt-number"><?php echo htmlspecialchars($receiptData['ma_benh_nhan']); ?></span>
            </div>
            <div class="receipt-stt">
                <span class="stt-label">Số HD:</span>
                <span class="stt-number"><?php echo htmlspecialchars($receiptData['ma_bien_lai']); ?></span>
            </div>
        </div>

        <!-- Patient Information -->
        <div class="patient-info">
            <div class="info-row">
                <div class="info-item">
                    <span class="label">Họ và Tên:</span>
                    <span class="value"><?php echo htmlspecialchars($receiptData['ho_ten']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Tuổi:</span>
                    <span class="value"><?php echo htmlspecialchars($receiptData['tuoi']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Giới tính:</span>
                    <span class="value"><?php echo htmlspecialchars($receiptData['gioi_tinh']); ?></span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <span class="label">Địa chỉ:</span>
                    <span class="value"><?php echo htmlspecialchars($receiptData['dia_chi']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Mã số BHYT (nếu có):</span>
                    <span class="value"><?php echo htmlspecialchars($receiptData['so_the_bhyt'] ?: 'Thu phí'); ?></span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-item full-width">
                    <span class="label">Nội Dung Thu:</span>
                    <span class="value fw-bold">Đề nghị thanh toán</span>
                </div>
            </div>
        </div>

        <!-- Receipt Table -->
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
                $stt = 1;
                $totalAmount = 0;
                $totalBhyt = 0;
                $totalPatient = 0;

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
                    usort($receiptDetails, function($a, $b) use ($sectionGroups) {
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
                            echo '<tr>';
                            echo '<td colspan="7" class="text-start"><div class="fw-bold">' . $sectionNames[$group] . '</div></td>';
                            echo '</tr>';
                        } elseif ($group !== $currentGroup) {
                            $currentGroup = $group;
                        }

                        echo '<tr>';
                        echo '<td>' . $stt . '</td>';
                        echo '<td class="text-start">' . htmlspecialchars($detail['ten_dich_vu']) . '</td>';
                        echo '<td>' . (int)$detail['so_luong'] . '</td>';
                        echo '<td class="text-end">' . number_format((float)$detail['don_gia']) . '</td>';
                        echo '<td class="text-end">' . number_format((float)$detail['thanh_tien']) . '</td>';
                        echo '<td class="text-end">' . number_format((float)$detail['quy_bhyt']) . '</td>';
                        echo '<td class="text-end">' . number_format((float)$detail['nguoi_benh']) . '</td>';
                        echo '</tr>';

                        $stt++;
                        $totalAmount += (float)$detail['thanh_tien'];
                        $totalBhyt += (float)$detail['quy_bhyt'];
                        $totalPatient += (float)$detail['nguoi_benh'];
                    }
                }

                // Add total row
                echo '<tr class="fw-bold">';
                echo '<td colspan="4" class="text-start">Tổng Cộng:</td>';
                echo '<td class="text-end">' . number_format($totalAmount) . '</td>';
                echo '<td class="text-end">' . number_format($totalBhyt) . '</td>';
                echo '<td class="text-end">' . number_format($totalPatient) . '</td>';
                echo '</tr>';
                ?>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="receipt-footer">
            <div class="payment-info">
                <div class="payment-row">
                    <span class="label">Người bệnh trả:</span>
                    <span class="value"><?php echo number_format($totalPatient); ?> đồng</span>
                </div>
                <div class="payment-row">
                    <span class="label">Bằng chữ:</span>
                    <span class="value"><?php echo numberToWords($totalPatient); ?> đồng</span>
                </div>
            </div>

            <div class="signature-section">
                <div class="signature-date">Ngày <?php echo date('d'); ?> tháng <?php echo date('m'); ?> năm
                    <?php echo date('Y'); ?></div>
                <div class="signature-title">Người Lập Bảng Kê</div>
                <div class="signature-instruction">(Ký, ghi rõ họ tên)</div>
                <div class="signature-name">
                    <?php echo htmlspecialchars($receiptData['ten_bac_si'] ?? 'Bác sĩ khám bệnh'); ?></div>
            </div>
        </div>
    </div>

    <script>
    // Auto print when page loads
    window.onload = function() {
        setTimeout(function() {
            window.print();
        }, 500);
    };
    </script>
</body>

</html>

<?php
/**
 * Convert number to Vietnamese words
 */
function numberToWords($num)
{
    $ones = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
    $tens = ['', '', 'hai mươi', 'ba mươi', 'bốn mươi', 'năm mươi', 'sáu mươi', 'bảy mươi', 'tám mươi', 'chín mươi'];

    if ($num === 0) return 'không';
    if ($num < 10) return $ones[$num];
    if ($num < 20) {
        if ($num === 10) return 'mười';
        if ($num === 11) return 'mười một';
        if ($num === 12) return 'mười hai';
        if ($num === 13) return 'mười ba';
        if ($num === 14) return 'mười bốn';
        if ($num === 15) return 'mười lăm';
        if ($num === 16) return 'mười sáu';
        if ($num === 17) return 'mười bảy';
        if ($num === 18) return 'mười tám';
        if ($num === 19) return 'mười chín';
        return 'mười ' . ($ones[$num - 10] ?: '');
    }
    if ($num < 100) {
        $ten = floor($num / 10);
        $one = $num % 10;
        if ($ten === 1) {
            return 'mười' . ($one > 0 ? ' ' . $ones[$one] : '');
        }
        return $tens[$ten] . ($one > 0 ? ' ' . $ones[$one] : '');
    }
    if ($num < 1000) {
        $hundred = floor($num / 100);
        $remainder = $num % 100;
        return $ones[$hundred] . ' trăm' . ($remainder > 0 ? ' ' . numberToWords($remainder) : '');
    }
    if ($num < 1000000) {
        $thousand = floor($num / 1000);
        $remainder = $num % 1000;
        return numberToWords($thousand) . ' ngàn' . ($remainder > 0 ? ' ' . numberToWords($remainder) : '');
    }
    if ($num < 1000000000) {
        $million = floor($num / 1000000);
        $remainder = $num % 1000000;
        return numberToWords($million) . ' triệu' . ($remainder > 0 ? ' ' . numberToWords($remainder) : '');
    }
    return $num;
}
?>