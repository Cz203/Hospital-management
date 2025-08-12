<?php
require_once 'Models/Doctor.php';
$doctorModel = new Doctor();
$doctors = $doctorModel->getAll();

$specialties = array_values(array_unique(array_filter(array_map(function ($d) {
    return $d['chuyen_khoa'] ?? '';
}, $doctors))));
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đội ngũ bác sĩ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    body {
        background: #f7f9fc;
    }

    .page-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        padding: 40px 0;
    }

    .doctor-card {
        border: 2px solid #eef2f7;
        transition: .2s;
    }

    .doctor-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, .08);
    }

    .doctor-avatar img {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
    }

    .badge {
        border-radius: 20px;
    }

    .filter-bar .form-select,
    .filter-bar .form-control {
        border-radius: 12px;
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="/hospital_management/home"><i class="fas fa-hospital me-2"></i>Hospital
                Management</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="/hospital_management/home">Trang chủ</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="page-header">
        <div class="container">
            <h1 class="mb-1"><i class="fas fa-user-md me-2"></i>Đội ngũ bác sĩ</h1>
            <p class="mb-0">Tìm kiếm và lọc theo chuyên khoa</p>
        </div>
    </header>

    <main class="py-4">
        <div class="container">
            <div class="row align-items-end g-3 filter-bar mb-4">
                <div class="col-md-4">
                    <label class="form-label">Chuyên khoa</label>
                    <select id="specialtyFilter" class="form-select">
                        <option value="">Tất cả</option>
                        <?php foreach ($specialties as $spec): ?>
                        <option value="<?php echo htmlspecialchars($spec); ?>"><?php echo htmlspecialchars($spec); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Tìm theo tên/điện thoại</label>
                    <input id="searchInput" type="text" class="form-control"
                        placeholder="Nhập tên bác sĩ hoặc số điện thoại...">
                </div>
                <div class="col-md-3 text-md-end">
                    <a href="/hospital_management/home" class="btn btn-outline-secondary me-2"><i
                            class="fas fa-arrow-left me-2"></i>Về trang chủ</a>
                    <a href="/hospital_management/hospital_appointment" class="btn btn-primary"><i
                            class="fas fa-calendar-check me-2"></i>Đặt lịch</a>
                </div>
            </div>

            <div class="row" id="doctorsContainer">
                <?php foreach ($doctors as $doc): ?>
                <?php
                    $avatarSeed = substr(md5($doc['email']), 0, 6);
                    $imgSrc = "https://via.placeholder.com/120x120/" . substr($avatarSeed, 0, 6) . "/ffffff?text=BS";
                    ?>
                <div class="col-lg-4 col-md-6 mb-4 doctor-item"
                    data-spec="<?php echo htmlspecialchars($doc['chuyen_khoa'] ?: ''); ?>"
                    data-name="<?php echo htmlspecialchars($doc['ten']); ?>"
                    data-phone="<?php echo htmlspecialchars($doc['so_dien_thoai'] ?: ''); ?>">
                    <div class="doctor-card p-4 bg-white rounded-3 h-100">
                        <div class="doctor-avatar mb-3 text-center">
                            <img src="<?php echo $imgSrc; ?>" alt="Bác sĩ">
                        </div>
                        <h5 class="mb-1 text-center"><?php echo htmlspecialchars($doc['ten']); ?></h5>
                        <p class="text-muted small text-center mb-2">
                            <?php echo htmlspecialchars($doc['chuyen_khoa'] ?: 'Đa khoa'); ?></p>
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <span class="badge bg-success"><?php echo (int)($doc['so_nam_kinh_nghiem'] ?? 0); ?> năm
                                KN</span>
                            <?php if (!empty($doc['so_giay_phep'])): ?>
                            <span class="badge bg-info text-dark">GPL:
                                <?php echo htmlspecialchars($doc['so_giay_phep']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="text-center">
                            <a href="/hospital_management/hospital_appointment"
                                class="btn btn-outline-primary btn-sm"><i class="fas fa-calendar-plus me-2"></i>Đặt
                                lịch</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const specSelect = document.getElementById('specialtyFilter');
    const searchInput = document.getElementById('searchInput');
    const items = Array.from(document.querySelectorAll('.doctor-item'));

    function applyFilters() {
        const spec = specSelect.value.trim().toLowerCase();
        const q = searchInput.value.trim().toLowerCase();
        items.forEach(el => {
            const elSpec = (el.dataset.spec || '').toLowerCase();
            const elName = (el.dataset.name || '').toLowerCase();
            const elPhone = (el.dataset.phone || '').toLowerCase();
            const matchSpec = !spec || elSpec.includes(spec);
            const matchQ = !q || elName.includes(q) || elPhone.includes(q);
            el.style.display = (matchSpec && matchQ) ? '' : 'none';
        });
    }

    specSelect.addEventListener('change', applyFilters);
    searchInput.addEventListener('input', applyFilters);
    </script>
</body>

</html>