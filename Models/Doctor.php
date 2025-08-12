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

    public function create($data)
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  (ten, email, mat_khau, so_dien_thoai, chuyen_khoa, so_giay_phep, so_nam_kinh_nghiem, ngay_tao) 
                  VALUES (:ten, :email, :mat_khau, :so_dien_thoai, :chuyen_khoa, :so_giay_phep, :so_nam_kinh_nghiem, NOW())";

        $stmt = $this->conn->prepare($query);

        $hashedPassword = $this->hashPassword($data['password']);

        $stmt->bindParam(":ten", $data['name']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":mat_khau", $hashedPassword);
        $stmt->bindParam(":so_dien_thoai", $data['phone']);
        $stmt->bindParam(":chuyen_khoa", $data['specialization']);
        $stmt->bindParam(":so_giay_phep", $data['license_number']);
        $stmt->bindParam(":so_nam_kinh_nghiem", $data['experience_years']);

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
        $query = "SELECT id, ten, email, so_dien_thoai, chuyen_khoa, so_giay_phep, so_nam_kinh_nghiem, ngay_tao FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getBySpecialization($specialization)
    {
        $query = "SELECT id, ten, email, so_dien_thoai, chuyen_khoa, so_giay_phep, so_nam_kinh_nghiem FROM " . $this->table_name . " WHERE chuyen_khoa = :chuyen_khoa";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":chuyen_khoa", $specialization);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}