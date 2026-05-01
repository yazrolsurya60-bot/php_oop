<?php
require_once 'User.php';

class Admin extends User
{
    private $id_admin;

    public function __construct($id_users, $username, $password, $nama, $alamat, $jenis_kelamin, $foto_profil, $id_admin)
    {
        parent::__construct($id_users, $username, $password, $nama, $alamat, $jenis_kelamin, $foto_profil);
        $this->id_admin = $id_admin;
    }

    public function tambahUser($userData)
    {
        global $conn;
        $hashedPass = password_hash($userData['password'], PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (id_users, username, password, nama, alamat, jenis_kelamin, foto_profil) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $userData['id_users'], $userData['username'], $hashedPass, $userData['nama'], $userData['alamat'], $userData['jenis_kelamin'], $userData['foto_profil']);
        return $stmt->execute();
    }

    public function hapusUser($id_users)
    {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM users WHERE id_users = ?");
        $stmt->bind_param("s", $id_users);
        return $stmt->execute();
    }

    public function editUser($id_users, $newData)
    {
        global $conn;
        $stmt = $conn->prepare("UPDATE users SET username = ?, nama = ?, alamat = ? WHERE id_users = ?");
        $stmt->bind_param("ssss", $newData['username'], $newData['nama'], $newData['alamat'], $id_users);
        return $stmt->execute();
    }

    public function cariUser($keyword)
    {
        global $conn;
        $keywordSearch = "%" . $keyword . "%";
        $stmt = $conn->prepare("SELECT * FROM users WHERE nama LIKE ? OR username LIKE ?");
        $stmt->bind_param("ss", $keywordSearch, $keywordSearch);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function tampilkanUser()
    {
        global $conn;
        $result = $conn->query("SELECT * FROM users");
        if($result) return $result->fetch_all(MYSQLI_ASSOC);
        return [];
    }

    public function tambahProduk($produkData)
    {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO menu (nama_produk, deskripsi, harga, status_tersedia, kategori, foto_produk) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiss", $produkData['nama_produk'], $produkData['deskripsi'], $produkData['harga'], $produkData['status_tersedia'], $produkData['kategori'], $produkData['foto_produk']);
        return $stmt->execute();
    }

    public function hapusProduk($id_produk)
    {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM menu WHERE id_produk = ?");
        $stmt->bind_param("i", $id_produk);
        return $stmt->execute();
    }

    public function editProduk($id_produk, $newData)
    {
        global $conn;
        $stmt = $conn->prepare("UPDATE menu SET nama_produk = ?, harga = ?, status_tersedia = ? WHERE id_produk = ?");
        $stmt->bind_param("sdii", $newData['nama_produk'], $newData['harga'], $newData['status_tersedia'], $id_produk);
        return $stmt->execute();
    }

    public function cariProduk($keyword)
    {
        global $conn;
        $keywordSearch = "%" . $keyword . "%";
        $stmt = $conn->prepare("SELECT * FROM menu WHERE nama_produk LIKE ? OR kategori LIKE ?");
        $stmt->bind_param("ss", $keywordSearch, $keywordSearch);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function tampilkanProduk()
    {
        global $conn;
        $result = $conn->query("SELECT * FROM menu");
        if($result) return $result->fetch_all(MYSQLI_ASSOC);
        return [];
    }

    public function tambahBahanBaku($bahanData)
    {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO bahan_baku (nama_bahan_baku, stok, satuan, harga_beli, foto_bahan_baku) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisds", $bahanData['nama_bahan_baku'], $bahanData['stok'], $bahanData['satuan'], $bahanData['harga_beli'], $bahanData['foto_bahan_baku']);
        return $stmt->execute();
    }

    public function hapusBahanBaku($id_bahan_baku)
    {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM bahan_baku WHERE id_bahan_baku = ?");
        $stmt->bind_param("i", $id_bahan_baku);
        return $stmt->execute();
    }

    public function editBahanBaku($id_bahan_baku, $newData)
    {
        global $conn;
        $stmt = $conn->prepare("UPDATE bahan_baku SET nama_bahan_baku = ?, stok = ?, harga_beli = ? WHERE id_bahan_baku = ?");
        $stmt->bind_param("sidi", $newData['nama_bahan_baku'], $newData['stok'], $newData['harga_beli'], $id_bahan_baku);
        return $stmt->execute();
    }

    public function cariBahanBaku($keyword)
    {
        global $conn;
        $keywordSearch = "%" . $keyword . "%";
        $stmt = $conn->prepare("SELECT * FROM bahan_baku WHERE nama_bahan_baku LIKE ?");
        $stmt->bind_param("s", $keywordSearch);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function tampilkanBahanBaku()
    {
        global $conn;
        $result = $conn->query("SELECT * FROM bahan_baku");
        if($result) return $result->fetch_all(MYSQLI_ASSOC);
        return [];
    }

    public function tampilkanPendapatan($periode)
    {
        global $conn;
        $stmt = $conn->prepare("SELECT SUM(total_pembayaran) as total FROM pembayaran p JOIN pesanan ps ON p.id_pesanan = ps.id_pesanan WHERE MONTH(ps.waktu_pesanan) = ?");
        $stmt->bind_param("i", $periode);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row['total'] ? (double) $row['total'] : 0.0;
    }

    public function tampilkanGrafikPendapatan()
    {
        global $conn;
        $result = $conn->query("SELECT DATE(ps.waktu_pesanan) as tanggal, SUM(p.total_pembayaran) as total FROM pembayaran p JOIN pesanan ps ON p.id_pesanan = ps.id_pesanan GROUP BY DATE(ps.waktu_pesanan)");
        if($result) return $result->fetch_all(MYSQLI_ASSOC);
        return [];
    }
}
