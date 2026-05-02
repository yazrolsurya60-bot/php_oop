<?php
require_once 'User.php';

class Koki extends User
{
    private $id_koki;
    private $shift;

    public function __construct($id_users, $username, $password, $nama, $alamat, $jenis_kelamin, $foto_profil, $id_koki, $shift)
    {
        parent::__construct($id_users, $username, $password, $nama, $alamat, $jenis_kelamin, $foto_profil);
        $this->id_koki = $id_koki;
        $this->shift = $shift;
    }

    public function manajemenStatusPesanan($id_pesanan, $status_baru)
    {
        // Update di tabel pesanan utama
        $stmt = $this->conn->prepare("UPDATE pesanan SET status_pesanan = ? WHERE id_pesanan = ?");
        $stmt->bind_param("si", $status_baru, $id_pesanan);
        $updateBerhasil = $stmt->execute();

        // Update antrian dapur jika tabel digunakan
        $stmtAntrian = $this->conn->prepare("UPDATE antrian_dapur SET status_antrian = ? WHERE id_pesanan = ?");
        $stmtAntrian->bind_param("si", $status_baru, $id_pesanan);
        $stmtAntrian->execute();

        return $updateBerhasil;
    }

    public function getShift()
    {
        return $this->shift;
    }
}
