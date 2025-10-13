<?php

class SieuAmHinhAnh {
    private $db;
    private $table = 'sieu_am_hinh_anh';

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Lưu thông tin hình ảnh siêu âm
     */
    public function save($data) {
        try {
            $sql = "INSERT INTO {$this->table} (id_ket_qua_sieu_am, ten_file, duong_dan, kich_thuoc, loai_file) 
                    VALUES (:id_ket_qua_sieu_am, :ten_file, :duong_dan, :kich_thuoc, :loai_file)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_ket_qua_sieu_am', $data['id_ket_qua_sieu_am']);
            $stmt->bindParam(':ten_file', $data['ten_file']);
            $stmt->bindParam(':duong_dan', $data['duong_dan']);
            $stmt->bindParam(':kich_thuoc', $data['kich_thuoc']);
            $stmt->bindParam(':loai_file', $data['loai_file']);
            
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error saving sieu_am_hinh_anh: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lưu nhiều hình ảnh cùng lúc
     */
    public function saveMultiple($ketQuaId, $imageDataArray) {
        try {
            $this->db->beginTransaction();
            
            $sql = "INSERT INTO {$this->table} (id_ket_qua_sieu_am, ten_file, duong_dan, kich_thuoc, loai_file) 
                    VALUES (:id_ket_qua_sieu_am, :ten_file, :duong_dan, :kich_thuoc, :loai_file)";
            
            $stmt = $this->db->prepare($sql);
            $savedIds = [];
            
            foreach ($imageDataArray as $data) {
                $stmt->bindParam(':id_ket_qua_sieu_am', $ketQuaId);
                $stmt->bindParam(':ten_file', $data['ten_file']);
                $stmt->bindParam(':duong_dan', $data['duong_dan']);
                $stmt->bindParam(':kich_thuoc', $data['kich_thuoc']);
                $stmt->bindParam(':loai_file', $data['loai_file']);
                
                if ($stmt->execute()) {
                    $savedIds[] = $this->db->lastInsertId();
                }
            }
            
            $this->db->commit();
            return $savedIds;
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Error saving multiple sieu_am_hinh_anh: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy tất cả hình ảnh theo ID kết quả siêu âm
     */
    public function getByKetQuaId($ketQuaId) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id_ket_qua_sieu_am = :ket_qua_id ORDER BY ngay_tao ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ket_qua_id', $ketQuaId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting sieu_am_hinh_anh by ket_qua_id: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy hình ảnh theo ID
     */
    public function getById($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting sieu_am_hinh_anh by id: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa hình ảnh
     */
    public function delete($id) {
        try {
            // Lấy thông tin file trước khi xóa
            $image = $this->getById($id);
            
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                // Xóa file vật lý nếu tồn tại
                if ($image && file_exists($image['duong_dan'])) {
                    unlink($image['duong_dan']);
                }
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error deleting sieu_am_hinh_anh: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa tất cả hình ảnh của một kết quả siêu âm
     */
    public function deleteByKetQuaId($ketQuaId) {
        try {
            // Lấy danh sách file trước khi xóa
            $images = $this->getByKetQuaId($ketQuaId);
            
            $sql = "DELETE FROM {$this->table} WHERE id_ket_qua_sieu_am = :ket_qua_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ket_qua_id', $ketQuaId);
            
            if ($stmt->execute()) {
                // Xóa các file vật lý
                foreach ($images as $image) {
                    if (file_exists($image['duong_dan'])) {
                        unlink($image['duong_dan']);
                    }
                }
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error deleting sieu_am_hinh_anh by ket_qua_id: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cập nhật thông tin hình ảnh
     */
    public function update($id, $data) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET ten_file = :ten_file, 
                        kich_thuoc = :kich_thuoc, 
                        loai_file = :loai_file
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':ten_file', $data['ten_file']);
            $stmt->bindParam(':kich_thuoc', $data['kich_thuoc']);
            $stmt->bindParam(':loai_file', $data['loai_file']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating sieu_am_hinh_anh: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Đếm số lượng hình ảnh theo kết quả siêu âm
     */
    public function countByKetQuaId($ketQuaId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE id_ket_qua_sieu_am = :ket_qua_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ket_qua_id', $ketQuaId);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Error counting sieu_am_hinh_anh: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy tổng kích thước file theo kết quả siêu âm
     */
    public function getTotalSizeByKetQuaId($ketQuaId) {
        try {
            $sql = "SELECT SUM(kich_thuoc) as total_size FROM {$this->table} WHERE id_ket_qua_sieu_am = :ket_qua_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ket_qua_id', $ketQuaId);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_size'] ?: 0;
        } catch (PDOException $e) {
            error_log("Error getting total size sieu_am_hinh_anh: " . $e->getMessage());
            return 0;
        }
    }
}
?>
