<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

// Proteksi halaman: Hanya Guru yang boleh akses
check_access(['guru']);

$guru_id = $_SESSION['user_id'];

// 1. Cari kelas yang diampu oleh guru ini
$query_kelas = "SELECT * FROM kelas WHERE id_guru = '$guru_id'";
$res_kelas   = mysqli_query($conn, $query_kelas);
$kelas       = mysqli_fetch_assoc($res_kelas);

$id_kelas = $kelas['id'] ?? 0;
$nama_kelas = $kelas['nama_kelas'] ?? 'Belum Ditugaskan';

// 2. Ambil semua pengaduan khusus dari siswa di kelas ini
$query = "SELECT p.*, u.nama_lengkap AS nama_siswa, k.nama_kategori 
          FROM pengaduan p
          JOIN users u ON p.id_siswa = u.id
          JOIN kategori_sarana k ON p.id_kategori = k.id
          WHERE u.id_kelas = '$id_kelas'
          ORDER BY p.tgl_pengaduan DESC";
$result = mysqli_query($conn, $query);

// Ringkasan status (untuk kartu statistik)
$total_laporan = 0;
$total_menunggu = 0;
$total_disetujui = 0;
$total_ditolak = 0;
while ($stat = mysqli_fetch_assoc($result)) {
    $total_laporan++;
    if ($stat['status'] === 'menunggu_guru') $total_menunggu++;
    elseif ($stat['status'] === 'disetujui_guru') $total_disetujui++;
    elseif ($stat['status'] === 'ditolak_guru') $total_ditolak++;
}
mysqli_data_seek($result, 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - SaranaCare</title>
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
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <div class="page-head">
        <div>
            <span class="eyebrow">Dashboard Guru</span>
            <h1>Verifikasi Pengaduan Siswa</h1>
            <p>Kelola dan verifikasi laporan kerusakan sarana dari siswa kelas <strong><?= htmlspecialchars($nama_kelas) ?></strong>.</p>
        </div>
        <div class="page-actions">
            <a href="siswa.php" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                Manajemen Siswa
            </a>
            <a href="profil.php" class="btn btn-soft">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="18" y1="8" x2="23" y2="8"/><line x1="18" y1="14" x2="23" y2="14"/></svg>
                Profil Guru
            </a>
            <span class="role-chip role-guru">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                Kelas <?= htmlspecialchars($nama_kelas) ?>
            </span>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card stat-blue">
            <span class="stat-icon">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 15h6"/><path d="M9 11h2"/></svg>
            </span>
            <div>
                <span class="stat-label">Total Pengaduan</span>
                <strong class="stat-value"><?= $total_laporan ?></strong>
            </div>
        </div>
        <div class="stat-card stat-amber">
            <span class="stat-icon">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 7v5l3 2"/></svg>
            </span>
            <div>
                <span class="stat-label">Menunggu Verifikasi</span>
                <strong class="stat-value"><?= $total_menunggu ?></strong>
            </div>
        </div>
        <div class="stat-card stat-green">
            <span class="stat-icon">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m22 4-10 10-3-3"/></svg>
            </span>
            <div>
                <span class="stat-label">Disetujui</span>
                <strong class="stat-value"><?= $total_disetujui ?></strong>
            </div>
        </div>
        <div class="stat-card stat-rose">
            <span class="stat-icon">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
            </span>
            <div>
                <span class="stat-label">Ditolak</span>
                <strong class="stat-value"><?= $total_ditolak ?></strong>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <h2 class="card-title">Pengaduan Kelas <?= htmlspecialchars($nama_kelas) ?></h2>
                <p class="card-sub">Laporan terbaru dari siswa di kelas yang Anda ampu</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Siswa</th>
                        <th>Judul &amp; Detail</th>
                        <th>Kategori</th>
                        <th>Foto</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="cell-no" data-label="No"><?= $no++ ?></td>
                                <td class="cell-date" data-label="Tanggal">
                                    <strong><?= date('d M Y', strtotime($row['tgl_pengaduan'])) ?></strong>
                                    <small><?= date('H:i', strtotime($row['tgl_pengaduan'])) ?> WIB</small>
                                </td>
                                <td data-label="Siswa">
                                    <div class="table-user">
                                        <span class="table-avatar"><?= htmlspecialchars(substr($row['nama_siswa'], 0, 1)) ?></span>
                                        <div>
                                            <strong><?= htmlspecialchars($row['nama_siswa']) ?></strong>
                                            <small>Kelas <?= htmlspecialchars($nama_kelas) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="cell-desc" data-label="Laporan">
                                    <span class="cell-strong"><?= htmlspecialchars($row['judul']) ?></span>
                                    <p><?= htmlspecialchars($row['deskripsi']) ?></p>
                                </td>
                                <td data-label="Kategori"><span class="type-pill"><?= htmlspecialchars($row['nama_kategori']) ?></span></td>
                                <td data-label="Foto">
                                    <?php if ($row['foto']): ?>
                                        <a href="../assets/uploads/<?= $row['foto'] ?>" class="photo-link" data-caption="Foto bukti pengaduan">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                                            Lihat
                                        </a>
                                    <?php else: ?>
                                        <span class="muted-dash">—</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Status">
                                    <?php
                                    switch ($row['status']) {
                                        case 'menunggu_guru':
                                            echo '<span class="badge badge-warning"><span class="dot"></span>Menunggu</span>';
                                            break;
                                        case 'ditolak_guru':
                                            echo '<span class="badge badge-danger"><span class="dot"></span>Ditolak</span>';
                                            break;
                                        case 'disetujui_guru':
                                            echo '<span class="badge badge-info"><span class="dot"></span>Disetujui</span>';
                                            break;
                                        case 'diproses':
                                            echo '<span class="badge badge-primary"><span class="dot"></span>Diproses Admin</span>';
                                            break;
                                        case 'selesai':
                                            echo '<span class="badge badge-success"><span class="dot"></span>Selesai</span>';
                                            break;
                                    }
                                    ?>
                                </td>
                                <td data-label="Aksi">
                                    <?php if ($row['status'] === 'menunggu_guru'): ?>
                                        <a href="verifikasi.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m22 4-10 10-3-3"/></svg>
                                            Verifikasi
                                        </a>
                                    <?php else: ?>
                                        <span class="muted-dash">Sudah diproses</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="cell-empty" style="padding:0;">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 15h6"/><path d="M9 11h2"/></svg>
                                    </div>
                                    <h3>Belum ada pengaduan</h3>
                                    <p>Saat ini belum ada siswa di kelas <strong><?= htmlspecialchars($nama_kelas) ?></strong> yang mengirim laporan.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div class="photo-modal" id="photoModal">
    <div class="photo-modal-content">
        <button class="photo-modal-close" id="photoModalClose" aria-label="Tutup">×</button>
        <img id="photoModalImg" src="" alt="Foto bukti">
        <p class="photo-modal-caption" id="photoModalCaption"></p>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('photoModal');
        var modalImg = document.getElementById('photoModalImg');
        var modalCaption = document.getElementById('photoModalCaption');
        var modalClose = document.getElementById('photoModalClose');
        var links = document.querySelectorAll('.photo-link, .photo-frame');

        if (!modal || !links.length) return;

        links.forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                modalImg.src = this.getAttribute('href');
                modalCaption.textContent = this.getAttribute('data-caption') || 'Foto bukti';
                modal.classList.add('is-open');
            });
        });

        function closeModal() {
            modal.classList.remove('is-open');
            modalImg.src = '';
        }

        modalClose.addEventListener('click', closeModal);
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
        });
    })();
</script>
</body>
</html>
