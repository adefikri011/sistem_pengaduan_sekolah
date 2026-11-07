<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

check_access(['admin']);

$msg = '';

// Hapus pengguna (tidak boleh menghapus diri sendiri)
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    if ($id_hapus !== $_SESSION['user_id']) {
        mysqli_query($conn, "UPDATE kelas SET id_guru=NULL WHERE id_guru='$id_hapus'");
        mysqli_query($conn, "DELETE FROM users WHERE id='$id_hapus'");
        $_SESSION['flash_success'] = 'Pengguna berhasil dihapus.';
        header("Location: pengguna.php");
        exit;
    } else {
        $_SESSION['flash_error'] = 'Anda tidak dapat menghapus akun sendiri.';
        header("Location: pengguna.php");
        exit;
    }
}

// Tambah pengguna
if (isset($_POST['tambah'])) {
    $role       = mysqli_real_escape_string($conn, trim($_POST['role']));
    $nama       = mysqli_real_escape_string($conn, trim($_POST['nama_lengkap']));
    $id_kelas   = (int)($_POST['id_kelas'] ?? 0);
    $id_kelas   = $id_kelas > 0 ? $id_kelas : 'NULL';
    $password   = '';

    if ($role === 'guru') {
        $nip = mysqli_real_escape_string($conn, trim($_POST['nip']));
        if (empty($nip)) {
            $_SESSION['flash_error'] = 'NIP wajib diisi untuk akun Guru.';
            header("Location: pengguna.php");
            exit;
        }
        $cek = mysqli_query($conn, "SELECT id FROM users WHERE nip='$nip' LIMIT 1");
        if (mysqli_num_rows($cek) > 0) {
            $_SESSION['flash_error'] = 'NIP sudah terdaftar.';
            header("Location: pengguna.php");
            exit;
        }
        $password = password_hash($nip, PASSWORD_DEFAULT);
        mysqli_query($conn, "INSERT INTO users (nip, password, nama_lengkap, role, id_kelas) VALUES ('$nip', '$password', '$nama', 'guru', $id_kelas)");
    } elseif ($role === 'siswa') {
        $nisn = mysqli_real_escape_string($conn, trim($_POST['nisn']));
        if (empty($nisn)) {
            $_SESSION['flash_error'] = 'NISN wajib diisi untuk akun Siswa.';
            header("Location: pengguna.php");
            exit;
        }
        $cek = mysqli_query($conn, "SELECT id FROM users WHERE nisn='$nisn' LIMIT 1");
        if (mysqli_num_rows($cek) > 0) {
            $_SESSION['flash_error'] = 'NISN sudah terdaftar.';
            header("Location: pengguna.php");
            exit;
        }
        $password = password_hash($nisn, PASSWORD_DEFAULT);
        mysqli_query($conn, "INSERT INTO users (nisn, password, nama_lengkap, role, id_kelas) VALUES ('$nisn', '$password', '$nama', 'siswa', $id_kelas)");
    } elseif ($role === 'admin') {
        $username = mysqli_real_escape_string($conn, trim($_POST['username']));
        $pass_raw = mysqli_real_escape_string($conn, trim($_POST['password']));
        if (empty($username) || empty($pass_raw)) {
            $_SESSION['flash_error'] = 'Username dan Password wajib diisi untuk akun Admin.';
            header("Location: pengguna.php");
            exit;
        }
        $cek = mysqli_query($conn, "SELECT id FROM users WHERE username='$username' LIMIT 1");
        if (mysqli_num_rows($cek) > 0) {
            $_SESSION['flash_error'] = 'Username sudah terdaftar.';
            header("Location: pengguna.php");
            exit;
        }
        $password = password_hash($pass_raw, PASSWORD_DEFAULT);
        mysqli_query($conn, "INSERT INTO users (username, password, nama_lengkap, role, id_kelas) VALUES ('$username', '$password', '$nama', 'admin', NULL)");
    }

    $_SESSION['flash_success'] = 'Pengguna berhasil ditambahkan.';
    header("Location: pengguna.php");
    exit;
}

// Edit pengguna
if (isset($_POST['update'])) {
    $id_update  = (int)$_POST['id'];
    $role       = mysqli_real_escape_string($conn, trim($_POST['role']));
    $nama       = mysqli_real_escape_string($conn, trim($_POST['nama_lengkap']));
    $id_kelas   = (int)($_POST['id_kelas'] ?? 0);
    $id_kelas   = $id_kelas > 0 ? $id_kelas : 'NULL';

    if ($role === 'guru') {
        $nip = mysqli_real_escape_string($conn, trim($_POST['nip']));
        mysqli_query($conn, "UPDATE users SET nip='$nip', nama_lengkap='$nama', role='guru', id_kelas=$id_kelas WHERE id='$id_update'");
    } elseif ($role === 'siswa') {
        $nisn = mysqli_real_escape_string($conn, trim($_POST['nisn']));
        mysqli_query($conn, "UPDATE users SET nisn='$nisn', nama_lengkap='$nama', role='siswa', id_kelas=$id_kelas WHERE id='$id_update'");
    } elseif ($role === 'admin') {
        $username = mysqli_real_escape_string($conn, trim($_POST['username']));
        mysqli_query($conn, "UPDATE users SET username='$username', nama_lengkap='$nama', role='admin', id_kelas=NULL WHERE id='$id_update'");
    }

    $_SESSION['flash_success'] = 'Data pengguna berhasil diperbarui.';
    header("Location: pengguna.php");
    exit;
}

// Data edit
$edit = null;
if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];
    $res     = mysqli_query($conn, "SELECT * FROM users WHERE id='$id_edit'");
    $edit    = mysqli_fetch_assoc($res);
}

// Daftar pengguna
$users = mysqli_query($conn, "SELECT u.*, k.nama_kelas 
                              FROM users u 
                              LEFT JOIN kelas k ON u.id_kelas = k.id 
                              ORDER BY u.role ASC, u.nama_lengkap ASC");

// Kelas untuk dropdown
$kelas_list = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - SaranaCare</title>
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
            <span class="role-chip role-admin">Admin</span>
            <div class="user-chip">
                <span class="avatar"><?= htmlspecialchars(substr($_SESSION['nama_lengkap'], 0, 1)) ?></span>
                <span class="user-meta">
                    <strong><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></strong>
                    <small>Administrator</small>
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
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="page-head">
        <div>
            <span class="eyebrow">Manajemen Pengguna</span>
            <h1>Data Pengguna Sekolah</h1>
            <p>Kelola akun Admin, Guru, dan Siswa. Password awal untuk Guru/Siswa adalah NIP/NISN mereka.</p>
        </div>
        <div class="page-actions">
            <a href="index.php" class="btn btn-ghost">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Kembali
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card stat-blue">
            <span class="stat-icon">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
            </span>
            <div>
                <span class="stat-label">Total Pengguna</span>
                <strong class="stat-value"><?= mysqli_num_rows($users) ?></strong>
            </div>
        </div>
        <?php
        $role_counts = ['admin'=>0,'guru'=>0,'siswa'=>0];
        mysqli_data_seek($users, 0);
        while ($rc = mysqli_fetch_assoc($users)) { $role_counts[$rc['role']]++; }
        ?>
        <div class="stat-card stat-violet">
            <span class="stat-icon">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><circle cx="12" cy="12" r="3"/></svg>
            </span>
            <div>
                <span class="stat-label">Guru</span>
                <strong class="stat-value"><?= $role_counts['guru'] ?></strong>
            </div>
        </div>
        <div class="stat-card stat-green">
            <span class="stat-icon">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m22 4-10 10-3-3"/></svg>
            </span>
            <div>
                <span class="stat-label">Siswa</span>
                <strong class="stat-value"><?= $role_counts['siswa'] ?></strong>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <h2 class="card-title"><?= $edit ? 'Edit Pengguna' : 'Tambah Pengguna Baru' ?></h2>
                <p class="card-sub"><?= $edit ? 'Ubah data pengguna yang terpilih.' : 'Buat akun baru untuk Admin, Guru, atau Siswa.' ?></p>
            </div>
        </div>

        <div class="card-body">
            <form method="POST" action="" id="formPengguna">
                <?php if ($edit): ?>
                    <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                <?php endif; ?>

                <div class="field">
                    <div class="field-label"><label for="role">Role / Jabatan</label></div>
                    <select id="role" name="role" class="form-select" required onchange="toggleRoleFields()">
                        <option value="admin" <?= ($edit && $edit['role']==='admin')?'selected':'' ?>>Admin</option>
                        <option value="guru" <?= ($edit && $edit['role']==='guru')?'selected':'' ?>>Guru (Wali Kelas)</option>
                        <option value="siswa" <?= ($edit && $edit['role']==='siswa')?'selected':'' ?>>Siswa</option>
                    </select>
                </div>

                <div class="field" id="field-username" style="<?= (!$edit || $edit['role']!=='admin')?'display:none':'' ?>">
                    <div class="field-label"><label for="username">Username Admin</label></div>
                    <div class="input-shell has-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <input type="text" id="username" name="username" class="form-control" value="<?= $edit && $edit['role']==='admin' ? htmlspecialchars($edit['username']) : '' ?>" placeholder="Contoh: admin" required>
                    </div>
                </div>
                <div class="field" id="field-password-admin" style="<?= (!$edit || $edit['role']!=='admin')?'display:none':'' ?>">
                    <div class="field-label"><label for="password_admin">Password Admin</label></div>
                    <div class="input-shell has-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <input type="password" id="password_admin" name="password" class="form-control" placeholder="••••••••" <?= $edit?'': 'required' ?>>
                    </div>
                </div>

                <div class="field" id="field-nip" style="<?= (!$edit || $edit['role']!=='guru')?'display:none':'' ?>">
                    <div class="field-label"><label for="nip">NIP Guru</label>
                        <span class="field-hint">NIP juga berfungsi sebagai password awal.</span>
                    </div>
                    <div class="input-shell has-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="21" x2="21" y2="3"/></svg>
                        <input type="text" id="nip" name="nip" class="form-control" value="<?= $edit && $edit['role']==='guru' ? htmlspecialchars($edit['nip']) : '' ?>" placeholder="Contoh: 198501012010011001" required maxlength="30">
                    </div>
                </div>

                <div class="field" id="field-nisn" style="<?= (!$edit || $edit['role']!=='siswa')?'display:none':'' ?>">
                    <div class="field-label"><label for="nisn">NISN Siswa</label>
                        <span class="field-hint">NISN juga berfungsi sebagai password awal.</span>
                    </div>
                    <div class="input-shell has-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="21" x2="21" y2="3"/></svg>
                        <input type="text" id="nisn" name="nisn" class="form-control" value="<?= $edit && $edit['role']==='siswa' ? htmlspecialchars($edit['nisn']) : '' ?>" placeholder="Contoh: 001234567890" required maxlength="30">
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
                    <div class="field-label"><label for="id_kelas">Kelas (Opsional)</label></div>
                    <select id="id_kelas" name="id_kelas" class="form-select">
                        <option value="">-- Belum Ada Kelas --</option>
                        <?php while ($k = mysqli_fetch_assoc($kelas_list)): ?>
                            <option value="<?= $k['id'] ?>" <?= ($edit && $edit['id_kelas'] == $k['id']) ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kelas']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" name="<?= $edit ? 'update' : 'tambah' ?>" class="btn btn-primary btn-lg">
                        <?= $edit ? 'Simpan Perubahan' : 'Tambah Pengguna' ?>
                    </button>
                    <a href="pengguna.php" class="btn btn-ghost btn-lg">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top:20px;">
        <div class="card-head">
            <div>
                <h2 class="card-title">Daftar Pengguna</h2>
                <p class="card-sub">Seluruh akun yang terdaftar dalam sistem</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Role</th>
                        <th>NIP / NISN / Username</th>
                        <th>Nama Lengkap</th>
                        <th>Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($users) > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($users)): ?>
                            <?php
                            $identifier = $row['role']==='guru' ? $row['nip'] : ($row['role']==='siswa' ? $row['nisn'] : $row['username']);
                            $identifier_label = $row['role']==='guru' ? 'NIP' : ($row['role']==='siswa' ? 'NISN' : 'Username');
                            ?>
                            <tr>
                                <td class="cell-no" data-label="No"><?= $no++ ?></td>
                                <td data-label="Role"><span class="badge badge-<?= $row['role']==='admin'?'violet':($row['role']==='guru'?'blue':'green') ?>"><?= ucfirst($row['role']) ?></span></td>
                                <td data-label="Identifier"><strong><?= htmlspecialchars($identifier_label) ?></strong><br><span style="font-size:12px;"><?= htmlspecialchars($identifier ?? '-') ?></span></td>
                                <td data-label="Nama"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td data-label="Kelas"><span class="type-pill"><?= htmlspecialchars($row['nama_kelas'] ?? '-') ?></span></td>
                                <td data-label="Aksi">
                                    <?php if ($row['id'] !== $_SESSION['user_id']): ?>
                                        <a href="pengguna.php?edit=<?= $row['id'] ?>" class="btn btn-soft btn-sm">Ubah</a>
                                        <a href="pengguna.php?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus akun ini?')">Hapus</a>
                                    <?php else: ?>
                                        <span class="muted-dash">Anda</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="cell-empty" style="padding:0;">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="1"/><path d="M4 21a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2z"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                    </div>
                                    <h3>Belum ada pengguna</h3>
                                    <p>Tambahkan pengguna baru menggunakan formulir di atas.</p>
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
    function toggleRoleFields() {
        var role = document.getElementById('role').value;
        document.getElementById('field-username').style.display = role === 'admin' ? 'block' : 'none';
        document.getElementById('field-password-admin').style.display = role === 'admin' ? 'block' : 'none';
        document.getElementById('field-nip').style.display = role === 'guru' ? 'block' : 'none';
        document.getElementById('field-nisn').style.display = role === 'siswa' ? 'block' : 'none';

        var usernameInput = document.getElementById('username');
        var passInput = document.getElementById('password_admin');
        var nipInput = document.getElementById('nip');
        var nisnInput = document.getElementById('nisn');

        if (usernameInput) usernameInput.required = role === 'admin';
        if (passInput) passInput.required = role === 'admin';
        if (nipInput) nipInput.required = role === 'guru';
        if (nisnInput) nisnInput.required = role === 'siswa';
    }

    // Inisialisasi saat halaman dimuat
    window.addEventListener('DOMContentLoaded', toggleRoleFields);
</script>
</body>
</html>
