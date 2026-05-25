<?php
// app/models/BarangModel.php
// MODEL = semua query SQL terkait barang ada di sini

class BarangModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // READ - Semua barang dengan filter & paginasi
    public function getAll($search = '', $filter = '', $kategori_id = 0, $limit = 10, $offset = 0)
    {
        $where  = [];
        $params = [];

        if ($search !== '') {
            $where[]  = "(b.nama_barang LIKE ? OR b.kode_barang LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($filter === 'rendah')   $where[] = "b.jumlah <= b.stok_minimum AND b.jumlah > 0";
        if ($filter === 'habis')    $where[] = "(b.jumlah = 0 OR b.status = 'habis')";
        if ($filter === 'aktif')    $where[] = "b.status = 'aktif'";
        if ($filter === 'nonaktif') $where[] = "b.status = 'nonaktif'";
        if ($kategori_id > 0) {
            $where[]  = "b.id_kategori = ?";
            $params[] = $kategori_id;
        }

        $where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Hitung total untuk paginasi
        $count = $this->conn->prepare(
            "SELECT COUNT(*) FROM barang b JOIN kategori k ON b.id_kategori = k.id_kategori $where_sql"
        );
        $count->execute($params);
        $total = (int)$count->fetchColumn();

        // Ambil data halaman ini
        $stmt = $this->conn->prepare("
            SELECT b.id, b.kode_barang, b.nama_barang, b.jumlah, b.harga,
                   b.stok_minimum, b.lokasi, b.tanggal_masuk, b.status,
                   b.deskripsi, b.tanggal_update, b.gambar,
                   k.nama_kategori, s.nama_satuan,
                   (b.jumlah * b.harga) AS total_nilai
            FROM barang b
            JOIN kategori k ON b.id_kategori = k.id_kategori
            JOIN satuan   s ON b.id_satuan   = s.id_satuan
            $where_sql
            ORDER BY b.id DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute(array_merge($params, [$limit, $offset]));

        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    // READ - Satu barang berdasarkan ID
    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM barang WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // READ - Generate kode barang otomatis
    public function generateKode()
    {
        $last = $this->conn->query("SELECT kode_barang FROM barang ORDER BY id DESC LIMIT 1")->fetchColumn();
        $next = 1;
        if ($last && preg_match('/BRG-(\d+)/', $last, $m)) {
            $next = (int)$m[1] + 1;
        }
        return 'BRG-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    // READ - Cek duplikat kode barang
    public function isKodeDuplikat($kode, $exclude_id = 0)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM barang WHERE kode_barang = ? AND id != ?");
        $stmt->execute([$kode, $exclude_id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // READ - Semua kategori
    public function getKategori()
    {
        return $this->conn->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
    }

    // READ - Semua satuan
    public function getSatuan()
    {
        return $this->conn->query("SELECT * FROM satuan ORDER BY nama_satuan")->fetchAll();
    }

    // CREATE - Simpan barang baru
    public function create($data)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO barang
                (kode_barang, nama_barang, id_kategori, id_satuan,
                 jumlah, harga, stok_minimum, lokasi, deskripsi,
                 gambar, tanggal_masuk, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['kode_barang'],
            $data['nama_barang'],
            $data['id_kategori'],
            $data['id_satuan'],
            (int)$data['jumlah'],
            (float)$data['harga'],
            (int)$data['stok_minimum'],
            $data['lokasi']    ?: null,
            $data['deskripsi'] ?: null,
            $data['gambar']    ?: null,
            $data['tanggal_masuk'],
            $data['status'],
        ]);
    }

    // UPDATE - Perbarui data barang
    public function update($id, $data)
    {
        $stmt = $this->conn->prepare("
            UPDATE barang SET
                kode_barang   = ?,
                nama_barang   = ?,
                id_kategori   = ?,
                id_satuan     = ?,
                jumlah        = ?,
                harga         = ?,
                stok_minimum  = ?,
                lokasi        = ?,
                deskripsi     = ?,
                gambar        = ?,
                tanggal_masuk = ?,
                status        = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['kode_barang'],
            $data['nama_barang'],
            $data['id_kategori'],
            $data['id_satuan'],
            (int)$data['jumlah'],
            (float)$data['harga'],
            (int)$data['stok_minimum'],
            $data['lokasi']    ?: null,
            $data['deskripsi'] ?: null,
            $data['gambar'],
            $data['tanggal_masuk'],
            $data['status'],
            $id,
        ]);
    }

    // DELETE - Hapus barang
    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM barang WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
