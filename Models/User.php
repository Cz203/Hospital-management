<?php
require_once 'config/database.php';

class User
{
    protected $conn;
    protected $table_name;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
