<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$celengan_id = $_GET['id'] ?? null;
if (!$celengan_id) {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Ambil data celengan
    $stmt = $pdo->prepare("SELECT * FROM celengan WHERE id = ? AND user_id = ?");
    $stmt->execute([$celengan_id, $user_id]);
    $celengan = $stmt->fetch();

    if (!$celengan) {
        header('Location: ../index.php?error=not_found');
        exit;
    }

    // Hitung persentase dan kekurangan
    $progress = ($celengan['target_tabungan'] > 0) ? round(($celengan['terkumpul'] / $celengan['target_tabungan']) * 100) : 0;
    $kekurangan = $celengan['target_tabungan'] - $celengan['terkumpul'];

    // Ambil tanggal terakhir transaksi (jika ada)
    $stmt_trans = $pdo->prepare("SELECT tanggal_transaksi FROM transaksi_pengisian WHERE celengan_id = ? ORDER BY tanggal_transaksi DESC LIMIT 1");
    $stmt_trans->execute([$celengan_id]);
    $last_transaction = $stmt_trans->fetchColumn();

    // Fungsi format Rupiah
    function formatRupiah($amount) {
        return 'Rp' . number_format($amount, 0, ',', '.');
    }

} catch (PDOException $e) {
    // error_log($e->getMessage());
    header('Location: ../index.php?error=db_fail');
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Celengan: <?= htmlspecialchars($celengan['nama_tabungan']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dark-mode">
    <header>
        <a href="../index.php" class="back-link">←</a>
        <h2><?= htmlspecialchars($celengan['nama_tabungan']) ?></h2>
        <div class="header-actions">
            <a href="#" class="icon-button edit-celengan-btn">✏️</a>
            <a href="api/api-hapus-celengan.php?id=<?= $celengan_id ?>" class="icon-button delete-celengan-btn" onclick="return confirm('Yakin ingin menghapus celengan ini?')">🗑️</a>
        </div>
    </header>

    <main class="detail-container">
        <div class="card detail-header-card">
            <h3><?= formatRupiah($celengan['target_tabungan']) ?></h3>
            <p class="daily-deposit"><?= formatRupiah($celengan['nominal_pengisian']) ?> Per <?= $celengan['rencana_pengisian'] ?></p>
            <div class="progress-info">
                <span class="progress-percent"><?= $progress ?>%</span>
                <div class="progress-bar-container"><div class="progress-bar" style="width: <?= $progress ?>%;"></div></div>
            </div>
            <div class="meta-info">
                <div>Tanggal Dibuat: <?= date('d M Y', strtotime($celengan['tanggal_dibuat'])) ?></div>
                <div>Estimasi: <?= date('d M Y', strtotime($celengan['tanggal_estimasi'])) ?> (<?= $celengan['tanggal_estimasi'] ? round((strtotime($celengan['tanggal_estimasi']) - time()) / (60 * 60 * 24)) : 'N/A' ?> Hari Lagi)</div>
            </div>
        </div>

        <div class="card detail-summary-card">
            <div class="summary-item">
                <p>Terkumpul</p>
                <h3 style="color: #4CAF50;"><?= formatRupiah($celengan['terkumpul']) ?></h3>
            </div>
            <div class="summary-item">
                <p>Kekurangan</p>
                <h3 style="color: #F44336;"><?= formatRupiah($kekurangan) ?></h3>
            </div>
            <p class="last-update">Update Terakhir: <?= $last_transaction ? date('d M Y • H:i', strtotime($last_transaction)) : 'Belum ada pengisian' ?></p>
        </div>

        <div class="card add-deposit-card">
            <h4>Tambah Pengisian</h4>
            <form action="api/api-edit-celengan.php" method="POST">
                <input type="hidden" name="celengan_id" value="<?= $celengan_id ?>">
                <label for="jumlah_pengisian">Nominal Pengisian</label>
                <input type="number" id="jumlah_pengisian" name="jumlah_pengisian" min="1000" required>
                <button type="submit" name="action" value="tambah_pengisian" class="save-button">Tambah Dana</button>
            </form>
        </div>
    </main>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h4>Edit Rencana Celengan</h4>
             <form id="formEditCelengan" action="api/api-edit-celengan.php" method="POST">
                <input type="hidden" name="celengan_id" value="<?= $celengan_id ?>">
                <input type="hidden" name="action" value="edit_rencana">
                
                <label for="edit_nama">Nama Tabungan</label>
                <input type="text" id="edit_nama" name="nama_tabungan" value="<?= htmlspecialchars($celengan['nama_tabungan']) ?>" required>
                
                <label for="edit_target">Target Tabungan</label>
                <input type="number" id="edit_target" name="target_tabungan" value="<?= $celengan['target_tabungan'] ?>" min="1000" required>
                
                <label for="edit_nominal">Nominal Pengisian</label>
                <input type="number" id="edit_nominal" name="nominal_pengisian" value="<?= $celengan['nominal_pengisian'] ?>" min="1000" required>
                
                <button type="submit" class="save-button">Simpan Perubahan</button>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>