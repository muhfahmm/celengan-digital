<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include('../config/db.php');

if (!isset($_GET['id'])) {
    die("ID celengan tidak ditemukan");
}

$celengan_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Ambil detail celengan
$stmt = $pdo->prepare("SELECT * FROM celengan WHERE id = ? AND user_id = ?");
$stmt->execute([$celengan_id, $user_id]);
$celengan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$celengan) {
    die("Data celengan tidak ditemukan");
}

// Ambil daftar transaksi
$stmt_transaksi = $pdo->prepare("SELECT * FROM transaksi WHERE celengan_id = ? ORDER BY tanggal DESC");
$stmt_transaksi->execute([$celengan_id]);
$transaksi = $stmt_transaksi->fetchAll(PDO::FETCH_ASSOC);

// Hitung progress
$progress = $celengan['target'] > 0 ? round(($celengan['total'] / $celengan['target']) * 100) : 0;

// Format rupiah
function rupiah($angka) {
    return 'Rp' . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Celengan - <?= htmlspecialchars($celengan['nama_celengan']); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .container {
            width: 80%;
            margin: 30px auto;
        }

        .detail-card {
            background: #f8f8f8;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .progress-bar {
            background: #ddd;
            border-radius: 10px;
            overflow: hidden;
            height: 10px;
            margin-top: 5px;
        }

        .progress {
            background: #4CAF50;
            height: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border-bottom: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #eee;
        }

        .btn-back {
            display: inline-block;
            padding: 8px 15px;
            background: #4CAF50;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            margin-bottom: 20px;
        }

        .btn-back:hover {
            background: #45a049;
        }

        .btn-edit, .btn-hapus {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
        }

        .btn-edit {
            background-color: #2196F3;
        }

        .btn-hapus {
            background-color: #f44336;
        }

        .btn-edit:hover {
            background-color: #1976D2;
        }

        .btn-hapus:hover {
            background-color: #d32f2f;
        }

        h2 {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn-back">← Kembali</a>

        <div class="detail-card">
            <h2><?= htmlspecialchars($celengan['nama_celengan']); ?></h2>
            <p><b>Total:</b> <?= rupiah($celengan['total']); ?></p>
            <p><b>Target:</b> <?= rupiah($celengan['target']); ?></p>
            <p><b>Progress:</b> <?= $progress; ?>%</p>
            <div class="progress-bar">
                <div class="progress" style="width: <?= $progress; ?>%;"></div>
            </div>
        </div>

        <h3>Riwayat Transaksi</h3>
        <?php if (empty($transaksi)): ?>
            <p>Belum ada transaksi pada celengan ini.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nominal</th>
                    <th>Jenis</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
                <?php 
                $no = 1;
                foreach ($transaksi as $t): 
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($t['tanggal']); ?></td>
                    <td><?= rupiah($t['nominal']); ?></td>
                    <td style="color: <?= strtolower($t['tipe']) == 'masuk' ? 'green' : 'red'; ?>;">
                        <?= htmlspecialchars($t['tipe']); ?>
                    </td>
                    <td><?= htmlspecialchars($t['keterangan']); ?></td>
                    <td>
                        <a href="../transaksi/edit-transaksi.php?id=<?= $t['id']; ?>&celengan_id=<?= $celengan_id; ?>" class="btn-edit">Edit</a>
                        <a href="../transaksi/hapus-transaksi.php?id=<?= $t['id']; ?>&celengan_id=<?= $celengan_id; ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus transaksi ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
