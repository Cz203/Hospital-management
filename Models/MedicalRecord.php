<?php
require_once 'config/database.php';

class MedicalRecord
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Lấy danh sách hồ sơ bệnh án của bác sĩ (từ lịch hẹn có trạng thái "Hoàn thành")
     * 
     * @param int $doctorId ID của bác sĩ
     * @param array $filters Các filter: search, search_type, selected_date, page, limit
     * @return array ['data' => [], 'total' => int, 'page' => int, 'limit' => int, 'total_pages' => int]
     */
    public function getRecordsByDoctor($doctorId, $filters = [])
    {
        try {
            $search = trim($filters['search'] ?? '');
            $searchType = trim($filters['search_type'] ?? ''); // 'ma_benh_nhan', 'so_dien_thoai', 'cccd'
            $selectedDate = trim($filters['selected_date'] ?? '');
            $page = (int)($filters['page'] ?? 1);
            $limit = (int)($filters['limit'] ?? 15);
            if ($limit <= 0) {
                $limit = 15;
            }
            $offset = ($page - 1) * $limit;

            error_log("MedicalRecord getRecordsByDoctor - Doctor ID: " . $doctorId);
            error_log("MedicalRecord getRecordsByDoctor - Selected Date: " . $selectedDate);

            // Build SQL query - Lấy từ lich_hen có trạng thái "Hoàn thành"
            $sql = "SELECT DISTINCT
                        lh.id as lich_hen_id,
                        lh.ngay_hen,
                        lh.gio_hen,
                        lh.trang_thai as trang_thai_lich_hen,
                        lh.ly_do,
                        pk.id as exam_id,
                        pk.ngay_kham, pk.thang_kham, pk.nam_kham,
                        pk.gio_kham, pk.phut_kham,
                        pk.ho_ten as ten_benh_nhan,
                        pk.tuoi,
                        pk.gioi_tinh,
                        pk.dia_chi,
                        pk.chan_doan_vao_vien,
                        pk.tom_tat_lam_sang,
                        pk.ten_bac_si,
                        bn.id as benh_nhan_id,
                        bn.ma_benh_nhan,
                        bn.so_dien_thoai,
                        bn.cccd,
                        bn.ngay_sinh
                    FROM lich_hen lh
                    JOIN benh_nhan bn ON lh.benh_nhan_id = bn.id
                    LEFT JOIN phieu_kham_benh pk ON lh.id = pk.id_lich_hen
                    WHERE lh.bac_si_id = :doctor_id 
                    AND lh.trang_thai = 'Hoàn thành'";

            $params = [':doctor_id' => $doctorId];

            // Filter theo search
            if (!empty($search) && !empty($searchType)) {
                switch ($searchType) {
                    case 'ma_benh_nhan':
                        $sql .= " AND bn.ma_benh_nhan LIKE :search";
                        $params[':search'] = '%' . $search . '%';
                        break;
                    case 'so_dien_thoai':
                        $sql .= " AND bn.so_dien_thoai LIKE :search";
                        $params[':search'] = '%' . $search . '%';
                        break;
                    case 'cccd':
                        $sql .= " AND bn.cccd LIKE :search";
                        $params[':search'] = '%' . $search . '%';
                        break;
                }
            }

            // Filter theo ngày - ưu tiên ngày khám từ phiếu khám, nếu không có thì dùng ngày hẹn
            if (!empty($selectedDate)) {
                $sql .= " AND (
                    (pk.id IS NOT NULL AND DATE(CONCAT(pk.nam_kham, '-', LPAD(pk.thang_kham, 2, '0'), '-', LPAD(pk.ngay_kham, 2, '0'))) = :selected_date)
                    OR (pk.id IS NULL AND lh.ngay_hen = :selected_date)
                )";
                $params[':selected_date'] = $selectedDate;
            }

            // Count total - tạo query riêng để count
            $countSql = "SELECT COUNT(DISTINCT lh.id) as total
                    FROM lich_hen lh
                    JOIN benh_nhan bn ON lh.benh_nhan_id = bn.id
                    LEFT JOIN phieu_kham_benh pk ON lh.id = pk.id_lich_hen
                    WHERE lh.bac_si_id = :doctor_id 
                    AND lh.trang_thai = 'Hoàn thành'";
            
            $countParams = [':doctor_id' => $doctorId];
            
            // Filter theo search
            if (!empty($search) && !empty($searchType)) {
                switch ($searchType) {
                    case 'ma_benh_nhan':
                        $countSql .= " AND bn.ma_benh_nhan LIKE :search";
                        $countParams[':search'] = '%' . $search . '%';
                        break;
                    case 'so_dien_thoai':
                        $countSql .= " AND bn.so_dien_thoai LIKE :search";
                        $countParams[':search'] = '%' . $search . '%';
                        break;
                    case 'cccd':
                        $countSql .= " AND bn.cccd LIKE :search";
                        $countParams[':search'] = '%' . $search . '%';
                        break;
                }
            }
            
            // Filter theo ngày
            if (!empty($selectedDate)) {
                $countSql .= " AND (
                    (pk.id IS NOT NULL AND DATE(CONCAT(pk.nam_kham, '-', LPAD(pk.thang_kham, 2, '0'), '-', LPAD(pk.ngay_kham, 2, '0'))) = :selected_date)
                    OR (pk.id IS NULL AND lh.ngay_hen = :selected_date)
                )";
                $countParams[':selected_date'] = $selectedDate;
            }
            
            $countStmt = $this->conn->prepare($countSql);
            $countStmt->execute($countParams);
            $total = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Order và limit - ưu tiên ngày khám từ phiếu khám, nếu không có thì dùng ngày hẹn
            $sql .= " ORDER BY 
                        COALESCE(pk.nam_kham, YEAR(lh.ngay_hen)) DESC,
                        COALESCE(pk.thang_kham, MONTH(lh.ngay_hen)) DESC,
                        COALESCE(pk.ngay_kham, DAY(lh.ngay_hen)) DESC,
                        COALESCE(pk.gio_kham, HOUR(lh.gio_hen)) DESC,
                        COALESCE(pk.phut_kham, MINUTE(lh.gio_hen)) DESC";
            $sql .= " LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("MedicalRecord getRecordsByDoctor - SQL: " . $sql);
            error_log("MedicalRecord getRecordsByDoctor - Records found: " . count($records));

            // Format date - ưu tiên ngày khám từ phiếu khám, nếu không có thì dùng ngày hẹn
            foreach ($records as &$record) {
                if ($record['exam_id'] && $record['ngay_kham'] && $record['thang_kham'] && $record['nam_kham']) {
                    // Có phiếu khám bệnh
                    $record['ngay_kham_formatted'] = sprintf('%02d/%02d/%04d', $record['ngay_kham'], $record['thang_kham'], $record['nam_kham']);

                    $gioKhamFormatted = null;
                    $hasNumericTime = $record['gio_kham'] !== null && $record['phut_kham'] !== null && $record['gio_kham'] !== '';
                    if ($hasNumericTime && is_numeric($record['gio_kham']) && is_numeric($record['phut_kham'])) {
                        $gioKhamFormatted = sprintf('%02d:%02d', (int)$record['gio_kham'], (int)$record['phut_kham']);
                    } elseif (!empty($record['gio_kham'])) {
                        try {
                            $gioKhamFormatted = (new DateTime($record['gio_kham']))->format('H:i');
                        } catch (Exception $e) {
                            if (preg_match('/(\d{1,2})[:h](\d{1,2})/', $record['gio_kham'], $matches)) {
                                $gioKhamFormatted = sprintf('%02d:%02d', (int)$matches[1], (int)$matches[2]);
                            }
                        }
                    }

                    if (!$gioKhamFormatted && !empty($record['gio_hen'])) {
                        try {
                            $gioKhamFormatted = (new DateTime($record['gio_hen']))->format('H:i');
                        } catch (Exception $e) {
                            $gioKhamFormatted = '-';
                        }
                    }

                    $record['gio_kham_formatted'] = $gioKhamFormatted ?: '-';
                } else {
                    // Không có phiếu khám, dùng ngày hẹn
                    $ngayHen = new DateTime($record['ngay_hen']);
                    $record['ngay_kham_formatted'] = $ngayHen->format('d/m/Y');
                    try {
                        $gioHen = new DateTime($record['gio_hen']);
                        $record['gio_kham_formatted'] = $gioHen->format('H:i');
                    } catch (Exception $e) {
                        $record['gio_kham_formatted'] = '-';
                    }
                }
            }

            return [
                'data' => $records,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ];
        } catch (Exception $e) {
            error_log("MedicalRecord getRecordsByDoctor error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy danh sách hồ sơ bệnh án của bệnh nhân (từ lịch hẹn có trạng thái "Hoàn thành")
     * 
     * @param int $patientId ID của bệnh nhân
     * @param array $filters Các filter: selected_date, page, limit
     * @return array ['data' => [], 'total' => int, 'page' => int, 'limit' => int, 'total_pages' => int]
     */
    public function getRecordsByPatient($patientId, $filters = [])
    {
        try {
            $selectedDate = trim($filters['selected_date'] ?? '');
            $page = (int)($filters['page'] ?? 1);
            $limit = (int)($filters['limit'] ?? 15);
            if ($limit <= 0) {
                $limit = 15;
            }
            $offset = ($page - 1) * $limit;

            // Build SQL query - Lấy từ lich_hen có trạng thái "Hoàn thành"
            $sql = "SELECT DISTINCT
                        lh.id as lich_hen_id,
                        lh.ngay_hen,
                        lh.gio_hen,
                        lh.trang_thai as trang_thai_lich_hen,
                        lh.ly_do,
                        pk.id as exam_id,
                        pk.ngay_kham, pk.thang_kham, pk.nam_kham,
                        pk.gio_kham, pk.phut_kham,
                        pk.ho_ten as ten_benh_nhan,
                        pk.tuoi,
                        pk.gioi_tinh,
                        pk.dia_chi,
                        pk.chan_doan_vao_vien,
                        pk.tom_tat_lam_sang,
                        pk.ten_bac_si,
                        bn.id as benh_nhan_id,
                        bn.ma_benh_nhan,
                        bn.so_dien_thoai,
                        bn.cccd,
                        bn.ngay_sinh,
                        bs.ten as ten_bac_si_full
                    FROM lich_hen lh
                    JOIN benh_nhan bn ON lh.benh_nhan_id = bn.id
                    LEFT JOIN phieu_kham_benh pk ON lh.id = pk.id_lich_hen
                    LEFT JOIN bac_si bs ON lh.bac_si_id = bs.id
                    WHERE lh.benh_nhan_id = :patient_id 
                    AND lh.trang_thai = 'Hoàn thành'";

            $params = [':patient_id' => $patientId];

            // Filter theo ngày - ưu tiên ngày khám từ phiếu khám, nếu không có thì dùng ngày hẹn
            if (!empty($selectedDate)) {
                $sql .= " AND (
                    (pk.id IS NOT NULL AND DATE(CONCAT(pk.nam_kham, '-', LPAD(pk.thang_kham, 2, '0'), '-', LPAD(pk.ngay_kham, 2, '0'))) = :selected_date)
                    OR (pk.id IS NULL AND lh.ngay_hen = :selected_date)
                )";
                $params[':selected_date'] = $selectedDate;
            }

            // Count total
            $countSql = "SELECT COUNT(DISTINCT lh.id) as total
                    FROM lich_hen lh
                    JOIN benh_nhan bn ON lh.benh_nhan_id = bn.id
                    LEFT JOIN phieu_kham_benh pk ON lh.id = pk.id_lich_hen
                    WHERE lh.benh_nhan_id = :patient_id 
                    AND lh.trang_thai = 'Hoàn thành'";
            
            $countParams = [':patient_id' => $patientId];
            
            // Filter theo ngày
            if (!empty($selectedDate)) {
                $countSql .= " AND (
                    (pk.id IS NOT NULL AND DATE(CONCAT(pk.nam_kham, '-', LPAD(pk.thang_kham, 2, '0'), '-', LPAD(pk.ngay_kham, 2, '0'))) = :selected_date)
                    OR (pk.id IS NULL AND lh.ngay_hen = :selected_date)
                )";
                $countParams[':selected_date'] = $selectedDate;
            }
            
            $countStmt = $this->conn->prepare($countSql);
            $countStmt->execute($countParams);
            $total = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Order và limit - ưu tiên ngày khám từ phiếu khám, nếu không có thì dùng ngày hẹn
            $sql .= " ORDER BY 
                        COALESCE(pk.nam_kham, YEAR(lh.ngay_hen)) DESC,
                        COALESCE(pk.thang_kham, MONTH(lh.ngay_hen)) DESC,
                        COALESCE(pk.ngay_kham, DAY(lh.ngay_hen)) DESC,
                        COALESCE(pk.gio_kham, HOUR(lh.gio_hen)) DESC,
                        COALESCE(pk.phut_kham, MINUTE(lh.gio_hen)) DESC";
            $sql .= " LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format date - ưu tiên ngày khám từ phiếu khám, nếu không có thì dùng ngày hẹn
            foreach ($records as &$record) {
                if ($record['exam_id'] && $record['ngay_kham'] && $record['thang_kham'] && $record['nam_kham']) {
                    // Có phiếu khám bệnh
                    $record['ngay_kham_formatted'] = sprintf('%02d/%02d/%04d', $record['ngay_kham'], $record['thang_kham'], $record['nam_kham']);

                    $gioKhamFormatted = null;
                    $hasNumericTime = $record['gio_kham'] !== null && $record['phut_kham'] !== null && $record['gio_kham'] !== '';
                    if ($hasNumericTime && is_numeric($record['gio_kham']) && is_numeric($record['phut_kham'])) {
                        $gioKhamFormatted = sprintf('%02d:%02d', (int)$record['gio_kham'], (int)$record['phut_kham']);
                    } elseif (!empty($record['gio_kham'])) {
                        try {
                            $gioKhamFormatted = (new DateTime($record['gio_kham']))->format('H:i');
                        } catch (Exception $e) {
                            if (preg_match('/(\d{1,2})[:h](\d{1,2})/', $record['gio_kham'], $matches)) {
                                $gioKhamFormatted = sprintf('%02d:%02d', (int)$matches[1], (int)$matches[2]);
                            }
                        }
                    }

                    if (!$gioKhamFormatted && !empty($record['gio_hen'])) {
                        try {
                            $gioKhamFormatted = (new DateTime($record['gio_hen']))->format('H:i');
                        } catch (Exception $e) {
                            $gioKhamFormatted = '-';
                        }
                    }

                    $record['gio_kham_formatted'] = $gioKhamFormatted ?: '-';
                } else {
                    // Không có phiếu khám, dùng ngày hẹn
                    $ngayHen = new DateTime($record['ngay_hen']);
                    $record['ngay_kham_formatted'] = $ngayHen->format('d/m/Y');
                    try {
                        $gioHen = new DateTime($record['gio_hen']);
                        $record['gio_kham_formatted'] = $gioHen->format('H:i');
                    } catch (Exception $e) {
                        $record['gio_kham_formatted'] = '-';
                    }
                }
                
                // Dùng tên bác sĩ từ join nếu có
                if (empty($record['ten_bac_si']) && !empty($record['ten_bac_si_full'])) {
                    $record['ten_bac_si'] = $record['ten_bac_si_full'];
                }
            }

            return [
                'data' => $records,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ];
        } catch (Exception $e) {
            error_log("MedicalRecord getRecordsByPatient error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy chi tiết hồ sơ bệnh án cho bệnh nhân (không cần kiểm tra doctor_id)
     * 
     * @param int $lichHenId ID của lịch hẹn
     * @param int $patientId ID của bệnh nhân
     * @return array|null Chi tiết hồ sơ bệnh án hoặc null nếu không tìm thấy
     */
    public function getRecordDetailForPatient($lichHenId, $patientId)
    {
        try {
            // Lấy thông tin từ lịch hẹn (có trạng thái Hoàn thành) và phiếu khám bệnh
            $examSql = "SELECT pk.*, 
                        lh.id as lich_hen_id,
                        lh.ngay_hen,
                        lh.gio_hen,
                        lh.ly_do as ly_do_kham,
                        bn.ma_benh_nhan, 
                        bn.so_dien_thoai, 
                        bn.cccd, 
                        bn.ngay_sinh as ngay_sinh_benh_nhan
                        FROM lich_hen lh
                        JOIN benh_nhan bn ON lh.benh_nhan_id = bn.id
                        LEFT JOIN phieu_kham_benh pk ON lh.id = pk.id_lich_hen
                        WHERE lh.id = :lich_hen_id 
                        AND lh.benh_nhan_id = :patient_id 
                        AND lh.trang_thai = 'Hoàn thành'";
            $examStmt = $this->conn->prepare($examSql);
            $examStmt->execute([':lich_hen_id' => $lichHenId, ':patient_id' => $patientId]);
            $exam = $examStmt->fetch(PDO::FETCH_ASSOC);

            if (!$exam) {
                return null;
            }

            // Nếu không có phiếu khám bệnh (pk.id là NULL), lấy thông tin từ lịch hẹn
            if (empty($exam['id'])) {
                // Lấy thông tin bệnh nhân từ lịch hẹn
                $patientSql = "SELECT bn.* FROM benh_nhan bn 
                               JOIN lich_hen lh ON bn.id = lh.benh_nhan_id 
                               WHERE lh.id = :lich_hen_id";
                $patientStmt = $this->conn->prepare($patientSql);
                $patientStmt->execute([':lich_hen_id' => $lichHenId]);
                $patient = $patientStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($patient) {
                    // Tạo exam data từ lịch hẹn
                    $exam = array_merge($exam, [
                        'ho_ten' => $patient['ho_ten'] ?? '',
                        'tuoi' => $patient['tuoi'] ?? '',
                        'gioi_tinh' => $patient['gioi_tinh'] ?? '',
                        'dia_chi' => $patient['dia_chi'] ?? '',
                        'ma_benh_nhan' => $patient['ma_benh_nhan'] ?? '',
                        'so_dien_thoai' => $patient['so_dien_thoai'] ?? '',
                        'cccd' => $patient['cccd'] ?? '',
                        'ngay_sinh_benh_nhan' => $patient['ngay_sinh'] ?? null
                    ]);
                }
            }

            // Lấy exam_id thực tế từ phiếu khám bệnh (nếu có)
            $actualExamId = $exam['id'] ?? null;

            // Lấy đơn thuốc - dùng exam_id từ phiếu khám bệnh
            $prescription = null;
            $prescriptionDetails = [];
            if ($actualExamId) {
                $prescriptionSql = "SELECT dt.*, 
                                    (SELECT COUNT(*) FROM chi_tiet_don_thuoc WHERE MaDonThuoc = dt.MaDonThuoc) as so_loai_thuoc
                                    FROM don_thuoc dt
                                    WHERE dt.id_phieu_kham_benh = :exam_id
                                    ORDER BY dt.NgayKe DESC LIMIT 1";
                $prescriptionStmt = $this->conn->prepare($prescriptionSql);
                $prescriptionStmt->execute([':exam_id' => $actualExamId]);
                $prescription = $prescriptionStmt->fetch(PDO::FETCH_ASSOC);

                // Lấy chi tiết đơn thuốc
                if ($prescription) {
                    $prescriptionDetailSql = "SELECT * FROM chi_tiet_don_thuoc WHERE MaDonThuoc = :ma_don_thuoc ORDER BY MaChiTiet";
                    $prescriptionDetailStmt = $this->conn->prepare($prescriptionDetailSql);
                    $prescriptionDetailStmt->execute([':ma_don_thuoc' => $prescription['MaDonThuoc']]);
                    $prescriptionDetails = $prescriptionDetailStmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }

            // Lấy phiếu yêu cầu xét nghiệm - dùng exam_id từ phiếu khám bệnh
            $labRequest = null;
            if ($actualExamId) {
                $labRequestSql = "SELECT * FROM phieu_yeu_cau_xet_nghiem WHERE id_phieu_kham_benh = :exam_id";
                $labRequestStmt = $this->conn->prepare($labRequestSql);
                $labRequestStmt->execute([':exam_id' => $actualExamId]);
                $labRequest = $labRequestStmt->fetch(PDO::FETCH_ASSOC);
            }

            // Lấy kết quả xét nghiệm
            $labResult = null;
            if ($labRequest) {
                $labResultSql = "SELECT kq.*, 
                                (SELECT COUNT(*) FROM chi_tiet_ket_qua_xet_nghiem WHERE id_phieu_tra_ket_qua = kq.id) as so_chi_so
                                FROM phieu_tra_ket_qua_xet_nghiem kq
                                WHERE kq.id_phieu_yeu_cau = :request_id
                                ORDER BY kq.ngay_tao DESC LIMIT 1";
                $labResultStmt = $this->conn->prepare($labResultSql);
                $labResultStmt->execute([':request_id' => $labRequest['id']]);
                $labResult = $labResultStmt->fetch(PDO::FETCH_ASSOC);

                // Lấy chi tiết kết quả xét nghiệm
                if ($labResult) {
                    $labTestDetailSql = "SELECT * FROM chi_tiet_ket_qua_xet_nghiem WHERE id_phieu_tra_ket_qua = :result_id ORDER BY stt";
                    $labTestDetailStmt = $this->conn->prepare($labTestDetailSql);
                    $labTestDetailStmt->execute([':result_id' => $labResult['id']]);
                    $labResult['chi_tiet'] = $labTestDetailStmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }

            // Lấy phiếu yêu cầu siêu âm - dùng exam_id từ phiếu khám bệnh
            $ultrasoundRequest = null;
            if ($actualExamId) {
                $ultrasoundSql = "SELECT * FROM phieu_yeu_cau_sieu_am WHERE id_phieu_kham_benh = :exam_id";
                $ultrasoundStmt = $this->conn->prepare($ultrasoundSql);
                $ultrasoundStmt->execute([':exam_id' => $actualExamId]);
                $ultrasoundRequest = $ultrasoundStmt->fetch(PDO::FETCH_ASSOC);
            }

            // Lấy kết quả siêu âm
            $ultrasoundResult = null;
            $ultrasoundImages = [];
            if ($ultrasoundRequest) {
                $ultrasoundResultSql = "SELECT * FROM ket_qua_sieu_am WHERE id_phieu_yeu_cau_sieu_am = :request_id ORDER BY ngay_cap_nhat DESC LIMIT 1";
                $ultrasoundResultStmt = $this->conn->prepare($ultrasoundResultSql);
                $ultrasoundResultStmt->execute([':request_id' => $ultrasoundRequest['id']]);
                $ultrasoundResult = $ultrasoundResultStmt->fetch(PDO::FETCH_ASSOC);

                if ($ultrasoundResult) {
                    $ultrasoundImageSql = "SELECT * FROM sieu_am_hinh_anh WHERE id_ket_qua_sieu_am = :result_id ORDER BY id";
                    $ultrasoundImageStmt = $this->conn->prepare($ultrasoundImageSql);
                    $ultrasoundImageStmt->execute([':result_id' => $ultrasoundResult['id']]);
                    $ultrasoundImages = $ultrasoundImageStmt->fetchAll(PDO::FETCH_ASSOC);
                    // Gán hình ảnh vào mảng kết quả để view có thể sử dụng
                    $ultrasoundResult['hinh_anh'] = $ultrasoundImages;
                }
            }

            // Lấy phiếu chụp X-Quang - dùng exam_id từ phiếu khám bệnh
            $xrayRequest = null;
            if ($actualExamId) {
                $xraySql = "SELECT * FROM phieu_chup_xquang WHERE id_phieu_kham_benh = :exam_id";
                $xrayStmt = $this->conn->prepare($xraySql);
                $xrayStmt->execute([':exam_id' => $actualExamId]);
                $xrayRequest = $xrayStmt->fetch(PDO::FETCH_ASSOC);
            }

            // Lấy kết quả X-Quang
            $xrayResult = null;
            $xrayImages = [];
            if ($xrayRequest) {
                $xrayResultSql = "SELECT * FROM ket_qua_xquang WHERE id_phieu_chup_xquang = :xray_id ORDER BY ngay_cap_nhat DESC LIMIT 1";
                $xrayResultStmt = $this->conn->prepare($xrayResultSql);
                $xrayResultStmt->execute([':xray_id' => $xrayRequest['id']]);
                $xrayResult = $xrayResultStmt->fetch(PDO::FETCH_ASSOC);

                if ($xrayResult) {
                    $xrayImageSql = "SELECT * FROM ket_qua_xquang_hinh_anh WHERE ket_qua_id = :ket_qua_id ORDER BY id";
                    $xrayImageStmt = $this->conn->prepare($xrayImageSql);
                    $xrayImageStmt->execute([':ket_qua_id' => $xrayResult['id']]);
                    $xrayImages = $xrayImageStmt->fetchAll(PDO::FETCH_ASSOC);
                    // Gán hình ảnh vào mảng kết quả để view có thể sử dụng
                    $xrayResult['hinh_anh'] = $xrayImages;
                }
            }

            // Lấy biên lai - dùng exam_id từ phiếu khám bệnh
            $receipt = null;
            $receiptDetails = [];
            if ($actualExamId) {
                $receiptSql = "SELECT bl.*, 
                              pk.ho_ten, pk.tuoi, pk.gioi_tinh, pk.dia_chi, pk.so_the_bhyt, pk.doi_tuong_bhyt,
                              bn.ma_benh_nhan, bs.ten as ten_bac_si
                              FROM bien_lai_vien_phi bl
                              JOIN phieu_kham_benh pk ON bl.id_phieu_kham_benh = pk.id
                              JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                              LEFT JOIN bac_si bs ON pk.bac_si_id = bs.id
                              WHERE bl.id_phieu_kham_benh = :exam_id
                              ORDER BY bl.created_at DESC LIMIT 1";
                $receiptStmt = $this->conn->prepare($receiptSql);
                $receiptStmt->execute([':exam_id' => $actualExamId]);
                $receipt = $receiptStmt->fetch(PDO::FETCH_ASSOC);

                // Lấy chi tiết biên lai
                if ($receipt) {
                    $receiptDetailSql = "SELECT * FROM chi_tiet_bien_lai WHERE id_bien_lai = :receipt_id ORDER BY id";
                    $receiptDetailStmt = $this->conn->prepare($receiptDetailSql);
                    $receiptDetailStmt->execute([':receipt_id' => $receipt['id']]);
                    $receiptDetails = $receiptDetailStmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }

            return [
                'exam' => $exam,
                'prescription' => $prescription,
                'prescription_details' => $prescriptionDetails,
                'lab_request' => $labRequest,
                'lab_result' => $labResult,
                'lab_test_details' => $labResult['chi_tiet'] ?? [],
                'ultrasound_request' => $ultrasoundRequest,
                'ultrasound_result' => $ultrasoundResult,
                'ultrasound_images' => $ultrasoundImages,
                'xray_request' => $xrayRequest,
                'xray_result' => $xrayResult,
                'xray_images' => $xrayImages,
                'receipt' => $receipt,
                'receipt_details' => $receiptDetails
            ];
        } catch (Exception $e) {
            error_log("MedicalRecord getRecordDetailForPatient error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy chi tiết hồ sơ bệnh án (bao gồm phiếu khám, đơn thuốc, xét nghiệm, siêu âm, X-Quang)
     * 
     * @param int $lichHenId ID của lịch hẹn
     * @param int $doctorId ID của bác sĩ
     * @return array|null Chi tiết hồ sơ bệnh án hoặc null nếu không tìm thấy
     */
    public function getRecordDetail($lichHenId, $doctorId)
    {
        try {
            // Lấy thông tin từ lịch hẹn (có trạng thái Hoàn thành) và phiếu khám bệnh
            $examSql = "SELECT pk.*, 
                        lh.id as lich_hen_id,
                        lh.ngay_hen,
                        lh.gio_hen,
                        lh.ly_do as ly_do_kham,
                        bn.ma_benh_nhan, 
                        bn.so_dien_thoai, 
                        bn.cccd, 
                        bn.ngay_sinh as ngay_sinh_benh_nhan
                        FROM lich_hen lh
                        JOIN benh_nhan bn ON lh.benh_nhan_id = bn.id
                        LEFT JOIN phieu_kham_benh pk ON lh.id = pk.id_lich_hen
                        WHERE lh.id = :lich_hen_id 
                        AND lh.bac_si_id = :doctor_id 
                        AND lh.trang_thai = 'Hoàn thành'";
            $examStmt = $this->conn->prepare($examSql);
            $examStmt->execute([':lich_hen_id' => $lichHenId, ':doctor_id' => $doctorId]);
            $exam = $examStmt->fetch(PDO::FETCH_ASSOC);

            if (!$exam) {
                return null;
            }

            // Nếu không có phiếu khám bệnh (pk.id là NULL), lấy thông tin từ lịch hẹn
            if (empty($exam['id'])) {
                // Lấy thông tin bệnh nhân từ lịch hẹn
                $patientSql = "SELECT bn.* FROM benh_nhan bn 
                               JOIN lich_hen lh ON bn.id = lh.benh_nhan_id 
                               WHERE lh.id = :lich_hen_id";
                $patientStmt = $this->conn->prepare($patientSql);
                $patientStmt->execute([':lich_hen_id' => $lichHenId]);
                $patient = $patientStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($patient) {
                    // Tạo exam data từ lịch hẹn
                    $exam['ho_ten'] = $patient['ten'] ?? '';
                    $exam['ngay_sinh'] = null;
                    $exam['thang_sinh'] = null;
                    $exam['nam_sinh'] = null;
                    $exam['tuoi'] = $patient['ngay_sinh'] ? (new DateTime())->diff(new DateTime($patient['ngay_sinh']))->y : null;
                    $exam['gioi_tinh'] = $patient['gioi_tinh'] ?? '';
                    $exam['dia_chi'] = $patient['dia_chi'] ?? '';
                    $exam['ngay_kham'] = date('d', strtotime($exam['ngay_hen']));
                    $exam['thang_kham'] = date('m', strtotime($exam['ngay_hen']));
                    $exam['nam_kham'] = date('Y', strtotime($exam['ngay_hen']));
                    $exam['gio_kham'] = date('H', strtotime($exam['gio_hen']));
                    $exam['phut_kham'] = date('i', strtotime($exam['gio_hen']));
                }
            }

            // Format date
            if ($exam['ngay_kham'] && $exam['thang_kham'] && $exam['nam_kham']) {
                $exam['ngay_kham_formatted'] = sprintf('%02d/%02d/%04d', $exam['ngay_kham'], $exam['thang_kham'], $exam['nam_kham']);
                $exam['gio_kham_formatted'] = sprintf('%02d:%02d', $exam['gio_kham'] ?? 0, $exam['phut_kham'] ?? 0);
            } else {
                $ngayHen = new DateTime($exam['ngay_hen']);
                $exam['ngay_kham_formatted'] = $ngayHen->format('d/m/Y');
                $gioHen = new DateTime($exam['gio_hen']);
                $exam['gio_kham_formatted'] = $gioHen->format('H:i');
            }

            // Lấy exam_id thực tế từ phiếu khám bệnh (nếu có)
            $actualExamId = $exam['id'] ?? null;

            // Lấy đơn thuốc - dùng exam_id từ phiếu khám bệnh
            $prescription = null;
            $prescriptionDetails = [];
            if ($actualExamId) {
                $prescriptionSql = "SELECT dt.*, 
                                    (SELECT COUNT(*) FROM chi_tiet_don_thuoc WHERE MaDonThuoc = dt.MaDonThuoc) as so_loai_thuoc
                                    FROM don_thuoc dt
                                    WHERE dt.id_phieu_kham_benh = :exam_id
                                    ORDER BY dt.NgayKe DESC LIMIT 1";
                $prescriptionStmt = $this->conn->prepare($prescriptionSql);
                $prescriptionStmt->execute([':exam_id' => $actualExamId]);
                $prescription = $prescriptionStmt->fetch(PDO::FETCH_ASSOC);

                // Lấy chi tiết đơn thuốc
                if ($prescription) {
                    $detailSql = "SELECT * FROM chi_tiet_don_thuoc WHERE MaDonThuoc = :ma_don_thuoc ORDER BY MaChiTiet";
                    $detailStmt = $this->conn->prepare($detailSql);
                    $detailStmt->execute([':ma_don_thuoc' => $prescription['MaDonThuoc']]);
                    $prescriptionDetails = $detailStmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }

            // Lấy phiếu yêu cầu xét nghiệm - dùng exam_id từ phiếu khám bệnh
            $labRequest = null;
            $labResult = null;
            if ($actualExamId) {
                $labSql = "SELECT * FROM phieu_yeu_cau_xet_nghiem WHERE id_phieu_kham_benh = :exam_id";
                $labStmt = $this->conn->prepare($labSql);
                $labStmt->execute([':exam_id' => $actualExamId]);
                $labRequest = $labStmt->fetch(PDO::FETCH_ASSOC);

                // Lấy kết quả xét nghiệm
                if ($labRequest) {
                    $labResultSql = "SELECT kq.*, 
                                    (SELECT COUNT(*) FROM chi_tiet_ket_qua_xet_nghiem WHERE id_phieu_tra_ket_qua = kq.id) as so_chi_so
                                    FROM phieu_tra_ket_qua_xet_nghiem kq
                                    WHERE kq.id_phieu_yeu_cau = :lab_id
                                    ORDER BY kq.ngay_tao DESC LIMIT 1";
                    $labResultStmt = $this->conn->prepare($labResultSql);
                    $labResultStmt->execute([':lab_id' => $labRequest['id']]);
                    $labResult = $labResultStmt->fetch(PDO::FETCH_ASSOC);

                    // Lấy chi tiết kết quả xét nghiệm
                    if ($labResult) {
                        $labDetailSql = "SELECT * FROM chi_tiet_ket_qua_xet_nghiem WHERE id_phieu_tra_ket_qua = :result_id ORDER BY stt";
                        $labDetailStmt = $this->conn->prepare($labDetailSql);
                        $labDetailStmt->execute([':result_id' => $labResult['id']]);
                        $labResult['chi_tiet'] = $labDetailStmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                }
            }

            // Lấy phiếu yêu cầu siêu âm - dùng exam_id từ phiếu khám bệnh
            $ultrasoundRequest = null;
            $ultrasoundResult = null;
            if ($actualExamId) {
                $ultrasoundSql = "SELECT * FROM phieu_yeu_cau_sieu_am WHERE id_phieu_kham_benh = :exam_id";
                $ultrasoundStmt = $this->conn->prepare($ultrasoundSql);
                $ultrasoundStmt->execute([':exam_id' => $actualExamId]);
                $ultrasoundRequest = $ultrasoundStmt->fetch(PDO::FETCH_ASSOC);

                // Lấy kết quả siêu âm
                if ($ultrasoundRequest) {
                    $ultrasoundResultSql = "SELECT * FROM ket_qua_sieu_am WHERE id_phieu_yeu_cau_sieu_am = :us_id ORDER BY ngay_tao DESC LIMIT 1";
                    $ultrasoundResultStmt = $this->conn->prepare($ultrasoundResultSql);
                    $ultrasoundResultStmt->execute([':us_id' => $ultrasoundRequest['id']]);
                    $ultrasoundResult = $ultrasoundResultStmt->fetch(PDO::FETCH_ASSOC);

                    // Lấy hình ảnh siêu âm
                    if ($ultrasoundResult) {
                        $usImageSql = "SELECT * FROM sieu_am_hinh_anh WHERE id_ket_qua_sieu_am = :result_id ORDER BY id";
                        $usImageStmt = $this->conn->prepare($usImageSql);
                        $usImageStmt->execute([':result_id' => $ultrasoundResult['id']]);
                        $ultrasoundResult['hinh_anh'] = $usImageStmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                }
            }

            // Lấy phiếu chụp X-Quang - dùng exam_id từ phiếu khám bệnh
            $xrayRequest = null;
            $xrayResult = null;
            if ($actualExamId) {
                $xraySql = "SELECT * FROM phieu_chup_xquang WHERE id_phieu_kham_benh = :exam_id";
                $xrayStmt = $this->conn->prepare($xraySql);
                $xrayStmt->execute([':exam_id' => $actualExamId]);
                $xrayRequest = $xrayStmt->fetch(PDO::FETCH_ASSOC);

                // Lấy kết quả X-Quang
                if ($xrayRequest) {
                    $xrayResultSql = "SELECT * FROM ket_qua_xquang WHERE id_phieu_chup_xquang = :xray_id ORDER BY ngay_cap_nhat DESC LIMIT 1";
                    $xrayResultStmt = $this->conn->prepare($xrayResultSql);
                    $xrayResultStmt->execute([':xray_id' => $xrayRequest['id']]);
                    $xrayResult = $xrayResultStmt->fetch(PDO::FETCH_ASSOC);

                    // Lấy hình ảnh X-Quang
                    if ($xrayResult) {
                        $xrayImageSql = "SELECT * FROM ket_qua_xquang_hinh_anh WHERE ket_qua_id = :ket_qua_id ORDER BY id";
                        $xrayImageStmt = $this->conn->prepare($xrayImageSql);
                        $xrayImageStmt->execute([':ket_qua_id' => $xrayResult['id']]);
                        $xrayResult['hinh_anh'] = $xrayImageStmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                }
            }

            // Lấy biên lai viện phí - dùng exam_id từ phiếu khám bệnh
            $receipt = null;
            $receiptDetails = [];
            if ($actualExamId) {
                $receiptSql = "SELECT bl.*, 
                              pk.ho_ten, pk.tuoi, pk.gioi_tinh, pk.dia_chi, pk.so_the_bhyt, pk.doi_tuong_bhyt,
                              bn.ma_benh_nhan, bs.ten as ten_bac_si
                              FROM bien_lai_vien_phi bl
                              JOIN phieu_kham_benh pk ON bl.id_phieu_kham_benh = pk.id
                              JOIN benh_nhan bn ON pk.benh_nhan_id = bn.id
                              LEFT JOIN bac_si bs ON pk.bac_si_id = bs.id
                              WHERE bl.id_phieu_kham_benh = :exam_id
                              ORDER BY bl.created_at DESC LIMIT 1";
                $receiptStmt = $this->conn->prepare($receiptSql);
                $receiptStmt->execute([':exam_id' => $actualExamId]);
                $receipt = $receiptStmt->fetch(PDO::FETCH_ASSOC);

                // Lấy chi tiết biên lai
                if ($receipt) {
                    $receiptDetailSql = "SELECT * FROM chi_tiet_bien_lai WHERE id_bien_lai = :receipt_id ORDER BY id";
                    $receiptDetailStmt = $this->conn->prepare($receiptDetailSql);
                    $receiptDetailStmt->execute([':receipt_id' => $receipt['id']]);
                    $receiptDetails = $receiptDetailStmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }

            return [
                'exam' => $exam,
                'prescription' => $prescription,
                'prescription_details' => $prescriptionDetails,
                'lab_request' => $labRequest,
                'lab_result' => $labResult,
                'ultrasound_request' => $ultrasoundRequest,
                'ultrasound_result' => $ultrasoundResult,
                'xray_request' => $xrayRequest,
                'xray_result' => $xrayResult,
                'receipt' => $receipt,
                'receipt_details' => $receiptDetails
            ];
        } catch (Exception $e) {
            error_log("MedicalRecord getRecordDetail error: " . $e->getMessage());
            throw $e;
        }
    }
}

