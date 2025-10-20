<?php
// Ganti dengan kredensial database Anda
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'db_celengan');

// Membuat koneksi
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Untuk memastikan koneksi menggunakan UTF-8
$conn->set_charset("utf8mb4");
?>