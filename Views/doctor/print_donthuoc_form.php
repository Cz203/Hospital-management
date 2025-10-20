<?php
require_once __DIR__ . '/../../config/database.php';

// Resolve code by code or exam_id
$code = $_GET['code'] ?? '';
$examId = $_GET['exam_id'] ?? '';

$pdo = (new Database())->getConnection();

if (empty($code) && !empty($examId)) {
    $stmt = $pdo->prepare("SELECT MaDonThuoc FROM don_thuoc WHERE id_phieu_kham_benh = ? ORDER BY NgayTao DESC LIMIT 1");
    $stmt->execute([(int)$examId]);
    $code = $stmt->fetchColumn();
}

if (empty($code)) {
    echo '<script>alert("Chưa có đơn thuốc để in");window.close();</script>';
    exit;
}

// Header
$stmtH = $pdo->prepare("SELECT dt.*, bn.ten AS ten_benh_nhan, bn.dia_chi, bn.so_dien_thoai, bs.ten AS ten_bac_si
                        FROM don_thuoc dt
                        LEFT JOIN benh_nhan bn ON bn.id = dt.MaBenhNhan
                        LEFT JOIN bac_si bs ON bs.id = dt.MaBacSi
                        WHERE dt.MaDonThuoc = ?");
$stmtH->execute([$code]);
$header = $stmtH->fetch(PDO::FETCH_ASSOC);
if (!$header) {
    echo '<script>alert("Không tìm thấy đơn thuốc");window.close();</script>';
    exit;
}

// Details
$stmtD = $pdo->prepare("SELECT ctdt.*, t.TenThuoc AS TenThuocMaster, t.HoatChatChinh AS HoatChatMaster, t.DonViTinh AS DonViTinhMaster, t.LieuDung AS LieuDungMaster
                        FROM chi_tiet_don_thuoc ctdt
                        LEFT JOIN thuoc t ON t.MaThuoc = ctdt.MaThuoc
                        WHERE ctdt.MaDonThuoc = ?");
$stmtD->execute([$code]);
$details = $stmtD->fetchAll(PDO::FETCH_ASSOC);
?>
<?php
// File view in cũ đã được yêu cầu xóa/không sử dụng. Để trống để tránh dùng nhầm.
?>

