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
        $query = "INSERT INTO " . $this->table_name . " (ten, email, mat_khau, so_dien_thoai, ngay_tao) VALUES (:ten, :email, :mat_khau, :so_dien_thoai, NOW())";
        $stmt = $this->conn->prepare($query);
        $hashed = $this->hashPassword($data['mat_khau']);
        $stmt->bindParam(':ten', $data['ten']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':mat_khau', $hashed);
        $stmt->bindParam(':so_dien_thoai', $data['so_dien_thoai']);
        return $stmt->execute();
    }
}