<div class="sidebar">
    <div class="sidebar-header px-3 py-3">
        <div class="d-flex align-items-center">
            <div class="brand-icon d-flex align-items-center justify-content-center rounded-circle me-3 bg-accent-soft">
                <i class="fas fa-concierge-bell"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-0 fw-semibold clinic-title">Lễ tân - Phòng khám đa khoa</h6>
                <small class="text-muted d-block">Tiếp đón & hỗ trợ bệnh nhân</small>
            </div>
        </div>
    </div>

    <div class="sidebar-body p-0">
        <ul class="nav flex-column">
            <!-- Tổng quan -->
            <li class="nav-item mt-2 mb-1 px-3">
                <div class="sidebar-section-label">Tổng quan</div>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center <?php echo isActiveUrl('reception_dashboard'); ?>"
                    href="./reception_dashboard">
                    <i class="fas fa-house-user me-3"></i>
                    <span>Trang bệnh nhân</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center <?php echo isActiveUrl('reception_attendance'); ?>"
                    href="./reception_attendance">
                    <i class="fas fa-clock me-3"></i>
                    <span>Chấm công</span>
                </a>
            </li>

            <!-- Lịch & xếp số -->
            <li class="nav-item mt-3 mb-1 px-3">
                <div class="sidebar-section-label">Lịch & xếp số</div>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center <?php echo isActiveUrl('reception_doctor_schedules'); ?>"
                    href="./reception_doctor_schedules">
                    <i class="fas fa-user-md me-3"></i>
                    <span>Lịch làm việc bác sĩ</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center <?php echo isActiveUrl('reception_schedule_management'); ?>"
                    href="./reception_schedule_management">
                    <i class="fas fa-calendar-alt me-3"></i>
                    <span>Lịch làm việc lễ tân</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center <?php echo isActiveUrl('patient_appointments'); ?>"
                    href="./patient_appointments">
                    <i class="fas fa-calendar-check me-3"></i>
                    <span>Lịch hẹn bệnh nhân</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center <?php echo isActiveUrl('reception_queue'); ?>"
                    href="./reception_queue">
                    <i class="fas fa-sort-numeric-down me-3"></i>
                    <span>Bốc số</span>
                </a>
            </li>

            <!-- Bệnh nhân & chuyên khoa -->
            <li class="nav-item mt-3 mb-1 px-3">
                <div class="sidebar-section-label">Bệnh nhân & chuyên khoa</div>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center <?php echo isActiveUrl('reception_patient_create'); ?>"
                    href="./reception_patient_create">
                    <i class="fas fa-user-plus me-3"></i>
                    <span>Thêm bệnh nhân</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center <?php echo isActiveUrl('doctors_by_specialty'); ?>"
                    href="./specialties_all">
                    <i class="fas fa-stethoscope me-3"></i>
                    <span>Bác sĩ theo chuyên khoa</span>
                </a>
            </li>

            <!-- Thanh toán -->
            <li class="nav-item mt-3 mb-1 px-3">
                <div class="sidebar-section-label">Thanh toán</div>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link d-flex align-items-center <?php echo isActiveUrl('reception_payment'); ?>"
                    href="./reception_payment">
                    <i class="fas fa-credit-card me-3"></i>
                    <span>Thanh toán</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
    /* Clinic theme giống admin/doctor/patient */
    :root {
        --clinic-bg: #ffffff;
        --clinic-surface: #f6fbfb;
        --clinic-text: #0f1724;
        --clinic-muted: #667085;
        --clinic-accent: #0d6efd;
        --clinic-accent-strong: #0a58ca;
        --clinic-border: rgba(13, 20, 25, 0.06);
    }

    .sidebar {
        background: linear-gradient(180deg, var(--clinic-bg), var(--clinic-surface));
        box-shadow: 6px 0 26px rgba(15, 23, 36, 0.06);
        color: var(--clinic-text) !important;
        width: 250px;
        min-height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        z-index: 1000;
    }

    .sidebar-header {
        position: sticky;
        top: 0;
        z-index: 3;
        background: linear-gradient(90deg, #ecfeff, #f1f5f9);
        border-bottom: 1px solid var(--clinic-border);
    }

    .brand-icon {
        width: 40px;
        height: 40px;
        box-shadow: 0 4px 14px rgba(13, 110, 253, 0.25);
    }

    .brand-icon i {
        color: var(--clinic-accent);
        font-size: 1.1rem;
    }

    .sidebar-header .clinic-title {
        letter-spacing: .03em;
    }

    .sidebar-header h6 {
        color: var(--clinic-text);
    }

    .sidebar-header small {
        color: var(--clinic-muted);
    }

    .sidebar .sidebar-body {
        height: calc(100vh - 80px);
        overflow-y: auto;
        padding-bottom: 24px;
    }

    .sidebar .nav-link {
        border-left: 3px solid transparent;
        border-radius: 8px;
        margin: 6px 10px;
        padding: 0.66rem 0.9rem;
        transition: background .18s ease, border-color .18s ease, transform .18s ease, box-shadow .18s ease;
        width: calc(100% - 20px);
        text-align: left;
        color: var(--clinic-text) !important;
        background: transparent;
    }

    .sidebar .nav-link i {
        width: 22px;
        text-align: center;
        color: var(--clinic-accent);
    }

    .sidebar .nav-link:hover {
        background: rgba(13, 110, 253, 0.06);
        transform: translateX(3px);
        box-shadow: 0 4px 14px rgba(13, 110, 253, 0.04);
    }

    .sidebar .nav-link.active {
        background: rgba(13, 110, 253, 0.12);
        border-left-color: var(--clinic-accent);
        box-shadow: inset 0 0 0 1px rgba(10, 88, 202, 0.03);
        color: var(--clinic-accent-strong) !important;
    }

    .sidebar-section-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--clinic-muted);
        font-weight: 600;
    }

    .bg-accent-soft {
        background: rgba(13, 110, 253, 0.12);
    }

    .sidebar .sidebar-body::-webkit-scrollbar {
        width: 8px;
    }

    .sidebar .sidebar-body::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar .sidebar-body::-webkit-scrollbar-thumb {
        background: rgba(15, 23, 36, 0.08);
        border-radius: 8px;
    }

    .sidebar .sidebar-body::-webkit-scrollbar-thumb:hover {
        background: rgba(15, 23, 36, 0.12);
    }

    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.28s ease;
        }

        .sidebar.show {
            transform: translateX(0);
        }
    }
</style>