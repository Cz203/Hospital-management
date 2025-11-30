<?php
// $receptions, $total, $page, $perPage được truyền từ AdminController::receptionList()
?>

<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="mb-0"><i class="fas fa-user-tie me-2"></i>Danh sách Lễ tân</h2>
        <div>
            <a href="./admin_dashboard" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Quay lại Dashboard
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <input type="text" id="receptionSearchInput" class="form-control"
                        placeholder="Tìm theo tên, email, SĐT">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="receptionTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Lễ tân</th>
                            <th>Số điện thoại</th>
                            <th>Ngày tạo</th>
                            <th>Ngày cập nhật</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($receptions)): ?>
                            <?php foreach ($receptions as $lt): ?>
                                <tr class="reception-row">
                                    <td>
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                            style="width:40px;height:40px;">
                                            <span class="text-muted">
                                                <i class="fas fa-user"></i>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark reception-name">
                                            <?php echo htmlspecialchars($lt['ten'] ?? ''); ?></div>
                                        <div class="small text-muted reception-email">
                                            <?php echo htmlspecialchars($lt['email'] ?? ''); ?></div>
                                    </td>
                                    <td class="reception-phone">
                                        <?php echo htmlspecialchars($lt['so_dien_thoai'] ?? ''); ?>
                                    </td>
                                    <td>
                                        <?php
                                        $ts = isset($lt['ngay_tao']) ? strtotime($lt['ngay_tao']) : null;
                                        echo $ts ? date('d-m-Y', $ts) : '';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $ts2 = isset($lt['ngay_cap_nhat']) ? strtotime($lt['ngay_cap_nhat']) : null;
                                        echo $ts2 ? date('d-m-Y', $ts2) : '-';
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Chưa có dữ liệu lễ tân.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php
            require_once __DIR__ . '/../layouts/pagination_helper.php';
            $total = $total ?? 0;
            $page = $page ?? 1;
            $perPage = $perPage ?? 10;
            $totalPages = $total && $perPage ? (int)ceil($total / $perPage) : 1;
            if ($totalPages > 1) {
                renderPagination($page, $totalPages, './reception_list', ['per_page' => $perPage]);
            }
            ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('receptionSearchInput');
        var rows = Array.prototype.slice.call(document.querySelectorAll('#receptionTable tbody tr.reception-row'));

        function normalize(str) {
            return (str || '').toString().toLowerCase();
        }

        function applyFilters() {
            var keyword = normalize(searchInput.value);

            rows.forEach(function(row) {
                var name = normalize(row.querySelector('.reception-name')?.textContent);
                var email = normalize(row.querySelector('.reception-email')?.textContent);
                var phone = normalize(row.querySelector('.reception-phone')?.textContent);

                var matchKeyword = !keyword || name.includes(keyword) || email.includes(keyword) || phone
                    .includes(keyword);
                row.style.display = matchKeyword ? '' : 'none';
            });
        }

        if (searchInput) searchInput.addEventListener('input', applyFilters);
    });
</script>