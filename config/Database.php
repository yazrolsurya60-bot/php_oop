<?php
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "db_sistem_pos";

// Membuat koneksi MySQLi secara global
$conn = new mysqli($host, $user, $pass, $db_name);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set agar mysqli menampilkan exception jika ada error (mirip PDOException)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>