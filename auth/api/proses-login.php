<?php
session_start();
require '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$username_email = trim($_POST['username_email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username_email) || empty($password)) {
    header('Location: ../login.php?error=empty');
    exit;
}

try {
    // Ambil data user berdasarkan username atau email
    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username_email, $username_email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Login berhasil
        $_SESSION['user_id'] = $user['id'];
        header('Location: ../../index.php');
        exit;
    } else {
        // Login gagal
        header('Location: ../login.php?error=invalid');
        exit;
    }

} catch (PDOException $e) {
    // error_log($e->getMessage());
    header('Location: ../login.php?error=db_fail');
    exit;
}
?>