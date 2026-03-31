<?php
// register.php
session_start();
require_once 'koneksi.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validasi sederhana
    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } elseif (strlen($username) < 3) {
        $error = 'Username minimal 3 karakter.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        // Cek apakah username sudah digunakan
        $cek = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $cek->execute([$username]);

        if ($cek->fetch()) {
            $error = 'Username sudah digunakan, coba yang lain.';
        } else {
            // Hash password sebelum disimpan ke database
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Simpan ke database
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");

            if ($stmt->execute([$username, $hash])) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Gagal mendaftar, coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi — InvenRed</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f4f4;
            font-family: sans-serif;
        }

        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.10);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .auth-logo .logo-icon {
            width: 52px;
            height: 52px;
            background: #922B21;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.6rem;
        }

        .auth-logo h5 {
            font-weight: 700;
            color: #4A1210;
            margin: 0;
        }

        .auth-logo p {
            font-size: 0.8rem;
            color: #888;
            margin: 0;
        }

        .form-control:focus {
            border-color: #C0392B;
            box-shadow: 0 0 0 0.15rem rgba(192,57,43,0.2);
        }

        .btn-red {
            background: #C0392B;
            color: #fff;
            border: none;
            width: 100%;
            padding: 0.6rem;
            font-weight: 600;
            border-radius: 6px;
        }

        .btn-red:hover {
            background: #922B21;
            color: #fff;
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.25rem;
            font-size: 0.85rem;
            color: #888;
        }

        .auth-footer a {
            color: #C0392B;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">

        <!-- Logo -->
        <div class="auth-logo">
            <div class="logo-icon">
                <i class="bi bi-boxes text-white" style="font-size:1.4rem;"></i>
            </div>
            <h5>InvenRed</h5>
            <p>Buat akun baru</p>
        </div>

        <!-- Pesan error -->
        <?php if ($error): ?>
        <div class="alert alert-danger py-2 mb-3" style="font-size:0.85rem;">
            <i class="bi bi-x-circle me-1"></i><?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Pesan sukses -->
        <?php if ($success): ?>
        <div class="alert alert-success py-2 mb-3" style="font-size:0.85rem;">
            <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>

        <!-- Form Registrasi -->
        <form method="POST" action="register.php">

            <!-- Username -->
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:0.85rem;">Username</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:#FADBD8; border-color:#ddd;">
                        <i class="bi bi-person" style="color:#922B21;"></i>
                    </span>
                    <input type="text" name="username"
                           class="form-control form-control-sm"
                           placeholder="Masukkan username..."
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           required>
                </div>
                <small class="text-muted" style="font-size:0.75rem;">Minimal 3 karakter.</small>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label class="form-label fw-semibold" style="font-size:0.85rem;">Password</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:#FADBD8; border-color:#ddd;">
                        <i class="bi bi-lock" style="color:#922B21;"></i>
                    </span>
                    <input type="password" name="password"
                           class="form-control form-control-sm"
                           placeholder="Masukkan password..."
                           required>
                </div>
                <small class="text-muted" style="font-size:0.75rem;">Minimal 6 karakter.</small>
            </div>

            <!-- Tombol Daftar -->
            <button type="submit" class="btn btn-red">
                <i class="bi bi-person-plus me-1"></i>Daftar Sekarang
            </button>

        </form>

        <!-- Link ke Login -->
        <div class="auth-footer">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>