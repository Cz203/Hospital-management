<?php
/**
 * View đơn thuốc cho Patient - PDF-like interface
 */

$exam = $data['exam'] ?? [];
$prescription = $data['prescription'] ?? null;
$prescriptionDetails = $data['prescription_details'] ?? [];

function escapeHtml($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

// Chuẩn bị dữ liệu đơn thuốc giống như print view
$prescriptionData = $prescription;
if ($prescriptionData && !empty($prescriptionDetails)) {
    $prescriptionData['medications'] = $prescriptionDetails;
}

// Lấy thông tin bệnh nhân từ prescription (đã JOIN với phieu_kham_benh và benh_nhan) hoặc từ exam
if ($prescriptionData) {
    // Ưu tiên dữ liệu từ prescription (đã JOIN), fallback về exam
    $prescriptionData['ho_ten'] = $prescriptionData['ho_ten'] ?? $exam['ho_ten'] ?? '';
    $prescriptionData['ma_benh_nhan'] = $prescriptionData['ma_benh_nhan'] ?? $exam['ma_benh_nhan'] ?? '';
    $prescriptionData['so_the_bhyt'] = $prescriptionData['so_the_bhyt'] ?? $exam['so_the_bhyt'] ?? '';
    $prescriptionData['dia_chi'] = $prescriptionData['dia_chi'] ?? $exam['dia_chi'] ?? '';
    $prescriptionData['ngay_sinh'] = $prescriptionData['ngay_sinh'] ?? $exam['ngay_sinh_benh_nhan'] ?? '';
    $prescriptionData['gioi_tinh'] = $prescriptionData['gioi_tinh'] ?? $exam['gioi_tinh'] ?? '';
    $prescriptionData['so_dien_thoai'] = $prescriptionData['so_dien_thoai'] ?? $exam['so_dien_thoai'] ?? '';
    // ten_bac_si đã được lấy từ JOIN với phieu_kham_benh
    $prescriptionData['ten_bac_si'] = $prescriptionData['ten_bac_si'] ?? $exam['ten_bac_si'] ?? 'GSTS. Cao Việt';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn Thuốc</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            background-color: #525252;
            padding: 20px;
            min-height: 100vh;
        }
        
        .pdf-viewer-container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #525252;
            padding: 20px;
        }
        
        .pdf-toolbar {
            background-color: #2d2d2d;
            padding: 10px 20px;
            border-radius: 4px 4px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #fff;
        }
        
        .pdf-toolbar-left {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .pdf-toolbar-right {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .pdf-toolbar button {
            background-color: #3d3d3d;
            border: 1px solid #555;
            color: #fff;
            padding: 6px 12px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .pdf-toolbar button:hover {
            background-color: #4d4d4d;
        }
        
        .pdf-content {
            background-color: #fff;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            min-height: 800px;
            font-family: 'Times New Roman', serif;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        
        .hospital-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .form-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 20px 0;
        }
        
        .prescription-id {
            position: absolute;
            top: 0;
            right: 0;
            font-size: 14px;
            font-weight: bold;
        }
        
        .patient-info-section {
            display: flex;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 15px;
        }
        
        .patient-info-left {
            flex: 1;
            padding-right: 20px;
            border-right: 1px solid #ddd;
        }
        
        .patient-info-right {
            flex: 1;
            padding-left: 20px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 10px;
            align-items: center;
        }
        
        .info-label {
            font-weight: bold;
            min-width: 120px;
            margin-right: 10px;
        }
        
        .info-value {
            flex: 1;
            min-height: 20px;
            border-bottom: 1px solid #000;
            padding: 2px 5px;
        }
        
        .diagnosis-section {
            margin-bottom: 20px;
        }
        
        .diagnosis-label {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .diagnosis-content {
            border-bottom: 1px solid #000;
            padding: 5px;
            min-height: 40px;
        }
        
        .medication-section {
            margin-bottom: 20px;
        }
        
        .medication-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .instructions-section {
            margin-bottom: 20px;
        }
        
        .instructions-label {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .instructions-content {
            border-bottom: 1px solid #000;
            padding: 5px;
            min-height: 30px;
        }
        
        .no-medication {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #666;
        }
        
        .medication-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .medication-table th,
        .medication-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .medication-table th {
            background-color: #e8f5e8;
            font-weight: bold;
            text-align: center;
        }
        
        .medication-table .stt {
            text-align: center;
            width: 50px;
        }
        
        .medication-table .medication-name {
            width: 200px;
        }
        
        .medication-table .active-ingredient {
            width: 150px;
        }
        
        .medication-table .unit {
            text-align: center;
            width: 80px;
        }
        
        .medication-table .quantity {
            text-align: center;
            width: 60px;
        }
        
        .medication-table .usage {
            width: 200px;
        }
        
        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
        }
        
        .signature-box {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            min-width: 200px;
        }
        
        .signature-date {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .signature-label {
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .doctor-name {
            font-weight: bold;
        }
        
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            font-size: 16px;
        }
        
        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            .pdf-viewer-container {
                padding: 0;
            }
            .pdf-toolbar {
                display: none;
            }
            .pdf-content {
                box-shadow: none;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="pdf-viewer-container">
        <!-- PDF Toolbar -->
        <div class="pdf-toolbar">
            <div class="pdf-toolbar-left">
                <button onclick="window.print()">
                    <i class="fas fa-print"></i> In
                </button>
                <button onclick="window.close()">
                    <i class="fas fa-times"></i> Đóng
                </button>
            </div>
            <div class="pdf-toolbar-right">
                <span>Trang 1</span>
            </div>
        </div>
        
        <!-- PDF Content -->
        <div class="pdf-content">
            <?php if ($prescriptionData): ?>
                <div class="header">
                    <div style="margin-bottom:10px;">
                        <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:70px;object-fit:contain;">
                    </div>
                    <div class="hospital-name">PHÒNG KHÁM ĐA KHOA THINHVIET</div>
                    <div style="font-size: 12px;">Địa chỉ: Gò vấp</div>
                    <div class="form-title">ĐƠN THUỐC</div>
                    <div class="prescription-id">Mã đơn thuốc: <?php echo escapeHtml($prescriptionData['MaDonThuoc'] ?? ''); ?></div>
                </div>

                <!-- Thông tin bệnh nhân - Layout 2 cột -->
                <div class="patient-info-section">
                    <div class="patient-info-left">
                        <div class="info-row">
                            <div class="info-label">Họ tên:</div>
                            <div class="info-value"><?php echo !empty($prescriptionData['ho_ten']) ? escapeHtml($prescriptionData['ho_ten']) : '...'; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Mã Bệnh nhân:</div>
                            <div class="info-value"><?php echo !empty($prescriptionData['ma_benh_nhan']) ? escapeHtml($prescriptionData['ma_benh_nhan']) : '...'; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Mã số BHYT (nếu có):</div>
                            <div class="info-value"><?php echo !empty($prescriptionData['so_the_bhyt']) ? escapeHtml($prescriptionData['so_the_bhyt']) : 'Thu phí'; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Địa chỉ liên hệ:</div>
                            <div class="info-value"><?php echo !empty($prescriptionData['dia_chi']) ? escapeHtml($prescriptionData['dia_chi']) : '...'; ?></div>
                        </div>
                    </div>

                    <div class="patient-info-right">
                        <div class="info-row">
                            <div class="info-label">Ngày sinh:</div>
                            <div class="info-value"><?php echo !empty($prescriptionData['ngay_sinh']) ? escapeHtml($prescriptionData['ngay_sinh']) : '...'; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Giới tính:</div>
                            <div class="info-value"><?php echo !empty($prescriptionData['gioi_tinh']) ? escapeHtml($prescriptionData['gioi_tinh']) : '...'; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Số điện thoại:</div>
                            <div class="info-value"><?php echo !empty($prescriptionData['so_dien_thoai']) ? escapeHtml($prescriptionData['so_dien_thoai']) : '...'; ?></div>
                        </div>
                    </div>
                </div>

                <!-- Chẩn đoán -->
                <div class="diagnosis-section">
                    <div class="diagnosis-label">Chẩn đoán:</div>
                    <div class="diagnosis-content"><?php echo escapeHtml($prescriptionData['ChanDoan'] ?? ''); ?></div>
                </div>

                <!-- Thuốc điều trị -->
                <div class="medication-section">
                    <div class="medication-title">Thuốc điều trị</div>
                    <?php 
                        // Lấy số ngày dùng thuốc hiển thị riêng (ưu tiên từ chi tiết, fallback 1)
                        $soNgayIn = 1;
                        if (!empty($prescriptionData['medications'])) {
                            foreach ($prescriptionData['medications'] as $m) {
                                if (isset($m['so_ngay']) && (int)$m['so_ngay'] > 0) { 
                                    $soNgayIn = (int)$m['so_ngay']; 
                                    break; 
                                }
                            }
                        }
                    ?>
                    <div style="display:flex; justify-content:flex-end; margin-bottom:10px;">
                        <div style="display:inline-flex; align-items:center; gap:8px; font-weight:bold;">
                            <span>Số ngày dùng thuốc:</span>
                            <span style="min-width:40px; border-bottom:1px solid #000; text-align:center; display:inline-block; padding:2px 6px;">
                                <?php echo escapeHtml($soNgayIn); ?>
                            </span>
                            <span>ngày</span>
                        </div>
                    </div>
                    
                    <?php if (!empty($prescriptionData['medications'])): ?>
                    <table class="medication-table">
                        <thead>
                            <tr>
                                <th class="stt">STT</th>
                                <th class="medication-name">Tên Thuốc</th>
                                <th class="active-ingredient">Hoạt chất</th>
                                <th class="unit">ĐVT</th>
                                <th class="quantity">SL</th>
                                <th class="usage">Cách dùng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prescriptionData['medications'] as $index => $medication): ?>
                            <tr>
                                <td class="stt"><?php echo $index + 1; ?></td>
                                <td class="medication-name"><?php echo escapeHtml($medication['TenThuoc'] ?? ''); ?></td>
                                <td class="active-ingredient"><?php echo escapeHtml($medication['HoatChat'] ?? ''); ?></td>
                                <td class="unit"><?php echo escapeHtml($medication['DonViTinh'] ?? ''); ?></td>
                                <td class="quantity"><?php echo escapeHtml($medication['SoLuong'] ?? ''); ?></td>
                                <td class="usage">
                                    <?php
                                        $unit = strtolower($medication['DonViTinh'] ?? '');
                                        $hasPerSession = isset($medication['vien_sang']) || isset($medication['vien_trua']) || isset($medication['vien_chieu']) || isset($medication['vien_toi']);
                                        if ($hasPerSession && (strpos($unit, 'viên') !== false || strpos($unit, 'vien') !== false)) {
                                            $sessions = [
                                                ['label' => 'Sáng',  'dose' => $medication['vien_sang']  ?? 0, 'meal' => $medication['sang_bua']  ?? 'none'],
                                                ['label' => 'Trưa',  'dose' => $medication['vien_trua']  ?? 0, 'meal' => $medication['trua_bua']  ?? 'none'],
                                                ['label' => 'Chiều', 'dose' => $medication['vien_chieu'] ?? 0, 'meal' => $medication['chieu_bua'] ?? 'none'],
                                                ['label' => 'Tối',   'dose' => $medication['vien_toi']   ?? 0, 'meal' => $medication['toi_bua']   ?? 'none'],
                                            ];
                                            foreach ($sessions as $s) {
                                                $dose = (float)$s['dose'];
                                                if ($dose > 0) {
                                                    $meal = $s['meal'] === 'before' ? 'trước ăn' : ($s['meal'] === 'after' ? 'sau ăn' : '');
                                                    echo '<div>' . $s['label'] . ': ' . rtrim(rtrim(number_format($dose, 2, ',', '.'), '0'), ',') . ' viên' . ($meal ? ' (' . $meal . ')' : '') . '</div>';
                                                }
                                            }
                                            if (!empty($medication['ghi_chu_cach_dung'])) {
                                                echo '<div><em>' . escapeHtml($medication['ghi_chu_cach_dung']) . '</em></div>';
                                            }
                                        } else {
                                            echo escapeHtml($medication['LieuDung'] ?? '');
                                        }
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-medication">Không có thuốc trong đơn</div>
                    <?php endif; ?>
                </div>

                <!-- Lời dặn -->
                <div class="instructions-section">
                    <div class="instructions-label">Lời dặn:</div>
                    <div class="instructions-content"><?php echo escapeHtml($prescriptionData['GhiChu'] ?? ''); ?></div>
                </div>

                <!-- Chữ ký -->
                <div class="signature-section">
                    <div class="signature-box">
                        <div class="signature-date"><?php echo date('d/m/Y'); ?></div>
                        <div class="signature-label">Bác sỹ ký tên</div>
                        <div class="doctor-name"><?php echo escapeHtml($prescriptionData['ten_bac_si'] ?? 'GSTS. Cao Việt'); ?></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <p>Chưa có đơn thuốc</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>

