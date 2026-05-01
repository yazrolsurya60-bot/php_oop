<?php

class ItemPesanan
{
    private $id_item;
    private $jumlah;
    private $harga_item;
    private $catatan;
    private $subtotal;

    public function __construct($id_item, $jumlah, $harga_item, $catatan)
    {
        $this->id_item = $id_item;
        $this->jumlah = $jumlah;
        $this->harga_item = $harga_item;
        $this->catatan = $catatan;
        $this->subtotal = $this->hitungSubTotal();
    }

    public function hitungSubTotal()
    {
        $hasil = $this->jumlah * $this->harga_item;

        global $conn;
        $stmt = $conn->prepare("UPDATE item_pesanan SET subtotal = ? WHERE id_item = ?");
        $stmt->bind_param("di", $hasil, $this->id_item);
        $stmt->execute();

        $this->subtotal = $hasil;
        return $hasil;
    }
}
