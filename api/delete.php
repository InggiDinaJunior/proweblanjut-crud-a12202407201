<?php
// api/delete.php
// METHOD : POST atau DELETE
// URL    : http://localhost/inventaris_mvc/api/delete.php
// DESC   : Menerima id barang yang akan dihapus dan menghapusnya dari database

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Izinkan POST dan DELETE
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'DELETE'])) {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method tidak diizinkan. Gunakan POST atau DELETE."]);
    exit;
}

// ── Baca input ────────────────────────────────────────────
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($content_type, 'application/json') !== false) {
    $raw  = file_get_contents("php://input");
    $body = json_decode($raw, true) ?? [];
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $body);
} else {
    $body = $_POST;
}

// ── Ambil id ─────────────────────────────────────────────
$id = (int)($body['id'] ?? 0);

if ($id === 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Field 'id' wajib diisi untuk menentukan barang yang dihapus."]);
    exit;
}

// Koneksi database
require_once __DIR__ . '/../config/database.php';

try {
    // Cek apakah barang ada
    $cek = $conn->prepare("SELECT id, nama_barang, gambar FROM barang WHERE id = ?");
    $cek->execute([$id]);
    $barang = $cek->fetch(PDO::FETCH_ASSOC);

    if (!$barang) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Barang dengan id=$id tidak ditemukan."]);
        exit;
    }

    // Hapus file gambar jika ada
    if ($barang['gambar']) {
        $path_gambar = __DIR__ . '/../uploads/' . $barang['gambar'];
        if (file_exists($path_gambar)) {
            unlink($path_gambar);
        }
    }

    // Hapus dari database
    $stmt = $conn->prepare("DELETE FROM barang WHERE id = ?");
    $stmt->execute([$id]);

    http_response_code(200);
    echo json_encode([
        "status"  => "success",
        "message" => "Barang '{$barang['nama_barang']}' (id=$id) berhasil dihapus.",
        "data"    => [
            "id"          => (int)$barang['id'],
            "nama_barang" => $barang['nama_barang']
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Gagal menghapus data: " . $e->getMessage()]);
}
