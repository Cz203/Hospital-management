<?php
// Notifications dropdown (shared across layouts)
?>
<div class="dropdown notifications-dropdown" style="display:flex;align-items:center;">
    <a class="nav-link position-relative nav-bel1" href="#" role="button" data-bs-toggle="dropdown"
        data-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <span id="notif-badge"
            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">0</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-start p-0 mt-3" style="width: 340px; left: auto; right: 0;">
        <li class="dropdown-header px-3 py-2 fw-bold">Thông báo</li>
        <li>
            <div id="notif-list" class="list-group list-group-flush small" style="max-height: 320px; overflow-y: auto;">
                <div class="p-3 text-muted">Không có thông báo</div>
            </div>
        </li>
    </ul>
</div>