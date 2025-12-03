<?php
// $patients, $total, $page, $perPage được truyền từ AdminController::patientsList()
?>

<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="mb-0"><i class="fas fa-user-injured me-2"></i>Danh sách Bệnh nhân</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#patientCreateModal">
                <i class="fas fa-user-plus me-1"></i> Thêm bệnh nhân
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
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($patients)): ?>
                        <?php foreach ($patients as $bn): ?>
                        <tr class="patient-row">
                            <td>
                                <div class="rounded-circle overflow-hidden d-flex align-items-center justify-content-center bg-light"
                                    style="width:40px;height:40px;">
                                    <?php
                                            $imgSrc = null;
                                            if (!empty($bn['hinh_anh'])) {
                                                // Nếu hinh_anh đã là đường dẫn uploads/ thì dùng trực tiếp
                                                if (strpos($bn['hinh_anh'], 'uploads/') === 0) {
                                                    $imgSrc = './' . $bn['hinh_anh'];
                                                } else {
                                                    // Nếu chỉ là tên file thì ghép với thư mục uploads/BN/
                                                    $imgSrc = './uploads/BN/' . $bn['hinh_anh'];
                                                }
                                            }
                                            ?>
                                    <?php if ($imgSrc): ?>
                                    <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="avatar"
                                        style="width:40px;height:40px;object-fit:cover;">
                                    <?php else: ?>
                                    <span class="text-muted">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <?php endif; ?>
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
                                        $ts = isset($bn['ngay_tao']) ? strtotime($bn['ngay_tao']) : '';
                                        echo $ts ? date('d-m-Y', $ts) : '';
                                        ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                        data-bs-toggle="modal" data-bs-target="#patientEditModal"
                                        data-id="<?php echo (int)$bn['id']; ?>"
                                        data-ten="<?php echo htmlspecialchars($bn['ten'] ?? '', ENT_QUOTES); ?>"
                                        data-email="<?php echo htmlspecialchars($bn['email'] ?? '', ENT_QUOTES); ?>"
                                        data-phone="<?php echo htmlspecialchars($bn['so_dien_thoai'] ?? '', ENT_QUOTES); ?>"
                                        data-dob="<?php echo htmlspecialchars($bn['ngay_sinh'] ?? '', ENT_QUOTES); ?>"
                                        data-gioi_tinh="<?php echo htmlspecialchars($bn['gioi_tinh'] ?? '', ENT_QUOTES); ?>"
                                        data-dia_chi="<?php echo htmlspecialchars($bn['dia_chi'] ?? '', ENT_QUOTES); ?>"
                                        data-cccd="<?php echo htmlspecialchars($bn['cccd'] ?? '', ENT_QUOTES); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="post" action="./admin_delete_patient"
                                        onsubmit="return confirm('Bạn có chắc muốn xóa bệnh nhân này?');">
                                        <input type="hidden" name="id" value="<?php echo (int)$bn['id']; ?>">
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

<!-- Modal: Thêm bệnh nhân -->
<div class="modal fade" id="patientCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm bệnh nhân</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="./admin_create_patient">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token"
                        value="<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Họ và tên<span class="text-danger">*</span></label>
                            <input type="text" name="ten" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email<span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại<span class="text-danger">*</span></label>
                            <input type="text" name="so_dien_thoai" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" name="ngay_sinh" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giới tính</label>
                            <select name="gioi_tinh" class="form-select">
                                <option value="">-- Chọn --</option>
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CCCD</label>
                            <input type="text" name="cccd" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Địa chỉ</label>
                            <textarea name="dia_chi" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mật khẩu (mặc định 1111 nếu để trống)</label>
                            <input type="text" name="mat_khau" class="form-control">
                        </div>
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

<!-- Modal: Sửa bệnh nhân -->
<div class="modal fade" id="patientEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cập nhật bệnh nhân</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="./admin_update_patient" id="patientEditForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Họ và tên<span class="text-danger">*</span></label>
                            <input type="text" name="ten" id="edit_ten" class="form-control" required>
                        </div>
                        <div class="col-md  -6">
                            <label class="form-label">Email<span class="text-danger">*</span></label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="so_dien_thoai" id="edit_phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" name="ngay_sinh" id="edit_dob" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giới tính</label>
                            <select name="gioi_tinh" id="edit_gioi_tinh" class="form-select">
                                <option value="">-- Chọn --</option>
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CCCD</label>
                            <input type="text" name="cccd" id="edit_cccd" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Địa chỉ</label>
                            <textarea name="dia_chi" id="edit_dia_chi" class="form-control" rows="2"></textarea>
                        </div>
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

    var editModal = document.getElementById('patientEditModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id') || '';
            var ten = button.getAttribute('data-ten') || '';
            var email = button.getAttribute('data-email') || '';
            var phone = button.getAttribute('data-phone') || '';
            var dob = button.getAttribute('data-dob') || '';
            var gioiTinh = button.getAttribute('data-gioi_tinh') || '';
            var diaChi = button.getAttribute('data-dia_chi') || '';
            var cccd = button.getAttribute('data-cccd') || '';

            document.getElementById('edit_id').value = id;
            document.getElementById('edit_ten').value = ten;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_phone').value = phone;
            document.getElementById('edit_dob').value = dob;
            document.getElementById('edit_gioi_tinh').value = gioiTinh;
            document.getElementById('edit_dia_chi').value = diaChi;
            document.getElementById('edit_cccd').value = cccd;
        });
    }
});
</script>