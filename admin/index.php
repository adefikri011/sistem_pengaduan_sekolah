<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

// Proteksi halaman: Hanya Admin yang boleh akses
check_access(['admin']);

// Ambil pengaduan yang minimal sudah disetujui guru
$query = "SELECT p.*, u.nama_lengkap AS nama_siswa, k.nama_kategori, kl.nama_kelas
          FROM pengaduan p
          JOIN users u ON p.id_siswa = u.id
          LEFT JOIN kelas kl ON u.id_kelas = kl.id
          JOIN kategori_sarana k ON p.id_kategori = k.id
          WHERE p.status IN ('disetujui_guru', 'diproses', 'selesai')
          ORDER BY p.tgl_pengaduan DESC";
$result = mysqli_query($conn, $query);

// Ringkasan status (untuk kartu statistik)
$total_laporan = 0;
$total_baru = 0;
$total_proses = 0;
$total_selesai = 0;
while ($stat = mysqli_fetch_assoc($result)) {
    $total_laporan++;
    if ($stat['status'] === 'disetujui_guru') $total_baru++;
    elseif ($stat['status'] === 'diproses') $total_proses++;
    elseif ($stat['status'] === 'selesai') $total_selesai++;
}
mysqli_data_seek($result, 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SaranaCare</title>
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
            <span class="eyebrow">Dashboard Administrator</span>
            <h1>Kelola Pengaduan Sekolah</h1>
            <p>Tindak lanjuti laporan yang telah diverifikasi guru dan pantau progres perbaikannya.</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card stat-blue">
            <span class="stat-icon">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="M8 2v4"/><path d="M16 2v4"/><path d="M2 10h20"/></svg>
            </span>
            <div>
                <span class="stat-label">Total Pengaduan</span>
                <strong class="stat-value"><?= $total_laporan ?></strong>
            </div>
        </div>
        <div class="stat-card stat-amber">
            <span class="stat-icon">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
            </span>
            <div>
                <span class="stat-label">Belum Ditindak</span>
                <strong class="stat-value"><?= $total_baru ?></strong>
            </div>
        </div>
        <div class="stat-card stat-violet">
            <span class="stat-icon">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
            </span>
            <div>
                <span class="stat-label">Sedang Diproses</span>
                <strong class="stat-value"><?= $total_proses ?></strong>
            </div>
        </div>
        <div class="stat-card stat-green">
            <span class="stat-icon">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m22 4-10 10-3-3"/></svg>
            </span>
            <div>
                <span class="stat-label">Selesai</span>
                <strong class="stat-value"><?= $total_selesai ?></strong>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <h2 class="card-title">Daftar Pengaduan Masuk</h2>
                <p class="card-sub">Pengaduan yang sudah diverifikasi oleh guru</p>
            </div>
            <div class="card-tools">
                <div class="search-box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" id="tableSearch" placeholder="Cari pengaduan...">
                </div>
                <select id="statusFilter" class="filter-select">
                    <option value="">Semua Status</option>
                    <option value="disetujui_guru">Belum Ditindak</option>
                    <option value="diproses">Diproses</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table" id="pengaduanTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Siswa &amp; Kelas</th>
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
                            <tr data-status="<?= htmlspecialchars($row['status']) ?>" data-search="<?= htmlspecialchars(strtolower($row['nama_siswa'] . ' ' . $row['judul'] . ' ' . $row['deskripsi'] . ' ' . $row['nama_kategori'] . ' ' . ($row['nama_kelas'] ?? ''))) ?>">
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
                                            <small>Kelas <?= htmlspecialchars($row['nama_kelas'] ?? '-') ?></small>
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
                                        case 'disetujui_guru':
                                            echo '<span class="badge badge-info"><span class="dot"></span>Belum Ditindak</span>';
                                            break;
                                        case 'diproses':
                                            echo '<span class="badge badge-primary"><span class="dot"></span>Diproses</span>';
                                            break;
                                        case 'selesai':
                                            echo '<span class="badge badge-success"><span class="dot"></span>Selesai</span>';
                                            break;
                                    }
                                    ?>
                                </td>
                                <td data-label="Aksi">
                                    <a href="tanggapan.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                        Tanggapi
                                    </a>
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
                                    <p>Belum ada pengaduan yang diteruskan oleh guru. Laporan akan muncul di sini setelah diverifikasi.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr id="noResultRow" class="is-hidden">
                        <td colspan="8" style="padding:0;">
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                </div>
                                <h3>Tidak ditemukan</h3>
                                <p>Tidak ada pengaduan yang cocok dengan pencarian atau filter yang dipilih.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    (function () {
        var search = document.getElementById('tableSearch');
        var filter = document.getElementById('statusFilter');
        var table = document.getElementById('pengaduanTable');
        var noResult = document.getElementById('noResultRow');

        if (!table) return;

        var rows = Array.prototype.slice.call(table.querySelectorAll('tbody tr[data-status]'));

        function applyFilter() {
            var keyword = (search ? search.value : '').toLowerCase().trim();
            var status = filter ? filter.value : '';
            var visible = 0;

            rows.forEach(function (row) {
                var matchKeyword = !keyword || (row.getAttribute('data-search') || '').indexOf(keyword) !== -1;
                var matchStatus = !status || row.getAttribute('data-status') === status;
                var show = matchKeyword && matchStatus;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            if (noResult) {
                noResult.classList.toggle('is-hidden', visible !== 0 || rows.length === 0);
            }
        }

        if (search) search.addEventListener('input', applyFilter);
        if (filter) filter.addEventListener('change', applyFilter);
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
