<?php
// Handler penghapusan barang hanya menerima POST request
session_start();
require_once '../koneksi.php';

//  KEAMANAN: Tolak akses selain POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = [
        'type'    => 'error',
        'message' => 'Akses tidak diizinkan.'
    ];
    header('Location: data_barang.php');
    exit;
}

//  AMBIL & VALIDASI ID
$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash'] = [
        'type'    => 'error',
        'message' => 'ID barang tidak valid.'
    ];
    header('Location: data_barang.php');
    exit;
}

//  CEK KEBERADAAN DATA
$stmt = $pdo->prepare("SELECT id, nama_barang, kode_barang FROM barang WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$barang = $stmt->fetch();

if (!$barang) {
    $_SESSION['flash'] = [
        'type'    => 'error',
        'message' => 'Data barang tidak ditemukan atau sudah dihapus.'
    ];
    header('Location: data_barang.php');
    exit;
}

//  PROSES HAPUS
try {
    $stmt = $pdo->prepare("DELETE FROM barang WHERE id = :id");
    $stmt->execute([':id' => $id]);

    $_SESSION['flash'] = [
        'type'    => 'success',
        'message' => "Barang \"{$barang['nama_barang']}\" ({$barang['kode_barang']}) berhasil dihapus."
    ];

} catch (PDOException $e) {
    $_SESSION['flash'] = [
        'type'    => 'error',
        'message' => 'Gagal menghapus data: ' . $e->getMessage()
    ];
}

//  REDIRECT KEMBALI KE DATA BARANG
header('Location: data_barang.php');
exit;
?>