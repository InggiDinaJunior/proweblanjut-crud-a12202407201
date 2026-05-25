<?php
// includes/menu.php
// Sidebar navigasi — dipanggil oleh semua View yang butuh layout

$current_action = $_GET['action'] ?? 'dashboard';

// Hitung badge stok rendah & habis dari Model (via conn yang sudah ada)
$badge_rendah = 0;
$badge_habis  = 0;
try {
    $badge_rendah = (int)$conn->query("SELECT COUNT(*) FROM barang WHERE jumlah <= stok_minimum AND jumlah > 0")->fetchColumn();
    $badge_habis  = (int)$conn->query("SELECT COUNT(*) FROM barang WHERE jumlah = 0 OR status = 'habis'")->fetchColumn();
} catch (Exception $e) {}
?>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="nav-section">Menu</div>

    <a href="index.php?action=dashboard" class="nav-item <?= $current_action === 'dashboard' ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="index.php?action=barang" class="nav-item <?= $current_action === 'barang' ? 'active' : '' ?>">
        <i class="bi bi-box-seam"></i> Data Barang
    </a>
    <a href="index.php?action=tambah_barang" class="nav-item <?= $current_action === 'tambah_barang' ? 'active' : '' ?>">
        <i class="bi bi-plus-circle"></i> Tambah Barang
    </a>

    <hr class="nav-divider">
    <div class="nav-section">Laporan</div>

    <a href="index.php?action=barang&filter=rendah" class="nav-item">
        <i class="bi bi-exclamation-triangle"></i> Stok Rendah
        <?php if ($badge_rendah > 0): ?>
            <span class="nav-badge"><?= $badge_rendah ?></span>
        <?php endif; ?>
    </a>
    <a href="index.php?action=barang&filter=habis" class="nav-item">
        <i class="bi bi-x-circle"></i> Stok Habis
        <?php if ($badge_habis > 0): ?>
            <span class="nav-badge"><?= $badge_habis ?></span>
        <?php endif; ?>
    </a>

    <hr class="nav-divider">
    <a href="index.php?action=logout" class="nav-item">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>
