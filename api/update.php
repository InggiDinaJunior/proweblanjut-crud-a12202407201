<?php
// api/update.php
// METHOD : POST atau PUT
// URL    : http://localhost/inventaris_mvc/api/update.php
// DESC   : Menerima id barang yang akan diubah beserta field yang diperbarui,
//          lalu menyimpan perubahannya ke database

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, PUT");
header("Access-Control-Allow-Headers: Content-Type");

// Izinkan POST dan PUT
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT'])) {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method tidak diizinkan. Gunakan POST atau PUT."]);
    exit;
}

// ── Baca input: bisa dari form-urlencoded ATAU JSON body ──
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($content_type, 'application/json') !== false) {
    $raw  = file_get_contents("php://input");
    $body = json_decode($raw, true) ?? [];
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // PUT dengan form-urlencoded perlu dibaca manual
    parse_str(file_get_contents("php://input"), $body);
} else {
    $body = $_POST;
}

// ── Ambil id ─────────────────────────────────────────────
$id = (int)($body['id'] ?? 0);

if ($id === 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Field 'id' wajib diisi untuk menentukan barang yang diubah."]);
    exit;
}

// Koneksi database
require_once __DIR__ . '/../config/database.php';

try {
    // Cek apakah barang dengan id tersebut ada
    $cek = $conn->prepare("SELECT * FROM barang WHERE id = ?");
    $cek->execute([$id]);
    $barang = $cek->fetch(PDO::FETCH_ASSOC);

    if (!$barang) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Barang dengan id=$id tidak ditemukan."]);
        exit;
    }

    // ── Gunakan nilai lama jika field tidak dikirim ───────
    $kode_barang  = trim($body['kode_barang']   ?? $barang['kode_barang']);
    $nama_barang  = trim($body['nama_barang']   ?? $barang['nama_barang']);
    $id_kategori  = (int)($body['id_kategori']  ?? $barang['id_kategori']);
    $id_satuan    = (int)($body['id_satuan']    ?? $barang['id_satuan']);
    $jumlah       = (int)($body['jumlah']       ?? $barang['jumlah']);
    $harga        = (float)($body['harga']      ?? $barang['harga']);
    $stok_minimum = (int)($body['stok_minimum'] ?? $barang['stok_minimum']);
    $lokasi       = trim($body['lokasi']        ?? ($barang['lokasi'] ?? ''));
    $deskripsi    = trim($body['deskripsi']     ?? ($barang['deskripsi'] ?? ''));
    $tanggal_masuk= trim($body['tanggal_masuk'] ?? $barang['tanggal_masuk']);
    $status       = trim($body['status']        ?? $barang['status']);

    // ── Validasi ──────────────────────────────────────────
    $errors = [];
    if (empty($kode_barang))  $errors[] = "kode_barang tidak boleh kosong.";
    if (empty($nama_barang))  $errors[] = "nama_barang tidak boleh kosong.";
    if ($id_kategori === 0)   $errors[] = "id_kategori tidak boleh 0.";
    if ($id_satuan === 0)     $errors[] = "id_satuan tidak boleh 0.";
    if ($jumlah < 0)          $errors[] = "jumlah tidak boleh negatif.";
    if ($harga < 0)           $errors[] = "harga tidak boleh negatif.";
    if (!in_array($status, ['aktif','nonaktif','habis'])) {
        $errors[] = "status harus salah satu dari: aktif, nonaktif, habis.";
    }

    // Cek duplikat kode_barang (kecuali milik dirinya sendiri)
    if (empty($errors)) {
        $duplikat = $conn->prepare("SELECT id FROM barang WHERE kode_barang = ? AND id != ?");
        $duplikat->execute([$kode_barang, $id]);
        if ($duplikat->fetch()) {
            $errors[] = "Kode barang '$kode_barang' sudah digunakan barang lain.";
        }
    }

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(["status" => "error", "message" => "Validasi gagal.", "errors" => $errors]);
        exit;
    }

    // ── Update ke database ────────────────────────────────
    $stmt = $conn->prepare("
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
            tanggal_masuk = ?,
            status        = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $kode_barang, $nama_barang, $id_kategori, $id_satuan,
        $jumlah, $harga, $stok_minimum,
        $lokasi    ?: null,
        $deskripsi ?: null,
        $tanggal_masuk, $status, $id
    ]);

    http_response_code(200);
    echo json_encode([
        "status"  => "success",
        "message" => "Barang id=$id berhasil diperbarui.",
        "data"    => [
            "id"           => $id,
            "kode_barang"  => $kode_barang,
            "nama_barang"  => $nama_barang,
            "id_kategori"  => $id_kategori,
            "id_satuan"    => $id_satuan,
            "jumlah"       => $jumlah,
            "harga"        => $harga,
            "stok_minimum" => $stok_minimum,
            "lokasi"       => $lokasi    ?: null,
            "deskripsi"    => $deskripsi ?: null,
            "tanggal_masuk"=> $tanggal_masuk,
            "status"       => $status
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Gagal memperbarui data: " . $e->getMessage()]);
}
