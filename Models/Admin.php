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
                  (ten, email, mat_khau, so_dien_thoai, ngay_tao) 
                  VALUES (:ten, :email, :mat_khau, :so_dien_thoai, NOW())";

        $stmt = $this->conn->prepare($query);

        $hashedPassword = $this->hashPassword($data['password']);

        $stmt->bindParam(":ten", $data['name']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":mat_khau", $hashedPassword);
        $stmt->bindParam(":so_dien_thoai", $data['phone']);

        return $stmt->execute();
    }

    public function getAll()
    {
        $query = "SELECT id, ten, email, so_dien_thoai, ngay_tao FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = "SELECT id, ten, email, so_dien_thoai, ngay_tao FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
