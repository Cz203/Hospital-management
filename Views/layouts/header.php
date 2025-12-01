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

    <style>
    /* Unified nav-bell styles (match main_layout) */
    .nav-bell {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        transition: background 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    }

    .nav-bell i {
        font-size: 18px;
        color: #6b7280;
    }

    .nav-bell:hover {
        background: linear-gradient(135deg, #e8ecff 0%, #e5f2ff 100%);
        box-shadow: 0 4px 10px rgba(102, 126, 234, 0.2);
    }

    /* Notification items highlight */
    .notif-item.unread {
        background-color: rgba(102, 126, 234, 0.08);
    }

    .notif-item.unread:hover {
        background-color: #08429840;
    }

    .notif-item.clicked {
        background-color: rgba(32, 201, 151, 0.12);
    }
    </style>
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

                        <!-- Notifications (shared) -->
                        <li class="nav-item ml-2">
                            <?php include 'Views/layouts/notifications_dropdown.php'; ?>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Notifications script (giữ lại từ header cũ) -->
    <script>
    // Notifications helper: render from server (DB)
    (function setupNotifications() {
        var notifBadge = document.getElementById('notif-badge');
        var notifList = document.getElementById('notif-list');
        var userIdMeta = document.querySelector('meta[name="user-id"]');
        var currentUserId = userIdMeta ? (userIdMeta.getAttribute('content') || '').trim() : '';

        function getNotifLinkByRole() {
            try {
                var roleMeta = document.querySelector('meta[name="user-role"]');
                var role = roleMeta ? (roleMeta.getAttribute('content') || '').trim() : '';
                if (role === 'patient') return './patient_appointments';
                if (role === 'doctor' || role === 'xray_doctor' || role === 'sieuam_doctor' || role ===
                    'xetnghiem_doctor') {
                    return './doctor_appointment_management';
                }
                return './';
            } catch (e) {
                return './';
            }
        }

        function renderItem(message, type, ts, isUnread) {
            var item = document.createElement('a');
            item.className = 'list-group-item list-group-item-action d-flex align-items-start notif-item';
            if (isUnread) item.classList.add('unread');
            item.href = getNotifLinkByRole();
            item.style.cursor = 'pointer';
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
            var item = renderItem(message, type, Date.now(), true);
            notifList.prepend(item);
            if (notifBadge) {
                notifBadge.classList.remove('d-none');
                var current = parseInt(notifBadge.textContent || '0');
                notifBadge.textContent = String(current + 1);
            }
        };

        // Mark all read when opening dropdown (server + UI)
        var notifDropdown = document.querySelector('.notifications-dropdown');
        if (notifDropdown) {
            notifDropdown.addEventListener('show.bs.dropdown', function() {
                if (currentUserId) {
                    fetch('./notifications_mark_all_read').catch(function() {});
                }
                if (notifBadge) {
                    notifBadge.textContent = '0';
                    notifBadge.classList.add('d-none');
                }
            });
            // Highlight clicked notification
            var list = document.getElementById('notif-list');
            if (list) {
                list.addEventListener('click', function(e) {
                    var target = e.target;
                    while (target && target !== list && !target.classList.contains('notif-item')) {
                        target = target.parentNode;
                    }
                    if (target && target.classList && target.classList.contains('notif-item')) {
                        Array.prototype.forEach.call(list.querySelectorAll('.notif-item.clicked'), function(
                            el) {
                            el.classList.remove('clicked');
                        });
                        target.classList.add('clicked');
                        target.classList.remove('unread');
                    }
                });
            }
        }

        // Hydrate from server (DB) and then drain queued items
        try {
            if (currentUserId) {
                fetch('./notifications')
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(resp) {
                        if (resp && resp.success && Array.isArray(resp.data)) {
                            // compute unread count
                            var unreadCount = 0;
                            try {
                                unreadCount = resp.data.filter(function(n) {
                                    return String(n.da_doc) === '0' || n.da_doc === 0;
                                }).length;
                            } catch (e) {}
                            var serverItems = resp.data.map(function(n) {
                                return {
                                    message: n.noi_dung,
                                    type: n.loai || 'info',
                                    ts: new Date(n.ngay_tao).getTime(),
                                    isUnread: (String(n.da_doc) === '0' || n.da_doc === 0)
                                };
                            });
                            while (notifList.firstChild) notifList.removeChild(notifList.firstChild);
                            if (!serverItems.length) {
                                var div = document.createElement('div');
                                div.className = 'p-3 text-muted';
                                div.textContent = 'Không có thông báo';
                                notifList.appendChild(div);
                            } else {
                                // resp.data is DESC (newest first). Append to keep newest on top.
                                serverItems.slice(0, 20).forEach(function(n) {
                                    notifList.appendChild(renderItem(n.message, n.type, n.ts, n
                                        .isUnread));
                                });
                            }
                            // update badge UI
                            if (notifBadge) {
                                if (unreadCount > 0) {
                                    notifBadge.classList.remove('d-none');
                                    notifBadge.textContent = String(unreadCount);
                                } else {
                                    notifBadge.textContent = '0';
                                    notifBadge.classList.add('d-none');
                                }
                            }
                        }
                    })
                    .catch(function() {});
            }
            // no client-side queue
        } catch (e) {}
    })();
    </script>