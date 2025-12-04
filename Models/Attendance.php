<?php
require_once 'config/database.php';

class Attendance
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Lưu face encoding cho user
     */
    public function saveFaceEncoding($userId, $userType, $faceEncoding, $sampleImagePath = null)
    {
        try {
            // Kiểm tra xem đã có encoding chưa
            $checkSql = "SELECT id FROM face_encodings WHERE user_id = ? AND user_type = ? AND is_active = 1";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$userId, $userType]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Cập nhật encoding hiện có
                $sql = "UPDATE face_encodings 
                        SET face_encoding = ?, sample_image_path = ?, ngay_cap_nhat = NOW() 
                        WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([$faceEncoding, $sampleImagePath, $existing['id']]);
                if (!$result) {
                    $errorInfo = $stmt->errorInfo();
                    error_log("SQL Error updating face_encodings: " . print_r($errorInfo, true));
                }
            } else {
                // Tạo mới
                $sql = "INSERT INTO face_encodings (user_id, user_type, face_encoding, sample_image_path, is_active, ngay_tao) 
                        VALUES (?, ?, ?, ?, 1, NOW())";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([$userId, $userType, $faceEncoding, $sampleImagePath]);
                if (!$result) {
                    $errorInfo = $stmt->errorInfo();
                    error_log("SQL Error inserting face_encodings: " . print_r($errorInfo, true));
                }
            }

            // Cập nhật vào bảng tương ứng (bac_si hoặc le_tan)
            if ($userType === 'doctor') {
                $updateSql = "UPDATE bac_si SET face_encoding = ?, face_encoding_updated = NOW() WHERE id = ?";
            } elseif ($userType === 'reception') {
                $updateSql = "UPDATE le_tan SET face_encoding = ?, face_encoding_updated = NOW() WHERE id = ?";
            } else {
                $updateSql = "UPDATE quan_tri_vien SET face_encoding = ?, face_encoding_updated = NOW() WHERE id = ?";
            }

            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([$faceEncoding, $userId]);

            return true;
        } catch (Exception $e) {
            error_log("Error saving face encoding: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy face encoding của user
     */
    public function getFaceEncoding($userId, $userType)
    {
        try {
            $sql = "SELECT face_encoding, sample_image_path FROM face_encodings 
                    WHERE user_id = ? AND user_type = ? AND is_active = 1 
                    ORDER BY ngay_cap_nhat DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $userType]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting face encoding: " . $e->getMessage());
            return null;
        }
    }

    /**
     * So sánh face encoding với face encoding của user cụ thể
     */
    public function compareFaceWithUser($faceEncoding, $userId, $userType)
    {
        try {
            // Lấy face encoding của user từ database
            $storedEncoding = $this->getFaceEncoding($userId, $userType);

            if (!$storedEncoding || empty($storedEncoding['face_encoding'])) {
                return ['match' => false, 'distance' => null, 'message' => 'Chưa đăng ký khuôn mặt'];
            }

            // Parse input encoding
            $inputEncoding = null;
            if (is_string($faceEncoding)) {
                $inputEncoding = json_decode($faceEncoding, true);
            } else if (is_array($faceEncoding)) {
                $inputEncoding = $faceEncoding;
            }

            if (!$inputEncoding || !is_array($inputEncoding)) {
                return ['match' => false, 'distance' => null, 'message' => 'Face encoding không hợp lệ'];
            }

            // Parse stored encoding
            $storedEncodingArray = json_decode($storedEncoding['face_encoding'], true);
            if (!$storedEncodingArray || !is_array($storedEncodingArray)) {
                return ['match' => false, 'distance' => null, 'message' => 'Face encoding đã lưu không hợp lệ'];
            }

            // Tính khoảng cách Euclidean
            $distance = $this->euclideanDistance($inputEncoding, $storedEncodingArray);

            // Threshold cho face-api.js: thường là 0.4-0.5, nhưng để linh hoạt hơn dùng 0.5
            $threshold = 0.5;

            $match = $distance < $threshold;

            return [
                'match' => $match,
                'distance' => $distance,
                'threshold' => $threshold,
                'confidence' => $match ? (1 - ($distance / $threshold)) : 0,
                'message' => $match ? 'Khớp' : 'Không khớp'
            ];
        } catch (Exception $e) {
            error_log("Error comparing face: " . $e->getMessage());
            return ['match' => false, 'distance' => null, 'message' => 'Lỗi khi so sánh: ' . $e->getMessage()];
        }
    }

    /**
     * So sánh face encoding và tìm user khớp nhất
     */
    public function recognizeFace($faceEncoding, $userType = null)
    {
        try {
            $sql = "SELECT user_id, user_type, face_encoding FROM face_encodings WHERE is_active = 1";
            $params = [];

            if ($userType) {
                $sql .= " AND user_type = ?";
                $params[] = $userType;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $encodings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $bestMatch = null;
            $bestDistance = PHP_FLOAT_MAX;
            $threshold = 0.5; // Ngưỡng độ tương đồng (điều chỉnh từ 0.6 xuống 0.5)

            // Parse input encoding
            $inputEncoding = null;
            if (is_string($faceEncoding)) {
                $inputEncoding = json_decode($faceEncoding, true);
            } else if (is_array($faceEncoding)) {
                $inputEncoding = $faceEncoding;
            }

            if (!$inputEncoding || !is_array($inputEncoding)) {
                return null;
            }

            foreach ($encodings as $encoding) {
                $storedEncoding = json_decode($encoding['face_encoding'], true);
                if (!$storedEncoding || !is_array($storedEncoding)) {
                    continue;
                }

                // Tính khoảng cách Euclidean
                $distance = $this->euclideanDistance($inputEncoding, $storedEncoding);

                if ($distance < $bestDistance && $distance < $threshold) {
                    $bestDistance = $distance;
                    $bestMatch = [
                        'user_id' => $encoding['user_id'],
                        'user_type' => $encoding['user_type'],
                        'confidence' => 1 - ($distance / $threshold), // Chuyển đổi thành confidence (0-1)
                        'distance' => $distance
                    ];
                }
            }

            return $bestMatch;
        } catch (Exception $e) {
            error_log("Error recognizing face: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tính khoảng cách Euclidean giữa 2 face encodings
     */
    private function euclideanDistance($encoding1, $encoding2)
    {
        if (count($encoding1) !== count($encoding2)) {
            return PHP_FLOAT_MAX;
        }

        $sum = 0;
        for ($i = 0; $i < count($encoding1); $i++) {
            $diff = $encoding1[$i] - $encoding2[$i];
            $sum += $diff * $diff;
        }

        return sqrt($sum);
    }

    /**
     * Chấm công check-in
     * Áp dụng cho cả bác sĩ (doctor) và lễ tân (reception)
     * 
     * @param int $userId ID của user
     * @param string $userType Loại user: 'doctor' hoặc 'reception'
     * @param string $faceEncoding Face encoding JSON
     * @param string|null $imagePath Đường dẫn ảnh check-in
     * @param string|null $location Vị trí GPS
     * @return array Kết quả check-in
     */
    public function checkIn($userId, $userType, $faceEncoding, $imagePath = null, $location = null)
    {
        try {
            $today = date('Y-m-d');
            $now = date('Y-m-d H:i:s');

            // Kiểm tra xem đã check-in hôm nay chưa
            $checkSql = "SELECT id, status FROM attendance 
                         WHERE user_id = ? AND user_type = ? AND DATE(check_in_time) = ? 
                         ORDER BY check_in_time DESC LIMIT 1";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$userId, $userType, $today]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existing && $existing['status'] === 'checked_in') {
                return [
                    'success' => false,
                    'message' => 'Bạn đã check-in hôm nay rồi!'
                ];
            }

            // Tính trễ dựa trên ca làm việc (áp dụng cho cả bác sĩ và lễ tân)
            // Ca 1: 7h - 11h30 (check-in sau 7h10 = trễ)
            // Ca 2: 13h - 21h (check-in sau 13h10 = trễ)
            $checkInTime = date('H:i:s');
            $checkInHour = (int)date('H');
            $checkInMinute = (int)date('i');
            $isLate = 0; // 0 = không trễ, 1 = trễ
            $shiftName = '';
            $lateMessage = '';

            // Xác định ca và tính trễ (áp dụng cho cả doctor và reception)
            if ($checkInHour >= 7 && $checkInHour < 13) {
                // Ca 1: 7h - 11h30
                $shiftName = 'Ca 1 (7h - 11h30)';
                // Trễ nếu sau 7h10
                if ($checkInHour > 7 || ($checkInHour == 7 && $checkInMinute > 10)) {
                    $isLate = 1;
                    $minutesLate = ($checkInHour - 7) * 60 + ($checkInMinute - 10);
                    $lateMessage = "Bạn đã check-in trễ {$minutesLate} phút so với giờ quy định (7h10).";
                } else {
                    $lateMessage = "Bạn đã check-in đúng giờ hoặc sớm.";
                }
            } elseif ($checkInHour >= 13 && $checkInHour < 21) {
                // Ca 2: 13h - 21h
                $shiftName = 'Ca 2 (13h - 21h)';
                // Trễ nếu sau 13h10
                if ($checkInHour > 13 || ($checkInHour == 13 && $checkInMinute > 10)) {
                    $isLate = 1;
                    $minutesLate = ($checkInHour - 13) * 60 + ($checkInMinute - 10);
                    $lateMessage = "Bạn đã check-in trễ {$minutesLate} phút so với giờ quy định (13h10).";
                } else {
                    $lateMessage = "Bạn đã check-in đúng giờ hoặc sớm.";
                }
            } else {
                // Ngoài ca
                $shiftName = 'Ngoài ca làm việc';
                $lateMessage = "Bạn check-in ngoài giờ ca làm việc.";
            }

            // Lưu vào bảng attendance
            $sql = "INSERT INTO attendance (user_id, user_type, check_in_time, check_in_image, status, location, ngay_tao) 
                    VALUES (?, ?, ?, ?, 'checked_in', ?, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $userType, $now, $imagePath, $location]);

            // Lưu vào bảng cham_cong (bao gồm trạng thái trễ)
            $chamCongSql = "INSERT INTO cham_cong (user_id, user_type, ngay_cham, gio_vao, trang_thai, tre, face_id_data, dia_diem, ngay_tao) 
                           VALUES (?, ?, ?, ?, 'check_in', ?, ?, ?, NOW())
                           ON DUPLICATE KEY UPDATE 
                           gio_vao = VALUES(gio_vao), 
                           trang_thai = 'check_in',
                           tre = VALUES(tre),
                           face_id_data = VALUES(face_id_data),
                           dia_diem = VALUES(dia_diem),
                           ngay_cap_nhat = NOW()";
            $faceIdData = json_encode([
                'recognized_user_id' => $userId,
                'confidence' => 1.0,
                'recognized_at' => $now
            ]);
            $chamCongStmt = $this->db->prepare($chamCongSql);
            $chamCongStmt->execute([$userId, $userType, $today, $checkInTime, $isLate, $faceIdData, $location]);

            // Tạo thông báo chi tiết
            $message = 'Check-in thành công!';
            if ($isLate) {
                $message .= ' ⚠️ ' . $lateMessage;
            } else {
                $message .= ' ✅ ' . $lateMessage;
            }
            if (!empty($shiftName)) {
                $message .= ' [' . $shiftName . ']';
            }

            return [
                'success' => true,
                'message' => $message,
                'check_in_time' => $now,
                'check_in_time_formatted' => $checkInTime,
                'is_late' => $isLate,
                'shift' => $shiftName,
                'late_message' => $lateMessage
            ];
        } catch (Exception $e) {
            error_log("Error checking in: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi khi check-in: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Chấm công check-out
     */
    public function checkOut($userId, $userType, $faceEncoding, $imagePath = null, $location = null)
    {
        try {
            $today = date('Y-m-d');
            $now = date('Y-m-d H:i:s');

            // Kiểm tra xem đã check-in chưa
            $checkSql = "SELECT id, check_in_time FROM attendance 
                         WHERE user_id = ? AND user_type = ? AND DATE(check_in_time) = ? 
                         AND status = 'checked_in'
                         ORDER BY check_in_time DESC LIMIT 1";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$userId, $userType, $today]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Bạn chưa check-in hôm nay!'
                ];
            }

            // Cập nhật attendance
            $sql = "UPDATE attendance 
                    SET check_out_time = ?, check_out_image = ?, status = 'checked_out', ngay_cap_nhat = NOW() 
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$now, $imagePath, $existing['id']]);

            // Cập nhật cham_cong
            // Nếu chưa có dia_diem khi check-in, cập nhật khi check-out
            $chamCongSql = "UPDATE cham_cong 
                           SET gio_ra = ?, trang_thai = 'completed', 
                               dia_diem = COALESCE(dia_diem, ?), 
                               ngay_cap_nhat = NOW() 
                           WHERE user_id = ? AND user_type = ? AND ngay_cham = ?";
            $chamCongStmt = $this->db->prepare($chamCongSql);
            $chamCongStmt->execute([date('H:i:s'), $location, $userId, $userType, $today]);

            return [
                'success' => true,
                'message' => 'Check-out thành công!',
                'check_out_time' => $now
            ];
        } catch (Exception $e) {
            error_log("Error checking out: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi khi check-out: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lấy lịch sử chấm công
     */
    public function getAttendanceHistory($userId, $userType, $limit = 30)
    {
        try {
            $sql = "SELECT * FROM attendance 
                    WHERE user_id = ? AND user_type = ? 
                    ORDER BY check_in_time DESC 
                    LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $userType, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting attendance history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy trạng thái chấm công hôm nay (bao gồm thông tin trễ)
     */
    public function getTodayStatus($userId, $userType)
    {
        try {
            $today = date('Y-m-d');
            $sql = "SELECT a.*, cc.tre as is_late 
                    FROM attendance a
                    LEFT JOIN cham_cong cc ON a.user_id = cc.user_id 
                        AND a.user_type = cc.user_type 
                        AND DATE(a.check_in_time) = cc.ngay_cham
                    WHERE a.user_id = ? AND a.user_type = ? AND DATE(a.check_in_time) = ? 
                    ORDER BY a.check_in_time DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $userType, $today]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting today status: " . $e->getMessage());
            return null;
        }
    }
}