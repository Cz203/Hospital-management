<?php
/**
 * View kết quả xét nghiệm cho Patient - PDF-like interface (chỉ kết quả)
 */

$exam = $data['exam'] ?? [];
$labResult = $data['lab_result'] ?? null;

function escapeHtml($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

function formatDate($date) {
    if (!$date) return '-';
    return date('d/m/Y', strtotime($date));
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

// Helper function để check out of range
function lab_is_out_of_range($testName, $resultValue) {
    static $pdo = null;
    static $cache = [];
    
    if (empty($testName) || empty($resultValue)) return false;
    
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
            $cache[$testName] = null;
        }
    }
    
    $range = $cache[$testName];
    if (!$range) return false;
    
    $numeric = is_numeric($resultValue) ? floatval($resultValue) : null;
    if ($numeric === null) return false;
    
    return ($numeric < floatval($range['chi_so_tu']) || $numeric > floatval($range['chi_so_den']));
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết Quả Xét Nghiệm</title>
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
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 10px 0;
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
        
        .form-group input {
            width: 100%;
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 13px;
            font-family: 'Times New Roman', serif;
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
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 8px;
        }
        
        th {
            background-color: #f0f0f0;
            text-align: center;
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
            <?php if ($labResult): ?>
                <?php
                $chiTiet = $labResult['chi_tiet'] ?? [];
                $ngayTao = !empty($labResult['ngay_cap_nhat']) ? new DateTime($labResult['ngay_cap_nhat']) : null;
                // Ngày ĐK lấy từ ngay_dang_ky trong labResult (từ pxn.ngay_tao) hoặc từ labRequest
                $ngayDangKyStr = $labResult['ngay_dang_ky'] ?? $labRequest['ngay_tao'] ?? $labRequest['thoi_gian_yeu_cau'] ?? null;
                $ngayDangKy = !empty($ngayDangKyStr) ? new DateTime($ngayDangKyStr) : null;
                $maBenhNhan = $exam['ma_benh_nhan'] ?? ($labResult['ma_benh_nhan'] ?? '0000000');
                $hoTen = $labResult['ho_ten'] ?? $exam['ho_ten'] ?? '-';
                $tuoi = $labResult['tuoi'] ?? $exam['tuoi'] ?? '-';
                $gioiTinh = $labResult['gioi_tinh'] ?? $exam['gioi_tinh'] ?? '-';
                $diaChi = $labResult['dia_chi'] ?? $exam['dia_chi'] ?? '-';
                $chanDoan = $labResult['chan_doan'] ?? $labResult['chan_doan_so_bo'] ?? $labRequest['chan_doan'] ?? '-';
                $bacSiYeuCau = $labResult['bac_si_yeu_cau'] ?? $labResult['bac_si_kham'] ?? $labRequest['bac_si_kham'] ?? $exam['ten_bac_si'] ?? '-';
                ?>
                
                <!-- Header -->
                <div style="text-align:center; margin-bottom:20px;">
                    <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:60px;object-fit:contain;">
                </div>
                <div style="text-align:center; margin-bottom:15px;">
                    <div style="font-size: 18px; font-weight: bold; color: #000; margin-bottom: 5px;">PHÒNG KHÁM ĐA KHOA THINHVIET</div>
                    <div style="font-size: 14px; font-weight: bold; margin-bottom: 10px;">KHOA XÉT NGHIỆM</div>
                    <div style="font-size: 16px; font-weight: bold; color: #dc3545; margin-bottom: 10px;">KẾT QUẢ XÉT NGHIỆM</div>
                    <div style="display:flex; justify-content:center; gap: 10px; font-size: 12px;">
                        <span>Ngày ĐK: <span><?php echo $ngayDangKy ? $ngayDangKy->format('d/m/Y') : '-'; ?></span></span>
                        <span>|</span>
                        <span><?php echo $ngayDangKy ? $ngayDangKy->format('H:i') : '-'; ?></span>
                    </div>
                </div>
                <hr style="border-top: 2px solid #000; margin: 15px 0;">
                
                <!-- Patient Information - 2 columns layout -->
                <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                    <!-- Left Column -->
                    <div style="flex: 1;">
                        <div style="display: flex; margin-bottom: 12px; align-items: center;">
                            <div style="min-width: 140px; font-weight: bold; font-size: 14px;">Mã bệnh nhân:</div>
                            <div style="flex: 1; border-bottom: 1px solid #000; padding-bottom: 3px; min-height: 20px; font-size: 14px;"><?php echo escapeHtml($maBenhNhan); ?></div>
                        </div>
                        <div style="display: flex; margin-bottom: 12px; align-items: center;">
                            <div style="min-width: 140px; font-weight: bold; font-size: 14px;">Họ và tên:</div>
                            <div style="flex: 1; border-bottom: 1px solid #000; padding-bottom: 3px; min-height: 20px; font-size: 14px;"><?php echo escapeHtml($hoTen); ?></div>
                        </div>
                        <div style="display: flex; margin-bottom: 12px; align-items: center;">
                            <div style="min-width: 140px; font-weight: bold; font-size: 14px;">Địa chỉ:</div>
                            <div style="flex: 1; border-bottom: 1px solid #000; padding-bottom: 3px; min-height: 20px; font-size: 14px;"><?php echo escapeHtml($diaChi); ?></div>
                        </div>
                        <div style="display: flex; margin-bottom: 12px; align-items: center;">
                            <div style="min-width: 140px; font-weight: bold; font-size: 14px;">Chẩn đoán sơ bộ:</div>
                            <div style="flex: 1; border-bottom: 1px solid #000; padding-bottom: 3px; min-height: 20px; font-size: 14px;"><?php echo escapeHtml($chanDoan); ?></div>
                        </div>
                        <div style="display: flex; margin-bottom: 12px; align-items: center;">
                            <div style="min-width: 140px; font-weight: bold; font-size: 14px;">Vị trí lấy mẫu:</div>
                            <div style="flex: 1; border-bottom: 1px solid #000; padding-bottom: 3px; min-height: 20px; font-size: 14px;"><?php echo escapeHtml($labResult['vi_tri_lay_mau'] ?? '-'); ?></div>
                        </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div style="flex: 1;">
                        <div style="display: flex; margin-bottom: 12px; align-items: center;">
                            <div style="min-width: 140px; font-weight: bold; font-size: 14px;">Tuổi:</div>
                            <div style="flex: 1; border-bottom: 1px solid #000; padding-bottom: 3px; min-height: 20px; font-size: 14px;"><?php echo escapeHtml($tuoi); ?></div>
                        </div>
                        <div style="display: flex; margin-bottom: 12px; align-items: center;">
                            <div style="min-width: 140px; font-weight: bold; font-size: 14px;">Giới tính:</div>
                            <div style="flex: 1; border-bottom: 1px solid #000; padding-bottom: 3px; min-height: 20px; font-size: 14px;"><?php echo escapeHtml($gioiTinh); ?></div>
                        </div>
                        <div style="display: flex; margin-bottom: 12px; align-items: center;">
                            <div style="min-width: 140px; font-weight: bold; font-size: 14px;">BS yêu cầu:</div>
                            <div style="flex: 1; border-bottom: 1px solid #000; padding-bottom: 3px; min-height: 20px; font-size: 14px;"><?php echo escapeHtml($bacSiYeuCau); ?></div>
                        </div>
                        <div style="display: flex; margin-bottom: 12px; align-items: center;">
                            <div style="min-width: 140px; font-weight: bold; font-size: 14px;">Giờ nhận kết quả:</div>
                            <div style="flex: 1; border-bottom: 1px solid #000; padding-bottom: 3px; min-height: 20px; font-size: 14px;"><?php echo formatTime($labResult['ngay_cap_nhat']); ?></div>
                        </div>
                        <div style="display: flex; margin-bottom: 12px; align-items: center;">
                            <div style="min-width: 140px; font-weight: bold; font-size: 14px;">Chất lượng mẫu:</div>
                            <div style="flex: 1; border-bottom: 1px solid #000; padding-bottom: 3px; min-height: 20px; font-size: 14px;"><?php echo escapeHtml($labResult['tinh_trang_mau'] ?? '-'); ?></div>
                        </div>
                    </div>
                </div>
                    
                    <!-- Yêu cầu xét nghiệm -->
                    <div style="margin-top: 20px; margin-bottom: 15px;">
                        <div style="font-weight: bold; font-size: 16px; text-align: center;">
                            <?php echo escapeHtml($labResult['yeu_cau'] ?? $labRequest['yeu_cau'] ?? 'CHƯA CÓ YÊU CẦU XÉT NGHIỆM'); ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($chiTiet)): ?>
                        <div style="margin-top: 20px;">
                            <div style="text-align: center; font-weight: bold; font-size: 16px; margin-bottom: 15px;">
                                BẢNG KẾT QUẢ XÉT NGHIỆM
                            </div>
                            
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">STT</th>
                                        <th>Xét nghiệm</th>
                                        <th>Giá trị tham chiếu</th>
                                        <th style="font-weight: bold;">Kết quả</th>
                                        <th>Đơn vị</th>
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
                                            $resultStyle = $isOutOfRange || $isPositive ? 'font-weight: bold; color: #dc3545; text-align: right;' : 'text-align: center;';
                                        ?>
                                        <tr>
                                            <td style="text-align: center;"><?php echo $item['stt'] ?? ($idx + 1); ?></td>
                                            <td><?php echo escapeHtml($tenXetNghiem ?: '-'); ?></td>
                                            <td style="text-align: center;"><?php echo escapeHtml($item['gia_tri_tham_chieu'] ?? '-'); ?></td>
                                            <td style="<?php echo $resultStyle; ?>"><?php echo escapeHtml($ketQua ?: '-'); ?></td>
                                            <td style="text-align: center;"><?php echo escapeHtml($item['don_vi'] ?? '-'); ?></td>
                                            <td><?php echo escapeHtml($item['may_qtkt'] ?? '-'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            
                            <div style="margin-top: 15px; font-size: 11px;">
                                <div>Ghi chú: Kết quả in đậm là kết quả nằm ngoài khoảng tham chiếu.</div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Footer Signature -->
                    <div style="margin-top: 40px; display: flex;">
                        <div style="flex: 1;">
                            <!-- Trống -->
                        </div>
                        <div style="flex: 1;">
                            <div style="text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 20px;">
                                Ngày <span><?php echo $ngayTao ? $ngayTao->format('d') : '-'; ?></span> tháng <span><?php echo $ngayTao ? $ngayTao->format('m') : '-'; ?></span> năm <span><?php echo $ngayTao ? $ngayTao->format('Y') : '-'; ?></span>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-weight: bold; font-size: 14px; margin-bottom: 10px;">BÁC SĨ XÉT NGHIỆM</div>
                                <div style="font-size: 14px;">
                                    <?php 
                                    // Ưu tiên lấy từ labResult (đã JOIN với bac_si), nếu không có thì lấy từ labRequest
                                    $tenBacSiXetNghiem = $labResult['ten_bac_si_xet_nghiem'] ?? $labRequest['ten_bac_si_xet_nghiem'] ?? '-';
                                    echo escapeHtml($tenBacSiXetNghiem);
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <p>Chưa có kết quả xét nghiệm</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>

