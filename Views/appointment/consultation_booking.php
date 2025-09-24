<?php
require_once 'config/database.php';
require_once 'Models/Patient.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login");
    exit();
}

// Get patient information
$patient = new Patient();
$patient_info = $patient->getById($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lịch tư vấn trực tuyến - Hệ thống quản lý bệnh viện</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./assets/css/appointment.css">

</head>

<body>

    <?php include 'Views/layouts/header.php'; ?>
    <div class="appointment-booking-container">
        <div class="container">
            <div class="row justify-content-center">
                <!-- Main content - Form đặt lịch -->
                <div class="col-lg-10 col-xl-8">
                    <div class="appointment-form-panel">
                        <div class="panel-header">
                            <h4><i class="fas fa-headset me-2"></i>Đặt lịch tư vấn trực tuyến</h4>

                        </div>

                        <!-- Thông tin bác sĩ đã chọn -->
                        <?php if (isset($doctor) && $doctor): ?>
                        <div class="selected-doctor-info">
                            <div class="doctor-card-selected">
                                <div class="doctor-avatar">
                                    <?php
                                        $imgSrc = null;
                                        if (!empty($doctor['hinh_anh'])) {
                                            // Xây dựng đường dẫn web và kiểm tra tồn tại theo đường dẫn filesystem tuyệt đối
                                            if (strpos($doctor['hinh_anh'], 'uploads/') === 0 || strpos($doctor['hinh_anh'], './uploads/') === 0) {
                                                $relWeb = $doctor['hinh_anh'];
                                            } else {
                                                $relWeb = 'uploads/' . $doctor['hinh_anh'];
                                            }
                                            // Chuẩn hóa bớt ./ nếu đã có
                                            $relWeb = ltrim($relWeb, './');
                                            $imgSrcWeb = './' . $relWeb;
                                            $imgSrcFs = __DIR__ . '/../../' . $relWeb; // FS path
                                            if (file_exists($imgSrcFs)) {
                                                $imgSrc = $imgSrcWeb;
                                            }
                                        }
                                        if (!$imgSrc) {
                                            // Placeholder SVG nếu không có ảnh
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

                        <!-- Đặt khám nhanh (giống lịch hẹn trực tiếp) -->
                        <div class="card mb-4" id="quickBookingCard" style="display: none;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar me-2"></i>
                                        <h6 class="mb-0">Chọn ngày tư vấn</h6>
                                    </div>
                                    <div>
                                        <button type="button" id="qbPrev" class="btn btn-sm btn-outline-secondary"><i
                                                class="fas fa-chevron-left"></i></button>
                                        <button type="button" id="qbNext"
                                            class="btn btn-sm btn-outline-secondary ms-2"><i
                                                class="fas fa-chevron-right"></i></button>
                                    </div>
                                </div>
                                <div class="d-flex overflow-auto" id="quickDays" style="gap:12px;"></div>
                                <div class="mt-3">
                                    <div class="d-flex align-items-center mb-2">

                                        <strong class="me-2">Khung giờ</strong>
                                        <small class="text-muted" id="slotCountLabel"></small>
                                    </div>
                                    <div id="quickTimeSlots" class="d-flex flex-wrap" style="gap:10px;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Form đặt lịch -->
                        <form id="appointmentForm" method="POST" action="./book_appointment">
                            <input type="hidden" name="loai_lich" value="Tư vấn">
                            <input type="hidden" name="link_tu_van" value="">
                            <input type="hidden" id="doctorId" name="doctor_id"
                                value="<?php echo $doctor['id'] ?? ''; ?>">
                            <!-- Debug: Doctor ID = <?php echo $doctor['id'] ?? 'null'; ?> -->


                            <div class="row">
                                <!-- Thông tin lịch hẹn -->
                                <div class="col-md-12">
                                    <div class="form-section">
                                        <h6 class="section-title">
                                            <i class="fas fa-calendar me-2"></i>Thông tin lịch hẹn
                                        </h6>

                                        <input type="hidden" id="appointmentDate" name="date" value="">
                                        <div class="mb-3 d-none">
                                            <label class="form-label">Ngày đã chọn</label>
                                            <input type="text" id="selectedDateDisplay" class="form-control" value=""
                                                disabled>
                                        </div>

                                        <input type="hidden" id="appointmentTime" name="time" value="">
                                        <div class="mb-3 d-none">
                                            <label class="form-label">Giờ đã chọn</label>
                                            <input type="text" id="selectedTimeDisplay" class="form-control" value=""
                                                disabled>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-text">Khung giờ 20 phút/lần</div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Lý do tư vấn</label>
                                            <textarea id="appointmentReason" name="reason" class="form-control" rows="3"
                                                placeholder="Mô tả triệu chứng hoặc câu hỏi bạn muốn tư vấn..."></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Ghi chú thêm</label>
                                            <textarea id="appointmentNotes" name="notes" class="form-control" rows="2"
                                                placeholder="Thông tin bổ sung (nếu có)..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cột trống để cân layout (tùy ý) -->
                                <div class="col-md-6"></div>
                            </div>

                            <!-- Nút submit -->
                            <div class="form-actions">
                                <button type="submit" id="submitBtn" class="btn btn-primary btn-lg" disabled>
                                    <i class="fas fa-headset me-2"></i>Đặt lịch tư vấn
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