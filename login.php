<?php
session_start();
require_once 'koneksi.php';

// Cek apakah sudah login atau ada cookie remember me
 if (isset($_SESSION['user_id']) || isset($_COOKIE['remember_user_id'])) {
     header("Location: pages/dashboard.php");
     exit();
 }

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"] ?? '');
    $password = trim($_POST["password"] ?? '');
    $remember = isset($_POST["remember_me"]);

    // Validasi sederhana
    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        // PDO - BENAR!
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);  //  PDO pakai array
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user["password"])) {
            // LOGIN SUKSES! 🎉
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            
            // ✅ FITUR REMEMBER ME
            if ($remember) {
                $remember_token = $user["id"] . '|' . password_hash($user["username"], PASSWORD_DEFAULT);
                setcookie("remember_user_id", $user["id"], time() + (7 * 24 * 60 * 60), "/");
                setcookie("remember_token", $remember_token, time() + (7 * 24 * 60 * 60), "/");
            }
            
            header("Location: pages/dashboard.php");
            exit();
        } else {
            $error = 'Username atau password salah!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — InvenRed</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f4f4f4; font-family: sans-serif; }
        .auth-wrapper { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { 
            background: #fff; border-radius: 8px; box-shadow: 0 2px 16px rgba(0,0,0,0.10); 
            padding: 2rem; width: 100%; max-width: 400px; 
        }
        .auth-logo { text-align: center; margin-bottom: 1.5rem; }
        .auth-logo .logo-icon { 
            width: 52px; height: 52px; background: #922B21; border-radius: 10px; 
            display: flex; align-items: center; justify-content: center; margin: 0 auto 0.6rem; 
        }
        .auth-logo h5 { font-weight: 700; color: #4A1210; margin: 0; }
        .auth-logo p { font-size: 0.8rem; color: #888; margin: 0; }
        .form-control:focus { border-color: #C0392B; box-shadow: 0 0 0 0.15rem rgba(192,57,43,0.2); }
        .btn-red { 
            background: #C0392B; color: #fff; border: none; width: 100%; 
            padding: 0.6rem; font-weight: 600; border-radius: 6px; 
        }
        .btn-red:hover { background: #922B21; color: #fff; }
        .form-check-input:checked { background-color: #C0392B; border-color: #C0392B; }
        .auth-footer { text-align: center; margin-top: 1.25rem; font-size: 0.85rem; color: #888; }
        .auth-footer a { color: #C0392B; text-decoration: none; font-weight: 600; }
        .auth-footer a:hover { text-decoration: underline; }
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
            <p>Masuk ke akun Anda</p>
        </div>

        <!-- Pesan error -->
        <?php if ($error): ?>
        <div class="alert alert-danger py-2 mb-3" style="font-size:0.85rem;">
            <i class="bi bi-x-circle me-1"></i><?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Form Login -->
        <form method="POST" action="login.php">
            <!-- Username -->
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:0.85rem;">Username</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:#FADBD8; border-color:#ddd;">
                        <i class="bi bi-person" style="color:#922B21;"></i>
                    </span>
                    <input type="text" name="username" class="form-control form-control-sm" 
                           placeholder="Masukkan username..." value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                           required autofocus>
                </div>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label class="form-label fw-semibold" style="font-size:0.85rem;">Password</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:#FADBD8; border-color:#ddd;">
                        <i class="bi bi-lock" style="color:#922B21;"></i>
                    </span>
                    <input type="password" name="password" class="form-control form-control-sm" 
                           placeholder="Masukkan password..." required>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember_me" id="remember_me">
                    <label class="form-check-label" for="remember_me" style="font-size:0.85rem;">
                        <i class="bi bi-clock me-1"></i>Ingat saya (7 hari)
                    </label>
                </div>
            </div>

            <!-- Tombol Login -->
            <button type="submit" class="btn btn-red mb-3">
                <i class="bi bi-box-arrow-in-right me-1"></i>Masuk Sekarang
            </button>
        </form>

        <!-- Link ke Register -->
        <div class="auth-footer">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>