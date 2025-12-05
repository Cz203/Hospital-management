<?php
$current_uri = $_SERVER['REQUEST_URI'] ?? '';
$userMgmtOpen = (strpos($current_uri, 'doctors_list') !== false
    || strpos($current_uri, 'patients') !== false
    || strpos($current_uri, 'reception') !== false
    || strpos($current_uri, 'specialties') !== false)
    ? 'open'
    : '';

$appointmentsOpen = (strpos($current_uri, 'admin_appointments') !== false
    || strpos($current_uri, 'doctor_schedules') !== false
    || strpos($current_uri, 'admin_reception_schedules') !== false)
    ? 'open'
    : '';
?>

<div class="sidebar">
    <div class="sidebar-header px-3 py-3">
        <div class="d-flex align-items-center">
            <div class="brand-icon d-flex align-items-center justify-content-center rounded-circle me-3 bg-accent-soft">
                <i class="fas fa-clinic-medical"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-0 fw-semibold clinic-title">Phòng khám đa khoa</h6>
                <small class="text-muted d-block">Bảng điều khiển quản trị</small>
            </div>
        </div>
    </div>

    <div class="sidebar-body p-0">
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item mt-2 mb-1 px-3">
                <div class="sidebar-section-label">Tổng quan</div>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center <?php echo isActiveUrl('admin_dashboard'); ?>"
                    href="./admin_dashboard">
                    <i class="fas fa-tachometer-alt me-3"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- User Management group -->
            <li class="nav-item mt-3 mb-1 px-3">
                <div class="sidebar-section-label">Quản lý người dùng</div>
            </li>
            <li class="nav-item">
                <button
                    class="nav-link d-flex align-items-center justify-content-between btn-toggle <?php echo $userMgmtOpen; ?>"
                    aria-expanded="<?php echo ($userMgmtOpen ? 'true' : 'false'); ?>">
                    <span class="d-flex align-items-center">
                        <i class="fas fa-users me-3"></i>
                        <span>Quản lý nhân sự</span>
                    </span>
                    <i class="fas fa-chevron-down small"></i>
                </button>
                <ul class="submenu <?php echo $userMgmtOpen; ?>">
                    <li>
                        <a class="nav-link <?php echo isActiveUrl('doctors_list'); ?>" href="./doctors_list">
                            <i class="fas fa-user-md me-2"></i>
                            <span>Bác sĩ</span>
                        </a>
                    </li>
                    <li>
                        <a class="nav-link" href="./patients">
                            <i class="fas fa-user-injured me-2"></i>
                            <span>Bệnh nhân</span>
                        </a>
                    </li>
                    <li>
                        <a class="nav-link" href="./reception_list">
                            <i class="fas fa-user-tie me-2"></i>
                            <span>Lễ tân</span>
                        </a>
                    </li>

                </ul>
            </li>

            <!-- Appointments & schedules group -->
            <li class="nav-item mt-3 mb-1 px-3">
                <div class="sidebar-section-label">Lịch & hẹn</div>
            </li>
            <li class="nav-item">
                <button
                    class="nav-link d-flex align-items-center justify-content-between btn-toggle <?php echo $appointmentsOpen; ?>"
                    aria-expanded="<?php echo ($appointmentsOpen ? 'true' : 'false'); ?>">
                    <span class="d-flex align-items-center">
                        <i class="fas fa-calendar-alt me-3"></i>
                        <span>Quản lý lịch hẹn</span>
                    </span>
                    <i class="fas fa-chevron-down small"></i>
                </button>
                <ul class="submenu <?php echo $appointmentsOpen; ?>">
                    <li>
                        <a class="nav-link <?php echo isActiveUrl('admin_appointments'); ?>"
                            href="./admin_appointments">
                            <i class="fas fa-calendar-check me-2"></i>
                            <span>Lịch hẹn khám</span>
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo isActiveUrl('doctor_schedules'); ?>" href="./doctor_schedules">
                            <i class="fas fa-user-md me-2"></i>
                            <span>Lịch làm việc bác sĩ</span>
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo isActiveUrl('admin_reception_schedules'); ?>"
                            href="./admin_reception_schedules">
                            <i class="fas fa-user-clock me-2"></i>
                            <span>Lịch làm việc lễ tân</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Clinical & resources -->
            <li class="nav-item mt-3 mb-1 px-3">
                <div class="sidebar-section-label">Nghiệp vụ & tài nguyên</div>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center <?php echo isActiveUrl('specialties'); ?>"
                    href="./specialties">
                    <i class="fas fa-th-large me-3"></i>
                    <span>Quản lý chuyên khoa</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="./medical_records">
                    <i class="fas fa-file-medical me-3"></i>
                    <span>Hồ sơ bệnh án</span>
                </a>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center <?php echo isActiveUrl('admin_face_registration'); ?>"
                    href="./admin_face_registration">
                    <i class="fas fa-user-check me-3"></i>
                    <span>Đăng ký nhận diện khuôn mặt</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link d-flex align-items-center" href="./settings">
                    <i class="fas fa-cog me-3"></i>
                    <span>Cài đặt hệ thống</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
    /* Clinic-friendly color palette: soft whites with blue accents */
    :root {
        --clinic-bg: #ffffff;
        --clinic-surface: #f6fbfb;
        --clinic-text: #0f1724;
        --clinic-muted: #667085;
        --clinic-accent: #0d6efd;
        /* blue */
        --clinic-accent-strong: #0a58ca;
        --clinic-border: rgba(13, 20, 25, 0.06);
    }

    .sidebar {
        background: linear-gradient(180deg, var(--clinic-bg), var(--clinic-surface));
        box-shadow: 6px 0 26px rgba(15, 23, 36, 0.06);
        color: var(--clinic-text) !important;
        /* override text-white */
        width: 260px;
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

    .sidebar-header .fa-hospital {
        color: var(--clinic-accent);
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

    .sidebar .nav-link,
    .sidebar .btn-toggle {
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

    .sidebar .nav-link i,
    .sidebar .btn-toggle i {
        width: 22px;
        text-align: center;
        color: var(--clinic-accent);
    }

    .sidebar .nav-link:hover,
    .sidebar .btn-toggle:hover {
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

    .btn-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: transparent;
        color: inherit;
        border: none;
        cursor: pointer;
    }

    .btn-toggle:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.12);
    }

    .btn-toggle .small {
        opacity: 0.9;
    }

    .submenu {
        list-style: none;
        margin: 0 0 8px 0;
        padding: 0;
        max-height: 0;
        overflow: hidden;
        transition: max-height .28s ease, opacity .2s ease;
        opacity: 0;
    }

    .submenu.open {
        opacity: 1;
        max-height: 480px;
    }

    .submenu li {
        margin: 0;
    }

    .submenu .nav-link {
        padding-left: 34px;
        border-radius: 6px;
        font-size: .95rem;
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

    /* Footer / push to bottom */
    .sidebar .nav .mt-auto {
        margin-top: auto;
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

    /* Responsive */
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

<script>
    // Sidebar submenu toggle - mỗi dropdown hoạt động độc lập
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            var toggles = document.querySelectorAll('.sidebar .btn-toggle');
            toggles.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var next = btn.nextElementSibling;
                    if (!next) return;
                    var isOpen = next.classList.contains('open');
                    if (isOpen) {
                        next.classList.remove('open');
                        btn.setAttribute('aria-expanded', 'false');
                    } else {
                        next.classList.add('open');
                        btn.setAttribute('aria-expanded', 'true');
                    }
                });
            });
        });
    })();
</script>