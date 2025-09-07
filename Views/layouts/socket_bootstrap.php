<?php
// Socket bootstrap partial: include this file on any page to enable realtime
// Assumes PHP session carries user info. Safe to include multiple times.

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Read socket config
$socketCfg = @include __DIR__ . '/../../config/socket.php';
$socketMode = is_array($socketCfg) && !empty($socketCfg['mode']) ? $socketCfg['mode'] : 'auto';
$socketProd = is_array($socketCfg) && !empty($socketCfg['server_url']) ? $socketCfg['server_url'] : '';
$socketDev = is_array($socketCfg) && !empty($socketCfg['dev_url']) ? $socketCfg['dev_url'] : '';

// Current user context (optional for guests)
$userId = $_SESSION['user_id'] ?? '';
$userRole = $_SESSION['user_role'] ?? '';
$userName = $_SESSION['user_name'] ?? '';
?>

<!-- Socket.IO Bootstrap (no UI) -->
<meta name="user-id" content="<?php echo htmlspecialchars($userId, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="user-role" content="<?php echo htmlspecialchars($userRole, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="user-name" content="<?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="socket-mode" content="<?php echo htmlspecialchars($socketMode, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="socket-server-url" content="<?php echo htmlspecialchars($socketProd, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="socket-dev-url" content="<?php echo htmlspecialchars($socketDev, ENT_QUOTES, 'UTF-8'); ?>">

<script src="./assets/js/socket-client.js"></script>