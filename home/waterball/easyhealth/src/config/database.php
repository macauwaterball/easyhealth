<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        $this->host = getenv('DB_HOST') ?: 'mysql';
        $this->db_name = getenv('DB_NAME') ?: 'easyhealth';
        $this->username = getenv('DB_USER') ?: 'easyhealth';
        $this->password = getenv('DB_PASSWORD') ?: 'easyhealth123';
    }

    public function getConnection() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $e) {
            echo "连接错误: " . $e->getMessage();
        }

        return $this->conn;
    }
}