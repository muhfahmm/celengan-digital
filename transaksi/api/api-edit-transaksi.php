<?php
session_start();
include('../../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$id = $_POST['id'];
$nominal = $_POST['nominal'];
$tipe = $_POST['tipe'];
$keterangan = $_POST['keterangan'] ?? '';

// Ambil transaksi lama
$stmt = $pdo->prepare("SELECT * FROM transaksi WHERE id = ?");
$stmt->execute([$id]);
$old = $stmt->fetch(PDO::FETCH_ASSOC);

if ($old) {
    // Balikkan efek lama
    if ($old['tipe'] == 'masuk') {
        $pdo->prepare("UPDATE celengan SET total = total - ? WHERE id = ?")
            ->execute([$old['nominal'], $old['celengan_id']]);
    } else {
        $pdo->prepare("UPDATE celengan SET total = total + ? WHERE id = ?")
            ->execute([$old['nominal'], $old['celengan_id']]);
    }

    // Update transaksi baru
    $stmt = $pdo->prepare("UPDATE transaksi SET nominal = ?, tipe = ?, keterangan = ? WHERE id = ?");
    $stmt->execute([$nominal, $tipe, $keterangan, $id]);

    // Terapkan efek baru
    if ($tipe == 'masuk') {
        $pdo->prepare("UPDATE celengan SET total = total + ? WHERE id = ?")
            ->execute([$nominal, $old['celengan_id']]);
    } else {
        $pdo->prepare("UPDATE celengan SET total = total - ? WHERE id = ?")
            ->execute([$nominal, $old['celengan_id']]);
    }

    // Redirect dengan id celengan
    header("Location: ../../dashboard/detail-celengan.php?id=" . $old['celengan_id']);
    exit;
}
?>