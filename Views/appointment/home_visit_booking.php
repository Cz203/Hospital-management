<?php

require_once 'config/database.php';
require_once 'Models/Patient.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login");
    exit();
}

// Get patient information
$patient = new Patient();
$patient_info = $patient->getById($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lịch khám tại nhà - Hệ thống Quản lý Bệnh viện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .booking-container {
        max-width: 800px;
        margin: 50px auto;
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .booking-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        text-align: center;
    }

    .booking-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 700;
    }

    .booking-header p {
        margin: 10px 0 0 0;
        opacity: 0.9;
        font-size: 1.1rem;
    }

    .booking-content {
        padding: 40px;
    }

    .step-indicator {
        display: flex;
        justify-content: center;
        margin-bottom: 40px;
    }

    .step {
        display: flex;
        align-items: center;
        margin: 0 15px;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 10px;
        transition: all 0.3s ease;
    }

    .step.active .step-number {
        background: #667eea;
        color: white;
    }

    .step.completed .step-number {
        background: #28a745;
        color: white;
    }

    .step-text {
        font-weight: 500;
        color: #6c757d;
    }

    .step.active .step-text {
        color: #667eea;
    }

    .step.completed .step-text {
        color: #28a745;
    }

    .form-step {
        display: none;
    }

    .form-step.active {
        display: block;
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
        display: block;
    }

    .form-control,
    .form-select {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 12px 15px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .service-card {
        border: 2px solid #e9ecef;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .service-card:hover {
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .service-card.selected {
        border-color: #667eea;
        background: rgba(102, 126, 234, 0.05);
    }

    .service-icon {
        font-size: 2rem;
        color: #667eea;
        margin-bottom: 10px;
    }

    .time-slot {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }

    .time-slot:hover {
        border-color: #667eea;
        background: rgba(102, 126, 234, 0.05);
    }

    .time-slot.selected {
        border-color: #667eea;
        background: #667eef;
        color: white;
    }

    .btn-navigation {
        padding: 12px 30px;
        border-radius: 25px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-outline-primary {
        border: 2px solid #667eea;
        color: #667eea;
    }

    .btn-outline-primary:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
    }

    .summary-card {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #dee2e6;
    }

    .summary-item:last-child {
        border-bottom: none;
    }

    .summary-label {
        font-weight: 600;
        color: #495057;
    }

    .summary-value {
        color: #667eea;
        font-weight: 500;
    }

    .price-display {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        margin-top: 20px;
    }

    .price-amount {
        font-size: 2rem;
        font-weight: 700;
        margin: 10px 0;
    }

    .navbar {
        background: rgba(255, 255, 255, 0.1) !important;
        backdrop-filter: blur(10px);
    }

    .navbar-brand {
        color: white !important;
        font-weight: 700;
        font-size: 1.5rem;
    }

    .nav-link {
        color: white !important;
    }

    .nav-link:hover {
        color: rgba(255, 255, 255, 0.8) !important;
    }

    .success-animation {
        text-align: center;
        padding: 40px 20px;
    }

    .success-icon {
        font-size: 4rem;
        color: #28a745;
        margin-bottom: 20px;
        animation: bounce 1s ease;
    }

    @keyframes bounce {

        0%,
        20%,
        50%,
        80%,
        100% {
            transform: translateY(0);
        }

        40% {
            transform: translateY(-10px);
        }

        60% {
            transform: translateY(-5px);
        }
    }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="./home">
                <i class="fas fa-hospital me-2"></i>Bệnh viện
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="./home">Trang chủ</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="./<?php echo $_SESSION['user_role']; ?>_dashboard">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a></li>
                            <li><a class="dropdown-item" href="./<?php echo $_SESSION['user_role']; ?>_profile">
                                    <i class="fas fa-user me-2"></i>Hồ sơ
                                </a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="./logout">
                                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                </a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="booking-container">
        <div class="booking-header">
            <h1><i class="fas fa-home me-3"></i>Đặt lịch khám tại nhà</h1>
            <p>Chọn dịch vụ và thời gian phù hợp với bạn</p>
        </div>

        <div class="booking-content">
            <form id="home-visit-form">
                <h3 class="mb-4"><i class="fas fa-user me-2"></i>Thông tin đặt lịch</h3>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" id="patient-name"
                                value="<?php echo htmlspecialchars($patient_info['ten']); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Số điện thoại</label>
                            <input type="tel" class="form-control" id="patient-phone"
                                value="<?php echo htmlspecialchars($patient_info['so_dien_thoai']); ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label">Địa chỉ khám tại nhà</label>
                            <textarea class="form-control" id="home-address" rows="3"
                                placeholder="Nhập địa chỉ chi tiết nơi bạn muốn bác sĩ đến khám" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Ngày khám</label>
                            <input type="date" class="form-control" id="visit-date"
                                min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Giờ khám</label>
                            <div class="row g-2">
                                <div class="col-3">
                                    <div class="time-slot" data-time="08:00">8:00</div>
                                </div>
                                <div class="col-3">
                                    <div class="time-slot" data-time="09:00">9:00</div>
                                </div>
                                <div class="col-3">
                                    <div class="time-slot" data-time="10:00">10:00</div>
                                </div>
                                <div class="col-3">
                                    <div class="time-slot" data-time="11:00">11:00</div>
                                </div>
                                <div class="col-3">
                                    <div class="time-slot" data-time="14:00">14:00</div>
                                </div>
                                <div class="col-3">
                                    <div class="time-slot" data-time="15:00">15:00</div>
                                </div>
                                <div class="col-3">
                                    <div class="time-slot" data-time="16:00">16:00</div>
                                </div>
                                <div class="col-3">
                                    <div class="time-slot" data-time="17:00">17:00</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Dịch vụ</label>
                            <select id="service-select" class="form-select" required>
                                <option value="">Chọn dịch vụ</option>
                                <option value="general" data-price="500000">Khám tổng quát - 500,000 VNĐ</option>
                                <option value="specialist" data-price="800000">Khám chuyên khoa - 800,000 VNĐ</option>
                                <option value="elderly" data-price="600000">Khám người già - 600,000 VNĐ</option>
                                <option value="pediatric" data-price="700000">Khám nhi khoa - 700,000 VNĐ</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="price-display" id="price-display" style="display:none;">
                            <h6 class="mb-1">Tổng chi phí</h6>
                            <div class="price-amount" id="price-amount">0 VNĐ</div>
                            <p class="mb-0">Bao gồm phí khám và phí di chuyển</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">Ghi chú</label>
                            <textarea class="form-control" id="notes" rows="2"
                                placeholder="Mô tả triệu chứng hoặc yêu cầu đặc biệt (không bắt buộc)"></textarea>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary btn-navigation">
                        <i class="fas fa-check me-2"></i>Đặt lịch khám
                    </button>
                </div>
            </form>

            <!-- Success -->
            <div class="form-step" id="success-content" style="display:none;">
                <div class="success-animation">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="text-success mb-3">Đặt lịch thành công!</h3>
                    <p class="text-muted mb-4">Chúng tôi đã nhận được yêu cầu đặt lịch khám tại nhà của bạn. Bác sĩ sẽ
                        liên hệ để xác nhận trong thời gian sớm nhất.</p>
                    <div class="alert alert-info">
                        <strong>Mã đặt lịch:</strong> <span id="booking-code"></span>
                    </div>
                    <div class="mt-4">
                        <a href="./patient_dashboard" class="btn btn-primary btn-navigation me-3">
                            <i class="fas fa-tachometer-alt me-2"></i>Về Dashboard
                        </a>
                        <a href="./home" class="btn btn-outline-primary btn-navigation">
                            <i class="fas fa-home me-2"></i>Về trang chủ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    let selectedTime = null;

    // chọn giờ
    document.querySelectorAll('.time-slot').forEach(slot => {
        slot.addEventListener('click', function() {
            document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
            this.classList.add('selected');
            selectedTime = this.dataset.time;
        });
    });

    // chọn dịch vụ -> cập nhật giá
    const serviceSelect = document.getElementById('service-select');
    const priceDisplay = document.getElementById('price-display');
    const priceAmount = document.getElementById('price-amount');
    serviceSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const price = option.getAttribute('data-price');
        if (price) {
            priceDisplay.style.display = 'block';
            priceAmount.textContent = new Intl.NumberFormat('vi-VN').format(parseInt(price)) + ' VNĐ';
        } else {
            priceDisplay.style.display = 'none';
        }
    });

    // submit form đơn giản
    document.getElementById('home-visit-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const address = document.getElementById('home-address').value.trim();
        const date = document.getElementById('visit-date').value;
        const serviceVal = serviceSelect.value;

        if (!address) return alert('Vui lòng nhập địa chỉ khám tại nhà');
        if (!date) return alert('Vui lòng chọn ngày khám');
        if (!selectedTime) return alert('Vui lòng chọn giờ khám');
        if (!serviceVal) return alert('Vui lòng chọn dịch vụ');

        const selectedDate = new Date(date);
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        if (selectedDate < tomorrow) return alert('Ngày khám phải từ ngày mai trở đi');

        // Hiển thị thành công
        document.getElementById('home-visit-form').style.display = 'none';
        const success = document.getElementById('success-content');
        success.style.display = 'block';
        document.getElementById('booking-code').textContent = 'HV' + Date.now().toString().slice(-6);
    });
    </script>
</body>

</html>