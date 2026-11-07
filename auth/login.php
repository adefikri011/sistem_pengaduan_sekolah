<?php
session_start();
require_once '../config/database.php';

// Jika sudah login, redirect sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') header("Location: ../admin/index.php");
    elseif ($_SESSION['role'] === 'guru') header("Location: ../guru/index.php");
    elseif ($_SESSION['role'] === 'siswa') header("Location: ../siswa/index.php");
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    $identifier = mysqli_real_escape_string($conn, trim($_POST['identifier']));
    $password   = $_POST['password'];

    // Cari pengguna berdasarkan NIP (guru), NISN (siswa), atau username (admin)
    $query = "SELECT * FROM users WHERE (nip = '$identifier' OR nisn = '$identifier' OR username = '$identifier') LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Untuk guru/siswa password default = NIP/NISN. Untuk admin password di-set saat pembuatan akun.
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']      = $user['id'];
            $_SESSION['username']     = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role']         = $user['role'];
            $_SESSION['id_kelas']     = $user['id_kelas'];
            $_SESSION['nip']          = $user['nip'];
            $_SESSION['nisn']         = $user['nisn'];

            if ($user['role'] === 'admin') {
                header("Location: ../admin/index.php");
            } elseif ($user['role'] === 'guru') {
                header("Location: ../guru/index.php");
            } else {
                header("Location: ../siswa/index.php");
            }
            exit;
        } else {
            $error = "NIP/NISN/Username atau Password yang Anda masukkan salah.";
        }
    } else {
        $error = "NIP/NISN/Username tidak terdaftar dalam sistem.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SaranaCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="auth-body">

<div class="auth-wrap">
    <!-- Banner kiri -->
    <section class="auth-banner">
        <a class="auth-brand" href="../index.php">
            <span class="brand-mark">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
            </span>
            <span class="brand-text">SaranaCare<span class="brand-dot">.</span></span>
        </a>

        <div class="auth-banner-content">
            <span class="auth-badge"><span class="pulse"></span> Sistem Pengaduan Sarana Sekolah</span>
            <h1>Laporkan Fasilitas,<br><em>Ciptakan Kenyamanan.</em></h1>
            <p>Platform pengaduan sarana dan prasarana sekolah yang transparan, akurat, serta terverifikasi langsung oleh guru dan administrator.</p>

            <div class="auth-features">
                <span class="auth-feature">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Terverifikasi Guru
                </span>
                <span class="auth-feature">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    Pantau Status Real-time
                </span>
                <span class="auth-feature">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 12 2 2 4-4"/><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                    Transparan &amp; Akurat
                </span>
            </div>
        </div>

        <p class="auth-banner-foot">&copy; <?= date('Y') ?> SaranaCare — Sistem Pengaduan Sarana Sekolah. All rights reserved.</p>
    </section>

    <!-- Form kanan -->
    <section class="auth-panel">
        <div class="auth-card">
            <div class="auth-card-head">
                <a class="brand auth-mobile-brand" href="../index.php">
                    <span class="brand-mark">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </span>
                    <span class="brand-text">SaranaCare<span class="brand-dot">.</span></span>
                </a>
                <h2>Selamat Datang</h2>
                <p>Masukkan NIP/NISN atau Username serta password Anda untuk melanjutkan ke sistem.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="field">
                    <div class="field-label">
                        <label for="identifier">NIP / NISN / Username</label>
                    </div>
                    <div class="input-shell has-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <input type="text" id="identifier" name="identifier" class="form-control" placeholder="Masukkan NIP, NISN, atau Username" required autocomplete="username" autofocus>
                    </div>
                </div>

                <div class="field">
                    <div class="field-label">
                        <label for="password">Password</label>
                        <span class="field-hint">Untuk Guru/Siswa, password awal adalah NIP/NISN Anda.</span>
                    </div>
                    <div class="input-shell has-icon has-action">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="password-toggle" data-password-toggle aria-label="Tampilkan password">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8-11 8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" name="login" class="btn btn-primary btn-block btn-lg auth-submit">
                    Masuk ke Akun
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
            </form>

            <p class="auth-foot">&copy; <?= date('Y') ?> SaranaCare. Sistem siap digunakan untuk sekolah.</p>
        </div>
    </section>
</div>

<script>
    (function () {
        var toggle = document.querySelector('[data-password-toggle]');
        var input = document.getElementById('password');
        if (!toggle || !input) return;
        toggle.addEventListener('click', function () {
            var isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            toggle.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
        });
    })();
</script>
</body>
</html>
