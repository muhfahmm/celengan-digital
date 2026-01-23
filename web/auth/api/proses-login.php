<?php
session_start();
include('../../config/db.php');

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    
    // Check if request expects JSON
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        echo json_encode(['status' => 'success', 'user' => ['id' => $user['id'], 'username' => $user['username']]]);
        exit;
    }
    
    header("Location: ../../dashboard/index.php");
} else {
    // Check if request expects JSON
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        echo json_encode(['status' => 'failed', 'message' => 'Username atau password salah!']);
        exit;
    }
    
    header("Location: ../login.php?status=failed");
}
exit;
