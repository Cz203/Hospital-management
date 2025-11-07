<div class="sidebar bg-success text-white"
    style="width: 250px; min-height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000;">
    <div class="sidebar-header p-3 border-bottom border-light">
        <div class="d-flex align-items-center">
            <i class="fas fa-user-md fa-2x text-white me-3"></i>
            <div>
                <h6 class="mb-0 fw-bold">Doctor Panel</h6>
                <small class="text-light"><?php echo $_SESSION['specialization'] ?? 'Chuyên khoa'; ?></small>
            </div>
        </div>
    </div>

    <div class="sidebar-body p-0">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo isActiveUrl('doctor_dashboard'); ?>"
                    href="./doctor_dashboard">
                    <i class="fas fa-tachometer-alt me-3"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo isActiveUrl('doctor_today_appointments'); ?>"
                    href="./doctor_today_appointments">
                    <i class="fas fa-calendar-check me-3"></i>
                    <span>Lịch hẹn hôm nay</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo (strpos($_SERVER['REQUEST_URI'], 'doctor_appointment_management') !== false) ? 'active' : ''; ?>"
                    href="./doctor_appointment_management">
                    <i class="fas fa-calendar-check me-3"></i>
                    <span>Quản lý lịch hẹn</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo isActiveUrl('doctor_schedule_management'); ?>"
                    href="./doctor_schedule_management">
                    <i class="fas fa-calendar-alt me-3"></i>
                    <span>Đăng ký ca trực</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
                    <i class="fas fa-user-injured me-3"></i>
                    <span>Danh sách bệnh nhân</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo isActiveUrl('doctor_examination'); ?>"
                    href="./doctor_examination">
                    <i class="fas fa-stethoscope me-3"></i>
                    <span>Khám bệnh</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo isActiveUrl('doctor_medical_records'); ?>"
                    href="./doctor_medical_records">
                    <i class="fas fa-file-medical me-3"></i>
                    <span>Hồ sơ bệnh án</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
                    <i class="fas fa-prescription me-3"></i>
                    <span>Kê đơn thuốc</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
                    <i class="fas fa-procedures me-3"></i>
                    <span>Chỉ định xét nghiệm</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
                    <i class="fas fa-chart-line me-3"></i>
                    <span>Theo dõi bệnh nhân</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
                    <i class="fas fa-bell me-3"></i>
                    <span>Thông báo</span>
                </a>
            </li>


        </ul>
    </div>


</div>

<style>
    .sidebar {
        backdrop-filter: blur(6px);
        box-shadow: 4px 0 24px rgba(0, 0, 0, .08);
        background: linear-gradient(180deg, #0d6efd 0%, #1f86ff 45%, #f7fbff 100%) !important;
        color: #ffffff;
        border-right: 1px solid rgba(255, 255, 255, 0.08);
    }

    .sidebar-header {
        position: sticky;
        top: 0;
        z-index: 2;
        background: rgba(255, 255, 255, 0.06);
    }

    .sidebar .sidebar-body {
        height: calc(100vh - 64px);
        overflow-y: auto;
    }

    .sidebar .nav-link {
        color: #ffffff !important;
        border-left: 3px solid transparent;
        border-radius: 10px;
        margin: 4px 8px;
        padding: 0.75rem 0.9rem;
        transition: background .2s ease, border-color .2s ease, transform .2s ease;
    }

    .sidebar .nav-link i {
        width: 20px;
        text-align: center;
    }

    .sidebar .nav-link:hover {
        background: rgba(255, 255, 255, 0.12);
        transform: translateX(2px);
    }

    .sidebar .nav-link.active {
        background: rgba(255, 255, 255, 0.2);
        border-left-color: #0d6efd;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.12);
    }

    .user-avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0d6efd 0%, #1f86ff 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
    }

    .sidebar .sidebar-body::-webkit-scrollbar {
        width: 8px;
    }

    .sidebar .sidebar-body::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.06);
    }

    .sidebar .sidebar-body::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.26);
        border-radius: 8px;
    }

    .sidebar .sidebar-body::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.36);
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