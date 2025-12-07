<?php
// Set page title
$page_title = 'Tra cứu hồ sơ bệnh án';

// Include header
include 'Views/layouts/header.php';

// Các biến này được truyền từ Controller (từ session)
// Không lấy từ $_GET để bảo mật thông tin
$patient = $patient ?? null;
$records = $records ?? [];
$hoTen = isset($hoTen) ? trim($hoTen) : '';
$soDienThoai = isset($soDienThoai) ? trim($soDienThoai) : '';
$cccd = isset($cccd) ? trim($cccd) : '';
$selectedDate = isset($selectedDate) ? trim($selectedDate) : '';
?>

<style>
    .lookup-section {
        padding: 80px 0;
        background: #FFFFFF;
        min-height: 60vh;
    }

    .lookup-card {
        background: #fff;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        max-width: 800px;
        margin: 0 auto;
    }

    .lookup-title {
        text-align: center;
        margin-bottom: 30px;
        color: #333;
    }

    .lookup-title h2 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .lookup-title p {
        color: #666;
        font-size: 1rem;
    }

    .search-form {
        margin-bottom: 30px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        display: block;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .btn-search {
        width: 100%;
        padding: 12px;
        background: #223A66;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-search:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }

    .records-section {
        margin-top: 40px;
    }

    .records-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e0e0e0;
    }

    .patient-info {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
    }

    .patient-info h4 {
        color: #667eea;
        margin-bottom: 15px;
    }

    .patient-info p {
        margin-bottom: 8px;
        color: #555;
    }

    .records-list {
        list-style: none;
        padding: 0;
    }

    .record-item {
        background: #fff;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .record-item:hover {
        border-color: #667eea;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.1);
    }

    .record-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .record-date {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
    }

    .record-status {
        background: #28a745;
        color: #fff;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .record-details {
        color: #666;
    }

    .record-details p {
        margin-bottom: 8px;
    }

    .record-details strong {
        color: #333;
    }

    .btn-view {
        display: inline-block;
        margin-top: 15px;
        padding: 10px 20px;
        background: #667eea;
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-view:hover {
        background: #764ba2;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .no-records {
        text-align: center;
        padding: 40px;
        color: #999;
    }

    .no-records i {
        font-size: 4rem;
        margin-bottom: 20px;
        color: #ddd;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert-info {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
</style>

<section class="lookup-section">
    <div class="container">
        <div class="lookup-card">
            <div class="lookup-title">
                <h2><i class="icofont-search-1"></i> Tra cứu hồ sơ bệnh án</h2>
            </div>

            <?php
            // Hiển thị thông báo thành công nếu có
            if (isset($_SESSION['lookup_success'])) {
                echo '<div class="alert alert-success" style="margin-bottom: 20px; padding: 15px 20px; border-radius: 10px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb;">';
                echo '<i class="icofont-check-circled"></i> ' . htmlspecialchars($_SESSION['lookup_success']);
                echo '</div>';
                unset($_SESSION['lookup_success']); // Xóa thông báo sau khi hiển thị
            }
            ?>

            <?php if (!empty($hoTen) && !empty($soDienThoai) && !empty($cccd)): ?>
                <div style="text-align: center; margin-bottom: 20px;">
                    <a href="./lookup_medical_record?clear=1" class="btn-search"
                        style="width: auto; padding: 8px 20px; font-size: 14px; text-decoration: none; display: inline-block; background: #dc3545;">
                        <i class="icofont-refresh"></i> Tra cứu mới
                    </a>
                </div>
            <?php endif; ?>

            <form method="POST" action="./lookup_medical_record" class="search-form">
                <div class="form-group">
                    <label for="ho_ten">Họ và tên <span style="color: red;">*</span>:</label>
                    <input type="text" name="ho_ten" id="ho_ten" class="form-control" placeholder="Ví dụ: Nguyễn Văn A"
                        value="<?php echo htmlspecialchars($hoTen ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="so_dien_thoai">Số điện thoại <span style="color: red;">*</span>:</label>
                    <input type="tel" name="so_dien_thoai" id="so_dien_thoai" class="form-control"
                        placeholder="Ví dụ: 0912345678 hoặc 84912345678"
                        value="<?php echo htmlspecialchars($soDienThoai ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="cccd">Số căn cước công dân (CCCD) <span style="color: red;">*</span>:</label>
                    <input type="text" name="cccd" id="cccd" class="form-control" placeholder="001234567890"
                        value="<?php echo htmlspecialchars($cccd ?? ''); ?>" required>
                </div>

                <button type="submit" class="btn-search">
                    <i class="icofont-search-1"></i> Tra cứu
                </button>
            </form>

            <?php
            // Đảm bảo các biến đã được set từ controller
            $hoTen = isset($hoTen) ? trim($hoTen) : '';
            $soDienThoai = isset($soDienThoai) ? trim($soDienThoai) : '';
            $cccd = isset($cccd) ? trim($cccd) : '';

            // Kiểm tra nếu có thông tin tra cứu (từ session)
            $hasLookupInfo = !empty($hoTen) && !empty($soDienThoai) && !empty($cccd);

            if ($hasLookupInfo):
            ?>
                <div class="records-section">
                    <?php if ($patient): ?>
                        <div class="patient-info">
                            <h4><i class="icofont-user-alt-4"></i> Thông tin bệnh nhân</h4>
                            <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($patient['ten'] ?? '-'); ?></p>
                            <p><strong>Mã bệnh nhân:</strong> <?php echo htmlspecialchars($patient['ma_benh_nhan'] ?? '-'); ?>
                            </p>
                            <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($patient['so_dien_thoai'] ?? '-'); ?>
                            </p>
                            <?php if (!empty($patient['ngay_sinh'])): ?>
                                <p><strong>Ngày sinh:</strong> <?php echo date('d/m/Y', strtotime($patient['ngay_sinh'])); ?></p>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($records)): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h3 class="records-title" style="margin-bottom: 0;">Danh sách hồ sơ khám bệnh</h3>
                                <form method="GET" action="./lookup_medical_record"
                                    style="display: flex; gap: 10px; align-items: center;">
                                    <label for="filter_date" style="margin: 0; font-weight: 600; color: #333;">Lọc theo
                                        ngày:</label>
                                    <input type="date" name="selected_date" id="filter_date" class="form-control"
                                        style="width: auto; padding: 8px 12px; font-size: 14px;"
                                        value="<?php echo htmlspecialchars($selectedDate ?? ''); ?>" onchange="this.form.submit()">
                                    <?php if (!empty($selectedDate)): ?>
                                        <a href="./lookup_medical_record" class="btn-search"
                                            style="width: auto; padding: 8px 15px; font-size: 14px; text-decoration: none; display: inline-block;">
                                            <i class="icofont-close"></i> Xóa lọc
                                        </a>
                                    <?php endif; ?>
                                </form>
                            </div>
                            <ul class="records-list">
                                <?php foreach ($records as $record): ?>
                                    <?php
                                    // Xác định ngày khám
                                    $ngayKham = '-';
                                    if (!empty($record['exam_id']) && !empty($record['ngay_kham']) && !empty($record['thang_kham']) && !empty($record['nam_kham'])) {
                                        $ngayKham = sprintf('%02d/%02d/%04d', $record['ngay_kham'], $record['thang_kham'], $record['nam_kham']);
                                    } else if (!empty($record['ngay_hen'])) {
                                        $ngayKham = date('d/m/Y', strtotime($record['ngay_hen']));
                                    }

                                    // Xác định giờ khám
                                    $gioKham = '-';
                                    if (!empty($record['exam_id']) && isset($record['gio_kham']) && isset($record['phut_kham'])) {
                                        $gioKham = sprintf('%02d:%02d', $record['gio_kham'], $record['phut_kham']);
                                    } else if (!empty($record['gio_hen'])) {
                                        $time = strtotime($record['gio_hen']);
                                        $gioKham = date('H:i', $time);
                                    }
                                    ?>
                                    <li class="record-item">
                                        <div class="record-header">
                                            <div class="record-date">
                                                <i class="icofont-calendar"></i> <?php echo htmlspecialchars($ngayKham); ?>
                                                <?php if ($gioKham !== '-'): ?>
                                                    <span style="margin-left: 10px; color: #666; font-size: 0.9rem;">
                                                        <i class="icofont-clock-time"></i> <?php echo htmlspecialchars($gioKham); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="record-status">Hoàn thành</span>
                                        </div>
                                        <div class="record-details">
                                            <?php if (!empty($record['ten_bac_si_full']) || !empty($record['ten_bac_si'])): ?>
                                                <p><strong>Bác sĩ khám:</strong>
                                                    <?php echo htmlspecialchars($record['ten_bac_si_full'] ?? $record['ten_bac_si'] ?? '-'); ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if (!empty($record['chan_doan_vao_vien'])): ?>
                                                <p><strong>Chẩn đoán:</strong>
                                                    <?php echo htmlspecialchars($record['chan_doan_vao_vien']); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($record['ly_do'])): ?>
                                                <p><strong>Lý do khám:</strong> <?php echo htmlspecialchars($record['ly_do']); ?></p>
                                            <?php endif; ?>
                                            <a href="./lookup_medical_record_detail?exam_id=<?php echo $record['lich_hen_id']; ?>&patient_id=<?php echo $patient['id']; ?>"
                                                class="btn-view" target="_blank">
                                                <i class="icofont-eye"></i> Xem chi tiết
                                            </a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="no-records">
                                <i class="icofont-file-alt"></i>
                                <h4>Chưa có hồ sơ khám bệnh</h4>
                                <p>Bệnh nhân này chưa có lịch hẹn nào đã hoàn thành.</p>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="icofont-warning"></i> Không tìm thấy hồ sơ với thông tin đã nhập. Vui lòng kiểm tra lại
                            thông tin!
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
// Include footer
include 'Views/layouts/footer.php';
?>