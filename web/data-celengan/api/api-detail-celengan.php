<?php
include('../../config/db.php');
require_once('../../config/auth_check.php');

$user_id = $_SESSION['user_id'];
$id_celengan = $_GET['id'];

// Get celengan detail
$stmt = $pdo->prepare("SELECT * FROM celengan WHERE id = ? AND user_id = ?");
$stmt->execute([$id_celengan, $user_id]);
$celengan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$celengan) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Celengan tidak ditemukan']);
    exit;
}

// Get transactions
$stmtTrans = $pdo->prepare("SELECT * FROM transaksi WHERE celengan_id = ? ORDER BY date DESC");
$stmtTrans->execute([$id_celengan]);
$transactions = $stmtTrans->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'status' => 'success',
    'data' => $celengan,
    'transactions' => $transactions
]);
