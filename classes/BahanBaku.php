<?php

class BahanBaku
{
    private $id_bahan_baku;
    private $nama_bahan_baku;
    private $stok;
    private $satuan;
    private $harga_beli;
    private $foto_bahan_baku;

    public function __construct($id_bahan_baku, $nama_bahan_baku, $stok, $satuan, $harga_beli, $foto_bahan_baku)
    {
        $this->id_bahan_baku = $id_bahan_baku;
        $this->nama_bahan_baku = $nama_bahan_baku;
        $this->stok = $stok;
        $this->satuan = $satuan;
        $this->harga_beli = $harga_beli;
        $this->foto_bahan_baku = $foto_bahan_baku;
    }

    public function cekStok()
    {
        global $conn;
        $stmt = $conn->prepare("SELECT stok FROM bahan_baku WHERE id_bahan_baku = ?");
        $stmt->bind_param("i", $this->id_bahan_baku);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row && isset($row['stok'])) {
            $this->stok = $row['stok'];
        }
        
        return $this->stok;
    }
}
