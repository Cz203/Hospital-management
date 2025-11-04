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
     * Fake CCCD database (giả lập cơ sở dữ liệu CCCD thực tế)
     * Trong thực tế, đây sẽ là API call đến hệ thống Công an
     */
    private const FAKE_CCCD_DATABASE = [
        '001234567890' => [
            'ten' => 'Cao Dương Quốc Việt',
            'ngay_sinh' => '2003-03-22',
            'gioi_tinh' => 'Nam',
            'dia_chi' => 'An Giang',
            'ngay_cap' => '2020-01-01',
            'noi_cap' => 'Cục cảnh sát ĐKQL cư trú và DLQG về dân cư'
        ],
        '001234567891' => [
            'ten' => 'Ung Nguyễn Trường Thịnh',
            'ngay_sinh' => '2003-03-22',
            'gioi_tinh' => 'Nam',
            'dia_chi' => 'Hồ Chí Minh',
            'ngay_cap' => '2020-06-15',
            'noi_cap' => 'Cục cảnh sát ĐKQL cư trú và DLQG về dân cư'
        ],
        '003456789012' => [
            'ten' => 'Lê Văn C',
            'ngay_sinh' => '1988-12-10',
            'gioi_tinh' => 'Nam',
            'dia_chi' => 'Đà Nẵng',
            'ngay_cap' => '2019-03-20',
            'noi_cap' => 'Cục cảnh sát ĐKQL cư trú và DLQG về dân cư'
        ]
    ];

    public function __construct($db = null)
    {
        $this->conn = $db;
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
     * Trong thực tế sẽ gọi API của Bộ Công an
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

        // Bước 2: Kiểm tra trong fake database (giả lập API call)
        if (!isset(self::FAKE_CCCD_DATABASE[$cccd])) {
            return [
                'success' => false,
                'message' => 'CCCD không tồn tại trong hệ thống. Vui lòng kiểm tra lại!',
                'verified' => false,
                'suggestion' => 'CCCD mẫu để test: ' . implode(', ', array_keys(self::FAKE_CCCD_DATABASE))
            ];
        }

        $cccdData = self::FAKE_CCCD_DATABASE[$cccd];

        // Bước 3: Verify thông tin (nếu có)
        if ($ten !== null) {
            // So sánh tên (case-insensitive, bỏ dấu)
            $tenNormalized = $this->normalizeVietnamese($ten);
            $cccdTenNormalized = $this->normalizeVietnamese($cccdData['ten']);

            if ($tenNormalized !== $cccdTenNormalized) {
                return [
                    'success' => false,
                    'message' => 'Họ tên không khớp với CCCD. CCCD này thuộc về: ' . $cccdData['ten'],
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

        // Bước 4: Kiểm tra CCCD đã được đăng ký chưa
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
                    'already_registered' => true
                ];
            }
        }

        // Bước 5: Xác thực thành công
        return [
            'success' => true,
            'message' => 'Xác thực CCCD thành công!',
            'verified' => true,
            'cccd_data' => $cccdData
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
     * Get list CCCD mẫu để test
     */
    public static function getSampleCCCD()
    {
        return array_keys(self::FAKE_CCCD_DATABASE);
    }

    /**
     * Get CCCD info for testing
     */
    public static function getCCCDInfo($cccd)
    {
        return self::FAKE_CCCD_DATABASE[$cccd] ?? null;
    }
}