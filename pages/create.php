<?php
// pages/create.php
session_start();

// Guard session
if (!isset($_SESSION['user_id'])) {
    if (isset($_COOKIE['remember_user_id']) && isset($_COOKIE['remember_token'])) {
        $_SESSION['user_id'] = $_COOKIE['remember_user_id'];
    } else {
        header('Location: ../login.php');
        exit();
    }
}

require_once '../koneksi.php';

$page_title = 'Tambah Barang';

// Generate kode barang otomatis
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

// Default nilai form
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

// ============================================================
//  PROSES SUBMIT
// ============================================================
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

    // --------------------------------------------------------
    //  VALIDASI INPUT
    // --------------------------------------------------------
    if ($old['kode_barang'] === '')
        $errors['kode_barang'] = 'Kode barang wajib diisi.';

    if ($old['nama_barang'] === '')
        $errors['nama_barang'] = 'Nama barang wajib diisi.';

    if ($old['id_kategori'] === 0)
        $errors['id_kategori'] = 'Pilih kategori.';

    if ($old['id_satuan'] === 0)
        $errors['id_satuan'] = 'Pilih satuan.';

    if (!is_numeric($old['jumlah']) || (int)$old['jumlah'] < 0)
        $errors['jumlah'] = 'Jumlah harus berupa angka positif.';

    if (!is_numeric($old['harga']) || (float)$old['harga'] < 0)
        $errors['harga'] = 'Harga harus berupa angka positif.';

    if (!is_numeric($old['stok_minimum']) || (int)$old['stok_minimum'] < 0)
        $errors['stok_minimum'] = 'Stok minimum harus berupa angka positif.';

    if ($old['tanggal_masuk'] === '')
        $errors['tanggal_masuk'] = 'Tanggal masuk wajib diisi.';

    // Cek duplikat kode barang
    if (empty($errors['kode_barang'])) {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM barang WHERE kode_barang = ?");
        $chk->execute([$old['kode_barang']]);
        if ((int)$chk->fetchColumn() > 0)
            $errors['kode_barang'] = 'Kode barang sudah digunakan.';
    }

    // --------------------------------------------------------
    //  VALIDASI & PROSES UPLOAD GAMBAR
    // --------------------------------------------------------
    $nama_file_gambar = null;

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {

        $file       = $_FILES['gambar'];
        $allowed    = ['jpg', 'jpeg', 'png'];
        $max_size   = 2 * 1024 * 1024; // 2 MB
        $ext        = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['gambar'] = 'Terjadi kesalahan saat upload file.';
        } elseif (!in_array($ext, $allowed)) {
            $errors['gambar'] = 'Format file tidak diizinkan. Gunakan JPG atau PNG.';
        } elseif ($file['size'] > $max_size) {
            $errors['gambar'] = 'Ukuran file maksimal 2 MB.';
        } else {
            // Buat nama file unik: uniqid + nama asli
            $nama_file_gambar = uniqid() . '_' . basename($file['name']);
            $upload_dir       = '../uploads/';

            // Buat folder uploads jika belum ada
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Pindahkan file dari direktori temporer ke folder uploads
            if (!move_uploaded_file($file['tmp_name'], $upload_dir . $nama_file_gambar)) {
                $errors['gambar']  = 'Gagal menyimpan file. Coba lagi.';
                $nama_file_gambar  = null;
            }
        }
    }

    // --------------------------------------------------------
    //  SIMPAN KE DATABASE (jika tidak ada error)
    // --------------------------------------------------------
    if (empty($errors)) {
        try {
            // Prepared statement INSERT
            $stmt = $pdo->prepare("
                INSERT INTO barang
                    (kode_barang, nama_barang, id_kategori, id_satuan,
                     jumlah, harga, stok_minimum, lokasi, deskripsi,
                     gambar, tanggal_masuk, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $old['kode_barang'],
                $old['nama_barang'],
                $old['id_kategori'],
                $old['id_satuan'],
                (int)$old['jumlah'],
                (float)$old['harga'],
                (int)$old['stok_minimum'],
                $old['lokasi']    ?: null,
                $old['deskripsi'] ?: null,
                $nama_file_gambar,
                $old['tanggal_masuk'],
                $old['status'],
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
    <!-- enctype="multipart/form-data" wajib ada untuk upload file -->
    <div class="card border-0 shadow-sm">
        <div class="card-header-red">
            <i class="bi bi-pencil-square"></i> Form Tambah Barang
        </div>
        <div class="card-body">
            <form method="POST" action="create.php" enctype="multipart/form-data">
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

                    <!-- Upload Gambar -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Gambar Barang
                        </label>
                        <input type="file" name="gambar" accept=".jpg,.jpeg,.png"
                               class="form-control form-control-sm <?= isset($errors['gambar']) ? 'is-invalid' : '' ?>">
                        <?php if (isset($errors['gambar'])): ?>
                            <div class="invalid-feedback"><?= $errors['gambar'] ?></div>
                        <?php endif; ?>
                        <small class="text-muted" style="font-size:0.75rem;">
                            Format: JPG, JPEG, PNG. Maksimal 2 MB. (Opsional)
                        </small>
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

</div><!-- /.main-wrapper -->

<?php require_once '../includes/footer.php'; ?>