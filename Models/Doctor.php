<?php
require_once 'Models/User.php';

class Doctor extends User
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = "bac_si";
    }

    public function login($email, $password)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
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
        // Kiểm tra email đã tồn tại chưa
        if ($this->emailExists($data['email'])) {
            throw new Exception("Email đã được sử dụng. Vui lòng chọn email khác.");
        }

        // Kiểm tra số điện thoại đã tồn tại chưa
        if ($this->phoneExists($data['so_dien_thoai'])) {
            throw new Exception("Số điện thoại đã được sử dụng. Vui lòng chọn số khác.");
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  (ten, email, mat_khau, so_dien_thoai, phone_verified, chuyen_khoa, so_giay_phep, so_nam_kinh_nghiem, hinh_anh, ngay_tao) 
                  VALUES (:ten, :email, :mat_khau, :so_dien_thoai, :phone_verified, :chuyen_khoa, :so_giay_phep, :so_nam_kinh_nghiem, :hinh_anh, NOW())";

        $stmt = $this->conn->prepare($query);

        $hashedPassword = $this->hashPassword($data['mat_khau']);
        $phone_verified = $data['phone_verified'] ?? 0;

        $stmt->bindParam(":ten", $data['ten']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":mat_khau", $hashedPassword);
        $stmt->bindParam(":so_dien_thoai", $data['so_dien_thoai']);
        $stmt->bindParam(":phone_verified", $phone_verified);
        $stmt->bindParam(":chuyen_khoa", $data['chuyen_khoa']);
        $stmt->bindParam(":so_giay_phep", $data['so_giay_phep']);
        $stmt->bindParam(":so_nam_kinh_nghiem", $data['so_nam_kinh_nghiem']);
        $stmt->bindParam(":hinh_anh", $data['hinh_anh'] ?? null);

        return $stmt->execute();
    }

    public function getAll()
    {
        $query = "SELECT id, ten, email, so_dien_thoai, chuyen_khoa, so_giay_phep, so_nam_kinh_nghiem, ngay_tao, hinh_anh FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = "SELECT id, ten, email, so_dien_thoai, chuyen_khoa, so_giay_phep, so_nam_kinh_nghiem, mat_khau, ngay_tao, ngay_cap_nhat FROM " . $this->table_name . " WHERE id = :id";
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

    public function getBySpecialization($specialization)
    {
        $query = "SELECT id, ten, email, so_dien_thoai, chuyen_khoa, so_giay_phep, so_nam_kinh_nghiem FROM " . $this->table_name . " WHERE chuyen_khoa = :chuyen_khoa";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":chuyen_khoa", $specialization);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateImage($id, $imageName)
    {
        $query = "UPDATE " . $this->table_name . " SET hinh_anh = :hinh_anh WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":hinh_anh", $imageName);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function updateProfile($id, $data)
    {
        $query = "UPDATE " . $this->table_name . " SET 
                  ten = :ten, 
                  email = :email, 
                  so_dien_thoai = :so_dien_thoai, 
                  chuyen_khoa = :chuyen_khoa, 
                  so_giay_phep = :so_giay_phep, 
                  so_nam_kinh_nghiem = :so_nam_kinh_nghiem,
                  ngay_cap_nhat = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ten", $data['ten']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":so_dien_thoai", $data['so_dien_thoai']);
        $stmt->bindParam(":chuyen_khoa", $data['chuyen_khoa']);
        $stmt->bindParam(":so_giay_phep", $data['so_giay_phep']);
        $stmt->bindParam(":so_nam_kinh_nghiem", $data['so_nam_kinh_nghiem']);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }
}
