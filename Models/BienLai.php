<?php
require_once 'config/database.php';

class BienLai
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    /**
     * Lưu biên lai viện phí
     */
    public function saveReceipt($data)
    {
        try {
            $this->db->beginTransaction();

            // Lưu biên lai chính
            $sql = "INSERT INTO bien_lai_vien_phi 
                    (ma_bien_lai, id_phieu_kham_benh, id_le_tan, id_bac_si, tong_tien_co_ban, tong_quy_bhyt, tong_nguoi_benh, 
                     ngay_lap, nguoi_lap, trang_thai, ghi_chu) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['ma_bien_lai'],
                $data['id_phieu_kham_benh'],
                $data['id_le_tan'] ?? null,
                $data['id_bac_si'] ?? null,
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
    private function saveReceiptDetail($bienLaiId, $chiTiet)
    {
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
    public function getById($id)
    {
        $sql = "SELECT bl.*, pk.ho_ten, pk.tuoi, pk.gioi_tinh, pk.dia_chi, pk.so_the_bhyt, 
                       pk.doi_tuong_bhyt, pk.benh_nhan_id, bn.id AS benh_nhan_id_thuc_te, bn.ma_benh_nhan, bs.ten as ten_bac_si
                FROM bien_lai_vien_phi bl
                JOIN phieu_kham_benh pk ON bl.id_phieu_kham_benh = pk.id  
                JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                LEFT JOIN bac_si bs ON pk.bac_si_id = bs.id
                WHERE bl.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy biên lai theo mã biên lai
     */
    public function getByMaBienLai($maBienLai)
    {
        $sql = "SELECT bl.*, pk.ho_ten, pk.tuoi, pk.gioi_tinh, pk.dia_chi, pk.so_the_bhyt, 
                       pk.doi_tuong_bhyt, bn.ma_benh_nhan, bs.ten as ten_bac_si
                FROM bien_lai_vien_phi bl
                JOIN phieu_kham_benh pk ON bl.id_phieu_kham_benh = pk.id  
                JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                LEFT JOIN bac_si bs ON pk.bac_si_id = bs.id
                WHERE bl.ma_bien_lai = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$maBienLai]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi tiết biên lai
     */
    public function getDetails($bienLaiId)
    {
        $sql = "SELECT * FROM chi_tiet_bien_lai WHERE id_bien_lai = ? ORDER BY id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bienLaiId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Kiểm tra biên lai đã tồn tại cho phiếu khám
     */
    public function existsForExam($examId)
    {
        $sql = "SELECT COUNT(*) as count FROM bien_lai_vien_phi WHERE id_phieu_kham_benh = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$examId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Lấy biên lai theo ID phiếu khám bệnh
     */
    public function getByExamId($examId)
    {
        $sql = "SELECT * FROM bien_lai_vien_phi WHERE id_phieu_kham_benh = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$examId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cập nhật biên lai viện phí
     */
    public function updateReceipt($data)
    {
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

    /**
     * Lấy danh sách biên lai chưa thanh toán
     */
    public function getUnpaidReceipts($limit = 50, $offset = 0, $date = null)
    {
        $sql = "SELECT bl.*, pk.ho_ten, pk.tuoi, pk.gioi_tinh, pk.dia_chi, pk.so_the_bhyt, 
                       pk.doi_tuong_bhyt, bn.ma_benh_nhan, bs.ten as ten_bac_si,
                       lh.ngay_hen, lh.gio_hen
                FROM bien_lai_vien_phi bl
                JOIN phieu_kham_benh pk ON bl.id_phieu_kham_benh = pk.id  
                JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                LEFT JOIN bac_si bs ON pk.bac_si_id = bs.id
                LEFT JOIN lich_hen lh ON pk.id_lich_hen = lh.id
                WHERE bl.trang_thai = 'Chưa thanh toán'";

        if ($date) {
            $sql .= " AND DATE(bl.created_at) = ?";
        }

        $sql .= " ORDER BY bl.ngay_lap DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $this->db->prepare($sql);
        if ($date) {
            $stmt->execute([$date]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Đếm tổng số biên lai chưa thanh toán
     */
    public function countUnpaidReceipts()
    {
        $sql = "SELECT COUNT(*) as total FROM bien_lai_vien_phi WHERE trang_thai = 'Chưa thanh toán'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Lấy tất cả biên lai (cho thống kê)
     */
    public function getAllReceipts($limit = 50, $offset = 0, $date = null)
    {
        $sql = "SELECT bl.*, pk.ho_ten, pk.tuoi, pk.gioi_tinh, pk.dia_chi, pk.so_the_bhyt, 
                       pk.doi_tuong_bhyt, bn.ma_benh_nhan, bs.ten as ten_bac_si,
                       lh.ngay_hen, lh.gio_hen
                FROM bien_lai_vien_phi bl
                JOIN phieu_kham_benh pk ON bl.id_phieu_kham_benh = pk.id  
                JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                LEFT JOIN bac_si bs ON pk.bac_si_id = bs.id
                LEFT JOIN lich_hen lh ON pk.id_lich_hen = lh.id";

        if ($date) {
            $sql .= " WHERE DATE(bl.created_at) = ?";
        }

        $sql .= " ORDER BY bl.ngay_lap DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $this->db->prepare($sql);
        if ($date) {
            $stmt->execute([$date]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Đếm tổng số biên lai
     */
    public function countAllReceipts()
    {
        $sql = "SELECT COUNT(*) as total FROM bien_lai_vien_phi";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Lấy danh sách biên lai theo bệnh nhân (patient portal)
     */
    public function getReceiptsByPatient($patientId, $limit = 15, $offset = 0, $date = null, $code = null)
    {
        $params = [$patientId];
        $sql = "SELECT bl.*, 
                       lh.ngay_hen, lh.gio_hen,
                       DATE_FORMAT(lh.ngay_hen, '%d/%m/%Y') AS ngay_kham_formatted,
                       TIME_FORMAT(lh.gio_hen, '%H:%i') AS gio_kham_formatted
                FROM bien_lai_vien_phi bl
                JOIN phieu_kham_benh pk ON bl.id_phieu_kham_benh = pk.id
                JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                LEFT JOIN lich_hen lh ON pk.id_lich_hen = lh.id
                WHERE bn.id = ?";
        if (!empty($date)) {
            $sql .= " AND (DATE(bl.ngay_lap) = ? OR (lh.ngay_hen IS NOT NULL AND DATE(lh.ngay_hen) = ?))";
            $params[] = $date;
            $params[] = $date;
        }
        if (!empty($code)) {
            $sql .= " AND bl.ma_bien_lai LIKE ?";
            $params[] = '%' . $code . '%';
        }
        $sql .= " ORDER BY bl.ngay_lap DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Đếm tổng số biên lai theo bệnh nhân (patient portal)
     */
    public function countReceiptsByPatient($patientId, $date = null, $code = null)
    {
        $params = [$patientId];
        $sql = "SELECT COUNT(*) as total
                FROM bien_lai_vien_phi bl
                JOIN phieu_kham_benh pk ON bl.id_phieu_kham_benh = pk.id
                JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                LEFT JOIN lich_hen lh ON pk.id_lich_hen = lh.id
                WHERE bn.id = ?";
        if (!empty($date)) {
            $sql .= " AND (DATE(bl.ngay_lap) = ? OR (lh.ngay_hen IS NOT NULL AND DATE(lh.ngay_hen) = ?))";
            $params[] = $date;
            $params[] = $date;
        }
        if (!empty($code)) {
            $sql .= " AND bl.ma_bien_lai LIKE ?";
            $params[] = '%' . $code . '%';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Cập nhật trạng thái thanh toán
     */
    public function updatePaymentStatus($id, $status, $paymentMethod = null, $paymentNote = null, $idLeTan = null)
    {
        // Cập nhật trạng thái và id_le_tan (nếu có)
        $sql = "UPDATE bien_lai_vien_phi SET trang_thai = ?";
        $params = [$status];

        // Nếu có id_le_tan, cập nhật luôn
        if ($idLeTan !== null) {
            $sql .= ", id_le_tan = ?";
            $params[] = $idLeTan;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($params);

        // Log the payment details for reference (since we can't store them in DB)
        if ($paymentMethod && $paymentNote) {
            error_log("Payment processed - Receipt ID: $id, Method: $paymentMethod, Note: $paymentNote, Status: $status, LeTan ID: " . ($idLeTan ?? 'N/A'));
        }

        return $result;
    }

    /**
     * Tìm kiếm biên lai theo mã biên lai hoặc tên bệnh nhân
     */
    public function searchReceipts($keyword, $status = 'Chưa thanh toán', $limit = 50, $date = null)
    {
        $sql = "SELECT bl.*, pk.ho_ten, pk.tuoi, pk.gioi_tinh, pk.dia_chi, pk.so_the_bhyt, 
                       pk.doi_tuong_bhyt, bn.ma_benh_nhan, bs.ten as ten_bac_si,
                       lh.ngay_hen, lh.gio_hen
                FROM bien_lai_vien_phi bl
                JOIN phieu_kham_benh pk ON bl.id_phieu_kham_benh = pk.id  
                JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                LEFT JOIN bac_si bs ON pk.bac_si_id = bs.id
                LEFT JOIN lich_hen lh ON pk.id_lich_hen = lh.id
                WHERE bl.trang_thai = ? 
                AND (bl.ma_bien_lai LIKE ? OR pk.ho_ten LIKE ? OR bn.ma_benh_nhan LIKE ?)";

        if ($date) {
            $sql .= " AND DATE(bl.created_at) = ?";
        }

        $sql .= " ORDER BY bl.ngay_lap DESC LIMIT " . (int)$limit;

        $searchTerm = "%{$keyword}%";
        $stmt = $this->db->prepare($sql);

        if ($date) {
            $stmt->execute([$status, $searchTerm, $searchTerm, $searchTerm, $date]);
        } else {
            $stmt->execute([$status, $searchTerm, $searchTerm, $searchTerm]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
