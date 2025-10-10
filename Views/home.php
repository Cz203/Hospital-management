<?php
require_once 'Models/Doctor.php';
require_once 'Models/Specialty.php';
require_once 'Controllers/AuthController.php';

$auth = new AuthController();

$doctorModel = new Doctor();
$doctors = $doctorModel->getAll();

// Limit to 6 doctors for the home page
$displayDoctors = array_slice($doctors, 0, 6);

// Get specialties directly from chuyen_khoa with doctor counts
$specialtyModel = new Specialty();
$specialties = $specialtyModel->allWithDoctorCounts();

// Define specialty colors for consistent styling
$specialtyColors = [
    'Nội tổng quát' => ['text' => 'text-primary', 'bg' => 'primary'],
    'Ung bướu' => ['text' => 'text-danger', 'bg' => 'danger'],
    'Sản phụ khoa' => ['text' => 'text-pink', 'bg' => 'pink'],
    'Chẩn đoán hình ảnh' => ['text' => 'text-info', 'bg' => 'info'],
    'Xét nghiệm' => ['text' => 'text-warning', 'bg' => 'warning'],
    'Ngoại khoa' => ['text' => 'text-success', 'bg' => 'success'],
    'Tiêu hóa' => ['text' => 'text-orange', 'bg' => 'orange'],
    'Nội tiết' => ['text' => 'text-purple', 'bg' => 'purple'],
    'Tim mạch' => ['text' => 'text-danger', 'bg' => 'danger'],
    'Nam khoa' => ['text' => 'text-blue', 'bg' => 'blue'],
    'Cơ xương khớp' => ['text' => 'text-secondary', 'bg' => 'secondary'],
    'Truyền nhiễm' => ['text' => 'text-warning', 'bg' => 'warning'],
    'Thần kinh' => ['text' => 'text-indigo', 'bg' => 'indigo'],
    'Nhi khoa' => ['text' => 'text-info', 'bg' => 'info'],
    'Mắt' => ['text' => 'text-primary', 'bg' => 'primary'],
    'Tai mũi họng' => ['text' => 'text-success', 'bg' => 'success'],
    'Da liễu' => ['text' => 'text-warning', 'bg' => 'warning'],
    'Răng hàm mặt' => ['text' => 'text-light', 'bg' => 'light']
];

// Set page title
$page_title = 'Trang chủ';

// Include header
include 'Views/layouts/header.php';
?>

<!-- Hero Section -->
<section class="hero-section" id="home">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <div class="hero-content">
                    <h1 class="hero-title">Phòng khám đa khoa ThinhViet</h1>

                    <!-- Search Section -->
                    <div class="hero-search animate-on-scroll">
                        <div class="search-container">
                            <div class="search-box">
                                <div class="search-input-group">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" class="search-input"
                                        placeholder="Tìm kiếm bác sĩ, chuyên khoa...">
                                    <button type="button" class="search-btn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="search-suggestions" class="search-suggestions" style="display:none;">
                                <div
                                    class="suggestions-header d-flex align-items-center justify-content-between px-2 py-2">
                                    <small class="text-muted">Gợi ý</small>
                                    <button type="button" id="suggestions-clear"
                                        class="btn btn-sm btn-link text-danger p-0">Xóa tất cả</button>
                                </div>
                                <div id="search-suggestions-list"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>



<!-- Appointment Section removed per request -->

<!-- Doctors List Section -->
<section class="doctors-list-section" id="doctors">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-4 mb-4 animate-on-scroll">Danh sách bác sĩ</h2>
                <p class="lead text-muted animate-on-scroll">
                    Đội ngũ bác sĩ chuyên môn cao với nhiều năm kinh nghiệm trong các lĩnh vực khác nhau.
                    Cam kết mang đến dịch vụ chăm sóc sức khỏe tốt nhất cho bệnh nhân.
                </p>
            </div>
        </div>

        <div class="row">
            <?php if (!empty($doctors)) : ?>
            <?php $doctorsLimited = array_slice($doctors, 0, 6);
                foreach ($doctorsLimited as $doc) : ?>
            <?php
                    $spec = $doc['chuyen_khoa'] ?? '';
                    $colors = $specialtyColors[$spec] ?? ['text' => 'text-primary', 'bg' => 'primary'];
                    $textClass = $colors['text'];
                    ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="doctor-card animate-on-scroll">
                    <div class="doctor-avatar">
                        <img src="<?php echo $doc['hinh_anh']; ?>"
                            alt="Bác sĩ <?php echo htmlspecialchars($doc['ten']); ?>"
                            onerror="this.src='./assets/img/default-doctor.jpg'">
                    </div>
                    <h5><?php echo htmlspecialchars($doc['ten']); ?></h5>
                    <p class="<?php echo $textClass; ?> mb-2">
                        <i class="fas fa-stethoscope me-1"></i>
                        Chuyên khoa <?php echo htmlspecialchars($spec ?: 'Đa khoa'); ?>
                    </p>
                    <p class="text-muted small mb-3">
                        <i class="fas fa-clock me-1"></i>
                        <?php echo (int)($doc['so_nam_kinh_nghiem'] ?? 0); ?> năm kinh nghiệm
                    </p>

                </div>
            </div>
            <?php endforeach; ?>
            <?php else : ?>
            <div class="col-12">
                <div class="text-center">
                    <i class="fas fa-user-md fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Chưa có dữ liệu bác sĩ.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="row mt-4">
            <div class="col-12 text-center">
                <a class="btn btn-primary btn-lg animate-on-scroll" href="./doctor_team">
                    <i class="fas fa-users me-2"></i>Xem tất cả bác sĩ
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Specialties Section -->
<section class="doctors-section" id="chuyenkhoa">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-4 mb-4 text-white animate-on-scroll">Các Chuyên khoa</h2>
                <p class="lead text-white-50 animate-on-scroll">
                    Đội ngũ bác sĩ chuyên môn cao với nhiều năm kinh nghiệm trong các chuyên khoa đa dạng.
                    Chúng tôi cam kết mang đến dịch vụ chăm sóc sức khỏe toàn diện và chuyên nghiệp.
                </p>
            </div>
        </div>

        <div class="row">
            <?php $specialtiesLimited = array_slice($specialties ?? [], 0, 8);
            foreach ($specialtiesLimited as $sp) :
                $name = $sp['ten'];
                $count = (int)($sp['doctor_count'] ?? 0);
                $iconClass = !empty($sp['icon']) ? $sp['icon'] : 'fas fa-stethoscope';
                $desc = $sp['mo_ta'] ?? '';
                $badgeColor = 'primary';
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <a class="text-decoration-none text-reset"
                    href="./doctors_by_specialty?slug=<?php echo urlencode($sp['slug'] ?? ''); ?>">
                    <div class="specialty-card animate-on-scroll">
                        <div class="specialty-icon">
                            <i class="<?php echo htmlspecialchars($iconClass); ?>"></i>
                        </div>
                        <h5><?php echo htmlspecialchars($name); ?></h5>
                        <p><?php echo htmlspecialchars($desc); ?></p>
                        <div class="doctor-count">
                            <span class="badge bg-<?php echo $badgeColor; ?>">
                                <i class="fas fa-user-md me-1"></i><?php echo $count; ?> Bác sĩ
                            </span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="row mt-3">
            <div class="col-12 text-center">
                <a class="btn btn-primary btn-lg animate-on-scroll" href="./specialties_all">
                    <i class="fas fa-users me-2"></i>Xem tất chuyên khoa
                </a>
            </div>
        </div>
    </div>
</section>


<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="stats-card animate-on-scroll">
                    <h3 class="text-gradient">Thống kê đội ngũ y tế</h3>
                    <div class="row">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-item">
                                <div class="stat-number text-primary"><?php echo count($doctors); ?>+</div>
                                <div class="stat-label">Bác sĩ chuyên khoa</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-item">
                                <div class="stat-number text-success">
                                    <?php echo is_array($specialties) ? count($specialties) : 0; ?>+</div>
                                <div class="stat-label">Chuyên khoa</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-item">
                                <div class="stat-number text-info"><?php
                                                                    $avgExperience = 0;
                                                                    if (count($doctors) > 0) {
                                                                        $totalExp = 0;
                                                                        foreach ($doctors as $doctor) {
                                                                            $totalExp += (int)($doctor['so_nam_kinh_nghiem'] ?? 0);
                                                                        }
                                                                        $avgExperience = round($totalExp / count($doctors));
                                                                    }
                                                                    echo $avgExperience;
                                                                    ?>+</div>
                                <div class="stat-label">Năm kinh nghiệm TB</div>
                            </div>

                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-item">
                                <div class="stat-number text-warning">50,000+</div>
                                <div class="stat-label">Bệnh nhân đã điều trị</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
// Include footer
include 'Views/layouts/footer.php';
?>
<script>
// Expose specialty names to help detect exact specialty searches
window.homeSpecialties = <?php echo json_encode(array_map(function ($s) {
                                    return $s['ten'] ?? '';
                                }, $specialties ?? [])); ?>;
(function() {
    try {
        var root = document.querySelector('.hero-section') || document.querySelector('.section.hero');
        var input = root ? root.querySelector('.search-input') : null;
        var btn = root ? root.querySelector('.search-btn') : null;
        var specs = Array.isArray(window.homeSpecialties) ? window.homeSpecialties : [];
        var suggWrap = document.getElementById('search-suggestions');
        var suggList = document.getElementById('search-suggestions-list');
        var clearBtn = document.getElementById('suggestions-clear');
        var names = [];
        try {
            names = (<?php echo json_encode(array_map(function ($d) {
                                return $d['ten'] ?? '';
                            }, $doctors ?? [])); ?>) || [];
        } catch (e) {}

        function handleSearch() {
            var q = (input && input.value ? input.value : '').trim();
            if (!q) {
                window.location.href = './doctor_team';
                return;
            }
            var lower = q.toLowerCase();
            var matchedSpec = '';
            for (var i = 0; i < specs.length; i++) {
                var s = String(specs[i] || '');
                if (s.toLowerCase() === lower) {
                    matchedSpec = s;
                    break;
                }
            }
            var url = './doctor_team?q=' + encodeURIComponent(q);
            if (matchedSpec) url += '&spec=' + encodeURIComponent(matchedSpec);
            window.location.href = url;
        }

        if (btn) btn.addEventListener('click', handleSearch);
        if (input) input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleSearch();
            }
            if (e.key === 'Escape') {
                if (suggWrap) suggWrap.style.display = 'none';
            }
        });

        function renderSuggestions(items) {
            if (!suggWrap || !suggList) return;
            if (!items.length) {
                suggWrap.style.display = 'none';
                suggList.innerHTML = '';
                return;
            }
            var html = '';
            for (var i = 0; i < Math.min(items.length, 8); i++) {
                var it = items[i];
                var type = it.type === 'spec' ? 'Chuyên khoa' : 'Bác sĩ';
                var icon = it.type === 'spec' ? 'fa-stethoscope' : 'fa-user-md';
                html +=
                    '<div class="d-flex align-items-center justify-content-between py-2 px-2 suggestion-item" style="cursor:pointer;">' +
                    '<div class="d-flex align-items-center"><i class="fas ' + icon +
                    ' text-primary me-2"></i><span class="s-label">' + it.label + '</span></div>' +
                    '<div class="d-flex align-items-center gap-2"><small class="text-muted me-2">' + type +
                    '</small><button type="button" class="btn btn-sm btn-outline-danger s-remove">×</button></div>' +
                    '</div>';
            }
            suggList.innerHTML = html;
            suggWrap.style.display = 'block';
            Array.prototype.forEach.call(suggList.children, function(row, idx) {
                // click row to navigate
                row.addEventListener('click', function(e) {
                    if (e.target && e.target.classList.contains('s-remove'))
                        return; // ignore when remove button
                    var it = items[idx];
                    if (!it) return;
                    if (it.type === 'spec') {
                        window.location.href = './doctor_team?spec=' + encodeURIComponent(it.label);
                    } else {
                        window.location.href = './doctor_team?q=' + encodeURIComponent(it.label);
                    }
                });
                // remove single item
                var removeBtn = row.querySelector('.s-remove');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function(ev) {
                        ev.stopPropagation();
                        row.remove();
                        if (!suggList.children.length) {
                            suggWrap.style.display = 'none';
                        }
                    });
                }
            });
        }

        function onInput() {
            var q = (input && input.value ? input.value : '').trim().toLowerCase();
            if (!q) {
                renderSuggestions([]);
                return;
            }
            var specMatches = specs
                .filter(function(s) {
                    return String(s || '').toLowerCase().includes(q);
                })
                .map(function(s) {
                    return {
                        type: 'spec',
                        label: String(s)
                    };
                });
            var nameMatches = names
                .filter(function(n) {
                    return String(n || '').toLowerCase().includes(q);
                })
                .map(function(n) {
                    return {
                        type: 'name',
                        label: String(n)
                    };
                });
            renderSuggestions(specMatches.concat(nameMatches));
        }

        if (input) input.addEventListener('input', onInput);
        if (clearBtn) clearBtn.addEventListener('click', function() {
            if (suggWrap) suggWrap.style.display = 'none';
            if (suggList) suggList.innerHTML = '';
        });
        document.addEventListener('click', function(e) {
            if (!suggWrap) return;
            var t = e.target;
            if (t !== input && !suggWrap.contains(t)) {
                suggWrap.style.display = 'none';
            }
        });
    } catch (_) {}
})();
</script>