<div class="sidebar bg-dark text-white"
    style="width: 250px; min-height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000;">
    <div class="sidebar-header p-3 border-bottom border-secondary">
        <div class="d-flex align-items-center">
            <i class="fas fa-hospital-user fa-2x text-info me-3"></i>
            <div>
                <h6 class="mb-0 fw-bold">Hospital Management</h6>
                <small class="text-muted">Lễ tân</small>
            </div>
        </div>
    </div>

    <div class="sidebar-body p-0">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo isActiveUrl('reception_dashboard'); ?>"
                    href="./reception_dashboard">
                    <i class="fas fa-house-user me-3"></i>
                    <span>Trang bệnh nhân</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo isActiveUrl('hospital_appointment'); ?>"
                    href="./hospital_appointment">
                    <i class="fas fa-calendar-plus me-3"></i>
                    <span>Tạo lịch hẹn</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo isActiveUrl('reception_doctor_schedules'); ?>"
                    href="./reception_doctor_schedules">
                    <i class="fas fa-user-md me-3"></i>
                    <span>Lịch làm việc bác sĩ</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo isActiveUrl('reception_patient_create'); ?>"
                    href="./reception_patient_create">
                    <i class="fas fa-user-plus me-3"></i>
                    <span>Thêm bệnh nhân</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo isActiveUrl('patient_appointments'); ?>"
                    href="./patient_appointments">
                    <i class="fas fa-calendar-check me-3"></i>
                    <span>Lịch hẹn bệnh nhân</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo isActiveUrl('reception_queue'); ?>"
                    href="./reception_queue">
                    <i class="fas fa-sort-numeric-down me-3"></i>
                    <span>Bốc số</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white py-3 px-3 d-flex align-items-center <?php echo isActiveUrl('doctors_by_specialty'); ?>"
                    href="./specialties_all">
                    <i class="fas fa-stethoscope me-3"></i>
                    <span>Bác sĩ theo chuyên khoa</span>
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
    border-left-color: #0dcaf0;
}

.sidebar .nav-link.active {
    background-color: rgba(255, 255, 255, 0.2);
    border-left-color: #0dcaf0;
    font-weight: 600;
    box-shadow: inset 0 0 10px rgba(255, 255, 255, 0.1);
}

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