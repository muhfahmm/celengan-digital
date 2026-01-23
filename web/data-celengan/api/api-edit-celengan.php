<?php
session_start();
include('../../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$id = $_POST['id'];
$nama = $_POST['nama_celengan'];
$target = $_POST['target'] ?? 0;

$stmt = $pdo->prepare("UPDATE celengan SET nama_celengan = ?, target = ? WHERE id = ? AND user_id = ?");
$stmt->execute([$nama, $target, $id, $_SESSION['user_id']]);

header("Location: ../../dashboard/index.php");
exit;
