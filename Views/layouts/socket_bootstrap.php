<?php

use Firebase\JWT\JWT;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$socketCfg = @include __DIR__ . '/../../config/socket.php';
$socketMode = is_array($socketCfg) && !empty($socketCfg['mode']) ? $socketCfg['mode'] : 'auto';
$socketProd = is_array($socketCfg) && !empty($socketCfg['server_url']) ? $socketCfg['server_url'] : '';
$socketDev = is_array($socketCfg) && !empty($socketCfg['dev_url']) ? $socketCfg['dev_url'] : '';

require_once __DIR__ . '/layout_helper.php';
$ctx = function_exists('getCurrentUserContext') ? getCurrentUserContext() : ['id' => null, 'role' => '', 'name' => ''];
$userId = $ctx['id'] ?? '';
$userRole = $ctx['role'] ?? '';
$userName = $ctx['name'] ?? '';

$socketJwt = '';
try {
    if ($userId && $userRole) {
        // Đọc secret từ .env: SOCKET_JWT_SECRET (ưu tiên) hoặc API_SECRET
        $secret = $_ENV['SOCKET_JWT_SECRET'] ?? getenv('SOCKET_JWT_SECRET');
        if (!$secret) {
            $secret = $_ENV['API_SECRET'] ?? getenv('API_SECRET') ?: '';
        }
        if ($secret !== '') {
            $now = time();
            $payload = [
                'sub'  => (string)$userId,
                'role' => (string)$userRole,
                'name' => (string)$userName,
                'iat'  => $now,
                'exp'  => $now + 3600,
            ];
            $socketJwt = JWT::encode($payload, $secret, 'HS256');
        }
    }
} catch (Throwable $e) {
    $socketJwt = '';
}
?>

<meta name="user-id" content="<?php echo htmlspecialchars($userId, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="user-role" content="<?php echo htmlspecialchars($userRole, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="socket-mode" content="<?php echo htmlspecialchars($socketMode, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="socket-server-url" content="<?php echo htmlspecialchars($socketProd, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="socket-dev-url" content="<?php echo htmlspecialchars($socketDev, ENT_QUOTES, 'UTF-8'); ?>">
<?php if (!empty($socketJwt)): ?>
    <meta name="socket-auth-token" content="<?php echo htmlspecialchars($socketJwt, ENT_QUOTES, 'UTF-8'); ?>">
<?php endif; ?>

<script src="./assets/js/socket-client.js"></script>