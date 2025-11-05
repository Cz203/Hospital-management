<?php
require_once 'Models/User.php';

class Patient extends User
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = "benh_nhan";
    }

    public function login($email, $password)
    {
        // Đổi logic: tham số đầu dùng như số điện thoại để đăng nhập, chấp nhận 84/0
        $raw = trim($email);
        $p1 = $raw;
        $p2 = $raw;
        if (str_starts_with($raw, '84')) {
            $p2 = '0' . substr($raw, 2);
        } elseif (str_starts_with($raw, '0')) {
            $p2 = '84' . substr($raw, 1);
        }
        $query = "SELECT * FROM " . $this->table_name . " WHERE so_dien_thoai = :p1 OR so_dien_thoai = :p2 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":p1", $p1);
        $stmt->bindParam(":p2", $p2);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($this->verifyPassword($password, $row['mat_khau'])) {
                return $row;
            }
        }
        return false;
    }

    public function emailExists($email)
    {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function phoneExists($phone)
    {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE so_dien_thoai = :phone";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":phone", $phone);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function create($data)
    {

        // Sinh mã bệnh nhân tự động
        $ma_benh_nhan = 'BN' . date('ymd') . rand(10, 99);
        $query = "INSERT INTO " . $this->table_name . " 
                  (ma_benh_nhan, ten, email, mat_khau, so_dien_thoai, phone_verified, ngay_sinh, gioi_tinh, dia_chi, cccd, ngay_tao) 
                  VALUES (:ma_benh_nhan, :ten, :email, :mat_khau, :so_dien_thoai, :phone_verified, :ngay_sinh, :gioi_tinh, :dia_chi, :cccd, NOW())";

        $stmt = $this->conn->prepare($query);

        $hashedPassword = $this->hashPassword($data['mat_khau']);
        $phone_verified = $data['phone_verified'] ?? 0;
        $cccd = $data['cccd'] ?? null;

        $stmt->bindParam(":ma_benh_nhan", $ma_benh_nhan);
        $stmt->bindParam(":ten", $data['ten']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":mat_khau", $hashedPassword);
        $stmt->bindParam(":so_dien_thoai", $data['so_dien_thoai']);
        $stmt->bindParam(":phone_verified", $phone_verified);
        $stmt->bindParam(":ngay_sinh", $data['ngay_sinh']);
        $stmt->bindParam(":gioi_tinh", $data['gioi_tinh']);
        $stmt->bindParam(":dia_chi", $data['dia_chi']);
        $stmt->bindParam(":cccd", $cccd);

        return $stmt->execute();
    }

    public function getAll()
    {
        $query = "SELECT id, ten, email, so_dien_thoai, ngay_sinh, gioi_tinh, dia_chi, cccd, ngay_tao FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = "SELECT id, ten, email, so_dien_thoai, ngay_sinh, gioi_tinh, dia_chi, cccd, mat_khau, ngay_tao, ngay_cap_nhat, bao_hiem_y_te, bao_hiem_y_te_id FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($id, $newPassword)
    {
        $query = "UPDATE " . $this->table_name . " SET mat_khau = :mat_khau WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $hashedPassword = $this->hashPassword($newPassword);
        $stmt->bindParam(":mat_khau", $hashedPassword);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function updateProfile($id, $data)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET ten = :ten, so_dien_thoai = :so_dien_thoai, ngay_sinh = :ngay_sinh, 
                      gioi_tinh = :gioi_tinh, dia_chi = :dia_chi, nhom_mau = :nhom_mau 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":ten", $data['ten']);
        $stmt->bindParam(":so_dien_thoai", $data['so_dien_thoai']);
        $stmt->bindParam(":ngay_sinh", $data['ngay_sinh']);
        $stmt->bindParam(":gioi_tinh", $data['gioi_tinh']);
        $stmt->bindParam(":dia_chi", $data['dia_chi']);
        $stmt->bindParam(":cccd", $data['cccd']);

        return $stmt->execute();
    }

    public function updateProfileWithEmail($id, $data)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET ten = :ten, email = :email, ngay_sinh = :ngay_sinh, 
                      gioi_tinh = :gioi_tinh, dia_chi = :dia_chi, nhom_mau = :nhom_mau, 
                      ngay_cap_nhat = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":ten", $data['ten']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":ngay_sinh", $data['ngay_sinh']);
        $stmt->bindParam(":gioi_tinh", $data['gioi_tinh']);
        $stmt->bindParam(":dia_chi", $data['dia_chi']);
        $stmt->bindParam(":cccd", $data['cccd']);

        return $stmt->execute();
    }

    public function updateImage($id, $imagePath)
    {
        $query = "UPDATE " . $this->table_name . " SET hinh_anh = :hinh_anh, ngay_cap_nhat = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":hinh_anh", $imagePath);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getByPhone($phone)
    {
        $query = "SELECT id, bao_hiem_y_te, ten, email, so_dien_thoai, ngay_sinh, gioi_tinh, dia_chi, cccd, mat_khau, ngay_tao FROM " . $this->table_name . " WHERE so_dien_thoai = :phone LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":phone", $phone);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePasswordById($id, $hashedPassword)
    {
        $query = "UPDATE " . $this->table_name . " SET mat_khau = :mat_khau WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":mat_khau", $hashedPassword);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    /**
     * Bổ sung các trường còn thiếu: chỉ update nếu cột hiện tại đang NULL hoặc rỗng
     */
    public function completeMissingFields(int $patientId, array $fields): bool
    {
        if ($patientId <= 0 || empty($fields)) {
            return false;
        }

        // Lấy bản ghi hiện tại
        $current = $this->getById($patientId);
        if (!$current) return false;

        $allowed = ['email', 'ngay_sinh', 'gioi_tinh', 'dia_chi', 'cccd', 'bao_hiem_y_te', 'bao_hiem_y_te_id'];
        $updates = [];
        $params = [':id' => $patientId];

        foreach ($allowed as $col) {
            if (!array_key_exists($col, $fields)) continue;
            $newVal = $fields[$col];
            $curVal = isset($current[$col]) ? $current[$col] : null;
            $isEmpty = ($curVal === null) || (trim((string)$curVal) === '');
            if ($isEmpty) {
                $updates[] = "$col = :$col";
                if ($col === 'bao_hiem_y_te_id') {
                    $params[":$col"] = (int)$newVal ?: null;
                } else {
                    $params[":$col"] = $newVal;
                }
            }
        }

        if (empty($updates)) return false;

        $sql = "UPDATE " . $this->table_name . " SET " . implode(', ', $updates) . ", ngay_cap_nhat = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Cho phép lễ tân cập nhật hoặc xóa (set NULL) các trường cho phép.
     * Nếu giá trị truyền vào là chuỗi rỗng => set NULL.
     */
    public function updateFieldsByReception(int $patientId, array $fields): bool
    {
        if ($patientId <= 0) return false;

        $allowed = ['email', 'ngay_sinh', 'gioi_tinh', 'dia_chi', 'cccd', 'bao_hiem_y_te'];
        $updates = [];
        $params = [':id' => $patientId];

        foreach ($allowed as $col) {
            if (!array_key_exists($col, $fields)) continue;
            $val = $fields[$col];
            if ($val === '') {
                // Xóa -> NULL
                $updates[] = "$col = NULL";
            } else {
                $updates[] = "$col = :$col";
                $params[":$col"] = $val;
            }
        }

        if (empty($updates)) return false;

        $sql = "UPDATE " . $this->table_name . " SET " . implode(', ', $updates) . ", ngay_cap_nhat = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
}
