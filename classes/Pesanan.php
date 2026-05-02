<?php

class Pesanan extends Database
{
    private $id_pesanan;
    private $nama_customer;
    private $total_pesanan;
    private $waktu_pesanan;
    private $status_pesanan;

    public function __construct($id_pesanan, $nama_customer, $total_pesanan, $waktu_pesanan, $status_pesanan)
    {
        $this->id_pesanan = $id_pesanan;
        $this->nama_customer = $nama_customer;
        $this->total_pesanan = $total_pesanan;
        $this->waktu_pesanan = $waktu_pesanan;
        $this->status_pesanan = $status_pesanan;
    }

    public function hitungTotalPesanan($items)
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item->hitungSubTotal();
        }

        $stmt = $this->conn->prepare("UPDATE pesanan SET total_pesanan = ? WHERE id_pesanan = ?");
        $stmt->bind_param("di", $total, $this->id_pesanan);
        $stmt->execute();

        $this->total_pesanan = $total;
        return $this->total_pesanan;
    }

    public function updateStatusPesanan($status_baru)
    {
        $stmt = $this->conn->prepare("UPDATE pesanan SET status_pesanan = ? WHERE id_pesanan = ?");
        $stmt->bind_param("si", $status_baru, $this->id_pesanan);
        $stmt->execute();

        $this->status_pesanan = $status_baru;
        return true;
    }
}
