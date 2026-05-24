<?php
require_once('../config/auth_check.php');
include('../config/db.php');

if (!isset($_GET['id'])) {
    die('ID celengan tidak ditemukan');
}

$celengan_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Pastikan pemilik celengan
$stmt = $pdo->prepare("SELECT * FROM celengan WHERE id = ? AND user_id = ?");
$stmt->execute([$celengan_id, $user_id]);
$celengan = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$celengan) {
    die('Celengan tidak ditemukan atau bukan milik Anda');
}

// Ambil transaksi untuk bulan yang sama (atau bulan terakhir jika tidak ada di bulan sekarang)
$historyMonth = (int) date('n');
$historyYear = (int) date('Y');

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE celengan_id = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?");
$count_stmt->execute([$celengan_id, $historyMonth, $historyYear]);
$total_transaksi = $count_stmt->fetchColumn();

if ($total_transaksi == 0) {
    $latest_stmt = $pdo->prepare("SELECT tanggal FROM transaksi WHERE celengan_id = ? ORDER BY tanggal DESC LIMIT 1");
    $latest_stmt->execute([$celengan_id]);
    $latest_transaksi = $latest_stmt->fetch(PDO::FETCH_ASSOC);
    if ($latest_transaksi && !empty($latest_transaksi['tanggal'])) {
        $latestDate = strtotime($latest_transaksi['tanggal']);
        $historyMonth = (int) date('n', $latestDate);
        $historyYear = (int) date('Y', $latestDate);
    }
}

$stmt = $pdo->prepare("SELECT * FROM transaksi WHERE celengan_id = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ? ORDER BY tanggal ASC");
$stmt->execute([$celengan_id, $historyMonth, $historyYear]);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

function rupiah($angka) { return 'Rp' . number_format($angka, 0, ',', '.'); }

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Riwayat Transaksi - <?= htmlspecialchars($celengan['nama_celengan']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root{--bg:#0f1724;--card:#111827;--muted:#9CA3AF;--white:#FFFFFF}
        body{font-family:Inter, sans-serif;background:linear-gradient(135deg,#0b1220 0%, #0b1628 100%);color:var(--white);margin:0;padding:20px}
        .top-nav{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}
        .btn-back{color:var(--white);text-decoration:none;font-weight:600}
        .top-actions button{background:transparent;border:none;color:var(--white);font-size:20px;padding:8px;border-radius:50%;cursor:pointer}
        .card{background:rgba(255,255,255,0.03);padding:18px;border-radius:12px;border:1px solid rgba(255,255,255,0.04)}
        .table{width:100%;border-collapse:collapse;margin-top:12px}
        th,td{padding:12px;border-bottom:1px solid rgba(255,255,255,0.03);text-align:left}
        th{font-size:13px;color:var(--muted);font-weight:600}
        td{font-size:14px}
        .badge-in{color:#10B981;font-weight:700}
        .badge-out{color:#EF4444;font-weight:700}
        .empty{color:var(--muted);padding:30px;text-align:center}
    </style>
</head>
<body>
    <div class="top-nav">
        <a href="index.php" class="btn-back">&larr; Kembali ke Dashboard</a>
        <div class="top-actions">
            <button id="themeToggle" title="Ganti Tema"><i class="bi bi-sun"></i></button>
        </div>
    </div>

    <div class="card">
        <h2 style="margin:0 0 6px 0;">Riwayat Transaksi - <?= htmlspecialchars($celengan['nama_celengan']); ?></h2>
        <div style="color:var(--muted);margin-bottom:12px;">Menampilkan: <?= sprintf('%02d/%04d', $historyMonth, $historyYear); ?></div>

        <?php if (empty($transaksi)): ?>
            <div class="empty">Belum ada transaksi pada periode ini.</div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr><th>Tanggal</th><th>Nominal</th><th>Tipe</th><th>Keterangan</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($transaksi as $t): ?>
                        <tr>
                            <td><?= date('d/m/y', strtotime($t['tanggal'])); ?></td>
                            <td style="font-family:monospace"><?= rupiah($t['nominal']); ?></td>
                            <td><?= strtolower($t['tipe']) == 'masuk' ? '<span class="badge-in">Masuk</span>' : '<span class="badge-out">Keluar</span>'; ?></td>
                            <td style="color:var(--muted)"><?= htmlspecialchars($t['keterangan']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
    const themeBtn = document.getElementById('themeToggle');
    themeBtn && themeBtn.addEventListener('click', ()=>{
        document.body.classList.toggle('dark');
    });
    </script>
</body>
</html>
