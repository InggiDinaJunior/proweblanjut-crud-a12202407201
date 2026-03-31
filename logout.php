<?php
session_start();

// Hapus session
session_destroy();

// Hapus cookie remember me 
setcookie('remember_user_id', '', time() - 3600, '/');
setcookie('remember_token',   '', time() - 3600, '/');

// Kembali ke halaman login
header('Location: login.php');
exit();
?>