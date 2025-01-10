<?php
class Database {
    private $host = '127.0.0.1';
    private $user = 'root';
    private $pass = 'password';
    private $database = 'OOP';
    private $conn;

    public function __construct() {
        $this->conn = new PDO("mysql:host={$this->host};dbname={$this->database}", $this->user, $this->pass);
    }

    public function getConnection() {
        return $this->conn;
    }
}


$database = new Database();
$conn = $database->getConnection();
?>
