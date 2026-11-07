<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Memeriksa akses role pengguna
 * @param array $allowed_roles Contoh: ['admin', 'guru']
 */
function check_access($allowed_roles = []) {
    // 1. Cek apakah pengguna sudah login
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit;
    }

    // 2. Cek apakah role pengguna diizinkan
    if (!empty($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
        echo "<script>
                alert('Akses ditolak! Anda tidak memiliki izin mengakses halaman ini.');
                window.location.href = '../auth/login.php';
              </script>";
        exit;
    }
}
?>