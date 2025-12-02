<?php
// $doctors is provided by AdminController::doctorsList()
?>

<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="mb-0"><i class="fas fa-user-md me-2"></i>Danh sách Bác sĩ</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
                <i class="fas fa-plus me-1"></i>Thêm bác sĩ
            </button>
            <a href="./admin_dashboard" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Quay lại
                Dashboard</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control"
                        placeholder="Tìm theo tên, email, chuyên khoa">
                </div>
                <div class="col-md-3">
                    <select id="specialtyFilter" class="form-select">
                        <option value="">Tất cả chuyên khoa</option>
                        <?php
                        $specialtyNames = [];
                        foreach (($doctors ?? []) as $d) {
                            $spec = $d['chuyen_khoa'] ?? '';
                            if ($spec && !in_array($spec, $specialtyNames)) $specialtyNames[] = $spec;
                        }
                        sort($specialtyNames);
                        foreach ($specialtyNames as $s): ?>
                            <option value="<?php echo htmlspecialchars($s); ?>"><?php echo htmlspecialchars($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>


            <div class="table-responsive">
                <table class="table table-hover align-middle" id="doctorsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Bác sĩ</th>
                            <th>Chuyên khoa</th>
                            <th>Kinh nghiệm</th>
                            <th>Số điện thoại</th>
                            <th>Ngày tạo</th>
                            <th style="width: 140px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($doctors)): ?>
                            <?php foreach ($doctors as $doc): ?>
                                <tr class="doctor-row">
                                    <td>
                                        <div class="rounded-circle overflow-hidden d-flex align-items-center justify-content-center bg-light"
                                            style="width:40px;height:40px;">
                                            <?php if (!empty($doc['hinh_anh'])): ?>
                                                <img src="<?php echo htmlspecialchars($doc['hinh_anh']); ?>" alt="avatar"
                                                    style="width:40px;height:40px;object-fit:cover;">
                                            <?php else: ?>
                                                <i class="fas fa-user-md text-secondary"></i>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark doctor-name">
                                            <?php echo htmlspecialchars($doc['ten']); ?></div>
                                        <div class="small text-muted doctor-email">
                                            <?php echo htmlspecialchars($doc['email']); ?></div>
                                    </td>
                                    <td class="doctor-spec"><?php echo htmlspecialchars($doc['chuyen_khoa'] ?? ''); ?></td>
                                    <td><?php echo (int)($doc['so_nam_kinh_nghiem'] ?? 0); ?> năm</td>
                                    <td><?php echo htmlspecialchars($doc['so_dien_thoai'] ?? ''); ?></td>
                                    <td>
                                        <?php
                                        $ts = isset($doc['ngay_tao']) ? strtotime($doc['ngay_tao']) : null;
                                        echo $ts ? date('d-m-Y', $ts) : '';
                                        ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#editDoctorModal" data-id="<?php echo (int)$doc['id']; ?>"
                                                data-ten="<?php echo htmlspecialchars($doc['ten']); ?>"
                                                data-email="<?php echo htmlspecialchars($doc['email']); ?>"
                                                data-phone="<?php echo htmlspecialchars($doc['so_dien_thoai'] ?? ''); ?>"
                                                data-specid="<?php echo (int)($doc['chuyen_khoa_id'] ?? 0); ?>"
                                                data-gpl="<?php echo htmlspecialchars($doc['so_giay_phep'] ?? ''); ?>"
                                                data-exp="<?php echo (int)($doc['so_nam_kinh_nghiem'] ?? 0); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="./admin_delete_doctor"
                                                onsubmit="return confirm('Xóa bác sĩ này?');">
                                                <input type="hidden" name="csrf_token"
                                                    value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                                <input type="hidden" name="id" value="<?php echo (int)$doc['id']; ?>">
                                                <button class="btn btn-outline-danger" type="submit"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Chưa có dữ liệu bác sĩ.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php
            // Pagination controls (dựa trên biến $total, $page, $perPage)
            $total = $total ?? 0;
            $page = $page ?? 1;
            $perPage = $perPage ?? 10;
            $totalPages = $total && $perPage ? (int)ceil($total / $perPage) : 1;
            if ($totalPages > 1):
            ?>
                <nav>
                    <ul class="pagination justify-content-end">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="./doctors_list?page=<?php echo max(1, $page - 1); ?>&per_page=<?php echo $perPage; ?>">«</a>
                        </li>
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <li class="page-item <?php echo $p == $page ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="./doctors_list?page=<?php echo $p; ?>&per_page=<?php echo $perPage; ?>"><?php echo $p; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="./doctors_list?page=<?php echo min($totalPages, $page + 1); ?>&per_page=<?php echo $perPage; ?>">»</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Doctor Modal -->
<div class="modal fade" id="addDoctorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="./admin_create_doctor" id="addDoctorForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Thêm bác sĩ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Họ tên</label>
                            <input type="text" name="ten" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mật khẩu</label>
                            <input type="password" name="mat_khau" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="so_dien_thoai" id="doctor_phone" class="form-control"
                                placeholder="VD: 0912345678 hoặc 84912345678">
                            <div class="invalid-feedback" id="doctor_phone_feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Chuyên khoa</label>
                            <select name="chuyen_khoa_id" class="form-select" required>
                                <option value="">-- Chọn chuyên khoa --</option>
                                <?php foreach (($specialties ?? []) as $sp): ?>
                                    <option value="<?php echo (int)$sp['id']; ?>">
                                        <?php echo htmlspecialchars($sp['ten']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số giấy phép</label>
                            <input type="text" name="so_giay_phep" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số năm kinh nghiệm</label>
                            <input type="number" name="so_nam_kinh_nghiem" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ảnh bác sĩ (tối đa 5MB)</label>
                            <input type="file" name="hinh_anh" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Thêm bác sĩ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Doctor Modal -->
<div class="modal fade" id="editDoctorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="./admin_update_doctor">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Sửa bác sĩ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Họ tên</label>
                            <input type="text" name="ten" id="edit_ten" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="so_dien_thoai" id="edit_phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Chuyên khoa</label>
                            <select name="chuyen_khoa_id" id="edit_specid" class="form-select" required>
                                <option value="">-- Chọn chuyên khoa --</option>
                                <?php foreach (($specialties ?? []) as $sp): ?>
                                    <option value="<?php echo (int)$sp['id']; ?>">
                                        <?php echo htmlspecialchars($sp['ten']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số giấy phép</label>
                            <input type="text" name="so_giay_phep" id="edit_gpl" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số năm kinh nghiệm</label>
                            <input type="number" name="so_nam_kinh_nghiem" id="edit_exp" class="form-control" value="0"
                                min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('searchInput');
        var specialtyFilter = document.getElementById('specialtyFilter');
        var rows = Array.prototype.slice.call(document.querySelectorAll('#doctorsTable tbody tr.doctor-row'));

        function normalize(str) {
            return (str || '').toString().toLowerCase();
        }

        function applyFilters() {
            var keyword = normalize(searchInput.value);
            var spec = normalize(specialtyFilter.value);

            rows.forEach(function(row) {
                var name = normalize(row.querySelector('.doctor-name')?.textContent);
                var email = normalize(row.querySelector('.doctor-email')?.textContent);
                var dspec = normalize(row.querySelector('.doctor-spec')?.textContent);

                var matchKeyword = !keyword || name.includes(keyword) || email.includes(keyword) || dspec
                    .includes(keyword);
                var matchSpec = !spec || dspec === spec;

                row.style.display = (matchKeyword && matchSpec) ? '' : 'none';
            });
        }

        if (searchInput) searchInput.addEventListener('input', applyFilters);
        if (specialtyFilter) specialtyFilter.addEventListener('change', applyFilters);
        // Phone validation for add doctor modal
        var form = document.getElementById('addDoctorForm');
        var phoneInput = document.getElementById('doctor_phone');
        var feedback = document.getElementById('doctor_phone_feedback');

        function validatePhone(value) {
            try {
                // Reuse same logic as register page: accept 0XXXXXXXXX or 84XXXXXXXXX
                var phone = (value || '').replace(/\D/g, '');
                if (phone.startsWith('84')) phone = phone.substring(2);
                else if (phone.startsWith('0')) phone = phone.substring(1);

                var validPrefixes = [
                    '32', '33', '34', '35', '36', '37', '38', '39', '86', '96', '97', '98',
                    '81', '82', '83', '84', '85', '88', '91', '94',
                    '70', '76', '77', '78', '79', '89', '90', '93',
                    '52', '56', '58', '92',
                    '59', '99'
                ];
                if (phone.length !== 9) return {
                    ok: false,
                    msg: 'Số điện thoại phải có 10 chữ số.'
                };
                var prefix = phone.substring(0, 2);
                if (validPrefixes.indexOf(prefix) === -1) return {
                    ok: false,
                    msg: 'Số điện thoại không hợp lệ.'
                };
                return {
                    ok: true,
                    normalized: '84' + phone
                };
            } catch (e) {
                return {
                    ok: false,
                    msg: 'Số điện thoại không hợp lệ.'
                };
            }
        }

        function setInvalid(msg) {
            if (!phoneInput) return;
            phoneInput.classList.add('is-invalid');
            if (feedback) feedback.textContent = msg || 'Số điện thoại không hợp lệ.';
        }

        function clearInvalid() {
            if (!phoneInput) return;
            phoneInput.classList.remove('is-invalid');
            if (feedback) feedback.textContent = '';
        }

        if (phoneInput) {
            phoneInput.addEventListener('input', function() {
                var result = validatePhone(phoneInput.value);
                if (!result.ok) setInvalid(result.msg);
                else clearInvalid();
            });
        }

        if (form) {
            form.addEventListener('submit', function(e) {
                if (!phoneInput) return;
                var result = validatePhone(phoneInput.value);
                if (!result.ok) {
                    e.preventDefault();
                    setInvalid(result.msg);
                    phoneInput.focus();
                    return false;
                }
                // On success, replace input with normalized value (84xxxxxxxxx)
                phoneInput.value = result.normalized;
            });
        }

        // Prefill edit modal
        const editModal = document.getElementById('editDoctorModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                if (!button) return;
                document.getElementById('edit_id').value = button.getAttribute('data-id') || '';
                document.getElementById('edit_ten').value = button.getAttribute('data-ten') || '';
                document.getElementById('edit_email').value = button.getAttribute('data-email') || '';
                document.getElementById('edit_phone').value = button.getAttribute('data-phone') || '';
                document.getElementById('edit_gpl').value = button.getAttribute('data-gpl') || '';
                document.getElementById('edit_exp').value = button.getAttribute('data-exp') || 0;
                const specId = parseInt(button.getAttribute('data-specid') || 0);
                const select = document.getElementById('edit_specid');
                if (select) select.value = specId > 0 ? specId : '';
            });
            // validate phone on edit submit
            editModal.querySelector('form').addEventListener('submit', function(e) {
                var input = document.getElementById('edit_phone');
                if (!input) return;
                var result = validatePhone(input.value);
                if (!result.ok) {
                    e.preventDefault();
                    input.classList.add('is-invalid');
                    alert(result.msg);
                    input.focus();
                    return false;
                }
                // normalize to 84xxxxxxxxx for transport; server will store as 0xxxxxxxxx
                input.value = result.normalized;
            });
        }
    });
</script>