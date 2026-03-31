<?php
session_start();

// Guard: cek session atau cookie remember me
if (!isset($_SESSION['user_id'])) {
    if (isset($_COOKIE['remember_user_id'])) {
        // Pulihkan session dari cookie
        $_SESSION['user_id'] = $_COOKIE['remember_user_id'];
    } else {
        // Belum login, redirect ke login
        header('Location: ../login.php');
        exit();
    }
}
require_once '../koneksi.php';

$page_title = 'Tambah Barang';

function generateKode(PDO $pdo): string {
    $last = $pdo->query("SELECT kode_barang FROM barang ORDER BY id DESC LIMIT 1")->fetchColumn();
    $next = 1;
    if ($last && preg_match('/BRG-(\d+)/', $last, $m)) {
        $next = (int)$m[1] + 1;
    }
    return 'BRG-' . str_pad($next, 4, '0', STR_PAD_LEFT);
}

$kategori_list = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
$satuan_list   = $pdo->query("SELECT * FROM satuan ORDER BY nama_satuan")->fetchAll();

$errors = [];
$old    = [
    'kode_barang'   => generateKode($pdo),
    'nama_barang'   => '',
    'id_kategori'   => 0,
    'id_satuan'     => 0,
    'jumlah'        => '',
    'harga'         => '',
    'stok_minimum'  => '5',
    'lokasi'        => '',
    'deskripsi'     => '',
    'tanggal_masuk' => date('Y-m-d'),
    'status'        => 'aktif',
];

// SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'kode_barang'   => trim($_POST['kode_barang']   ?? ''),
        'nama_barang'   => trim($_POST['nama_barang']   ?? ''),
        'id_kategori'   => (int)($_POST['id_kategori']  ?? 0),
        'id_satuan'     => (int)($_POST['id_satuan']    ?? 0),
        'jumlah'        => trim($_POST['jumlah']        ?? ''),
        'harga'         => trim($_POST['harga']         ?? ''),
        'stok_minimum'  => trim($_POST['stok_minimum']  ?? ''),
        'lokasi'        => trim($_POST['lokasi']        ?? ''),
        'deskripsi'     => trim($_POST['deskripsi']     ?? ''),
        'tanggal_masuk' => trim($_POST['tanggal_masuk'] ?? ''),
        'status'        => trim($_POST['status']        ?? 'aktif'),
    ];

    // Validasi
    if ($old['kode_barang'] === '')   $errors['kode_barang']   = 'Wajib diisi.';
    if ($old['nama_barang'] === '')   $errors['nama_barang']   = 'Wajib diisi.';
    if ($old['id_kategori'] === 0)    $errors['id_kategori']   = 'Pilih kategori.';
    if ($old['id_satuan']   === 0)    $errors['id_satuan']     = 'Pilih satuan.';
    if (!is_numeric($old['jumlah']) || (int)$old['jumlah'] < 0)
                                      $errors['jumlah']        = 'Harus angka positif.';
    if (!is_numeric($old['harga'])  || (float)$old['harga'] < 0)
                                      $errors['harga']         = 'Harus angka positif.';
    if (!is_numeric($old['stok_minimum']) || (int)$old['stok_minimum'] < 0)
                                      $errors['stok_minimum']  = 'Harus angka positif.';
    if ($old['tanggal_masuk'] === '') $errors['tanggal_masuk'] = 'Wajib diisi.';

    // Cek duplikat kode
    if (empty($errors['kode_barang'])) {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM barang WHERE kode_barang = :kode");
        $chk->execute([':kode' => $old['kode_barang']]);
        if ((int)$chk->fetchColumn() > 0)
            $errors['kode_barang'] = 'Kode sudah digunakan.';
    }

    if (empty($errors)) {
        try {
            $pdo->prepare("
                INSERT INTO barang
                    (kode_barang, nama_barang, id_kategori, id_satuan,
                     jumlah, harga, stok_minimum, lokasi, deskripsi,
                     tanggal_masuk, status)
                VALUES
                    (:kode, :nama, :id_kategori, :id_satuan,
                     :jumlah, :harga, :stok_min, :lokasi, :deskripsi,
                     :tgl, :status)
            ")->execute([
                ':kode'        => $old['kode_barang'],
                ':nama'        => $old['nama_barang'],
                ':id_kategori' => $old['id_kategori'],
                ':id_satuan'   => $old['id_satuan'],
                ':jumlah'      => (int)$old['jumlah'],
                ':harga'       => (float)$old['harga'],
                ':stok_min'    => (int)$old['stok_minimum'],
                ':lokasi'      => $old['lokasi']    ?: null,
                ':deskripsi'   => $old['deskripsi'] ?: null,
                ':tgl'         => $old['tanggal_masuk'],
                ':status'      => $old['status'],
            ]);

            $_SESSION['flash'] = [
                'type'    => 'success',
                'message' => "Barang \"{$old['nama_barang']}\" berhasil ditambahkan."
            ];
            header('Location: data_barang.php');
            exit;

        } catch (PDOException $e) {
            $errors['global'] = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/menu.php';
?>

<div class="main-wrapper">

    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5><i class="bi bi-plus-circle me-1" style="color:var(--red);"></i> Tambah Barang</h5>
            <p>Isi form untuk menambahkan barang baru</p>
        </div>
        <a href="data_barang.php" class="btn btn-outline-red btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <?php if (isset($errors['global'])): ?>
    <div class="alert alert-danger py-2 mb-3" style="font-size:0.85rem;">
        <?= htmlspecialchars($errors['global']) ?>
    </div>
    <?php endif; ?>

    <!-- FORM -->
    <div class="card border-0 shadow-sm">
        <div class="card-header-red">
            <i class="bi bi-pencil-square"></i> Form Tambah Barang
        </div>
        <div class="card-body">
            <form method="POST" action="create.php">
                <div class="row g-3">

                    <!-- Kode Barang -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Kode Barang <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="kode_barang"
                               class="form-control form-control-sm <?= isset($errors['kode_barang']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($old['kode_barang']) ?>">
                        <?php if (isset($errors['kode_barang'])): ?>
                            <div class="invalid-feedback"><?= $errors['kode_barang'] ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Nama Barang -->
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">
                            Nama Barang <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama_barang"
                               class="form-control form-control-sm <?= isset($errors['nama_barang']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($old['nama_barang']) ?>"
                               placeholder="Nama barang...">
                        <?php if (isset($errors['nama_barang'])): ?>
                            <div class="invalid-feedback"><?= $errors['nama_barang'] ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Kategori -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Kategori <span class="text-danger">*</span>
                        </label>
                        <select name="id_kategori"
                                class="form-select form-select-sm <?= isset($errors['id_kategori']) ? 'is-invalid' : '' ?>">
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

                    <!-- Satuan -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Satuan <span class="text-danger">*</span>
                        </label>
                        <select name="id_satuan"
                                class="form-select form-select-sm <?= isset($errors['id_satuan']) ? 'is-invalid' : '' ?>">
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

                    <!-- Jumlah -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Jumlah <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="jumlah" min="0"
                               class="form-control form-control-sm <?= isset($errors['jumlah']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($old['jumlah']) ?>"
                               placeholder="0">
                        <?php if (isset($errors['jumlah'])): ?>
                            <div class="invalid-feedback"><?= $errors['jumlah'] ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Stok Minimum -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Stok Minimum <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="stok_minimum" min="0"
                               class="form-control form-control-sm <?= isset($errors['stok_minimum']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($old['stok_minimum']) ?>">
                        <?php if (isset($errors['stok_minimum'])): ?>
                            <div class="invalid-feedback"><?= $errors['stok_minimum'] ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Harga -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Harga Satuan <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="harga" min="0" step="100"
                                   class="form-control <?= isset($errors['harga']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($old['harga']) ?>"
                                   placeholder="0">
                            <?php if (isset($errors['harga'])): ?>
                                <div class="invalid-feedback"><?= $errors['harga'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Lokasi -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Lokasi Penyimpanan</label>
                        <input type="text" name="lokasi"
                               class="form-control form-control-sm"
                               value="<?= htmlspecialchars($old['lokasi']) ?>"
                               placeholder="Contoh: Rak A-01">
                    </div>

                    <!-- Tanggal Masuk -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            Tanggal Masuk <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="tanggal_masuk"
                               class="form-control form-control-sm <?= isset($errors['tanggal_masuk']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($old['tanggal_masuk']) ?>">
                        <?php if (isset($errors['tanggal_masuk'])): ?>
                            <div class="invalid-feedback"><?= $errors['tanggal_masuk'] ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Status -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="aktif"    <?= $old['status'] === 'aktif'    ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= $old['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                            <option value="habis"    <?= $old['status'] === 'habis'    ? 'selected' : '' ?>>Habis</option>
                        </select>
                    </div>

                    <!-- Deskripsi -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="deskripsi" rows="3"
                                  class="form-control form-control-sm"
                                  placeholder="Keterangan tambahan (opsional)..."><?= htmlspecialchars($old['deskripsi']) ?></textarea>
                    </div>

                    <!-- Tombol -->
                    <div class="col-12 d-flex gap-2 pt-1">
                        <button type="submit" class="btn btn-red btn-sm">
                            <i class="bi bi-check-circle me-1"></i>Simpan Barang
                        </button>
                        <a href="data_barang.php" class="btn btn-outline-red btn-sm">
                            Batal
                        </a>
                    </div>

                </div><!-- /.row -->
            </form>
        </div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>