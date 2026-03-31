<?php
$current  = basename($_SERVER['PHP_SELF']);
$in_pages = basename(dirname($_SERVER['PHP_SELF'])) === 'pages';
$base     = $in_pages ? '../' : '';

function navItem(string $href, string $icon, string $label, string $current, string $page): string {
    $active = ($current === $page) ? 'active' : '';
    return "
    <a href=\"{$href}\" class=\"nav-item {$active}\">
        <i class=\"bi {$icon}\"></i> {$label}
    </a>";
}

// Hitung badge stok rendah & habis
$badge_rendah = 0;
$badge_habis  = 0;
try {
    $k = $base . 'koneksi.php';
    if (file_exists($k)) {
        require_once $k;
        $badge_rendah = (int)$pdo->query("SELECT COUNT(*) FROM barang WHERE jumlah <= stok_minimum AND jumlah > 0")->fetchColumn();
        $badge_habis  = (int)$pdo->query("SELECT COUNT(*) FROM barang WHERE jumlah = 0 OR status = 'habis'")->fetchColumn();
    }
} catch (Exception $e) {}
?>

<!-- SIDEBAR -->
<div class="sidebar">
    <style>
        .nav-section {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.3);
            text-transform: uppercase;
            padding: 1rem 1rem 0.3rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1rem;
            color: rgba(255,255,255,0.65);
            text-decoration: none;
            font-size: 0.85rem;
            transition: background 0.15s;
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.08);
            color: #fff;
        }

        .nav-item.active {
            background: var(--red);
            color: #fff;
            font-weight: 600;
        }

        .nav-item .nav-badge {
            margin-left: auto;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.1rem 0.4rem;
            border-radius: 10px;
            background: var(--red-light);
            color: var(--red);
        }

        .nav-divider {
            border-color: rgba(255,255,255,0.08);
            margin: 0.4rem 0;
        }

        .sidebar-footer {
            padding: 1rem;
            font-size: 0.72rem;
            color: rgba(255,255,255,0.25);
            border-top: 1px solid rgba(255,255,255,0.07);
            margin-top: 1rem;
        }
    </style>

    <div class="nav-section">Menu</div>

    <?= navItem($base . 'pages/dashboard.php', 'bi-speedometer2', 'Dashboard',    $current, 'dashboard.php') ?>
    <?= navItem($base . 'pages/data_barang.php', 'bi-box-seam',   'Data Barang',  $current, 'data_barang.php') ?>
    <?= navItem($base . 'pages/create.php', 'bi-plus-circle',     'Tambah Barang',$current, 'create.php') ?>

    <hr class="nav-divider">
    <div class="nav-section">Laporan</div>

    <a href="<?= $base ?>pages/data_barang.php?filter=rendah" class="nav-item">
        <i class="bi bi-exclamation-triangle"></i> Stok Rendah
        <?php if ($badge_rendah > 0): ?>
            <span class="nav-badge"><?= $badge_rendah ?></span>
        <?php endif; ?>
    </a>

    <a href="<?= $base ?>pages/data_barang.php?filter=habis" class="nav-item">
        <i class="bi bi-x-circle"></i> Stok Habis
        <?php if ($badge_habis > 0): ?>
            <span class="nav-badge"><?= $badge_habis ?></span>
        <?php endif; ?>
    </a>

    <div class="sidebar-footer">
        InvenRed v1.0 &mdash; Admin
    </div>

    
</div>