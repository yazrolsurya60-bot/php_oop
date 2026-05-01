<?php

class AntrianDapur
{
    private $id_antrian;
    private $status_antrian;

    public function __construct($id_antrian, $status_antrian)
    {
        $this->id_antrian = $id_antrian;
        $this->status_antrian = $status_antrian;
    }

    public function tambahKeAntrian($id_pesanan)
    {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO antrian_dapur (id_pesanan, status_antrian) VALUES (?, 'Menunggu')");
        $stmt->bind_param("i", $id_pesanan);
        return $stmt->execute();
    }

    public function updateStatus($status_baru)
    {
        global $conn;
        $stmt = $conn->prepare("UPDATE antrian_dapur SET status_antrian = ? WHERE id_antrian = ?");
        $stmt->bind_param("si", $status_baru, $this->id_antrian);
        $berhasil = $stmt->execute();

        if ($berhasil) {
            $this->status_antrian = $status_baru;
        }
        return $berhasil;
    }
}
