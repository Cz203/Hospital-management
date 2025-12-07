<?php
/**
 * View hình ảnh X-Quang cho Patient
 */

$exam = $data['exam'] ?? [];
$xrayResult = $data['xray_result'] ?? null;
$xrayImages = $data['xray_images'] ?? [];

function escapeHtml($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hình Ảnh X-Quang</title>
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
            max-width: 1400px;
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
            min-height: 600px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header img {
            height: 60px;
            object-fit: contain;
            margin-bottom: 15px;
        }
        
        .header h1 {
            font-size: 20px;
            font-weight: bold;
            color: #000;
            margin-bottom: 10px;
        }
        
        .patient-info {
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .patient-info-row {
            display: flex;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .patient-info-row:last-child {
            margin-bottom: 0;
        }
        
        .patient-info-label {
            font-weight: bold;
            min-width: 120px;
        }
        
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .image-item {
            border: 2px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
            background-color: #fff;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .image-item:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .image-item img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            font-size: 16px;
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
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .image-zoom-content img {
            max-width: 100%;
            max-height: 95vh;
            object-fit: contain;
            transition: transform 0.2s ease;
            cursor: move;
            position: relative;
        }
        
        .image-zoom-controls {
            position: absolute;
            top: 20px;
            right: 80px;
            display: flex;
            gap: 10px;
            z-index: 10001;
        }
        
        .image-zoom-controls button {
            background-color: rgba(0, 0, 0, 0.6);
            border: 2px solid #fff;
            color: #fff;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .image-zoom-controls button:hover {
            background-color: rgba(0, 0, 0, 0.8);
            transform: scale(1.1);
        }
        
        .image-zoom-close {
            position: absolute;
            top: 20px;
            right: 20px;
            color: #fff;
            font-size: 30px;
            font-weight: bold;
            cursor: pointer;
            z-index: 10002;
            background-color: rgba(0, 0, 0, 0.6);
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
            transition: all 0.3s ease;
        }
        
        .image-zoom-close:hover {
            background-color: rgba(255, 0, 0, 0.8);
            transform: scale(1.1);
        }
        
        .image-zoom-info {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.6);
            color: #fff;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 14px;
            z-index: 10001;
        }
        
        .image-zoom-modal.fullscreen .image-zoom-content img {
            max-width: none;
            max-height: none;
            width: 100vw;
            height: 100vh;
            object-fit: contain;
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
            <?php if (!empty($xrayImages) && is_array($xrayImages) && count($xrayImages) > 0): ?>
                <!-- Header -->
                <div class="header">
                    <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo">
                    <h1>HÌNH ẢNH X-QUANG</h1>
                </div>
                
                <!-- Patient Info -->
                <div class="patient-info">
                    <div class="patient-info-row">
                        <span class="patient-info-label">Họ tên:</span>
                        <span><?php echo escapeHtml($exam['ho_ten'] ?? '-'); ?></span>
                    </div>
                    <div class="patient-info-row">
                        <span class="patient-info-label">Mã bệnh nhân:</span>
                        <span><?php echo escapeHtml($exam['ma_benh_nhan'] ?? '-'); ?></span>
                    </div>
                    <div class="patient-info-row">
                        <span class="patient-info-label">Ngày khám:</span>
                        <span><?php echo !empty($exam['ngay_hen']) ? date('d/m/Y', strtotime($exam['ngay_hen'])) : '-'; ?></span>
                    </div>
                </div>
                
                <!-- Image Gallery -->
                <div class="image-gallery">
                    <?php foreach ($xrayImages as $img): 
                        // Bảng ket_qua_xquang_hinh_anh sử dụng cột file_path
                        $imagePath = $img['file_path'] ?? $img['duong_dan'] ?? '';
                        if (!empty($imagePath)): 
                    ?>
                            <div class="image-item" onclick="openImageZoom('<?php echo escapeHtml($imagePath); ?>')">
                                <img src="<?php echo escapeHtml($imagePath); ?>" alt="Hình X-Quang" onerror="this.style.display='none'; console.error('Failed to load image: <?php echo escapeHtml($imagePath); ?>');">
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <p>Chưa có hình ảnh X-Quang</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Image Zoom Modal -->
    <div id="imageZoomModal" class="image-zoom-modal" onclick="closeImageZoom()">
        <div class="image-zoom-controls" onclick="event.stopPropagation()">
            <button onclick="event.stopPropagation(); zoomIn();" title="Phóng to (+)">
                <i class="fas fa-search-plus"></i>
            </button>
            <button onclick="event.stopPropagation(); zoomOut();" title="Thu nhỏ (-)">
                <i class="fas fa-search-minus"></i>
            </button>
            <button onclick="event.stopPropagation(); resetZoom();" title="Đặt lại (0)">
                <i class="fas fa-redo"></i>
            </button>
            <button onclick="event.stopPropagation(); toggleFullscreen();" title="Toàn màn hình (F)">
                <i class="fas fa-expand" id="fullscreenIcon"></i>
            </button>
        </div>
        <span class="image-zoom-close" onclick="event.stopPropagation(); closeImageZoom();">&times;</span>
        <div class="image-zoom-content" onclick="event.stopPropagation()">
            <img id="zoomedImage" src="" alt="Zoomed Image">
        </div>
        <div class="image-zoom-info" id="zoomInfo" onclick="event.stopPropagation()">100%</div>
    </div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        let currentZoom = 1;
        let isDragging = false;
        let startX, startY, scrollLeft, scrollTop;
        let isFullscreen = false;
        
        function openImageZoom(imageSrc) {
            var modal = document.getElementById('imageZoomModal');
            var img = document.getElementById('zoomedImage');
            img.src = imageSrc;
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Reset zoom và position
            currentZoom = 1;
            img.style.transform = 'scale(1)';
            img.style.transformOrigin = 'center center';
            img.style.left = '0';
            img.style.top = '0';
            updateZoomInfo();
            
            // Reset fullscreen
            isFullscreen = false;
            modal.classList.remove('fullscreen');
            document.getElementById('fullscreenIcon').classList.remove('fa-compress');
            document.getElementById('fullscreenIcon').classList.add('fa-expand');
        }
        
        function closeImageZoom() {
            var modal = document.getElementById('imageZoomModal');
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
            
            // Reset fullscreen
            if (isFullscreen) {
                toggleFullscreen();
            }
        }
        
        function zoomIn() {
            currentZoom = Math.min(currentZoom + 0.25, 5);
            applyZoom();
        }
        
        function zoomOut() {
            currentZoom = Math.max(currentZoom - 0.25, 0.5);
            applyZoom();
        }
        
        function resetZoom() {
            currentZoom = 1;
            var img = document.getElementById('zoomedImage');
            img.style.transform = 'scale(1)';
            img.style.transformOrigin = 'center center';
            img.style.left = '0';
            img.style.top = '0';
            updateZoomInfo();
        }
        
        function applyZoom() {
            var img = document.getElementById('zoomedImage');
            img.style.transform = 'scale(' + currentZoom + ')';
            img.style.cursor = currentZoom > 1 ? 'move' : 'default';
            updateZoomInfo();
        }
        
        function updateZoomInfo() {
            var info = document.getElementById('zoomInfo');
            info.textContent = Math.round(currentZoom * 100) + '%';
        }
        
        function toggleFullscreen() {
            var modal = document.getElementById('imageZoomModal');
            var icon = document.getElementById('fullscreenIcon');
            
            if (!isFullscreen) {
                modal.classList.add('fullscreen');
                icon.classList.remove('fa-expand');
                icon.classList.add('fa-compress');
                isFullscreen = true;
            } else {
                modal.classList.remove('fullscreen');
                icon.classList.remove('fa-compress');
                icon.classList.add('fa-expand');
                isFullscreen = false;
            }
        }
        
        // Mouse wheel zoom (không cần Ctrl)
        var modal = document.getElementById('imageZoomModal');
        var img = document.getElementById('zoomedImage');
        var imgContainer = document.querySelector('.image-zoom-content');
        
        modal.addEventListener('wheel', function(e) {
            e.preventDefault();
            var delta = e.deltaY > 0 ? -0.1 : 0.1;
            var oldZoom = currentZoom;
            currentZoom = Math.max(0.5, Math.min(5, currentZoom + delta));
            
            if (currentZoom !== oldZoom) {
                // Tính toán vị trí chuột trên hình ảnh
                var rect = img.getBoundingClientRect();
                var mouseX = e.clientX - rect.left;
                var mouseY = e.clientY - rect.top;
                
                // Tính toán tỷ lệ zoom
                var zoomRatio = currentZoom / oldZoom;
                
                // Điều chỉnh vị trí để zoom tại điểm chuột
                var currentLeft = parseFloat(img.style.left) || 0;
                var currentTop = parseFloat(img.style.top) || 0;
                
                img.style.left = (currentLeft - (mouseX - rect.width / 2) * (zoomRatio - 1)) + 'px';
                img.style.top = (currentTop - (mouseY - rect.height / 2) * (zoomRatio - 1)) + 'px';
                
                applyZoom();
            }
        }, { passive: false });
        
        // Drag to pan
        img.addEventListener('mousedown', function(e) {
            if (currentZoom > 1) {
                e.preventDefault();
                isDragging = true;
                var rect = img.getBoundingClientRect();
                startX = e.clientX - rect.left - (parseFloat(img.style.left) || 0);
                startY = e.clientY - rect.top - (parseFloat(img.style.top) || 0);
                img.style.cursor = 'grabbing';
            }
        });
        
        document.addEventListener('mousemove', function(e) {
            if (isDragging && currentZoom > 1) {
                e.preventDefault();
                var rect = imgContainer.getBoundingClientRect();
                var newLeft = e.clientX - rect.left - startX;
                var newTop = e.clientY - rect.top - startY;
                
                // Giới hạn di chuyển trong phạm vi container
                var maxLeft = rect.width * (currentZoom - 1) / 2;
                var maxTop = rect.height * (currentZoom - 1) / 2;
                newLeft = Math.max(-maxLeft, Math.min(maxLeft, newLeft));
                newTop = Math.max(-maxTop, Math.min(maxTop, newTop));
                
                img.style.left = newLeft + 'px';
                img.style.top = newTop + 'px';
            }
        });
        
        document.addEventListener('mouseup', function() {
            if (isDragging) {
                isDragging = false;
                img.style.cursor = currentZoom > 1 ? 'move' : 'default';
            }
        });
        
        // Close on ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeImageZoom();
            } else if (event.key === '+' || event.key === '=') {
                zoomIn();
            } else if (event.key === '-') {
                zoomOut();
            } else if (event.key === '0') {
                resetZoom();
            } else if (event.key === 'f' || event.key === 'F') {
                toggleFullscreen();
            }
        });
    </script>
</body>
</html>

