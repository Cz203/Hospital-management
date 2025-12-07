<?php
/**
 * View kết quả X-Quang cho Patient - PDF-like interface (chỉ kết quả)
 */

$exam = $data['exam'] ?? [];
$xrayRequest = $data['xray_request'] ?? null;
$xrayResult = $data['xray_result'] ?? null;

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
    <title>Kết Quả X-Quang</title>
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
            color: #dc3545;
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
        
        .result-content {
            border: 1px solid #333;
            padding: 15px;
            min-height: 150px;
            white-space: pre-wrap;
            font-size: 13px;
            margin-top: 10px;
        }
        
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .image-item img {
            width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .hr {
            border-top: 2px solid #000;
            margin: 15px 0;
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
        
        /* Image Zoom Modal Styles */
        .image-zoom-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            cursor: zoom-out;
        }
        
        .image-zoom-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .image-zoom-content {
            position: relative;
            max-width: 95%;
            max-height: 95%;
            margin: auto;
        }
        
        .image-zoom-content img {
            max-width: 100%;
            max-height: 95vh;
            object-fit: contain;
            border-radius: 4px;
        }
        
        .image-zoom-close {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            z-index: 10001;
        }
        
        .image-zoom-close:hover {
            color: #ccc;
        }
        
        .zoomable-image {
            cursor: zoom-in;
            transition: transform 0.2s;
        }
        
        .zoomable-image:hover {
            opacity: 0.9;
            transform: scale(1.02);
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
            <?php if ($xrayResult): ?>
                <?php
                $ngayNhanKetQua = null;
                $gioNhanKetQua = '-';
                if (!empty($xrayResult['ngay_doc'])) {
                    try {
                        $ngayNhanKetQua = new DateTime($xrayResult['ngay_doc']);
                        $gioNhanKetQua = $ngayNhanKetQua->format('H:i:s');
                    } catch (Exception $e) {
                        $ngayNhanKetQua = null;
                    }
                } elseif (!empty($xrayResult['ngay_cap_nhat'])) {
                    try {
                        $ngayNhanKetQua = new DateTime($xrayResult['ngay_cap_nhat']);
                        $gioNhanKetQua = $ngayNhanKetQua->format('H:i:s');
                    } catch (Exception $e) {
                        $ngayNhanKetQua = null;
                    }
                }
                $ngayNhanKetQuaStr = $ngayNhanKetQua ? $ngayNhanKetQua->format('d/m/Y') : '-';
                
                $gioChiDinh = '-';
                if ($xrayRequest && !empty($xrayRequest['ngay_cap_nhat'])) {
                    $gioChiDinh = formatTime($xrayRequest['ngay_cap_nhat']);
                } elseif ($xrayRequest && !empty($xrayRequest['ngay_tao'])) {
                    $gioChiDinh = formatTime($xrayRequest['ngay_tao']);
                }
                
                $namSinh = $exam['nam_sinh'] ?? '-';
                if (!empty($exam['ngay_sinh_benh_nhan'])) {
                    try {
                        $namSinh = (new DateTime($exam['ngay_sinh_benh_nhan']))->format('Y');
                    } catch (Exception $e) {
                        $namSinh = $exam['nam_sinh'] ?? '-';
                    }
                }
                ?>
                
                <style>
                    .xray-report {
                        font-family: "Times New Roman", serif;
                        padding: 18px;
                    }
                    
                    .xray-report .title {
                        font-weight: bold;
                        text-transform: uppercase;
                        text-align: center;
                        letter-spacing: .5px;
                        font-size: 18px;
                        margin-bottom: 6px;
                        color: red;
                    }
                    
                    .xray-report .hr {
                        border-top: 2px solid #000;
                        margin: 10px 0;
                    }
                    
                    .xray-report .row-line {
                        display: flex;
                        gap: 8px;
                        margin-bottom: 6px;
                        font-size: 15px;
                    }
                    
                    .xray-report .label {
                        min-width: 150px;
                        font-weight: bold;
                    }
                    
                    .xray-report .dots {
                        flex: 0 0 auto;
                    }
                    
                    .xray-report .value {
                        flex: 1;
                        border-bottom: 1px dotted #333;
                        min-height: 20px;
                    }
                    
                    .xray-report .section {
                        margin-top: 10px;
                        margin-bottom: 6px;
                        font-weight: bold;
                        text-transform: uppercase;
                        color: blue;
                    }
                    
                    .xray-report .result-content {
                        border: 1px solid #333;
                        padding: 10px;
                        min-height: 100px;
                        white-space: pre-wrap;
                    }
                    
                    .xray-report .conclusion-content {
                        border: 1px solid #333;
                        padding: 10px;
                        min-height: 60px;
                        white-space: pre-wrap;
                    }
                </style>
                
                <div class="xray-report">
                    <div class="text-center mb-3" style="text-align: center;">
                        <div style="text-align: center; margin-bottom: 10px;">
                            <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:60px;object-fit:contain;margin: 0 auto; display: block;">
                        </div>
                        <div class="fw-bold" style="font-size: 18px; color: #333; text-align: center;">PHÒNG KHÁM ĐA KHOA THINHVIET</div>
                        <div class="fw-bold" style="font-size: 16px; color: #666; text-align: center;">KHOA CHUẨN ĐOÁN HÌNH ẢNH</div>
                    </div>
                    <div class="title">KẾT QUẢ X-QUANG</div>
                    
                    <div class="text-center mb-2" style="text-align: center;">
                        <div class="fw-bold" style="text-align: center;">Địa chỉ: Gò Vấp - Điện thoại: 0777871608</div>
                    </div>
                    
                    <div class="row-line">
                        <div class="label">Họ và tên</div>
                        <div class="dots">:</div>
                        <div class="value"><?php echo escapeHtml($exam['ho_ten'] ?? '-'); ?></div>
                    </div>
                    
                    <div class="row-line">
                        <div class="label">Năm sinh</div>
                        <div class="dots">:</div>
                        <div class="value"><?php echo escapeHtml($namSinh); ?></div>
                        <div class="label" style="min-width:90px; margin-left: 20px;">Giới tính</div>
                        <div class="dots">:</div>
                        <div class="value"><?php echo escapeHtml($exam['gioi_tinh'] ?? '-'); ?></div>
                    </div>
                    
                    <div class="row-line">
                        <div class="label">Địa chỉ</div>
                        <div class="dots">:</div>
                        <div class="value"><?php echo escapeHtml($exam['dia_chi'] ?? '-'); ?></div>
                    </div>
                    
                    <div class="row-line">
                        <div class="label">Khoa chỉ định</div>
                        <div class="dots">:</div>
                        <div class="value">KHOA CHUẨN ĐOÁN HÌNH ẢNH</div>
                        <div class="label" style="min-width:120px; margin-left: 20px;">Số phiếu chỉ định</div>
                        <div class="dots">:</div>
                        <div class="value"><?php echo escapeHtml($xrayRequest['id'] ?? '-'); ?></div>
                    </div>
                    
                    <?php
                    $ngayChiDinh = null;
                    $ngayChiDinhStr = '-';
                    $gioChiDinhStr = '-';
                    if ($xrayRequest && !empty($xrayRequest['ngay_cap_nhat'])) {
                        try {
                            $ngayChiDinh = new DateTime($xrayRequest['ngay_cap_nhat']);
                            $ngayChiDinhStr = $ngayChiDinh->format('d/m/Y');
                            $gioChiDinhStr = $ngayChiDinh->format('H:i');
                        } catch (Exception $e) {}
                    } elseif ($xrayRequest && !empty($xrayRequest['ngay_tao'])) {
                        try {
                            $ngayChiDinh = new DateTime($xrayRequest['ngay_tao']);
                            $ngayChiDinhStr = $ngayChiDinh->format('d/m/Y');
                            $gioChiDinhStr = $ngayChiDinh->format('H:i');
                        } catch (Exception $e) {}
                    }
                    ?>
                    
                    <div class="row-line">
                        <div class="label">Ngày chỉ định</div>
                        <div class="dots">:</div>
                        <div class="value"><?php echo escapeHtml($ngayChiDinhStr); ?></div>
                        <div class="label" style="min-width:120px; margin-left: 20px;">Giờ chỉ định</div>
                        <div class="dots">:</div>
                        <div class="value"><?php echo escapeHtml($gioChiDinhStr); ?></div>
                    </div>
                    
                    <div class="row-line">
                        <div class="label">Giờ nhận kết quả</div>
                        <div class="dots">:</div>
                        <div class="value"><?php echo escapeHtml($gioNhanKetQua); ?></div>
                    </div>
                    
                    <div class="hr"></div>
                    
                    <div class="row-line">
                        <div class="label">Chẩn đoán</div>
                        <div class="dots">:</div>
                        <div class="value"><?php echo escapeHtml($xrayRequest['chan_doan_vao_vien'] ?? $exam['chan_doan_vao_vien'] ?? '-'); ?></div>
                    </div>
                    
                    <div class="row-line">
                        <div class="label">Bác sĩ chỉ định</div>
                        <div class="dots">:</div>
                        <div class="value"><?php echo escapeHtml($xrayRequest['bac_si_kham'] ?? $exam['ten_bac_si'] ?? '-'); ?></div>
                    </div>
                    
                    <div class="row-line">
                        <div class="label">Nội dung</div>
                        <div class="dots">:</div>
                        <div class="value"><?php echo escapeHtml($xrayRequest['yeu_cau_chup'] ? ('Chụp X-Quang ' . $xrayRequest['yeu_cau_chup']) : '-'); ?></div>
                    </div>
                    
                    <div class="hr"></div>
                    
                    <div class="section">KẾT QUẢ:</div>
                    <div class="result-content"><?php echo escapeHtml($xrayResult['noi_dung'] ?? '-'); ?></div>
                    
                    <div class="section">KẾT LUẬN:</div>
                    <div class="conclusion-content"><?php echo escapeHtml($xrayResult['ket_luan'] ?? '-'); ?></div>
                    
                    <div style="margin-top: 30px; text-align: right;">
                        <div style="display: inline-block; text-align: center;">
                            <div style="margin-bottom: 15px;">
                                <span>Ngày</span>
                                <span style="display: inline-block; width: 60px; margin: 0 5px; border-bottom: 1px solid #000; text-align: center;"><?php echo $ngayNhanKetQua ? $ngayNhanKetQua->format('d') : '-'; ?></span>
                                <span>tháng</span>
                                <span style="display: inline-block; width: 60px; margin: 0 5px; border-bottom: 1px solid #000; text-align: center;"><?php echo $ngayNhanKetQua ? $ngayNhanKetQua->format('m') : '-'; ?></span>
                                <span>năm</span>
                                <span style="display: inline-block; width: 80px; margin: 0 5px; border-bottom: 1px solid #000; text-align: center;"><?php echo $ngayNhanKetQua ? $ngayNhanKetQua->format('Y') : '-'; ?></span>
                            </div>
                            <div>
                                <div style="font-weight: bold; font-size: 14px;">BÁC SĨ X QUANG</div>
                                <div style="margin-top: 10px; font-size: 14px;"><?php echo escapeHtml($xrayResult['bac_si_xquang'] ?? '-'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <p>Chưa có kết quả X-Quang</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Image Zoom Modal -->
    <div id="imageZoomModal" class="image-zoom-modal" onclick="closeImageZoom()">
        <span class="image-zoom-close" onclick="closeImageZoom()">&times;</span>
        <div class="image-zoom-content" onclick="event.stopPropagation()">
            <img id="zoomedImage" src="" alt="Zoomed Image">
        </div>
    </div>
    
    <script>
        function openImageZoom(imageSrc) {
            var modal = document.getElementById('imageZoomModal');
            var img = document.getElementById('zoomedImage');
            img.src = imageSrc;
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeImageZoom() {
            var modal = document.getElementById('imageZoomModal');
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageZoom();
            }
        });
    </script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>

