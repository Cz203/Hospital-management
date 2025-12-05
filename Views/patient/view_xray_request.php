<?php
/**
 * View phiếu chỉ định X-Quang cho Patient - PDF-like interface (chỉ phiếu yêu cầu)
 */

$exam = $data['exam'] ?? [];
$xrayRequest = $data['xray_request'] ?? null;

function escapeHtml($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

function formatTime($time) {
    if (!$time) return '-';
    try {
        $date = new DateTime($time);
        return $date->format('H:i:s');
    } catch (Exception $e) {
        return '-';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Chỉ Định Chụp X-Quang</title>
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
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-header img {
            height: 60px;
            object-fit: contain;
            margin-bottom: 10px;
        }
        
        .form-title {
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 10px 0;
            text-align: center;
        }
        
        .form-section {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 12px;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-group label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            font-size: 13px;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 13px;
            font-family: 'Times New Roman', serif;
        }
        
        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }
        
        .full-width {
            width: 100%;
        }
        
        .signature-section {
            margin-top: 40px;
            text-align: right;
        }
        
        .signature-box {
            display: inline-block;
            min-width: 200px;
            text-align: center;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 5px;
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
            <?php if ($xrayRequest): ?>
                <div class="form-header">
                    <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo">
                    <div class="form-title">PHIẾU CHỤP X – QUANG</div>
                </div>
                
                <div class="form-section">
                    <div class="form-row">
                        <div class="form-group" style="flex: 0 0 50%;">
                            <label>Cơ sở y tế</label>
                            <input type="text" value="Thịnh Việt" readonly>
                        </div>
                        <div class="form-group" style="flex: 0 0 30%;">
                            <label>Điện thoại</label>
                            <input type="text" value="0777871608" readonly>
                        </div>
                        <div class="form-group" style="flex: 0 0 20%;">
                            <label>Quận</label>
                            <input type="text" value="Gò Vấp" readonly>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group" style="flex: 0 0 15%;">
                            <label>Mã bệnh nhân</label>
                            <input type="text" value="<?php echo escapeHtml($exam['ma_benh_nhan'] ?? '-'); ?>" readonly>
                        </div>
                        <div class="form-group" style="flex: 0 0 45%;">
                            <label>Họ tên người bệnh</label>
                            <input type="text" value="<?php echo escapeHtml($exam['ho_ten'] ?? '-'); ?>" readonly>
                        </div>
                        <div class="form-group" style="flex: 0 0 15%;">
                            <label>Tuổi</label>
                            <input type="text" value="<?php echo escapeHtml($exam['tuoi'] ?? '-'); ?>" readonly>
                        </div>
                        <div class="form-group" style="flex: 0 0 15%;">
                            <label>Nam/Nữ</label>
                            <input type="text" value="<?php echo escapeHtml($exam['gioi_tinh'] ?? '-'); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label>Địa chỉ</label>
                            <input type="text" value="<?php echo escapeHtml($exam['dia_chi'] ?? '-'); ?>" readonly>
                        </div>
                    </div>
                    
                    <?php
                    $doiTuong = 'Khác';
                    if (!empty($exam['doi_tuong_bhyt']) && $exam['doi_tuong_bhyt'] == 1) {
                        $doiTuong = 'BHYT';
                    } elseif (!empty($exam['doi_tuong_thu_phi']) && $exam['doi_tuong_thu_phi'] == 1) {
                        $doiTuong = 'Thu phí';
                    } elseif (!empty($exam['doi_tuong_mien']) && $exam['doi_tuong_mien'] == 1) {
                        $doiTuong = 'Miễn';
                    }
                    ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Đối tượng</label>
                            <input type="text" value="<?php echo escapeHtml($doiTuong); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Số thẻ BHYT</label>
                            <input type="text" value="<?php echo escapeHtml($exam['so_the_bhyt'] ?? '-'); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Giờ chỉ định:</label>
                            <input type="text" value="<?php 
                                $gioChiDinh = '-';
                                if (!empty($xrayRequest['ngay_cap_nhat'])) {
                                    $gioChiDinh = formatTime($xrayRequest['ngay_cap_nhat']);
                                } elseif (!empty($xrayRequest['ngay_tao'])) {
                                    $gioChiDinh = formatTime($xrayRequest['ngay_tao']);
                                }
                                echo escapeHtml($gioChiDinh);
                            ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label>Chuẩn đoán:</label>
                            <input type="text" value="<?php echo escapeHtml($xrayRequest['chan_doan_vao_vien'] ?? $exam['chan_doan_vao_vien'] ?? '-'); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label style="text-align: center; font-size: 16px;">YÊU CẦU CHỤP</label>
                            <textarea readonly><?php echo escapeHtml($xrayRequest['yeu_cau_chup'] ?? '-'); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="signature-section">
                        <div class="signature-box">
                            <div style="margin-bottom: 10px;">
                                <span>Ngày <?php 
                                    $ngayTao = !empty($xrayRequest['ngay_tao']) ? new DateTime($xrayRequest['ngay_tao']) : null;
                                    echo $ngayTao ? $ngayTao->format('d') : '';
                                ?> tháng <?php echo $ngayTao ? $ngayTao->format('m') : ''; ?> năm <?php echo $ngayTao ? $ngayTao->format('Y') : ''; ?></span>
                            </div>
                            <div style="font-weight: bold; margin-bottom: 5px;">BÁC SĨ ĐIỀU TRỊ</div>
                            <div class="signature-line">
                                <?php echo escapeHtml($xrayRequest['bac_si_kham'] ?? $exam['ten_bac_si'] ?? '-'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <p>Chưa có phiếu chỉ định chụp X-quang</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>

