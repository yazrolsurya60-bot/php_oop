<?php

class Menu extends Database
{
    private $id_produk;
    private $nama_produk;
    private $deskripsi;
    private $harga;
    private $status_tersedia;
    private $kategori;
    private $foto_produk;

    public function __construct($id_produk, $nama_produk, $deskripsi, $harga, $status_tersedia, $kategori, $foto_produk)
    {
        $this->id_produk = $id_produk;
        $this->nama_produk = $nama_produk;
        $this->deskripsi = $deskripsi;
        $this->harga = $harga;
        $this->status_tersedia = $status_tersedia;
        $this->kategori = $kategori;
        $this->foto_produk = $foto_produk;
    }

    public function tampilkanMenu()
    {
        $result = $this->conn->query("SELECT * FROM menu WHERE status_tersedia = 1");
        if ($result)
            return $result->fetch_all(MYSQLI_ASSOC);
        return [];
    }

    public function cariMenu($keyword)
    {
        $keywordSearch = "%" . $keyword . "%";
        $stmt = $this->conn->prepare("SELECT * FROM menu WHERE (nama_produk LIKE ? OR kategori LIKE ?) AND status_tersedia = 1");
        $stmt->bind_param("ss", $keywordSearch, $keywordSearch);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
