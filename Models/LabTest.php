<?php
require_once 'config/database.php';

class LabTest
{
    private $db;
    private $table_name = "phieu_yeu_cau_xet_nghiem";

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Lưu phiếu yêu cầu xét nghiệm
     */
    public function saveLabForm($data)
    {
        try {
            $examId = $data['exam_id'];
            $soHoSo = $data['so_ho_so'];
            $hoTen = $data['ho_ten'];
            $tuoi = $data['tuoi'];
            $gioiTinh = $data['gioi_tinh'];
            $doiTuong = $data['doi_tuong'];
            $soTheBhyt = $data['so_the_bhyt'];
            $phongKham = $data['phong_kham'];
            $chanDoan = $data['chan_doan'];
            $yeuCau = $data['yeu_cau'];
            $bacSiKham = $data['bac_si_kham'];
            $ngay = $data['ngay'] ?? '';
            $thang = $data['thang'] ?? '';
            $nam = $data['nam'] ?? '';

            // Validation
            if (empty($examId) || empty($hoTen) || empty($yeuCau)) {
                return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc'];
            }

            // Kiểm tra yêu cầu xét nghiệm có hợp lệ không
            $validation = $this->validateLabRequests($yeuCau);
            if (!$validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }

            // Kiểm tra xem đã có phiếu xét nghiệm chưa
            $stmt = $this->db->prepare("SELECT id FROM {$this->table_name} WHERE id_phieu_kham_benh = ?");
            $stmt->execute([$examId]);
            $existingForm = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingForm) {
                // Cập nhật phiếu hiện có
                $stmt = $this->db->prepare("
                    UPDATE {$this->table_name} 
                    SET so_ho_so = ?, ho_ten = ?, tuoi = ?, gioi_tinh = ?, doi_tuong = ?, 
                        so_the_bhyt = ?, phong_kham = ?, chan_doan = ?, yeu_cau = ?, 
                        bac_si_kham = ?, thoi_gian_yeu_cau = NOW()
                    WHERE id_phieu_kham_benh = ?
                ");
                $result = $stmt->execute([
                    $soHoSo, $hoTen, $tuoi, $gioiTinh, $doiTuong, $soTheBhyt, 
                    $phongKham, $chanDoan, $yeuCau, $bacSiKham, $examId
                ]);
            } else {
                // Tạo phiếu mới
                $stmt = $this->db->prepare("
                    INSERT INTO {$this->table_name} 
                    (id_phieu_kham_benh, so_ho_so, ho_ten, tuoi, gioi_tinh, doi_tuong, 
                     so_the_bhyt, phong_kham, chan_doan, yeu_cau, bac_si_kham, thoi_gian_yeu_cau)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $result = $stmt->execute([
                    $examId, $soHoSo, $hoTen, $tuoi, $gioiTinh, $doiTuong, 
                    $soTheBhyt, $phongKham, $chanDoan, $yeuCau, $bacSiKham
                ]);
            }

            return ['success' => true, 'message' => 'Lưu phiếu xét nghiệm thành công'];

        } catch (Exception $e) {
            error_log('Error saving lab form: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()];
        }
    }

    /**
     * Kiểm tra yêu cầu xét nghiệm có hợp lệ không
     */
    public function validateLabRequests($yeuCau) {
        if (empty($yeuCau)) {
            return ['valid' => false, 'message' => 'Vui lòng nhập yêu cầu xét nghiệm'];
        }

        // Tách các yêu cầu theo dấu phẩy và loại bỏ khoảng trắng
        $requests = array_filter(array_map('trim', explode(',', $yeuCau)), function($item) {
            return !empty($item);
        });
        
        // Nếu không có yêu cầu hợp lệ sau khi filter
        if (empty($requests)) {
            return ['valid' => false, 'message' => 'Vui lòng nhập yêu cầu xét nghiệm'];
        }
        
        $validRequests = [];
        $invalidRequests = [];

        try {
            // Lấy tất cả dịch vụ xét nghiệm từ bảng suggestions
            $sql = "SELECT ten_goi_y FROM xet_nghiem_suggestions WHERE trang_thai = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $suggestions = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Kiểm tra từng yêu cầu
            foreach ($requests as $request) {
                $found = false;
                foreach ($suggestions as $suggestion) {
                    if (stripos($suggestion, $request) !== false || stripos($request, $suggestion) !== false) {
                        $validRequests[] = $request;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $invalidRequests[] = $request;
                }
            }
            
            // Nếu có yêu cầu không hợp lệ
            if (!empty($invalidRequests)) {
                $invalidList = implode(', ', $invalidRequests);
                return [
                    'valid' => false, 
                    'message' => "Các yêu cầu sau không có trong danh sách dịch vụ: " . $invalidList
                ];
            }
            
            return [
                'valid' => true, 
                'validRequests' => $validRequests
            ];

        } catch (PDOException $e) {
            error_log("LabTest validateLabRequests error: " . $e->getMessage());
            return ['valid' => false, 'message' => 'Lỗi kiểm tra dịch vụ'];
        }
    }

    /**
     * Lấy dữ liệu phiếu xét nghiệm
     */
    public function getLabFormData($examId)
    {
        try {
            if (empty($examId)) {
                return ['success' => false, 'message' => 'Thiếu thông tin phiếu khám'];
            }

            $stmt = $this->db->prepare("
                SELECT * FROM {$this->table_name} 
                WHERE id_phieu_kham_benh = ?
            ");
            $stmt->execute([$examId]);
            $formData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($formData) {
                return ['success' => true, 'form_data' => $formData, 'form_id' => $formData['id']];
            } else {
                return ['success' => false, 'message' => 'Không tìm thấy phiếu xét nghiệm'];
            }

        } catch (Exception $e) {
            error_log('Error getting lab form data: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi hệ thống'];
        }
    }

    public function getById($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT pxn.*, pk.ho_ten, pk.tuoi, COALESCE(bn.gioi_tinh, pk.gioi_tinh) as gioi_tinh, 
                       pxn.chan_doan, bn.ma_benh_nhan, bn.dia_chi, pk.benh_nhan_id
                FROM {$this->table_name} pxn
                JOIN phieu_kham_benh pk ON pxn.id_phieu_kham_benh = pk.id
                JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                WHERE pxn.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Error getting lab test by id: ' . $e->getMessage());
            return false;
        }
    }

}
?>
