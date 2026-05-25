<?php
// app/views/dashboard/index.php
// VIEW = hanya menampilkan data. Variabel dikirim oleh DashboardController.
$page_title = 'Dashboard';
require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/menu.php';
?>

<div class="main-wrapper">

    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h5><i class="bi bi-speedometer2 me-1" style="color:var(--red);"></i> Dashboard</h5>
            <p>Ringkasan inventaris per <?= date('d F Y') ?> &mdash;
               Selamat datang, <strong><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></strong>
            </p>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon red"><i class="bi bi-box-seam"></i></div>
                <div>
                    <div class="stat-val"><?= $statistik['total_barang'] ?></div>
                    <div class="stat-label">Total Barang</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="stat-val" style="font-size:1rem;">
                        Rp <?= number_format($statistik['total_nilai'], 0, ',', '.') ?>
                    </div>
                    <div class="stat-label">Total Nilai</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="bi bi-exclamation-triangle"></i></div>
                <div>
                    <div class="stat-val"><?= $statistik['stok_rendah'] ?></div>
                    <div class="stat-label">Stok Rendah</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon red"><i class="bi bi-x-circle"></i></div>
                <div>
                    <div class="stat-val"><?= $statistik['stok_habis'] ?></div>
                    <div class="stat-label">Stok Habis</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Barang Terbaru -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header-red">
                    <i class="bi bi-clock-history"></i> Barang Terbaru
                    <a href="index.php?action=barang" class="ms-auto text-white-50 text-decoration-none" style="font-size:0.75rem;">
                        Lihat semua &rarr;
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-red table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th><th>Nama Barang</th><th>Kategori</th>
                                <th class="text-center">Stok</th><th class="text-end">Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($barang_terbaru)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada data.</td></tr>
                            <?php else: ?>
                            <?php foreach ($barang_terbaru as $row): ?>
                            <tr>
                                <td style="font-size:0.78rem;color:var(--maroon);"><?= htmlspecialchars($row['kode_barang']) ?></td>
                                <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                <td style="font-size:0.8rem;"><?= htmlspecialchars($row['nama_kategori']) ?></td>
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
                                <td class="text-end" style="font-size:0.82rem;">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
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
                </div>
                <div class="table-responsive">
                    <table class="table table-red table-sm table-bordered mb-0">
                        <thead>
                            <tr><th>Nama Barang</th><th class="text-center">Stok</th><th class="text-center">Min</th><th class="text-center">Status</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($barang_kritis)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3"><i class="bi bi-check-circle text-success"></i> Semua stok aman.</td></tr>
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

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
