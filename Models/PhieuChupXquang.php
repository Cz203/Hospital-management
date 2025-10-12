<?php

class PhieuChupXquang
{
    private $db;

    public function __construct()
    {
        require_once 'config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Lưu phiếu chụp X-Quang
     */
    public function save($data)
    {
        $sql = "INSERT INTO phieu_chup_xquang (
            id_phieu_kham_benh, so_dien_thoai, quan, yeu_cau_chup, 
            bac_si_kham, chan_doan_vao_vien, trang_thai
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log('PhieuChupXquang save - SQL prepare failed');
            return false;
        }

        $trangThai = 'Đã yêu cầu';
        
        $result = $stmt->execute([
            $data['id_phieu_kham_benh'],
            $data['so_dien_thoai'],
            $data['quan'],
            $data['yeu_cau_chup'],
            $data['bac_si_kham'],
            $data['chan_doan_vao_vien'] ?? '',
            $trangThai
        ]);

        if (!$result) {
            error_log('PhieuChupXquang save - SQL execute failed');
            return false;
        }

        return $this->db->lastInsertId();
    }

    /**
     * Lấy thông tin phiếu chụp X-Quang theo ID
     */
    public function getById($id)
    {
        $sql = "SELECT 
            px.*,
            pk.ho_ten, pk.tuoi, pk.dia_chi, pk.nam_sinh,
            COALESCE(px.chan_doan_vao_vien, pk.chan_doan_vao_vien) as chan_doan_vao_vien,
            pk.ten_bac_si as ten_bac_si,
            bs.ten as bac_si_chi_dinh,
            ck.ten as khoa_chi_dinh,
            bn.ma_benh_nhan,
            COALESCE(bn.gioi_tinh, pk.gioi_tinh) as gioi_tinh,
            CASE 
                WHEN pk.doi_tuong_bhyt = 1 THEN 'BHYT'
                WHEN pk.doi_tuong_thu_phi = 1 THEN 'Thu phí'
                WHEN pk.doi_tuong_mien = 1 THEN 'Miễn'
                ELSE 'Khác'
            END as doi_tuong,
            pk.so_the_bhyt
            FROM phieu_chup_xquang px
            JOIN phieu_kham_benh pk ON px.id_phieu_kham_benh = pk.id
            JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
            LEFT JOIN bac_si bs ON pk.bac_si_id = bs.id
            LEFT JOIN chuyen_khoa ck ON bs.chuyen_khoa_id = ck.id
            WHERE px.id = ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log('PhieuChupXquang getById - SQL prepare failed');
            return null;
        }

        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cập nhật trạng thái phiếu
     */
    public function updateStatus($id, $trangThai)
    {
        $sql = "UPDATE phieu_chup_xquang SET trang_thai = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log('PhieuChupXquang updateStatus - SQL prepare failed');
            return false;
        }

        return $stmt->execute([$trangThai, $id]);
    }

    /**
     * Lấy danh sách phiếu chụp X-Quang
     */
    public function getAll($limit = 50, $offset = 0)
    {
        $sql = "SELECT 
            px.*,
            bn.ho_ten, bn.tuoi, bn.gioi_tinh,
            bs.ho_ten as ten_bac_si
            FROM phieu_chup_xquang px
            JOIN phieu_kham_benh pk ON px.id_phieu_kham_benh = pk.id
            JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
            JOIN bac_si bs ON pk.bac_si_id = bs.id
            ORDER BY px.ngay_tao DESC
            LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log('PhieuChupXquang getAll - SQL prepare failed');
            return [];
        }

        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy phiếu chụp X-Quang theo ID phiếu khám bệnh
     */
    public function getByExamId($examId)
    {
        $sql = "SELECT 
            px.*,
            pk.ho_ten, pk.tuoi, pk.dia_chi, pk.nam_sinh,
            COALESCE(px.chan_doan_vao_vien, pk.chan_doan_vao_vien) as chan_doan_vao_vien,
            pk.ten_bac_si as ten_bac_si,
            bs.ten as bac_si_chi_dinh,
            ck.ten as khoa_chi_dinh,
            bn.ma_benh_nhan,
            COALESCE(bn.gioi_tinh, pk.gioi_tinh) as gioi_tinh,
            CASE 
                WHEN pk.doi_tuong_bhyt = 1 THEN 'BHYT'
                WHEN pk.doi_tuong_thu_phi = 1 THEN 'Thu phí'
                WHEN pk.doi_tuong_mien = 1 THEN 'Miễn'
                ELSE 'Khác'
            END as doi_tuong,
            pk.so_the_bhyt
            FROM phieu_chup_xquang px
            JOIN phieu_kham_benh pk ON px.id_phieu_kham_benh = pk.id
            JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
            LEFT JOIN bac_si bs ON pk.bac_si_id = bs.id
            LEFT JOIN chuyen_khoa ck ON bs.chuyen_khoa_id = ck.id
            WHERE px.id_phieu_kham_benh = ? ORDER BY px.id DESC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log('PhieuChupXquang getByExamId - SQL prepare failed');
            return null;
        }

        $stmt->execute([$examId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách phiếu chụp có trạng thái "Đã yêu cầu"
     */
    public function getRequested($limit = 50, $offset = 0, $date = null, $keyword = null)
    {
        $whereDate = $date ? " AND DATE(px.ngay_tao) = ?" : "";
        $whereKeyword = '';
        if ($keyword !== null && $keyword !== '') {
            // Search by patient code (ma_benh_nhan)
            $whereKeyword = " AND bn.ma_benh_nhan LIKE ?";
        }
        $sql = "SELECT px.id, px.id_phieu_kham_benh, px.yeu_cau_chup, px.trang_thai, px.ngay_tao,
                       pk.ho_ten, pk.tuoi, COALESCE(bn.gioi_tinh, pk.gioi_tinh) as gioi_tinh, bn.ma_benh_nhan
                FROM phieu_chup_xquang px
                JOIN phieu_kham_benh pk ON px.id_phieu_kham_benh = pk.id
                JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                WHERE px.trang_thai = 'Đã yêu cầu'" . $whereDate . $whereKeyword . "
                ORDER BY px.ngay_tao DESC, px.id DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log('PhieuChupXquang getRequested - SQL prepare failed');
            return [];
        }

        $bindIndex = 1;
        if ($date) {
            $stmt->bindValue($bindIndex++, $date, PDO::PARAM_STR);
        }
        if ($keyword !== null && $keyword !== '') {
            $stmt->bindValue($bindIndex++, "%" . $keyword . "%", PDO::PARAM_STR);
        }
        $stmt->bindValue($bindIndex++, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue($bindIndex++, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cập nhật phiếu chụp X-Quang
     */
    public function update($id, $data)
    {
        $sql = "UPDATE phieu_chup_xquang SET 
            so_dien_thoai = ?, quan = ?, yeu_cau_chup = ?, 
            bac_si_kham = ?, chan_doan_vao_vien = ?, ngay_cap_nhat = CURRENT_TIMESTAMP
            WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log('PhieuChupXquang update - SQL prepare failed');
            return false;
        }

        $result = $stmt->execute([
            $data['so_dien_thoai'],
            $data['quan'],
            $data['yeu_cau_chup'],
            $data['bac_si_kham'],
            $data['chan_doan_vao_vien'] ?? '',
            $id
        ]);

        if (!$result) {
            error_log('PhieuChupXquang update - SQL execute failed');
            return false;
        }

        return $id; // Trả về ID của bản ghi đã cập nhật
    }

    /**
     * Kiểm tra yêu cầu chụp X-Quang có hợp lệ không
     */
    public function validateXrayRequests($yeuCauChup) {
        if (empty($yeuCauChup)) {
            return ['valid' => false, 'message' => 'Vui lòng nhập yêu cầu chụp X-Quang'];
        }

        // Tách các yêu cầu theo dấu phẩy và loại bỏ khoảng trắng
        $requests = array_filter(array_map('trim', explode(',', $yeuCauChup)), function($item) {
            return !empty($item);
        });
        
        // Nếu không có yêu cầu hợp lệ sau khi filter
        if (empty($requests)) {
            return ['valid' => false, 'message' => 'Vui lòng nhập yêu cầu chụp X-Quang'];
        }
        
        $validRequests = [];
        $invalidRequests = [];

        try {
            // Lấy tất cả dịch vụ X-Quang từ bảng suggestions
            $sql = "SELECT ten_goi_y FROM xray_suggestions WHERE trang_thai = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $availableServices = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Kiểm tra từng yêu cầu
            foreach ($requests as $request) {
                if (in_array($request, $availableServices)) {
                    $validRequests[] = $request;
                } else {
                    $invalidRequests[] = $request;
                }
            }

            if (!empty($invalidRequests)) {
                return [
                    'valid' => false, 
                    'message' => 'Dịch vụ không tồn tại: ' . implode(', ', $invalidRequests) . '. Vui lòng chọn từ danh sách gợi ý.'
                ];
            }

            return [
                'valid' => true, 
                'validRequests' => $validRequests
            ];

        } catch (PDOException $e) {
            error_log("PhieuChupXquang validateXrayRequests error: " . $e->getMessage());
            return ['valid' => false, 'message' => 'Lỗi kiểm tra dịch vụ'];
        }
    }
}
