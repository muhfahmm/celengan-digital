<?php
session_start();
include('../../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$celengan_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$celengan_id || !is_numeric($celengan_id)) {
    header('Location: ../../index.php?error=no_id');
    exit;
}

try {
    // Hapus celengan berdasarkan ID dan pastikan hanya user yang bersangkutan
    // Karena ada ON DELETE CASCADE pada tabel transaksi_pengisian, 
    // semua transaksi terkait akan ikut terhapus.
    $stmt = $pdo->prepare("DELETE FROM celengan WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $celengan_id, 'user_id' => $user_id]);

    if ($stmt->rowCount()) {
        header('Location: ../../index.php?success=deleted');
    } else {
        // Celengan tidak ditemukan atau bukan milik user
        header('Location: ../../index.php?error=delete_failed');
    }
    exit;

} catch (PDOException $e) {
    // error_log($e->getMessage());
    header('Location: ../../index.php?error=db_fail');
    exit;
}
?>