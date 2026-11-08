<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

// Proteksi halaman: Hanya Siswa
check_access(['siswa']);

$siswa_id = $_SESSION['user_id'];
$error    = '';

// Ambil data kategori sarana untuk dropdown
$kategori_query = mysqli_query($conn, "SELECT * FROM kategori_sarana ORDER BY nama_kategori ASC");

if (isset($_POST['submit'])) {
    $judul       = mysqli_real_escape_string($conn, trim($_POST['judul']));
    $id_kategori = (int)$_POST['id_kategori'];
    $deskripsi   = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));
    $nama_foto   = NULL;

    // Upload Foto
    if (!empty($_FILES['foto']['name'])) {
        $filename   = $_FILES['foto']['name'];
        $tmp_name   = $_FILES['foto']['tmp_name'];
        $file_ext   = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed    = ['jpg', 'jpeg', 'png'];
        $upload_err = '';

        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            $upload_err = "Gagal upload foto (kode error: " . $_FILES['foto']['error'] . ").";
        } elseif (!in_array($file_ext, $allowed)) {
            $upload_err = "Format foto harus JPG, JPEG, atau PNG!";
        } else {
            $nama_foto = time() . '_' . uniqid() . '.' . $file_ext;
            $upload_dir = __DIR__ . '/../assets/uploads/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (!move_uploaded_file($tmp_name, $upload_dir . $nama_foto)) {
                $upload_err = "Gagal menyimpan foto ke folder. Periksa izin tulis folder assets/uploads.";
                $nama_foto = NULL;
            }
        }

        if (!empty($upload_err)) {
            $error = $upload_err;
        }
    }

    if (empty($error)) {
        $query = "INSERT INTO pengaduan (id_siswa, id_kategori, judul, deskripsi, foto, status) 
                  VALUES ('$siswa_id', '$id_kategori', '$judul', '$deskripsi', " . ($nama_foto ? "'$nama_foto'" : "NULL") . ", 'menunggu_guru')";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['flash_success'] = 'Laporan berhasil dikirim dan sedang menunggu verifikasi Wali Kelas.';
            header("Location: index.php");
            exit;
        } else {
            $error = "Gagal mengirim pengaduan: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Pengaduan - SaranaCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="app-body">

<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<main class="app-main">
    <div class="page-head">
        <div>
            <span class="eyebrow">Laporan Baru</span>
            <h1>Buat Pengaduan Sarana</h1>
            <p>Isi rincian sarana atau fasilitas yang rusak secara jelas dan jujur agar cepat ditindaklanjuti.</p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <div class="form-shell">
        <div class="form-card">
            <div class="form-card-head">
                <h2>Form Pengaduan</h2>
                <p>Data yang kamu kirim akan diverifikasi oleh wali kelas terlebih dahulu.</p>
            </div>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="field">
                    <div class="field-label">
                        <label for="judul">Judul Laporan / Barang Rusak</label>
                    </div>
                    <div class="input-shell has-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                        <input type="text" id="judul" name="judul" class="form-control" placeholder="Contoh: Proyektor di XII RPL 1 Buram" required autocomplete="off">
                    </div>
                </div>

                <div class="field">
                    <div class="field-label">
                        <label for="id_kategori">Kategori Sarana</label>
                    </div>
                    <div class="input-shell has-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h6v6H4z"/><path d="M14 4h6v6h-6z"/><path d="M4 14h6v6H4z"/><path d="M14 14h6v6h-6z"/></svg>
                        <select id="id_kategori" name="id_kategori" class="form-select" required>
                            <option value="">Pilih kategori sarana</option>
                            <?php while ($kat = mysqli_fetch_assoc($kategori_query)): ?>
                                <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <div class="field-label">
                        <label for="deskripsi">Deskripsi Detail Kerusakan</label>
                        <span class="field-hint" id="descCount">0 / 500</span>
                    </div>
                    <textarea id="deskripsi" name="deskripsi" rows="5" maxlength="500" class="form-control" placeholder="Jelaskan secara rinci lokasi barang, kondisi kerusakan, dan sejak kapan kerusakan terjadi." required></textarea>
                </div>

                <div class="field">
                    <div class="field-label">
                        <label for="foto">Foto Bukti</label>
                        <span class="field-optional">Opsional</span>
                    </div>
                    <label class="file-drop" id="fileDrop">
                        <input type="file" id="foto" name="foto" accept="image/*">
                        <span class="file-drop-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/></svg>
                        </span>
                        <strong>Klik atau seret foto ke sini</strong>
                        <span>Format JPG, JPEG, atau PNG</span>
                    </label>
                    <div class="file-preview" id="filePreview">
                        <img id="filePreviewImg" src="" alt="Pratinjau foto">
                        <div class="file-preview-info">
                            <span id="fileName">-</span>
                            <button type="button" id="fileRemove">Hapus</button>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="submit" class="btn btn-primary btn-lg">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4z"/><path d="M22 2 11 13"/></svg>
                        Kirim Laporan
                    </button>
                    <a href="index.php" class="btn btn-ghost btn-lg">Batal</a>
                </div>
            </form>
        </div>

        <aside class="side-panel">
            <span class="side-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
            </span>
            <h3>Tips laporan yang baik</h3>
            <p>Laporan yang jelas membantu guru dan admin mengambil tindakan lebih cepat dan tepat.</p>
            <ul class="tip-list">
                <li>
                    <span class="tip-check"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                    Tulis judul singkat dan spesifik, sebutkan nama barangnya.
                </li>
                <li>
                    <span class="tip-check"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                    Jelaskan lokasi persis, misalnya ruang kelas atau laboratorium.
                </li>
                <li>
                    <span class="tip-check"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                    Sertakan foto bukti agar kondisi kerusakan terlihat jelas.
                </li>
                <li>
                    <span class="tip-check"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                    Gunakan bahasa yang sopan dan hindari laporan palsu.
                </li>
            </ul>
            <div class="side-foot">Laporan akan berstatus <strong style="color:#cbd5e1;">Menunggu Verifikasi</strong> sampai wali kelas menyetujuinya.</div>
        </aside>
    </div>
</main>

<script>
    (function () {
        var input = document.getElementById('foto');
        var drop = document.getElementById('fileDrop');
        var preview = document.getElementById('filePreview');
        var previewImg = document.getElementById('filePreviewImg');
        var fileName = document.getElementById('fileName');
        var removeBtn = document.getElementById('fileRemove');
        var desc = document.getElementById('deskripsi');
        var descCount = document.getElementById('descCount');

        if (input) {
            input.addEventListener('change', function () {
                var file = input.files && input.files[0];
                if (!file) return;
                fileName.textContent = file.name;
                if (file.type.indexOf('image/') === 0) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        previewImg.src = e.target.result;
                        preview.classList.add('is-visible');
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.classList.remove('is-visible');
                }
            });
        }

        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                input.value = '';
                previewImg.src = '';
                preview.classList.remove('is-visible');
                fileName.textContent = '';
            });
        }

        if (drop) {
            ['dragover', 'dragenter'].forEach(function (evt) {
                drop.addEventListener(evt, function (e) {
                    e.preventDefault();
                    drop.classList.add('is-dragover');
                });
            });
            ['dragleave', 'drop'].forEach(function (evt) {
                drop.addEventListener(evt, function (e) {
                    e.preventDefault();
                    drop.classList.remove('is-dragover');
                });
            });
        }

        if (desc && descCount) {
            var updateCount = function () {
                descCount.textContent = desc.value.length + ' / 500';
            };
            desc.addEventListener('input', updateCount);
            updateCount();
        }
    })();
</script>
</body>
</html>
