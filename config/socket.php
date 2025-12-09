<?php

// Simple .env loader (reused pattern from database.php but localized here)
// Loads key=value pairs into $_ENV and process environment for getenv()
(function () {
    $envFile = __DIR__ . '/../.env';
    if (!file_exists($envFile)) {
        return;
    }
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') === false || strpos($line, '#') === 0) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, "\"' ");
        if ($key === '') {
            continue;
        }
        $_ENV[$key] = $value;
        @putenv("$key=$value");
    }
})();

return [
    'mode' => ($_ENV['SOCKET_MODE'] ?? getenv('SOCKET_MODE')) ?: 'auto',
    'server_url' => ($_ENV['SOCKET_SERVER_URL'] ?? getenv('SOCKET_SERVER_URL')) ?: 'https://your-socket-service.onrender.com',
    'dev_url' => ($_ENV['SOCKET_DEV_URL'] ?? getenv('SOCKET_DEV_URL')) ?: 'http://localhost:3001',
];