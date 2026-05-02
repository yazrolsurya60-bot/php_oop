<?php

class Pembayaran extends Database
{
    private $id_pembayaran;
    private $metode_pembayaran;
    private $total_pembayaran;
    private $total_kembalian;

    public function __construct($id_pembayaran, $metode_pembayaran, $total_pembayaran)
    {
        $this->id_pembayaran = $id_pembayaran;
        $this->metode_pembayaran = $metode_pembayaran;
        $this->total_pembayaran = $total_pembayaran;
        $this->total_kembalian = 0;
    }

    public function hitungTotal($diskon = 0, $pajak = 0)
    {
        $totalAkhir = ($this->total_pembayaran - $diskon) + $pajak;

        $stmt = $this->conn->prepare("UPDATE pembayaran SET total_pembayaran = ? WHERE id_pembayaran = ?");
        $stmt->bind_param("di", $totalAkhir, $this->id_pembayaran);
        $stmt->execute();

        $this->total_pembayaran = $totalAkhir;
        return $totalAkhir;
    }

    public function hitungKembalian($uang_diberikan)
    {
        if ($uang_diberikan >= $this->total_pembayaran) {
            $kembalian = $uang_diberikan - $this->total_pembayaran;


            $stmt = $this->conn->prepare("UPDATE pembayaran SET total_kembalian = ? WHERE id_pembayaran = ?");
            $stmt->bind_param("di", $kembalian, $this->id_pembayaran);
            $stmt->execute();

            $this->total_kembalian = $kembalian;
            return $kembalian;
        }
        return -1; // Indikator uang tidak cukup
    }
}
