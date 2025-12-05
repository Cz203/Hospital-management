<?php

require_once 'Models/User.php';
require_once 'Models/Admin.php';
require_once 'Models/Doctor.php';
require_once 'Models/Patient.php';
require_once 'Models/Reception.php';
require_once 'config/database.php';

class UserController
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Lấy trạng thái truy cập của user theo role
     * @param int $userId
     * @param string $userType - 'admin', 'doctor', 'letan', 'patient'
     * @return array|null
     */
    public function getAccessStatus($userId, $userType)
    {
        try {
            $tableName = $this->getTableNameByRole($userType);
            if (!$tableName) {
                return null;
            }

            $sql = "SELECT trang_thai_truy_cap FROM {$tableName} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? [
                'trang_thai_truy_cap' => $result['trang_thai_truy_cap'] ?? 'active',
                'user_id' => $userId,
                'user_type' => $userType
            ] : null;
        } catch (Exception $e) {
            error_log("Error getting access status: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cập nhật trạng thái truy cập
     * @param int $userId
     * @param string $userType
     * @param string $status - 'active', 'inactive', 'blocked', etc.
     * @return bool
     */
    public function updateAccessStatus($userId, $userType, $status)
    {
        try {
            $tableName = $this->getTableNameByRole($userType);
            if (!$tableName) {
                return false;
            }

            // Validate status
            $allowedStatuses = ['active', 'inactive', 'blocked', 'suspended'];
            if (!in_array($status, $allowedStatuses)) {
                return false;
            }

            $sql = "UPDATE {$tableName} 
                    SET trang_thai_truy_cap = :status, 
                        ngay_cap_nhat = NOW() 
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating access status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kiểm tra user có được phép truy cập không
     * @param int $userId
     * @param string $userType
     * @return bool
     */
    public function canAccess($userId, $userType)
    {
        $status = $this->getAccessStatus($userId, $userType);
        if (!$status) {
            return false;
        }

        return $status['trang_thai_truy_cap'] === 'active';
    }

    /**
     * Lấy tên bảng theo role
     * @param string $userType
     * @return string|null
     */
    private function getTableNameByRole($userType)
    {
        $tableMap = [
            'admin' => 'quan_tri_vien',
            'doctor' => 'bac_si',
            'xray_doctor' => 'bac_si',
            'sieuam_doctor' => 'bac_si',
            'xetnghiem_doctor' => 'bac_si',
            'letan' => 'le_tan',
            'patient' => 'benh_nhan'
        ];

        return $tableMap[$userType] ?? null;
    }

    /**
     * Lấy danh sách user theo role với trạng thái truy cập
     * @param string $userType
     * @param string|null $status - Filter theo status (optional)
     * @return array
     */
    public function getUsersByRole($userType, $status = null)
    {
        try {
            $tableName = $this->getTableNameByRole($userType);
            if (!$tableName) {
                return [];
            }

            $sql = "SELECT id, ten, email, so_dien_thoai, trang_thai_truy_cap, ngay_tao, ngay_cap_nhat 
                    FROM {$tableName}";

            $params = [];
            if ($status !== null) {
                $sql .= " WHERE trang_thai_truy_cap = :status";
                $params[':status'] = $status;
            }

            $sql .= " ORDER BY ngay_tao DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting users by role: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Middleware: Kiểm tra trạng thái truy cập trước khi cho phép action
     * @param int $userId
     * @param string $userType
     * @return bool - true nếu được phép, false nếu bị chặn
     */
    public function checkAccessBeforeAction($userId, $userType)
    {
        if (!$this->canAccess($userId, $userType)) {
            // Có thể log hoặc thực hiện action khác
            error_log("User {$userId} ({$userType}) bị chặn truy cập do trạng thái không active");
            return false;
        }
        return true;
    }
}
