# Dokumentasi Sistem Pengaduan Sarana Sekolah (SaranaCare)

Sistem informasi pengaduan sarana dan prasarana sekolah berbasis web (PHP + MySQL) dengan sistem role-based access control: **Administrator, Guru (Wali Kelas), dan Siswa**.

---

## 1. Struktur Folder

```text
sistem-pengaduan-sarana-sekolah/
├── index.php                      # Redirect otomatis ke halaman login
├── database.sql                   # Skema database lengkap (baru)
├── DOKUMENTASI.md                 # Dokumentasi sistem (baru)
├── config/
│   └── database.php               # Koneksi MySQL
├── includes/
│   └── auth_check.php             # Proteksi halaman & pengecekan role
├── assets/
│   ├── bg.jpeg                    # Gambar latar halaman login
│   ├── style.css                  # Design system — konsisten semua halaman
│   └── uploads/                   # Tempat foto bukti pengaduan (dibuat otomatis)
├── auth/
│   ├── login.php                  # Halaman masuk (NIP/NISN/Username)
│   ├── logout.php                 # Keluar & hapus sesi
│   └── fix_user.php               # Utility: buat ulang akun default
├── admin/
│   ├── index.php                  # Dashboard admin — kelola semua pengaduan
│   ├── tanggapan.php              # Tanggapi & ubah status pengaduan
│   ├── kelas.php                  # Manajemen Kelas (terhubung Jurusan)
│   ├── jurusan.php                # Manajemen Jurusan
│   ├── guru.php                   # Manajemen Guru
│   ├── pengguna.php               # Manajemen Pengguna (Admin/Guru/Siswa) (baru)
│   └── profil.php                 # Profil Admin (baru)
├── guru/
│   ├── index.php                  # Dashboard guru — verifikasi pengaduan kelas
│   ├── siswa.php                  # CRUD Siswa kelas yang diampu
│   ├── verifikasi.php             # Verifikasi (setuju/tolak) pengaduan siswa
│   └── profil.php                 # Profil Guru (baru)
└── siswa/
    ├── index.php                  # Dashboard siswa — riwayat pengaduan
    ├── tambah_laporan.php         # Form buat pengaduan baru + upload foto
    └── profil.php                 # Profil Siswa (baru)
```

> Folder struktur **tidak diubah** dari versi asli. File baru hanya ditambahkan di dalam folder yang sudah ada.

---

## 2. Role & Login

| Role | Login dengan | Password Default | Akses |
|------|-------------|------------------|-------|
| **Admin** | Username | Di-set saat pembuatan akun | `admin/` — kelola semua |
| **Guru** | NIP | NIP (sama dengan NIP) | `guru/` — verifikasi kelas |
| **Siswa** | NISN | NISN (sama dengan NISN) | `siswa/` — buat & pantau laporan |

Untuk Guru dan Siswa, password awal otomatis sama dengan NIP atau NISN mereka. Password dapat diubah melalui halaman Profil masing-masing.

---

## 3. Fitur Utama per Halaman

### Login (`auth/login.php`)
- Input: **NIP / NISN / Username** + Password
- Tidak ada keterangan akun demo (100% siap pakai)
- Redirect otomatis ke dashboard sesuai role setelah login

### Dashboard Admin (`admin/index.php`)
- Kartu statistik pengaduan
- Navigasi cepat ke: Pengaduan, Tanggapan, Kelas, Jurusan, Pengguna, Profil Admin

### Manajemen Pengguna (`admin/pengguna.php`)
- **Create**: Buat akun Admin/Guru/Siswa
  - Guru: hanya butuh NIP + Nama (password otomatis = NIP)
  - Siswa: hanya butuh NISN + Nama (password otomatis = NISN)
  - Admin: Username + Nama + Password
- **Read**: Tabel semua pengguna dengan role dan kelas
- **Update**: Ubah data pengguna
- **Delete**: Hapus pengguna (tidak dapat menghapus diri sendiri)

### Manajemen Kelas (`admin/kelas.php`)
- CRUD Kelas dengan pilihan Jurusan dan Wali Kelas (Guru)

### Manajemen Jurusan (`admin/jurusan.php`)
- CRUD Jurusan (kode + nama jurusan)

### Manajemen Guru (`admin/guru.php`)
- CRUD data guru (NIP, Nama, Password)

### Tanggapan Admin (`admin/tanggapan.php`)
- Detail pengaduan + foto bukti
- Ubah status: Diproses / Selesai
- **Status Selesai tidak dapat diubah kembali** (terkunci otomatis)
- Timeline riwayat tanggapan

### Profil Admin (`admin/profil.php`)
- Ubah username dan nama lengkap
- Ganti password

### Dashboard Guru (`guru/index.php`)
- Kartu statistik kelas yang diampu
- Navigasi ke: Manajemen Siswa, Verifikasi, Profil Guru

### Manajemen Siswa Guru (`guru/siswa.php`)
- CRUD siswa dalam kelas yang diampu guru

### Verifikasi Guru (`guru/verifikasi.php`)
- Setujui atau Tolak pengaduan siswa
- Catatan alasan verifikasi

### Profil Guru (`guru/profil.php`)
- Ubah NIP, Nama Lengkap
- Ganti password (awal = NIP)

### Dashboard Siswa (`siswa/index.php`)
- Kartu statistik: Total, Menunggu, Diproses, Selesai
- Tabel riwayat pengaduan dengan badge status
- Navigasi ke: Buat Pengaduan, Profil Siswa

### Buat Pengaduan (`siswa/tambah_laporan.php`)
- Form: Judul, Kategori, Deskripsi, Foto bukti
- Pratinjau foto & hitungan karakter

### Profil Siswa (`siswa/profil.php`)
- Ubah NISN, Nama Lengkap
- Ganti password (awal = NISN)

---

## 4. Status Pengaduan

| Status | Keterangan | Dapat Diubah? |
|--------|------------|---------------|
| `menunggu_guru` | Menunggu verifikasi wali kelas | Ya |
| `disetujui_guru` | Disetujui guru, siap diproses admin | Ya |
| `ditolak_guru` | Ditolak oleh wali kelas | Ya |
| `diproses` | Sedang ditangani admin | Ya |
| `selesai` | Selesai diperbaiki | **Tidak (terkunci)** |

---

## 5. Akun Default (setelah menjalankan `fix_user.php`)

| Role | Login | Password | Keterangan |
|------|-------|----------|------------|
| Admin | `admin` | `admin123` | Dibuat via fix_user.php |
| Guru | NIP: `198501012010011001` | `198501012010011001` | Password = NIP |
| Siswa | NISN: `001234567890` | `001234567890` | Password = NISN |

---

## 6. Skema Database (`database.sql`)

Impor file `database.sql` di phpMyAdmin (InfinityFree). Nama database: **`db_pengaduan_sekolah`**

Tabel yang dibuat:
- `users` — Admin, Guru, Siswa (dengan kolom nip, nisn, username)
- `jurusan` — Program studi
- `kelas` — Kelas (terhubung ke jurusan & guru)
- `kategori_sarana` — Kategori fasilitas
- `pengaduan` — Laporan kerusakan
- `tanggapan` — Balasan admin

---

## 7. Cara Menjalankan (InfinityFree / Hosting)

1. Buat database bernama **`db_pengaduan_sekolah`** di phpMyAdmin InfinityFree
2. Impor file `database.sql` ke database tersebut
3. Upload semua file ke folder `public_html` (atau root domain)
4. Buka `auth/fix_user.php` di browser untuk membuat akun awal
5. Buka halaman utama → login dengan akun default di atas
6. Setelah login, segera ganti password default melalui halaman Profil

---

## 8. Teknologi yang Digunakan

- PHP (native, tanpa framework)
- MySQL / MySQLi
- HTML5 + CSS3 (kustom, design system sendiri)
- JavaScript vanilla (interaksi halaman)
- Google Fonts: Plus Jakarta Sans

---

## 9. Desain UI/UX

- **Responsif**: Tabel berubah jadi kartu di layar kecil, navbar menyesuaikan
- **Profesional & Elegan**: Warna seimbang, bayangan halus, tipografi modern
- **Interaktif**: Hover efek, animasi alert, toggle password, pratinjau foto
- **Tidak ada elemen demo**: Login bersih, tanpa keterangan akun coba-coba
