-- ============================================================================
-- Skema Database: Sistem Pengaduan Sarana Sekolah (SaranaCare)
-- Database: db_pengaduan_sekolah
-- Kompatibel: InfinityFree / MySQL / phpMyAdmin
-- ============================================================================

CREATE DATABASE IF NOT EXISTS db_pengaduan_sekolah
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE db_pengaduan_sekolah;

-- ----------------------------------------------------------------------------
-- Tabel: users (Admin, Guru, Siswa)
-- Login: Guru pakai NIP, Siswa pakai NISN, Admin pakai username
-- Password default untuk Guru/Siswa = NIP / NISN itu sendiri
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nip VARCHAR(30) NULL UNIQUE COMMENT 'NIP untuk Guru',
    nisn VARCHAR(30) NULL UNIQUE COMMENT 'NISN untuk Siswa',
    username VARCHAR(100) NULL COMMENT 'Username untuk Admin',
    nama_lengkap VARCHAR(150) NOT NULL,
    role ENUM('admin', 'guru', 'siswa') NOT NULL DEFAULT 'siswa',
    id_kelas INT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- Tabel: jurusan (Program studi di sekolah)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS jurusan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_jurusan VARCHAR(20) NOT NULL UNIQUE,
    nama_jurusan VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- Tabel: kelas (Kelas siswa, terhubung jurusan & wali kelas)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS kelas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kelas VARCHAR(50) NOT NULL,
    id_jurusan INT NULL,
    id_guru INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_jurusan) REFERENCES jurusan(id) ON DELETE SET NULL,
    FOREIGN KEY (id_guru) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- Tabel: kategori_sarana (Kategori fasilitas yang dapat dilaporkan)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS kategori_sarana (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- Tabel: pengaduan (Laporan kerusakan sarana oleh siswa)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pengaduan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_siswa INT NOT NULL,
    id_kategori INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT NOT NULL,
    foto VARCHAR(255) NULL,
    status ENUM('menunggu_guru','disetujui_guru','ditolak_guru','diproses','selesai') NOT NULL DEFAULT 'menunggu_guru',
    catatan_guru TEXT NULL,
    tgl_pengaduan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_siswa) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (id_kategori) REFERENCES kategori_sarana(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- Tabel: tanggapan (Tanggapan admin terhadap pengaduan)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tanggapan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pengaduan INT NOT NULL,
    id_admin INT NOT NULL,
    tanggapan TEXT NOT NULL,
    tgl_tanggapan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengaduan) REFERENCES pengaduan(id) ON DELETE CASCADE,
    FOREIGN KEY (id_admin) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- Index untuk performa
-- ----------------------------------------------------------------------------
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_nip ON users(nip);
CREATE INDEX idx_users_nisn ON users(nisn);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_kelas_jurusan ON kelas(id_jurusan);
CREATE INDEX idx_kelas_guru ON kelas(id_guru);
CREATE INDEX idx_pengaduan_status ON pengaduan(status);
CREATE INDEX idx_pengaduan_siswa ON pengaduan(id_siswa);
CREATE INDEX idx_pengaduan_kategori ON pengaduan(id_kategori);
CREATE INDEX idx_tanggapan_pengaduan ON tanggapan(id_pengaduan);

-- ============================================================================
-- Selesai. Impor file ini di phpMyAdmin (InfinityFree) lalu jalankan
-- auth/fix_user.php untuk membuat akun awal.
-- ============================================================================
