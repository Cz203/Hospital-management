<?php

/**
 * Security Configuration
 * Cấu hình bảo mật cho ứng dụng
 */

class SecurityConfig
{
    /**
     * Tạo CSRF token
     */
    public static function generateCSRFToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Sanitize input
     */
    public static function sanitizeInput($input)
    {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate email
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone number
     */
    public static function validatePhone($phone)
    {
        return preg_match('/^[0-9+\-\s()]+$/', $phone);
    }

    /**
     * Rate limiting cho login
     */
    public static function checkRateLimit($key, $maxAttempts = 5, $timeWindow = 900) // 15 phút
    {
        $attempts = $_SESSION['rate_limit'][$key] ?? [];
        $now = time();

        // Xóa các attempt cũ
        $attempts = array_filter($attempts, function ($timestamp) use ($now, $timeWindow) {
            return ($now - $timestamp) < $timeWindow;
        });

        if (count($attempts) >= $maxAttempts) {
            return false;
        }

        $attempts[] = $now;
        $_SESSION['rate_limit'][$key] = $attempts;

        return true;
    }

    /**
     * Cấu hình session security
     */
    public static function configureSession()
    {
        // Chỉ cho phép cookie qua HTTP (không JavaScript)
        ini_set('session.cookie_httponly', 1);

        // Chỉ gửi cookie qua HTTPS (nếu có HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            ini_set('session.cookie_secure', 1);
        }

        // Sử dụng strict mode
        ini_set('session.use_strict_mode', 1);

        // SameSite cookie
        ini_set('session.cookie_samesite', 'Strict');
    }
}