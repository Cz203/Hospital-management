<?php

class ReceiptData
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Lấy dữ liệu yêu cầu xét nghiệm cho biên lai
     */
    public function getLabRequests($examId)
    {
        try {
            // Lấy tất cả yêu cầu xét nghiệm đã yêu cầu hoặc hoàn thành (để kê vào biên lai)
            $sql = "SELECT 
                        pxl.id,
                        pxl.yeu_cau,
                        pxl.trang_thai
                    FROM phieu_yeu_cau_xet_nghiem pxl
                    WHERE pxl.id_phieu_kham_benh = :exam_id
                    AND pxl.trang_thai IN ('Đã yêu cầu', 'Hoàn thành')";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':exam_id' => $examId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $processedResults = [];
            
            foreach ($results as $result) {
                // Tách các dịch vụ bằng dấu phẩy
                $services = array_map('trim', explode(',', $result['yeu_cau']));
                
                foreach ($services as $service) {
                    if (empty($service)) continue;
                    
                    // Tìm giá từ bảng suggestions cho từng dịch vụ
                    $priceSql = "SELECT gia_tien, ten_goi_y FROM xet_nghiem_suggestions 
                                WHERE TRIM(ten_goi_y) = :service_name LIMIT 1";
                    $priceStmt = $this->db->prepare($priceSql);
                    $priceStmt->execute([':service_name' => trim($service)]);
                    $priceData = $priceStmt->fetch(PDO::FETCH_ASSOC);
                    
                    $processedResults[] = [
                        'id' => $result['id'],
                        'yeu_cau' => trim($service),
                        'trang_thai' => $result['trang_thai'],
                        'gia_tien' => $priceData ? $priceData['gia_tien'] : 50000,
                        'ten_xet_nghiem' => $priceData ? $priceData['ten_goi_y'] : 'Xét nghiệm'
                    ];
                }
            }
            
            return $processedResults;
            
        } catch (PDOException $e) {
            error_log("ReceiptData getLabRequests error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy dữ liệu yêu cầu siêu âm cho biên lai
     */
    public function getUltrasoundRequests($examId)
    {
        try {
            
            // Lấy tất cả yêu cầu siêu âm đã yêu cầu hoặc hoàn thành (để kê vào biên lai)
            $sql = "SELECT 
                        pysa.id,
                        pysa.yeu_cau,
                        pysa.trang_thai
                    FROM phieu_yeu_cau_sieu_am pysa
                    WHERE pysa.id_phieu_kham_benh = :exam_id
                    AND pysa.trang_thai IN ('Đã yêu cầu', 'Hoàn thành')";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':exam_id' => $examId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $processedResults = [];
            
            foreach ($results as $result) {
                // Tách các dịch vụ bằng dấu phẩy
                $services = array_map('trim', explode(',', $result['yeu_cau']));
                
                foreach ($services as $service) {
                    if (empty($service)) continue;
                    
                    // Tìm giá từ bảng suggestions cho từng dịch vụ
                    $priceSql = "SELECT gia_tien, ten_goi_y FROM sieuam_suggestions 
                                WHERE TRIM(ten_goi_y) = :service_name LIMIT 1";
                    $priceStmt = $this->db->prepare($priceSql);
                    $priceStmt->execute([':service_name' => trim($service)]);
                    $priceData = $priceStmt->fetch(PDO::FETCH_ASSOC);
                    
                    $processedResults[] = [
                        'id' => $result['id'],
                        'yeu_cau' => trim($service),
                        'trang_thai' => $result['trang_thai'],
                        'gia_tien' => $priceData ? $priceData['gia_tien'] : 100000,
                        'ten_sieu_am' => $priceData ? $priceData['ten_goi_y'] : 'Siêu âm'
                    ];
                }
            }
            
            return $processedResults;
            
        } catch (PDOException $e) {
            error_log("ReceiptData getUltrasoundRequests error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy dữ liệu yêu cầu chụp X-Quang cho biên lai
     */
    public function getXrayRequests($examId)
    {
        try {
            // Lấy tất cả yêu cầu X-Quang đã yêu cầu hoặc hoàn thành (để kê vào biên lai)
            $sql = "SELECT 
                        pcx.id,
                        pcx.yeu_cau_chup,
                        pcx.trang_thai
                    FROM phieu_chup_xquang pcx
                    WHERE pcx.id_phieu_kham_benh = :exam_id
                    AND pcx.trang_thai IN ('Đã yêu cầu', 'Hoàn thành')";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':exam_id' => $examId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $processedResults = [];
            
            foreach ($results as $result) {
                // Tách các dịch vụ bằng dấu phẩy
                $services = array_map('trim', explode(',', $result['yeu_cau_chup']));
                
                foreach ($services as $service) {
                    if (empty($service)) continue;
                    
                    // Tìm giá từ bảng suggestions cho từng dịch vụ
                    $priceSql = "SELECT gia_tien, ten_goi_y FROM xray_suggestions 
                                WHERE TRIM(ten_goi_y) = :service_name LIMIT 1";
                    $priceStmt = $this->db->prepare($priceSql);
                    $priceStmt->execute([':service_name' => trim($service)]);
                    $priceData = $priceStmt->fetch(PDO::FETCH_ASSOC);
                    
                    $processedResults[] = [
                        'id' => $result['id'],
                        'yeu_cau_chup' => trim($service),
                        'trang_thai' => $result['trang_thai'],
                        'gia_tien' => $priceData ? $priceData['gia_tien'] : 80000,
                        'ten_xquang' => $priceData ? $priceData['ten_goi_y'] : 'X-Quang'
                    ];
                }
            }
            
            return $processedResults;
            
        } catch (PDOException $e) {
            error_log("ReceiptData getXrayRequests error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy tất cả dữ liệu yêu cầu cho biên lai
     */
    public function getMedications($examId)
    {
        try {
            // Lấy thuốc từ chi_tiet_don_thuoc dựa trên don_thuoc và id_phieu_kham_benh
            $sql = "SELECT 
                        ctdt.TenThuoc as ten_thuoc,
                        ctdt.SoLuong as so_luong,
                        ctdt.LieuDung as lieu_dung,
                        t.DonGia as don_gia,
                        t.BaoHiem as bao_hiem,
                        (t.DonGia * ctdt.SoLuong) as thanh_tien
                    FROM chi_tiet_don_thuoc ctdt
                    JOIN don_thuoc dt ON ctdt.MaDonThuoc = dt.MaDonThuoc
                    JOIN thuoc t ON ctdt.TenThuoc = t.TenThuoc
                    WHERE dt.id_phieu_kham_benh = :exam_id
                    ORDER BY ctdt.TenThuoc";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':exam_id' => $examId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $results;
            
        } catch (PDOException $e) {
            error_log("ReceiptData getMedications error: " . $e->getMessage());
            return [];
        }
    }

    public function getAllRequests($examId)
    {
        $requests = [];
        
        // Lấy yêu cầu xét nghiệm
        $labRequests = $this->getLabRequests($examId);
        foreach ($labRequests as $request) {
            $requests[] = [
                'type' => 'xet_nghiem',
                'id' => $request['id'],
                'content' => $request['yeu_cau'],
                'price' => $request['gia_tien'],
                'name' => $request['ten_xet_nghiem']
            ];
        }
        
        // Lấy yêu cầu siêu âm
        $ultrasoundRequests = $this->getUltrasoundRequests($examId);
        foreach ($ultrasoundRequests as $request) {
            $requests[] = [
                'type' => 'sieu_am',
                'id' => $request['id'],
                'content' => $request['yeu_cau'],
                'price' => $request['gia_tien'],
                'name' => $request['ten_sieu_am']
            ];
        }
        
        // Lấy yêu cầu X-Quang
        $xrayRequests = $this->getXrayRequests($examId);
        foreach ($xrayRequests as $request) {
            $requests[] = [
                'type' => 'xquang',
                'id' => $request['id'],
                'content' => $request['yeu_cau_chup'],
                'price' => $request['gia_tien'],
                'name' => $request['ten_xquang']
            ];
        }
        
        return $requests;
    }
}
