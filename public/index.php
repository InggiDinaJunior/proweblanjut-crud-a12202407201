<?php
// public/index.php
// FRONT CONTROLLER = satu-satunya pintu masuk semua request
// Semua URL menggunakan parameter ?action=

session_start();

// ── 1. Koneksi database ────────────────────────────────────
require_once __DIR__ . '/../config/database.php';

// ── 2. Guard session (cek login untuk halaman protected) ───
$public_actions = ['login', 'login_post', 'register', 'register_post'];
$action = $_GET['action'] ?? 'login';

if (!in_array($action, $public_actions)) {
    if (!isset($_SESSION['user_id'])) {
        if (isset($_COOKIE['remember_user_id'])) {
            $_SESSION['user_id'] = $_COOKIE['remember_user_id'];
        } else {
            header('Location: index.php?action=login');
            exit;
        }
    }
}

// ── 3. Load Controller sesuai action ──────────────────────
// Auth
if (in_array($action, ['login', 'login_post', 'register', 'register_post', 'logout'])) {
    require_once __DIR__ . '/../app/controllers/AuthController.php';
    $controller = new AuthController($conn);
}
// Dashboard
elseif ($action === 'dashboard') {
    require_once __DIR__ . '/../app/controllers/DashboardController.php';
    $controller = new DashboardController($conn);
}
// Barang
else {
    require_once __DIR__ . '/../app/controllers/BarangController.php';
    $controller = new BarangController($conn);
}

// ── 4. Routing — jalankan method yang sesuai ──────────────
switch ($action) {

    // Auth
    case 'login':           $controller->login();           break;
    case 'login_post':      $controller->loginPost();       break;
    case 'register':        $controller->register();        break;
    case 'register_post':   $controller->registerPost();    break;
    case 'logout':          $controller->logout();          break;

    // Dashboard
    case 'dashboard':       $controller->index();           break;

    // Barang - CRUD
    case 'barang':          $controller->index();           break;
    case 'tambah_barang':   $controller->create();          break;
    case 'simpan_barang':   $controller->store();           break;
    case 'edit_barang':     $controller->edit();            break;
    case 'update_barang':   $controller->update();          break;
    case 'hapus_barang':    $controller->delete();          break;

    default:
        header('Location: index.php?action=dashboard');
        exit;
}
