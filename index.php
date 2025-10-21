<?php
session_start();
include('config/db.php'); // Koneksi PDO

// Jika belum login, arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$status = isset($_GET['status']) && $_GET['status'] == 'Tercapai' ? 'Tercapai' : 'Berlangsung';

// Ambil data username dari database
try {
    $stmt_user = $pdo->prepare("SELECT username FROM users WHERE id = :id");
    $stmt_user->execute(['id' => $user_id]);
    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);
    $username = $user_data ? $user_data['username'] : 'User';
} catch (PDOException $e) {
    $username = 'User';
}

// Ambil data celengan
try {
    $sql = "SELECT * FROM celengan WHERE user_id = :user_id AND status = :status ORDER BY tanggal_dibuat DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id, 'status' => $status]);
    $celengan_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $celengan_list = [];
}

// Fungsi format
function formatRupiah($amount) {
    return 'Rp' . number_format($amount, 0, ',', '.');
}

function calculateProgress($terkumpul, $target) {
    if ($target <= 0) return 0;
    return round(($terkumpul / $target) * 100);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CelenganKu - Beranda</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body class="dark-mode">
    <header>
        <h1>Celengan <?= htmlspecialchars($username) ?></h1>
        <a href="auth/logout.php" style="color: #4CAF50;">Logout</a>
    </header>

    <div class="tabs">
        <a href="index.php?status=Berlangsung" class="<?= $status == 'Berlangsung' ? 'active' : '' ?>">Berlangsung</a>
        <a href="index.php?status=Tercapai" class="<?= $status == 'Tercapai' ? 'active' : '' ?>">Tercapai</a>
    </div>

    <div class="celengan-container">
        <?php if (empty($celengan_list)): ?>
            <p style="text-align: center; color: #aaa; padding: 20px;">Belum ada celengan dengan status <?= htmlspecialchars($status) ?>.</p>
        <?php else: ?>
            <?php foreach ($celengan_list as $celengan): 
                $progress = calculateProgress($celengan['terkumpul'], $celengan['target_tabungan']);
                $sisa_kebutuhan = $celengan['target_tabungan'] - $celengan['terkumpul'];

                $nominal_harian_estimasi = $celengan['nominal_pengisian'];
                if ($celengan['rencana_pengisian'] == 'Mingguan') {
                    $nominal_harian_estimasi = $celengan['nominal_pengisian'] / 7;
                } elseif ($celengan['rencana_pengisian'] == 'Bulanan') {
                    $nominal_harian_estimasi = $celengan['nominal_pengisian'] / 30;
                }

                $sisa_hari = ($nominal_harian_estimasi > 0 && $sisa_kebutuhan > 0)
                    ? ceil($sisa_kebutuhan / $nominal_harian_estimasi)
                    : 'N/A';
            ?>
                <div class="celengan-card" onclick="window.location.href='proses-data/edit-celengan.php?id=<?= $celengan['id'] ?>'">
                    <span class="progress-percent"><?= $progress ?>%</span>
                    <h2><?= htmlspecialchars($celengan['nama_tabungan']) ?></h2>
                    <p class="target-amount"><?= formatRupiah($celengan['target_tabungan']) ?></p>
                    <p class="daily-deposit">
                        <?= formatRupiah($celengan['nominal_pengisian']) ?> Per <?= $celengan['rencana_pengisian'] ?>
                    </p>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: <?= $progress ?>%;"></div>
                    </div>
                    <p class="days-left">
                        <?= $sisa_hari != 'N/A' ? $sisa_hari . ' Hari Lagi' : ($status == 'Tercapai' ? 'Selesai' : 'Tidak terhitung') ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <a href="proses-data/tambah-celengan.php" class="fab-button">+ Tambah Celengan</a>
</body>
</html>
