<?php

/**
 * Zoom API Configuration
 * Cấu hình Zoom API cho tạo meeting links
 */

class ZoomConfig
{
    // Zoom API credentials - Đọc từ .env file (OAuth App)

    public static function getAccountId()
    {
        self::loadEnvFile();
        return $_ENV['ZOOM_ACCOUNT_ID'] ?? 'YOUR_ACCOUNT_ID';
    }

    public static function getClientId()
    {
        self::loadEnvFile();
        return $_ENV['ZOOM_CLIENT_ID'] ?? 'YOUR_CLIENT_ID';
    }

    public static function getClientSecret()
    {
        self::loadEnvFile();
        return $_ENV['ZOOM_CLIENT_SECRET'] ?? 'YOUR_CLIENT_SECRET';
    }


    // Zoom API endpoints
    const BASE_URL = 'https://api.zoom.us/v2';
    const CREATE_MEETING_URL = self::BASE_URL . '/users/me/meetings';

    // Meeting settings
    const MEETING_CONFIG = [
        'type' => 2, // Scheduled meeting
        'duration' => 30, // 30 minutes
        'timezone' => 'Asia/Ho_Chi_Minh',
        'settings' => [
            'host_video' => true,
            'participant_video' => true,
            'join_before_host' => true, // Cho phép tham gia trước host
            'waiting_room' => false, // Tắt waiting room
            'auto_recording' => 'none',
            'mute_upon_entry' => false,
            'approval_type' => 0, // 0 = Automatically approve
            'audio' => 'both',
            'enforce_login' => false, // Không yêu cầu đăng nhập
            'registrants_confirmation_email' => false
        ]
    ];

    private static function loadEnvFile()
    {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value, '"\'');
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }
    /**
     * Kiểm tra cấu hình Zoom API
     */
    public static function isConfigured()
    {
        $accountId = self::getAccountId();
        $clientId = self::getClientId();
        $clientSecret = self::getClientSecret();

        return !empty($accountId) &&
            !empty($clientId) &&
            !empty($clientSecret) &&
            $accountId !== 'YOUR_ACCOUNT_ID' &&
            $clientId !== 'YOUR_CLIENT_ID' &&
            $clientSecret !== 'YOUR_CLIENT_SECRET';
    }
}
