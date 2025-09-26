<?php

// Lightweight .env loader (same style as other configs)
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

return [
    'driver' => ($_ENV['MAIL_DRIVER'] ?? getenv('MAIL_DRIVER')) ?: 'smtp',
    'host' => ($_ENV['MAIL_HOST'] ?? getenv('MAIL_HOST')) ?: 'smtp.gmail.com',
    'port' => (int)(($_ENV['MAIL_PORT'] ?? getenv('MAIL_PORT')) ?: 587),
    'username' => ($_ENV['MAIL_USERNAME'] ?? getenv('MAIL_USERNAME')) ?: '',
    'password' => ($_ENV['MAIL_PASSWORD'] ?? getenv('MAIL_PASSWORD')) ?: '',
    'encryption' => ($_ENV['MAIL_ENCRYPTION'] ?? getenv('MAIL_ENCRYPTION')) ?: 'tls', // tls|ssl|empty
    'from_email' => ($_ENV['MAIL_FROM_ADDRESS'] ?? getenv('MAIL_FROM_ADDRESS')) ?: 'no-reply@yourdomain.com',
    'from_name' => ($_ENV['MAIL_FROM_NAME'] ?? getenv('MAIL_FROM_NAME')) ?: 'Hospital Management',
    'debug' => (bool)(($_ENV['MAIL_DEBUG'] ?? getenv('MAIL_DEBUG')) ?: false),
    // optional advanced spam-resistance
    'dkim_domain' => ($_ENV['MAIL_DKIM_DOMAIN'] ?? getenv('MAIL_DKIM_DOMAIN')) ?: '',
    'dkim_selector' => ($_ENV['MAIL_DKIM_SELECTOR'] ?? getenv('MAIL_DKIM_SELECTOR')) ?: '',
    'dkim_private_key' => ($_ENV['MAIL_DKIM_PRIVATE_KEY'] ?? getenv('MAIL_DKIM_PRIVATE_KEY')) ?: '',
];