<?php
// $specialties được truyền từ AdminController::specialties()
?>

<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Quản lý Chuyên khoa</h2>
        <a href="./admin_dashboard" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Quay lại</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Thêm chuyên khoa</div>
                <div class="card-body">
                    <form method="POST" action="./specialty_create">
                        <div class="mb-3">
                            <label class="form-label">Tên chuyên khoa</label>
                            <input type="text" name="ten" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Slug (tùy chọn)</label>
                            <input type="text" name="slug" class="form-control" placeholder="vd: tim-mach">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon (tùy chọn, class FontAwesome)</label>
                            <input type="text" name="icon" class="form-control" placeholder="vd: fas fa-heartbeat">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea name="mo_ta" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Thứ tự</label>
                                <input type="number" name="thu_tu" class="form-control" value="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Trạng thái</label>
                                <select name="trang_thai" class="form-select">
                                    <option value="active">active</option>
                                    <option value="inactive">inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Thêm</button>
                            <button type="reset" class="btn btn-outline-secondary">Làm mới</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Danh sách chuyên khoa</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>

                                    <th>Tên</th>
                                    <th>Slug</th>
                                    <th>Trạng thái</th>
                                    <th style="width:160px">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($specialties)): ?>
                                <?php foreach ($specialties as $sp): ?>
                                <tr>

                                    <td class="fw-semibold"><?php echo htmlspecialchars($sp['ten']); ?></td>
                                    <td class="text-muted"><?php echo htmlspecialchars($sp['slug'] ?? ''); ?></td>
                                    <td>
                                        <span
                                            class="badge <?php echo ($sp['trang_thai'] === 'active') ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo htmlspecialchars($sp['trang_thai']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#editModal" data-id="<?php echo (int)$sp['id']; ?>"
                                            data-ten="<?php echo htmlspecialchars($sp['ten']); ?>"
                                            data-slug="<?php echo htmlspecialchars($sp['slug'] ?? ''); ?>"
                                            data-icon="<?php echo htmlspecialchars($sp['icon'] ?? ''); ?>"
                                            data-mo_ta="<?php echo htmlspecialchars($sp['mo_ta'] ?? ''); ?>"
                                            data-thu_tu="<?php echo (int)($sp['thu_tu'] ?? 0); ?>"
                                            data-trang_thai="<?php echo htmlspecialchars($sp['trang_thai'] ?? 'active'); ?>">
                                            <i class="fas fa-edit me-1"></i>Sửa
                                        </button>
                                        <form method="POST" action="./specialty_delete" class="d-inline"
                                            onsubmit="return confirm('Xóa chuyên khoa này?');">
                                            <input type="hidden" name="id" value="<?php echo (int)$sp['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i
                                                    class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Chưa có chuyên khoa.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="./specialty_update">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Cập nhật chuyên khoa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tên chuyên khoa</label>
                            <input type="text" name="ten" id="edit_ten" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="edit_slug" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Icon</label>
                            <input type="text" name="icon" id="edit_icon" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Thứ tự</label>
                            <input type="number" name="thu_tu" id="edit_thu_tu" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mô tả</label>
                            <textarea name="mo_ta" id="edit_mo_ta" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Trạng thái</label>
                            <select name="trang_thai" id="edit_trang_thai" class="form-select">
                                <option value="active">active</option>
                                <option value="inactive">inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var editModal = document.getElementById('editModal');
    if (!editModal) return;
    editModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        if (!button) return;
        var id = button.getAttribute('data-id');
        var ten = button.getAttribute('data-ten');
        var slug = button.getAttribute('data-slug');
        var icon = button.getAttribute('data-icon');
        var mo_ta = button.getAttribute('data-mo_ta');
        var thu_tu = button.getAttribute('data-thu_tu');
        var trang_thai = button.getAttribute('data-trang_thai');

        document.getElementById('edit_id').value = id || '';
        document.getElementById('edit_ten').value = ten || '';
        document.getElementById('edit_slug').value = slug || '';
        document.getElementById('edit_icon').value = icon || '';
        document.getElementById('edit_mo_ta').value = mo_ta || '';
        document.getElementById('edit_thu_tu').value = thu_tu || 0;
        document.getElementById('edit_trang_thai').value = trang_thai || 'active';
    });
});
</script>