<?php
require_once 'config/database.php';

class Prescription {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    
    /**
     * Lưu đơn thuốc mới
     */
    public function savePrescription($data) {
        $sql = "INSERT INTO don_thuoc (MaDonThuoc, MaBenhNhan, MaBacSi, id_phieu_kham_benh, NgayKe, ChanDoan, GhiChu, TrangThai, NgayTao) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['MaDonThuoc'],
            $data['MaBenhNhan'],
            $data['MaBacSi'],
            $data['id_phieu_kham_benh'],
            $data['NgayKe'],
            $data['ChanDoan'],
            $data['GhiChu'],
            $data['TrangThai']
        ]);
        
        return $result ? $data['MaDonThuoc'] : false;
    }

    /**
     * Cập nhật đơn thuốc hiện có theo MaDonThuoc
     */
    public function updatePrescription($data) {
        $sql = "UPDATE don_thuoc 
                SET MaBenhNhan = ?, MaBacSi = ?, id_phieu_kham_benh = ?, NgayKe = ?, ChanDoan = ?, GhiChu = ?, TrangThai = ?, NgayTao = NgayTao
                WHERE MaDonThuoc = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['MaBenhNhan'],
            $data['MaBacSi'],
            $data['id_phieu_kham_benh'],
            $data['NgayKe'],
            $data['ChanDoan'],
            $data['GhiChu'],
            $data['TrangThai'],
            $data['MaDonThuoc']
        ]);
    }

    /**
     * Xóa tất cả chi tiết theo MaDonThuoc
     */
    public function deleteMedicationDetails($maDonThuoc) {
        $sql = "DELETE FROM chi_tiet_don_thuoc WHERE MaDonThuoc = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$maDonThuoc]);
    }

    /**
     * Giảm tồn kho theo mã thuốc
     */
    public function reduceStock(string $maThuoc, int $quantity): bool {
        $sql = "UPDATE thuoc SET SoLuongTon = GREATEST(SoLuongTon - ?, 0) WHERE MaThuoc = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$quantity, $maThuoc]);
    }

    /**
     * Tăng tồn kho (phục hồi) theo mã thuốc
     */
    public function increaseStock(string $maThuoc, int $quantity): bool {
        $sql = "UPDATE thuoc SET SoLuongTon = SoLuongTon + ? WHERE MaThuoc = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$quantity, $maThuoc]);
    }
    
    /**
     * Lưu chi tiết thuốc trong đơn
     */
    public function saveMedicationDetail($data) {
        $sql = "INSERT INTO chi_tiet_don_thuoc (MaDonThuoc, MaThuoc, TenThuoc, HoatChat, SoLuong, DonViTinh, LieuDung, GhiChu) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['MaDonThuoc'],
            $data['MaThuoc'],
            $data['TenThuoc'] ?? null,
            $data['HoatChat'] ?? null,
            $data['SoLuong'],
            $data['DonViTinh'],
            $data['LieuDung'],
            $data['GhiChu']
        ]);
    }
    
    /**
     * Lấy thông tin đơn thuốc theo ID
     */
    public function getPrescriptionById($prescriptionId) {
        $sql = "SELECT dt.*, pk.ho_ten, pk.tuoi, pk.gioi_tinh, pk.dia_chi, pk.chan_doan_vao_vien, pk.ten_bac_si, bn.ma_benh_nhan, bn.ngay_sinh, bn.so_dien_thoai,
                       CASE 
                           WHEN pk.doi_tuong_bhyt = 1 THEN 'BHYT'
                           WHEN pk.doi_tuong_thu_phi = 1 THEN 'Thu phí'
                           WHEN pk.doi_tuong_mien = 1 THEN 'Miễn'
                           ELSE 'Khác'
                       END as doi_tuong,
                       pk.so_the_bhyt
                FROM don_thuoc dt
                JOIN phieu_kham_benh pk ON dt.id_phieu_kham_benh = pk.id
                JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                WHERE dt.MaDonThuoc = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prescriptionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Tính tuổi từ ngày sinh
        if ($result && $result['ngay_sinh']) {
            $birthDate = new DateTime($result['ngay_sinh']);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;
            $result['tuoi'] = $age;
        }
        
        return $result;
    }
    
    /**
     * Lấy chi tiết thuốc trong đơn
     */
    public function getMedicationDetails($prescriptionId) {
        $sql = "SELECT ctdt.*, t.TenThuoc, t.HoatChatChinh, t.DonViTinh as DonViTinhThuoc, t.LieuDung as LieuDungThuoc
                FROM chi_tiet_don_thuoc ctdt
                LEFT JOIN thuoc t ON ctdt.MaThuoc = t.MaThuoc
                WHERE ctdt.MaDonThuoc = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prescriptionId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy lịch sử đơn thuốc của bệnh nhân
     */
    public function getPatientPrescriptions($patientId) {
        $sql = "SELECT dt.*, bs.ten as ten_bac_si
                FROM don_thuoc dt
                LEFT JOIN bac_si bs ON dt.MaBacSi = bs.id
                WHERE dt.MaBenhNhan = ?
                ORDER BY dt.NgayKe DESC, dt.NgayTao DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$patientId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy đơn thuốc theo ID phiếu khám bệnh
     */
    public function getPrescriptionByExamId($examId) {
        $sql = "SELECT dt.*, pk.ho_ten, pk.tuoi, pk.gioi_tinh, pk.dia_chi, pk.chan_doan_vao_vien, pk.ten_bac_si, bn.ma_benh_nhan, bn.ngay_sinh,
                       CASE 
                           WHEN pk.doi_tuong_bhyt = 1 THEN 'BHYT'
                           WHEN pk.doi_tuong_thu_phi = 1 THEN 'Thu phí'
                           WHEN pk.doi_tuong_mien = 1 THEN 'Miễn'
                           ELSE 'Khác'
                       END as doi_tuong,
                       pk.so_the_bhyt
                FROM don_thuoc dt
                JOIN phieu_kham_benh pk ON dt.id_phieu_kham_benh = pk.id
                JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                WHERE dt.id_phieu_kham_benh = ?
                ORDER BY dt.NgayKe DESC, dt.NgayTao DESC
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$examId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Tính tuổi từ ngày sinh
        if ($result && $result['ngay_sinh']) {
            $birthDate = new DateTime($result['ngay_sinh']);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;
            $result['tuoi'] = $age;
        }
        
        return $result;
    }
    
    /**
     * Cập nhật trạng thái đơn thuốc
     */
    public function updateStatus($prescriptionId, $status) {
        $sql = "UPDATE don_thuoc SET TrangThai = ? WHERE MaDonThuoc = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $prescriptionId]);
    }
    
    /**
     * Xóa đơn thuốc
     */
    public function deletePrescription($prescriptionId) {
        // Start transaction
        $this->beginTransaction();
        
        try {
            // Delete medication details first (due to foreign key constraint)
            $sql1 = "DELETE FROM chi_tiet_don_thuoc WHERE MaDonThuoc = ?";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->execute([$prescriptionId]);
            
            // Delete prescription
            $sql2 = "DELETE FROM don_thuoc WHERE MaDonThuoc = ?";
            $stmt2 = $this->db->prepare($sql2);
            $result = $stmt2->execute([$prescriptionId]);
            
            // Commit transaction
            $this->commit();
            
            return $result;
            
        } catch (Exception $e) {
            // Rollback transaction
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Lấy danh sách đơn thuốc theo bác sĩ
     */
    public function getDoctorPrescriptions($doctorId, $dateFrom = null, $dateTo = null) {
        $sql = "SELECT dt.*, bn.ten as ten_benh_nhan, bn.so_dien_thoai
                FROM don_thuoc dt
                LEFT JOIN benh_nhan bn ON dt.MaBenhNhan = bn.id
                WHERE dt.MaBacSi = ?";
        
        $params = [$doctorId];
        
        if ($dateFrom) {
            $sql .= " AND dt.NgayKe >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND dt.NgayKe <= ?";
            $params[] = $dateTo;
        }
        
        $sql .= " ORDER BY dt.NgayKe DESC, dt.NgayTao DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Thống kê đơn thuốc
     */
    public function getPrescriptionStats($dateFrom = null, $dateTo = null) {
        $sql = "SELECT 
                    COUNT(*) as total_prescriptions,
                    COUNT(CASE WHEN TrangThai = 'Chưa lấy thuốc' THEN 1 END) as pending_prescriptions,
                    COUNT(CASE WHEN TrangThai = 'Đã lấy thuốc' THEN 1 END) as completed_prescriptions,
                    COUNT(CASE WHEN TrangThai = 'Hủy' THEN 1 END) as cancelled_prescriptions
                FROM don_thuoc";
        
        $params = [];
        
        if ($dateFrom) {
            $sql .= " WHERE NgayKe >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= ($dateFrom ? " AND" : " WHERE") . " NgayKe <= ?";
            $params[] = $dateTo;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Tìm kiếm đơn thuốc
     */
    public function searchPrescriptions($keyword, $doctorId = null) {
        $sql = "SELECT dt.*, bn.ten as ten_benh_nhan, bs.ten as ten_bac_si
                FROM don_thuoc dt
                LEFT JOIN benh_nhan bn ON dt.MaBenhNhan = bn.id
                LEFT JOIN bac_si bs ON dt.MaBacSi = bs.id
                WHERE (dt.MaDonThuoc LIKE ? OR bn.ten LIKE ? OR dt.ChanDoan LIKE ?)";
        
        $params = ["%$keyword%", "%$keyword%", "%$keyword%"];
        
        if ($doctorId) {
            $sql .= " AND dt.MaBacSi = ?";
            $params[] = $doctorId;
        }
        
        $sql .= " ORDER BY dt.NgayKe DESC, dt.NgayTao DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        $this->db->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        $this->db->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        $this->db->rollback();
    }
}
?>
