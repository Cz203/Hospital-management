<?php
/**
 * View chi tiết hồ sơ bệnh án cho Patient
 * Giao diện với folder icons như trong hình
 */

// Data được truyền từ Controller
$exam = $data['exam'] ?? [];
$prescription = $data['prescription'] ?? null;
$prescriptionDetails = $data['prescription_details'] ?? [];
$labRequest = $data['lab_request'] ?? null;
$labResult = $data['lab_result'] ?? null;
$ultrasoundRequest = $data['ultrasound_request'] ?? null;
$ultrasoundResult = $data['ultrasound_result'] ?? null;
$xrayRequest = $data['xray_request'] ?? null;
$xrayResult = $data['xray_result'] ?? null;
$receipt = $data['receipt'] ?? null;
$receiptDetails = $data['receipt_details'] ?? [];

// Helper functions
function escapeHtml($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

function formatDate($date) {
    if (!$date) return '-';
    return date('d/m/Y', strtotime($date));
}

function formatTime($time) {
    if (!$time) return '-';
    return date('H:i', strtotime($time));
}

// Lấy thông tin bệnh nhân
$patientName = escapeHtml($exam['ho_ten'] ?? '');
$patientId = $exam['ma_benh_nhan'] ?? '';
$patientBirthYear = $exam['nam_sinh'] ?? '';
$patientGender = escapeHtml($exam['gioi_tinh'] ?? '');
$patientPhone = escapeHtml($exam['so_dien_thoai'] ?? '');
$patientAddress = escapeHtml($exam['dia_chi'] ?? '');

// Lấy ngày khám
$examDate = formatDate($exam['ngay_kham_formatted'] ?? $exam['ngay_hen'] ?? '');
$examDateFormatted = $examDate !== '-' ? date('d/m/Y', strtotime(str_replace('/', '-', $examDate))) : date('d/m/Y');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết hồ sơ bệnh án</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .patient-info-box {
            border: 1px solid #000;
            padding: 15px;
            margin-bottom: 30px;
            background-color: #fff;
            border-radius: 4px;
        }
        .patient-info-box .info-row {
            margin-bottom: 8px;
            font-size: 14px;
        }
        .patient-info-box .info-row:last-child {
            margin-bottom: 0;
        }
        .exam-date {
            text-align: center;
            color: #dc3545;
            font-weight: bold;
            font-size: 18px;
            margin: 30px 0;
        }
        .folder-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
            margin: 40px 0;
        }
        .folder-item {
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .folder-item:hover {
            transform: translateY(-5px);
        }
        .folder-icon {
            width: 80px;
            height: 80px;
            background-color: #5dade2;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .folder-icon i {
            font-size: 40px;
            color: #fff;
        }
        .folder-label {
            font-size: 14px;
            font-weight: 500;
            color: #333;
            margin-top: 8px;
        }
        .back-link {
            text-align: center;
            margin-top: 40px;
        }
        .back-link a {
            color: #0d6efd;
            text-decoration: underline;
            font-size: 16px;
        }
        .back-link a:hover {
            color: #0a58ca;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Patient Info Box -->
        <div class="patient-info-box">
            <div class="info-row">
                <strong>Họ tên:</strong> <?php echo $patientName; ?> - <strong>ID:</strong> <?php echo escapeHtml($patientId); ?>
            </div>
            <div class="info-row">
                <strong>Năm sinh:</strong> <?php echo escapeHtml($patientBirthYear); ?> - 
                <strong><?php echo $patientGender; ?></strong> - 
                <strong>Điện thoại:</strong> <?php echo $patientPhone; ?>
            </div>
            <div class="info-row">
                <strong>Địa chỉ:</strong> <?php echo $patientAddress; ?>
            </div>
        </div>

        <!-- Exam Date -->
        <div class="exam-date">
            NGÀY KHÁM <?php echo $examDateFormatted; ?>
        </div>

        <!-- Folder Icons -->
        <div class="folder-container">
            <!-- Phiếu chỉ định xét nghiệm -->
            <?php if (!empty($labRequest)): ?>
            <div class="folder-item" onclick="showFormDetail('lab-request')">
                <div class="folder-icon">
                    <i class="fas fa-flask"></i>
                </div>
                <div class="folder-label">Phiếu chỉ định xét nghiệm</div>
            </div>
            <?php endif; ?>

            <!-- Phiếu chỉ định siêu âm -->
            <?php if (!empty($ultrasoundRequest)): ?>
            <div class="folder-item" onclick="showFormDetail('ultrasound-request')">
                <div class="folder-icon">
                    <i class="fas fa-wave-square"></i>
                </div>
                <div class="folder-label">Phiếu chỉ định siêu âm</div>
            </div>
            <?php endif; ?>

            <!-- Phiếu chỉ định chụp X-quang -->
            <?php if (!empty($xrayRequest)): ?>
            <div class="folder-item" onclick="showFormDetail('xray-request')">
                <div class="folder-icon">
                    <i class="fas fa-x-ray"></i>
                </div>
                <div class="folder-label">Phiếu chỉ định chụp X-quang</div>
            </div>
            <?php endif; ?>

            <!-- Kết quả xét nghiệm -->
            <?php if (!empty($labResult)): ?>
            <div class="folder-item" onclick="showFormDetail('lab-result')">
                <div class="folder-icon">
                    <i class="fas fa-vial"></i>
                </div>
                <div class="folder-label">Kết quả xét nghiệm</div>
            </div>
            <?php endif; ?>

            <!-- Kết quả siêu âm -->
            <?php if (!empty($ultrasoundResult)): ?>
            <div class="folder-item" onclick="showFormDetail('ultrasound-result')">
                <div class="folder-icon">
                    <i class="fas fa-procedures"></i>
                </div>
                <div class="folder-label">Kết quả siêu âm</div>
            </div>
            <?php endif; ?>

            <!-- Kết quả chụp X-quang -->
            <?php if (!empty($xrayResult)): ?>
            <div class="folder-item" onclick="showFormDetail('xray-result')">
                <div class="folder-icon">
                    <i class="fas fa-x-ray"></i>
                </div>
                <div class="folder-label">Kết quả chụp X-quang</div>
            </div>
            <?php endif; ?>

            <!-- Hình ảnh X-Quang -->
            <?php 
            $hasXrayImages = !empty($xrayResult) && !empty($xrayResult['hinh_anh']) && is_array($xrayResult['hinh_anh']) && count($xrayResult['hinh_anh']) > 0;
            if ($hasXrayImages): 
            ?>
            <div class="folder-item" onclick="showFormDetail('xray-images')">
                <div class="folder-icon">
                    <i class="fas fa-images"></i>
                </div>
                <div class="folder-label">Hình ảnh X-Quang</div>
            </div>
            <?php endif; ?>

            <!-- Hình ảnh Siêu âm -->
            <?php 
            $hasUltrasoundImages = !empty($ultrasoundResult) && !empty($ultrasoundResult['hinh_anh']) && is_array($ultrasoundResult['hinh_anh']) && count($ultrasoundResult['hinh_anh']) > 0;
            if ($hasUltrasoundImages): 
            ?>
            <div class="folder-item" onclick="showFormDetail('ultrasound-images')">
                <div class="folder-icon">
                    <i class="fas fa-image"></i>
                </div>
                <div class="folder-label">Hình ảnh Siêu âm</div>
            </div>
            <?php endif; ?>

            <!-- Phiếu khám bệnh -->
            <?php if (!empty($exam) && !empty($exam['id'])): ?>
            <div class="folder-item" onclick="showFormDetail('examination')">
                <div class="folder-icon">
                    <i class="fas fa-stethoscope"></i>
                </div>
                <div class="folder-label">Phiếu khám bệnh</div>
            </div>
            <?php endif; ?>

            <!-- Đơn thuốc -->
            <?php if (!empty($prescription)): ?>
            <div class="folder-item" onclick="showFormDetail('prescription')">
                <div class="folder-icon">
                    <i class="fas fa-pills"></i>
                </div>
                <div class="folder-label">Đơn thuốc</div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Back Link -->
        <div class="back-link">
            <a href="javascript:history.back()"><< Trở về</a>
        </div>
    </div>

    <script>
        function showFormDetail(formType) {
            // Lấy exam_id và patient_id từ URL
            const urlParams = new URLSearchParams(window.location.search);
            let examId = urlParams.get('exam_id') || urlParams.get('lich_hen_id');
            let patientId = urlParams.get('patient_id');
            
            // Nếu không có trong URL, thử lấy từ PHP variable (lich_hen_id từ exam)
            if (!examId) {
                <?php 
                $lichHenId = $exam['lich_hen_id'] ?? $exam['id'] ?? null;
                if ($lichHenId): 
                ?>
                examId = <?php echo json_encode($lichHenId); ?>;
                <?php endif; ?>
            }
            
            if (!examId) {
                alert('Không tìm thấy ID lịch hẹn');
                console.error('examId not found. URL params:', window.location.search);
                return;
            }

            // Kiểm tra xem có phải đang ở trang tra cứu không (có patient_id trong URL)
            const isLookupPage = patientId !== null && patientId !== '';
            
            let url = '';
            if (isLookupPage) {
                // Sử dụng routes tra cứu (không cần đăng nhập)
                switch(formType) {
                    case 'lab-request':
                        url = `./?action=lookup_patient_lab_request&exam_id=${examId}&lich_hen_id=${examId}&patient_id=${patientId}`;
                        break;
                    case 'ultrasound-request':
                        url = `./?action=lookup_patient_ultrasound_request&exam_id=${examId}&lich_hen_id=${examId}&patient_id=${patientId}`;
                        break;
                    case 'xray-request':
                        url = `./?action=lookup_patient_xray_request&exam_id=${examId}&lich_hen_id=${examId}&patient_id=${patientId}`;
                        break;
                    case 'lab-result':
                        url = `./?action=lookup_patient_lab_result&exam_id=${examId}&lich_hen_id=${examId}&patient_id=${patientId}`;
                        break;
                    case 'ultrasound-result':
                        url = `./?action=lookup_patient_ultrasound_result&exam_id=${examId}&lich_hen_id=${examId}&patient_id=${patientId}`;
                        break;
                    case 'xray-result':
                        url = `./?action=lookup_patient_xray_result&exam_id=${examId}&lich_hen_id=${examId}&patient_id=${patientId}`;
                        break;
                    case 'examination':
                        url = `./?action=lookup_patient_examination_form&exam_id=${examId}&lich_hen_id=${examId}&patient_id=${patientId}`;
                        break;
                    case 'prescription':
                        url = `./?action=lookup_patient_prescription_form&exam_id=${examId}&lich_hen_id=${examId}&patient_id=${patientId}`;
                        break;
                    case 'xray-images':
                        url = `./?action=lookup_patient_xray_images&exam_id=${examId}&lich_hen_id=${examId}&patient_id=${patientId}`;
                        break;
                    case 'ultrasound-images':
                        url = `./?action=lookup_patient_ultrasound_images&exam_id=${examId}&lich_hen_id=${examId}&patient_id=${patientId}`;
                        break;
                    default:
                        alert('Không tìm thấy thông tin');
                        return;
                }
            } else {
                // Sử dụng routes đăng nhập (yêu cầu authentication)
                switch(formType) {
                    case 'lab-request':
                        url = `./?action=view_patient_lab_request&exam_id=${examId}&lich_hen_id=${examId}`;
                        break;
                    case 'ultrasound-request':
                        url = `./?action=view_patient_ultrasound_request&exam_id=${examId}&lich_hen_id=${examId}`;
                        break;
                    case 'xray-request':
                        url = `./?action=view_patient_xray_request&exam_id=${examId}&lich_hen_id=${examId}`;
                        break;
                    case 'lab-result':
                        url = `./?action=view_patient_lab_result&exam_id=${examId}&lich_hen_id=${examId}`;
                        break;
                    case 'ultrasound-result':
                        url = `./?action=view_patient_ultrasound_result&exam_id=${examId}&lich_hen_id=${examId}`;
                        break;
                    case 'xray-result':
                        url = `./?action=view_patient_xray_result&exam_id=${examId}&lich_hen_id=${examId}`;
                        break;
                    case 'examination':
                        url = `./?action=view_patient_examination_form&exam_id=${examId}&lich_hen_id=${examId}`;
                        break;
                    case 'prescription':
                        url = `./?action=view_patient_prescription_form&exam_id=${examId}&lich_hen_id=${examId}`;
                        break;
                    case 'xray-images':
                        url = `./?action=view_patient_xray_images&exam_id=${examId}&lich_hen_id=${examId}`;
                        break;
                    case 'ultrasound-images':
                        url = `./?action=view_patient_ultrasound_images&exam_id=${examId}&lich_hen_id=${examId}`;
                        break;
                    default:
                        alert('Không tìm thấy thông tin');
                        return;
                }
            }
            
            console.log('Opening URL:', url);
            // Mở trong tab mới với giao diện PDF-like
            window.open(url, '_blank');
        }
    </script>
</body>
</html>

