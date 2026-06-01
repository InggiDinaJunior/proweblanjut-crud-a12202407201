<?php
// api/read.php
// METHOD : GET
// URL    : http://localhost/inventaris_mvc/api/read.php
// DESC   : Mengambil semua data barang dari database dan
//          mengembalikannya sebagai array of objects dalam format JSON

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// Hanya izinkan method GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method tidak diizinkan. Gunakan GET."]);
    exit;
}

// Koneksi database
require_once __DIR__ . '/../config/database.php';

try {
    // Query JOIN untuk mendapatkan nama kategori dan satuan
    $stmt = $conn->query("
        SELECT
            b.id,
            b.kode_barang,
            b.nama_barang,
            k.nama_kategori,
            s.nama_satuan,
            b.jumlah,
            b.harga,
            b.stok_minimum,
            b.lokasi,
            b.deskripsi,
            b.tanggal_masuk,
            b.status,
            b.gambar,
            (b.jumlah * b.harga) AS total_nilai
        FROM barang b
        JOIN kategori k ON b.id_kategori = k.id_kategori
        JOIN satuan   s ON b.id_satuan   = s.id_satuan
        ORDER BY b.id DESC
    ");

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Konversi tipe data numerik agar tidak menjadi string di JSON
    foreach ($data as &$row) {
        $row['id']           = (int)$row['id'];
        $row['jumlah']       = (int)$row['jumlah'];
        $row['harga']        = (float)$row['harga'];
        $row['stok_minimum'] = (int)$row['stok_minimum'];
        $row['total_nilai']  = (float)$row['total_nilai'];
    }

    http_response_code(200);
    echo json_encode([
        "status"  => "success",
        "total"   => count($data),
        "data"    => $data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Gagal mengambil data: " . $e->getMessage()]);
}
