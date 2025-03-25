<?php
class Database {
    private $host = "db";
    private $db_name = "health_db";
    private $username = "healthuser";
    private $password = "aa123456";
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8mb4");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "连接错误: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>