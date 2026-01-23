<?php
include('../../config/db.php');

$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$result = $stmt->execute([$username, $password]);

// Check if request expects JSON
if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Registrasi berhasil']);
    } else {
        echo json_encode(['status' => 'failed', 'message' => 'Registrasi gagal']);
    }
    exit;
}

header("Location: ../login.php?status=success");
exit;
