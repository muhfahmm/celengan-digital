<?php
session_start();

require '../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$id = $_POST['id'] ?? null;
$tanggal = $_POST['tanggal'] ?? null;

if (!$id || !$tanggal) {
    http_response_code(400);
    echo json_encode(['error' => 'ID dan tanggal dibutuhkan.']);
    exit;
}

$parsed = DateTime::createFromFormat('Y-m-d', $tanggal);
if (!$parsed || $parsed->format('Y-m-d') !== $tanggal) {
    http_response_code(400);
    echo json_encode(['error' => 'Format tanggal tidak valid. Gunakan YYYY-MM-DD.']);
    exit;
}

$stmt = $pdo->prepare("SELECT t.*, c.user_id FROM transaksi t JOIN celengan c ON t.celengan_id = c.id WHERE t.id = ?");
$stmt->execute([$id]);
$old = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$old || $old['user_id'] != $_SESSION['user_id']) {
    http_response_code(404);
    echo json_encode(['error' => 'Transaksi tidak ditemukan atau tidak diizinkan.']);
    exit;
}

$update = $pdo->prepare("UPDATE transaksi SET tanggal = ? WHERE id = ?");
$success = $update->execute([$tanggal, $id]);

if ($success) {
    echo json_encode(['success' => true, 'tanggal' => $tanggal]);
    exit;
}

http_response_code(500);
echo json_encode(['error' => 'Gagal menyimpan tanggal.']);
