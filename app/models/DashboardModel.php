<?php
// app/models/DashboardModel.php
// MODEL = query statistik untuk halaman dashboard

class DashboardModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getStatistik()
    {
        return [
            'total_barang' => (int)$this->conn->query("SELECT COUNT(*) FROM barang")->fetchColumn(),
            'total_nilai'  => (float)$this->conn->query("SELECT SUM(jumlah * harga) FROM barang")->fetchColumn(),
            'stok_rendah'  => (int)$this->conn->query("SELECT COUNT(*) FROM barang WHERE jumlah <= stok_minimum AND jumlah > 0")->fetchColumn(),
            'stok_habis'   => (int)$this->conn->query("SELECT COUNT(*) FROM barang WHERE jumlah = 0 OR status = 'habis'")->fetchColumn(),
        ];
    }

    public function getBarangTerbaru()
    {
        return $this->conn->query("
            SELECT b.nama_barang, b.kode_barang, b.jumlah, b.harga,
                   b.status, b.stok_minimum, k.nama_kategori
            FROM barang b
            JOIN kategori k ON b.id_kategori = k.id_kategori
            ORDER BY b.id DESC LIMIT 5
        ")->fetchAll();
    }

    public function getBarangKritis()
    {
        return $this->conn->query("
            SELECT nama_barang, jumlah, stok_minimum, status
            FROM barang
            WHERE jumlah <= stok_minimum OR status = 'habis'
            ORDER BY jumlah ASC LIMIT 5
        ")->fetchAll();
    }
}
