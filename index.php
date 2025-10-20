<?php
session_start();
include('config/db_connect.php');

// Pengecekan sesi user, arahkan ke login jika belum
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$status = isset($_GET['status']) && $_GET['status'] == 'Tercapai' ? 'Tercapai' : 'Berlangsung';

// Ambil data celengan dari database
$sql = "SELECT * FROM celengan WHERE user_id = ? AND status = ? ORDER BY tanggal_dibuat DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $status);
$stmt->execute();
$result = $stmt->get_result();
$celengan_list = $result->fetch_all(MYSQLI_ASSOC);

function formatRupiah($amount) {
    return 'Rp' . number_format($amount, 0, ',', '.');
}

function calculateProgress($terkumpul, $target) {
    if ($target == 0) return 0;
    return round(($terkumpul / $target) * 100);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>CelenganKu</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dark-mode">
    <header>
        <h1>CelenganKu</h1>
        <a href="auth/logout.php">Logout</a>
    </header>

    <div class="tabs">
        <a href="index.php?status=Berlangsung" class="<?= $status == 'Berlangsung' ? 'active' : '' ?>">Berlangsung</a>
        <a href="index.php?status=Tercapai" class="<?= $status == 'Tercapai' ? 'active' : '' ?>">Tercapai</a>
    </div>

    <div class="celengan-container">
        <?php if (empty($celengan_list)): ?>
            <p style="text-align: center; color: #aaa;">Belum ada celengan dengan status <?= $status ?>.</p>
        <?php else: ?>
            <?php foreach ($celengan_list as $celengan): 
                $progress = calculateProgress($celengan['terkumpul'], $celengan['target_tabungan']);
                // Logika sederhana untuk menghitung sisa hari
                $sisa_kebutuhan = $celengan['target_tabungan'] - $celengan['terkumpul'];
                $sisa_hari = $celengan['nominal_pengisian'] > 0 ? ceil($sisa_kebutuhan / $celengan['nominal_pengisian']) : 'N/A';
            ?>
                <div class="celengan-card" onclick="window.location.href='proses-data/edit-celengan.php?id=<?= $celengan['id'] ?>'">
                    <h2><?= htmlspecialchars($celengan['nama_tabungan']) ?></h2>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: <?= $progress ?>%;"></div>
                    </div>
                    <p class="target-amount"><?= formatRupiah($celengan['target_tabungan']) ?></p>
                    <p class="daily-deposit">
                        <?= formatRupiah($celengan['nominal_pengisian']) ?> Per <?= $celengan['rencana_pengisian'] ?>
                    </p>
                    <p class="days-left"><?= $sisa_hari != 'N/A' ? $sisa_hari . ' Hari Lagi' : 'Target tidak dapat dihitung' ?></p>
                    <span class="progress-percent"><?= $progress ?>%</span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <a href="proses-data/tambah-celengan.php" class="fab-button">+ Tambah Celengan</a>

</body>
</html>
<?php $conn->close(); ?>