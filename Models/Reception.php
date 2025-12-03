<?php
require_once 'Models/User.php';

class Reception extends User
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'le_tan';
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

    public function create($data)
    {
        $fields = ['ten', 'email', 'mat_khau', 'so_dien_thoai'];
        $values = [':ten', ':email', ':mat_khau', ':so_dien_thoai'];

        // Thêm gioi_tinh nếu có trong data
        if (isset($data['gioi_tinh']) && $data['gioi_tinh'] !== '') {
            $fields[] = 'gioi_tinh';
            $values[] = ':gioi_tinh';
        }

        $query = "INSERT INTO " . $this->table_name . " (" . implode(', ', $fields) . ", ngay_tao) VALUES (" . implode(', ', $values) . ", NOW())";
        $stmt = $this->conn->prepare($query);
        $hashed = $this->hashPassword($data['mat_khau']);
        $stmt->bindParam(':ten', $data['ten']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':mat_khau', $hashed);
        $stmt->bindParam(':so_dien_thoai', $data['so_dien_thoai']);

        // Bind gioi_tinh nếu có
        if (isset($data['gioi_tinh']) && $data['gioi_tinh'] !== '') {
            $stmt->bindParam(':gioi_tinh', $data['gioi_tinh']);
        }

        return $stmt->execute();
    }

    /**
     * Cập nhật thông tin lễ tân
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE " . $this->table_name . " 
                SET ten = :ten, email = :email, so_dien_thoai = :so_dien_thoai";

        // Thêm gioi_tinh nếu có trong data
        if (isset($data['gioi_tinh'])) {
            $sql .= ", gioi_tinh = :gioi_tinh";
        }

        $sql .= ", ngay_cap_nhat = NOW() WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':ten', $data['ten']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':so_dien_thoai', $data['so_dien_thoai']);

        // Bind gioi_tinh nếu có
        if (isset($data['gioi_tinh'])) {
            $stmt->bindParam(':gioi_tinh', $data['gioi_tinh']);
        }

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Lấy danh sách tất cả lễ tân (cho Admin quản lý lịch làm việc)
     */
    public function getAll()
    {
        $query = "SELECT id, ten, email, so_dien_thoai, gioi_tinh, ngay_tao, ngay_cap_nhat 
        FROM " . $this->table_name . " 
        ORDER BY ten ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy 1 lễ tân theo ID
     */
    public function getById(int $id): ?array
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
    public function countAll(): int
    {
        $stmt = $this->conn->query("SELECT COUNT(*) AS c FROM " . $this->table_name);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['c'] ?? 0);
    }

    public function getPaginated(int $offset, int $limit): array
    {
        $sql = "SELECT id, ten, email, so_dien_thoai, gioi_tinh, ngay_tao, ngay_cap_nhat
                FROM " . $this->table_name . "
                ORDER BY ngay_tao DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Xóa lễ tân theo ID (dùng cho Admin)
     */
    public function deleteById(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}