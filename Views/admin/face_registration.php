<?php
require_once __DIR__ . '/../layouts/layout_helper.php';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user-check text-primary me-2"></i>Đăng ký nhận diện khuôn mặt
            </h1>
            <p class="text-muted mb-0">Đăng ký khuôn mặt cho bác sĩ và lễ tân</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Chọn nhân viên</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Loại nhân viên</label>
                        <select class="form-select" id="userType">
                            <option value="doctor">Bác sĩ</option>
                            <option value="reception">Lễ tân</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Chọn nhân viên</label>
                        <select class="form-select" id="userId">
                            <option value="">-- Chọn nhân viên --</option>
                        </select>
                    </div>
                    <div id="userInfo" class="alert alert-info" style="display: none;">
                        <strong id="userName"></strong><br>
                        <small id="userEmail"></small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-camera me-2"></i>Chụp ảnh đăng ký</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="camera-box mb-3">
                                <h6>Camera Preview</h6>
                                <video id="video" autoplay playsinline
                                    style="width: 100%; border-radius: 8px; border: 2px solid #0d6efd;"></video>
                                <canvas id="canvas" style="display: none;"></canvas>
                                <div class="mt-2">
                                    <button class="btn btn-primary btn-sm" id="startBtn" onclick="startCamera()">
                                        <i class="fas fa-video me-1"></i> Bật Camera
                                    </button>
                                    <button class="btn btn-success btn-sm" id="captureBtn" onclick="capturePhoto()"
                                        disabled>
                                        <i class="fas fa-camera me-1"></i> Chụp ảnh
                                    </button>
                                    <button class="btn btn-danger btn-sm" id="stopBtn" onclick="stopCamera()" disabled>
                                        <i class="fas fa-stop me-1"></i> Tắt Camera
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="camera-box mb-3">
                                <h6>Ảnh đã chụp</h6>
                                <img id="capturedImage" src="" alt="Captured Image"
                                    style="display: none; width: 100%; border-radius: 8px; border: 2px solid #28a745;">
                                <div id="noImage" class="text-center text-muted p-4"
                                    style="border: 2px dashed #ccc; border-radius: 8px;">
                                    Chưa có ảnh được chụp
                                </div>
                                <div class="mt-2">
                                    <button class="btn btn-primary w-100" id="registerBtn" onclick="registerFace()"
                                        disabled>
                                        <i class="fas fa-user-check me-1"></i> Đăng ký khuôn mặt
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="resultMessage" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách người đã đăng ký khuôn mặt -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách đã đăng ký khuôn mặt</h5>
                    <div>
                        <select class="form-select form-select-sm" id="filterType"
                            style="width: auto; display: inline-block;">
                            <option value="">Tất cả</option>
                            <option value="doctor">Bác sĩ</option>
                            <option value="reception">Lễ tân</option>
                        </select>
                        <button class="btn btn-light btn-sm ms-2" onclick="loadRegisteredFaces()">
                            <i class="fas fa-sync-alt me-1"></i> Làm mới
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="registeredFacesList" class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Họ tên</th>
                                    <th>Email</th>
                                    <th>Loại</th>
                                    <th>Ngày đăng ký</th>
                                    <th>Ngày cập nhật</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="registeredFacesTableBody">
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="fas fa-spinner fa-spin me-2"></i> Đang tải...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal xóa khuôn mặt -->
    <div class="modal fade" id="deleteFaceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Xóa khuôn mặt đã đăng ký
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Lưu ý:</strong> Để xóa khuôn mặt, bạn cần nhận diện lại khuôn mặt đã đăng ký trước đó.
                    </div>
                    <div id="deleteFaceUserInfo" class="mb-3">
                        <p><strong>Người dùng:</strong> <span id="deleteFaceUserName"></span></p>
                        <p><strong>Loại:</strong> <span id="deleteFaceUserType"></span></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Chụp ảnh để xác nhận</label>
                        <div class="camera-box mb-3">
                            <video id="deleteVideo" autoplay playsinline
                                style="width: 100%; border-radius: 8px; border: 2px solid #ffc107; display: none;"></video>
                            <canvas id="deleteCanvas" style="display: none;"></canvas>
                            <div id="deleteNoVideo" class="text-center text-muted p-4"
                                style="border: 2px dashed #ccc; border-radius: 8px;">
                                Chưa bật camera
                            </div>
                            <div class="mt-2">
                                <button class="btn btn-warning btn-sm" id="deleteStartBtn"
                                    onclick="startDeleteCamera()">
                                    <i class="fas fa-video me-1"></i> Bật Camera
                                </button>
                                <button class="btn btn-success btn-sm" id="deleteCaptureBtn"
                                    onclick="captureDeletePhoto()" disabled>
                                    <i class="fas fa-camera me-1"></i> Chụp ảnh xác nhận
                                </button>
                                <button class="btn btn-danger btn-sm" id="deleteStopBtn" onclick="stopDeleteCamera()"
                                    disabled>
                                    <i class="fas fa-stop me-1"></i> Tắt Camera
                                </button>
                            </div>
                        </div>
                        <img id="deleteCapturedImage" src="" alt="Captured Image"
                            style="display: none; width: 100%; border-radius: 8px; border: 2px solid #ffc107; max-height: 200px;">
                    </div>
                    <div id="deleteFaceMessage"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="confirmDeleteFace()"
                        disabled>
                        <i class="fas fa-trash me-1"></i> Xóa khuôn mặt
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load face-api.js từ CDN -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"
    onerror="console.error('Failed to load face-api.js'); document.getElementById('faceApiError').style.display='block';">
</script>

<script>
let stream = null;
let capturedImageData = null;
let modelsLoaded = false;

// Kiểm tra face-api.js đã load chưa sau 3 giây
setTimeout(function() {
    if (typeof faceapi === 'undefined') {
        console.error('Face-api.js không được tải!');
        const errorDiv = document.createElement('div');
        errorDiv.id = 'faceApiError';
        errorDiv.className = 'alert alert-danger';
        errorDiv.innerHTML =
            '<strong>Lỗi:</strong> Không thể tải thư viện face-api.js. Vui lòng kiểm tra kết nối internet và tải lại trang.';
        document.querySelector('.container-fluid').insertBefore(errorDiv, document.querySelector(
            '.container-fluid').firstChild);
    }
}, 3000);

// Load face-api.js models
async function loadModels() {
    if (modelsLoaded) return true;

    // Kiểm tra xem faceapi đã được load chưa
    if (typeof faceapi === 'undefined') {
        showMessage('Face-api.js chưa được tải. Vui lòng tải lại trang!', 'danger');
        return false;
    }

    try {
        showMessage('Đang tải models nhận diện khuôn mặt...', 'info');

        // Thêm timeout 30 giây
        // Sử dụng GitHub raw content (đáng tin cậy hơn CDN)
        // Nếu vẫn lỗi 404, vui lòng tải weights về local:
        // 1. Tạo thư mục: public/weights/
        // 2. Tải từ: https://github.com/justadudewhohacks/face-api.js/tree/master/weights
        // 3. Sau đó đổi weightsBaseUrl thành: '/public/weights'
        const weightsBaseUrl = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';

        const loadPromise = Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(weightsBaseUrl),
            faceapi.nets.faceLandmark68Net.loadFromUri(weightsBaseUrl),
            faceapi.nets.faceRecognitionNet.loadFromUri(weightsBaseUrl)
        ]);

        const timeoutPromise = new Promise((_, reject) =>
            setTimeout(() => reject(new Error(
                'Timeout: Không thể tải models trong 30 giây. Vui lòng kiểm tra kết nối internet.')), 30000)
        );

        await Promise.race([loadPromise, timeoutPromise]);
        modelsLoaded = true;
        return true;
    } catch (error) {
        console.error('✗ Error loading face-api.js models:', error);
        modelsLoaded = false;
        let errorMsg = error.message || 'Lỗi không xác định';

        if (errorMsg.includes('404') || errorMsg.includes('Failed to fetch')) {
            errorMsg = 'Không thể tải models từ CDN. Vui lòng tải weights về local server:<br>' +
                '1. Tạo thư mục: <code>public/weights/</code><br>' +
                '2. Tải các file từ: <a href="https://github.com/justadudewhohacks/face-api.js/tree/master/weights" target="_blank">GitHub</a><br>' +
                '3. Sau đó đổi <code>weightsBaseUrl</code> trong code thành: <code>/public/weights</code>';
        } else if (errorMsg.includes('NetworkError')) {
            errorMsg = 'Không thể kết nối đến server. Vui lòng kiểm tra kết nối internet!';
        }

        showMessage('Lỗi khi tải models: ' + errorMsg, 'danger');
        return false;
    }
}

// Load models khi trang được tải
window.addEventListener('DOMContentLoaded', function() {
    loadModels().then(success => {
        if (success) {}
    });
});

// Load danh sách nhân viên
document.getElementById('userType').addEventListener('change', function() {
    loadUsers(this.value);
});

function loadUsers(userType) {
    const userIdSelect = document.getElementById('userId');
    userIdSelect.innerHTML = '<option value="">-- Đang tải --</option>';

    fetch(`?action=admin_get_users&type=${userType}`)
        .then(response => response.json())
        .then(data => {
            userIdSelect.innerHTML = '<option value="">-- Chọn nhân viên --</option>';
            if (data.success && data.users) {
                data.users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.ten;
                    userIdSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading users:', error);
            userIdSelect.innerHTML = '<option value="">-- Lỗi khi tải --</option>';
        });
}

document.getElementById('userId').addEventListener('change', function() {
    const userId = this.value;
    const userType = document.getElementById('userType').value;

    if (userId) {
        fetch(`?action=admin_get_user_info&id=${userId}&type=${userType}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('userName').textContent = data.user.ten;
                    document.getElementById('userEmail').textContent = data.user.email || data.user
                        .so_dien_thoai || '';
                    document.getElementById('userInfo').style.display = 'block';
                }
            })
            .catch(error => console.error('Error loading user info:', error));
    } else {
        document.getElementById('userInfo').style.display = 'none';
    }
});

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
        document.getElementById('captureBtn').disabled = false;
        document.getElementById('stopBtn').disabled = false;
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
        document.getElementById('captureBtn').disabled = true;
        document.getElementById('stopBtn').disabled = true;
    }
}

function capturePhoto() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const capturedImage = document.getElementById('capturedImage');
    const noImage = document.getElementById('noImage');

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0);

    capturedImageData = canvas.toDataURL('image/png');
    capturedImage.src = capturedImageData;
    capturedImage.style.display = 'block';
    noImage.style.display = 'none';
    document.getElementById('registerBtn').disabled = false;
}

async function registerFace() {
    if (!capturedImageData) {
        showMessage('Vui lòng chụp ảnh trước!', 'warning');
        return;
    }

    const userId = document.getElementById('userId').value;
    const userType = document.getElementById('userType').value;

    if (!userId) {
        showMessage('Vui lòng chọn nhân viên!', 'warning');
        return;
    }

    // Kiểm tra faceapi đã load chưa
    if (typeof faceapi === 'undefined') {
        showMessage('Face-api.js chưa được tải. Vui lòng tải lại trang!', 'danger');
        return;
    }

    // Đảm bảo models đã được load
    if (!modelsLoaded) {
        showMessage('Đang tải models nhận diện khuôn mặt, vui lòng đợi...', 'info');
        const loaded = await loadModels();
        if (!loaded) {
            showMessage('Không thể tải models. Vui lòng thử lại!', 'danger');
            return;
        }
    }

    // Disable button để tránh click nhiều lần
    const registerBtn = document.getElementById('registerBtn');
    registerBtn.disabled = true;
    registerBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang xử lý...';

    showMessage('Đang xử lý ảnh và tạo face encoding...', 'info');

    try {
        // Tạo image element từ base64
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.src = capturedImageData;

        // Đợi image load với timeout
        await new Promise((resolve, reject) => {
            img.onload = resolve;
            img.onerror = () => reject(new Error('Không thể tải ảnh'));
            setTimeout(() => reject(new Error('Timeout khi tải ảnh')), 10000);
        });

        showMessage('Đang phát hiện khuôn mặt...', 'info');

        // Phát hiện khuôn mặt và tạo encoding với timeout
        const detectPromise = faceapi
            .detectSingleFace(img, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptor();

        const timeoutPromise = new Promise((_, reject) =>
            setTimeout(() => reject(new Error(
                'Timeout: Quá trình phát hiện khuôn mặt mất quá nhiều thời gian')), 15000)
        );

        const detection = await Promise.race([detectPromise, timeoutPromise]);

        if (!detection) {
            showMessage('Không phát hiện được khuôn mặt trong ảnh. Vui lòng chụp lại với khuôn mặt rõ ràng hơn!',
                'danger');
            registerBtn.disabled = false;
            registerBtn.innerHTML = '<i class="fas fa-user-check me-1"></i> Đăng ký khuôn mặt';
            return;
        }

        showMessage('Đang tạo face encoding...', 'info');

        // Lấy face descriptor (encoding)
        const faceEncoding = Array.from(detection.descriptor);

        if (!faceEncoding || faceEncoding.length === 0) {
            throw new Error('Không thể tạo face encoding');
        }

        showMessage('Đang gửi dữ liệu lên server...', 'info');

        // Gửi lên server với timeout (bao gồm cả ảnh để lưu làm sample)
        const requestData = {
            user_id: parseInt(userId),
            user_type: userType,
            face_encoding: faceEncoding,
            image_data: capturedImageData // Gửi ảnh để lưu làm sample
        };

        const fetchPromise = fetch('?action=admin_save_face_encoding', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestData)
        });

        const fetchTimeout = new Promise((_, reject) =>
            setTimeout(() => reject(new Error('Timeout: Không nhận được phản hồi từ server')), 20000)
        );

        const response = await Promise.race([fetchPromise, fetchTimeout]);

        if (!response.ok) {
            throw new Error('Server error: ' + response.status);
        }

        const result = await response.json();

        if (result.success) {
            showMessage('Đăng ký nhận diện khuôn mặt thành công!', 'success');
            // Reset form
            document.getElementById('capturedImage').style.display = 'none';
            document.getElementById('noImage').style.display = 'block';
            capturedImageData = null;
            registerBtn.disabled = true;
            registerBtn.innerHTML = '<i class="fas fa-user-check me-1"></i> Đăng ký khuôn mặt';
            // Reload danh sách đã đăng ký
            loadRegisteredFaces();
        } else {
            showMessage('Lỗi: ' + (result.message || 'Không xác định'), 'danger');
            registerBtn.disabled = false;
            registerBtn.innerHTML = '<i class="fas fa-user-check me-1"></i> Đăng ký khuôn mặt';
        }
    } catch (error) {
        console.error('Error registering face:', error);
        let errorMsg = error.message || 'Lỗi không xác định';

        // Thông báo lỗi cụ thể hơn
        if (errorMsg.includes('Timeout')) {
            errorMsg = 'Quá trình xử lý mất quá nhiều thời gian. Vui lòng thử lại!';
        } else if (errorMsg.includes('faceapi')) {
            errorMsg = 'Lỗi face-api.js: ' + errorMsg;
        }

        showMessage('Lỗi khi xử lý: ' + errorMsg, 'danger');
        registerBtn.disabled = false;
        registerBtn.innerHTML = '<i class="fas fa-user-check me-1"></i> Đăng ký khuôn mặt';
    }
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

// ========== QUẢN LÝ DANH SÁCH ĐÃ ĐĂNG KÝ ==========
let deleteStream = null;
let deleteCapturedImageData = null;
let currentDeleteUserId = null;
let currentDeleteUserType = null;

// Load danh sách đã đăng ký
function loadRegisteredFaces() {
    const filterType = document.getElementById('filterType').value;
    const url = `?action=admin_get_registered_faces${filterType ? '&type=' + filterType : ''}`;

    const tbody = document.getElementById('registeredFacesTableBody');
    tbody.innerHTML =
        '<tr><td colspan="8" class="text-center text-muted"><i class="fas fa-spinner fa-spin me-2"></i> Đang tải...</td></tr>';

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.faces && data.faces.length > 0) {
                let html = '';
                data.faces.forEach((face, index) => {
                    const userTypeLabel = face.user_type === 'doctor' ? 'Bác sĩ' :
                        face.user_type === 'reception' ? 'Lễ tân' : 'Admin';
                    const userTypeBadge = face.user_type === 'doctor' ? 'badge bg-primary' :
                        face.user_type === 'reception' ? 'badge bg-success' : 'badge bg-info';

                    const ngayTao = new Date(face.ngay_tao).toLocaleString('vi-VN');
                    const ngayCapNhat = new Date(face.ngay_cap_nhat).toLocaleString('vi-VN');

                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td><strong>${face.ten || 'N/A'}</strong></td>
                            <td>${face.email || 'N/A'}</td>
                            <td><span class="${userTypeBadge}">${userTypeLabel}</span></td>
              
                            <td>${ngayTao}</td>
                            <td>${ngayCapNhat}</td>
                            <td>
                                <button class="btn btn-danger btn-sm" onclick="openDeleteFaceModal(${face.user_id}, '${face.user_type}', '${face.ten || 'N/A'}', '${userTypeLabel}')">
                                    <i class="fas fa-trash me-1"></i> Xóa
                                </button>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            } else {
                tbody.innerHTML =
                    '<tr><td colspan="8" class="text-center text-muted">Chưa có ai đăng ký khuôn mặt</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading registered faces:', error);
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Lỗi khi tải danh sách</td></tr>';
        });
}

// Filter danh sách
document.getElementById('filterType').addEventListener('change', function() {
    loadRegisteredFaces();
});

// Load danh sách khi trang được tải
window.addEventListener('DOMContentLoaded', function() {
    loadRegisteredFaces();
});

// ========== XÓA KHUÔN MẶT ==========
function openDeleteFaceModal(userId, userType, userName, userTypeLabel) {
    currentDeleteUserId = userId;
    currentDeleteUserType = userType;

    document.getElementById('deleteFaceUserName').textContent = userName;
    document.getElementById('deleteFaceUserType').textContent = userTypeLabel;

    // Reset modal
    deleteCapturedImageData = null;
    document.getElementById('deleteCapturedImage').style.display = 'none';
    document.getElementById('deleteNoVideo').style.display = 'block';
    document.getElementById('deleteVideo').style.display = 'none';
    document.getElementById('confirmDeleteBtn').disabled = true;
    document.getElementById('deleteFaceMessage').innerHTML = '';

    // Reset camera buttons
    document.getElementById('deleteStartBtn').disabled = false;
    document.getElementById('deleteCaptureBtn').disabled = true;
    document.getElementById('deleteStopBtn').disabled = true;

    // Stop any existing stream
    if (deleteStream) {
        stopDeleteCamera();
    }

    const modal = new bootstrap.Modal(document.getElementById('deleteFaceModal'));
    modal.show();
}

function startDeleteCamera() {
    navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: 'user',
                width: {
                    ideal: 640
                },
                height: {
                    ideal: 480
                }
            }
        })
        .then(stream => {
            deleteStream = stream;
            document.getElementById('deleteVideo').srcObject = stream;
            document.getElementById('deleteVideo').style.display = 'block';
            document.getElementById('deleteNoVideo').style.display = 'none';
            document.getElementById('deleteStartBtn').disabled = true;
            document.getElementById('deleteCaptureBtn').disabled = false;
            document.getElementById('deleteStopBtn').disabled = false;
        })
        .catch(error => {
            showDeleteMessage('Không thể truy cập camera: ' + error.message, 'danger');
        });
}

function stopDeleteCamera() {
    if (deleteStream) {
        deleteStream.getTracks().forEach(track => track.stop());
        deleteStream = null;
        document.getElementById('deleteVideo').srcObject = null;
        document.getElementById('deleteVideo').style.display = 'none';
        document.getElementById('deleteNoVideo').style.display = 'block';
        document.getElementById('deleteStartBtn').disabled = false;
        document.getElementById('deleteCaptureBtn').disabled = true;
        document.getElementById('deleteStopBtn').disabled = true;
    }
}

function captureDeletePhoto() {
    const video = document.getElementById('deleteVideo');
    const canvas = document.getElementById('deleteCanvas');
    const capturedImage = document.getElementById('deleteCapturedImage');

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0);

    deleteCapturedImageData = canvas.toDataURL('image/png');
    capturedImage.src = deleteCapturedImageData;
    capturedImage.style.display = 'block';
    document.getElementById('confirmDeleteBtn').disabled = false;
}

async function confirmDeleteFace() {
    if (!deleteCapturedImageData || !currentDeleteUserId || !currentDeleteUserType) {
        showDeleteMessage('Vui lòng chụp ảnh để xác nhận!', 'warning');
        return;
    }

    // Đảm bảo models đã được load
    if (!modelsLoaded) {
        showDeleteMessage('Đang tải models nhận diện khuôn mặt, vui lòng đợi...', 'info');
        const loaded = await loadModels();
        if (!loaded) {
            showDeleteMessage('Không thể tải models. Vui lòng thử lại!', 'danger');
            return;
        }
    }

    const confirmBtn = document.getElementById('confirmDeleteBtn');
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang xử lý...';

    showDeleteMessage('Đang xử lý ảnh và nhận diện khuôn mặt...', 'info');

    try {
        // Tạo image element từ base64
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.src = deleteCapturedImageData;

        await new Promise((resolve, reject) => {
            img.onload = resolve;
            img.onerror = () => reject(new Error('Không thể tải ảnh'));
            setTimeout(() => reject(new Error('Timeout khi tải ảnh')), 10000);
        });

        showDeleteMessage('Đang phát hiện và nhận diện khuôn mặt...', 'info');

        // Phát hiện khuôn mặt
        const detection = await faceapi
            .detectSingleFace(img, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection) {
            showDeleteMessage('Không phát hiện được khuôn mặt trong ảnh. Vui lòng chụp lại!', 'danger');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fas fa-trash me-1"></i> Xóa khuôn mặt';
            return;
        }

        // Lấy face encoding
        const faceEncoding = Array.from(detection.descriptor);

        // Gửi lên server để xóa
        const response = await fetch('?action=admin_delete_face_encoding', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: currentDeleteUserId,
                user_type: currentDeleteUserType,
                face_encoding: faceEncoding
            })
        });

        const result = await response.json();

        if (result.success) {
            showDeleteMessage('Xóa khuôn mặt thành công!', 'success');
            // Đóng modal sau 1.5 giây
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('deleteFaceModal')).hide();
                loadRegisteredFaces(); // Reload danh sách
            }, 1500);
        } else {
            showDeleteMessage('Lỗi: ' + (result.message || 'Không xác định'), 'danger');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fas fa-trash me-1"></i> Xóa khuôn mặt';
        }
    } catch (error) {
        console.error('Error deleting face:', error);
        showDeleteMessage('Lỗi khi xử lý: ' + error.message, 'danger');
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="fas fa-trash me-1"></i> Xóa khuôn mặt';
    }
}

function showDeleteMessage(message, type) {
    const messageDiv = document.getElementById('deleteFaceMessage');
    messageDiv.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
}

// Dừng camera khi đóng modal
document.getElementById('deleteFaceModal').addEventListener('hidden.bs.modal', function() {
    stopDeleteCamera();
});
</script>