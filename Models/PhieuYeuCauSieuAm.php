<?php
class PhieuYeuCauSieuAm {
    private $db;
    private $table = 'phieu_yeu_cau_sieu_am';

    public function __construct() {
        require_once 'config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Lưu phiếu yêu cầu siêu âm mới
     */
    public function save($data) {
        // Validate required fields
        if (empty($data['id_phieu_kham_benh']) || empty($data['ho_ten'])) {
            error_log("PhieuYeuCauSieuAm save: Missing required fields");
            return false;
        }
        
        try {
            $sql = "INSERT INTO {$this->table} 
                    (id_phieu_kham_benh, so_ho_so, ho_ten, gioi_tinh, doi_tuong, 
                     so_the_bhyt, phong_kham, chan_doan, yeu_cau, bac_si_kham, thoi_gian_yeu_cau, trang_thai) 
                    VALUES (:id_phieu_kham_benh, :so_ho_so, :ho_ten, :gioi_tinh, :doi_tuong, 
                            :so_the_bhyt, :phong_kham, :chan_doan, :yeu_cau, :bac_si_kham, :thoi_gian_yeu_cau, :trang_thai)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id_phieu_kham_benh' => $data['id_phieu_kham_benh'],
                ':so_ho_so' => $data['so_ho_so'],
                ':ho_ten' => $data['ho_ten'],
                ':gioi_tinh' => $data['gioi_tinh'],
                ':doi_tuong' => $data['doi_tuong'],
                ':so_the_bhyt' => $data['so_the_bhyt'],
                ':phong_kham' => $data['phong_kham'],
                ':chan_doan' => $data['chan_doan'],
                ':yeu_cau' => $data['yeu_cau'],
                ':bac_si_kham' => $data['bac_si_kham'],
                ':thoi_gian_yeu_cau' => $data['thoi_gian_yeu_cau'],
                ':trang_thai' => $data['trang_thai'] ?? 'Đã yêu cầu'
            ]);
        } catch (PDOException $e) {
            error_log("PhieuYeuCauSieuAm save error: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("PhieuYeuCauSieuAm save general error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cập nhật phiếu yêu cầu siêu âm
     */
    public function update($id, $data) {
        // Validate required fields
        if (empty($id) || empty($data['ho_ten'])) {
            error_log("PhieuYeuCauSieuAm update: Missing required fields");
            return false;
        }
        
        try {
            $sql = "UPDATE {$this->table} SET 
                    so_ho_so = :so_ho_so, ho_ten = :ho_ten, gioi_tinh = :gioi_tinh, 
                    doi_tuong = :doi_tuong, so_the_bhyt = :so_the_bhyt, phong_kham = :phong_kham, 
                    chan_doan = :chan_doan, yeu_cau = :yeu_cau, bac_si_kham = :bac_si_kham, 
                    thoi_gian_yeu_cau = :thoi_gian_yeu_cau, trang_thai = :trang_thai
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':so_ho_so' => $data['so_ho_so'],
                ':ho_ten' => $data['ho_ten'],
                ':gioi_tinh' => $data['gioi_tinh'],
                ':doi_tuong' => $data['doi_tuong'],
                ':so_the_bhyt' => $data['so_the_bhyt'],
                ':phong_kham' => $data['phong_kham'],
                ':chan_doan' => $data['chan_doan'],
                ':yeu_cau' => $data['yeu_cau'],
                ':bac_si_kham' => $data['bac_si_kham'],
                ':thoi_gian_yeu_cau' => $data['thoi_gian_yeu_cau'],
                ':trang_thai' => $data['trang_thai'] ?? 'Đã yêu cầu'
            ]);
        } catch (PDOException $e) {
            error_log("PhieuYeuCauSieuAm update error: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("PhieuYeuCauSieuAm update general error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy phiếu yêu cầu siêu âm theo ID phiếu khám bệnh
     */
    public function getByExamId($examId) {
        try {
            $sql = "SELECT pysa.*, pk.ho_ten, COALESCE(bn.gioi_tinh, pk.gioi_tinh) as gioi_tinh, pk.dia_chi, pk.chan_doan_vao_vien, pk.ten_bac_si,
                           CASE 
                               WHEN pk.doi_tuong_bhyt = 1 THEN 'BHYT'
                               WHEN pk.doi_tuong_thu_phi = 1 THEN 'Thu phí'
                               WHEN pk.doi_tuong_mien = 1 THEN 'Miễn'
                               ELSE 'Khác'
                           END as doi_tuong,
                           pk.so_the_bhyt
                    FROM {$this->table} pysa
                    JOIN phieu_kham_benh pk ON pysa.id_phieu_kham_benh = pk.id
                    JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                    WHERE pysa.id_phieu_kham_benh = :exam_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':exam_id' => $examId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("PhieuYeuCauSieuAm getByExamId error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy phiếu yêu cầu siêu âm theo ID
     */
    public function getById($id) {
        try {
            $sql = "SELECT pysa.*, pk.ho_ten, COALESCE(bn.gioi_tinh, pk.gioi_tinh) as gioi_tinh, pk.dia_chi, pk.chan_doan_vao_vien, pk.ten_bac_si, bn.ma_benh_nhan, bn.ngay_sinh
                    FROM {$this->table} pysa
                    JOIN phieu_kham_benh pk ON pysa.id_phieu_kham_benh = pk.id
                    JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                    WHERE pysa.id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Tính tuổi từ ngày sinh
            if ($result && $result['ngay_sinh']) {
                $birthDate = new DateTime($result['ngay_sinh']);
                $today = new DateTime();
                $age = $today->diff($birthDate)->y;
                $result['tuoi'] = $age;
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("PhieuYeuCauSieuAm getById error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cập nhật trạng thái phiếu yêu cầu siêu âm
     */
    public function updateStatus($id, $status) {
        try {
            $sql = "UPDATE {$this->table} SET trang_thai = :status WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':status' => $status
            ]);
        } catch (PDOException $e) {
            error_log("PhieuYeuCauSieuAm updateStatus error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kiểm tra yêu cầu siêu âm có hợp lệ không
     */
    public function validateUltrasoundRequests($yeuCau) {
        if (empty($yeuCau)) {
            return ['valid' => false, 'message' => 'Vui lòng nhập yêu cầu siêu âm'];
        }

        // Tách các yêu cầu theo dấu phẩy và loại bỏ khoảng trắng
        $requests = array_filter(array_map('trim', explode(',', $yeuCau)), function($item) {
            return !empty($item);
        });
        
        // Nếu không có yêu cầu hợp lệ sau khi filter
        if (empty($requests)) {
            return ['valid' => false, 'message' => 'Vui lòng nhập yêu cầu siêu âm'];
        }
        
        $validRequests = [];
        $invalidRequests = [];

        try {
            // Lấy tất cả dịch vụ siêu âm từ bảng suggestions
            $sql = "SELECT ten_goi_y FROM sieuam_suggestions WHERE trang_thai = 1";
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
            error_log("PhieuYeuCauSieuAm validateUltrasoundRequests error: " . $e->getMessage());
            return ['valid' => false, 'message' => 'Lỗi kiểm tra dịch vụ'];
        }
    }
}
