<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn Thuốc</title>
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
        <i class="fas fa-print"></i> In đơn thuốc
    </button>

    <div class="header">
        <div class="hospital-name">PHÒNG KHÁM ĐA KHOA THINHVIET</div>
        <div style="font-size: 12px;">Địa chỉ: Gò vấp</div>
        <div class="form-title">ĐƠN THUỐC</div>
        <div class="prescription-id">Mã đơn thuốc:
            <?php echo htmlspecialchars($prescriptionData['MaDonThuoc'] ?? ''); ?></div>
    </div>

    <!-- Thông tin bệnh nhân - Layout 2 cột -->
    <div class="patient-info-section">
        <div class="patient-info-left">
            <div class="info-row">
                <div class="info-label">Họ tên:</div>
                <div class="info-value">
                    <?php echo !empty($prescriptionData['ho_ten']) ? htmlspecialchars($prescriptionData['ho_ten']) : '...'; ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Mã Bệnh nhân:</div>
                <div class="info-value">
                    <?php echo !empty($prescriptionData['ma_benh_nhan']) ? htmlspecialchars($prescriptionData['ma_benh_nhan']) : '...'; ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Mã số BHYT (nếu có):</div>
                <div class="info-value">
                    <?php echo !empty($prescriptionData['so_the_bhyt']) ? htmlspecialchars($prescriptionData['so_the_bhyt']) : 'Thu phí'; ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Địa chỉ liên hệ:</div>
                <div class="info-value">
                    <?php echo !empty($prescriptionData['dia_chi']) ? htmlspecialchars($prescriptionData['dia_chi']) : '...'; ?>
                </div>
            </div>
        </div>

        <div class="patient-info-right">
            <div class="info-row">
                <div class="info-label">Ngày sinh:</div>
                <div class="info-value">
                    <?php echo !empty($prescriptionData['ngay_sinh']) ? htmlspecialchars($prescriptionData['ngay_sinh']) : '...'; ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Giới tính:</div>
                <div class="info-value">
                    <?php echo !empty($prescriptionData['gioi_tinh']) ? htmlspecialchars($prescriptionData['gioi_tinh']) : '...'; ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Số điện thoại:</div>
                <div class="info-value">
                    <?php echo !empty($prescriptionData['so_dien_thoai']) ? htmlspecialchars($prescriptionData['so_dien_thoai']) : '...'; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Chẩn đoán -->
    <div class="diagnosis-section">
        <div class="diagnosis-label">Chẩn đoán:</div>
        <div class="diagnosis-content"><?php echo htmlspecialchars($prescriptionData['ChanDoan'] ?? ''); ?></div>
    </div>

    <!-- Thuốc điều trị -->
    <div class="medication-section">
        <div class="medication-title">Thuốc điều trị</div>
<<<<<<< HEAD
        <?php 
            // Lấy số ngày dùng thuốc hiển thị riêng (ưu tiên từ chi tiết, fallback 1)
            $soNgayIn = 1;
            if (!empty($prescriptionData['medications'])) {
                foreach ($prescriptionData['medications'] as $m) {
                    if (isset($m['so_ngay']) && (int)$m['so_ngay'] > 0) { $soNgayIn = (int)$m['so_ngay']; break; }
                }
            }
        ?>
        <div style="display:flex; justify-content:flex-end; margin-bottom:10px;">
            <div style="display:inline-flex; align-items:center; gap:8px; font-weight:bold;">
                <span>Số ngày dùng thuốc:</span>
                <span style="min-width:40px; border-bottom:1px solid #000; text-align:center; display:inline-block; padding:2px 6px;">
                    <?php echo htmlspecialchars($soNgayIn); ?>
                </span>
                <span>ngày</span>
            </div>
        </div>
        
=======

>>>>>>> feca24286521f4c570348ed0f0dcf7939751d6a4
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
                    <td class="medication-name"><?php echo htmlspecialchars($medication['TenThuoc'] ?? ''); ?></td>
                    <td class="active-ingredient"><?php echo htmlspecialchars($medication['HoatChat'] ?? ''); ?></td>
                    <td class="unit"><?php echo htmlspecialchars($medication['DonViTinh'] ?? ''); ?></td>
                    <td class="quantity"><?php echo htmlspecialchars($medication['SoLuong'] ?? ''); ?></td>
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
                                    echo '<div><em>' . htmlspecialchars($medication['ghi_chu_cach_dung']) . '</em></div>';
                                }
                            } else {
                                echo htmlspecialchars($medication['LieuDung'] ?? '');
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
        <div class="instructions-content"><?php echo htmlspecialchars($prescriptionData['GhiChu'] ?? ''); ?></div>
    </div>

    <!-- Chữ ký -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-date"><?php echo date('d/m/Y'); ?></div>
            <div class="signature-label">Bác sỹ ký tên</div>
            <div class="doctor-name">
                <?php echo htmlspecialchars($prescriptionData['ten_bac_si'] ?? 'GSTS. Cao Việt'); ?></div>
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