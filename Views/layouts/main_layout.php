<?php
// Đảm bảo session đã được start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'Views/layouts/layout_helper.php';
$__ctx = getCurrentUserContext();
// Kiểm tra user đã đăng nhập
if (!$__ctx['id']) {
    header("Location: ./login");
    exit();
}
$user_role = $__ctx['role'];
$page_title = $page_title ?? 'Hệ thống Quản lý Bệnh viện';


$ctx = getCurrentUserContext();
$displayName = htmlspecialchars($ctx['name'] ?: 'Bệnh nhân', ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>

    <!-- Socket.IO Bootstrap Partial -->
    <?php include 'Views/layouts/socket_bootstrap.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #f8f9fa;
    }

    .main-content {
        margin-left: 250px;
        padding: 20px;
        min-height: 100vh;
    }

    .navbar {
        margin-left: 250px;
    }

    .navbar-brand {
        font-weight: 600;
        font-size: 1.5rem;
    }

    .navbar-nav .nav-link {
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .navbar-nav .nav-link:hover {
        color: #667eea !important;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
    }

    .dropdown-menu {
        border: none;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }

    .dropdown-item {
        padding: 8px 20px;
        transition: all 0.3s ease;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #667eea;
    }

    /* Bell styles */
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

    .nav-bell .badge {
        transform: translate(35%, -35%);
    }

    .role-badge {
        font-size: 0.75rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
    }

    .role-admin {
        background-color: #dc3545;
        color: white;
    }

    .role-doctor {
        background-color: #198754;
        color: white;
    }

    .role-patient {
        background-color: #0d6efd;
        color: white;
    }

    .role-letan {
        background-color: #6c757d;
        color: white;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
        }

        .navbar {
            margin-left: 0;
        }

        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }
        .sidebar.show {
            transform: translateX(0);

        .navbar-nav .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #667eea !important;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .dropdown-item {
            padding: 8px 20px;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #667eea;
        }

        /* Bell styles */
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

        .nav-bell .badge {
            transform: translate(35%, -35%);
        }

        .role-badge {
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 500;
        }

        .role-admin {
            background-color: #dc3545;
            color: white;
        }

        .role-doctor {
            background-color: #198754;
            color: white;
        }

        .role-patient {
            background-color: #0d6efd;
            color: white;
        }

        .role-letan {
            background-color: #6c757d;
            color: white;
        }

        .role-sieuam {
            background-color: #fd7e14;
            color: white;
        }

        .role-xetnghiem {
            background-color: #20c997;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }

            .navbar {
                margin-left: 0;
            }

            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.show {
                transform: translateX(0);
            }
        }
    }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <?php
    switch ($user_role) {
        case 'admin':
            include 'Views/layouts/admin_sidebar.php';
            break;
        case 'doctor':
            include 'Views/layouts/doctor_sidebar.php';
            break;
        case 'xray_doctor':
            include 'Views/layouts/xray_sidebar.php';
            break;
        case 'sieuam_doctor':
            include 'Views/layouts/sieuam_sidebar.php';
            break;
        case 'xetnghiem_doctor':
            include 'Views/layouts/xetnghiem_sidebar.php';
            break;
        case 'patient':
            include 'Views/layouts/patient_sidebar.php';
            break;
        case 'letan':
            include 'Views/layouts/reception_sidebar.php';
            break;
    }
    ?>

    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <!-- Mobile Toggle -->
            <button class="navbar-toggler d-lg-none" type="button" onclick="toggleSidebar()">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Logo -->
            <a class="navbar-brand" href="./home">
                <i class="fas fa-hospital text-primary me-2"></i>
                ThinhViet Clinic
            </a>

            <!-- Navigation Items -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if ($user_role == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="./admin_dashboard">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-users me-1"></i>Quản lý người dùng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-calendar-alt me-1"></i>Lịch hẹn
                        </a>
                    </li>
                    <?php elseif ($user_role == 'doctor'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="./doctor_dashboard">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-calendar-check me-1"></i>Lịch hẹn
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-user-injured me-1"></i>Bệnh nhân
                        </a>
                    </li>
                    <?php elseif ($user_role == 'xray_doctor'): ?>

                        <li class="nav-item">
                            <a class="nav-link" href="./xray_dashboard">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-x-ray me-1"></i>Chụp X-Quang
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-file-medical me-1"></i>Kết quả
                            </a>
                        </li>
                    <?php elseif ($user_role == 'sieuam_doctor'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="./sieuam_dashboard">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-procedures me-1"></i>Siêu âm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-file-medical me-1"></i>Kết quả
                            </a>
                        </li>
                    <?php elseif ($user_role == 'xetnghiem_doctor'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="./xetnghiem_dashboard">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-flask me-1"></i>Xét nghiệm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-file-medical me-1"></i>Kết quả
                            </a>
                        </li>
                    <?php elseif ($user_role == 'patient'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="./patient_dashboard">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-calendar-plus me-1"></i>Đặt lịch
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-file-medical me-1"></i>Hồ sơ bệnh án
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>

                <!-- User Menu -->
                <div class="navbar-nav">
                    <!-- Notifications -->
                    <div class="nav-item dropdown me-3">
                        <a class="nav-link position-relative nav-bell" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <span id="notif-badge"
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-0" style="width: 340px;">
                            <li class="dropdown-header px-3 py-2 fw-bold">Thông báo</li>
                            <li>
                                <div id="notif-list" class="list-group list-group-flush small"
                                    style="max-height: 320px; overflow-y: auto;">
                                    <div class="p-3 text-muted">Không có thông báo</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button"
                            data-bs-toggle="dropdown">
                            <div class="user-avatar me-2">
                                <?php echo strtoupper(substr($ctx['name'] ?: 'U', 0, 1)); ?>
                            </div>
                            <div class="user-info">
                                <div>
                                    <div class="fw-bold"><?php echo $displayName; ?></div>
                                    <small class="text-muted">
                                        <?php
                                        $role_text = '';
                                        $role_class = '';
                                        switch ($user_role) {
                                            case 'admin':
                                                $role_text = 'Quản trị viên';
                                                $role_class = 'role-admin';
                                                break;
                                            case 'doctor':
                                                $role_text = 'Bác sĩ';
                                                $role_class = 'role-doctor';
                                                break;
                                            case 'xray_doctor':
                                                $role_text = 'Bác sĩ X-Quang';
                                                $role_class = 'role-doctor';
                                                break;
                                            case 'sieuam_doctor':
                                                $role_text = 'Bác sĩ Siêu âm';
                                                $role_class = 'role-sieuam';
                                                break;
                                            case 'xetnghiem_doctor':
                                                $role_text = 'Bác sĩ Xét nghiệm';
                                                $role_class = 'role-xetnghiem';
                                                break;
                                            case 'patient':
                                                $role_text = 'Bệnh nhân';
                                                $role_class = 'role-patient';
                                                break;
                                            case 'letan':
                                                $role_text = 'Lễ tân';
                                                $role_class = 'role-letan';
                                                break;
                                        }
                                        ?>
                                        <span
                                            class="role-badge <?php echo $role_class; ?>"><?php echo $role_text; ?></span>
                                    </small>
                                </div>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="./<?php echo $user_role; ?>_profile"><i
                                        class="fas fa-user me-2"></i>Hồ sơ</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Cài đặt</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="./logout"><i class="fas fa-sign-out-alt me-2"></i>Đăng
                                    xuất</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <?php echo $_SESSION['success']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo $_SESSION['error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Page Content -->
        <?php echo $content ?? ''; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('show');
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.querySelector('.navbar-toggler');

        if (window.innerWidth <= 768) {
            if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        }
    });

    // Auto-dismiss Bootstrap alerts after 3 seconds (global)
    window.addEventListener('DOMContentLoaded', function() {
        var alerts = document.querySelectorAll('.alert');
        if (!alerts.length) return;
        setTimeout(function() {
            alerts.forEach(function(el) {
                try {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                        bootstrap.Alert.getOrCreateInstance(el).close();
                    } else {
                        el.classList.remove('show');
                        setTimeout(function() {
                            el.remove();
                        }, 300);
                    }
                } catch (e) {
                    el.remove();
                }
            });
        }, 3000);
    });

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
                var d = ts ? new Date(ts) : null;
                if (!d || isNaN(d.getTime())) d = new Date();
                var dd = String(d.getDate()).padStart(2, '0');
                var mm = String(d.getMonth() + 1).padStart(2, '0');
                var yyyy = d.getFullYear();
                var hh = String(d.getHours()).padStart(2, '0');
                var mi = String(d.getMinutes()).padStart(2, '0');
                dateStr = dd + '-' + mm + '-' + yyyy + ' ' + hh + ':' + mi;
            } catch (e) {
                dateStr = '';
            }
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
            // persist
            var items = readStore();
            items.unshift({
                message: message,
                type: type || 'info',
                ts: Date.now()
            });
            writeStore(items);
        };

        // Clear badge when opening dropdown
        var bell = document.querySelector('.nav-item.dropdown.me-3 > a[data-bs-toggle="dropdown"]');
        if (bell) {
            bell.addEventListener('show.bs.dropdown', function() {
                if (notifBadge) {
                    notifBadge.textContent = '0';
                    notifBadge.classList.add('d-none');
                }
            });
        }

        // Drain queued notifications from early socket events & hydrate from storage/server
        try {
            // hydrate existing from storage
            var stored = readStore();
            if (stored && stored.length) {
                var emptyHydrate = notifList.querySelector('.text-muted');
                if (emptyHydrate) emptyHydrate.remove();
                stored.slice(0, 20).reverse().forEach(function(n) {
                    notifList.prepend(renderItem(n.message, n.type, n.ts));
                });
            }
            // hydrate from server API (ưu tiên server)
            if (currentUserId) {
                fetch('./notifications')
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(resp) {
                        if (resp && resp.success && Array.isArray(resp.data)) {
                            var serverItems = resp.data.map(function(n) {
                                return {
                                    message: n.noi_dung,
                                    type: n.loai || 'info',
                                    ts: new Date(n.ngay_tao).getTime()
                                };
                            });
                            // render trực tiếp server items và đồng bộ localStorage
                            while (notifList.firstChild) notifList.removeChild(notifList.firstChild);
                            if (!serverItems.length) {
                                var div = document.createElement('div');
                                div.className = 'p-3 text-muted';
                                div.textContent = 'Không có thông báo';
                                notifList.appendChild(div);
                            } else {
                                serverItems.slice(0, 20).forEach(function(n) {
                                    notifList.prepend(renderItem(n.message, n.type, n.ts));
                                });
                            }
                            writeStore(serverItems);
                        }
                    }).catch(function(e) {});
            }
            // then drain queue
            if (window.__notifQueue && Array.isArray(window.__notifQueue)) {
                window.__notifQueue.forEach(function(n) {
                    window.addNotification(n.message, n.type);
                });
                window.__notifQueue = [];
            }
        } catch (e) {}
    })();
    </script>

    <!-- Socket.IO Client Script -->

</body>

</html>