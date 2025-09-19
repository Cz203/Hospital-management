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
        $query = "INSERT INTO " . $this->table_name . " 
                  (ten, email, mat_khau, so_dien_thoai, phone_verified, ngay_sinh, gioi_tinh, dia_chi, nhom_mau, ngay_tao) 
                  VALUES (:ten, :email, :mat_khau, :so_dien_thoai, :phone_verified, :ngay_sinh, :gioi_tinh, :dia_chi, :nhom_mau, NOW())";

        $stmt = $this->conn->prepare($query);

        $hashedPassword = $this->hashPassword($data['mat_khau']);
        $phone_verified = $data['phone_verified'] ?? 0;

        $stmt->bindParam(":ten", $data['ten']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":mat_khau", $hashedPassword);
        $stmt->bindParam(":so_dien_thoai", $data['so_dien_thoai']);
        $stmt->bindParam(":phone_verified", $phone_verified);
        $stmt->bindParam(":ngay_sinh", $data['ngay_sinh']);
        $stmt->bindParam(":gioi_tinh", $data['gioi_tinh']);
        $stmt->bindParam(":dia_chi", $data['dia_chi']);
        $stmt->bindParam(":nhom_mau", $data['nhom_mau']);

        return $stmt->execute();
    }

    public function getAll()
    {
        $query = "SELECT id, ten, email, so_dien_thoai, ngay_sinh, gioi_tinh, dia_chi, nhom_mau, ngay_tao FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = "SELECT id, ten, email, so_dien_thoai, ngay_sinh, gioi_tinh, dia_chi, nhom_mau, mat_khau, ngay_tao, ngay_cap_nhat, bao_hiem_y_te, bao_hiem_y_te_id FROM " . $this->table_name . " WHERE id = :id";
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
        $stmt->bindParam(":nhom_mau", $data['nhom_mau']);

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
        $stmt->bindParam(":nhom_mau", $data['nhom_mau']);

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
        $query = "SELECT id, ten, email, so_dien_thoai, ngay_sinh, gioi_tinh, dia_chi, nhom_mau, mat_khau, ngay_tao FROM " . $this->table_name . " WHERE so_dien_thoai = :phone LIMIT 1";
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
}
