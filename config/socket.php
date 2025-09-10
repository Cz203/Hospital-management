<?php
return [
    // mode: 'auto' | 'prod' | 'dev'
    'mode' => getenv('SOCKET_MODE') ?: 'auto',
    // URL Render (production)
    'server_url' => getenv('SOCKET_SERVER_URL') ?: 'https://your-socket-service.onrender.com', // Thay bằng URL socket server thật
    // URL local dev
    'dev_url' => getenv('SOCKET_DEV_URL') ?: 'http://localhost:3001',
];