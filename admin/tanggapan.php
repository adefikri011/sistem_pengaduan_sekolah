<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

// Proteksi halaman: Hanya Admin
check_access(['admin']);

$admin_id     = $_SESSION['user_id'];
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

// Ambil riwayat tanggapan admin sebelumnya
$query_tanggapan = "SELECT t.*, u.nama_lengkap AS nama_admin 
                    FROM tanggapan t 
                    JOIN users u ON t.id_admin = u.id 
                    WHERE t.id_pengaduan = '$id_pengaduan' 
                    ORDER BY t.tgl_tanggapan DESC";
$res_tanggapan   = mysqli_query($conn, $query_tanggapan);

if (isset($_POST['submit'])) {
    // Jika sudah selesai, tidak bisa diubah kembali
    if ($data['status'] === 'selesai') {
        header("Location: index.php?msg=locked");
        exit;
    }

    $status_baru = mysqli_real_escape_string($conn, $_POST['status']);
    $tanggapan   = mysqli_real_escape_string($conn, trim($_POST['tanggapan']));

    // 1. Update Status Pengaduan
    mysqli_query($conn, "UPDATE pengaduan SET status = '$status_baru' WHERE id = '$id_pengaduan'");

    // 2. Simpan Catatan Tanggapan jika diisi
    if (!empty($tanggapan)) {
        mysqli_query($conn, "INSERT INTO tanggapan (id_pengaduan, id_admin, tanggapan) VALUES ('$id_pengaduan', '$admin_id', '$tanggapan')");
    }

    $_SESSION['flash_success'] = 'Tanggapan &amp; status pengaduan berhasil diperbarui!';
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanggapan Admin - SaranaCare</title>
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
    <div class="page-head">
        <div>
            <span class="eyebrow">Tanggapan Admin</span>
            <h1><?= htmlspecialchars($data['judul']) ?></h1>
            <p>Tindak lanjuti dan berikan tanggapan untuk pengaduan dari <strong><?= htmlspecialchars($data['nama_siswa']) ?></strong>.</p>
        </div>
        <div class="page-actions">
            <a href="index.php" class="btn btn-ghost">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Kembali
            </a>
        </div>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'locked'): ?>
        <div class="alert alert-warning">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><rect x="9" y="9" width="2" height="6"/><line x1="12" y1="17" x2="12" y2="17"/></svg>
            <span>Pengaduan sudah berstatus <strong>Selesai</strong> dan tidak dapat diubah kembali.</span>
        </div>
    <?php endif; ?>

    <div class="detail-grid">
        <section class="detail-card">
            <div class="detail-card-head">
                <div>
                    <h2>Detail Pengaduan</h2>
                    <p class="detail-sub">Diteruskan dari wali kelas ke administrator</p>
                </div>
                <?php
                $statusLabel = 'Belum Ditindak';
                $statusClass = 'badge-info';
                if ($data['status'] === 'disetujui_guru') { $statusLabel = 'Belum Ditindak'; $statusClass = 'badge-info'; }
                elseif ($data['status'] === 'diproses') { $statusLabel = 'Diproses'; $statusClass = 'badge-primary'; }
                elseif ($data['status'] === 'selesai') { $statusLabel = 'Selesai'; $statusClass = 'badge-success'; }
                ?>
                <span class="badge <?= $statusClass ?>"><span class="dot"></span><?= htmlspecialchars($statusLabel) ?></span>
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
                    <div class="meta-item">
                        <span>Tanggal Pengaduan</span>
                        <strong><?= date('d M Y, H:i', strtotime($data['tgl_pengaduan'])) ?> WIB</strong>
                    </div>
                    <div class="meta-item">
                        <span>Catatan Guru</span>
                        <strong><?= !empty($data['catatan_guru']) ? htmlspecialchars($data['catatan_guru']) : '-' ?></strong>
                    </div>
                </div>

                <div class="desc-block">
                    <h3>Deskripsi Kerusakan</h3>
                    <p><?= nl2br(htmlspecialchars($data['deskripsi'])) ?></p>
                </div>

                <div class="desc-block">
                    <h3>Foto Bukti</h3>
                    <?php if (!empty($data['foto'])): ?>
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
            <h3>Tindak Lanjuti &amp; Beri Tanggapan</h3>
            <p>Perbarui status pengaduan dan tulis catatan perbaikan untuk siswa.</p>

            <form method="POST" action="" <?= $data['status'] === 'selesai' ? 'disabled' : '' ?>>
                <div class="field">
                    <div class="field-label">
                        <label for="status">Update Status Pengaduan</label>
                    </div>
                    <div class="radio-cards">
                        <label class="radio-card is-success">
                            <input type="radio" name="status" value="diproses" <?php if ($data['status'] === 'diproses') echo 'checked'; ?>>
                            <span class="radio-indicator"></span>
                            <span class="radio-text">
                                <strong>Sedang Diproses</strong>
                                <small>Petugas admin sedang mengerjakan atau memperbaiki.</small>
                            </span>
                        </label>
                        <label class="radio-card is-danger">
                            <input type="radio" name="status" value="selesai" <?php if ($data['status'] === 'selesai') echo 'checked'; ?>>
                            <span class="radio-indicator"></span>
                            <span class="radio-text">
                                <strong>Selesai</strong>
                                <small>Fasilitas sudah diperbaiki dan selesai ditangani.</small>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="field">
                    <div class="field-label">
                        <label for="tanggapan">Tambah Tanggapan Baru / Catatan Perbaikan</label>
                        <span class="field-hint" id="replyCount">0 / 1000</span>
                    </div>
                    <textarea id="tanggapan" name="tanggapan" rows="4" class="form-control" placeholder="Contoh: Petunjuk perbaikan sudah dikirim ke tim teknisi IT sekolah."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" name="submit" class="btn btn-primary btn-lg">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                        Simpan Perubahan
                    </button>
                    <a href="index.php" class="btn btn-ghost btn-lg">Batal</a>
                </div>
            </form>
        </section>
    </div>

    <section class="detail-card" style="margin-top:24px;">
        <div class="detail-card-head">
            <div>
                <h2>Riwayat Tanggapan Admin</h2>
                <p class="detail-sub">Catatan perbaikan yang sudah disimpan untuk pengaduan ini</p>
            </div>
        </div>

        <div class="detail-body">
            <?php if (mysqli_num_rows($res_tanggapan) > 0): ?>
                <div class="timeline">
                    <?php while ($t = mysqli_fetch_assoc($res_tanggapan)): ?>
                        <div class="timeline-item">
                            <span class="timeline-avatar">
                                <?= htmlspecialchars(strtoupper(substr($t['nama_admin'], 0, 1))) ?>
                            </span>
                            <div class="timeline-content">
                                <div class="timeline-top">
                                    <strong><?= htmlspecialchars($t['nama_admin']) ?></strong>
                                    <time><?= date('d M Y, H:i', strtotime($t['tgl_tanggapan'])) ?> WIB</time>
                                </div>
                                <p><?= nl2br(htmlspecialchars($t['tanggapan'])) ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="timeline-empty">
                    Belum ada tanggapan tertulis. Tambahkan tanggapan di atas untuk memberikan informasi progres kepada siswa.
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<script>
    (function () {
        var desc = document.getElementById('tanggapan');
        var descCount = document.getElementById('replyCount');
        if (!desc || !descCount) return;
        var updateCount = function () {
            descCount.textContent = desc.value.length + ' / 1000';
        };
        desc.addEventListener('input', updateCount);
        updateCount();
    })();
</script>
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
