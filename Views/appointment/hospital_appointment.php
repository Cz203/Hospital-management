<?php
// Lấy thông tin bệnh nhân hiện tại
require_once 'Models/Patient.php';
$patientModel = new Patient();
$patient = $patientModel->getById($_SESSION['user_id']);

// Set page title for header
$page_title = 'Đặt lịch khám tại bệnh viện';

// Include header
include 'Views/layouts/header.php';
?>

<!-- Page Header (Novena Style) -->
<section class="section page-header-section"
    style="background: linear-gradient(135deg, #223a66 0%, #1e5f8e 100%); position: relative; overflow: hidden; padding-top: 120px; padding-bottom: 80px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <div class="section-title">
                    <h2 class="text-white mb-3">
                        <i class="icofont-calendar mr-2" style="color: #e12454;"></i>
                        Đặt lịch khám tại bệnh viện
                    </h2>
                    <div class="divider mx-auto my-4" style="background: rgba(255,255,255,0.3);"></div>
                    <p class="text-white-50">Chọn bác sĩ và thời gian phù hợp để đặt lịch khám</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Appointment Booking Section -->
<section class="section appointment-booking-section"
    style="padding-top: 80px; padding-bottom: 80px; background: #f8f9fa;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">

                <!-- Unified Appointment Card -->
                <?php if (isset($doctor) && $doctor): ?>
                <div class="unified-appointment-card">
                    <div class="card-header-custom">
                        <h4 class="mb-0">
                            <i class="icofont-calendar mr-2"></i>
                            Đặt lịch khám tại bệnh viện
                        </h4>
                    </div>
                    <div class="card-body-custom">

                        <!-- Thông tin bác sĩ đã chọn -->
                        <div class="section-divider">
                            <h6 class="section-title-small">
                                <i class="icofont-doctor mr-2"></i>
                                Bác sĩ đã chọn
                            </h6>
                        </div>
                        <div class="doctor-info-card mb-4">
                            <div class="doctor-avatar-wrapper">
                                <?php
                                    $imgSrc = null;
                                    if (!empty($doctor['hinh_anh'])) {
                                        $hinhAnh = trim($doctor['hinh_anh']);
                                        $hinhAnh = ltrim($hinhAnh, './');
                                        if (strpos($hinhAnh, 'uploads/') !== 0) {
                                            $hinhAnh = 'uploads/' . $hinhAnh;
                                        }
                                        $imgSrcWeb = './' . $hinhAnh;
                                        $imgSrcFs = __DIR__ . '/../../' . $hinhAnh;
                                        if (file_exists($imgSrcFs)) {
                                            $imgSrc = $imgSrcWeb;
                                        }
                                    }
                                    ?>
                                <?php if ($imgSrc): ?>
                                <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                                    alt="Bác sĩ <?php echo htmlspecialchars($doctor['ten']); ?>"
                                    class="doctor-avatar-img"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <?php endif; ?>
                                <div class="doctor-avatar-placeholder"
                                    style="display: <?php echo $imgSrc ? 'none' : 'flex'; ?>;">
                                    <i class="icofont-doctor"></i>
                                </div>
                            </div>
                            <div class="doctor-info-content">
                                <h4 class="doctor-name"><?php echo htmlspecialchars($doctor['ten']); ?></h4>
                                <p class="doctor-specialty">
                                    <i class="icofont-stethoscope mr-2"></i>
                                    <?php echo htmlspecialchars($doctor['chuyen_khoa'] ?? 'Đa khoa'); ?>
                                </p>
                                <p class="doctor-experience">
                                    <i class="icofont-clock-time mr-2"></i>
                                    <?php echo ($doctor['so_nam_kinh_nghiem'] ?? 0); ?> năm kinh nghiệm
                                </p>
                            </div>
                        </div>

                        <!-- Quick Booking Section -->
                        <div class="section-divider mt-4" id="quickBookingSection" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="section-title-small mb-0">
                                    <i class="icofont-calendar mr-2"></i>
                                    Chọn ngày và giờ khám
                                </h6>
                                <div class="date-nav-buttons">
                                    <button type="button" id="qbPrev"
                                        class="btn btn-sm btn-outline-secondary btn-round">
                                        <i class="icofont-simple-left"></i>
                                    </button>
                                    <button type="button" id="qbNext"
                                        class="btn btn-sm btn-outline-secondary btn-round ml-2">
                                        <i class="icofont-simple-right"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="quick-days-container">
                                <div class="quick-days-grid" id="quickDays"></div>
                            </div>
                            <div class="time-slots-container mt-4">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="icofont-clock-time mr-2" style="color: #e12454; font-size: 1.2rem;"></i>
                                    <strong class="mr-2">Khung giờ có sẵn</strong>
                                    <small class="text-muted" id="slotCountLabel">(0 khung giờ)</small>
                                </div>
                                <div id="quickTimeSlots" class="time-slots-container-custom"></div>
                            </div>
                        </div>



                        <form id="appointmentForm" method="POST" action="./book_appointment">
                            <input type="hidden" name="loai_lich" value="Trực tiếp">
                            <input type="hidden" id="doctorId" name="doctor_id"
                                value="<?php echo $doctor['id'] ?? ''; ?>">
                            <input type="hidden" id="appointmentDate" name="date" value="">
                            <input type="hidden" id="appointmentTime" name="time" value="">



                            <!-- Appointment Reason -->
                            <div class="form-group-custom mb-4">
                                <label class="form-label-custom">
                                    <i class="icofont-info-circle mr-2"></i>
                                    Lý do khám <span class="text-danger">*</span>
                                </label>
                                <textarea id="appointmentReason" name="reason" class="form-control-custom" rows="4"
                                    placeholder="Mô tả triệu chứng hoặc lý do khám..."></textarea>
                                <small class="form-text-custom">Vui lòng mô tả chi tiết để bác sĩ có thể chuẩn bị tốt
                                    nhất.</small>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-actions-custom mt-4">
                                <button type="submit" id="submitBtn" class="btn btn-main-2 btn-round-full btn-lg"
                                    disabled>
                                    <i class="icofont-calendar mr-2"></i>
                                    Đặt lịch hẹn
                                </button>
                                <a href="./doctor_team" class="btn btn-main btn-round-full btn-lg ml-3">
                                    <i class="icofont-simple-left mr-2"></i>
                                    Quay lại
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="unified-appointment-card">
                    <div class="card-body-custom">
                        <div class="alert alert-warning text-center mb-0">
                            <i class="icofont-warning-alt mr-2"></i>
                            Vui lòng chọn bác sĩ trước khi đặt lịch.
                            <a href="./doctor_team" class="btn btn-main btn-sm ml-2">
                                <i class="icofont-search-1 mr-1"></i>Chọn bác sĩ
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

<style>
/* Page Header */
.page-header-section {
    position: relative;
}

.page-header-section::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="60" height="60" xmlns="http://www.w3.org/2000/svg"><circle cx="30" cy="30" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
    opacity: 0.3;
}

/* Unified Appointment Card */
.unified-appointment-card {
    background: #ffffff;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    border: 1px solid #e9ecef;
    margin-bottom: 2rem;
}

.card-header-custom {
    background: linear-gradient(135deg, #223a66 0%, #1e5f8e 100%);
    color: #ffffff;
    padding: 1.5rem 1.75rem;
    border-bottom: none;
}

.card-header-custom h4 {
    color: #ffffff;
    font-weight: 700;
    font-size: 1.5rem;
    margin: 0;
}

.card-body-custom {
    padding: 2rem;
}

/* Section Divider */
.section-divider {
    padding-top: 1.5rem;
    border-top: 2px solid #f8f9fa;
    margin-top: 1.5rem;
}

.section-divider:first-child {
    border-top: none;
    padding-top: 0;
    margin-top: 0;
}

.section-title-small {
    color: #223a66;
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
}

/* Doctor Info Card */
.doctor-info-card {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.doctor-avatar-wrapper {
    position: relative;
    flex-shrink: 0;
}

.doctor-avatar-img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #f8f9fa;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.doctor-avatar-placeholder {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #223a66 0%, #1e5f8e 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    border: 4px solid #f8f9fa;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.doctor-info-content {
    flex: 1;
}

.doctor-name {
    color: #223a66;
    font-weight: 700;
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.doctor-specialty {
    color: #e12454;
    font-size: 1rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.doctor-experience {
    color: #6c757d;
    font-size: 0.95rem;
    margin-bottom: 0;
}

/* Quick Days Grid */
.quick-days-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    gap: 12px;
    min-height: 100px;
}

.quick-day-btn {
    min-width: 110px;
    padding: 14px 12px;
    border: 2px solid #e9ecef;
    background: #ffffff;
    border-radius: 12px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.quick-day-btn:hover {
    border-color: #223a66;
    background: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(34, 58, 102, 0.15);
}

.quick-day-btn.selected {
    background: #223a66;
    border-color: #223a66;
    color: #ffffff;
    box-shadow: 0 4px 15px rgba(34, 58, 102, 0.3);
}

.quick-day-btn.today {
    border-color: #e12454;
}

.quick-day-btn .day-name {
    display: block;
    font-size: 0.75rem;
    font-weight: 500;
    margin-bottom: 4px;
    opacity: 0.7;
}

.quick-day-btn .day-number {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.2;
}

.quick-day-btn .day-month {
    display: block;
    font-size: 0.7rem;
    margin-top: 4px;
    opacity: 0.6;
}

.date-nav-buttons .btn-round {
    border-radius: 50%;
    width: 35px;
    height: 35px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Time Slots */
.time-slots-container-custom {
    width: 100%;
}

.time-slots-columns {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 12px;
    margin-bottom: 1rem;
}

.time-slot-btn {
    padding: 14px 16px;
    border: 2px solid #e9ecef;
    background: #ffffff;
    border-radius: 12px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    color: #223a66;
    font-size: 0.95rem;
    white-space: nowrap;
}

.time-slot-btn:hover {
    border-color: #223a66;
    background: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(34, 58, 102, 0.15);
}

.time-slot-btn.selected {
    background: #223a66;
    border-color: #223a66;
    color: #ffffff;
    box-shadow: 0 4px 15px rgba(34, 58, 102, 0.3);
}

.time-slot-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #f8f9fa;
}

/* Form Styles */
.form-group-custom {
    margin-bottom: 1.5rem;
}

.form-label-custom {
    display: block;
    color: #223a66;
    font-weight: 600;
    margin-bottom: 0.75rem;
    font-size: 1rem;
}

.form-control-custom {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #ffffff;
    color: #2c3e50;
}

.form-control-custom:focus {
    outline: none;
    border-color: #223a66;
    box-shadow: 0 0 0 3px rgba(34, 58, 102, 0.1);
}

.form-text-custom {
    display: block;
    margin-top: 0.5rem;
    color: #6c757d;
    font-size: 0.875rem;
}

.selected-info-display {
    background: #f8f9fa;
    padding: 1.25rem;
    border-radius: 12px;
    border-left: 4px solid #223a66;
}

.info-item {
    display: flex;
    align-items: center;
    color: #2c3e50;
}

.info-item i {
    color: #e12454;
    font-size: 1.1rem;
}

.form-actions-custom {
    padding-top: 1.5rem;
    border-top: 2px solid #f8f9fa;
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Responsive */
@media (max-width: 768px) {
    .doctor-info-card {
        flex-direction: column;
        text-align: center;
    }

    .quick-days-grid {
        grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
    }

    .time-slots-columns {
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    }

    .form-actions-custom {
        flex-direction: column;
    }

    .form-actions-custom .btn {
        width: 100%;
    }

    .card-body-custom {
        padding: 1.5rem;
    }
}
</style>

<?php
// Include footer
include 'Views/layouts/footer.php';
?>

<!-- Include JavaScript -->
<script src="./assets/js/appointment.js"></script>