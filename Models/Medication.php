<?php
require_once 'config/database.php';

class Medication {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    
    /**
     * Tìm kiếm thuốc theo tên hoặc mã
     */
    public function searchMedications($keyword) {
        $sql = "SELECT * FROM thuoc 
                WHERE (TenThuoc LIKE ? OR MaThuoc LIKE ? OR HoatChatChinh LIKE ?) 
                AND TrangThai = 1 
                ORDER BY TenThuoc";
        
        $keyword = "%$keyword%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$keyword, $keyword, $keyword]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
?>
