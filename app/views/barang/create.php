<?php
// app/views/barang/create.php
// VIEW = form tambah barang. Variabel: $old, $errors, $kategori_list, $satuan_list
$page_title = 'Tambah Barang';
require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/menu.php';
?>

<div class="main-wrapper">

    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5><i class="bi bi-plus-circle me-1" style="color:var(--red);"></i> Tambah Barang</h5>
            <p>Isi form untuk menambahkan barang baru</p>
        </div>
        <a href="index.php?action=barang" class="btn btn-outline-red btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <?php if (isset($errors['global'])): ?>
    <div class="alert alert-danger py-2 mb-3" style="font-size:0.85rem;">
        <?= htmlspecialchars($errors['global']) ?>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header-red"><i class="bi bi-pencil-square"></i> Form Tambah Barang</div>
        <div class="card-body">
            <form method="POST" action="index.php?action=simpan_barang" enctype="multipart/form-data">
                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Kode Barang <span class="text-danger">*</span></label>
                        <input type="text" name="kode_barang"
                               class="form-control form-control-sm <?= isset($errors['kode_barang']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($old['kode_barang']) ?>">
                        <?php if (isset($errors['kode_barang'])): ?>
                            <div class="invalid-feedback"><?= $errors['kode_barang'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="nama_barang"
                               class="form-control form-control-sm <?= isset($errors['nama_barang']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($old['nama_barang']) ?>" placeholder="Nama barang...">
                        <?php if (isset($errors['nama_barang'])): ?>
                            <div class="invalid-feedback"><?= $errors['nama_barang'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                        <select name="id_kategori" class="form-select form-select-sm <?= isset($errors['id_kategori']) ? 'is-invalid' : '' ?>">
                            <option value="0">-- Pilih Kategori --</option>
                            <?php foreach ($kategori_list as $kat): ?>
                            <option value="<?= $kat['id_kategori'] ?>"
                                <?= (int)$old['id_kategori'] === (int)$kat['id_kategori'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat['nama_kategori']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['id_kategori'])): ?>
                            <div class="invalid-feedback"><?= $errors['id_kategori'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Satuan <span class="text-danger">*</span></label>
                        <select name="id_satuan" class="form-select form-select-sm <?= isset($errors['id_satuan']) ? 'is-invalid' : '' ?>">
                            <option value="0">-- Pilih Satuan --</option>
                            <?php foreach ($satuan_list as $sat): ?>
                            <option value="<?= $sat['id_satuan'] ?>"
                                <?= (int)$old['id_satuan'] === (int)$sat['id_satuan'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sat['nama_satuan']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['id_satuan'])): ?>
                            <div class="invalid-feedback"><?= $errors['id_satuan'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jumlah <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah" min="0"
                               class="form-control form-control-sm <?= isset($errors['jumlah']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($old['jumlah']) ?>" placeholder="0">
                        <?php if (isset($errors['jumlah'])): ?>
                            <div class="invalid-feedback"><?= $errors['jumlah'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Stok Minimum <span class="text-danger">*</span></label>
                        <input type="number" name="stok_minimum" min="0"
                               class="form-control form-control-sm <?= isset($errors['stok_minimum']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($old['stok_minimum']) ?>">
                        <?php if (isset($errors['stok_minimum'])): ?>
                            <div class="invalid-feedback"><?= $errors['stok_minimum'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Harga Satuan <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="harga" min="0" step="100"
                                   class="form-control <?= isset($errors['harga']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($old['harga']) ?>" placeholder="0">
                            <?php if (isset($errors['harga'])): ?>
                                <div class="invalid-feedback"><?= $errors['harga'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Lokasi Penyimpanan</label>
                        <input type="text" name="lokasi" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($old['lokasi']) ?>" placeholder="Contoh: Rak A-01">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tanggal Masuk <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_masuk"
                               class="form-control form-control-sm <?= isset($errors['tanggal_masuk']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($old['tanggal_masuk']) ?>">
                        <?php if (isset($errors['tanggal_masuk'])): ?>
                            <div class="invalid-feedback"><?= $errors['tanggal_masuk'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="aktif"    <?= $old['status'] === 'aktif'    ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= $old['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                            <option value="habis"    <?= $old['status'] === 'habis'    ? 'selected' : '' ?>>Habis</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="deskripsi" rows="3" class="form-control form-control-sm"
                                  placeholder="Keterangan tambahan (opsional)..."><?= htmlspecialchars($old['deskripsi']) ?></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Gambar Barang</label>
                        <input type="file" name="gambar" accept=".jpg,.jpeg,.png"
                               class="form-control form-control-sm <?= isset($errors['gambar']) ? 'is-invalid' : '' ?>">
                        <?php if (isset($errors['gambar'])): ?>
                            <div class="invalid-feedback"><?= $errors['gambar'] ?></div>
                        <?php endif; ?>
                        <small class="text-muted" style="font-size:0.75rem;">Format: JPG, PNG. Maks 2 MB. (Opsional)</small>
                    </div>

                    <div class="col-12 d-flex gap-2 pt-1">
                        <button type="submit" class="btn btn-red btn-sm">
                            <i class="bi bi-check-circle me-1"></i>Simpan Barang
                        </button>
                        <a href="index.php?action=barang" class="btn btn-outline-red btn-sm">Batal</a>
                    </div>

                </div>
            </form>
        </div>
    </div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
