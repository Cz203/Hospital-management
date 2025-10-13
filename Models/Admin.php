<?php
require_once 'Models/User.php';

class Admin extends User
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = "quan_tri_vien";
    }

    public function login($email, $password)
    {
        // Đổi logic: tham số đầu dùng như số điện thoại để đăng nhập
        $raw = trim($email);
        $p1 = $raw;
        $p2 = $raw;
        if (str_starts_with($raw, '84')) {
            $p2 = '0' . substr($raw, 2);
        } elseif (str_starts_with($raw, '0')) {
            $p2 = '84' . substr($raw, 1);
        }
        $query = "SELECT * FROM " . $this->table_name . " WHERE so_dien_thoai = :p1 OR so_dien_thoai = :p2 LIMIT 1";
        $stmt = $this->getConnection()->prepare($query);
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
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function phoneExists($phone)
    {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE so_dien_thoai = :phone";
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bindParam(":phone", $phone);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function create($data)
    {
        // Kiểm tra email đã tồn tại chưa
        if ($this->emailExists($data['email'])) {
            throw new Exception("Email đã được sử dụng. Vui lòng chọn email khác.");
        }

        // Kiểm tra số điện thoại đã tồn tại chưa
        if ($this->phoneExists($data['so_dien_thoai'])) {
            throw new Exception("Số điện thoại đã được sử dụng. Vui lòng chọn số khác.");
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  (ten, email, mat_khau, so_dien_thoai, phone_verified, ngay_tao) 
                  VALUES (:ten, :email, :mat_khau, :so_dien_thoai, :phone_verified, NOW())";

        $stmt = $this->getConnection()->prepare($query);

        $hashedPassword = $this->hashPassword($data['mat_khau']);
        $phone_verified = $data['phone_verified'] ?? 0;

        $stmt->bindParam(":ten", $data['ten']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":mat_khau", $hashedPassword);
        $stmt->bindParam(":so_dien_thoai", $data['so_dien_thoai']);
        $stmt->bindParam(":phone_verified", $phone_verified);

        return $stmt->execute();
    }

    public function getAll()
    {
        $query = "SELECT id, ten, email, so_dien_thoai, ngay_tao FROM " . $this->table_name;
        $stmt = $this->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = "SELECT id, ten, email, so_dien_thoai, mat_khau, ngay_tao FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($id, $newPassword)
    {
        $query = "UPDATE " . $this->table_name . " SET mat_khau = :mat_khau WHERE id = :id";
        $stmt = $this->getConnection()->prepare($query);
        $hashedPassword = $this->hashPassword($newPassword);
        $stmt->bindParam(":mat_khau", $hashedPassword);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}