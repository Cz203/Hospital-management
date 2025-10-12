<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'ThinhViet Hospital'; ?></title>
    <?php include 'Views/layouts/socket_bootstrap.php'; ?>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="./assets/css/home.css?v=<?php echo filemtime('./assets/css/home.css'); ?>">

    <!-- Additional CSS for specific pages -->
    <?php if (isset($additional_css)): ?>
    <?php foreach ($additional_css as $css): ?>
    <link rel="stylesheet" href="<?php echo $css; ?>">
    <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container">
            <!-- Brand -->
            <a class="navbar-brand d-flex align-items-center" href="./home">
                <div class="brand-icon me-2">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="brand-text">
                    <span class="brand-name">ThinhViet</span>
                    <span class="brand-subtitle">Hospital</span>
                </div>
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Main Navigation -->
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">
                            <i class="fas fa-home me-1"></i>Trang chủ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#appointment">
                            <i class="fas fa-calendar-check me-1"></i>Đặt lịch
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#chuyenkhoa">
                            <i class="fas fa-stethoscope me-1"></i>Chuyên khoa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#doctors">
                            <i class="fas fa-user-md me-1"></i>Bác sĩ
                        </a>
                    </li>
                </ul>

                <!-- Authentication Links + Notifications -->
                <ul class="navbar-nav">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light btn-sm px-3" href="login">
                            <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="nav-link btn btn-primary btn-sm px-3" href="./register">
                            <i class="fas fa-user-plus me-1"></i>Đăng ký
                        </a>
                    </li>
                    <?php else: ?>
                    <!-- Notifications bell -->
                    <li class="nav-item me-2">
                        <div class="dropdown">
                            <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false" title="Thông báo">
                                <i class="fas fa-bell"></i>
                                <span id="notif-badge"
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">0</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end p-0" style="width: 320px;">
                                <li class="dropdown-header px-3 py-2 fw-bold">Thông báo</li>
                                <li>
                                    <div id="notif-list" class="list-group list-group-flush small"
                                        style="max-height: 320px; overflow-y: auto;">
                                        <div class="p-3 text-muted">Không có thông báo</div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <div class="dropdown d-flex align-items-center">
                            <div class="user-avatar me-2">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <span class="user-name me-1"><?php echo $_SESSION['user_name']; ?></span>
                            <button class="btn btn-link text-white p-0 ms-1" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <?php 
                                        $dashboardRoute = match($_SESSION['user_role']) {
                                            'xray_doctor' => 'xray_dashboard',
                                            'sieuam_doctor' => 'sieuam_dashboard',
                                            default => $_SESSION['user_role'] . '_dashboard'
                                        };
                                    ?>
                                    <a class="dropdown-item" href="./<?php echo $dashboardRoute; ?>">
                                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="./<?php echo $_SESSION['user_role']; ?>_profile">
                                        <i class="fas fa-user me-2"></i>Hồ sơ
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="./logout">
                                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Scroll Progress Bar removed to reduce lag -->
    <script>
    // Notifications helper for socket/client code to push messages
    (function setupNotifications() {
        var notifBadge = document.getElementById('notif-badge');
        var notifList = document.getElementById('notif-list');
        var userIdMeta = document.querySelector('meta[name="user-id"]');
        var currentUserId = userIdMeta ? (userIdMeta.getAttribute('content') || '').trim() : '';

        function getStoreKey() {
            return currentUserId ? ('hm_notifications_' + currentUserId) : 'hm_notifications';
        }

        function readStore() {
            try {
                return JSON.parse(localStorage.getItem(getStoreKey()) || '[]');
            } catch (e) {
                return [];
            }
        }

        function writeStore(items) {
            try {
                localStorage.setItem(getStoreKey(), JSON.stringify(items.slice(0, 20)));
            } catch (e) {}
        }

        function renderItem(message, type, ts) {
            var item = document.createElement('a');
            item.className = 'list-group-item list-group-item-action d-flex align-items-start';
            var color = 'primary';
            if (type === 'success') color = 'success';
            else if (type === 'warning') color = 'warning';
            else if (type === 'danger' || type === 'error') color = 'danger';
            var dateStr = '';
            try {
                var d = ts ? new Date(ts) : new Date();
                var dd = String(d.getDate()).padStart(2, '0');
                var mm = String(d.getMonth() + 1).padStart(2, '0');
                var yyyy = d.getFullYear();
                var hh = String(d.getHours()).padStart(2, '0');
                var mi = String(d.getMinutes()).padStart(2, '0');
                dateStr = dd + '-' + mm + '-' + yyyy + ' ' + hh + ':' + mi;
            } catch (e) {}
            item.innerHTML = '<div class="d-flex w-100">' +
                '<i class="fas fa-circle me-2 mt-1 text-' + color + '"></i>' +
                '<div class="flex-grow-1">' +
                '<div class="text-wrap">' + message + '</div>' +
                '<div class="text-muted small mt-1">' + dateStr + '</div>' +
                '</div>' +
                '</div>';
            return item;
        }

        window.addNotification = function(message, type) {
            if (!notifList) return;
            var empty = notifList.querySelector('.text-muted');
            if (empty) empty.remove();
            var item = renderItem(message, type, Date.now());
            notifList.prepend(item);
            if (notifBadge) {
                notifBadge.classList.remove('d-none');
                var current = parseInt(notifBadge.textContent || '0');
                notifBadge.textContent = String(current + 1);
            }
            var items = readStore();
            items.unshift({
                message: message,
                type: type || 'info',
                ts: Date.now()
            });
            writeStore(items);
        };

        // Clear badge when opening dropdown
        var bells = document.querySelectorAll('a.nav-link[title="Thông báo"][data-bs-toggle="dropdown"]');
        if (bells && bells.length) {
            bells.forEach(function(b) {
                b.addEventListener('show.bs.dropdown', function() {
                    if (notifBadge) {
                        notifBadge.textContent = '0';
                        notifBadge.classList.add('d-none');
                    }
                });
            });
        }

        // Hydrate from storage and drain early queue
        try {
            var stored = readStore();
            if (stored && stored.length && notifList) {
                var emptyHydrate = notifList.querySelector('.text-muted');
                if (emptyHydrate) emptyHydrate.remove();
                stored.slice(0, 20).reverse().forEach(function(n) {
                    notifList.prepend(renderItem(n.message, n.type, n.ts));
                });
            }
            if (window.__notifQueue && Array.isArray(window.__notifQueue)) {
                window.__notifQueue.forEach(function(n) {
                    window.addNotification(n.message, n.type);
                });
                window.__notifQueue = [];
            }
        } catch (e) {}
    })();
    </script>