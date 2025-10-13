<?php

class XraySuggestion
{
    private $db;

    public function __construct()
    {
        require_once 'config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Lấy tất cả gợi ý X-Quang đang hoạt động
     */
    public function getAllActive()
    {
        $sql = "SELECT * FROM xray_suggestions 
                WHERE trang_thai = 1 
                ORDER BY loai_chup, thu_tu, ten_goi_y";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy gợi ý theo loại chụp
     */
    public function getByType($loaiChup)
    {
        $sql = "SELECT * FROM xray_suggestions 
                WHERE loai_chup = ? AND trang_thai = 1 
                ORDER BY thu_tu, ten_goi_y";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$loaiChup]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tìm kiếm gợi ý theo từ khóa
     */
    public function search($keyword)
    {
        $sql = "SELECT * FROM xray_suggestions 
                WHERE (ten_goi_y LIKE ? OR mo_ta LIKE ?) 
                AND trang_thai = 1 
                ORDER BY thu_tu, ten_goi_y";
        
        $searchTerm = "%{$keyword}%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy gợi ý theo ID
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM xray_suggestions WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy gợi ý theo tên
     */
    public function getByName($tenGoiY)
    {
        $sql = "SELECT * FROM xray_suggestions 
                WHERE ten_goi_y = ? AND trang_thai = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenGoiY]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Tính tổng giá tiền từ danh sách tên gợi ý
     */
    public function calculateTotalPrice($suggestionNames)
    {
        if (empty($suggestionNames)) {
            return 0;
        }

        // Tách danh sách theo dấu phẩy
        $names = array_map('trim', explode(',', $suggestionNames));
        
        if (empty($names)) {
            return 0;
        }
        
        $placeholders = str_repeat('?,', count($names) - 1) . '?';
        
        $sql = "SELECT SUM(gia_tien) as total FROM xray_suggestions 
                WHERE ten_goi_y IN ($placeholders) AND trang_thai = 1";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log('XraySuggestion calculateTotalPrice - SQL prepare failed');
            return 0;
        }
        
        if (!$stmt->execute($names)) {
            error_log('XraySuggestion calculateTotalPrice - SQL execute failed');
            return 0;
        }
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    /**
     * Lấy chi tiết giá tiền cho từng gợi ý
     */
    public function getPriceDetails($suggestionNames)
    {
        if (empty($suggestionNames)) {
            return [];
        }

        // Tách danh sách theo dấu phẩy
        $names = array_map('trim', explode(',', $suggestionNames));
        
        if (empty($names)) {
            return [];
        }
        
        $placeholders = str_repeat('?,', count($names) - 1) . '?';
        
        $sql = "SELECT ten_goi_y, gia_tien FROM xray_suggestions 
                WHERE ten_goi_y IN ($placeholders) AND trang_thai = 1 
                ORDER BY thu_tu";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log('XraySuggestion getPriceDetails - SQL prepare failed');
            return [];
        }
        
        if (!$stmt->execute($names)) {
            error_log('XraySuggestion getPriceDetails - SQL execute failed');
            return [];
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
