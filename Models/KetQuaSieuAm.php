<?php

class KetQuaSieuAm {
    private $db;
    private $table = 'ket_qua_sieu_am';

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Lưu kết quả siêu âm mới
     */
    public function save($data) {
        try {
            $sql = "INSERT INTO {$this->table} (id_phieu_yeu_cau_sieu_am, ket_qua_khao_sat, ket_luan, bac_si_sieu_am) 
                    VALUES (:id_phieu_yeu_cau_sieu_am, :ket_qua_khao_sat, :ket_luan, :bac_si_sieu_am)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_phieu_yeu_cau_sieu_am', $data['id_phieu_yeu_cau_sieu_am']);
            $stmt->bindParam(':ket_qua_khao_sat', $data['ket_qua_khao_sat']);
            $stmt->bindParam(':ket_luan', $data['ket_luan']);
            $stmt->bindParam(':bac_si_sieu_am', $data['bac_si_sieu_am']);
            
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error saving ket_qua_sieu_am: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cập nhật kết quả siêu âm
     */
    public function update($id, $data) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET ket_qua_khao_sat = :ket_qua_khao_sat, 
                        ket_luan = :ket_luan,
                        bac_si_sieu_am = :bac_si_sieu_am,
                        ngay_cap_nhat = CURRENT_TIMESTAMP
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':ket_qua_khao_sat', $data['ket_qua_khao_sat']);
            $stmt->bindParam(':ket_luan', $data['ket_luan']);
            $stmt->bindParam(':bac_si_sieu_am', $data['bac_si_sieu_am']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating ket_qua_sieu_am: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy kết quả siêu âm theo ID
     */
    public function getById($id) {
        try {
            $sql = "SELECT kq.*, kq.bac_si_sieu_am, pysa.id as phieu_id, pysa.yeu_cau as yeu_cau_sieu_am, pysa.chan_doan,
                           pk.ho_ten, pk.tuoi, pk.gioi_tinh, pk.dia_chi, pk.ten_bac_si,
                           bn.ma_benh_nhan
                    FROM {$this->table} kq
                    JOIN phieu_yeu_cau_sieu_am pysa ON kq.id_phieu_yeu_cau_sieu_am = pysa.id
                    JOIN phieu_kham_benh pk ON pysa.id_phieu_kham_benh = pk.id
                    JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                    WHERE kq.id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting ket_qua_sieu_am by id: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy kết quả siêu âm theo ID phiếu yêu cầu
     */
    public function getByPhieuYeuCauId($phieuId) {
        try {
            $sql = "SELECT kq.*, pysa.id as phieu_id, pysa.yeu_cau as yeu_cau_sieu_am, pysa.chan_doan,
                           pk.ho_ten, pk.tuoi, pk.gioi_tinh, pk.dia_chi, pk.chan_doan_vao_vien, pk.ten_bac_si,
                           bn.ma_benh_nhan
                    FROM {$this->table} kq
                    JOIN phieu_yeu_cau_sieu_am pysa ON kq.id_phieu_yeu_cau_sieu_am = pysa.id
                    JOIN phieu_kham_benh pk ON pysa.id_phieu_kham_benh = pk.id
                    JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                    WHERE kq.id_phieu_yeu_cau_sieu_am = :phieu_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':phieu_id', $phieuId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting ket_qua_sieu_am by phieu_id: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kiểm tra xem đã có kết quả siêu âm cho phiếu yêu cầu chưa
     */
    public function existsByPhieuYeuCauId($phieuId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE id_phieu_yeu_cau_sieu_am = :phieu_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':phieu_id', $phieuId);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Error checking ket_qua_sieu_am exists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa kết quả siêu âm
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting ket_qua_sieu_am: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy tất cả kết quả siêu âm với phân trang
     */
    public function getAll($limit = 50, $offset = 0) {
        try {
            $sql = "SELECT kq.*, pysa.id as phieu_id, pysa.yeu_cau_sieu_am,
                           pk.ho_ten, pk.tuoi, pk.gioi_tinh, pk.chan_doan_vao_vien,
                           bn.ma_benh_nhan
                    FROM {$this->table} kq
                    JOIN phieu_yeu_cau_sieu_am pysa ON kq.id_phieu_yeu_cau_sieu_am = pysa.id
                    JOIN phieu_kham_benh pk ON pysa.id_phieu_kham_benh = pk.id
                    JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                    ORDER BY kq.ngay_tao DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all ket_qua_sieu_am: " . $e->getMessage());
            return false;
        }
    }
}
?>
