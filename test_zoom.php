<?php

/**
 * Test Zoom API Integration
 * File test để kiểm tra Zoom API hoạt động
 */

require_once 'Services/ZoomService.php';

echo "<h1>🎯 Test Zoom API Integration</h1>";

// Test 1: Kiểm tra cấu hình
echo "<h2>1. Kiểm tra cấu hình Zoom API</h2>";
require_once 'config/zoom.php';

if (ZoomConfig::isConfigured()) {
    echo "✅ <strong>Zoom API đã được cấu hình</strong><br>";
    echo "Account ID: " . substr(ZoomConfig::getAccountId(), 0, 10) . "...<br>";
    echo "Client ID: " . substr(ZoomConfig::getClientId(), 0, 10) . "...<br>";
    echo "Client Secret: " . substr(ZoomConfig::getClientSecret(), 0, 10) . "...<br>";
} else {
    echo "❌ <strong>Zoom API chưa được cấu hình</strong><br>";
    echo "Vui lòng cập nhật ZOOM_ACCOUNT_ID, ZOOM_CLIENT_ID và ZOOM_CLIENT_SECRET trong file .env<br>";
    exit;
}

// Test 2: Kiểm tra ZoomService
echo "<h2>2. Kiểm tra ZoomService</h2>";
$zoomService = new ZoomService();

if ($zoomService->isReady()) {
    echo "✅ <strong>ZoomService sẵn sàng</strong><br>";
} else {
    echo "❌ <strong>ZoomService chưa sẵn sàng</strong><br>";
    exit;
}

// Test 3: Tạo meeting test
echo "<h2>3. Test tạo Zoom meeting</h2>";

$testData = [
    'appointment_id' => 999,
    'ngay_hen' => '2025-01-15',
    'gio_hen' => '14:00:00',
    'ly_do' => 'Test meeting',
    'doctor_name' => 'Dr. Test',
    'doctor_email' => 'doctor@test.com',
    'patient_email' => 'patient@test.com'
];

echo "Dữ liệu test:<br>";
echo "<pre>" . print_r($testData, true) . "</pre>";

$result = $zoomService->createMeetingLink($testData);

if ($result['success']) {
    echo "✅ <strong>Meeting tạo thành công!</strong><br>";
    echo "Meeting Link: <a href='{$result['meet_link']}' target='_blank'>{$result['meet_link']}</a><br>";
    echo "Meeting ID: {$result['meeting_id']}<br>";
    if (isset($result['password'])) {
        echo "Password: {$result['password']}<br>";
    }
} else {
    echo "❌ <strong>Lỗi tạo meeting:</strong><br>";
    echo "Error: {$result['error']}<br>";
}

echo "<hr>";
echo "<p><strong>📝 Lưu ý:</strong> File này chỉ để test. Sau khi test xong, hãy xóa file này để bảo mật.</p>";
echo "<p><strong>🔗 Hướng dẫn:</strong> Xem file ZOOM_SETUP.md để biết cách thiết lập Zoom API.</p>";