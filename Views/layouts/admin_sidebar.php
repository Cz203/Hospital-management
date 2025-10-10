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
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo isActiveUrl('doctors_list'); ?>"
                    href="./doctors_list">
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
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo isActiveUrl('doctor_schedules'); ?>"
                    href="./doctor_schedules">
                    <i class="fas fa-calendar-check me-3"></i>
                    <span>Lịch làm việc bác sĩ</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo isActiveUrl('specialties'); ?>"
                    href="./specialties">
                    <i class="fas fa-stethoscope me-3"></i>
                    <span>Quản lý chuyên khoa</span>
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
.sidebar {
    backdrop-filter: blur(6px);
    box-shadow: 4px 0 24px rgba(0, 0, 0, .08);
}

.sidebar-header {
    position: sticky;
    top: 0;
    z-index: 2;
    background: rgba(255, 255, 255, 0.04);
}

.sidebar .sidebar-body {
    height: calc(100vh - 64px);
    overflow-y: auto;
}

.sidebar .nav-link {
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
    background: rgba(255, 255, 255, 0.08);
    transform: translateX(2px);
}

.sidebar .nav-link.active {
    background: rgba(13, 110, 253, 0.18);
    border-left-color: #0d6efd;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08);
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
    background: rgba(255, 255, 255, 0.18);
    border-radius: 8px;
}

.sidebar .sidebar-body::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.28);
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