<?php
$current_uri = $_SERVER['REQUEST_URI'] ?? '';
$scheduleOpen = (strpos($current_uri, 'doctor_today_appointments') !== false
    || strpos($current_uri, 'doctor_appointment_management') !== false
    || strpos($current_uri, 'doctor_schedule_management') !== false)
    ? 'open'
    : '';
?>

<div class="sidebar">
    <div class="sidebar-header px-3 py-3">
        <div class="d-flex align-items-center">
            <div class="brand-icon d-flex align-items-center justify-content-center rounded-circle me-3 bg-accent-soft">
                <i class="fas fa-user-md"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-0 fw-semibold clinic-title">Bác sĩ - Phòng khám đa khoa</h6>
                <small class="text-muted d-block">
                    <?php echo $_SESSION['specialization'] ?? 'Chuyên khoa'; ?>
                </small>
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
                <a class="nav-link d-flex align-items-center <?php echo isActiveUrl('doctor_dashboard'); ?>"
                    href="./doctor_dashboard">
                    <i class="fas fa-tachometer-alt me-3"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center <?php echo isActiveUrl('doctor_attendance'); ?>"
                    href="./doctor_attendance">
                    <i class="fas fa-clock me-3"></i>
                    <span>Chấm công</span>
                </a>
            </li>

            <!-- Lịch & bệnh nhân -->
            <li class="nav-item mt-3 mb-1 px-3">
                <div class="sidebar-section-label">Lịch & bệnh nhân</div>
            </li>
            <li class="nav-item">
                <button
                    class="nav-link d-flex align-items-center justify-content-between btn-toggle <?php echo $scheduleOpen; ?>"
                    aria-expanded="<?php echo ($scheduleOpen ? 'true' : 'false'); ?>">
                    <span class="d-flex align-items-center">
                        <i class="fas fa-calendar-alt me-3"></i>
                        <span>Lịch hẹn & ca trực</span>
                    </span>
                    <i class="fas fa-chevron-down small"></i>
                </button>
                <ul class="submenu <?php echo $scheduleOpen; ?>">
                    <li>
                        <a class="nav-link d-flex align-items-center <?php echo isActiveUrl('doctor_today_appointments'); ?>"
                            href="./doctor_today_appointments">
                            <i class="fas fa-calendar-check me-3"></i>
                            <span>Lịch hẹn hôm nay</span>
                        </a>
                    </li>
                    <li>
                        <a class="nav-link d-flex align-items-center <?php echo (strpos($_SERVER['REQUEST_URI'], 'doctor_appointment_management') !== false) ? 'active' : ''; ?>"
                            href="./doctor_appointment_management">
                            <i class="fas fa-calendar-check me-3"></i>
                            <span>Quản lý lịch hẹn</span>
                        </a>
                    </li>
                    <li>
                        <a class="nav-link d-flex align-items-center <?php echo isActiveUrl('doctor_schedule_management'); ?>"
                            href="./doctor_schedule_management">
                            <i class="fas fa-user-clock me-3"></i>
                            <span>Đăng ký ca trực</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Khám & hồ sơ -->
            <li class="nav-item mt-3 mb-1 px-3">
                <div class="sidebar-section-label">Khám & hồ sơ</div>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="#">
                    <i class="fas fa-user-injured me-3"></i>
                    <span>Danh sách bệnh nhân</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center <?php echo isActiveUrl('doctor_examination'); ?>"
                    href="./doctor_examination">
                    <i class="fas fa-stethoscope me-3"></i>
                    <span>Khám bệnh</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center <?php echo isActiveUrl('doctor_medical_records'); ?>"
                    href="./doctor_medical_records">
                    <i class="fas fa-file-medical me-3"></i>
                    <span>Hồ sơ bệnh án</span>
                </a>
            </li>

            <!-- Điều trị & theo dõi -->
            <li class="nav-item mt-3 mb-1 px-3">
                <div class="sidebar-section-label">Điều trị & theo dõi</div>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="#">
                    <i class="fas fa-prescription me-3"></i>
                    <span>Kê đơn thuốc</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="#">
                    <i class="fas fa-procedures me-3"></i>
                    <span>Chỉ định xét nghiệm</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="#">
                    <i class="fas fa-chart-line me-3"></i>
                    <span>Theo dõi bệnh nhân</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
    /* Sử dụng tone giống admin (phòng khám đa khoa) */
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
    // Toggle dropdown cho phần Lịch hẹn & ca trực
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            var toggles = document.querySelectorAll('.sidebar .btn-toggle');
            toggles.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var next = btn.nextElementSibling;
                    if (!next) return;
                    var isOpen = next.classList.contains('open');
                    // Đóng các submenu khác trong sidebar doctor
                    var all = document.querySelectorAll('.sidebar .submenu');
                    all.forEach(function(s) {
                        if (s !== next) s.classList.remove('open');
                    });
                    if (isOpen) next.classList.remove('open');
                    else next.classList.add('open');
                });
            });
        });
    })();
</script>