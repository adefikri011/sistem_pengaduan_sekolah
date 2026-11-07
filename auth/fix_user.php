<?php
require_once __DIR__ . '/../config/database.php';

// Disable foreign key checks
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");
mysqli_query($conn, "TRUNCATE TABLE users");

// Generate hash password secara realtime dari PHP server
$pass_admin = password_hash('123456', PASSWORD_DEFAULT);

// Admin
$q1 = mysqli_query($conn, "INSERT INTO users (username, password, nama_lengkap, role, id_kelas) VALUES ('admin', '$pass_admin', 'Administrator Sistem', 'admin', NULL)");

// Guru: login dengan NIP, password default = NIP
$nip_guru = '198501012010011001';
$pass_guru = password_hash('123456', PASSWORD_DEFAULT);
$q2 = mysqli_query($conn, "INSERT INTO users (nip, password, nama_lengkap, role, id_kelas) VALUES ('$nip_guru', '$pass_guru', 'Krisna S.Kom', 'guru', NULL)");

// Siswa: login dengan NISN, password default = NISN
$nisn_siswa = '001234567890';
$pass_siswa = password_hash('123456', PASSWORD_DEFAULT);
$q3 = mysqli_query($conn, "INSERT INTO users (nisn, password, nama_lengkap, role, id_kelas) VALUES ('$nisn_siswa', '$pass_siswa', 'Budi Santoso', 'siswa', 1)");

// Re-enable foreign key checks
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

if ($q1 && $q2 && $q3) {
    echo "<h2 style='color:green;'>AKUN BERHASIL DIBUAT!</h2>";
    echo "<p>Silakan login kembali dengan:<br>";
    echo "<strong>Admin:</strong> Username: admin | Password: 123456<br>";
    echo "<strong>Guru:</strong> NIP: $nip_guru | Password: 123456<br>";
    echo "<strong>Siswa:</strong> NISN: $nisn_siswa | Password: 123456</p>";
    echo "<a href='login.php'>Ke Halaman Login</a>";
} else {
    echo "Error: " . mysqli_error($conn);
}
