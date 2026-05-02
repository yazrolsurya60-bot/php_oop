<?php
class Database
{

    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db_name = "db_sistem_pos";
    protected $conn;

    public function __construct()
    {
        if (!isset($this->conn)) {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db_name);
            if ($this->conn->connect_error) {
                die("Koneksi gagal: " . $this->conn->connect_error);
            }
        }
    }
    public function getConn()
    {
        return $this->conn;
    }
}
$conn = new Database();
?>