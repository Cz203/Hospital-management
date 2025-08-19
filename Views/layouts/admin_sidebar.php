<div class="sidebar bg-dark text-white"
    style="width: 250px; min-height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000;">
    <div class="sidebar-header p-3 border-bottom border-secondary">
        <div class="d-flex align-items-center">
            <i class="fas fa-hospital fa-2x text-primary me-3"></i>
            <div>
                <h6 class="mb-0 fw-bold">Hospital Management</h6>
                <small class="text-muted">Admin Panel</small>
            </div>
        </div>
    </div>

    <div class="sidebar-body p-0">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo isActiveUrl('admin_dashboard'); ?>"
                    href="./admin_dashboard">
                    <i class="fas fa-tachometer-alt me-3"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
                    <i class="fas fa-users me-3"></i>
                    <span>Quản lý người dùng</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
                    <i class="fas fa-user-md me-3"></i>
                    <span>Quản lý bác sĩ</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
                    <i class="fas fa-user-injured me-3"></i>
                    <span>Quản lý bệnh nhân</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
                    <i class="fas fa-calendar-alt me-3"></i>
                    <span>Quản lý lịch hẹn</span>
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
                    <i class="fas fa-pills me-3"></i>
                    <span>Quản lý thuốc</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
                    <i class="fas fa-bed me-3"></i>
                    <span>Quản lý phòng bệnh</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
                    <i class="fas fa-chart-bar me-3"></i>
                    <span>Báo cáo thống kê</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
                    <i class="fas fa-cog me-3"></i>
                    <span>Cài đặt hệ thống</span>
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
        border-left-color: #007bff;
    }

    .sidebar .nav-link.active {
        background-color: rgba(255, 255, 255, 0.2);
        border-left-color: #007bff;
        font-weight: 600;
        box-shadow: inset 0 0 10px rgba(255, 255, 255, 0.1);
    }

    .user-avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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