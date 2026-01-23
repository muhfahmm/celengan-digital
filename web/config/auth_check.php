<?php
// File untuk memeriksa autentikasi user
// Include file ini di setiap halaman yang memerlukan login

// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    // Hapus semua session data
    $_SESSION = array();
    
    // Hapus cookie session jika ada
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    
    // Hancurkan session
    session_destroy();
    
    // Redirect ke login
    header("Location: " . getLoginPath());
    exit;
}

// Fungsi untuk mendapatkan path ke login.php
function getLoginPath() {
    // Dapatkan path file saat ini
    $currentFile = $_SERVER['SCRIPT_FILENAME'];
    $documentRoot = $_SERVER['DOCUMENT_ROOT'];
    
    // Dapatkan path relatif dari document root
    $relativePath = str_replace($documentRoot, '', $currentFile);
    $relativePath = str_replace('\\', '/', $relativePath);
    
    // Hitung jumlah direktori dari file saat ini ke root
    $pathParts = explode('/', trim($relativePath, '/'));
    $depth = count($pathParts) - 1; // -1 karena file itu sendiri bukan direktori
    
    // Buat path relatif ke auth/login.php
    if ($depth > 0) {
        $prefix = str_repeat('../', $depth);
        return $prefix . 'auth/login.php';
    } else {
        return 'auth/login.php';
    }
}
?>
