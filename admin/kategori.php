<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

check_access(['admin']);

$msg = '';

// Hapus kategori
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    mysqli_query($conn, "UPDATE pengaduan SET id_kategori=NULL WHERE id_kategori='$id_hapus'");
    mysqli_query($conn, "DELETE FROM kategori_sarana WHERE id='$id_hapus'");
    header("Location: kategori.php?msg=deleted");
    exit;
}

// Tambah kategori
if (isset($_POST['tambah'])) {
    $nama_kategori = mysqli_real_escape_string($conn, trim($_POST['nama_kategori']));
    mysqli_query($conn, "INSERT INTO kategori_sarana (nama_kategori) VALUES ('$nama_kategori')");
    header("Location: kategori.php?msg=added");
    exit;
}

// Edit kategori
if (isset($_POST['update'])) {
    $id_update    = (int)$_POST['id'];
    $nama_kategori = mysqli_real_escape_string($conn, trim($_POST['nama_kategori']));
    mysqli_query($conn, "UPDATE kategori_sarana SET nama_kategori='$nama_kategori' WHERE id='$id_update'");
    header("Location: kategori.php?msg=updated");
    exit;
}

// Data edit
$edit = null;
if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];
    $res     = mysqli_query($conn, "SELECT * FROM kategori_sarana WHERE id='$id_edit'");
    $edit    = mysqli_fetch_assoc($res);
}

// Daftar kategori
$kategori = mysqli_query($conn, "SELECT * FROM kategori_sarana ORDER BY nama_kategori ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - SaranaCare</title>
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
        if ($_GET['msg'] === 'added') $m = 'Kategori berhasil ditambahkan.';
        if ($_GET['msg'] === 'updated') $m = 'Data kategori berhasil diperbarui.';
        if ($_GET['msg'] === 'deleted') $m = 'Kategori berhasil dihapus.';
        if ($m): ?>
            <div class="alert alert-success">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                <span><?= htmlspecialchars($m) ?></span>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="page-head">
        <div>
            <span class="eyebrow">Manajemen Kategori</span>
            <h1>Kategori Sarana</h1>
            <p>Kelola kategori sarana yang bisa dipilih siswa saat membuat pengaduan.</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card stat-blue">
            <span class="stat-icon">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2z"/><path d="M12 4L9.5 8h5L12 4z"/><path d="M12 18L8.5 14h5L12 18z"/></svg>
            </span>
            <div>
                <span class="stat-label">Total Kategori</span>
                <strong class="stat-value"><?= mysqli_num_rows($kategori) ?></strong>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <h2 class="card-title"><?= $edit ? 'Edit Kategori' : 'Tambah Kategori Baru' ?></h2>
                <p class="card-sub"><?= $edit ? 'Ubah nama kategori sarana.' : 'Tambahkan kategori sarana baru.' ?></p>
            </div>
        </div>

        <div class="card-body">
            <form method="POST" action="">
                <?php if ($edit): ?>
                    <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                <?php endif; ?>

                <div class="field">
                    <div class="field-label"><label for="nama_kategori">Nama Kategori</label></div>
                    <div class="input-shell has-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="1"/><path d="M4 21a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2z"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                        <input type="text" id="nama_kategori" name="nama_kategori" class="form-control" value="<?= $edit ? htmlspecialchars($edit['nama_kategori']) : '' ?>" placeholder="Contoh: Laboratorium Komputer" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="<?= $edit ? 'update' : 'tambah' ?>" class="btn btn-primary btn-lg">
                        <?= $edit ? 'Simpan Perubahan' : 'Tambah Kategori' ?>
                    </button>
                    <a href="kategori.php" class="btn btn-ghost btn-lg">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top:20px;">
        <div class="card-head">
            <div>
                <h2 class="card-title">Daftar Kategori</h2>
                <p class="card-sub">Seluruh kategori sarana yang tersedia untuk pengaduan</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($kategori) > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($kategori)): ?>
                            <tr>
                                <td class="cell-no" data-label="No"><?= $no++ ?></td>
                                <td data-label="Kategori"><strong><?= htmlspecialchars($row['nama_kategori']) ?></strong></td>
                                <td data-label="Aksi">
                                    <a href="kategori.php?edit=<?= $row['id'] ?>" class="btn btn-soft btn-sm">Ubah</a>
                                    <a href="kategori.php?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus kategori ini? Pengaduan yang terkait akan dilepaskan dari kategorinya.')">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="cell-empty" style="padding:0;">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="1"/><path d="M4 21a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2z"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                    </div>
                                    <h3>Belum ada kategori</h3>
                                    <p>Tambahkan kategori sarana baru menggunakan formulir di atas.</p>
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
