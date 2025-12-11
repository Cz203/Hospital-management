<?php

/**
 * View chi tiết hồ sơ bệnh án
 * Template này chỉ cần làm 1 lần, mỗi lần gọi chỉ cần truyền data khác vào
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
function escapeHtml($text)
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

function formatDate($date)
{
    if (!$date) return '-';
    return date('d/m/Y', strtotime($date));
}

function formatTime($time)
{
    if (!$time) return '-';
    return date('H:i', strtotime($time));
}

function formatCurrency($amount)
{
    return number_format($amount ?? 0, 0, ',', '.');
}
?>

<ul class="nav nav-tabs mb-3" id="detailTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-exam" data-bs-toggle="tab" data-bs-target="#pane-exam" type="button"
            role="tab">
            <i class="fas fa-stethoscope me-2"></i>Phiếu khám bệnh
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-prescription" data-bs-toggle="tab" data-bs-target="#pane-prescription"
            type="button" role="tab">
            <i class="fas fa-pills me-2"></i>Đơn thuốc
            <?php if ($prescription): ?>
                <span class="badge bg-success ms-2">Có</span>
            <?php else: ?>
                <span class="badge bg-secondary ms-2">Không có</span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-lab-request" data-bs-toggle="tab" data-bs-target="#pane-lab-request"
            type="button" role="tab">
            <i class="fas fa-clipboard-list me-2"></i>Phiếu yêu cầu xét nghiệm
            <?php if ($labRequest): ?>
                <span class="badge bg-success ms-2">Có</span>
            <?php else: ?>
                <span class="badge bg-secondary ms-2">Không có</span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-lab" data-bs-toggle="tab" data-bs-target="#pane-lab" type="button" role="tab">
            <i class="fas fa-vial me-2"></i>Kết quả xét nghiệm
            <?php if ($labResult): ?>
                <span class="badge bg-success ms-2">Có</span>
            <?php else: ?>
                <span class="badge bg-secondary ms-2">Không có</span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-ultrasound-request" data-bs-toggle="tab"
            data-bs-target="#pane-ultrasound-request" type="button" role="tab">
            <i class="fas fa-clipboard me-2"></i>Phiếu yêu cầu siêu âm
            <?php if ($ultrasoundRequest): ?>
                <span class="badge bg-success ms-2">Có</span>
            <?php else: ?>
                <span class="badge bg-secondary ms-2">Không có</span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-ultrasound" data-bs-toggle="tab" data-bs-target="#pane-ultrasound"
            type="button" role="tab">
            <i class="fas fa-wave-square me-2"></i>Kết quả siêu âm
            <?php if ($ultrasoundResult): ?>
                <span class="badge bg-success ms-2">Có</span>
            <?php else: ?>
                <span class="badge bg-secondary ms-2">Không có</span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-xray-request" data-bs-toggle="tab" data-bs-target="#pane-xray-request"
            type="button" role="tab">
            <i class="fas fa-clipboard-check me-2"></i>Phiếu yêu cầu X-Quang
            <?php if ($xrayRequest): ?>
                <span class="badge bg-success ms-2">Có</span>
            <?php else: ?>
                <span class="badge bg-secondary ms-2">Không có</span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-xray" data-bs-toggle="tab" data-bs-target="#pane-xray" type="button"
            role="tab">
            <i class="fas fa-x-ray me-2"></i>Kết quả X-Quang
            <?php if ($xrayResult): ?>
                <span class="badge bg-success ms-2">Có</span>
            <?php else: ?>
                <span class="badge bg-secondary ms-2">Không có</span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-receipt" data-bs-toggle="tab" data-bs-target="#pane-receipt" type="button"
            role="tab">
            <i class="fas fa-receipt me-2"></i>Biên lai viện phí
            <?php if ($receipt): ?>
                <span class="badge bg-success ms-2">Có</span>
            <?php else: ?>
                <span class="badge bg-secondary ms-2">Không có</span>
            <?php endif; ?>
        </button>
    </li>
</ul>

<div class="tab-content" id="detailTabContent">
    <!-- Tab: Phiếu khám bệnh -->
    <?php include 'Views/doctor/partials/medical_record_exam_tab.php'; ?>

    <!-- Tab: Đơn thuốc -->
    <?php include 'Views/doctor/partials/medical_record_prescription_tab.php'; ?>

    <!-- Tab: Phiếu yêu cầu xét nghiệm -->
    <?php include 'Views/doctor/partials/medical_record_lab_request_tab.php'; ?>

    <!-- Tab: Kết quả xét nghiệm -->
    <?php include 'Views/doctor/partials/medical_record_lab_tab.php'; ?>

    <!-- Tab: Phiếu yêu cầu siêu âm -->
    <?php include 'Views/doctor/partials/medical_record_ultrasound_request_tab.php'; ?>

    <!-- Tab: Kết quả siêu âm -->
    <?php include 'Views/doctor/partials/medical_record_ultrasound_tab.php'; ?>

    <!-- Tab: Phiếu yêu cầu X-Quang -->
    <?php include 'Views/doctor/partials/medical_record_xray_request_tab.php'; ?>

    <!-- Tab: Kết quả X-Quang -->
    <?php include 'Views/doctor/partials/medical_record_xray_tab.php'; ?>

    <!-- Tab: Biên lai viện phí -->
    <?php include 'Views/doctor/partials/medical_record_receipt_tab.php'; ?>
</div>