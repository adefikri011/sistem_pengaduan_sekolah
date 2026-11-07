<?php
$host = "localhost";
$user = "root";
$pass = "app123";
$db   = "db_pengaduan_sekolah";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}
?>