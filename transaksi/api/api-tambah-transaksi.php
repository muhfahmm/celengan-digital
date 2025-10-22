<?php
session_start();
include('../../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$celengan_id = $_POST['celengan_id'];
$nominal = $_POST['nominal'];
$tipe = $_POST['tipe'];
$keterangan = $_POST['keterangan'] ?? '';

$stmt = $pdo->prepare("INSERT INTO transaksi (celengan_id, nominal, tipe, keterangan) VALUES (?, ?, ?, ?)");
$stmt->execute([$celengan_id, $nominal, $tipe, $keterangan]);

// Update total di tabel celengan
if ($tipe == 'masuk') {
    $pdo->prepare("UPDATE celengan SET total = total + ? WHERE id = ?")->execute([$nominal, $celengan_id]);
} else {
    $pdo->prepare("UPDATE celengan SET total = total - ? WHERE id = ?")->execute([$nominal, $celengan_id]);
}

header("Location: ../../dashboard/index.php");
exit;
