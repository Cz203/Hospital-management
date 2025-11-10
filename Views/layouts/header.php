<!DOCTYPE html>
<html lang="vi">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="description" content="ThinhViet Hospital - Phòng khám đa khoa uy tín">
    <meta name="author" content="Cao Dương Quốc Việt & Ung Nguyễn Trường Thịnh">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo isset($page_title) ? $page_title . ' - ThinhViet Hospital' : 'ThinhViet Hospital - Chăm sóc sức khỏe toàn diện'; ?>
    </title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="./assets/images/favicon.ico" />

    <?php include 'Views/layouts/socket_bootstrap.php'; ?>

    <!-- Bootstrap CSS từ Novena -->
    <link rel="stylesheet" href="./assets/plugins/bootstrap/css/bootstrap.min.css">

    <!-- Icon Font từ Novena -->
    <link rel="stylesheet" href="./assets/plugins/icofont/icofont.min.css">

    <!-- Slick Slider CSS -->
    <link rel="stylesheet" href="./assets/plugins/slick-carousel/slick/slick.css">
    <link rel="stylesheet" href="./assets/plugins/slick-carousel/slick/slick-theme.css">

    <!-- Font Awesome (giữ lại cho icons hiện tại) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Novena Main Stylesheet -->
    <link rel="stylesheet" href="./assets/css/home.css">

    <!-- Custom CSS Override -->
    <link rel="stylesheet" href="./assets/css/novena-custom.css?v=<?php echo time(); ?>">

    <!-- Additional CSS for specific pages -->
    <?php if (isset($additional_css)) : ?>
    <?php foreach ($additional_css as $css) : ?>
    <link rel="stylesheet" href="<?php echo $css; ?>">
    <?php endforeach; ?>
    <?php endif; ?>
</head>

<body id="top">

    <header>
        <!-- Top Bar -->
        <div class="header-top-bar">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <ul class="top-bar-info list-inline-item pl-0 mb-0">
                            <li class="list-inline-item">
                                <a href="mailto:info@thinhviet.com">
                                    <i class="icofont-support-faq mr-2"></i>info@thinhviet.com
                                </a>
                            </li>
                            <li class="list-inline-item">
                                <i class="icofont-location-pin mr-2"></i>123 Đường ABC, Quận 1, TP.HCM
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-6">
                        <div class="text-lg-right top-right-bar mt-2 mt-lg-0">
                            <a href="tel:+842812345678">
                                <span>Gọi ngay: </span>
                                <span class="h4">(84) 28-1234-5678</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Navigation -->
        <nav class="navbar navbar-expand-lg navigation" id="navbar">
            <div class="container">
                <a class="navbar-brand" href="./home">
                    <img src="./assets/img/novena-logo.png" alt="ThinhViet Hospital" class="img-fluid">
                </a>

                <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#navbarmain"
                    aria-controls="navbarmain" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="icofont-navigation-menu"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarmain">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item active">
                            <a class="nav-link" href="./home">Trang chủ</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="./doctor_team">Đặt lịch khám</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="./home#chuyenkhoa">Chuyên khoa</a>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="dropdown03" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                Bác sĩ <i class="icofont-thin-down"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="dropdown03">
                                <li><a class="dropdown-item" href="./doctor_team">Danh sách bác sĩ</a></li>
                                <li><a class="dropdown-item" href="./specialties_all">Bác sĩ theo chuyên khoa</a></li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="./contact">Liên hệ</a>
                        </li>

                        <?php $ctx = getCurrentUserContext(); ?>
                        <?php if (!$ctx['id']) : ?>
                        <!-- Not logged in -->
                        <li class="nav-item">
                            <a class="nav-link btn btn-main btn-sm" href="./login"
                                style="color: #fff; padding: 8px 20px; border-radius: 5px;">
                                <i class="icofont-login mr-1"></i>Đăng nhập
                            </a>
                        </li>
                        <li class="nav-item ml-2">
                            <a class="nav-link btn btn-main-2 btn-sm" href="./register"
                                style="padding: 8px 20px; border-radius: 5px;">
                                <i class="icofont-ui-add mr-1"></i>Đăng ký
                            </a>
                        </li>
                        <?php else : ?>
                        <!-- Logged in -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="dropdown-user" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="icofont-user-alt-4 mr-1"></i>
                                <?php echo htmlspecialchars($ctx['name'] ?: 'Người dùng', ENT_QUOTES, 'UTF-8'); ?>
                                <i class="icofont-thin-down"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-user">
                                <li>
                                    <a class="dropdown-item" href="./<?php echo $ctx['role']; ?>_dashboard">
                                        <i class="icofont-dashboard-web mr-2"></i>Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="./<?php echo $ctx['role']; ?>_profile">
                                        <i class="icofont-user mr-2"></i>Hồ sơ
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="./logout">
                                        <i class="icofont-logout mr-2"></i>Đăng xuất
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Notifications (giữ lại chức năng cũ) -->
                        <li class="nav-item dropdown ml-2">
                            <a class="nav-link position-relative" href="#" id="dropdown-notif" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="icofont-notification"></i>
                                <span id="notif-badge"
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">0</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right p-0" style="width: 320px;">
                                <li class="dropdown-header px-3 py-2 fw-bold">Thông báo</li>
                                <li>
                                    <div id="notif-list" class="list-group list-group-flush small"
                                        style="max-height: 320px; overflow-y: auto;">
                                        <div class="p-3 text-muted">Không có thông báo</div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Notifications script (giữ lại từ header cũ) -->
    <script>
    // Notifications helper
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
        var dropdowns = document.querySelectorAll('[data-toggle="dropdown"]');
        if (dropdowns && dropdowns.length) {
            dropdowns.forEach(function(d) {
                d.addEventListener('click', function() {
                    if (this.id === 'dropdown-notif' && notifBadge) {
                        notifBadge.textContent = '0';
                        notifBadge.classList.add('d-none');
                    }
                });
            });
        }

        // Hydrate from storage
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