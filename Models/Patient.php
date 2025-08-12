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

    public function create($data)
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  (ten, email, mat_khau, so_dien_thoai, ngay_sinh, gioi_tinh, dia_chi, nhom_mau, ngay_tao) 
                  VALUES (:ten, :email, :mat_khau, :so_dien_thoai, :ngay_sinh, :gioi_tinh, :dia_chi, :nhom_mau, NOW())";

        $stmt = $this->conn->prepare($query);

        $hashedPassword = $this->hashPassword($data['password']);

        $stmt->bindParam(":ten", $data['name']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":mat_khau", $hashedPassword);
        $stmt->bindParam(":so_dien_thoai", $data['phone']);
        $stmt->bindParam(":ngay_sinh", $data['date_of_birth']);
        $stmt->bindParam(":gioi_tinh", $data['gender']);
        $stmt->bindParam(":dia_chi", $data['address']);
        $stmt->bindParam(":nhom_mau", $data['blood_group']);

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
        $query = "SELECT id, ten, email, so_dien_thoai, ngay_sinh, gioi_tinh, dia_chi, nhom_mau, ngay_tao FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($id, $data)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET ten = :ten, so_dien_thoai = :so_dien_thoai, ngay_sinh = :ngay_sinh, 
                      gioi_tinh = :gioi_tinh, dia_chi = :dia_chi, nhom_mau = :nhom_mau 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":ten", $data['name']);
        $stmt->bindParam(":so_dien_thoai", $data['phone']);
        $stmt->bindParam(":ngay_sinh", $data['date_of_birth']);
        $stmt->bindParam(":gioi_tinh", $data['gender']);
        $stmt->bindParam(":dia_chi", $data['address']);
        $stmt->bindParam(":nhom_mau", $data['blood_group']);

        return $stmt->execute();
    }
}
