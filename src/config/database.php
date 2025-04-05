<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        $this->host = "db";
        $this->db_name = "health_db";
        $this->username = "healthuser";
        $this->password = "aa123456";
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $e) {
            error_log("数据库连接错误: " . $e->getMessage());
            throw new Exception("数据库连接失败");
        }

        return $this->conn;
    }
}
?>