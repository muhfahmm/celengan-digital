<?php
require '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($email) || empty($password)) {
    header('Location: ../register.php?error=empty');
    exit;
}

// Hashing password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // Cek apakah username atau email sudah ada
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetchColumn() > 0) {
        header('Location: ../register.php?error=exists');
        exit;
    }

    // Insert user baru
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hashed_password]);

    // Redirect ke halaman login setelah berhasil
    header('Location: ../login.php?success=registered');
    exit;

} catch (PDOException $e) {
    // Log error (sebaiknya jangan tampilkan error DB ke user)
    // error_log($e->getMessage()); 
    header('Location: ../register.php?error=db_fail');
    exit;
}
?>