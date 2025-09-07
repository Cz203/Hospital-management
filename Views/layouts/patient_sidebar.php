<div class="sidebar bg-primary text-white"
    style="width: 250px; min-height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000;">
    <div class="sidebar-header p-3 border-bottom border-light">
        <div class="d-flex align-items-center">
            <i class="fas fa-user-injured fa-2x text-white me-3"></i>
            <div>
                <h6 class="mb-0 fw-bold">Patient Portal</h6>
                <small class="text-light">Chăm sóc sức khỏe</small>
            </div>
        </div>
    </div>

    <div class="sidebar-body p-0">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo (strpos($_SERVER['REQUEST_URI'], 'patient_dashboard') !== false) ? 'active' : ''; ?>"
                    href="./patient_dashboard">
                    <i class="fas fa-tachometer-alt me-3"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo (strpos($_SERVER['REQUEST_URI'], 'hospital_appointment') !== false) ? 'active' : ''; ?>"
                    href="./doctor_team">
                    <i class="fas fa-calendar-plus me-3"></i>
                    <span>Đặt lịch hẹn</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo isActiveUrl('patient_appointments'); ?>"
                    href="./patient_appointments">
                    <i class="fas fa-calendar-check me-3"></i>
                    <span>Lịch hẹn của tôi</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo isActiveUrl('patient_medical_records'); ?>"
                    href="./patient_medical_records">
                    <i class="fas fa-file-medical me-3"></i>
                    <span>Hồ sơ bệnh án</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
                    <i class="fas fa-prescription me-3"></i>
                    <span>Đơn thuốc</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
                    <i class="fas fa-flask me-3"></i>
                    <span>Kết quả xét nghiệm</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
                    <i class="fas fa-user-md me-3"></i>
                    <span>Bác sĩ của tôi</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
                    <i class="fas fa-credit-card me-3"></i>
                    <span>Thanh toán</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
                    <i class="fas fa-bell me-3"></i>
                    <span>Thông báo</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
                    <i class="fas fa-question-circle me-3"></i>
                    <span>Hỗ trợ</span>
                </a>
            </li>


        </ul>
    </div>


</div>

<style>
.sidebar .nav-link {
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.sidebar .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-left-color: #ffffff;
}

.sidebar .nav-link.active {
    background-color: rgba(255, 255, 255, 0.2);
    border-left-color: #ffffff;
    font-weight: 600;
    box-shadow: inset 0 0 10px rgba(255, 255, 255, 0.1);
}

.user-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    .sidebar.show {
        transform: translateX(0);
    }
}
</style>