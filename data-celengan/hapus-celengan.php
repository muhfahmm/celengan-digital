<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}
include('../config/db.php');

$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM celengan WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);

header("Location: ../dashboard/index.php");
exit;
?>
