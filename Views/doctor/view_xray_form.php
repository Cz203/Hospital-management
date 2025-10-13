<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết phiếu chụp X-Quang</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.1/css/bootstrap.min.css" />
</head>
<body class="p-4">
    <div class="container">
        <h4 class="mb-4"><i class="fas fa-file-medical me-2"></i>Chi tiết phiếu chụp X-Quang</h4>

        <div class="card">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-6"><strong>ID phiếu:</strong> <?php echo htmlspecialchars($phieuChup['id']); ?></div>
                    <div class="col-md-6"><strong>Ngày tạo:</strong> <?php echo htmlspecialchars($phieuChup['ngay_tao']); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6"><strong>Họ tên:</strong> <?php echo htmlspecialchars($phieuChup['ho_ten'] ?? ''); ?></div>
                    <div class="col-md-3"><strong>Tuổi:</strong> <?php echo htmlspecialchars($phieuChup['tuoi'] ?? ''); ?></div>
                    <div class="col-md-3"><strong>Giới tính:</strong> <?php echo htmlspecialchars($phieuChup['gioi_tinh'] ?? ''); ?></div>
                </div>
                <div class="mb-2"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($phieuChup['dia_chi'] ?? ''); ?></div>
                <div class="mb-2"><strong>Chẩn đoán vào viện:</strong> <?php echo htmlspecialchars($phieuChup['chan_doan_vao_vien'] ?? ''); ?></div>
                <div class="mb-3">
                    <strong>Yêu cầu chụp:</strong>
                    <div class="border rounded p-2" style="min-height:60px; white-space:pre-line;"><?php echo htmlspecialchars($phieuChup['yeu_cau_chup'] ?? ''); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6"><strong>Bác sĩ điều trị:</strong> <?php echo htmlspecialchars($phieuChup['bac_si_kham'] ?? ''); ?></div>
                    <div class="col-md-6"><strong>Trạng thái:</strong> <span class="badge bg-info text-dark"><?php echo htmlspecialchars($phieuChup['trang_thai']); ?></span></div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a class="btn btn-secondary" href="javascript:window.close()"><i class="fas fa-times me-1"></i>Đóng</a>
            <a class="btn btn-primary" target="_blank" href="./print_xray_form?id=<?php echo urlencode($phieuChup['id']); ?>"><i class="fas fa-print me-1"></i>In phiếu</a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
</body>
</html>

