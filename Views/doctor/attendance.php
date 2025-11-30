<?php
require_once __DIR__ . '/../layouts/layout_helper.php';
$ctx = getCurrentUserContext();
$doctorName = htmlspecialchars(($ctx['name'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-clock text-primary me-2"></i>Chấm công
            </h1>
            <p class="text-muted mb-0">Chào mừng bác sĩ <?php echo $doctorName; ?></p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <!-- Thông báo kết quả chấm công (hiển thị trên đầu camera) -->
            <div id="resultMessage"></div>
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
                        <span id="statusText">Vui lòng bật camera để chấm công</span>
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
</div>

<!-- Load face-api.js từ CDN -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
let stream = null;
let modelsLoaded = false;
let lastFacePosition = null; // Lưu vị trí khuôn mặt lần trước để kiểm tra liveness
let faceMovementDetected = false; // Đánh dấu đã phát hiện chuyển động
let isProcessing = false; // Tránh spam - đang xử lý
let lastAttendanceTime = 0; // Thời gian chấm công lần cuối
const MIN_ATTENDANCE_INTERVAL = 5000; // Tối thiểu 3 giây giữa các lần chấm công

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

// Kiểm tra liveness - yêu cầu người dùng di chuyển đầu
async function checkLiveness() {
    if (!stream || !modelsLoaded) {
        return false;
    }

    const video = document.getElementById('video');
    if (video.readyState < 2 || video.videoWidth === 0) {
        return false;
    }

    try {
        // Chụp 2 ảnh cách nhau 1 giây
        const canvas1 = document.createElement('canvas');
        canvas1.width = video.videoWidth;
        canvas1.height = video.videoHeight;
        const ctx1 = canvas1.getContext('2d');
        ctx1.drawImage(video, 0, 0);

        const detection1 = await faceapi
            .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks();

        if (!detection1) {
            return false;
        }

        // Đợi 1 giây
        await new Promise(resolve => setTimeout(resolve, 1000));

        // Chụp ảnh thứ 2
        const detection2 = await faceapi
            .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks();

        if (!detection2) {
            return false;
        }

        // So sánh vị trí khuôn mặt - phải có sự khác biệt (chứng minh camera live)
        const box1 = detection1.detection.box;
        const box2 = detection2.detection.box;

        const centerX1 = box1.x + box1.width / 2;
        const centerY1 = box1.y + box1.height / 2;
        const centerX2 = box2.x + box2.width / 2;
        const centerY2 = box2.y + box2.height / 2;

        const distance = Math.sqrt(
            Math.pow(centerX2 - centerX1, 2) + Math.pow(centerY2 - centerY1, 2)
        );

        // Nếu khoảng cách quá nhỏ (< 5px), có thể là ảnh tĩnh
        // Nhưng cũng có thể người dùng đứng yên, nên chỉ cảnh báo nếu quá nhỏ (< 2px)
        if (distance < 2) {
            return false; // Có thể là ảnh tĩnh
        }

        return true;
    } catch (error) {
        console.error('Liveness check error:', error);
        return false;
    }
}

// Helper function để reset processing state
function resetProcessingState() {
    isProcessing = false;
    const checkInBtn = document.getElementById('checkInBtn');
    const checkOutBtn = document.getElementById('checkOutBtn');
    if (checkInBtn && !checkInBtn.hasAttribute('data-disabled-by-status')) {
        checkInBtn.disabled = false;
    }
    if (checkOutBtn && !checkOutBtn.hasAttribute('data-disabled-by-status')) {
        checkOutBtn.disabled = false;
    }
}

async function captureAndProcess(action) {
    // Chống spam: Kiểm tra đang xử lý
    if (isProcessing) {
        showMessage('Đang xử lý, vui lòng đợi...', 'warning');
        return;
    }

    // Chống spam: Kiểm tra thời gian giữa các lần chấm công
    const now = Date.now();
    if (now - lastAttendanceTime < MIN_ATTENDANCE_INTERVAL) {
        const remainingTime = Math.ceil((MIN_ATTENDANCE_INTERVAL - (now - lastAttendanceTime)) / 1000);
        showMessage(`Vui lòng đợi ${remainingTime} giây trước khi chấm công lại!`, 'warning');
        return;
    }

    // Đánh dấu đang xử lý
    isProcessing = true;
    lastAttendanceTime = now;

    // Disable các nút để tránh click nhiều lần
    const checkInBtn = document.getElementById('checkInBtn');
    const checkOutBtn = document.getElementById('checkOutBtn');
    if (checkInBtn) checkInBtn.disabled = true;
    if (checkOutBtn) checkOutBtn.disabled = true;

    try {
        // Kiểm tra stream có đang active không
        if (!stream) {
            showMessage('Vui lòng bật camera trước!', 'warning');
            resetProcessingState();
            return;
        }

        // Kiểm tra stream tracks có đang active không
        const activeTracks = stream.getVideoTracks().filter(track => track.readyState === 'live');
        if (activeTracks.length === 0) {
            showMessage('Camera không hoạt động. Vui lòng bật lại camera!', 'warning');
            resetProcessingState();
            return;
        }

        if (!modelsLoaded) {
            showMessage('Đang tải models nhận diện khuôn mặt, vui lòng đợi...', 'info');
            await loadModels();
        }

        const video = document.getElementById('video');

        // Kiểm tra video element có đang phát không
        if (video.readyState < 2 || video.paused || video.ended) {
            showMessage('Camera chưa sẵn sàng. Vui lòng đợi camera khởi động!', 'warning');
            resetProcessingState();
            return;
        }

        // Kiểm tra video có kích thước hợp lệ không (ảnh từ camera phải có kích thước > 0)
        if (video.videoWidth === 0 || video.videoHeight === 0) {
            showMessage('Camera chưa sẵn sàng. Vui lòng đợi camera khởi động!', 'warning');
            resetProcessingState();
            return;
        }

        // KIỂM TRA LIVENESS - Yêu cầu người dùng di chuyển đầu
        showMessage('Vui lòng di chuyển đầu nhẹ nhàng để xác thực...', 'info');
        const isLive = await checkLiveness();

        if (!isLive) {
            showMessage('Không phát hiện chuyển động. Vui lòng di chuyển đầu và thử lại! (Để tránh dùng ảnh tĩnh)',
                'warning');
            resetProcessingState();
            return;
        }

        const canvas = document.getElementById('canvas');

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');

        // Vẽ ảnh từ video stream
        ctx.drawImage(video, 0, 0);

        // Thêm timestamp watermark để xác thực ảnh được chụp trực tiếp
        const now = new Date();
        const timestamp = now.toLocaleString('vi-VN');
        ctx.fillStyle = 'rgba(0, 0, 0, 0.5)';
        ctx.fillRect(10, canvas.height - 30, 300, 25);
        ctx.fillStyle = 'white';
        ctx.font = '14px Arial';
        ctx.fillText(timestamp, 15, canvas.height - 10);

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
                resetProcessingState();
                return;
            }

            // Lấy face descriptor (encoding)
            const faceEncoding = Array.from(detection.descriptor);

            // BẮT BUỘC: Lấy location (GPS) - yêu cầu bật vị trí
            let location = null;
            try {
                if (!navigator.geolocation) {
                    showMessage(
                        'Trình duyệt không hỗ trợ GPS. Vui lòng sử dụng trình duyệt khác hoặc thiết bị có GPS!',
                        'danger');
                    resetProcessingState();
                    return;
                }

                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        timeout: 10000,
                        enableHighAccuracy: true,
                        maximumAge: 0 // Không dùng cache, phải lấy vị trí mới
                    });
                });

                location = `${position.coords.latitude},${position.coords.longitude}`;
                console.log('GPS Location:', location);
            } catch (error) {
                console.error('Lỗi khi lấy GPS location:', error);
                let errorMsg = 'Không thể lấy vị trí GPS. ';

                if (error.code === error.PERMISSION_DENIED) {
                    errorMsg += 'Vui lòng bật quyền truy cập vị trí trong cài đặt trình duyệt!';
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    errorMsg += 'Vị trí không khả dụng. Vui lòng kiểm tra GPS của thiết bị!';
                } else if (error.code === error.TIMEOUT) {
                    errorMsg += 'Hết thời gian chờ lấy vị trí. Vui lòng thử lại!';
                } else {
                    errorMsg += 'Vui lòng bật GPS và cho phép truy cập vị trí!';
                }

                showMessage(errorMsg, 'danger');
                resetProcessingState();
                return; // Dừng lại, không cho chấm công nếu không có GPS
            }

            if (!location) {
                showMessage('Không thể lấy vị trí GPS. Vui lòng bật GPS và cho phép truy cập vị trí để chấm công!',
                    'danger');
                resetProcessingState();
                return;
            }

            // Gửi lên server
            const response = await fetch('?action=doctor_process_attendance', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: action,
                    face_encoding: faceEncoding,
                    image_data: imageData,
                    location: location
                })
            });

            const result = await response.json();

            if (result.success) {
                const successMessage = result.message + (result.confidence ?
                    ` (Độ tin cậy: ${(result.confidence * 100).toFixed(1)}%)` : '');

                // Hiển thị ở đầu trang (top notification)
                if (typeof showTopNotification === 'function') {
                    showTopNotification(successMessage, 'success');
                }

                // Hiển thị ở trang hiện tại
                showMessage(successMessage, 'success');
                loadTodayStatus();
            } else {
                const errorMessage = 'Lỗi: ' + (result.message || 'Không xác định');

                // Hiển thị ở đầu trang nếu có lỗi
                if (typeof showTopNotification === 'function') {
                    showTopNotification(errorMessage, 'danger');
                }

                showMessage(errorMessage, 'danger');
            }

            // Reset processing state sau khi hoàn thành
            resetProcessingState();
        } catch (error) {
            console.error('Error processing attendance:', error);
            showMessage('Lỗi khi xử lý: ' + error.message, 'danger');
            resetProcessingState();
        }
    } catch (error) {
        console.error('Error in captureAndProcess:', error);
        showMessage('Lỗi khi xử lý: ' + error.message, 'danger');
        resetProcessingState();
    } finally {
        // Đảm bảo luôn reset sau 1 giây nếu vẫn còn processing
        setTimeout(() => {
            if (isProcessing) {
                resetProcessingState();
            }
        }, 1000);
    }
}

function checkIn() {
    if (isProcessing) {
        showMessage('Đang xử lý, vui lòng đợi...', 'warning');
        return;
    }
    captureAndProcess('check_in');
}

function checkOut() {
    if (isProcessing) {
        showMessage('Đang xử lý, vui lòng đợi...', 'warning');
        return;
    }
    captureAndProcess('check_out');
}

function loadTodayStatus() {
    fetch('?action=doctor_get_today_attendance')
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            const statusDiv = document.getElementById('todayStatus');
            if (!data.success) {
                statusDiv.innerHTML =
                    `<div class="text-danger">${data.message || 'Lỗi khi tải thông tin'}</div>`;
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
                    if (checkInBtn) {
                        checkInBtn.disabled = true;
                        checkInBtn.setAttribute('data-disabled-by-status', 'true');
                    }
                    if (checkOutBtn) {
                        checkOutBtn.disabled = false;
                        checkOutBtn.removeAttribute('data-disabled-by-status');
                    }
                    if (statusText) statusText.textContent =
                        'Bạn đã check-in. Vui lòng check-out khi kết thúc ca làm việc.';
                } else if (status.status === 'checked_out') {
                    if (checkInBtn) {
                        checkInBtn.disabled = true;
                        checkInBtn.setAttribute('data-disabled-by-status', 'true');
                    }
                    if (checkOutBtn) {
                        checkOutBtn.disabled = true;
                        checkOutBtn.setAttribute('data-disabled-by-status', 'true');
                    }
                    if (statusText) statusText.textContent = 'Bạn đã hoàn thành chấm công hôm nay.';
                }

                statusDiv.innerHTML = html;
            } else {
                statusDiv.innerHTML =
                    '<div class="text-center text-muted">Chưa có lịch sử chấm công hôm nay</div>';
                const statusText = document.getElementById('statusText');
                if (statusText) statusText.textContent =
                    'Vui lòng bật camera và check-in để bắt đầu ca làm việc.';
            }
        })
        .catch(error => {
            console.error('Error loading today status:', error);
            document.getElementById('todayStatus').innerHTML =
                '<div class="text-danger">Lỗi khi tải thông tin: ' +
                error.message + '</div>';
        });
}

// Function checkGPSPermission đã được xóa - không còn hiển thị thông báo GPS

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