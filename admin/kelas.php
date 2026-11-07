<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

check_access(['admin']);

// Pastikan kolom id_jurusan ada di tabel kelas (migrasi otomatis)
$cek_kolom = mysqli_query($conn, "SHOW COLUMNS FROM kelas LIKE 'id_jurusan'");
if (mysqli_num_rows($cek_kolom) === 0) {
    mysqli_query($conn, "ALTER TABLE kelas ADD COLUMN id_jurusan INT NULL AFTER nama_kelas");
    mysqli_query($conn, "ALTER TABLE kelas ADD FOREIGN KEY (id_jurusan) REFERENCES jurusan(id) ON DELETE SET NULL");
}

$msg = '';

// Ambil semua jurusan untuk dropdown
$jurusan_list = mysqli_query($conn, "SELECT * FROM jurusan ORDER BY nama_jurusan ASC");

// Ambil semua guru untuk dropdown wali kelas
$gurus = mysqli_query($conn, "SELECT id, nama_lengkap FROM users WHERE role='guru' ORDER BY nama_lengkap ASC");

// Hapus kelas
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    mysqli_query($conn, "UPDATE users SET id_kelas=NULL WHERE id_kelas='$id_hapus'");
    mysqli_query($conn, "DELETE FROM kelas WHERE id='$id_hapus'");
    $_SESSION['flash_success'] = 'Kelas berhasil dihapus.';
    header("Location: kelas.php");
    exit;
}

// Tambah kelas
if (isset($_POST['tambah'])) {
    $nama_kelas  = mysqli_real_escape_string($conn, trim($_POST['nama_kelas']));
    $id_jurusan  = (int)($_POST['id_jurusan'] ?? 0);
    $id_jurusan  = $id_jurusan > 0 ? $id_jurusan : 'NULL';
    $id_guru     = (int)($_POST['id_guru'] ?? 0);
    $id_guru     = $id_guru > 0 ? $id_guru : 'NULL';

    mysqli_query($conn, "INSERT INTO kelas (nama_kelas, id_jurusan, id_guru) VALUES ('$nama_kelas', $id_jurusan, $id_guru)");
    $_SESSION['flash_success'] = 'Kelas berhasil ditambahkan.';
    header("Location: kelas.php");
    exit;
}

// Edit kelas
if (isset($_POST['update'])) {
    $id_update   = (int)$_POST['id'];
    $nama_kelas  = mysqli_real_escape_string($conn, trim($_POST['nama_kelas']));
    $id_jurusan  = (int)($_POST['id_jurusan'] ?? 0);
    $id_jurusan  = $id_jurusan > 0 ? $id_jurusan : 'NULL';
    $id_guru     = (int)($_POST['id_guru'] ?? 0);
    $id_guru     = $id_guru > 0 ? $id_guru : 'NULL';

    mysqli_query($conn, "UPDATE kelas SET nama_kelas='$nama_kelas', id_jurusan=$id_jurusan, id_guru=$id_guru WHERE id='$id_update'");
    $_SESSION['flash_success'] = 'Data kelas berhasil diperbarui.';
    header("Location: kelas.php");
    exit;
}

// Data edit
$edit = null;
if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];
    $res     = mysqli_query($conn, "SELECT * FROM kelas WHERE id='$id_edit'");
    $edit    = mysqli_fetch_assoc($res);
}

// Daftar kelas dengan jurusan
$kelas = mysqli_query($conn, "SELECT k.*, u.nama_lengkap AS nama_guru, j.nama_jurusan 
                              FROM kelas k 
                              LEFT JOIN users u ON k.id_guru = u.id 
                              LEFT JOIN jurusan j ON k.id_jurusan = j.id 
                              ORDER BY j.nama_jurusan ASC, k.nama_kelas ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kelas - SaranaCare</title>
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

    <div class="page-head">
        <div>
            <span class="eyebrow">Manajemen Kelas</span>
            <h1>Data Kelas Sekolah</h1>
            <p>Kelola nama kelas, jurusan, dan tetapkan wali kelas yang mengampu.</p>
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
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h6"/><path d="M16 3a9 9 0 0 1-9 9m9 0a9 9 0 0 0 9-9"/></svg>
            </span>
            <div>
                <span class="stat-label">Total Kelas</span>
                <strong class="stat-value"><?= mysqli_num_rows($kelas) ?></strong>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <h2 class="card-title"><?= $edit ? 'Edit Kelas' : 'Tambah Kelas Baru' ?></h2>
                <p class="card-sub"><?= $edit ? 'Ubah nama kelas, jurusan, dan wali kelas.' : 'Tambahkan kelas baru ke dalam sistem.' ?></p>
            </div>
        </div>

        <div class="card-body">
            <form method="POST" action="">
                <?php if ($edit): ?>
                    <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                <?php endif; ?>

                <div class="field">
                    <div class="field-label"><label for="nama_kelas">Nama Kelas</label></div>
                    <div class="input-shell has-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="21" x2="21" y2="3"/></svg>
                        <input type="text" id="nama_kelas" name="nama_kelas" class="form-control" value="<?= $edit ? htmlspecialchars($edit['nama_kelas']) : '' ?>" placeholder="Contoh: XI RPL 1" required>
                    </div>
                </div>

                <div class="field">
                    <div class="field-label"><label for="id_jurusan">Jurusan</label></div>
                    <select id="id_jurusan" name="id_jurusan" class="form-select">
                        <option value="">-- Pilih Jurusan --</option>
                        <?php
                        // Reset jurusan_list pointer if needed
                        mysqli_data_seek($jurusan_list, 0);
                        while ($j = mysqli_fetch_assoc($jurusan_list)):
                        ?>
                            <option value="<?= $j['id'] ?>" <?= ($edit && $edit['id_jurusan'] == $j['id']) ? 'selected' : '' ?>><?= htmlspecialchars($j['nama_jurusan']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="field">
                    <div class="field-label"><label for="id_guru">Wali Kelas</label>
                        <span class="field-hint">Pilih guru yang mengampu kelas ini</span>
                    </div>
                    <select id="id_guru" name="id_guru" class="form-select">
                        <option value="">-- Belum Ada Wali Kelas --</option>
                        <?php while ($g = mysqli_fetch_assoc($gurus)): ?>
                            <option value="<?= $g['id'] ?>" <?= ($edit && $edit['id_guru'] == $g['id']) ? 'selected' : '' ?>><?= htmlspecialchars($g['nama_lengkap']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" name="<?= $edit ? 'update' : 'tambah' ?>" class="btn btn-primary btn-lg">
                        <?= $edit ? 'Simpan Perubahan' : 'Tambah Kelas' ?>
                    </button>
                    <a href="kelas.php" class="btn btn-ghost btn-lg">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top:20px;">
        <div class="card-head">
            <div>
                <h2 class="card-title">Daftar Kelas</h2>
                <p class="card-sub">Seluruh kelas yang terdaftar dalam sistem</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jurusan</th>
                        <th>Nama Kelas</th>
                        <th>Wali Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($kelas) > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($kelas)): ?>
                            <tr>
                                <td class="cell-no" data-label="No"><?= $no++ ?></td>
                                <td data-label="Jurusan">
                                    <?php if ($row['nama_jurusan']): ?>
                                        <span class="type-pill"><?= htmlspecialchars($row['nama_jurusan']) ?></span>
                                    <?php else: ?>
                                        <span class="muted-dash">Belum ditetapkan</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Kelas"><strong><?= htmlspecialchars($row['nama_kelas']) ?></strong></td>
                                <td data-label="Wali Kelas">
                                    <?php if ($row['nama_guru']): ?>
                                        <span class="type-pill"><?= htmlspecialchars($row['nama_guru']) ?></span>
                                    <?php else: ?>
                                        <span class="muted-dash">Belum ditetapkan</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Aksi">
                                    <a href="kelas.php?edit=<?= $row['id'] ?>" class="btn btn-soft btn-sm">Ubah</a>
                                    <a href="kelas.php?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus kelas ini? Siswa dalam kelas ini akan dilepaskan dari kelasnya.')">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="cell-empty" style="padding:0;">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="21" x2="21" y2="3"/></svg>
                                    </div>
                                    <h3>Belum ada kelas</h3>
                                    <p>Tambahkan kelas baru menggunakan formulir di atas.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>
