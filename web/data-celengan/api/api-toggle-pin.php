<?php
require_once('../../config/auth_check.php');
require_once('../../config/db.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$celengan_id = $_POST['celengan_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$celengan_id) {
    echo json_encode(['success' => false, 'message' => 'ID celengan tidak ditemukan']);
    exit;
}

try {
    // Cek apakah celengan milik user
    $stmt = $pdo->prepare("SELECT is_pinned FROM celengan WHERE id = ? AND user_id = ?");
    $stmt->execute([$celengan_id, $user_id]);
    $celengan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$celengan) {
        echo json_encode(['success' => false, 'message' => 'Celengan tidak ditemukan']);
        exit;
    }
    
    $current_pinned = $celengan['is_pinned'];
    
    // Jika ingin pin (dari unpinned ke pinned)
    if ($current_pinned == 0) {
        // Cek jumlah celengan yang sudah di-pin
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM celengan WHERE user_id = ? AND is_pinned = 1");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] >= 3) {
            echo json_encode([
                'success' => false, 
                'message' => 'Maksimal 3 celengan yang dapat disematkan'
            ]);
            exit;
        }
        
        // Pin celengan
        $stmt = $pdo->prepare("UPDATE celengan SET is_pinned = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$celengan_id, $user_id]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Celengan berhasil disematkan',
            'is_pinned' => 1
        ]);
    } else {
        // Unpin celengan
        $stmt = $pdo->prepare("UPDATE celengan SET is_pinned = 0 WHERE id = ? AND user_id = ?");
        $stmt->execute([$celengan_id, $user_id]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Celengan berhasil dilepas dari sematan',
            'is_pinned' => 0
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
?>
