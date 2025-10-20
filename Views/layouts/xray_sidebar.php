<!-- X-Ray Doctor Sidebar -->
<div class="sidebar bg-success text-white"
    style="width: 250px; height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000; overflow-y: auto;">
    <div class="p-4">
        <!-- Logo -->
        <div class="text-center mb-4">
            <i class="fas fa-x-ray fa-3x mb-2"></i>
            <h5 class="mb-0">X-Ray System</h5>
            <small class="text-light">Chẩn đoán hình ảnh</small>
        </div>

        <!-- Navigation Menu -->
        <nav class="nav flex-column">
            <!-- Dashboard -->
            <a class="nav-link text-white py-3 <?php echo isActiveUrl('xray_dashboard') ? 'active bg-light text-success' : ''; ?>"
                href="./xray_dashboard">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard
            </a>

            <!-- Schedule Management -->

            <!-- Duty Registration -->
            <a class="nav-link text-white py-3 <?php echo isActiveUrl('doctor_schedule_management') ? 'active bg-light text-success' : ''; ?>"
                href="./doctor_schedule_management">
                <i class="fas fa-user-clock me-2"></i>
                Đăng ký ca trực
            </a>

            <!-- History -->
            <a class="nav-link text-white py-3 <?php echo isActiveUrl('xray_history') ? 'active bg-light text-success' : ''; ?>"
                href="./xray_history">
                <i class="fas fa-history me-2"></i>
                Lịch sử chụp X-Quang
            </a>

            <!-- Settings -->
            <a class="nav-link text-white py-3 <?php echo isActiveUrl('xray_settings') ? 'active bg-light text-success' : ''; ?>"
                href="./xray_settings">
                <i class="fas fa-cog me-2"></i>
                Cài đặt
            </a>
        </nav>

        <!-- User Info -->
        <div class="mt-5 pt-4 border-top border-light">
            <div class="d-flex align-items-center">
                <div class="bg-light text-success rounded-circle d-flex align-items-center justify-content-center me-3"
                    style="width: 40px; height: 40px;">
                    <i class="fas fa-user-md"></i>
                </div>
                <div>
                    <?php $ctx = getCurrentUserContext();
                    $__name = htmlspecialchars($ctx['name'] ?: 'Bác sĩ', ENT_QUOTES, 'UTF-8'); ?>
                    <div class="fw-bold"><?php echo $__name; ?></div>
                    <small class="text-light">Bác sĩ X-Quang</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .sidebar .nav-link {
        border-radius: 8px;
        margin-bottom: 2px;
        transition: all 0.3s ease;
    }

    .sidebar .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
        transform: translateX(5px);
    }

    .sidebar .nav-link.active {
        background-color: rgba(255, 255, 255, 0.9);
        color: #198754 !important;
        font-weight: 600;
    }

    /* Mobile responsive */
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