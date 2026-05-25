<?php
// app/views/barang/index.php
// VIEW = tampilan daftar barang. Semua variabel dikirim oleh BarangController::index()
$page_title = 'Data Barang';
require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/menu.php';
?>

<div class="main-wrapper">

    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5><i class="bi bi-box-seam me-1" style="color:var(--red);"></i> Data Barang</h5>
            <p>Kelola seluruh data barang inventaris</p>
        </div>
        <a href="index.php?action=tambah_barang" class="btn btn-red btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Tambah Barang
        </a>
    </div>

    <!-- SEARCH & FILTER -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="action" value="barang">
                <div class="col-12 col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Cari nama / kode barang..."
                           value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
                <div class="col-6 col-md-2">
                    <select name="filter" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="aktif"    <?= ($filter ?? '') === 'aktif'    ? 'selected' : '' ?>>Aktif</option>
                        <option value="rendah"   <?= ($filter ?? '') === 'rendah'   ? 'selected' : '' ?>>Stok Rendah</option>
                        <option value="habis"    <?= ($filter ?? '') === 'habis'    ? 'selected' : '' ?>>Stok Habis</option>
                        <option value="nonaktif" <?= ($filter ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="kategori" class="form-select form-select-sm">
                        <option value="0">Semua Kategori</option>
                        <?php foreach ($kategori_list as $kat): ?>
                        <option value="<?= $kat['id_kategori'] ?>"
                            <?= ($kategori_f ?? 0) === (int)$kat['id_kategori'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kat['nama_kategori']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-red btn-sm flex-fill">
                        <i class="bi bi-search me-1"></i>Cari
                    </button>
                    <a href="index.php?action=barang" class="btn btn-outline-red btn-sm flex-fill">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL -->
    <div class="card border-0 shadow-sm">
        <div class="card-header-red">
            <i class="bi bi-table"></i> Daftar Barang
            <span class="ms-auto" style="font-size:0.75rem;opacity:0.75;"><?= $total_rows ?> data ditemukan</span>
        </div>
        <div class="table-responsive">
            <table class="table table-red table-sm table-bordered mb-0">
                <thead>
                    <tr>
                        <th>#</th><th>Gambar</th><th>Kode</th><th>Nama Barang</th>
                        <th>Kategori</th><th>Satuan</th>
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
                        <td colspan="12" class="text-center text-muted py-4">
                            <i class="bi bi-inbox d-block mb-1" style="font-size:1.5rem;"></i>
                            Tidak ada data barang.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($list as $i => $row):
                        if ($row['jumlah'] == 0 || $row['status'] === 'habis') {
                            $kondisi = 'Habis'; $kondisi_cls = 'badge-habis';
                        } elseif ($row['jumlah'] <= $row['stok_minimum']) {
                            $kondisi = 'Rendah'; $kondisi_cls = 'badge-rendah';
                        } else {
                            $kondisi = 'Aman'; $kondisi_cls = 'badge-aman';
                        }
                        $status_map = [
                            'aktif'    => ['badge-aktif',    'Aktif'],
                            'nonaktif' => ['badge-nonaktif', 'Nonaktif'],
                            'habis'    => ['badge-habis',    'Habis'],
                        ];
                        [$status_cls, $status_lbl] = $status_map[$row['status']] ?? ['badge-nonaktif', ucfirst($row['status'])];
                    ?>
                    <tr>
                        <td class="text-muted"><?= ($offset ?? 0) + $i + 1 ?></td>
                        <td class="text-center" style="width:70px;">
                            <?php if ($row['gambar']): ?>
                                <img src="../uploads/<?= htmlspecialchars($row['gambar']) ?>"
                                     style="width:50px;height:50px;object-fit:cover;border-radius:4px;border:1px solid #ddd;"
                                     onclick="showGambar('../uploads/<?= htmlspecialchars($row['gambar']) ?>','<?= htmlspecialchars(addslashes($row['nama_barang'])) ?>')"
                                     class="cursor-pointer">
                            <?php else: ?>
                                <div style="width:50px;height:50px;background:#f0f0f0;border-radius:4px;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;margin:auto;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:0.78rem;color:var(--maroon);"><?= htmlspecialchars($row['kode_barang']) ?></td>
                        <td>
                            <?= htmlspecialchars($row['nama_barang']) ?>
                            <?php if ($row['lokasi']): ?>
                                <div style="font-size:0.72rem;color:#aaa;"><?= htmlspecialchars($row['lokasi']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                        <td><?= htmlspecialchars($row['nama_satuan']) ?></td>
                        <td class="text-center fw-bold"><?= number_format($row['jumlah']) ?></td>
                        <td class="text-end" style="font-size:0.82rem;">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                        <td class="text-end" style="font-size:0.82rem;color:var(--red);font-weight:600;">
                            Rp <?= number_format($row['total_nilai'], 0, ',', '.') ?>
                        </td>
                        <td class="text-center"><span class="badge-pill <?= $status_cls ?>"><?= $status_lbl ?></span></td>
                        <td class="text-center"><span class="badge-pill <?= $kondisi_cls ?>"><?= $kondisi ?></span></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-secondary py-0 px-2"
                                    onclick="showDetail(<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>)"
                                    title="Detail"><i class="bi bi-eye"></i></button>
                            <a href="index.php?action=edit_barang&id=<?= $row['id'] ?>"
                               class="btn btn-sm btn-warning py-0 px-2" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form id="del-<?= $row['id'] ?>" action="index.php?action=hapus_barang"
                                  method="POST" class="d-inline">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="button" class="btn btn-sm btn-danger py-0 px-2"
                                        onclick="confirmDelete('del-<?= $row['id'] ?>','<?= htmlspecialchars(addslashes($row['nama_barang'])) ?>')"
                                        title="Hapus"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINASI -->
        <?php if (($total_pages ?? 1) > 1): ?>
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2"
             style="background:#fafafa;font-size:0.8rem;">
            <span class="text-muted">
                <?= ($offset ?? 0) + 1 ?>–<?= min(($offset ?? 0) + 10, $total_rows) ?>
                dari <?= $total_rows ?> data
            </span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?action=barang&page=<?= $page-1 ?>&search=<?= urlencode($search ?? '') ?>&filter=<?= urlencode($filter ?? '') ?>&kategori=<?= $kategori_f ?? 0 ?>">&laquo;</a>
                    </li>
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link"
                           href="?action=barang&page=<?= $p ?>&search=<?= urlencode($search ?? '') ?>&filter=<?= urlencode($filter ?? '') ?>&kategori=<?= $kategori_f ?? 0 ?>"
                           style="<?= $p === $page ? 'background:var(--red);border-color:var(--red);' : '' ?>"><?= $p ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?action=barang&page=<?= $page+1 ?>&search=<?= urlencode($search ?? '') ?>&filter=<?= urlencode($filter ?? '') ?>&kategori=<?= $kategori_f ?? 0 ?>">&raquo;</a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /.main-wrapper -->

<!-- Modal Detail -->
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--red-dark);color:#fff;border:none;">
                <h6 class="modal-title fw-bold"><i class="bi bi-box-seam me-1"></i> Detail Barang</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="detailModalBody"></div>
            <div class="modal-footer py-2">
                <a href="#" id="btnEditDetail" class="btn btn-warning btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Gambar -->
<div class="modal fade" id="modalGambar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:transparent;border:none;">
            <div class="modal-body text-center p-2">
                <img id="gambarBesar" src="" alt="" style="max-width:100%;max-height:80vh;border-radius:8px;">
                <div id="gambarNama" class="text-white mt-2" style="font-size:0.85rem;"></div>
            </div>
        </div>
    </div>
</div>

<script>
function showDetail(data) {
    const fmt = n => 'Rp ' + parseInt(n).toLocaleString('id-ID');
    const kondisi = data.jumlah == 0 || data.status === 'habis' ? 'Habis'
        : data.jumlah <= data.stok_minimum ? 'Stok Rendah' : 'Aman';
    const gambarHtml = data.gambar
        ? `<div class="text-center p-3 border-bottom"><img src="../uploads/${data.gambar}" style="max-height:150px;border-radius:6px;border:1px solid #ddd;"></div>`
        : '';
    const rows = [
        ['Kode Barang', `<span style="color:var(--maroon);font-weight:600;">${data.kode_barang}</span>`],
        ['Nama Barang', `<strong>${data.nama_barang}</strong>`],
        ['Kategori', data.nama_kategori], ['Satuan', data.nama_satuan],
        ['Lokasi', data.lokasi || '<span class="text-muted">—</span>'],
        ['Jumlah Stok', `<strong>${parseInt(data.jumlah).toLocaleString('id-ID')}</strong>`],
        ['Stok Minimum', data.stok_minimum], ['Harga Satuan', fmt(data.harga)],
        ['Total Nilai', `<strong style="color:var(--red);">${fmt(data.total_nilai)}</strong>`],
        ['Tanggal Masuk', data.tanggal_masuk], ['Status', data.status],
        ['Kondisi Stok', kondisi],
        ['Deskripsi', data.deskripsi || '<span class="text-muted">—</span>'],
    ];
    let html = '';
    rows.forEach(([l, v]) => {
        html += `<tr><td style="width:40%;background:#f9f9f9;font-weight:600;font-size:0.82rem;color:#555;">${l}</td><td style="font-size:0.85rem;">${v}</td></tr>`;
    });
    document.getElementById('detailModalBody').innerHTML = gambarHtml + `<table class="table table-bordered table-sm mb-0">${html}</table>`;
    document.getElementById('btnEditDetail').href = 'index.php?action=edit_barang&id=' + data.id;
    new bootstrap.Modal(document.getElementById('modalDetail')).show();
}
function showGambar(src, nama) {
    document.getElementById('gambarBesar').src = src;
    document.getElementById('gambarNama').textContent = nama;
    new bootstrap.Modal(document.getElementById('modalGambar')).show();
}
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
