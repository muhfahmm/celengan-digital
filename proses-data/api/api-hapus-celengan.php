<?php
session_start();
include('../../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$celengan_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$celengan_id) {
    header('Location: ../../index.php?error=no_id');
    exit;
}

try {
    // Hapus celengan. Karena ada foreign key ON DELETE CASCADE, 
    // transaksi terkait di `transaksi_pengisian` akan ikut terhapus otomatis.
    $stmt = $pdo->prepare("DELETE FROM celengan WHERE id = ? AND user_id = ?");
    $stmt->execute([$celengan_id, $user_id]);

    if ($stmt->rowCount()) {
        header('Location: ../../index.php?success=deleted');
    } else {
        header('Location: ../../index.php?error=delete_failed');
    }
    exit;

} catch (PDOException $e) {
    // error_log($e->getMessage());
    header('Location: ../../index.php?error=db_fail');
    exit;
}
?>