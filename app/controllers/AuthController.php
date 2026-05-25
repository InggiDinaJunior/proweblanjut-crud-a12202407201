<?php
// app/controllers/AuthController.php
// CONTROLLER = menangani semua alur autentikasi

require_once __DIR__ . '/../../app/models/AuthModel.php';

class AuthController
{
    private $model;

    public function __construct($conn)
    {
        $this->model = new AuthModel($conn);
    }

    // ── Tampilkan form login ────────────────────────────────
    public function login()
    {
        // Sudah login → langsung ke dashboard
        if (isset($_SESSION['user_id']) || isset($_COOKIE['remember_user_id'])) {
            header('Location: index.php?action=dashboard');
            exit;
        }
        $error = '';
        require_once __DIR__ . '/../../app/views/auth/login.php';
    }

    // ── Proses login ───────────────────────────────────────
    public function loginPost()
    {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $remember = isset($_POST['remember_me']);
        $error    = '';

        if (empty($username) || empty($password)) {
            $error = 'Username dan password wajib diisi.';
        } else {
            $user = $this->model->findByUsername($username);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];

                if ($remember) {
                    setcookie('remember_user_id', $user['id'], time() + (7 * 24 * 60 * 60), '/');
                    setcookie('remember_token',   $user['id'] . '|' . password_hash($user['username'], PASSWORD_DEFAULT), time() + (7 * 24 * 60 * 60), '/');
                }

                header('Location: index.php?action=dashboard');
                exit;
            }
            $error = 'Username atau password salah!';
        }

        require_once __DIR__ . '/../../app/views/auth/login.php';
    }

    // ── Tampilkan form register ─────────────────────────────
    public function register()
    {
        $error   = '';
        $success = '';
        require_once __DIR__ . '/../../app/views/auth/register.php';
    }

    // ── Proses register ────────────────────────────────────
    public function registerPost()
    {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $error    = '';
        $success  = '';

        if (empty($username) || empty($password)) {
            $error = 'Username dan password wajib diisi.';
        } elseif (strlen($username) < 3) {
            $error = 'Username minimal 3 karakter.';
        } elseif (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter.';
        } elseif ($this->model->isUsernameTaken($username)) {
            $error = 'Username sudah digunakan, coba yang lain.';
        } else {
            if ($this->model->register($username, $password)) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Gagal mendaftar, coba lagi.';
            }
        }

        require_once __DIR__ . '/../../app/views/auth/register.php';
    }

    // ── Logout ─────────────────────────────────────────────
    public function logout()
    {
        session_destroy();
        setcookie('remember_user_id', '', time() - 3600, '/');
        setcookie('remember_token',   '', time() - 3600, '/');
        header('Location: index.php?action=login');
        exit;
    }
}
