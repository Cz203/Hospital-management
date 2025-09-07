<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Hệ thống quản lý bệnh viện</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./assets/css/appointment.css">

</head>

<body>

    <?php
    // Lấy thông tin bệnh nhân hiện tại
    require_once 'Models/Patient.php';
    $patientModel = new Patient();
    $patient = $patientModel->getById($_SESSION['user_id']);
    ?>

    <?php include 'Views/layouts/header.php'; ?>
    <div class="appointment-booking-container">
        <div class="container">
            <div class="row justify-content-center">
                <!-- Main content - Form đặt lịch -->
                <div class="col-lg-10 col-xl-8">
                    <div class="appointment-form-panel">
                        <div class="panel-header">
                            <h4><i class="fas fa-calendar-plus me-2"></i>Đặt lịch khám tại bệnh viện</h4>
                            <p class="text-muted">Điền thông tin để đặt lịch hẹn</p>
                        </div>

                        <!-- Thông tin bác sĩ đã chọn -->
                        <?php if (isset($doctor) && $doctor): ?>
                            <div class="selected-doctor-info">
                                <div class="doctor-card-selected">
                                    <div class="doctor-avatar">
                                        <?php
                                        $imgSrc = null;

                                        // Debug: Kiểm tra dữ liệu doctor
                                        echo "<!-- Doctor data: " . print_r($doctor, true) . " -->";

                                        if (!empty($doctor['hinh_anh'])) {
                                            // Nếu hinh_anh đã có đường dẫn uploads/ thì sử dụng trực tiếp
                                            if (strpos($doctor['hinh_anh'], 'uploads/') === 0) {
                                                $imgSrc = './' . $doctor['hinh_anh'];
                                            } else {
                                                // Nếu chỉ có tên file thì thêm đường dẫn uploads/
                                                $imgSrc = './uploads/' . $doctor['hinh_anh'];
                                            }

                                            // Kiểm tra file có tồn tại không
                                            if (!file_exists($imgSrc)) {
                                                $imgSrc = null; // Reset nếu file không tồn tại
                                            }
                                        }

                                        // Nếu không có ảnh hoặc file không tồn tại, sử dụng placeholder
                                        if (!$imgSrc) {
                                            $imgSrc = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4MCIgaGVpZ2h0PSI4MCIgdmlld0JveD0iMCAwIDgwIDgwIj48cmVjdCB3aWR0aD0iODAiIGhlaWdodD0iODAiIGZpbGw9IiNmOGY5ZmEiLz48Y2lyY2xlIGN4PSI0MCIgY3k9IjMwIiByPSIxNSIgZmlsbD0iI2RlZTJlNiIvPjxwYXRoIGQ9Ik0xNSA2NSBRNDAgNDUgNjUgNjUiIHN0cm9rZT0iI2RlZTJlNiIgc3Ryb2tlLXdpZHRoPSIzIiBmaWxsPSJub25lIi8+PC9zdmc+';
                                        }
                                        ?>
                                        <img src="<?php echo $imgSrc; ?>"
                                            alt="Bác sĩ <?php echo htmlspecialchars($doctor['ten']); ?>"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <div class="avatar-placeholder"
                                            style="display: none; width: 100%; height: 100%; background: #f8f9fa; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #6c757d; font-size: 2rem;">
                                            <i class="fas fa-user-md"></i>
                                        </div>
                                    </div>
                                    <div class="doctor-details">
                                        <h5><?php echo htmlspecialchars($doctor['ten']); ?></h5>
                                        <p class="text-primary">
                                            <?php echo htmlspecialchars($doctor['chuyen_khoa'] ?? 'Đa khoa'); ?></p>
                                        <p class="text-muted"><?php echo ($doctor['so_nam_kinh_nghiem'] ?? 0); ?> năm kinh
                                            nghiệm</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Form đặt lịch -->
                        <form id="appointmentForm" method="POST" action="./book_appointment">
                            <input type="hidden" id="doctorId" name="doctor_id"
                                value="<?php echo $doctor['id'] ?? ''; ?>">


                            <div class="row">
                                <!-- Thông tin lịch hẹn -->
                                <div class="col-md-6">
                                    <div class="form-section">
                                        <h6 class="section-title">
                                            <i class="fas fa-calendar me-2"></i>Thông tin lịch hẹn
                                        </h6>

                                        <div class="mb-3">
                                            <label class="form-label">Ngày khám <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" id="appointmentDate" name="date" class="form-control"
                                                min="<?php echo date('Y-m-d'); ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Giờ khám <span
                                                    class="text-danger">*</span></label>
                                            <select id="appointmentTime" name="time" class="form-select" required
                                                disabled>
                                                <option value="">Chọn ngày trước</option>
                                            </select>
                                            <div class="form-text">Khung giờ 20 phút/lần</div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Lý do khám</label>
                                            <textarea id="appointmentReason" name="reason" class="form-control" rows="3"
                                                placeholder="Mô tả triệu chứng hoặc lý do khám..."></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Ghi chú thêm</label>
                                            <textarea id="appointmentNotes" name="notes" class="form-control" rows="2"
                                                placeholder="Thông tin bổ sung (nếu có)..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lịch làm việc của bác sĩ -->
                                <div class="col-md-6">
                                    <div class="form-section">
                                        <h6 class="section-title">
                                            <i class="fas fa-clock me-2"></i>Lịch làm việc của bác sĩ
                                        </h6>
                                        <div id="scheduleDisplay" class="schedule-display">
                                            <?php if (isset($schedules) && !empty($schedules)): ?>
                                                <?php
                                                // Group schedules by day
                                                $schedulesByDay = [];
                                                foreach ($schedules as $schedule) {
                                                    if (!isset($schedulesByDay[$schedule['thu_trong_tuan']])) {
                                                        $schedulesByDay[$schedule['thu_trong_tuan']] = [];
                                                    }
                                                    $schedulesByDay[$schedule['thu_trong_tuan']][] = $schedule;
                                                }

                                                $daysOrder = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
                                                foreach ($daysOrder as $day) {
                                                    if (isset($schedulesByDay[$day]) && !empty($schedulesByDay[$day])) {
                                                        echo '<div class="schedule-day">';
                                                        echo '<h6>' . $day . '</h6>';
                                                        echo '<div class="schedule-shifts">';
                                                        foreach ($schedulesByDay[$day] as $schedule) {
                                                            echo $schedule['loai_ca'] . ': ' . $schedule['gio_bat_dau'] . ' - ' . $schedule['gio_ket_thuc'] . '<br>';
                                                        }
                                                        echo '</div></div>';
                                                    }
                                                }
                                                ?>
                                            <?php else: ?>
                                                <p class="text-muted">Bác sĩ chưa có lịch làm việc</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Nút submit -->
                            <div class="form-actions">
                                <button type="submit" id="submitBtn" class="btn btn-primary btn-lg" disabled>
                                    <i class="fas fa-calendar-check me-2"></i>Đặt lịch hẹn
                                </button>
                                <a href="./doctor_team" class="btn btn-outline-secondary btn-lg ms-2">
                                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading overlay -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang xử lý...</span>
            </div>
            <p class="mt-3">Đang xử lý yêu cầu...</p>
        </div>
    </div>
    <?php include 'Views/layouts/footer.php'; ?>
    <!-- Include JavaScript -->
    <script src="./assets/js/appointment.js"></script>



    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>