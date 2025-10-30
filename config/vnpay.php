<?php

// Lightweight .env loader (same style as mail.php and database.php)
(function () {
    $envFile = __DIR__ . '/../.env';
    if (!file_exists($envFile)) return;

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') === false || strpos($line, '#') === 0) continue;

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, "\"' ");

        if ($key === '') continue;

        $_ENV[$key] = $value;
        @putenv("$key=$value");
    }
})();

// VNPAY Configuration
// Critical config must be set in .env, no fallback for security
$tmnCode = $_ENV['VNPAY_TMN_CODE'] ?? getenv('VNPAY_TMN_CODE');
$hashSecret = $_ENV['VNPAY_HASH_SECRET'] ?? getenv('VNPAY_HASH_SECRET');

// Validate critical config
if (empty($tmnCode)) {
    throw new Exception('VNPAY_TMN_CODE is not set in .env file. Please configure it.');
}
if (empty($hashSecret)) {
    throw new Exception('VNPAY_HASH_SECRET is not set in .env file. Please configure it.');
}

return [
    // Critical credentials (must be in .env)
    'tmn_code' => $tmnCode,
    'hash_secret' => $hashSecret,

    // URLs (sandbox as default, production must override in .env)
    'url' => ($_ENV['VNPAY_URL'] ?? getenv('VNPAY_URL')) ?: 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
    'api_url' => ($_ENV['VNPAY_API_URL'] ?? getenv('VNPAY_API_URL')) ?: 'http://sandbox.vnpayment.vn/merchant_webapi/merchant.html',

    // Return URL (environment-specific)
    'return_url' => ($_ENV['VNPAY_RETURN_URL'] ?? getenv('VNPAY_RETURN_URL')) ?: null,

    // Payment settings (optional, with sensible defaults)
    'expire_minutes' => (int)(($_ENV['VNPAY_EXPIRE_MINUTES'] ?? getenv('VNPAY_EXPIRE_MINUTES')) ?: 15),
    'locale' => ($_ENV['VNPAY_LOCALE'] ?? getenv('VNPAY_LOCALE')) ?: 'vn',
    'currency' => ($_ENV['VNPAY_CURRENCY'] ?? getenv('VNPAY_CURRENCY')) ?: 'VND',
    'order_type' => ($_ENV['VNPAY_ORDER_TYPE'] ?? getenv('VNPAY_ORDER_TYPE')) ?: 'billpayment',

    // Version (VNPAY API version, rarely changes)
    'version' => ($_ENV['VNPAY_VERSION'] ?? getenv('VNPAY_VERSION')) ?: '2.1.0',
];