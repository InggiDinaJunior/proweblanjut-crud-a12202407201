<?php
session_start();
require_once '../koneksi.php';

$page_title = 'Dashboard';

// Statistik
$total_barang = (int)$pdo->query("SELECT COUNT(*) FROM barang")->fetchColumn();
$total_nilai  = (float)$pdo->query("SELECT SUM(jumlah * harga) FROM barang")->fetchColumn();
$stok_rendah  = (int)$pdo->query("SELECT COUNT(*) FROM barang WHERE jumlah <= stok_minimum AND jumlah > 0")->fetchColumn();
$stok_habis   = (int)$pdo->query("SELECT COUNT(*) FROM barang WHERE jumlah = 0 OR status = 'habis'")->fetchColumn();

// 5 barang terbaru
$barang_terbaru = $pdo->query("
    SELECT b.nama_barang, b.kode_barang, b.jumlah, b.harga,
           b.status, b.stok_minimum, k.nama_kategori
    FROM barang b
    JOIN kategori k ON b.id_kategori = k.id_kategori
    ORDER BY b.id DESC LIMIT 5
")->fetchAll();

// 5 barang stok kritis
$barang_kritis = $pdo->query("
    SELECT nama_barang, jumlah, stok_minimum, status
    FROM barang
    WHERE jumlah <= stok_minimum OR status = 'habis'
    ORDER BY jumlah ASC LIMIT 5
")->fetchAll();

require_once '../includes/header.php';
require_once '../includes/menu.php';
?>

<div class="main-wrapper">

    <!-- Page Header -->
    <div class="page-header">
        <h5><i class="bi bi-speedometer2 me-1" style="color:var(--red);"></i> Dashboard</h5>
        <p>Ringkasan inventaris per <?= date('d F Y') ?></p>
    </div>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon red"><i class="bi bi-box-seam"></i></div>
                <div>
                    <div class="stat-val"><?= $total_barang ?></div>
                    <div class="stat-label">Total Barang</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="stat-val" style="font-size:1rem;">
                        Rp <?= number_format($total_nilai, 0, ',', '.') ?>
                    </div>
                    <div class="stat-label">Total Nilai</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="bi bi-exclamation-triangle"></i></div>
                <div>
                    <div class="stat-val"><?= $stok_rendah ?></div>
                    <div class="stat-label">Stok Rendah</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon red"><i class="bi bi-x-circle"></i></div>
                <div>
                    <div class="stat-val"><?= $stok_habis ?></div>
                    <div class="stat-label">Stok Habis</div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL -->
    <div class="row g-3">

        <!-- Barang Terbaru -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header-red">
                    <i class="bi bi-clock-history"></i> Barang Terbaru
                    <a href="data_barang.php" class="ms-auto text-white-50 text-decoration-none"
                       style="font-size:0.75rem;">Lihat semua &rarr;</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-red table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th class="text-center">Stok</th>
                                <th class="text-end">Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($barang_terbaru)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    Belum ada data.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($barang_terbaru as $row): ?>
                            <tr>
                                <td style="font-size:0.78rem; color:var(--maroon);">
                                    <?= htmlspecialchars($row['kode_barang']) ?>
                                </td>
                                <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                <td style="font-size:0.8rem;">
                                    <?= htmlspecialchars($row['nama_kategori']) ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                        if ($row['jumlah'] == 0 || $row['status'] === 'habis') {
                                            echo '<span class="badge-pill badge-habis">Habis</span>';
                                        } elseif ($row['jumlah'] <= $row['stok_minimum']) {
                                            echo '<span class="badge-pill badge-rendah">' . $row['jumlah'] . '</span>';
                                        } else {
                                            echo '<span class="badge-pill badge-aman">' . $row['jumlah'] . '</span>';
                                        }
                                    ?>
                                </td>
                                <td class="text-end" style="font-size:0.82rem;">
                                    Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Stok Kritis -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header-red">
                    <i class="bi bi-exclamation-triangle"></i> Peringatan Stok
                    <a href="data_barang.php?filter=rendah" class="ms-auto text-white-50 text-decoration-none"
                       style="font-size:0.75rem;">Lihat semua &rarr;</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-red table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th class="text-center">Stok</th>
                                <th class="text-center">Min</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($barang_kritis)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    <i class="bi bi-check-circle text-success"></i>
                                    Semua stok aman.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($barang_kritis as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                <td class="text-center"><?= $row['jumlah'] ?></td>
                                <td class="text-center"><?= $row['stok_minimum'] ?></td>
                                <td class="text-center">
                                    <?php if ($row['jumlah'] == 0 || $row['status'] === 'habis'): ?>
                                        <span class="badge-pill badge-habis">Habis</span>
                                    <?php else: ?>
                                        <span class="badge-pill badge-rendah">Rendah</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>

<?php require_once '../includes/footer.php'; ?>