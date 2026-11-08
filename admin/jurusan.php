<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

check_access(['admin']);

// Pastikan tabel jurusan ada (migrasi otomatis)
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS jurusan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_jurusan VARCHAR(20) NOT NULL UNIQUE,
    nama_jurusan VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$msg = '';

// Hapus jurusan
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    mysqli_query($conn, "UPDATE kelas SET id_jurusan=NULL WHERE id_jurusan='$id_hapus'");
    mysqli_query($conn, "DELETE FROM jurusan WHERE id='$id_hapus'");
    $_SESSION['flash_success'] = 'Jurusan berhasil dihapus.';
    header("Location: jurusan.php");
    exit;
}

// Tambah jurusan
if (isset($_POST['tambah'])) {
    $kode_jurusan = mysqli_real_escape_string($conn, trim($_POST['kode_jurusan']));
    $nama_jurusan = mysqli_real_escape_string($conn, trim($_POST['nama_jurusan']));

    mysqli_query($conn, "INSERT INTO jurusan (kode_jurusan, nama_jurusan) VALUES ('$kode_jurusan', '$nama_jurusan')");
    $_SESSION['flash_success'] = 'Jurusan berhasil ditambahkan.';
    header("Location: jurusan.php");
    exit;
}

// Edit jurusan
if (isset($_POST['update'])) {
    $id_update = (int)$_POST['id'];
    $kode_jurusan = mysqli_real_escape_string($conn, trim($_POST['kode_jurusan']));
    $nama_jurusan = mysqli_real_escape_string($conn, trim($_POST['nama_jurusan']));

    mysqli_query($conn, "UPDATE jurusan SET kode_jurusan='$kode_jurusan', nama_jurusan='$nama_jurusan' WHERE id='$id_update'");
    $_SESSION['flash_success'] = 'Data jurusan berhasil diperbarui.';
    header("Location: jurusan.php");
    exit;
}

// Data edit
$edit = null;
if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];
    $res     = mysqli_query($conn, "SELECT * FROM jurusan WHERE id='$id_edit'");
    $edit    = mysqli_fetch_assoc($res);
}

// Daftar jurusan
$jurusan = mysqli_query($conn, "SELECT * FROM jurusan ORDER BY nama_jurusan ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Jurusan - SaranaCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="app-body">

<?php include __DIR__ . '/../includes/sidebar.php'; ?>

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
            <span class="eyebrow">Manajemen Jurusan</span>
            <h1>Data Jurusan</h1>
            <p>Kelola jurusan atau program studi yang tersedia di sekolah.</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card stat-blue">
            <span class="stat-icon">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2z"/><path d="M12 4L9.5 8h5L12 4z"/><path d="M12 18L8.5 14h5L12 18z"/></svg>
            </span>
            <div>
                <span class="stat-label">Total Jurusan</span>
                <strong class="stat-value"><?= mysqli_num_rows($jurusan) ?></strong>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <h2 class="card-title"><?= $edit ? 'Edit Jurusan' : 'Tambah Jurusan Baru' ?></h2>
                <p class="card-sub"><?= $edit ? 'Ubah data jurusan yang terpilih.' : 'Tambahkan jurusan baru ke dalam sistem.' ?></p>
            </div>
        </div>

        <div class="card-body">
            <form method="POST" action="">
                <?php if ($edit): ?>
                    <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                <?php endif; ?>

                <div class="field">
                    <div class="field-label"><label for="kode_jurusan">Kode Jurusan</label></div>
                    <div class="input-shell has-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4.5"/><path d="M18 21l-6-6 6-6"/></svg>
                        <input type="text" id="kode_jurusan" name="kode_jurusan" class="form-control" value="<?= $edit ? htmlspecialchars($edit['kode_jurusan']) : '' ?>" placeholder="Contoh: RPL" required>
                    </div>
                </div>

                <div class="field">
                    <div class="field-label"><label for="nama_jurusan">Nama Jurusan</label></div>
                    <div class="input-shell has-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="21" x2="21" y2="3"/></svg>
                        <input type="text" id="nama_jurusan" name="nama_jurusan" class="form-control" value="<?= $edit ? htmlspecialchars($edit['nama_jurusan']) : '' ?>" placeholder="Contoh: Rekayasa Perangkat Lunak" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="<?= $edit ? 'update' : 'tambah' ?>" class="btn btn-primary btn-lg">
                        <?= $edit ? 'Simpan Perubahan' : 'Tambah Jurusan' ?>
                    </button>
                    <a href="jurusan.php" class="btn btn-ghost btn-lg">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top:20px;">
        <div class="card-head">
            <div>
                <h2 class="card-title">Daftar Jurusan</h2>
                <p class="card-sub">Seluruh jurusan yang terdaftar dalam sistem</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Jurusan</th>
                        <th>Nama Jurusan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($jurusan) > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($jurusan)): ?>
                            <tr>
                                <td class="cell-no" data-label="No"><?= $no++ ?></td>
                                <td data-label="Kode"><strong><?= htmlspecialchars($row['kode_jurusan']) ?></strong></td>
                                <td data-label="Nama"><?= htmlspecialchars($row['nama_jurusan']) ?></td>
                                <td data-label="Aksi">
                                    <a href="jurusan.php?edit=<?= $row['id'] ?>" class="btn btn-soft btn-sm">Ubah</a>
                                    <a href="jurusan.php?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus jurusan ini? Kelas yang terkait akan dilepaskan dari jurusan ini.')">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="cell-empty" style="padding:0;">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2z"/><path d="M12 4L9.5 8h5L12 4z"/><path d="M12 18L8.5 14h5L12 18z"/></svg>
                                    </div>
                                    <h3>Belum ada jurusan</h3>
                                    <p>Tambahkan jurusan baru menggunakan formulir di atas.</p>
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
