<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

check_access(['admin']);

$msg = '';

// Hapus guru
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    mysqli_query($conn, "UPDATE kelas SET id_guru=NULL WHERE id_guru='$id_hapus'");
    mysqli_query($conn, "DELETE FROM users WHERE id = '$id_hapus' AND role = 'guru'");
    header("Location: guru.php?msg=deleted");
    exit;
}

// Tambah guru
if (isset($_POST['tambah'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $nama     = mysqli_real_escape_string($conn, trim($_POST['nama_lengkap']));
    $password = !empty($_POST['password']) ? $_POST['password'] : '123456';
    $hash     = password_hash($password, PASSWORD_DEFAULT);

    mysqli_query($conn, "INSERT INTO users (username, password, nama_lengkap, role, id_kelas) 
                         VALUES ('$username', '$hash', '$nama', 'guru', NULL)");
    header("Location: guru.php?msg=added");
    exit;
}

// Edit guru
if (isset($_POST['update'])) {
    $id_update = (int)$_POST['id'];
    $username  = mysqli_real_escape_string($conn, trim($_POST['username']));
    $nama      = mysqli_real_escape_string($conn, trim($_POST['nama_lengkap']));

    if (!empty($_POST['password'])) {
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET username='$username', password='$hash', nama_lengkap='$nama' WHERE id='$id_update' AND role='guru'");
    } else {
        mysqli_query($conn, "UPDATE users SET username='$username', nama_lengkap='$nama' WHERE id='$id_update' AND role='guru'");
    }
    header("Location: guru.php?msg=updated");
    exit;
}

// Data edit
$edit = null;
if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];
    $res     = mysqli_query($conn, "SELECT * FROM users WHERE id='$id_edit' AND role='guru'");
    $edit    = mysqli_fetch_assoc($res);
}

// Daftar guru
$gurus = mysqli_query($conn, "SELECT * FROM users WHERE role='guru' ORDER BY nama_lengkap ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Guru - SaranaCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="app-body">

<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<main class="app-main">
    <?php if (isset($_GET['msg'])): ?>
        <?php
        $m = '';
        if ($_GET['msg'] === 'added') $m = 'Guru berhasil ditambahkan.';
        if ($_GET['msg'] === 'updated') $m = 'Data guru berhasil diperbarui.';
        if ($_GET['msg'] === 'deleted') $m = 'Guru berhasil dihapus.';
        if ($m): ?>
            <div class="alert alert-success">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                <span><?= htmlspecialchars($m) ?></span>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="page-head">
        <div>
            <span class="eyebrow">Manajemen Guru</span>
            <h1>Data Guru Sekolah</h1>
            <p>Kelola data guru yang berfungsi sebagai wali kelas dan verifikator pengaduan.</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card stat-blue">
            <span class="stat-icon">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
            </span>
            <div>
                <span class="stat-label">Total Guru</span>
                <strong class="stat-value"><?= mysqli_num_rows($gurus) ?></strong>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <h2 class="card-title"><?= $edit ? 'Edit Guru' : 'Tambah Guru Baru' ?></h2>
                <p class="card-sub"><?= $edit ? 'Ubah data guru yang terpilih.' : 'Tambahkan guru baru ke dalam sistem.' ?></p>
            </div>
        </div>

        <div class="card-body">
            <form method="POST" action="">
                <?php if ($edit): ?>
                    <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                <?php endif; ?>

                <div class="field">
                    <div class="field-label"><label for="username">Username</label></div>
                    <div class="input-shell has-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <input type="text" id="username" name="username" class="form-control" value="<?= $edit ? htmlspecialchars($edit['username']) : '' ?>" required autocomplete="off">
                    </div>
                </div>

                <div class="field">
                    <div class="field-label"><label for="nama_lengkap">Nama Lengkap</label></div>
                    <div class="input-shell has-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 9v2h20V9"/><path d="M4 17h16"/><path d="M10 3h4v16h-4"/></svg>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" value="<?= $edit ? htmlspecialchars($edit['nama_lengkap']) : '' ?>" required>
                    </div>
                </div>

                <div class="field">
                    <div class="field-label"><label for="password">Password</label>
                        <span class="field-hint"><?= $edit ? 'Kosongkan jika tidak ingin mengubah' : 'Default: 123456' ?></span>
                    </div>
                    <div class="input-shell has-icon has-action">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••">
                        <button type="button" class="password-toggle" data-password-toggle aria-label="Tampilkan password">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="<?= $edit ? 'update' : 'tambah' ?>" class="btn btn-primary btn-lg">
                        <?= $edit ? 'Simpan Perubahan' : 'Tambah Guru' ?>
                    </button>
                    <a href="guru.php" class="btn btn-ghost btn-lg">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top:20px;">
        <div class="card-head">
            <div>
                <h2 class="card-title">Daftar Guru</h2>
                <p class="card-sub">Seluruh guru yang terdaftar dalam sistem</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($gurus) > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($gurus)): ?>
                            <tr>
                                <td class="cell-no" data-label="No"><?= $no++ ?></td>
                                <td data-label="Username"><strong><?= htmlspecialchars($row['username']) ?></strong></td>
                                <td data-label="Nama"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td data-label="Role"><span class="badge badge-primary">Guru</span></td>
                                <td data-label="Aksi">
                                    <a href="guru.php?edit=<?= $row['id'] ?>" class="btn btn-soft btn-sm">Ubah</a>
                                    <a href="guru.php?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus guru ini? Kelas yang diampu guru ini akan dilepaskan.')">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="cell-empty" style="padding:0;">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21V19a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    </div>
                                    <h3>Belum ada guru</h3>
                                    <p>Tambahkan guru baru menggunakan formulir di atas.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    (function () {
        var toggle = document.querySelector('[data-password-toggle]');
        var input = document.getElementById('password');
        if (!toggle || !input) return;
        toggle.addEventListener('click', function () {
            var isPass = input.type === 'password';
            input.type = isPass ? 'text' : 'password';
            toggle.setAttribute('aria-label', isPass ? 'Sembunyikan password' : 'Tampilkan password');
        });
    })();
</script>
</body>
</html>
