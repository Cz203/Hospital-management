<?php
require_once __DIR__ . '/../layouts/layout_helper.php';
$ctx = getCurrentUserContext();
$receptionName = htmlspecialchars(($ctx['name'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-clock text-primary me-2"></i>Chấm công
            </h1>
            <p class="text-muted mb-0">Chào mừng lễ tân <?php echo $receptionName; ?></p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-camera me-2"></i>Camera chấm công</h5>
                </div>
                <div class="card-body">
                    <div class="camera-box mb-3">
                        <video id="video" autoplay playsinline
                            style="width: 100%; border-radius: 8px; border: 2px solid #0d6efd;"></video>
                        <canvas id="canvas" style="display: none;"></canvas>
                        <div class="mt-2 text-center">
                            <button class="btn btn-primary btn-sm" id="startBtn" onclick="startCamera()">
                                <i class="fas fa-video me-1"></i> Bật Camera
                            </button>
                            <button class="btn btn-danger btn-sm" id="stopBtn" onclick="stopCamera()" disabled>
                                <i class="fas fa-stop me-1"></i> Tắt Camera
                            </button>
                        </div>
                    </div>

                    <div id="statusInfo" class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="statusText">Vui lòng bật camera và chụp ảnh để chấm công</span>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-success btn-lg" id="checkInBtn" onclick="checkIn()" disabled>
                            <i class="fas fa-sign-in-alt me-2"></i> Check-in
                        </button>
                        <button class="btn btn-warning btn-lg" id="checkOutBtn" onclick="checkOut()" disabled>
                            <i class="fas fa-sign-out-alt me-2"></i> Check-out
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Lịch sử chấm công hôm nay</h5>
                </div>
                <div class="card-body">
                    <div id="todayStatus">
                        <div class="text-center text-muted">
                            <i class="fas fa-spinner fa-spin"></i> Đang tải...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="resultMessage" class="mt-3"></div>
</div>

<!-- Load face-api.js từ CDN -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
    let stream = null;
    let modelsLoaded = false;

    // Load face-api.js models
    async function loadModels() {
        if (modelsLoaded) return;

        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(
                    'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights'),
                faceapi.nets.faceLandmark68Net.loadFromUri(
                    'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights'),
                faceapi.nets.faceRecognitionNet.loadFromUri(
                    'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights')
            ]);
            modelsLoaded = true;
            console.log('Face-api.js models loaded');
        } catch (error) {
            console.error('Error loading face-api.js models:', error);
            showMessage('Lỗi khi tải models nhận diện khuôn mặt. Vui lòng thử lại!', 'danger');
        }
    }

    // Load models khi trang được tải
    loadModels();

    // Load trạng thái hôm nay
    loadTodayStatus();

    async function startCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: {
                        ideal: 640
                    },
                    height: {
                        ideal: 480
                    }
                }
            });

            document.getElementById('video').srcObject = stream;
            document.getElementById('startBtn').disabled = true;
            document.getElementById('stopBtn').disabled = false;
            document.getElementById('checkInBtn').disabled = false;
            document.getElementById('checkOutBtn').disabled = false;
        } catch (error) {
            showMessage('Không thể truy cập camera: ' + error.message, 'danger');
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
            document.getElementById('video').srcObject = null;
            document.getElementById('startBtn').disabled = false;
            document.getElementById('stopBtn').disabled = true;
            document.getElementById('checkInBtn').disabled = true;
            document.getElementById('checkOutBtn').disabled = true;
        }
    }

    async function captureAndProcess(action) {
        if (!stream) {
            showMessage('Vui lòng bật camera trước!', 'warning');
            return;
        }

        if (!modelsLoaded) {
            showMessage('Đang tải models nhận diện khuôn mặt, vui lòng đợi...', 'info');
            await loadModels();
        }

        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);

        const imageData = canvas.toDataURL('image/png');

        showMessage('Đang xử lý ảnh và nhận diện khuôn mặt...', 'info');

        try {
            // Tạo image element từ base64
            const img = new Image();
            img.src = imageData;
            await img.decode();

            // Phát hiện khuôn mặt và tạo encoding
            const detection = await faceapi
                .detectSingleFace(img, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (!detection) {
                showMessage('Không phát hiện được khuôn mặt. Vui lòng đảm bảo khuôn mặt rõ ràng trong khung hình!',
                    'danger');
                return;
            }

            // Lấy face descriptor (encoding)
            const faceEncoding = Array.from(detection.descriptor);

            // Gửi lên server
            const response = await fetch('?action=reception_process_attendance', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: action,
                    face_encoding: faceEncoding,
                    image_data: imageData
                })
            });

            const result = await response.json();

            if (result.success) {
                showMessage(result.message + (result.confidence ?
                    ` (Độ tin cậy: ${(result.confidence * 100).toFixed(1)}%)` : ''), 'success');
                loadTodayStatus();
            } else {
                showMessage('Lỗi: ' + (result.message || 'Không xác định'), 'danger');
            }
        } catch (error) {
            console.error('Error processing attendance:', error);
            showMessage('Lỗi khi xử lý: ' + error.message, 'danger');
        }
    }

    function checkIn() {
        captureAndProcess('check_in');
    }

    function checkOut() {
        captureAndProcess('check_out');
    }

    function loadTodayStatus() {
        fetch('?action=reception_get_today_attendance')
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                const statusDiv = document.getElementById('todayStatus');
                if (!data.success) {
                    statusDiv.innerHTML = `<div class="text-danger">${data.message || 'Lỗi khi tải thông tin'}</div>`;
                    return;
                }

                if (data.status) {
                    const status = data.status;
                    let html = '<div class="mb-3">';

                    if (status.check_in_time) {
                        html +=
                            `<p><strong>Check-in:</strong> ${new Date(status.check_in_time).toLocaleString('vi-VN')}</p>`;
                    }
                    if (status.check_out_time) {
                        html +=
                            `<p><strong>Check-out:</strong> ${new Date(status.check_out_time).toLocaleString('vi-VN')}</p>`;
                    }

                    html +=
                        `<p><strong>Trạng thái:</strong> <span class="badge bg-${status.status === 'checked_out' ? 'success' : 'warning'}">${status.status === 'checked_out' ? 'Đã check-out' : 'Đã check-in'}</span></p>`;
                    html += '</div>';

                    // Cập nhật nút
                    const checkInBtn = document.getElementById('checkInBtn');
                    const checkOutBtn = document.getElementById('checkOutBtn');
                    const statusText = document.getElementById('statusText');

                    if (status.status === 'checked_in') {
                        if (checkInBtn) checkInBtn.disabled = true;
                        if (checkOutBtn) checkOutBtn.disabled = false;
                        if (statusText) statusText.textContent =
                            'Bạn đã check-in. Vui lòng check-out khi kết thúc ca làm việc.';
                    } else if (status.status === 'checked_out') {
                        if (checkInBtn) checkInBtn.disabled = true;
                        if (checkOutBtn) checkOutBtn.disabled = true;
                        if (statusText) statusText.textContent = 'Bạn đã hoàn thành chấm công hôm nay.';
                    }

                    statusDiv.innerHTML = html;
                } else {
                    statusDiv.innerHTML = '<div class="text-center text-muted">Chưa có lịch sử chấm công hôm nay</div>';
                    const statusText = document.getElementById('statusText');
                    if (statusText) statusText.textContent = 'Vui lòng bật camera và check-in để bắt đầu ca làm việc.';
                }
            })
            .catch(error => {
                console.error('Error loading today status:', error);
                document.getElementById('todayStatus').innerHTML = '<div class="text-danger">Lỗi khi tải thông tin: ' +
                    error.message + '</div>';
            });
    }

    function showMessage(message, type) {
        const resultDiv = document.getElementById('resultMessage');
        resultDiv.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
    }

    // Dừng camera khi đóng trang
    window.addEventListener('beforeunload', stopCamera);
</script>