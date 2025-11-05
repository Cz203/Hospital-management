<?php
// Get result data from controller
$mainResult = $mainResult ?? null;
$testDetails = $testDetails ?? [];
if (!$mainResult) {
    echo "Không tìm thấy dữ liệu kết quả xét nghiệm";
    exit;
}

// Function to check if result is out of range
function isOutOfRange($testName, $resultValue, $pdo) {
    try {
        $sql = "SELECT chi_so_tu, chi_so_den FROM chi_so_xet_nghiem 
                WHERE xet_nghiem = ? AND chi_so_tu IS NOT NULL AND chi_so_den IS NOT NULL";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$testName]);
        $chiSo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($chiSo && is_numeric($resultValue)) {
            $resultNum = floatval($resultValue);
            $isOut = $resultNum < $chiSo['chi_so_tu'] || $resultNum > $chiSo['chi_so_den'];
            return $isOut;
        }
        return false;
    } catch (Exception $e) {
        error_log("Print validation error: " . $e->getMessage());
        return false;
    }
}

// Get database connection
require_once 'config/database.php';
$database = new Database();
$pdo = $database->getConnection();

// Function to determine form type from yeu_cau
function determineFormType($yeuCau) {
    if (empty($yeuCau)) {
        return 'other';
    }
    $yeuCauLower = mb_strtolower($yeuCau, 'UTF-8');
    // Check for "máu toàn phần"
    if (strpos($yeuCauLower, 'máu toàn phần') !== false || 
        strpos($yeuCauLower, 'cong thuc mau') !== false ||
        strpos($yeuCauLower, 'công thức máu') !== false) {
        return 'mau_toan_phan';
    }
    // Check for "máu" or "nước tiểu" (but not "máu toàn phần")
    if (strpos($yeuCauLower, 'máu') !== false || 
        strpos($yeuCauLower, 'nước tiểu') !== false ||
        strpos($yeuCauLower, 'nuoc tieu') !== false) {
        return 'mau_nuoc_tieu';
    }
    return 'other';
}

// Determine form type
$formType = determineFormType($mainResult['yeu_cau'] ?? '');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả xét nghiệm</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .clinic-name {
            font-size: 18px;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
        }
        
        .department {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 10px;
        }
        
        .date-time {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
            gap: 10px;
        }
        
        .patient-info {
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            width: 120px;
            flex-shrink: 0;
        }
        
        .info-value {
            flex: 1;
            border-bottom: 1px solid #000;
            padding-left: 10px;
        }
        
        .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin: 20px 0 10px 0;
            font-family: 'Times New Roman', serif;
            text-transform: uppercase;
        }
        
        .table-title {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        
        th.result {
            font-weight: bold;
        }
        
        .stt {
            width: 50px;
            text-align: center;
        }
        
        .test-name {
            width: 200px;
        }
        
        .reference {
            width: 150px;
        }
        
        .result {
            width: 120px;
            font-weight: normal;
            text-align: center;
        }
        
        .result.out-of-range {
            font-weight: bold;
            color: #dc3545;
            text-align: right;
        }
        
        @media print {
            th.result {
                font-weight: bold !important;
            }
            .result {
                font-weight: normal !important;
                text-align: center !important;
            }
            .result.out-of-range {
                font-weight: bold !important;
                color: #000 !important;
                text-align: right !important;
            }
        }
        
        .unit {
            width: 80px;
            text-align: center;
        }
        
        .machine {
            width: 120px;
        }
        
        .notes {
            font-size: 10px;
            color: #666;
            margin-bottom: 20px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: right;
        }
        
        .date-inputs {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 10px;
            margin-left: auto;
            width: fit-content;
        }
        
        .date-input {
            width: 50px;
            border: none;
            border-bottom: 1px solid #000;
            text-align: center;
            background: transparent;
        }
        
        .doctor-section {
            text-align: center;
            margin-left: auto;
            width: fit-content;
        }
        
        .doctor-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .doctor-name {
            width: 200px;
            margin: 0 auto;
            padding: 5px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="clinic-name">PHÒNG KHÁM ĐA KHOA THINHVIET</div>
        <div class="department">KHOA XÉT NGHIỆM</div>
        <div class="report-title">KẾT QUẢ XÉT NGHIỆM</div>
        <div class="date-time">
            <span>Ngày ĐK: <?php echo date('d/m/Y', strtotime($mainResult['ngay_dang_ky'])); ?></span>
            <span>|</span>
            <span><?php echo date('H:i', strtotime($mainResult['ngay_dang_ky'])); ?></span>
        </div>
    </div>

    <!-- Patient Information -->
    <div class="patient-info">
        <div class="info-row">
            <div class="info-label">Mã bệnh nhân:</div>
            <div class="info-value"><?php echo htmlspecialchars($mainResult['ma_benh_nhan'] ?? ''); ?></div>
            <div class="info-label" style="margin-left: 50px;">Tuổi:</div>
            <div class="info-value"><?php echo htmlspecialchars($mainResult['tuoi'] ?? ''); ?></div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Họ và tên:</div>
            <div class="info-value"><?php echo htmlspecialchars($mainResult['ho_ten'] ?? ''); ?></div>
            <div class="info-label" style="margin-left: 50px;">Giới tính:</div>
            <div class="info-value"><?php echo htmlspecialchars($mainResult['gioi_tinh'] ?? ''); ?></div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Địa chỉ:</div>
            <div class="info-value"><?php echo htmlspecialchars($mainResult['dia_chi'] ?? ''); ?></div>
            <div class="info-label" style="margin-left: 50px;">BS yêu cầu:</div>
            <div class="info-value"><?php echo htmlspecialchars($mainResult['bac_si_yeu_cau'] ?? $mainResult['bac_si_kham'] ?? ''); ?></div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Chẩn đoán sơ bộ:</div>
            <div class="info-value"><?php echo htmlspecialchars($mainResult['chan_doan_so_bo'] ?? $mainResult['chan_doan'] ?? ''); ?></div>
            <div class="info-label" style="margin-left: 50px;">Chất lượng mẫu:</div>
            <div class="info-value"><?php echo htmlspecialchars($mainResult['tinh_trang_mau'] ?? ''); ?></div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Vị trí lấy mẫu:</div>
            <div class="info-value"><?php echo htmlspecialchars($mainResult['vi_tri_lay_mau'] ?? ''); ?></div>
        </div>
    </div>

    <!-- Test Results Section -->
    <div class="section-title"><?php echo strtoupper($mainResult['yeu_cau'] ?? 'YÊU CẦU XÉT NGHIỆM'); ?></div>
    <div class="table-title">BẢNG KẾT QUẢ XÉT NGHIỆM</div>
    
    <table>
        <thead>
            <tr>
                <th class="stt">STT</th>
                <th class="test-name">Xét nghiệm</th>
                <th class="reference">Giá trị tham chiếu</th>
                <th class="result">Kết quả</th>
                <th class="unit">Đơn vị</th>
                <th class="machine">Máy/QTKT</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($testDetails)): ?>
                <?php if ($formType === 'mau_toan_phan'): ?>
                    <!-- Header rows for "mau_toan_phan" form -->
                    <tr class="fw-bold" style="background-color: #f8f9fa;">
                        <td class="fw-bold text-start" colspan="6" style="text-align: left;">
                            <span>XN Huyết học</span>
                        </td>
                    </tr>
                    <tr class="fw-bold" style="background-color: #f8f9fa;">
                        <td class="fw-bold text-start" colspan="6" style="text-align: left;">
                            <span>TPT tế bào máu(máy đếm larser)</span>
                        </td>
                    </tr>
                <?php elseif ($formType === 'mau_nuoc_tieu'): ?>
                    <!-- Header row for "mau_nuoc_tieu" form -->
                    <tr class="fw-bold" style="background-color: #f8f9fa;">
                        <td class="fw-bold text-start" colspan="6" style="text-align: left;">
                            <span>Sinh Hóa</span>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($testDetails as $test): ?>
                <?php 
                $isOutOfRange = isOutOfRange($test['ten_xet_nghiem'], $test['ket_qua'], $pdo);
                $resultClass = $isOutOfRange ? 'result out-of-range' : 'result';
                // Check if result is "Dương tính" - make it bold and right-aligned
                $ketQua = trim($test['ket_qua']);
                $isDuongTinh = stripos($ketQua, 'dương tính') !== false || stripos($ketQua, 'duong tinh') !== false;
                if ($isDuongTinh) {
                    $resultClass = 'result fw-bold';
                }
                ?>
                <tr>
                    <td class="stt"><?php echo $test['stt']; ?></td>
                    <td class="test-name"><?php echo htmlspecialchars($test['ten_xet_nghiem']); ?></td>
                    <td class="reference"><?php echo htmlspecialchars($test['gia_tri_tham_chieu']); ?></td>
                    <td class="<?php echo $resultClass; ?>" style="<?php echo $isDuongTinh ? 'font-weight: bold; color: #dc3545; text-align: right;' : ''; ?>"><?php echo htmlspecialchars($test['ket_qua']); ?></td>
                    <td class="unit"><?php echo htmlspecialchars($test['don_vi']); ?></td>
                    <td class="machine"><?php echo htmlspecialchars($test['may_qtkt']); ?></td>
                </tr>
                <?php 
                // Add header row "Miễn dịch" after STT 12 for "mau_nuoc_tieu" form
                if ($formType === 'mau_nuoc_tieu' && $test['stt'] == 12): ?>
                <tr class="fw-bold" style="background-color: #f8f9fa;">
                    <td class="fw-bold text-start" colspan="6" style="text-align: left;">
                        <span>Miễn dịch</span>
                    </td>
                </tr>
                <?php endif; ?>
                <?php 
                // Add header rows "Nước tiểu" and "Nước tiểu 10 thông số" after STT 14 for "mau_nuoc_tieu" form
                if ($formType === 'mau_nuoc_tieu' && $test['stt'] == 14): ?>
                <tr class="fw-bold" style="background-color: #f8f9fa;">
                    <td class="fw-bold text-start" colspan="6" style="text-align: left;">
                        <span>Nước tiểu</span>
                    </td>
                </tr>
                <tr class="fw-bold" style="background-color: #f8f9fa;">
                    <td class="fw-bold text-start" colspan="6" style="text-align: left;">
                        <span>Nước tiểu 10 thông số</span>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">Chưa có kết quả xét nghiệm</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Notes -->
    <div class="notes">
        <strong>Ghi chú:</strong> Kết quả in đậm là kết quả nằm ngoài khoảng tham chiếu.<br>
        Xét nghiệm đánh dấu (*) là xét nghiệm được thực hiện bởi PXN chuyển gửi.
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="doctor-section">
            <div class="date-inputs">
                <span>Ngày</span>
                <input type="text" class="date-input" value="<?php echo date('d'); ?>" readonly>
                <span>tháng</span>
                <input type="text" class="date-input" value="<?php echo date('m'); ?>" readonly>
                <span>năm</span>
                <input type="text" class="date-input" value="<?php echo date('Y'); ?>" readonly>
            </div>
            <div class="doctor-title">BÁC SĨ XÉT NGHIỆM</div>
            <div class="doctor-name"><?php echo htmlspecialchars($mainResult['bac_si_xet_nghiem'] ?? ''); ?></div>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
