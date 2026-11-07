<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

check_access(['guru']);

$guru_id = $_SESSION['user_id'];
$msg = '';

$res = mysqli_query($conn, "SELECT * FROM users WHERE id='$guru_id' AND role='guru'");
$guru = mysqli_fetch_assoc($res);

if (!$guru) {
    header("Location: ../auth/login.php");
    exit;
}

// Update profil guru
if (isset($_POST['update_profil'])) {
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama_lengkap']));
    $nip = mysqli_real_escape_string($conn, trim($_POST['nip']));
    mysqli_query($conn, "UPDATE users SET nama_lengkap='$nama', nip='$nip' WHERE id='$guru_id'");
    $_SESSION['nama_lengkap'] = $nama;
    header("Location: profil.php?msg=updated");
    exit;
}

// Ganti password guru (default = NIP, boleh diubah)
if (isset($_POST['ganti_password'])) {
    $pass_lama = $_POST['password_lama'];
    $pass_baru = $_POST['password_baru'];

    if (password_verify($pass_lama, $guru['password'])) {
        $hash_baru = password_hash($pass_baru, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$hash_baru' WHERE id='$guru_id'");
        header("Location: profil.php?msg=password_updated");
        exit;
    } else {
        header("Location: profil.php?msg=password_salah");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Guru - SaranaCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="app-body">

<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php">
            <span class="brand-mark">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
            </span>
            <span class="brand-text">SaranaCare<span class="brand-dot">.</span></span>
        </a>
        <div class="navbar-right">
            <span class="role-chip role-guru">Guru</span>
            <div class="user-chip">
                <span class="avatar"><?= htmlspecialchars(substr($_SESSION['nama_lengkap'], 0, 1)) ?></span>
                <span class="user-meta">
                    <strong><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></strong>
                    <small>Wali Kelas</small>
                </span>
            </div>
            <a href="../auth/logout.php" class="btn btn-logout" title="Keluar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                <span>Keluar</span>
            </a>
        </div>
    </div>
</header>

<main class="app-main">
    <?php if (isset($_GET['msg'])): ?>
        <?php
        $m = '';
        $type = 'alert-success';
        if ($_GET['msg'] === 'updated') $m = 'Profil berhasil diperbarui.';
        if ($_GET['msg'] === 'password_updated') $m = 'Password berhasil diganti.';
        if ($_GET['msg'] === 'password_salah') { $m = 'Password lama salah.'; $type = 'alert-danger'; }
        if ($m): ?>
            <div class="alert <?= $type ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                <span><?= htmlspecialchars($m) ?></span>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="page-head">
        <div>
            <span class="eyebrow">Profil Guru</span>
            <h1>Pengaturan Profil</span>
            <p>Kelola informasi pribadi dan akun guru Anda.</p>
        </div>
        <div class="page-actions">
            <a href="index.php" class="btn btn-ghost">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Kembali
            </a>
        </div>
    </div>

    <div class="detail-grid">
        <section class="detail-card">
            <div class="detail-card-head">
                <div>
                    <h2>Informasi Akun</h2>
                    <p class="detail-sub">Data identitas guru</p>
                </div>
            </div>
            <div class="detail-body">
                <form method="POST" action="">
                    <div class="field">
                        <div class="field-label"><label for="nip">NIP</label>
                            <span class="field-hint">NIP digunakan untuk login.</span>
                        </div>
                        <div class="input-shell has-icon">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="21" x2="21" y2="3"/></svg>
                            <input type="text" id="nip" name="nip" class="form-control" value="<?= htmlspecialchars($guru['nip']) ?>" required maxlength="30">
                        </div>
                    </div>
                    <div class="field">
                        <div class="field-label"><label for="nama_lengkap">Nama Lengkap</label></div>
                        <div class="input-shell has-icon">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 9v2h20V9"/><path d="M4 17h16"/><path d="M10 3h4v16h-4"/></svg>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($guru['nama_lengkap']) ?>" required>
                        </div>
                    </div>
                    <div class="field">
                        <div class="field-label"><label>Role</label></div>
                        <div class="input-shell">
                            <input type="text" class="form-control" value="Guru" readonly>
                        </div>
                    </div>
                    <div class="field">
                        <div class="field-label"><label>ID Pengguna</label></div>
                        <div class="input-shell">
                            <input type="text" class="form-control" value="#<?= $guru['id'] ?>" readonly>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="update_profil" class="btn btn-primary">Simpan Perubahan Profil</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="decision-card">
            <h3>Ganti Password</h3>
            <p>Ubah password akun guru Anda. Awalnya password adalah NIP Anda.</p>
            <form method="POST" action="">
                <div class="field">
                    <div class="field-label"><label for="password_lama">Password Lama</label></div>
                    <div class="input-shell has-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <input type="password" id="password_lama" name="password_lama" class="form-control" placeholder="Masukkan password lama" required>
                    </div>
                </div>
                <div class="field">
                    <div class="field-label"><label for="password_baru">Password Baru</label></div>
                    <div class="input-shell has-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <input type="password" id="password_baru" name="password_baru" class="form-control" placeholder="Masukkan password baru" required minlength="6">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" name="ganti_password" class="btn btn-primary">Ganti Password</button>
                </div>
            </form>
        </section>
    </div>
</main>

</body>
</html>
