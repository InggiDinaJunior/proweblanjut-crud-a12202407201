<?php
// app/controllers/BarangController.php
// CONTROLLER = menerima request → panggil Model → kirim data ke View

require_once __DIR__ . '/../../app/models/BarangModel.php';

class BarangController
{
    private $model;

    public function __construct($conn)
    {
        $this->model = new BarangModel($conn);
    }

    // ── Validasi input form barang ──────────────────────────
    private function validate($data, $exclude_id = 0)
    {
        $errors = [];
        if (empty($data['kode_barang']))  $errors['kode_barang']  = 'Kode barang wajib diisi.';
        if (empty($data['nama_barang']))  $errors['nama_barang']  = 'Nama barang wajib diisi.';
        if ((int)$data['id_kategori'] === 0) $errors['id_kategori'] = 'Pilih kategori.';
        if ((int)$data['id_satuan']   === 0) $errors['id_satuan']   = 'Pilih satuan.';
        if (!is_numeric($data['jumlah'])       || (int)$data['jumlah']      < 0) $errors['jumlah']       = 'Jumlah harus angka positif.';
        if (!is_numeric($data['harga'])        || (float)$data['harga']     < 0) $errors['harga']        = 'Harga harus angka positif.';
        if (!is_numeric($data['stok_minimum']) || (int)$data['stok_minimum'] < 0) $errors['stok_minimum'] = 'Stok minimum harus angka positif.';
        if (empty($data['tanggal_masuk']))  $errors['tanggal_masuk'] = 'Tanggal masuk wajib diisi.';

        if (empty($errors['kode_barang']) && $this->model->isKodeDuplikat($data['kode_barang'], $exclude_id)) {
            $errors['kode_barang'] = 'Kode barang sudah digunakan.';
        }
        return $errors;
    }

    // ── Proses upload gambar ────────────────────────────────
    private function uploadGambar(&$errors)
    {
        if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $file     = $_FILES['gambar'];
        $allowed  = ['jpg', 'jpeg', 'png'];
        $max_size = 2 * 1024 * 1024;
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['gambar'] = 'Kesalahan saat upload file.';
        } elseif (!in_array($ext, $allowed)) {
            $errors['gambar'] = 'Format tidak diizinkan. Gunakan JPG atau PNG.';
        } elseif ($file['size'] > $max_size) {
            $errors['gambar'] = 'Ukuran file maksimal 2 MB.';
        } else {
            $nama_file  = uniqid() . '_' . basename($file['name']);
            $upload_dir = __DIR__ . '/../../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $nama_file)) {
                return $nama_file;
            }
            $errors['gambar'] = 'Gagal menyimpan file.';
        }
        return null;
    }

    // ──────────────────────────────────────────────────────
    // READ - Daftar barang
    // ──────────────────────────────────────────────────────
    public function index()
    {
        $search     = trim($_GET['search']    ?? '');
        $filter     = trim($_GET['filter']    ?? '');
        $kategori_f = (int)($_GET['kategori'] ?? 0);
        $page       = max(1, (int)($_GET['page'] ?? 1));
        $per_page   = 10;
        $offset     = ($page - 1) * $per_page;

        $result        = $this->model->getAll($search, $filter, $kategori_f, $per_page, $offset);
        $list          = $result['data'];
        $total_rows    = $result['total'];
        $total_pages   = (int)ceil($total_rows / $per_page);
        $kategori_list = $this->model->getKategori();

        global $conn;
        
        require_once __DIR__ . '/../../app/views/barang/index.php';
    }

    // ──────────────────────────────────────────────────────
    // CREATE - Tampilkan form tambah
    // ──────────────────────────────────────────────────────
    public function create()
    {
        $kategori_list = $this->model->getKategori();
        $satuan_list   = $this->model->getSatuan();
        $errors = [];
        $old = [
            'kode_barang'   => $this->model->generateKode(),
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

        global $conn;

        require_once __DIR__ . '/../../app/views/barang/create.php';
    }

    // ──────────────────────────────────────────────────────
    // CREATE - Proses simpan data baru
    // ──────────────────────────────────────────────────────
    public function store()
    {
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

        $errors = $this->validate($old);
        $gambar = $this->uploadGambar($errors);

        if (empty($errors)) {
            $old['gambar'] = $gambar;
            $this->model->create($old);
            $_SESSION['flash'] = ['type' => 'success', 'message' => "Barang \"{$old['nama_barang']}\" berhasil ditambahkan."];
            header('Location: index.php?action=barang');
            exit;
        }

        // Jika ada error, tampilkan form lagi dengan pesan
        $kategori_list = $this->model->getKategori();
        $satuan_list   = $this->model->getSatuan();
        require_once __DIR__ . '/../../app/views/barang/create.php';
    }

    // ──────────────────────────────────────────────────────
    // UPDATE - Tampilkan form edit
    // ──────────────────────────────────────────────────────
    public function edit()
    {
        $id     = (int)($_GET['id'] ?? 0);
        $barang = $this->model->getById($id);

        if (!$barang) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Data tidak ditemukan.'];
            header('Location: index.php?action=barang');
            exit;
        }

        $kategori_list = $this->model->getKategori();
        $satuan_list   = $this->model->getSatuan();
        $errors = [];
        $old    = $barang;

        global $conn;

        require_once __DIR__ . '/../../app/views/barang/edit.php';
    }

    // ──────────────────────────────────────────────────────
    // UPDATE - Proses simpan perubahan
    // ──────────────────────────────────────────────────────
    public function update()
    {
        $id     = (int)($_POST['id'] ?? 0);
        $barang = $this->model->getById($id);

        if (!$barang) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Data tidak ditemukan.'];
            header('Location: index.php?action=barang');
            exit;
        }

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
            'gambar'        => $barang['gambar'], // default: gambar lama
        ];

        $errors = $this->validate($old, $id);

        // Proses upload gambar baru jika ada
        $gambar_baru = $this->uploadGambar($errors);
        if ($gambar_baru) {
            // Hapus gambar lama
            if ($barang['gambar']) {
                $path_lama = __DIR__ . '/../../uploads/' . $barang['gambar'];
                if (file_exists($path_lama)) unlink($path_lama);
            }
            $old['gambar'] = $gambar_baru;
        }

        if (empty($errors)) {
            $this->model->update($id, $old);
            $_SESSION['flash'] = ['type' => 'success', 'message' => "Barang \"{$old['nama_barang']}\" berhasil diperbarui."];
            header('Location: index.php?action=barang');
            exit;
        }

        $kategori_list = $this->model->getKategori();
        $satuan_list   = $this->model->getSatuan();
        require_once __DIR__ . '/../../app/views/barang/edit.php';
    }

    // ──────────────────────────────────────────────────────
    // DELETE - Hapus barang
    // ──────────────────────────────────────────────────────
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=barang');
            exit;
        }

        $id     = (int)($_POST['id'] ?? 0);
        $barang = $this->model->getById($id);

        if (!$barang) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Data tidak ditemukan.'];
            header('Location: index.php?action=barang');
            exit;
        }

        // Hapus file gambar jika ada
        if ($barang['gambar']) {
            $path = __DIR__ . '/../../uploads/' . $barang['gambar'];
            if (file_exists($path)) unlink($path);
        }

        $this->model->delete($id);
        $_SESSION['flash'] = ['type' => 'success', 'message' => "Barang \"{$barang['nama_barang']}\" berhasil dihapus."];
        header('Location: index.php?action=barang');
        exit;
    }
}
