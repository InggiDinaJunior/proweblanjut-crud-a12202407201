<?php
session_start();
require_once '../koneksi.php';

$page_title = 'Data Barang';

//  PARAMETER SEARCH, FILTER & PAGINASI
$search     = trim($_GET['search']    ?? '');
$filter     = trim($_GET['filter']    ?? '');
$kategori_f = (int)($_GET['kategori'] ?? 0);
$page       = max(1, (int)($_GET['page'] ?? 1));
$per_page   = 10;
$offset     = ($page - 1) * $per_page;

//  BUILD QUERY DINAMIS
$where  = [];
$params = [];

if ($search !== '') {
    $where[]           = "(b.nama_barang LIKE :search OR b.kode_barang LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($filter === 'rendah') {
    $where[] = "b.jumlah <= b.stok_minimum AND b.jumlah > 0";
} elseif ($filter === 'habis') {
    $where[] = "(b.jumlah = 0 OR b.status = 'habis')";
} elseif ($filter === 'aktif') {
    $where[] = "b.status = 'aktif'";
} elseif ($filter === 'nonaktif') {
    $where[] = "b.status = 'nonaktif'";
}

if ($kategori_f > 0) {
    $where[]             = "b.id_kategori = :kategori";
    $params[':kategori'] = $kategori_f;
}

$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Hitung total baris
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM barang b JOIN kategori k ON b.id_kategori = k.id_kategori $where_sql");
$count_stmt->execute($params);
$total_rows  = (int)$count_stmt->fetchColumn();
$total_pages = (int)ceil($total_rows / $per_page);

// Query data
$stmt = $pdo->prepare("
    SELECT b.id, b.kode_barang, b.nama_barang, b.jumlah, b.harga,
           b.stok_minimum, b.lokasi, b.tanggal_masuk, b.status,
           k.nama_kategori, s.nama_satuan,
           (b.jumlah * b.harga) AS total_nilai
    FROM barang b
    JOIN kategori k ON b.id_kategori = k.id_kategori
    JOIN satuan   s ON b.id_satuan   = s.id_satuan
    $where_sql
    ORDER BY b.id DESC
    LIMIT :limit OFFSET :offset
");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
$stmt->execute();
$list = $stmt->fetchAll();

// Kategori untuk dropdown filter
$kategori_list = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();

require_once '../includes/header.php';
require_once '../includes/menu.php';
?>

<div class="main-wrapper">

    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5><i class="bi bi-box-seam me-1" style="color:var(--red);"></i> Data Barang</h5>
            <p>Kelola seluruh data barang inventaris</p>
        </div>
        <a href="create.php" class="btn btn-red btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Tambah Barang
        </a>
    </div>

    <!-- Flash message -->
    <?php if (isset($_SESSION['flash'])):
        $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismiss py-2"
         style="font-size:0.85rem;">
        <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'x-circle' ?> me-1"></i>
        <?= htmlspecialchars($flash['message']) ?>
    </div>
    <?php endif; ?>

    <!-- SEARCH & FILTER -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Cari nama / kode barang..."
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-6 col-md-2">
                    <select name="filter" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="aktif"    <?= $filter === 'aktif'    ? 'selected' : '' ?>>Aktif</option>
                        <option value="rendah"   <?= $filter === 'rendah'   ? 'selected' : '' ?>>Stok Rendah</option>
                        <option value="habis"    <?= $filter === 'habis'    ? 'selected' : '' ?>>Stok Habis</option>
                        <option value="nonaktif" <?= $filter === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="kategori" class="form-select form-select-sm">
                        <option value="0">Semua Kategori</option>
                        <?php foreach ($kategori_list as $kat): ?>
                        <option value="<?= $kat['id_kategori'] ?>"
                            <?= $kategori_f === (int)$kat['id_kategori'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kat['nama_kategori']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-red btn-sm flex-fill">
                        <i class="bi bi-search me-1"></i>Cari
                    </button>
                    <a href="data_barang.php" class="btn btn-outline-red btn-sm flex-fill">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL DATA BARANG -->
    <div class="card border-0 shadow-sm">
        <div class="card-header-red">
            <i class="bi bi-table"></i> Daftar Barang
            <span class="ms-auto" style="font-size:0.75rem; opacity:0.75;">
                <?= $total_rows ?> data ditemukan
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-red table-sm table-bordered mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th class="text-center">Stok</th>
                        <th class="text-end">Harga</th>
                        <th class="text-end">Total Nilai</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Kondisi</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            <i class="bi bi-inbox d-block mb-1" style="font-size:1.5rem;"></i>
                            Tidak ada data barang.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($list as $i => $row): ?>
                    <?php
                        // Kondisi stok
                        if ($row['jumlah'] == 0 || $row['status'] === 'habis') {
                            $kondisi = 'Habis'; $kondisi_cls = 'badge-habis';
                        } elseif ($row['jumlah'] <= $row['stok_minimum']) {
                            $kondisi = 'Rendah'; $kondisi_cls = 'badge-rendah';
                        } else {
                            $kondisi = 'Aman'; $kondisi_cls = 'badge-aman';
                        }

                        // Status
                        switch ($row['status']) {
                            case 'aktif':    $status_cls = 'badge-aktif';    $status_lbl = 'Aktif';    break;
                            case 'nonaktif': $status_cls = 'badge-nonaktif'; $status_lbl = 'Nonaktif'; break;
                            case 'habis':    $status_cls = 'badge-habis';    $status_lbl = 'Habis';    break;
                            default:         $status_cls = 'badge-nonaktif'; $status_lbl = ucfirst($row['status']); break;
                        }
                    ?>
                    <tr>
                        <td class="text-muted"><?= $offset + $i + 1 ?></td>
                        <td style="font-size:0.78rem; color:var(--maroon);">
                            <?= htmlspecialchars($row['kode_barang']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($row['nama_barang']) ?>
                            <?php if ($row['lokasi']): ?>
                                <div style="font-size:0.72rem; color:#aaa;">
                                    <?= htmlspecialchars($row['lokasi']) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                        <td><?= htmlspecialchars($row['nama_satuan']) ?></td>
                        <td class="text-center fw-bold"><?= number_format($row['jumlah']) ?></td>
                        <td class="text-end" style="font-size:0.82rem;">
                            Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                        </td>
                        <td class="text-end" style="font-size:0.82rem; color:var(--red); font-weight:600;">
                            Rp <?= number_format($row['total_nilai'], 0, ',', '.') ?>
                        </td>
                        <td class="text-center">
                            <span class="badge-pill <?= $status_cls ?>"><?= $status_lbl ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge-pill <?= $kondisi_cls ?>"><?= $kondisi ?></span>
                        </td>
                        <td class="text-center">
                            <a href="edit.php?id=<?= $row['id'] ?>"
                               class="btn btn-sm btn-warning py-0 px-2"
                               title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form id="del-<?= $row['id'] ?>" action="delete.php"
                                  method="POST" class="d-inline">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="button"
                                        class="btn btn-sm btn-danger py-0 px-2"
                                        onclick="confirmDelete('del-<?= $row['id'] ?>','<?= htmlspecialchars(addslashes($row['nama_barang'])) ?>')"
                                        title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINASI -->
        <?php if ($total_pages > 1): ?>
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2"
             style="background:#fafafa; font-size:0.8rem;">
            <span class="text-muted">
                <?= $offset + 1 ?>–<?= min($offset + $per_page, $total_rows) ?>
                dari <?= $total_rows ?> data
            </span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link"
                           href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>&kategori=<?= $kategori_f ?>">
                            &laquo;
                        </a>
                    </li>
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link"
                           href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>&kategori=<?= $kategori_f ?>"
                           style="<?= $p === $page ? 'background:var(--red);border-color:var(--red);' : '' ?>">
                            <?= $p ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link"
                           href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>&kategori=<?= $kategori_f ?>">
                            &raquo;
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>

    </div>

</div>

<?php require_once '../includes/footer.php'; ?>