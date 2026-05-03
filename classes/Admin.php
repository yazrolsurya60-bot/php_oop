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
        $stmt = $this->conn->prepare("INSERT INTO users (id_users, username, password, nama, alamat, jenis_kelamin, role, foto_profil) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssssssss",
            $userData['id_users'],
            $userData['username'],
            $userData['password'],
            $userData['nama'],
            $userData['alamat'],
            $userData['jenis_kelamin'],
            $userData['role'],
            $userData['foto_profil']
        );
        return $stmt->execute();
    }

    public function hapusUser($id_users)
    {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id_users = ?");
        $stmt->bind_param("s", $id_users);
        return $stmt->execute();
    }

    public function editUser($id_users, $newData)
    {
        // Update foto hanya jika ada perubahan
        if (!empty($newData['foto_profil'])) {
            $stmt = $this->conn->prepare("UPDATE users SET username = ?, nama = ?, alamat = ?, role = ?, foto_profil = ? WHERE id_users = ?");
            $stmt->bind_param("ssssss", $newData['username'], $newData['nama'], $newData['alamat'], $newData['role'], $newData['foto_profil'], $id_users);
        } else {
            $stmt = $this->conn->prepare("UPDATE users SET username = ?, nama = ?, alamat = ?, role = ? WHERE id_users = ?");
            $stmt->bind_param("sssss", $newData['username'], $newData['nama'], $newData['alamat'], $newData['role'], $id_users);
        }
        return $stmt->execute();
    }

    public function cariUser($keyword)
    {
        $keywordSearch = "%" . $keyword . "%";
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE nama LIKE ? OR username LIKE ?");
        $stmt->bind_param("ss", $keywordSearch, $keywordSearch);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function tampilkanUser()
    {
        $result = $this->conn->query("SELECT * FROM users");
        if ($result)
            return $result->fetch_all(MYSQLI_ASSOC);
        return [];
    }

    // Tampilkan semua user beserta role mereka via LEFT JOIN
    public function tampilkanUserDenganRole()
    {
        $result = $this->conn->query("
            SELECT u.*,
                CASE
                    WHEN a.id_users IS NOT NULL THEN 'Admin'
                    WHEN k.id_users IS NOT NULL THEN 'Kasir'
                    WHEN ko.id_users IS NOT NULL THEN 'Koki'
                    ELSE '-'
                END AS role
            FROM users u
            LEFT JOIN admin a  ON u.id_users = a.id_users
            LEFT JOIN kasir k  ON u.id_users = k.id_users
            LEFT JOIN koki ko  ON u.id_users = ko.id_users
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // ===================== KASIR =====================

    public function tambahKasir($id_kasir, $id_users, $shift)
    {
        $stmt = $this->conn->prepare("INSERT INTO kasir (id_kasir, id_users, shift) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $id_kasir, $id_users, $shift);
        return $stmt->execute();
    }

    public function hapusKasir($id_kasir)
    {
        $stmt = $this->conn->prepare("DELETE FROM kasir WHERE id_kasir = ?");
        $stmt->bind_param("s", $id_kasir);
        return $stmt->execute();
    }

    public function editKasir($id_kasir, $newData)
    {
        $stmt = $this->conn->prepare("UPDATE kasir SET shift = ? WHERE id_kasir = ?");
        $stmt->bind_param("ss", $newData['shift'], $id_kasir);
        return $stmt->execute();
    }

    public function cariKasir($keyword)
    {
        $kw = "%$keyword%";
        $stmt = $this->conn->prepare("
            SELECT u.*, k.id_kasir, k.shift, 'Kasir' AS role
            FROM kasir k JOIN users u ON k.id_users = u.id_users
            WHERE u.nama LIKE ? OR u.username LIKE ?
        ");
        $stmt->bind_param("ss", $kw, $kw);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function tampilkanKasir()
    {
        $result = $this->conn->query("
            SELECT u.*, k.id_kasir, k.shift, 'Kasir' AS role
            FROM kasir k JOIN users u ON k.id_users = u.id_users
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // ===================== KOKI =====================

    public function tambahKoki($id_koki, $id_users, $shift)
    {
        $stmt = $this->conn->prepare("INSERT INTO koki (id_koki, id_users, shift) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $id_koki, $id_users, $shift);
        return $stmt->execute();
    }

    public function hapusKoki($id_koki)
    {
        $stmt = $this->conn->prepare("DELETE FROM koki WHERE id_koki = ?");
        $stmt->bind_param("s", $id_koki);
        return $stmt->execute();
    }

    public function editKoki($id_koki, $newData)
    {
        $stmt = $this->conn->prepare("UPDATE koki SET shift = ? WHERE id_koki = ?");
        $stmt->bind_param("ss", $newData['shift'], $id_koki);
        return $stmt->execute();
    }

    public function cariKoki($keyword)
    {
        $kw = "%$keyword%";
        $stmt = $this->conn->prepare("
            SELECT u.*, ko.id_koki, ko.shift, 'Koki' AS role
            FROM koki ko JOIN users u ON ko.id_users = u.id_users
            WHERE u.nama LIKE ? OR u.username LIKE ?
        ");
        $stmt->bind_param("ss", $kw, $kw);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function tampilkanKoki()
    {
        $result = $this->conn->query("
            SELECT u.*, ko.id_koki, ko.shift, 'Koki' AS role
            FROM koki ko JOIN users u ON ko.id_users = u.id_users
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // ===================== ADMIN =====================

    public function tambahAdmin($id_admin, $id_users, $shift)
    {
        $stmt = $this->conn->prepare("INSERT INTO admin (id_admin, id_users, shift) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $id_admin, $id_users, $shift);
        return $stmt->execute();
    }

    // Hapus user dari SEMUA tabel role (untuk reset sebelum insert role baru)
    public function hapusRoleUser($id_users)
    {
        foreach (['admin', 'kasir', 'koki'] as $tabel) {
            $stmt = $this->conn->prepare("DELETE FROM $tabel WHERE id_users = ?");
            $stmt->bind_param("s", $id_users);
            $stmt->execute();
        }
    }

    // Ganti role user: hapus dari tabel lama, insert ke tabel baru
    public function editRoleUser($id_users, $oldRole, $newRole, $id_role_baru, $shift = '')
    {
        // 1. Hapus dari tabel role lama
        if ($oldRole === 'Kasir') {
            $stmt = $this->conn->prepare("DELETE FROM kasir WHERE id_users = ?");
            $stmt->bind_param("s", $id_users);
            $stmt->execute();
        } elseif ($oldRole === 'Koki') {
            $stmt = $this->conn->prepare("DELETE FROM koki WHERE id_users = ?");
            $stmt->bind_param("s", $id_users);
            $stmt->execute();
        } elseif ($oldRole === 'Admin') {
            // Admin utama sebaiknya tidak dihapus, tapi bisa disesuaikan
            $stmt = $this->conn->prepare("DELETE FROM admin WHERE id_users = ?");
            $stmt->bind_param("s", $id_users);
            $stmt->execute();
        }

        // 2. Insert ke tabel role baru
        if ($newRole === 'Kasir') {
            return $this->tambahKasir($id_role_baru, $id_users, $shift);
        } elseif ($newRole === 'Koki') {
            return $this->tambahKoki($id_role_baru, $id_users, $shift);
        } elseif ($newRole === 'Admin') {
            $stmt = $this->conn->prepare("INSERT INTO admin (id_admin, id_users) VALUES (?, ?)");
            $stmt->bind_param("ss", $id_role_baru, $id_users);
            return $stmt->execute();
        }
        return true;
    }

    // Upload gambar (override dari User, arahkan ke folder images/)
    public function uploadGambar($file)
    {
        if ($file['error'] === 4)
            return '';
        $ekstensiValid = ['jpg', 'jpeg', 'png'];
        $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ekstensi, $ekstensiValid))
            return false;
        $namaFileBaru = uniqid() . '.' . $ekstensi;
        move_uploaded_file($file['tmp_name'], 'images/' . $namaFileBaru);
        return $namaFileBaru;
    }

    public function tambahProduk($produkData)
    {
        $stmt = $this->conn->prepare("INSERT INTO menu (nama_produk, deskripsi, harga, status_tersedia, kategori, foto_produk) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiss", $produkData['nama_produk'], $produkData['deskripsi'], $produkData['harga'], $produkData['status_tersedia'], $produkData['kategori'], $produkData['foto_produk']);
        return $stmt->execute();
    }

    public function hapusProduk($id_produk)
    {
        $stmt = $this->conn->prepare("DELETE FROM menu WHERE id_produk = ?");
        $stmt->bind_param("i", $id_produk);
        return $stmt->execute();
    }

    public function editProduk($id_produk, $newData)
    {
        $stmt = $this->conn->prepare("UPDATE menu SET nama_produk = ?, harga = ?, status_tersedia = ? WHERE id_produk = ?");
        $stmt->bind_param("sdii", $newData['nama_produk'], $newData['harga'], $newData['status_tersedia'], $id_produk);
        return $stmt->execute();
    }

    public function cariProduk($keyword)
    {
        $keywordSearch = "%" . $keyword . "%";
        $stmt = $this->conn->prepare("SELECT * FROM menu WHERE nama_produk LIKE ? OR kategori LIKE ?");
        $stmt->bind_param("ss", $keywordSearch, $keywordSearch);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function tampilkanProduk()
    {
        $result = $this->conn->query("SELECT * FROM menu");
        if ($result)
            return $result->fetch_all(MYSQLI_ASSOC);
        return [];
    }

    public function generateIdBahanBaku()
    {
        $result = $this->conn->query("SELECT MAX(id_bahan_baku) as max_id FROM bahan_baku");
        $row = $result->fetch_assoc();
        $max_id = $row['max_id'];

        if ($max_id) {
            $num = (int) substr($max_id, 3);
            $num++;
            return "BHN" . sprintf("%03d", $num);
        }
        return "BHN001";
    }

    public function tambahBahanBaku($bahanData)
    {
        $stmt = $this->conn->prepare("INSERT INTO bahan_baku (id_bahan_baku, nama_bahan_baku, stok, satuan, harga_beli, foto_bahan_baku) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisds", $bahanData['id_bahan_baku'], $bahanData['nama_bahan_baku'], $bahanData['stok'], $bahanData['satuan'], $bahanData['harga_beli'], $bahanData['foto_bahan_baku']);
        return $stmt->execute();
    }

    public function hapusBahanBaku($id_bahan_baku)
    {
        $stmt = $this->conn->prepare("DELETE FROM bahan_baku WHERE id_bahan_baku = ?");
        $stmt->bind_param("i", $id_bahan_baku);
        return $stmt->execute();
    }

    public function editBahanBaku($id_bahan_baku, $newData)
    {
        // Update foto hanya jika ada perubahan
        if (!empty($newData['foto_bahan_baku'])) {
            $stmt = $this->conn->prepare("UPDATE bahan_baku SET nama_bahan_baku = ?, stok = ?, satuan = ?, harga_beli = ?, foto_bahan_baku = ? WHERE id_bahan_baku = ?");
            $stmt->bind_param("sisdss", $newData['nama_bahan_baku'], $newData['stok'], $newData['satuan'], $newData['harga_beli'], $newData['foto_bahan_baku'], $id_bahan_baku);
        } else {
            $stmt = $this->conn->prepare("UPDATE bahan_baku SET nama_bahan_baku = ?, stok = ?, satuan = ?, harga_beli = ? WHERE id_bahan_baku = ?");
            $stmt->bind_param("sisds", $newData['nama_bahan_baku'], $newData['stok'], $newData['satuan'], $newData['harga_beli'], $id_bahan_baku);
        }
        return $stmt->execute();
    }

    public function cariBahanBaku($keyword)
    {
        $keywordSearch = "%" . $keyword . "%";
        $stmt = $this->conn->prepare("SELECT * FROM bahan_baku WHERE nama_bahan_baku LIKE ?");
        $stmt->bind_param("s", $keywordSearch);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function tampilkanBahanBaku()
    {
        $result = $this->conn->query("SELECT * FROM bahan_baku");
        if ($result)
            return $result->fetch_all(MYSQLI_ASSOC);
        return [];
    }

    public function tampilkanPendapatan($periode)
    {
        $stmt = $this->conn->prepare("SELECT SUM(total_pembayaran) as total FROM pembayaran p JOIN pesanan ps ON p.id_pesanan = ps.id_pesanan WHERE MONTH(ps.waktu_pesanan) = ?");
        $stmt->bind_param("i", $periode);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row['total'] ? (double) $row['total'] : 0.0;
    }

    public function tampilkanGrafikPendapatan()
    {
        $result = $this->conn->query("SELECT DATE(ps.waktu_pesanan) as tanggal, SUM(p.total_pembayaran) as total FROM pembayaran p JOIN pesanan ps ON p.id_pesanan = ps.id_pesanan GROUP BY DATE(ps.waktu_pesanan)");
        if ($result)
            return $result->fetch_all(MYSQLI_ASSOC);
        return [];
    }
}
