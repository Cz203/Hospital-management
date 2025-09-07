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
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
                    <i class="fas fa-stethoscope me-3"></i>
                    <span>Khám bệnh</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
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
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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