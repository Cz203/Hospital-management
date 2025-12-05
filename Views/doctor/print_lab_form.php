<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Yêu Cầu Xét Nghiệm</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
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
            border: 2px solid #000;
            padding: 10px;
        }
        
        .form-section {
            margin-bottom: 20px;
        }
        
        .section-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 10px;
        }
        
        .form-row {
            display: flex;
            margin-bottom: 8px;
        }
        
        .form-label {
            font-weight: bold;
            min-width: 120px;
            margin-right: 10px;
        }
        
        .form-value {
            border-bottom: 1px solid #000;
            flex: 1;
            min-height: 20px;
            padding: 2px 5px;
        }
        
        .form-value.underline {
            border-bottom: 2px solid #000;
            min-height: 25px;
        }
        
        .signature-section {
            margin-top: 30px;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            width: 200px;
            height: 40px;
            margin: 0 auto 10px;
        }
        .signature-container {
            width: 360px;
            margin-left: auto;
            margin-right: 40px;
            text-align: center;
        }
        .signature-right {
            width: 100%;
            text-align: center;
        }
        
         .date-inline {
             text-align: center;
             margin-top: 20px;
             margin-bottom: 10px;
             font-style: italic;
         }
        
        .date-input {
            text-align: center;
            border-bottom: 1px solid #000;
            min-width: 60px;
            padding: 5px;
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
        <i class="fas fa-print"></i> In phiếu
    </button>

    <div class="header">
        <div style="margin-bottom:10px;">
            <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:70px;object-fit:contain;">
        </div>
        <div class="hospital-name">PHÒNG KHÁM ĐA KHOA THINHVIET</div>
        <div style="font-size: 12px;">Địa chỉ: Gò Vấp, TP.HCM &nbsp;|&nbsp; ĐT: 0777871608</div>
    </div>

    <div class="form-title">PHIẾU YÊU CẦU XÉT NGHIỆM</div>

    <!-- Thông tin bệnh nhân -->
    <div class="form-section">
        <div class="section-title">I. THÔNG TIN BỆNH NHÂN</div>
        
        <div class="form-row">
            <div class="form-label">Mã bệnh nhân:</div>
            <div class="form-value"><?php echo htmlspecialchars($formData['so_ho_so'] ?? ''); ?></div>
            <div class="form-label" style="margin-left: 20px;">Họ và tên:</div>
            <div class="form-value underline"><?php echo htmlspecialchars($formData['ho_ten'] ?? ''); ?></div>
        </div>
        
        <div class="form-row">
            <div class="form-label">Tuổi:</div>
            <div class="form-value"><?php echo htmlspecialchars($formData['tuoi'] ?? ''); ?></div>
            <div class="form-label" style="margin-left: 20px;">Giới tính:</div>
            <div class="form-value"><?php echo htmlspecialchars($formData['gioi_tinh'] ?? 'N/A'); ?></div>
        </div>
        
        <div class="form-row">
            <div class="form-label">Địa chỉ:</div>
            <div class="form-value underline"><?php echo htmlspecialchars($formData['dia_chi'] ?? ''); ?></div>
        </div>
        
        <div class="form-row">
            <div class="form-label">Đối tượng:</div>
            <div class="form-value underline"><?php echo htmlspecialchars($formData['doi_tuong'] ?? ''); ?></div>
            <div class="form-label" style="margin-left: 50px;">Số thẻ BHYT:</div>
            <div class="form-value underline"><?php echo htmlspecialchars($formData['so_the_bhyt'] ?? ''); ?></div>
        </div>
    </div>

    <!-- Thông tin xét nghiệm -->
    <div class="form-section">
        <div class="section-title">II. THÔNG TIN XÉT NGHIỆM</div>
        
        <div class="form-row">
            <div class="form-label">Giờ chỉ định:</div>
            <div class="form-value underline">
                <?php 
                if (!empty($formData['ngay_cap_nhat'])) {
                    $date = new DateTime($formData['ngay_cap_nhat']);
                    echo $date->format('H:i:s');
                } else {
                    echo '';
                }
                ?>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-label">Chẩn đoán:</div>
            <div class="form-value underline"><?php echo htmlspecialchars($formData['chan_doan'] ?? ''); ?></div>
        </div>
        
        <div class="form-row" style="align-items: flex-start;">
            <div class="form-label" style="padding-top: 6px;">Yêu cầu xét nghiệm:</div>
            <div class="form-value underline" style="min-height: 26px; margin-top: 0; padding-top: 0; padding-bottom: 0;"><?php echo nl2br(htmlspecialchars($formData['yeu_cau'] ?? '')); ?></div>
        </div>
    </div>

    <!-- Chữ ký -->
    <div class="signature-section signature-container">
        <div class="date-inline">Ngày <?php echo date('d'); ?> &nbsp;&nbsp; tháng <?php echo date('m'); ?> &nbsp;&nbsp; năm <?php echo date('Y'); ?></div>

        <div class="signature-right">
            <div class="signature-line"></div>
            <div style="font-weight: bold; margin-bottom: 10px;">BÁC SĨ ĐIỀU TRỊ</div>
            <div style="font-weight: bold;"><?php echo htmlspecialchars($formData['bac_si_kham'] ?? ''); ?></div>
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

