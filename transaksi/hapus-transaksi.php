<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include('../config/db.php');

if (!isset($_GET['id'])) {
    die("ID transaksi tidak ditemukan");
}

$id = $_GET['id'];

// Ambil data transaksi
$stmt = $pdo->prepare("SELECT * FROM transaksi WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($data) {
    $celengan_id = $data['celengan_id'];

    // Update total celengan (balikkan pengaruh transaksi yang dihapus)
    $update = $data['tipe'] == 'masuk' ? -$data['nominal'] : $data['nominal'];
    $pdo->prepare("UPDATE celengan SET total = total + ? WHERE id = ?")
        ->execute([$update, $celengan_id]);

    // Hapus transaksi
    $pdo->prepare("DELETE FROM transaksi WHERE id = ?")->execute([$id]);

    // Kembali ke halaman detail celengan dengan parameter id
    header("Location: ../dashboard/detail-celengan.php?id=" . $celengan_id);
    exit;
} else {
    die("Data transaksi tidak ditemukan");
}
