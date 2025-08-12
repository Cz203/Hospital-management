<?php
session_start();
require_once 'config/database.php';
require_once 'Models/Patient.php';
require_once 'Models/Doctor.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /hospital_management/login");
    exit();
}

// Get patient information
$patient = new Patient();
$patient_info = $patient->getById($_SESSION['user_id']);

// Get all doctors for dynamic loading
$doctorModel = new Doctor();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /hospital_management/login");
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
    <title>Đặt lịch khám tại bệnh viện - Hệ thống Quản lý Bệnh viện</title>
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
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }

    .department-card {
        border: 2px solid #e9ecef;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .department-card:hover {
        border-color: #28a745;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .department-card.selected {
        border-color: #28a745;
        background: rgba(40, 167, 69, 0.05);
    }

    .department-icon {
        font-size: 2rem;
        color: #28a745;
        margin-bottom: 10px;
    }

    .doctor-card {
        border: 2px solid #e9ecef;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .doctor-card:hover {
        border-color: #28a745;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .doctor-card.selected {
        border-color: #28a745;
        background: rgba(40, 167, 69, 0.05);
    }

    .doctor-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #28a745;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
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
        border-color: #28a745;
        background: rgba(40, 167, 69, 0.05);
    }

    .time-slot.selected {
        border-color: #28a745;
        background: #28a745;
        color: white;
    }

    .btn-submit {
        padding: 15px 40px;
        border-radius: 25px;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
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

    .department-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .doctor-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }

    .time-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 10px;
    }

    .price-display {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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

    .alert-success {
        background: rgba(40, 167, 69, 0.1);
        border: 2px solid #28a745;
        color: #28a745;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
    }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/hospital_management/home">
                <i class="fas fa-hospital me-2"></i>Bệnh viện
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/hospital_management/home">Trang chủ</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item"
                                    href="/hospital_management/<?php echo $_SESSION['user_role']; ?>_dashboard">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a></li>
                            <li><a class="dropdown-item"
                                    href="/hospital_management/<?php echo $_SESSION['user_role']; ?>_profile">
                                    <i class="fas fa-user me-2"></i>Hồ sơ
                                </a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="/hospital_management/logout">
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
            <h1><i class="fas fa-hospital me-3"></i>Đặt lịch khám tại bệnh viện</h1>
            <p>Điền thông tin và chọn dịch vụ phù hợp</p>
        </div>

        <div class="booking-content">
            <form id="appointment-form">
                <!-- Patient Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="mb-3"><i class="fas fa-user me-2"></i>Thông tin bệnh nhân</h4>
                    </div>
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

                <!-- Appointment Details -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="mb-3"><i class="fas fa-calendar me-2"></i>Thông tin đặt lịch</h4>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Ngày khám</label>
                            <input type="date" class="form-control" id="visit-date"
                                min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Loại khám</label>
                            <select class="form-select" id="appointment-type" required>
                                <option value="">Chọn loại khám</option>
                                <option value="first">Khám lần đầu</option>
                                <option value="follow">Tái khám</option>
                                <option value="emergency">Khám cấp cứu</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Department Selection -->
                <div class="mb-4">
                    <h4 class="mb-3"><i class="fas fa-building me-2"></i>Chọn khoa khám</h4>
                    <div class="department-grid">
                        <div class="department-card" data-department="internal" data-price="200000">
                            <div class="department-icon">
                                <i class="fas fa-stethoscope"></i>
                            </div>
                            <h6>Khoa Nội</h6>
                            <p class="text-muted small">Điều trị các bệnh lý nội khoa</p>
                            <div class="text-success fw-bold">200,000 VNĐ</div>
                        </div>

                        <div class="department-card" data-department="surgery" data-price="300000">
                            <div class="department-icon">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <h6>Khoa Ngoại</h6>
                            <p class="text-muted small">Phẫu thuật và điều trị ngoại khoa</p>
                            <div class="text-success fw-bold">300,000 VNĐ</div>
                        </div>

                        <div class="department-card" data-department="cardiology" data-price="350000">
                            <div class="department-icon">
                                <i class="fas fa-heartbeat"></i>
                            </div>
                            <h6>Khoa Tim mạch</h6>
                            <p class="text-muted small">Chuyên điều trị bệnh lý tim mạch</p>
                            <div class="text-success fw-bold">350,000 VNĐ</div>
                        </div>

                        <div class="department-card" data-department="pediatrics" data-price="250000">
                            <div class="department-icon">
                                <i class="fas fa-child"></i>
                            </div>
                            <h6>Khoa Nhi</h6>
                            <p class="text-muted small">Chăm sóc sức khỏe trẻ em</p>
                            <div class="text-success fw-bold">250,000 VNĐ</div>
                        </div>

                        <div class="department-card" data-department="obstetrics" data-price="280000">
                            <div class="department-icon">
                                <i class="fas fa-baby"></i>
                            </div>
                            <h6>Khoa Sản</h6>
                            <p class="text-muted small">Chăm sóc sức khỏe phụ nữ</p>
                            <div class="text-success fw-bold">280,000 VNĐ</div>
                        </div>

                        <div class="department-card" data-department="neurology" data-price="400000">
                            <div class="department-icon">
                                <i class="fas fa-brain"></i>
                            </div>
                            <h6>Khoa Thần kinh</h6>
                            <p class="text-muted small">Điều trị bệnh lý thần kinh</p>
                            <div class="text-success fw-bold">400,000 VNĐ</div>
                        </div>
                    </div>
                </div>

                <!-- Doctor Selection -->
                <div class="mb-4" id="doctor-section" style="display: none;">
                    <h4 class="mb-3"><i class="fas fa-user-md me-2"></i>Chọn bác sĩ</h4>
                    <div class="doctor-grid" id="doctors-container">
                        <!-- Doctors will be loaded dynamically -->
                    </div>
                </div>

                <!-- Time Selection -->
                <div class="mb-4" id="time-section" style="display: none;">
                    <h4 class="mb-3"><i class="fas fa-clock me-2"></i>Chọn thời gian</h4>
                    <div class="time-grid">
                        <div class="time-slot" data-time="08:00">8:00</div>
                        <div class="time-slot" data-time="08:30">8:30</div>
                        <div class="time-slot" data-time="09:00">9:00</div>
                        <div class="time-slot" data-time="09:30">9:30</div>
                        <div class="time-slot" data-time="10:00">10:00</div>
                        <div class="time-slot" data-time="10:30">10:30</div>
                        <div class="time-slot" data-time="11:00">11:00</div>
                        <div class="time-slot" data-time="11:30">11:30</div>
                        <div class="time-slot" data-time="14:00">14:00</div>
                        <div class="time-slot" data-time="14:30">14:30</div>
                        <div class="time-slot" data-time="15:00">15:00</div>
                        <div class="time-slot" data-time="15:30">15:30</div>
                        <div class="time-slot" data-time="16:00">16:00</div>
                        <div class="time-slot" data-time="16:30">16:30</div>
                        <div class="time-slot" data-time="17:00">17:00</div>
                        <div class="time-slot" data-time="17:30">17:30</div>
                    </div>
                </div>

                <!-- Symptoms -->
                <div class="mb-4">
                    <div class="form-group">
                        <label class="form-label">Triệu chứng/Ghi chú</label>
                        <textarea class="form-control" id="symptoms" rows="3"
                            placeholder="Mô tả triệu chứng hoặc lý do khám bệnh"></textarea>
                    </div>
                </div>

                <!-- Price Display -->
                <div class="price-display" id="price-display" style="display: none;">
                    <h5>Chi phí khám</h5>
                    <div class="price-amount" id="price-amount">0 VNĐ</div>
                    <p class="mb-0">Bao gồm phí khám và tư vấn</p>
                </div>

                <!-- Submit Button -->
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-submit" id="submit-btn" disabled>
                        <i class="fas fa-check me-2"></i>Đặt lịch khám
                    </button>
                </div>
            </form>

            <!-- Success Message -->
            <div class="alert-success" id="success-message" style="display: none;">
                <h5><i class="fas fa-check-circle me-2"></i>Đặt lịch thành công!</h5>
                <p class="mb-2">Chúng tôi đã nhận được yêu cầu đặt lịch khám của bạn.</p>
                <p class="mb-0"><strong>Mã đặt lịch:</strong> <span id="booking-code"></span></p>
                <div class="mt-3">
                    <a href="/hospital_management/patient_dashboard" class="btn btn-outline-success me-2">
                        <i class="fas fa-tachometer-alt me-2"></i>Về Dashboard
                    </a>
                    <a href="/hospital_management/home" class="btn btn-outline-primary">
                        <i class="fas fa-home me-2"></i>Về trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    let selectedDepartment = null;
    let selectedDoctor = null;
    let selectedTime = null;

    // Get doctors data from PHP
    const doctorsData = <?php echo json_encode($doctorModel->getAll()); ?>;

    // Map department codes to Vietnamese specialties
    const departmentMapping = {
        'internal': 'Nội tổng quát',
        'surgery': 'Ngoại khoa',
        'cardiology': 'Tim mạch',
        'pediatrics': 'Nhi khoa',
        'obstetrics': 'Sản phụ khoa',
        'neurology': 'Thần kinh'
    };

    // Department selection
    document.querySelectorAll('.department-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.department-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            selectedDepartment = {
                name: this.querySelector('h6').textContent,
                code: this.dataset.department,
                price: parseInt(this.dataset.price)
            };

            // Show doctor section and load doctors
            document.getElementById('doctor-section').style.display = 'block';
            loadDoctors();
            updatePrice();
            checkFormCompletion();
        });
    });

    // Time selection
    document.querySelectorAll('.time-slot').forEach(slot => {
        slot.addEventListener('click', function() {
            document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
            this.classList.add('selected');
            selectedTime = this.dataset.time;
            checkFormCompletion();
        });
    });

    function loadDoctors() {
        const container = document.getElementById('doctors-container');
        const targetSpecialty = departmentMapping[selectedDepartment.code];

        // Filter doctors by specialty
        const doctors = doctorsData.filter(doctor =>
            doctor.chuyen_khoa === targetSpecialty
        );

        if (doctors.length === 0) {
            container.innerHTML =
                '<div class="col-12 text-center"><p class="text-muted">Không có bác sĩ nào trong chuyên khoa này</p></div>';
            return;
        }

        container.innerHTML = doctors.map(doctor => {
            const avatarText = doctor.ten.split(' ').slice(-2).map(n => n[0]).join('').toUpperCase();
            return `
                <div class="doctor-card" data-doctor-id="${doctor.id}">
                    <div class="doctor-avatar">${avatarText}</div>
                    <h6>${doctor.ten}</h6>
                    <p class="text-muted small mb-2">${doctor.chuyen_khoa}</p>
                    <p class="text-success small mb-3">${doctor.so_nam_kinh_nghiem || 0} năm kinh nghiệm</p>
                    <div class="doctor-info">
                        <span class="badge bg-success">Có lịch</span>
                        <span class="badge bg-info ms-1">${doctor.so_dien_thoai || 'Liên hệ'}</span>
                    </div>
                </div>
            `;
        }).join('');

        // Add click events to doctor cards
        document.querySelectorAll('.doctor-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.doctor-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                const doctorId = this.dataset.doctorId;
                const doctor = doctors.find(d => d.id == doctorId);
                selectedDoctor = doctor;
                // Show time section
                document.getElementById('time-section').style.display = 'block';
                checkFormCompletion();
            });
        });
    }

    function updatePrice() {
        if (selectedDepartment) {
            document.getElementById('price-display').style.display = 'block';
            document.getElementById('price-amount').textContent = formatPrice(selectedDepartment.price);
        }
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('vi-VN').format(price) + ' VNĐ';
    }

    function checkFormCompletion() {
        const date = document.getElementById('visit-date').value;
        const type = document.getElementById('appointment-type').value;
        const submitBtn = document.getElementById('submit-btn');

        if (date && type && selectedDepartment && selectedDoctor && selectedTime) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
    }

    // Form validation
    document.getElementById('visit-date').addEventListener('change', checkFormCompletion);
    document.getElementById('appointment-type').addEventListener('change', checkFormCompletion);

    // Form submission
    document.getElementById('appointment-form').addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate form
        const date = document.getElementById('visit-date').value;
        const type = document.getElementById('appointment-type').value;

        if (!date || !type || !selectedDepartment || !selectedDoctor || !selectedTime) {
            alert('Vui lòng điền đầy đủ thông tin');
            return;
        }

        const selectedDate = new Date(date);
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);

        if (selectedDate < tomorrow) {
            alert('Ngày khám phải từ ngày mai trở đi');
            return;
        }

        // Show success message
        document.getElementById('appointment-form').style.display = 'none';
        document.getElementById('success-message').style.display = 'block';

        // Generate booking code
        const bookingCode = 'BV' + Date.now().toString().slice(-6);
        document.getElementById('booking-code').textContent = bookingCode;
    });
    </script>
</body>

</html>