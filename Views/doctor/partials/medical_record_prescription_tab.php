<?php
/**
 * Tab: Đơn thuốc
 * Template này chỉ cần làm 1 lần, mỗi lần gọi chỉ cần truyền data khác vào
 */
?>
<div class="tab-pane fade" id="pane-prescription" role="tabpanel">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="fas fa-pills me-2"></i>Đơn thuốc</h6>
        </div>
        <div class="card-body">
            <?php if ($prescription): ?>
                <style>
                .prescription-container {
                    font-family: 'Times New Roman', serif;
                    font-size: 14px;
                    line-height: 1.4;
                }
                .prescription-header {
                    text-align: center;
                    margin-bottom: 30px;
                    position: relative;
                }
                .prescription-hospital-name {
                    font-size: 18px;
                    font-weight: bold;
                    text-transform: uppercase;
                    margin-bottom: 5px;
                }
                .prescription-address {
                    font-size: 12px;
                    margin-bottom: 10px;
                }
                .prescription-title {
                    font-size: 16px;
                    font-weight: bold;
                    text-transform: uppercase;
                    margin: 20px 0;
                }
                .prescription-id {
                    position: absolute;
                    top: 0;
                    right: 0;
                    font-size: 14px;
                    font-weight: bold;
                }
                .prescription-patient-info {
                    display: flex;
                    margin-bottom: 20px;
                    border: 1px solid #ddd;
                    padding: 15px;
                }
                .prescription-patient-left {
                    flex: 1;
                    padding-right: 20px;
                    border-right: 1px solid #ddd;
                }
                .prescription-patient-right {
                    flex: 1;
                    padding-left: 20px;
                }
                .prescription-info-row {
                    display: flex;
                    margin-bottom: 10px;
                    align-items: center;
                }
                .prescription-info-label {
                    font-weight: bold;
                    min-width: 120px;
                    margin-right: 10px;
                }
                .prescription-info-value {
                    flex: 1;
                    min-height: 20px;
                    border-bottom: 1px solid #000;
                    padding: 2px 5px;
                }
                .prescription-diagnosis {
                    margin-bottom: 20px;
                }
                .prescription-diagnosis-label {
                    font-weight: bold;
                    margin-bottom: 10px;
                }
                .prescription-diagnosis-content {
                    border-bottom: 1px solid #000;
                    padding: 5px;
                    min-height: 40px;
                }
                .prescription-medication {
                    margin-bottom: 20px;
                }
                .prescription-medication-title {
                    font-weight: bold;
                    margin-bottom: 10px;
                }
                .prescription-medication-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                .prescription-medication-table th,
                .prescription-medication-table td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                .prescription-medication-table th {
                    background-color: #e8f5e8;
                    font-weight: bold;
                    text-align: center;
                }
                .prescription-medication-table .stt {
                    text-align: center;
                    width: 50px;
                }
                .prescription-medication-table .medication-name {
                    width: 200px;
                }
                .prescription-medication-table .active-ingredient {
                    width: 150px;
                }
                .prescription-medication-table .unit {
                    text-align: center;
                    width: 80px;
                }
                .prescription-medication-table .quantity {
                    text-align: center;
                    width: 60px;
                }
                .prescription-medication-table .usage {
                    width: 200px;
                }
                .prescription-instructions {
                    margin-bottom: 20px;
                }
                .prescription-instructions-label {
                    font-weight: bold;
                    margin-bottom: 10px;
                }
                .prescription-instructions-content {
                    border-bottom: 1px solid #000;
                    padding: 5px;
                    min-height: 30px;
                }
                .prescription-signature {
                    margin-top: 30px;
                    display: flex;
                    justify-content: flex-end;
                }
                .prescription-signature-box {
                    background-color: #f5f5f5;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    padding: 20px;
                    text-align: center;
                    min-width: 200px;
                }
                .prescription-signature-date {
                    font-size: 14px;
                    font-weight: bold;
                    margin-bottom: 15px;
                }
                .prescription-signature-label {
                    font-weight: bold;
                    margin-bottom: 15px;
                }
                .prescription-doctor-name {
                    font-weight: bold;
                }
                </style>
                <div class="prescription-container">
                    <!-- Header -->
                    <div class="prescription-header">
                        <div class="prescription-hospital-name">PHÒNG KHÁM ĐA KHOA THINHVIET</div>
                        <div class="prescription-address">Địa chỉ: Gò vấp</div>
                        <div class="prescription-title">ĐƠN THUỐC</div>
                        <div class="prescription-id">Mã đơn thuốc: <?php echo escapeHtml($prescription['MaDonThuoc'] ?? ''); ?></div>
                    </div>

                    <!-- Thông tin bệnh nhân - Layout 2 cột -->
                    <div class="prescription-patient-info">
                        <div class="prescription-patient-left">
                            <div class="prescription-info-row">
                                <div class="prescription-info-label">Họ tên:</div>
                                <div class="prescription-info-value"><?php echo escapeHtml($exam['ho_ten'] ?? '-'); ?></div>
                            </div>
                            <div class="prescription-info-row">
                                <div class="prescription-info-label">Mã Bệnh nhân:</div>
                                <div class="prescription-info-value"><?php echo escapeHtml($exam['ma_benh_nhan'] ?? '-'); ?></div>
                            </div>
                            <div class="prescription-info-row">
                                <div class="prescription-info-label">Mã số BHYT (nếu có):</div>
                                <div class="prescription-info-value"><?php echo !empty($exam['so_the_bhyt']) ? escapeHtml($exam['so_the_bhyt']) : 'Thu phí'; ?></div>
                            </div>
                            <div class="prescription-info-row">
                                <div class="prescription-info-label">Địa chỉ liên hệ:</div>
                                <div class="prescription-info-value"><?php echo escapeHtml($exam['dia_chi'] ?? '-'); ?></div>
                            </div>
                        </div>

                        <div class="prescription-patient-right">
                            <div class="prescription-info-row">
                                <div class="prescription-info-label">Ngày sinh:</div>
                                <div class="prescription-info-value">
                                    <?php
                                        $ngaySinh = '-';
                                        if (!empty($exam['ngay_sinh_benh_nhan'])) {
                                            try {
                                                $ngaySinh = (new DateTime($exam['ngay_sinh_benh_nhan']))->format('d/m/Y');
                                            } catch (Exception $e) {
                                                $ngaySinh = $exam['ngay_sinh'] ?? '-';
                                            }
                                        } elseif (!empty($exam['ngay_sinh']) && !empty($exam['thang_sinh']) && !empty($exam['nam_sinh'])) {
                                            $ngaySinh = sprintf('%02d/%02d/%04d', $exam['ngay_sinh'], $exam['thang_sinh'], $exam['nam_sinh']);
                                        } else {
                                            $ngaySinh = $exam['ngay_sinh'] ?? '-';
                                        }
                                        echo escapeHtml($ngaySinh);
                                    ?>
                                </div>
                            </div>
                            <div class="prescription-info-row">
                                <div class="prescription-info-label">Giới tính:</div>
                                <div class="prescription-info-value"><?php echo escapeHtml($exam['gioi_tinh'] ?? '-'); ?></div>
                            </div>
                            <div class="prescription-info-row">
                                <div class="prescription-info-label">Số điện thoại:</div>
                                <div class="prescription-info-value"><?php echo escapeHtml($exam['so_dien_thoai'] ?? '-'); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Chẩn đoán -->
                    <div class="prescription-diagnosis">
                        <div class="prescription-diagnosis-label">Chẩn đoán:</div>
                        <div class="prescription-diagnosis-content"><?php echo escapeHtml($prescription['ChanDoan'] ?? $exam['chan_doan_vao_vien'] ?? ''); ?></div>
                    </div>

                    <!-- Thuốc điều trị -->
                    <div class="prescription-medication">
                        <div class="prescription-medication-title">Thuốc điều trị</div>
                        <?php 
                            // Lấy số ngày dùng thuốc hiển thị riêng (ưu tiên từ chi tiết, fallback 1)
                            $soNgayIn = 1;
                            if (!empty($prescriptionDetails)) {
                                foreach ($prescriptionDetails as $m) {
                                    if (isset($m['so_ngay']) && (int)$m['so_ngay'] > 0) { 
                                        $soNgayIn = (int)$m['so_ngay']; 
                                        break; 
                                    }
                                }
                            }
                        ?>
                        <div style="display:flex; justify-content:flex-end; margin-bottom:10px;">
                            <div style="display:inline-flex; align-items:center; gap:8px; font-weight:bold;">
                                <span>Số ngày dùng thuốc:</span>
                                <span style="min-width:40px; border-bottom:1px solid #000; text-align:center; display:inline-block; padding:2px 6px;">
                                    <?php echo $soNgayIn; ?>
                                </span>
                                <span>ngày</span>
                            </div>
                        </div>
                        
                        <?php if (!empty($prescriptionDetails)): ?>
                        <table class="prescription-medication-table">
                            <thead>
                                <tr>
                                    <th class="stt">STT</th>
                                    <th class="medication-name">Tên Thuốc</th>
                                    <th class="active-ingredient">Hoạt chất</th>
                                    <th class="unit">ĐVT</th>
                                    <th class="quantity">SL</th>
                                    <th class="usage">Cách dùng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prescriptionDetails as $index => $medication): ?>
                                <tr>
                                    <td class="stt"><?php echo $index + 1; ?></td>
                                    <td class="medication-name"><?php echo escapeHtml($medication['TenThuoc'] ?? ''); ?></td>
                                    <td class="active-ingredient"><?php echo escapeHtml($medication['HoatChat'] ?? ''); ?></td>
                                    <td class="unit"><?php echo escapeHtml($medication['DonViTinh'] ?? ''); ?></td>
                                    <td class="quantity"><?php echo escapeHtml($medication['SoLuong'] ?? ''); ?></td>
                                    <td class="usage">
                                        <?php
                                            $unit = strtolower($medication['DonViTinh'] ?? '');
                                            $hasPerSession = isset($medication['vien_sang']) || isset($medication['vien_trua']) || isset($medication['vien_chieu']) || isset($medication['vien_toi']);
                                            if ($hasPerSession && (strpos($unit, 'viên') !== false || strpos($unit, 'vien') !== false)) {
                                                $sessions = [
                                                    ['label' => 'Sáng',  'dose' => $medication['vien_sang']  ?? 0, 'meal' => $medication['sang_bua']  ?? 'none'],
                                                    ['label' => 'Trưa',  'dose' => $medication['vien_trua']  ?? 0, 'meal' => $medication['trua_bua']  ?? 'none'],
                                                    ['label' => 'Chiều', 'dose' => $medication['vien_chieu'] ?? 0, 'meal' => $medication['chieu_bua'] ?? 'none'],
                                                    ['label' => 'Tối',   'dose' => $medication['vien_toi']   ?? 0, 'meal' => $medication['toi_bua']   ?? 'none'],
                                                ];
                                                foreach ($sessions as $s) {
                                                    $dose = (float)$s['dose'];
                                                    if ($dose > 0) {
                                                        $meal = $s['meal'] === 'before' ? 'trước ăn' : ($s['meal'] === 'after' ? 'sau ăn' : '');
                                                        echo '<div>' . $s['label'] . ': ' . rtrim(rtrim(number_format($dose, 2, ',', '.'), '0'), ',') . ' viên' . ($meal ? ' (' . $meal . ')' : '') . '</div>';
                                                    }
                                                }
                                                if (!empty($medication['ghi_chu_cach_dung'])) {
                                                    echo '<div><em>' . escapeHtml($medication['ghi_chu_cach_dung']) . '</em></div>';
                                                }
                                            } else {
                                                echo escapeHtml($medication['LieuDung'] ?? '');
                                            }
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div style="text-align: center; padding: 20px; font-style: italic; color: #666;">Không có thuốc trong đơn</div>
                        <?php endif; ?>
                    </div>

                    <!-- Lời dặn -->
                    <div class="prescription-instructions">
                        <div class="prescription-instructions-label">Lời dặn:</div>
                        <div class="prescription-instructions-content"><?php echo escapeHtml($prescription['GhiChu'] ?? $prescription['LoiDan'] ?? ''); ?></div>
                    </div>

                    <!-- Chữ ký -->
                    <div class="prescription-signature">
                        <div class="prescription-signature-box">
                            <div class="prescription-signature-date">
                                <?php 
                                    if (!empty($prescription['NgayKe'])) {
                                        try {
                                            $ngayKe = new DateTime($prescription['NgayKe']);
                                            echo $ngayKe->format('d/m/Y');
                                        } catch (Exception $e) {
                                            echo formatDate($prescription['NgayKe']);
                                        }
                                    } else {
                                        echo date('d/m/Y');
                                    }
                                ?>
                            </div>
                            <div class="prescription-signature-label">Bác sỹ ký tên</div>
                            <div class="prescription-doctor-name">
                                <?php echo escapeHtml($prescription['TenBacSi'] ?? $prescription['ten_bac_si'] ?? $exam['ten_bac_si'] ?? 'GSTS. Cao Việt'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-5">Chưa có đơn thuốc</p>
            <?php endif; ?>
        </div>
    </div>
</div>
