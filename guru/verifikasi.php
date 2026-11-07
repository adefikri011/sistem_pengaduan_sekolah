<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

// Proteksi halaman: Hanya Guru
check_access(['guru']);

$id_pengaduan = (int)($_GET['id'] ?? 0);

// Ambil detail pengaduan
$query = "SELECT p.*, u.nama_lengkap AS nama_siswa, k.nama_kategori 
          FROM pengaduan p
          JOIN users u ON p.id_siswa = u.id
          JOIN kategori_sarana k ON p.id_kategori = k.id
          WHERE p.id = '$id_pengaduan'";
$result = mysqli_query($conn, $query);
$data   = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['submit'])) {
    $status_aksi  = mysqli_real_escape_string($conn, $_POST['status_aksi']); // disetujui_guru / ditolak_guru
    $catatan_guru = mysqli_real_escape_string($conn, trim($_POST['catatan_guru']));

    $update = "UPDATE pengaduan 
               SET status = '$status_aksi', catatan_guru = '$catatan_guru' 
               WHERE id = '$id_pengaduan'";

    if (mysqli_query($conn, $update)) {
    $_SESSION['flash_success'] = 'Status pengaduan berhasil diperbarui!';
    header("Location: index.php");
    exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Laporan - SaranaCare</title>
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
    <div class="page-head">
        <div>
            <span class="eyebrow">Verifikasi Laporan</span>
            <h1>Tinjau Pengaduan Siswa</h1>
            <p>Periksa detail laporan, lalu tentukan apakah pengaduan layak diteruskan ke administrator.</p>
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
                    <h2><?= htmlspecialchars($data['judul']) ?></h2>
                    <p class="detail-sub">Dikirim <?= date('d M Y, H:i', strtotime($data['tgl_pengaduan'])) ?> WIB</p>
                </div>
                <span class="badge badge-warning"><span class="dot"></span>Menunggu Verifikasi</span>
            </div>

            <div class="detail-body">
                <div class="meta-grid">
                    <div class="meta-item">
                        <span>Nama Siswa</span>
                        <strong><?= htmlspecialchars($data['nama_siswa']) ?></strong>
                    </div>
                    <div class="meta-item">
                        <span>Kategori Sarana</span>
                        <strong><?= htmlspecialchars($data['nama_kategori']) ?></strong>
                    </div>
                </div>

                <div class="desc-block">
                    <h3>Deskripsi Kerusakan</h3>
                    <p><?= nl2br(htmlspecialchars($data['deskripsi'])) ?></p>
                </div>

                <div class="desc-block">
                    <h3>Foto Bukti</h3>
                    <?php if ($data['foto']): ?>
                        <a class="photo-frame" href="../assets/uploads/<?= $data['foto'] ?>" data-caption="Foto bukti pengaduan">
                            <img src="../assets/uploads/<?= $data['foto'] ?>" alt="Foto bukti pengaduan">
                        </a>
                    <?php else: ?>
                        <div class="no-photo">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                            Siswa tidak melampirkan foto bukti.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="decision-card">
            <h3>Keputusan Verifikasi</h3>
            <p>Tentukan tindak lanjut untuk laporan ini.</p>

            <form method="POST" action="">
                <div class="field">
                    <div class="field-label">
                        <label>Pilih Keputusan</label>
                    </div>
                    <div class="radio-cards">
                        <label class="radio-card is-success">
                            <input type="radio" name="status_aksi" value="disetujui_guru" checked>
                            <span class="radio-indicator"></span>
                            <span class="radio-text">
                                <strong>Setujui &amp; Teruskan</strong>
                                <small>Laporan valid dan diteruskan ke admin untuk ditindaklanjuti.</small>
                            </span>
                        </label>
                        <label class="radio-card is-danger">
                            <input type="radio" name="status_aksi" value="ditolak_guru">
                            <span class="radio-indicator"></span>
                            <span class="radio-text">
                                <strong>Tolak Pengaduan</strong>
                                <small>Laporan tidak valid atau fasilitas sudah diperbaiki.</small>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="field">
                    <div class="field-label">
                        <label for="catatan_guru">Catatan / Alasan Guru</label>
                    </div>
                    <textarea id="catatan_guru" name="catatan_guru" rows="4" class="form-control" placeholder="Contoh: Pengaduan disetujui untuk ditindaklanjuti admin."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" name="submit" class="btn btn-primary btn-lg">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
                        Simpan Keputusan
                    </button>
                    <a href="index.php" class="btn btn-ghost btn-lg">Batal</a>
                </div>
            </form>
        </section>
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
