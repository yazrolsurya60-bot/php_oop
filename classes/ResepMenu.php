<?php

class ResepMenu extends Database
{
    private $id_resep;
    private $takaran;
    private $satuan;

    public function __construct($id_resep, $takaran, $satuan)
    {
        $this->id_resep = $id_resep;
        $this->takaran = $takaran;
        $this->satuan = $satuan;
    }

    public function tambahKomposisi($id_produk, $id_bahan_baku, $takaran_baru, $satuan_baru)
    {
        $stmt = $this->conn->prepare("INSERT INTO resep_menu (id_produk, id_bahan_baku, takaran, satuan) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iids", $id_produk, $id_bahan_baku, $takaran_baru, $satuan_baru);
        return $stmt->execute();
    }

    public function hapusKomposisi($id_resep_hapus)
    {
        $stmt = $this->conn->prepare("DELETE FROM resep_menu WHERE id_resep = ?");
        $stmt->bind_param("i", $id_resep_hapus);
        return $stmt->execute();
    }

    public function editKomposisi($id_resep_edit, $takaran_baru, $satuan_baru)
    {
        $stmt = $this->conn->prepare("UPDATE resep_menu SET takaran = ?, satuan = ? WHERE id_resep = ?");
        $stmt->bind_param("dsi", $takaran_baru, $satuan_baru, $id_resep_edit);
        $berhasil = $stmt->execute();

        if ($berhasil) {
            $this->takaran = $takaran_baru;
            $this->satuan = $satuan_baru;
        }
        return $berhasil;
    }

    public function getTakaran()
    {
        $stmt = $this->conn->prepare("SELECT takaran, satuan FROM resep_menu WHERE id_resep = ?");
        $stmt->bind_param("i", $this->id_resep);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        if ($data) {
            $this->takaran = $data['takaran'];
            $this->satuan = $data['satuan'];
        }

        return $this->takaran . ' ' . $this->satuan;
    }
}
