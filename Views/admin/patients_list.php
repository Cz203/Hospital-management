<?php
// $patients, $total, $page, $perPage được truyền từ AdminController::patientsList()
?>

<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="mb-0"><i class="fas fa-user-injured me-2"></i>Danh sách Bệnh nhân</h2>
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
                    <input type="text" id="patientSearchInput" class="form-control"
                        placeholder="Tìm theo tên, email, SĐT, CCCD">
                </div>
                <div class="col-md-3">
                    <select id="genderFilter" class="form-select">
                        <option value="">Tất cả giới tính</option>
                        <option value="nam">Nam</option>
                        <option value="nữ">Nữ</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="patientsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Bệnh nhân</th>
                            <th>Giới tính</th>
                            <th>Ngày sinh</th>
                            <th>Số điện thoại</th>
                            <th>CCCD</th>
                            <th>Địa chỉ</th>
                            <th>Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($patients)): ?>
                        <?php foreach ($patients as $bn): ?>
                        <tr class="patient-row">
                            <td>
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                    style="width:40px;height:40px;">
                                    <span class="text-muted">
                                        <i class="fas fa-user"></i>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark patient-name">
                                    <?php echo htmlspecialchars($bn['ten'] ?? ''); ?></div>
                                <div class="small text-muted patient-email">
                                    <?php echo htmlspecialchars($bn['email'] ?? ''); ?></div>
                            </td>
                            <td class="patient-gender">
                                <?php echo htmlspecialchars($bn['gioi_tinh'] ?? ''); ?>
                            </td>
                            <td>
                                <?php
                                        $dob = isset($bn['ngay_sinh']) ? strtotime($bn['ngay_sinh']) : null;
                                        echo $dob ? date('d-m-Y', $dob) : '';
                                        ?>
                            </td>
                            <td class="patient-phone">
                                <?php echo htmlspecialchars($bn['so_dien_thoai'] ?? ''); ?>
                            </td>
                            <td class="patient-cccd">
                                <?php echo htmlspecialchars($bn['cccd'] ?? ''); ?>
                            </td>
                            <td class="patient-address">
                                <span class="text-truncate d-inline-block" style="max-width:220px;">
                                    <?php echo htmlspecialchars($bn['dia_chi'] ?? ''); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                        $ts = isset($bn['ngay_tao']) ? strtotime($bn['ngay_tao']) : null;
                                        echo $ts ? date('d-m-Y', $ts) : '';
                                        ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Chưa có dữ liệu bệnh nhân.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php
            $total = $total ?? 0;
            $page = $page ?? 1;
            $perPage = $perPage ?? 10;
            $totalPages = $total && $perPage ? (int)ceil($total / $perPage) : 1;
            if ($totalPages > 1) {
                renderPagination($page, $totalPages, './patients', ['per_page' => $perPage]);
            }
            ?>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('patientSearchInput');
    var genderFilter = document.getElementById('genderFilter');
    var rows = Array.prototype.slice.call(document.querySelectorAll('#patientsTable tbody tr.patient-row'));

    function normalize(str) {
        return (str || '').toString().toLowerCase();
    }

    function applyFilters() {
        var keyword = normalize(searchInput.value);
        var gender = normalize(genderFilter.value);

        rows.forEach(function(row) {
            var name = normalize(row.querySelector('.patient-name')?.textContent);
            var email = normalize(row.querySelector('.patient-email')?.textContent);
            var phone = normalize(row.querySelector('.patient-phone')?.textContent);
            var cccd = normalize(row.querySelector('.patient-cccd')?.textContent);
            var addr = normalize(row.querySelector('.patient-address')?.textContent);
            var g = normalize(row.querySelector('.patient-gender')?.textContent);

            var matchKeyword = !keyword || name.includes(keyword) || email.includes(keyword) ||
                phone.includes(keyword) || cccd.includes(keyword) || addr.includes(keyword);
            var matchGender = !gender || g === gender;

            row.style.display = (matchKeyword && matchGender) ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (genderFilter) genderFilter.addEventListener('change', applyFilters);
});
</script>