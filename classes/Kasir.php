<?php
require_once 'User.php';

class Kasir extends User
{
    private $id_kasir;
    private $shift;

    public function __construct($id_users, $username, $password, $nama, $alamat, $jenis_kelamin, $foto_profil, $id_kasir, $shift)
    {
        parent::__construct($id_users, $username, $password, $nama, $alamat, $jenis_kelamin, $foto_profil);
        $this->id_kasir = $id_kasir;
        $this->shift = $shift;
    }

    public function buatPesanan($dataPesanan)
    {
        try {
            $this->conn->begin_transaction();

            // 1. Insert ke tabel pesanan
            $stmt = $this->conn->prepare("INSERT INTO pesanan (id_kasir, nama_customer, total_pesanan, waktu_pesanan, status_pesanan) VALUES (?, ?, ?, NOW(), ?)");
            $status = 'Menunggu';
            $stmt->bind_param("ssds", $this->id_kasir, $dataPesanan['nama_customer'], $dataPesanan['total_pesanan'], $status);
            $stmt->execute();
            $id_pesanan_baru = $this->conn->insert_id;

            // 2. Insert item pesanan ke tabel item_pesanan
            foreach ($dataPesanan['items'] as $item) {
                $stmtItem = $this->conn->prepare("INSERT INTO item_pesanan (id_pesanan, id_produk, jumlah, harga_item, catatan, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
                $stmtItem->bind_param("iiidsd", $id_pesanan_baru, $item['id_produk'], $item['jumlah'], $item['harga_item'], $item['catatan'], $item['subtotal']);
                $stmtItem->execute();
            }

            $this->conn->commit();
            return $id_pesanan_baru;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function batalkanPesanan($id_pesanan)
    {
        global $conn;
        $stmt = $conn->prepare("UPDATE pesanan SET status_pesanan = 'Dibatalkan' WHERE id_pesanan = ?");
        $stmt->bind_param("i", $id_pesanan);
        return $stmt->execute();
    }

    public function cetakStruk($id_pesanan)
    {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM pesanan WHERE id_pesanan = ?");
        $stmt->bind_param("i", $id_pesanan);
        $stmt->execute();
        $pesanan = $stmt->get_result()->fetch_assoc();

        $stmtItem = $conn->prepare("SELECT * FROM item_pesanan WHERE id_pesanan = ?");
        $stmtItem->bind_param("i", $id_pesanan);
        $stmtItem->execute();
        $items = $stmtItem->get_result()->fetch_all(MYSQLI_ASSOC);

        // Format struk
        $struk = "--- STRUK PESANAN --- \n";
        $struk .= "Customer: " . $pesanan['nama_customer'] . "\n";
        foreach ($items as $item) {
            $struk .= $item['jumlah'] . "x " . $item['harga_item'] . " = Rp " . $item['subtotal'] . "\n";
        }
        $struk .= "Total: Rp " . $pesanan['total_pesanan'] . "\n";
        return $struk;
    }

    public function lihatRiwayat()
    {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM pesanan WHERE id_kasir = ? ORDER BY waktu_pesanan DESC");
        $stmt->bind_param("s", $this->id_kasir);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getShift()
    {
        return $this->shift;
    }
}
