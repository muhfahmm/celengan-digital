<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include('../config/db.php');

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM celengan WHERE user_id = ?");
$stmt->execute([$user_id]);
$celengan = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Dashboard Celengan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/main.js" defer></script>
    <style>
        .celengan-card {
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
        }

        .celengan-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .progress-bar {
            background-color: #ddd;
            border-radius: 10px;
            overflow: hidden;
            height: 10px;
        }

        .progress {
            background-color: #4CAF50;
            height: 10px;
        }
    </style>
</head>

<body>
    <h2>Selamat datang, <?php echo $_SESSION['username']; ?></h2>
    <a href="../auth/logout.php">Logout</a>

    <div class="container">
        <h3>Daftar Celengan</h3>
        <a href="../data-celengan/tambah-celengan.php" class="btn">+ Tambah Celengan</a>

        <?php
        $no = 1;
        foreach ($celengan as $c):
            $progress = $c['target'] > 0 ? round(($c['total'] / $c['target']) * 100) : 0;
        ?>
            <div class="celengan-card">
                <div class="celengan-title"><?php echo $no++ . '. ' . htmlspecialchars($c['nama_celengan']); ?></div>

                <table>
                    <tr>
                        <th>Total</th>
                        <th>Target</th>
                        <th>Progress</th>
                        <th>Aksi</th>
                    </tr>
                    <tr>
                        <td>Rp<?php echo number_format($c['total'], 0, ',', '.'); ?></td>
                        <td>Rp<?php echo number_format($c['target'], 0, ',', '.'); ?></td>
                        <td>
                            <?php echo $progress . '%'; ?>
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo $progress; ?>%;"></div>
                            </div>
                        </td>
                        <td>
                            <a href="../data-celengan/edit-celengan.php?id=<?php echo $c['id']; ?>">Edit</a> |
                            <a href="../data-celengan/hapus-celengan.php?id=<?php echo $c['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a> |
                            <a href="../transaksi/tambah-transaksi.php?celengan_id=<?php echo $c['id']; ?>">Tambah Progress</a>
                        </td>
                    </tr>
                </table>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>