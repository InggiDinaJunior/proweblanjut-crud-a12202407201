<?php
// app/models/AuthModel.php
// MODEL = semua query SQL terkait user/autentikasi

class AuthModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Cari user berdasarkan username
    public function findByUsername($username)
    {
        $stmt = $this->conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    // Cek apakah username sudah digunakan
    public function isUsernameTaken($username)
    {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return (bool)$stmt->fetch();
    }

    // Daftarkan user baru
    public function register($username, $password)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        return $stmt->execute([$username, $hash]);
    }
}
