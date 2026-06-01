<?php
// api/create.php
// METHOD : POST
// URL    : http://localhost/inventaris_mvc/api/create.php
// DESC   : Menerima data barang baru dari body request (x-www-form-urlencoded atau JSON)
//          dan menyimpannya ke database

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Hanya izinkan method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method tidak diizinkan. Gunakan POST."]);
    exit;
}

// ── Baca input: bisa dari form-urlencoded ATAU JSON body ──
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($content_type, 'application/json') !== false) {
    // Input dari Postman body raw JSON
    $raw  = file_get_contents("php://input");
    $body = json_decode($raw, true) ?? [];
} else {
    // Input dari Postman body x-www-form-urlencoded
    $body = $_POST;
}

// ── Ambil dan bersihkan setiap field ─────────────────────
$kode_barang  = trim($body['kode_barang']   ?? '');
$nama_barang  = trim($body['nama_barang']   ?? '');
$id_kategori  = (int)($body['id_kategori']  ?? 0);
$id_satuan    = (int)($body['id_satuan']    ?? 0);
$jumlah       = (int)($body['jumlah']       ?? 0);
$harga        = (float)($body['harga']      ?? 0);
$stok_minimum = (int)($body['stok_minimum'] ?? 5);
$lokasi       = trim($body['lokasi']        ?? '');
$deskripsi    = trim($body['deskripsi']     ?? '');
$tanggal_masuk= trim($body['tanggal_masuk'] ?? date('Y-m-d'));
$status       = trim($body['status']        ?? 'aktif');

// ── Validasi field wajib ──────────────────────────────────
$errors = [];
if (empty($kode_barang))  $errors[] = "kode_barang wajib diisi.";
if (empty($nama_barang))  $errors[] = "nama_barang wajib diisi.";
if ($id_kategori === 0)   $errors[] = "id_kategori wajib diisi (angka > 0).";
if ($id_satuan === 0)     $errors[] = "id_satuan wajib diisi (angka > 0).";
if ($jumlah < 0)          $errors[] = "jumlah tidak boleh negatif.";
if ($harga < 0)           $errors[] = "harga tidak boleh negatif.";
if (empty($tanggal_masuk))$errors[] = "tanggal_masuk wajib diisi (format: YYYY-MM-DD).";
if (!in_array($status, ['aktif','nonaktif','habis'])) {
    $errors[] = "status harus salah satu dari: aktif, nonaktif, habis.";
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(["status" => "error", "message" => "Validasi gagal.", "errors" => $errors]);
    exit;
}

// Koneksi database
require_once __DIR__ . '/../config/database.php';

try {
    // Cek duplikat kode_barang
    $cek = $conn->prepare("SELECT id FROM barang WHERE kode_barang = ?");
    $cek->execute([$kode_barang]);
    if ($cek->fetch()) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "Kode barang '$kode_barang' sudah digunakan."]);
        exit;
    }

    // Simpan ke database
    $stmt = $conn->prepare("
        INSERT INTO barang
            (kode_barang, nama_barang, id_kategori, id_satuan,
             jumlah, harga, stok_minimum, lokasi, deskripsi,
             tanggal_masuk, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $kode_barang, $nama_barang, $id_kategori, $id_satuan,
        $jumlah, $harga, $stok_minimum,
        $lokasi    ?: null,
        $deskripsi ?: null,
        $tanggal_masuk, $status
    ]);

    $id_baru = (int)$conn->lastInsertId();

    http_response_code(201);
    echo json_encode([
        "status"  => "success",
        "message" => "Barang '$nama_barang' berhasil ditambahkan.",
        "data"    => [
            "id"           => $id_baru,
            "kode_barang"  => $kode_barang,
            "nama_barang"  => $nama_barang,
            "id_kategori"  => $id_kategori,
            "id_satuan"    => $id_satuan,
            "jumlah"       => $jumlah,
            "harga"        => $harga,
            "stok_minimum" => $stok_minimum,
            "lokasi"       => $lokasi       ?: null,
            "deskripsi"    => $deskripsi    ?: null,
            "tanggal_masuk"=> $tanggal_masuk,
            "status"       => $status
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Gagal menyimpan data: " . $e->getMessage()]);
}
