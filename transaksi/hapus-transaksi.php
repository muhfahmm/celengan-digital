<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include('../config/db.php');

$id = $_GET['id'];

// Ambil data transaksi
$stmt = $pdo->prepare("SELECT * FROM transaksi WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($data) {
    // Update total celengan
    $update = $data['tipe'] == 'masuk' ? -$data['nominal'] : $data['nominal'];
    $pdo->prepare("UPDATE celengan SET total = total + ? WHERE id = ?")
        ->execute([$update, $data['celengan_id']]);

    // Hapus transaksi
    $pdo->prepare("DELETE FROM transaksi WHERE id = ?")->execute([$id]);
}

header("Location: ../dashboard/index.php");
exit;
