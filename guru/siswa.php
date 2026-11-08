<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

check_access(['guru']);

$guru_id = $_SESSION['user_id'];

// Kelas yang diampu guru
$query_kelas = "SELECT * FROM kelas WHERE id_guru = '$guru_id'";
$res_kelas   = mysqli_query($conn, $query_kelas);
$kelas       = mysqli_fetch_assoc($res_kelas);
$id_kelas    = $kelas['id'] ?? 0;
$nama_kelas  = $kelas['nama_kelas'] ?? 'Belum Ditugaskan';

// Hapus siswa
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id = '$id_hapus' AND role = 'siswa'");
    header("Location: siswa.php?msg=deleted");
    exit;
}

// Tambah siswa
if (isset($_POST['tambah'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $nama     = mysqli_real_escape_string($conn, trim($_POST['nama_lengkap']));
    $password = !empty($_POST['password']) ? $_POST['password'] : '123456';
    $kelas_id = (int)($_POST['id_kelas'] ?? $id_kelas);
    $hash     = password_hash($password, PASSWORD_DEFAULT);

    mysqli_query($conn, "INSERT INTO users (username, password, nama_lengkap, role, id_kelas) 
                         VALUES ('$username', '$hash', '$nama', 'siswa', '$kelas_id')");
    header("Location: siswa.php?msg=added");
    exit;
}

// Update siswa
if (isset($_POST['update'])) {
    $id_update = (int)$_POST['id'];
    $username  = mysqli_real_escape_string($conn, trim($_POST['username']));
    $nama      = mysqli_real_escape_string($conn, trim($_POST['nama_lengkap']));
    $kelas_id  = (int)($_POST['id_kelas'] ?? $id_kelas);

    if (!empty($_POST['password'])) {
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET username='$username', password='$hash', nama_lengkap='$nama', id_kelas='$kelas_id' WHERE id='$id_update' AND role='siswa'");
    } else {
        mysqli_query($conn, "UPDATE users SET username='$username', nama_lengkap='$nama', id_kelas='$kelas_id' WHERE id='$id_update' AND role='siswa'");
    }
    header("Location: siswa.php?msg=updated");
    exit;
}

// Data edit
$edit = null;
if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];
    $res     = mysqli_query($conn, "SELECT * FROM users WHERE id='$id_edit' AND role='siswa'");
    $edit    = mysqli_fetch_assoc($res);
}

// Daftar siswa kelas guru
$siswa = mysqli_query($conn, "SELECT u.*, k.nama_kelas 
                              FROM users u 
                              LEFT JOIN kelas k ON u.id_kelas = k.id 
                              WHERE u.role='siswa' AND u.id_kelas='$id_kelas' 
                              ORDER BY u.nama_lengkap ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Siswa - SaranaCare</title>
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
        if ($_GET['msg'] === 'added') $m = 'Siswa berhasil ditambahkan.';
        if ($_GET['msg'] === 'updated') $m = 'Data siswa berhasil diperbarui.';
        if ($_GET['msg'] === 'deleted') $m = 'Siswa berhasil dihapus.';
        if ($m): ?>
            <div class="alert alert-success">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                <span><?= htmlspecialchars($m) ?></span>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="page-head">
        <div>
            <span class="eyebrow">Manajemen Siswa</span>
            <h1>Data Siswa Kelas <?= htmlspecialchars($nama_kelas) ?></h1>
            <p>Kelola data siswa yang terdaftar dalam kelas yang Anda ampu.</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card stat-blue">
            <span class="stat-icon">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><circle cx="12" cy="12" r="3"/></svg>
            </span>
            <div>
                <span class="stat-label">Total Siswa</span>
                <strong class="stat-value"><?= mysqli_num_rows($siswa) ?></strong>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <h2 class="card-title"><?= $edit ? 'Edit Siswa' : 'Tambah Siswa Baru' ?></h2>
                <p class="card-sub"><?= $edit ? 'Ubah data siswa yang terpilih.' : 'Isi data siswa baru untuk kelas ini.' ?></p>
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
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                </div>

                <div class="field">
                    <div class="field-label"><label for="id_kelas">Kelas</label></div>
                    <select id="id_kelas" name="id_kelas" class="form-select" required>
                        <option value="<?= $id_kelas ?>" <?= (!$edit || $edit['id_kelas'] == $id_kelas) ? 'selected' : '' ?>><?= htmlspecialchars($nama_kelas) ?></option>
                        <?php
                        $all_kelas = mysqli_query($conn, "SELECT * FROM kelas WHERE id_guru='$guru_id' ORDER BY nama_kelas ASC");
                        while ($k = mysqli_fetch_assoc($all_kelas)):
                            if ($k['id'] == $id_kelas) continue;
                        ?>
                            <option value="<?= $k['id'] ?>" <?= ($edit && $edit['id_kelas'] == $k['id']) ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kelas']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" name="<?= $edit ? 'update' : 'tambah' ?>" class="btn btn-primary btn-lg">
                        <?= $edit ? 'Simpan Perubahan' : 'Tambah Siswa' ?>
                    </button>
                    <a href="siswa.php" class="btn btn-ghost btn-lg">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top:20px;">
        <div class="card-head">
            <div>
                <h2 class="card-title">Daftar Siswa</h2>
                <p class="card-sub">Seluruh siswa terdaftar di kelas <?= htmlspecialchars($nama_kelas) ?></p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Kelas</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($siswa) > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($siswa)): ?>
                            <tr>
                                <td class="cell-no" data-label="No"><?= $no++ ?></td>
                                <td data-label="Username"><strong><?= htmlspecialchars($row['username']) ?></strong></td>
                                <td data-label="Nama"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td data-label="Kelas"><span class="type-pill"><?= htmlspecialchars($row['nama_kelas'] ?? '-') ?></span></td>
                                <td data-label="Role"><span class="badge badge-primary">Siswa</span></td>
                                <td data-label="Aksi">
                                    <a href="siswa.php?edit=<?= $row['id'] ?>" class="btn btn-soft btn-sm">Ubah</a>
                                    <a href="siswa.php?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus siswa ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="cell-empty" style="padding:0;">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    </div>
                                    <h3>Belum ada siswa</h3>
                                    <p>Tambahkan siswa baru menggunakan formulir di atas.</p>
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
