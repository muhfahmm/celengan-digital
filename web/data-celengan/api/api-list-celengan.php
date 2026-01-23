<?php
include('../../config/db.php');
require_once('../../config/auth_check.php');

$user_id = $_SESSION['user_id'];

// Get pinned celengan
$stmtPinned = $pdo->prepare("SELECT * FROM celengan WHERE user_id = ? AND is_pinned = 1 ORDER BY created_at DESC");
$stmtPinned->execute([$user_id]);
$pinned = $stmtPinned->fetchAll(PDO::FETCH_ASSOC);

// Get unpinned celengan
$stmtUnpinned = $pdo->prepare("SELECT * FROM celengan WHERE user_id = ? AND is_pinned = 0 ORDER BY created_at DESC");
$stmtUnpinned->execute([$user_id]);
$unpinned = $stmtUnpinned->fetchAll(PDO::FETCH_ASSOC);

$data = array_merge($pinned, $unpinned);

// Get summary
$sumStmt = $pdo->prepare("SELECT SUM(total) AS total_tabungan, SUM(target) AS total_target FROM celengan WHERE user_id = ?");
$sumStmt->execute([$user_id]);
$sum = $sumStmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'status' => 'success',
    'data' => $data,
    'summary' => [
        'total_tabungan' => $sum['total_tabungan'] ?? 0,
        'total_target' => $sum['total_target'] ?? 0
    ]
]);
