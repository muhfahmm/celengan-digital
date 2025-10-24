<?php
session_start();
include('../../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

// Ambil data dari form
$nama = trim($_POST['nama_celengan'] ?? '');
$target = trim($_POST['target'] ?? '');

// Validasi input
if ($nama === '' || $target === '' || !is_numeric($target) || $target <= 0) {
    header("Location: ../tambah-celengan.php?error=Nama dan target tidak boleh kosong atau 0");
    exit;
}

// Simpan data ke database
$stmt = $pdo->prepare("INSERT INTO celengan (user_id, nama_celengan, target, total) VALUES (?, ?, ?, 0)");
$stmt->execute([$_SESSION['user_id'], $nama, $target]);

header("Location: ../../dashboard/index.php");
exit;
