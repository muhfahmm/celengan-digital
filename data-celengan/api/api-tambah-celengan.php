<?php
session_start();
include('../../config/db.php');

if (!isset($_SESSION['user_id'])) {
  header("Location: ../../auth/login.php");
  exit;
}

$nama = $_POST['nama_celengan'];
$target = $_POST['target'] ?? 0;

$stmt = $pdo->prepare("INSERT INTO celengan (user_id, nama_celengan, target, total) VALUES (?, ?, ?, 0)");
$stmt->execute([$_SESSION['user_id'], $nama, $target]);

header("Location: ../../dashboard/index.php");
exit;
?>
