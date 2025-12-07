<?php

/**
 * CCCD Service - Xác thực căn cước công dân
 * Service giả lập để verify CCCD
 */

class CCCDService
{
    /**
     * Database connection
     */
    private $conn;

    /**
     * Table name for CCCD data
     */
    private const TABLE_NAME = 'cccd_data';

    public function __construct($db = null)
    {
        if ($db === null) {
            // Tự động kết nối database nếu chưa có
            require_once __DIR__ . '/../config/database.php';
            $database = new Database();
            $this->conn = $database->getConnection();
        } else {
            $this->conn = $db;
        }
    }

    /**
     * Validate format CCCD (12 số)
     */
    public function validateFormat($cccd)
    {
        // CCCD phải có đúng 12 số
        if (!preg_match('/^\d{12}$/', $cccd)) {
            return [
                'valid' => false,
                'message' => 'CCCD phải có đúng 12 số'
            ];
        }

        // Kiểm tra mã tỉnh/thành phố (3 số đầu)
        $provinceCode = substr($cccd, 0, 3);
        if (!$this->isValidProvinceCode($provinceCode)) {
            return [
                'valid' => false,
                'message' => 'Mã tỉnh/thành phố không hợp lệ'
            ];
        }

        // Kiểm tra mã giới tính và thế kỷ (số thứ 4)
        $genderCentury = substr($cccd, 3, 1);
        if (!in_array($genderCentury, ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'])) {
            return [
                'valid' => false,
                'message' => 'Mã giới tính/thế kỷ không hợp lệ'
            ];
        }

        // Kiểm tra năm sinh (2 số tiếp theo)
        $yearOfBirth = substr($cccd, 4, 2);
        if ((int)$yearOfBirth > 99) {
            return [
                'valid' => false,
                'message' => 'Năm sinh không hợp lệ'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Format CCCD hợp lệ'
        ];
    }

    /**
     * Kiểm tra mã tỉnh/thành phố có hợp lệ không
     */
    private function isValidProvinceCode($code)
    {
        // Danh sách mã tỉnh/thành phố từ 001 đến 096
        $validCodes = range(1, 96);
        return in_array((int)$code, $validCodes);
    }

    /**
     * Verify CCCD với hệ thống (giả lập)
     */
    public function verifyCCCD($cccd, $ten = null, $ngaySinh = null)
    {
        // Bước 1: Validate format
        $formatCheck = $this->validateFormat($cccd);
        if (!$formatCheck['valid']) {
            return [
                'success' => false,
                'message' => $formatCheck['message'],
                'verified' => false
            ];
        }

        // Bước 2: Kiểm tra trong database (giả lập API call)
        if (!$this->conn) {
            return [
                'success' => false,
                'message' => 'Lỗi kết nối database. Vui lòng thử lại sau!',
                'verified' => false
            ];
        }

        // Lấy thông tin CCCD từ database
        $cccdData = $this->getCCCDFromDatabase($cccd);

        if (!$cccdData) {
            // Lấy danh sách CCCD mẫu để gợi ý
            $sampleCCCDs = $this->getSampleCCCDFromDatabase();
            '';

            return [
                'success' => false,
                'message' => 'CCCD không tồn tại trong hệ thống. Vui lòng kiểm tra lại!',
                'verified' => false,

            ];
        }

        // Bước 3: Verify thông tin (nếu có)
        if ($ten !== null) {
            // So sánh tên (case-insensitive, bỏ dấu)
            $tenNormalized = $this->normalizeVietnamese($ten);
            $cccdTenNormalized = $this->normalizeVietnamese($cccdData['ten']);

            if ($tenNormalized !== $cccdTenNormalized) {
                return [
                    'success' => false,
                    'message' => 'Họ tên không khớp với CCCD. CCCD này thuộc về: ',
                    'verified' => false,
                    'cccd_data' => [
                        'ten' => $cccdData['ten']
                    ]
                ];
            }
        }

        if ($ngaySinh !== null && $ngaySinh !== $cccdData['ngay_sinh']) {
            return [
                'success' => false,
                'message' => 'Ngày sinh không khớp với CCCD',
                'verified' => false,
                'cccd_data' => [
                    'ngay_sinh' => $cccdData['ngay_sinh']
                ]
            ];
        }

        // Bước 4: Tìm thông tin bảo hiểm y tế (nếu có) dựa trên số CCCD (ma_bao_hiem = cccd)
        $baoHiemInfo = $this->getBaoHiemForCCCDData($cccdData);

        // Tính trạng thái còn hạn / hết hạn cho BHYT (nếu tìm thấy)
        $baoHiemResult = null;
        if ($baoHiemInfo) {
            $today = date('Y-m-d');
            $conHan = ($baoHiemInfo['trang_thai'] === 'Hieu luc')
                && (!empty($baoHiemInfo['ngay_bat_dau']) ? $baoHiemInfo['ngay_bat_dau'] <= $today : true)
                && (!empty($baoHiemInfo['ngay_het_han']) ? $baoHiemInfo['ngay_het_han'] >= $today : true);

            $baoHiemResult = [
                'id' => (int)$baoHiemInfo['id'],
                'ma_bao_hiem' => $baoHiemInfo['ma_bao_hiem'],
                'loai_the' => $baoHiemInfo['loai_the'],
                'ten_chu_the' => $baoHiemInfo['ten_chu_the'],
                'ngay_bat_dau' => $baoHiemInfo['ngay_bat_dau'],
                'ngay_het_han' => $baoHiemInfo['ngay_het_han'],
                'noi_cap' => $baoHiemInfo['noi_cap'],
                'trang_thai' => $baoHiemInfo['trang_thai'],
                'huong_muc' => (float)$baoHiemInfo['huong_muc'],
                'con_han' => $conHan,
            ];
        }

        // Bước 5: Kiểm tra CCCD đã được đăng ký chưa
        if ($this->conn) {
            $checkQuery = "SELECT id, ten FROM benh_nhan WHERE cccd = :cccd LIMIT 1";
            $stmt = $this->conn->prepare($checkQuery);
            $stmt->bindParam(':cccd', $cccd);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $existingPatient = $stmt->fetch(PDO::FETCH_ASSOC);
                return [
                    'success' => false,
                    'message' => 'CCCD này đã được đăng ký.',
                    'verified' => true,
                    'already_registered' => true,
                    'cccd_data' => $cccdData,
                    'bao_hiem_y_te' => $baoHiemResult,
                ];
            }
        }

        // Bước 6: Xác thực thành công
        return [
            'success' => true,
            'message' => 'Xác thực CCCD thành công!',
            'verified' => true,
            'cccd_data' => $cccdData,
            // Thông tin bảo hiểm y tế (nếu tìm thấy)
            'bao_hiem_y_te' => $baoHiemResult,
        ];
    }

    /**
     * Chuẩn hóa tiếng Việt để so sánh
     */
    private function normalizeVietnamese($str)
    {
        $str = mb_strtolower($str, 'UTF-8');
        // Bỏ dấu
        $str = preg_replace('/[àáạảãâầấậẩẫăằắặẳẵ]/u', 'a', $str);
        $str = preg_replace('/[èéẹẻẽêềếệểễ]/u', 'e', $str);
        $str = preg_replace('/[ìíịỉĩ]/u', 'i', $str);
        $str = preg_replace('/[òóọỏõôồốộổỗơờớợởỡ]/u', 'o', $str);
        $str = preg_replace('/[ùúụủũưừứựửữ]/u', 'u', $str);
        $str = preg_replace('/[ỳýỵỷỹ]/u', 'y', $str);
        $str = preg_replace('/[đ]/u', 'd', $str);
        // Bỏ khoảng trắng thừa
        $str = preg_replace('/\s+/', ' ', $str);
        return trim($str);
    }

    /**
     * Extract thông tin từ CCCD
     */
    public function extractInfo($cccd)
    {
        if (!$this->validateFormat($cccd)['valid']) {
            return null;
        }

        $info = [
            'ma_tinh' => substr($cccd, 0, 3),
            'gioi_tinh_the_ky' => substr($cccd, 3, 1),
            'nam_sinh' => substr($cccd, 4, 2),
            'so_ngau_nhien' => substr($cccd, 6, 6)
        ];

        // Xác định giới tính (số 4)
        // 0,1,2: Nam sinh thế kỷ 20, 21, 22
        // 3,4,5: Nữ sinh thế kỷ 20, 21, 22
        $genderCode = (int)$info['gioi_tinh_the_ky'];
        $info['gioi_tinh'] = ($genderCode >= 0 && $genderCode <= 2) ? 'Nam' : 'Nữ';

        // Xác định năm sinh đầy đủ
        $century = 1900 + floor($genderCode / 3) * 100;
        $info['nam_sinh_day_du'] = $century + (int)$info['nam_sinh'];

        return $info;
    }

    /**
     * Lấy thông tin CCCD từ database
     */
    private function getCCCDFromDatabase($cccd)
    {
        try {
            $query = "SELECT cccd, ten, ngay_sinh, gioi_tinh, dia_chi, ngay_cap, noi_cap 
                      FROM " . self::TABLE_NAME . " 
                      WHERE cccd = :cccd AND trang_thai = 1 
                      LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':cccd', $cccd, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                // Chuyển đổi định dạng ngày về Y-m-d
                if ($result['ngay_sinh']) {
                    $result['ngay_sinh'] = date('Y-m-d', strtotime($result['ngay_sinh']));
                }
                if ($result['ngay_cap']) {
                    $result['ngay_cap'] = date('Y-m-d', strtotime($result['ngay_cap']));
                }
                return $result;
            }

            return null;
        } catch (PDOException $e) {
            error_log("Error getting CCCD from database: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy thông tin bảo hiểm y tế cho một người dựa trên số CCCD.
     * Match theo: ma_bao_hiem = cccd (số CCCD từ cccd_data).
     */
    private function getBaoHiemForCCCDData(array $cccdData)
    {
        if (!$this->conn) {
            return null;
        }

        // Kiểm tra có số CCCD không
        if (empty($cccdData['cccd'])) {
            return null;
        }

        try {
            // Match theo ma_bao_hiem = cccd (số CCCD)
            $sql = "SELECT id, ma_bao_hiem, loai_the, ten_chu_the, ngay_sinh, gioi_tinh, 
                           ngay_bat_dau, ngay_het_han, noi_cap, trang_thai, huong_muc
                    FROM bao_hiem_y_te
                    WHERE ma_bao_hiem = :cccd
                    LIMIT 1";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':cccd', $cccdData['cccd'], PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log('CCCDService::getBaoHiemForCCCDData - Found BHYT: ma_bao_hiem=' . $result['ma_bao_hiem'] . ' for CCCD=' . $cccdData['cccd']);
                return $result;
            }

            error_log('CCCDService::getBaoHiemForCCCDData - No BHYT found for CCCD=' . $cccdData['cccd']);
            return null;
        } catch (PDOException $e) {
            error_log('Error getting bao_hiem_y_te for CCCD: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy danh sách CCCD mẫu từ database để test
     */
    private function getSampleCCCDFromDatabase()
    {
        try {
            $query = "SELECT cccd FROM " . self::TABLE_NAME . " 
                      WHERE trang_thai = 1 
                      ORDER BY id 
                      LIMIT 10";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $results = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[] = $row['cccd'];
            }

            return $results;
        } catch (PDOException $e) {
            error_log("Error getting sample CCCD from database: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get list CCCD mẫu để test (public method)
     */
    public function getSampleCCCD()
    {
        return $this->getSampleCCCDFromDatabase();
    }

    /**
     * Get CCCD info for testing (public method)
     */
    public function getCCCDInfo($cccd)
    {
        return $this->getCCCDFromDatabase($cccd);
    }

    /**
     * Thêm CCCD mới vào database (dùng cho admin/testing)
     */
    public function addCCCD($cccd, $ten, $ngaySinh, $gioiTinh, $diaChi = null, $ngayCap = null, $noiCap = null)
    {
        try {
            // Validate format trước
            $formatCheck = $this->validateFormat($cccd);
            if (!$formatCheck['valid']) {
                return [
                    'success' => false,
                    'message' => $formatCheck['message']
                ];
            }

            // Kiểm tra CCCD đã tồn tại chưa
            $existing = $this->getCCCDFromDatabase($cccd);
            if ($existing) {
                return [
                    'success' => false,
                    'message' => 'CCCD đã tồn tại trong hệ thống'
                ];
            }

            $query = "INSERT INTO " . self::TABLE_NAME . " 
                      (cccd, ten, ngay_sinh, gioi_tinh, dia_chi, ngay_cap, noi_cap, trang_thai) 
                      VALUES (:cccd, :ten, :ngay_sinh, :gioi_tinh, :dia_chi, :ngay_cap, :noi_cap, 1)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':cccd', $cccd, PDO::PARAM_STR);
            $stmt->bindParam(':ten', $ten, PDO::PARAM_STR);
            $stmt->bindParam(':ngay_sinh', $ngaySinh, PDO::PARAM_STR);
            $stmt->bindParam(':gioi_tinh', $gioiTinh, PDO::PARAM_STR);
            $stmt->bindParam(':dia_chi', $diaChi, PDO::PARAM_STR);
            $stmt->bindParam(':ngay_cap', $ngayCap, PDO::PARAM_STR);
            $stmt->bindParam(':noi_cap', $noiCap, PDO::PARAM_STR);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Thêm CCCD thành công'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Lỗi khi thêm CCCD vào database'
                ];
            }
        } catch (PDOException $e) {
            error_log("Error adding CCCD to database: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi database: ' . $e->getMessage()
            ];
        }
    }
}