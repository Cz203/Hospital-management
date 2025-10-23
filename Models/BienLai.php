<?php
require_once 'config/database.php';

class BienLai {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    
    /**
     * Lưu biên lai viện phí
     */
    public function saveReceipt($data) {
        try {
            $this->db->beginTransaction();
            
            // Lưu biên lai chính
            $sql = "INSERT INTO bien_lai_vien_phi 
                    (ma_bien_lai, id_phieu_kham_benh, tong_tien_co_ban, tong_quy_bhyt, tong_nguoi_benh, 
                     ngay_lap, nguoi_lap, trang_thai, ghi_chu) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['ma_bien_lai'],
                $data['id_phieu_kham_benh'],
                $data['tong_tien_co_ban'],
                $data['tong_quy_bhyt'],
                $data['tong_nguoi_benh'],
                $data['ngay_lap'],
                $data['nguoi_lap'],
                $data['trang_thai'],
                $data['ghi_chu']
            ]);
            
            if (!$result) {
                throw new Exception("Lỗi khi lưu biên lai chính");
            }
            
            $bienLaiId = $this->db->lastInsertId();
            
            // Lưu chi tiết biên lai
            if (!empty($data['chi_tiet'])) {
                foreach ($data['chi_tiet'] as $chiTiet) {
                    $this->saveReceiptDetail($bienLaiId, $chiTiet);
                }
            }
            
            $this->db->commit();
            return $bienLaiId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Error saving receipt: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Lưu chi tiết biên lai
     */
    private function saveReceiptDetail($bienLaiId, $chiTiet) {
        $sql = "INSERT INTO chi_tiet_bien_lai 
                (id_bien_lai, loai_dich_vu, ten_dich_vu, so_luong, don_gia, thanh_tien, 
                 quy_bhyt, nguoi_benh, bao_hiem, ghi_chu) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $bienLaiId,
            $chiTiet['loai_dich_vu'],
            $chiTiet['ten_dich_vu'],
            $chiTiet['so_luong'],
            $chiTiet['don_gia'],
            $chiTiet['thanh_tien'],
            $chiTiet['quy_bhyt'],
            $chiTiet['nguoi_benh'],
            $chiTiet['bao_hiem'],
            $chiTiet['ghi_chu']
        ]);
    }
    
    /**
     * Lấy biên lai theo ID
     */
    public function getById($id) {
        $sql = "SELECT bl.*, pk.ho_ten, pk.tuoi, pk.gioi_tinh, pk.dia_chi, pk.so_the_bhyt, 
                       pk.doi_tuong_bhyt, bn.ma_benh_nhan
                FROM bien_lai_vien_phi bl
                JOIN phieu_kham_benh pk ON bl.id_phieu_kham_benh = pk.id  
                JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                WHERE bl.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy chi tiết biên lai
     */
    public function getDetails($bienLaiId) {
        $sql = "SELECT * FROM chi_tiet_bien_lai WHERE id_bien_lai = ? ORDER BY id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bienLaiId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Kiểm tra biên lai đã tồn tại cho phiếu khám
     */
    public function existsForExam($examId) {
        $sql = "SELECT COUNT(*) as count FROM bien_lai_vien_phi WHERE id_phieu_kham_benh = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$examId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Lấy biên lai theo ID phiếu khám bệnh
     */
    public function getByExamId($examId) {
        $sql = "SELECT * FROM bien_lai_vien_phi WHERE id_phieu_kham_benh = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$examId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cập nhật biên lai viện phí
     */
    public function updateReceipt($data) {
        $this->db->beginTransaction();
        try {
            // 1. Cập nhật biên lai chính
            $sql = "UPDATE bien_lai_vien_phi 
                    SET tong_tien_co_ban = ?, tong_quy_bhyt = ?, tong_nguoi_benh = ?, 
                        nguoi_lap = ?, ghi_chu = ?, updated_at = NOW()
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['tong_tien_co_ban'],
                $data['tong_quy_bhyt'],
                $data['tong_nguoi_benh'],
                $data['nguoi_lap'],
                $data['ghi_chu'],
                $data['id']
            ]);

            // 2. Xóa chi tiết cũ
            $sql = "DELETE FROM chi_tiet_bien_lai WHERE id_bien_lai = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$data['id']]);

            // 3. Thêm chi tiết mới
            if (!empty($data['chi_tiet'])) {
                foreach ($data['chi_tiet'] as $chiTiet) {
                    $this->saveReceiptDetail($data['id'], $chiTiet);
                }
            }

            $this->db->commit();
            return $data['id'];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error updating receipt: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
