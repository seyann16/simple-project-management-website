<?php
class Database {
    private $host = "your_mysql_host";
    private $db_name = "your_database_name";
    private $username = "your_mysql_username";
    private $password = "your_mysql_password";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            // Don't echo here - just return null
            error_log("Database connection failed: " . $exception->getMessage());
            return null;
        }
        return $this->conn;
    }
}
?>