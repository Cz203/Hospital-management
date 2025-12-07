<?php
/**
 * View kết quả siêu âm cho Patient - PDF-like interface (chỉ kết quả)
 */

$exam = $data['exam'] ?? [];
$ultrasoundRequest = $data['ultrasound_request'] ?? null;
$ultrasoundResult = $data['ultrasound_result'] ?? null;

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
    <title>Kết Quả Siêu Âm</title>
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
        
        .conclusion-content {
            border: 1px solid #333;
            padding: 15px;
            min-height: 80px;
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
            <?php if ($ultrasoundResult): ?>
                <?php
                $ngayTao = !empty($ultrasoundResult['ngay_cap_nhat']) ? new DateTime($ultrasoundResult['ngay_cap_nhat']) : null;
                // Ngày ĐK lấy từ ngay_tao của phieu_yeu_cau_sieu_am
                $ngayDangKyStr = $ultrasoundResult['ngay_tao'] ?? $ultrasoundRequest['ngay_tao'] ?? null;
                $ngayDangKy = !empty($ngayDangKyStr) ? new DateTime($ngayDangKyStr) : null;
                $maBenhNhan = $ultrasoundResult['ma_benh_nhan'] ?? $exam['ma_benh_nhan'] ?? '0000000';
                $hoTen = $ultrasoundResult['ho_ten'] ?? $exam['ho_ten'] ?? '-';
                $tuoi = $ultrasoundResult['tuoi'] ?? $exam['tuoi'] ?? '-';
                $gioiTinh = $ultrasoundResult['gioi_tinh'] ?? $exam['gioi_tinh'] ?? '-';
                $diaChi = $ultrasoundResult['dia_chi'] ?? $exam['dia_chi'] ?? '-';
                $chanDoan = $ultrasoundResult['chan_doan'] ?? $ultrasoundRequest['chan_doan'] ?? $exam['chan_doan_vao_vien'] ?? '-';
                $bacSiChiDinh = $ultrasoundResult['ten_bac_si'] ?? $ultrasoundRequest['bac_si_kham'] ?? $exam['ten_bac_si'] ?? '-';
                $phieuChiDinh = $ultrasoundResult['phieu_id'] ?? $ultrasoundRequest['id'] ?? '-';
                $vungKhaoSat = $ultrasoundResult['yeu_cau_sieu_am'] ?? $ultrasoundRequest['yeu_cau'] ?? 'SIÊU ÂM BỤNG TỔNG QUÁT MÀU';
                ?>
                
                <!-- Header -->
                <div style="text-align:center; margin-bottom:20px;">
                    <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:60px;object-fit:contain;margin-bottom:10px;">
                    <div style="font-size: 18px; font-weight: bold; color: #333; margin-bottom: 5px;">PHÒNG KHÁM ĐA KHOA THINHVIET</div>
                    <div style="font-size: 16px; font-weight: bold; color: #666; margin-bottom: 10px;">KHOA CHẨN ĐOÁN HÌNH ẢNH</div>
                </div>
                <div style="text-align:center; font-size: 20px; font-weight: bold; margin-bottom: 15px;">KẾT QUẢ SIÊU ÂM</div>
                
                <div style="text-align: center; margin-bottom: 15px;">
                    <div style="font-weight: bold;">Máy: Medison Sonoace X6</div>
                </div>
                
                <!-- Row-line format giống examination_modal.php -->
                <div style="display: flex; gap: 8px; margin-bottom: 6px; font-size: 15px; align-items: center;">
                    <div style="min-width: 150px; font-weight: bold;">ID:</div>
                    <div style="flex: 0 0 auto;">:</div>
                    <div style="flex: 1; border-bottom: 1px dotted #333; min-height: 20px; font-size: 15px;">*<?php echo escapeHtml($maBenhNhan); ?>*</div>
                    <div style="min-width: 150px; font-weight: bold; margin-left: 20px;">Ngày ĐK:</div>
                    <div style="flex: 0 0 auto;">:</div>
                    <div style="flex: 1; border-bottom: 1px dotted #333; min-height: 20px; font-size: 15px;"><?php echo $ngayDangKy ? $ngayDangKy->format('d/m/Y') : '-'; ?></div>
                    <div style="flex: 0 0 auto;">-</div>
                    <div style="flex: 1; border-bottom: 1px dotted #333; min-height: 20px; font-size: 15px;"><?php echo $ngayDangKy ? $ngayDangKy->format('H:i') : '-'; ?></div>
                </div>
                
                <div style="border-top: 2px solid #000; margin: 10px 0;"></div>
                
                <div style="display: flex; gap: 8px; margin-bottom: 6px; font-size: 15px; align-items: center;">
                    <div style="min-width: 150px; font-weight: bold;">Họ tên:</div>
                    <div style="flex: 0 0 auto;">:</div>
                    <div style="flex: 1; border-bottom: 1px dotted #333; min-height: 20px; font-size: 15px;"><?php echo escapeHtml($hoTen); ?></div>
                </div>
                
                <div style="display: flex; gap: 8px; margin-bottom: 6px; font-size: 15px; align-items: center;">
                    <div style="min-width: 150px; font-weight: bold;">Tuổi:</div>
                    <div style="flex: 0 0 auto;">:</div>
                    <div style="flex: 1; border-bottom: 1px dotted #333; min-height: 20px; font-size: 15px;"><?php echo escapeHtml($tuoi); ?></div>
                    <div style="min-width: 150px; font-weight: bold; margin-left: 20px;">Giới:</div>
                    <div style="flex: 0 0 auto;">:</div>
                    <div style="flex: 1; border-bottom: 1px dotted #333; min-height: 20px; font-size: 15px;"><?php echo escapeHtml($gioiTinh); ?></div>
                </div>
                
                <div style="display: flex; gap: 8px; margin-bottom: 6px; font-size: 15px; align-items: center;">
                    <div style="min-width: 150px; font-weight: bold;">Địa chỉ:</div>
                    <div style="flex: 0 0 auto;">:</div>
                    <div style="flex: 1; border-bottom: 1px dotted #333; min-height: 20px; font-size: 15px;"><?php echo escapeHtml($diaChi); ?></div>
                </div>
                
                <div style="display: flex; gap: 8px; margin-bottom: 6px; font-size: 15px; align-items: center;">
                    <div style="min-width: 150px; font-weight: bold;">Chẩn đoán sơ bộ:</div>
                    <div style="flex: 0 0 auto;">:</div>
                    <div style="flex: 1; border-bottom: 1px dotted #333; min-height: 20px; font-size: 15px;"><?php echo escapeHtml($chanDoan); ?></div>
                </div>
                
                <div style="display: flex; gap: 8px; margin-bottom: 6px; font-size: 15px; align-items: center;">
                    <div style="min-width: 150px; font-weight: bold;">Bác sĩ chỉ định:</div>
                    <div style="flex: 0 0 auto;">:</div>
                    <div style="flex: 1; border-bottom: 1px dotted #333; min-height: 20px; font-size: 15px;"><?php echo escapeHtml($bacSiChiDinh); ?></div>
                </div>
                
                <div style="display: flex; gap: 8px; margin-bottom: 6px; font-size: 15px; align-items: center;">
                    <div style="min-width: 150px; font-weight: bold;">Phiếu chỉ định:</div>
                    <div style="flex: 0 0 auto;">:</div>
                    <div style="flex: 1; border-bottom: 1px dotted #333; min-height: 20px; font-size: 15px;"><?php echo escapeHtml($phieuChiDinh); ?></div>
                </div>
                
                <div style="display: flex; gap: 8px; margin-bottom: 6px; font-size: 15px; align-items: center;">
                    <div style="min-width: 150px; font-weight: bold;">Giờ nhận kết quả:</div>
                    <div style="flex: 0 0 auto;">:</div>
                    <div style="flex: 1; border-bottom: 1px dotted #333; min-height: 20px; font-size: 15px;"><?php echo formatTime($ultrasoundResult['ngay_cap_nhat']); ?></div>
                </div>
                
                <div style="border-top: 2px solid #000; margin: 10px 0;"></div>
                
                <div style="display: flex; gap: 8px; margin-bottom: 6px; font-size: 15px; align-items: center;">
                    <div style="min-width: 150px; font-weight: bold;">Vùng khảo sát:</div>
                    <div style="flex: 0 0 auto;">:</div>
                    <div style="flex: 1; border-bottom: 1px dotted #333; min-height: 20px; font-size: 15px;"><?php echo escapeHtml($vungKhaoSat); ?></div>
                </div>
                
                <div style="border-top: 2px solid #000; margin: 10px 0;"></div>
                
                <div style="margin-top: 10px; margin-bottom: 6px; font-weight: bold; text-transform: uppercase; color: blue; font-size: 15px;">KẾT QUẢ KHẢO SÁT:</div>
                <div style="border: 1px solid #333; padding: 10px; min-height: 100px; white-space: pre-wrap; font-size: 15px;"><?php echo escapeHtml($ultrasoundResult['ket_qua_khao_sat'] ?? '-'); ?></div>
                
                <!-- Hình ảnh siêu âm -->
                <?php
                $images = $ultrasoundResult['hinh_anh'] ?? [];
                if (!empty($images) && is_array($images)):
                ?>
                    <div style="border-top: 2px solid #000; margin: 20px 0;"></div>
                    <div style="margin-top: 10px; margin-bottom: 6px; font-weight: bold; text-transform: uppercase; color: blue; font-size: 15px;">HÌNH ẢNH SIÊU ÂM:</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 15px;">
                        <?php foreach ($images as $img): ?>
                            <?php if (!empty($img['duong_dan'])): ?>
                                <div style="flex: 0 0 calc(33.333% - 10px);">
                                    <img src="<?php echo escapeHtml($img['duong_dan']); ?>" alt="Hình siêu âm" class="zoomable-image" onclick="openImageZoom('<?php echo escapeHtml($img['duong_dan']); ?>')" style="width: 100%; height: auto; border: 1px solid #ddd;">
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div style="margin-top: 10px; margin-bottom: 6px; font-weight: bold; text-transform: uppercase; color: blue; font-size: 15px;">KẾT LUẬN:</div>
                <div style="border: 1px solid #333; padding: 10px; min-height: 60px; white-space: pre-wrap; font-size: 15px;"><?php echo escapeHtml($ultrasoundResult['ket_luan'] ?? '-'); ?></div>
                
                <div style="border-top: 2px solid #000; margin: 10px 0;"></div>
                
                <!-- Footer Signature -->
                <div style="margin-top: 30px; text-align: right;">
                    <div style="margin-bottom: 15px;">
                        <span>Ngày</span>
                        <span style="display: inline-block; width: 60px; margin: 0 5px; border-bottom: 1px solid #000; text-align: center;"><?php echo $ngayTao ? $ngayTao->format('d') : '-'; ?></span>
                        <span>tháng</span>
                        <span style="display: inline-block; width: 60px; margin: 0 5px; border-bottom: 1px solid #000; text-align: center;"><?php echo $ngayTao ? $ngayTao->format('m') : '-'; ?></span>
                        <span>năm</span>
                        <span style="display: inline-block; width: 80px; margin: 0 5px; border-bottom: 1px solid #000; text-align: center;"><?php echo $ngayTao ? $ngayTao->format('Y') : '-'; ?></span>
                    </div>
                    <div style="margin-right: 20px;">
                        <div style="font-weight: bold; font-size: 14px;">BÁC SĨ SIÊU ÂM</div>
                        <div style="margin-top: 10px; font-size: 14px;"><?php echo escapeHtml($ultrasoundResult['bac_si_sieu_am'] ?? '-'); ?></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <p>Chưa có kết quả siêu âm</p>
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

