<?php
// $receptions, $total, $page, $perPage được truyền từ AdminController::receptionList()
?>

<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="mb-0"><i class="fas fa-user-tie me-2"></i>Danh sách Lễ tân</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#receptionCreateModal">
                <i class="fas fa-user-plus me-1"></i> Thêm lễ tân
            </button>
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
                            <th>Giới tính</th>
                            <th>Số điện thoại</th>
                            <th>Ngày tạo</th>
                            <th>Ngày cập nhật</th>
                            <th>Hành động</th>
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
                                    <td class="reception-gender">
                                        <?php echo htmlspecialchars($lt['gioi_tinh'] ?? ''); ?>
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
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                data-bs-toggle="modal" data-bs-target="#receptionEditModal"
                                                data-id="<?php echo (int)$lt['id']; ?>"
                                                data-ten="<?php echo htmlspecialchars($lt['ten'] ?? '', ENT_QUOTES); ?>"
                                                data-email="<?php echo htmlspecialchars($lt['email'] ?? '', ENT_QUOTES); ?>"
                                                data-phone="<?php echo htmlspecialchars($lt['so_dien_thoai'] ?? '', ENT_QUOTES); ?>"
                                                data-gioi_tinh="<?php echo htmlspecialchars($lt['gioi_tinh'] ?? '', ENT_QUOTES); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="post" action="./admin_delete_reception"
                                                onsubmit="return confirm('Bạn có chắc muốn xóa lễ tân này?');">
                                                <input type="hidden" name="id" value="<?php echo (int)$lt['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Chưa có dữ liệu lễ tân.</td>
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

<!-- Modal: Thêm lễ tân -->
<div class="modal fade" id="receptionCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm lễ tân</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="./admin_create_reception">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Họ và tên<span class="text-danger">*</span></label>
                        <input type="text" name="ten" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email<span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại<span class="text-danger">*</span></label>
                        <input type="text" name="so_dien_thoai" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Giới tính</label>
                        <select name="gioi_tinh" class="form-select">
                            <option value="">-- Chọn --</option>
                            <option value="Nam">Nam</option>
                            <option value="Nữ">Nữ</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu (mặc định 1111 nếu để trống)</label>
                        <input type="text" name="mat_khau" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Sửa lễ tân -->
<div class="modal fade" id="receptionEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cập nhật lễ tân</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="./admin_update_reception" id="receptionEditForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="rec_edit_id">
                    <div class="mb-3">
                        <label class="form-label">Họ và tên<span class="text-danger">*</span></label>
                        <input type="text" name="ten" id="rec_edit_ten" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email<span class="text-danger">*</span></label>
                        <input type="email" name="email" id="rec_edit_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" name="so_dien_thoai" id="rec_edit_phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Giới tính</label>
                        <select name="gioi_tinh" id="rec_edit_gioi_tinh" class="form-select">
                            <option value="">-- Chọn --</option>
                            <option value="Nam">Nam</option>
                            <option value="Nữ">Nữ</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
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

        var editModal = document.getElementById('receptionEditModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                if (!button) return;
                var id = button.getAttribute('data-id') || '';
                var ten = button.getAttribute('data-ten') || '';
                var email = button.getAttribute('data-email') || '';
                var phone = button.getAttribute('data-phone') || '';
                var gioiTinh = button.getAttribute('data-gioi_tinh') || '';

                document.getElementById('rec_edit_id').value = id;
                document.getElementById('rec_edit_ten').value = ten;
                document.getElementById('rec_edit_email').value = email;
                document.getElementById('rec_edit_phone').value = phone;
                document.getElementById('rec_edit_gioi_tinh').value = gioiTinh;
            });
        }
    });
</script>