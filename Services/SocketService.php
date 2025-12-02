<?php

class SocketService
{
    public static function emit(string $event, array $data): void
    {
        try {
            // Load socket config (try local then parent)
            $cfg = @include __DIR__ . '/../config/socket.php';
            if (!is_array($cfg) || (empty($cfg['server_url']) && empty($cfg['dev_url']))) {
                $cfg = @include __DIR__ . '/../config/socket.php';
            }

            $mode = isset($cfg['mode']) && $cfg['mode'] ? $cfg['mode'] : 'auto';
            $prod = isset($cfg['server_url']) ? $cfg['server_url'] : '';
            $dev = isset($cfg['dev_url']) ? $cfg['dev_url'] : '';

            // Allow override via GET param (useful in testing)
            $override = isset($_GET['socket']) ? $_GET['socket'] : null;
            if ($override === 'dev' || $override === 'prod') {
                $mode = $override;
            }

            if ($mode === 'dev') {
                $baseUrl = $dev ?: 'http://localhost:3001';
            } elseif ($mode === 'prod') {
                $baseUrl = $prod ?: ($dev ?: 'http://localhost:3001');
            } else {
                $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
                $baseUrl = $isHttps ? ($prod ?: ($dev ?: 'http://localhost:3001'))
                    : ($dev ?: ($prod ?: 'http://localhost:3001'));
            }
            $baseUrl = rtrim($baseUrl, '/');
            $socketUrl = $baseUrl . '/emit';

            $payload = json_encode(['event' => $event, 'data' => $data], JSON_UNESCAPED_UNICODE);
            if ($payload === false) return;

            // Prefer cURL
            $socketApiKey = $_ENV['SOCKET_API_KEY'] ?? getenv('SOCKET_API_KEY') ?? ($_ENV['API_KEY'] ?? getenv('API_KEY')) ?? '';
            $authHeader = $socketApiKey ? 'Authorization: Bearer ' . $socketApiKey : '';

            if (function_exists('curl_init')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $socketUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                $headers = [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($payload)
                ];
                if ($authHeader) $headers[] = $authHeader;
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
                if (stripos($socketUrl, 'https://') === 0) {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                }
                @curl_exec($ch);
                @curl_close($ch);
                return;
            }

            // Fallback to file_get_contents
            $headers = "Content-Type: application/json\r\n";
            if ($authHeader) $headers .= $authHeader . "\r\n";
            $opts = [
                'http' => [
                    'method' => 'POST',
                    'header' => $headers,
                    'content' => $payload,
                    'timeout' => 3,
                ],
            ];
            $ctx = stream_context_create($opts);
            @file_get_contents($socketUrl, false, $ctx);
        } catch (Exception $e) {
            // Silent fail by design
        }
    }
}
