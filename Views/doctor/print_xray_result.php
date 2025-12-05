<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả X-Quang - <?php echo htmlspecialchars($data['ho_ten'] ?? ''); ?></title>
    <style>
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
        }
        
        body {
            font-family: "Times New Roman", serif;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 18px;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        
        .subtitle {
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .hr {
            border-top: 2px solid #000;
            margin: 15px 0;
        }
        
        .row-line {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 15px;
        }
        
        .label {
            min-width: 150px;
            font-weight: bold;
        }
        
        .dots {
            flex: 0 0 auto;
        }
        
        .value {
            flex: 1;
            border-bottom: 1px dotted #333;
            min-height: 20px;
        }
        
        .section {
            margin-top: 15px;
            margin-bottom: 10px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 16px;
        }
        
        .result-content {
            margin: 15px 0;
            padding: 10px;
            border: 1px solid #333;
            min-height: 100px;
        }
        
        .signature-section {
            margin-top: 30px;
            text-align: right;
        }
        
        .signature {
            min-width: 260px;
            display: inline-block;
        }
        
        .date-inline {
            margin-bottom: 10px;
            font-style: italic;
        }
        
        .doctor-signature {
            margin-top: 20px;
            min-height: 40px;
            border-bottom: 1px solid #000;
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="margin-bottom:10px;">
            <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:60px;object-fit:contain;">
        </div>
        <div class="title">PHÒNG KHÁM ĐA KHOA THINHVIET<br>KHOA CHUẨN ĐOÁN HÌNH ẢNH</div>
        <div class="subtitle">Địa chỉ: Gò Vấp - Điện thoại: 0777871608</div>
        <div class="hr"></div>
    </div>

    <div class="patient-info">
        <div class="row-line">
            <div class="label">Họ và tên</div>
            <div class="dots">:</div>
            <div class="value"><?php echo htmlspecialchars($data['ho_ten'] ?? ''); ?></div>
        </div>
        <div class="row-line">
            <div class="label">Năm sinh</div>
            <div class="dots">:</div>
            <div class="value"><?php echo htmlspecialchars($data['nam_sinh'] ?? ''); ?></div>
            <div class="label" style="min-width:90px">Giới tính</div>
            <div class="dots">:</div>
            <div class="value"><?php echo htmlspecialchars($data['gioi_tinh'] ?? ''); ?></div>
        </div>
        <div class="row-line">
            <div class="label">Địa chỉ</div>
            <div class="dots">:</div>
            <div class="value"><?php echo htmlspecialchars($data['dia_chi'] ?? ''); ?></div>
        </div>
        <div class="row-line">
            <div class="label">Khoa chỉ định</div>
            <div class="dots">:</div>
            <div class="value"><?php echo htmlspecialchars($data['khoa_chi_dinh'] ?? ''); ?></div>
            <div class="label" style="min-width:120px">Số phiếu chỉ định</div>
            <div class="dots">:</div>
            <div class="value"><?php echo htmlspecialchars($data['id'] ?? ''); ?></div>
        </div>
        <div class="row-line">
            <div class="label">Ngày chỉ định</div>
            <div class="dots">:</div>
            <div class="value"><?php echo isset($data['ngay_cap_nhat']) ? date('d/m/Y', strtotime($data['ngay_cap_nhat'])) : ''; ?></div>
            <div class="label" style="min-width:120px">Giờ chỉ định</div>
            <div class="dots">:</div>
            <div class="value"><?php echo isset($data['ngay_cap_nhat']) ? date('H:i', strtotime($data['ngay_cap_nhat'])) : ''; ?></div>
        </div>
        <div class="row-line">
            <div class="label">Giờ nhận kết quả</div>
            <div class="dots">:</div>
            <div class="value">
                <?php 
                $returnTime = '-';
                // Ưu tiên ngay_doc từ ket_qua_xquang (thời gian lưu kết quả)
                if (!empty($data['ngay_doc'])) {
                    $returnTime = date('H:i:s', strtotime($data['ngay_doc']));
                }
                echo $returnTime;
                ?>
            </div>
        </div>
        <div class="hr"></div>
        <div class="row-line">
            <div class="label">Chẩn đoán</div>
            <div class="dots">:</div>
            <div class="value"><?php echo htmlspecialchars($data['chan_doan_vao_vien'] ?? ''); ?></div>
        </div>
        <div class="row-line">
            <div class="label">Bác sĩ chỉ định</div>
            <div class="dots">:</div>
            <div class="value"><?php echo htmlspecialchars($data['bac_si_chi_dinh'] ?? ''); ?></div>
        </div>
        <div class="row-line">
            <div class="label">Nội dung</div>
            <div class="dots">:</div>
            <div class="value">Chụp X-Quang <?php echo htmlspecialchars($data['yeu_cau_chup'] ?? ''); ?></div>
        </div>
    </div>

    <div class="section">Kết quả</div>
    <div class="result-content">
        <?php echo nl2br(htmlspecialchars($data['noi_dung'] ?? '')); ?>
    </div>

    <div class="section">KẾT LUẬN</div>
    <div class="result-content">
        <?php echo nl2br(htmlspecialchars($data['ket_luan'] ?? '')); ?>
    </div>

    <div class="signature-section">
        <div class="signature">
            <div class="date-inline">
                <em>Ngày <?php echo date('d'); ?> tháng <?php echo date('m'); ?> năm <?php echo date('Y'); ?></em>
            </div>
            <div style="font-weight: bold; margin-top: 20px;">Bác sĩ X Quang</div>
            <div class="doctor-signature">
                <?php echo htmlspecialchars($data['bac_si_xquang'] ?? ''); ?>
            </div>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
