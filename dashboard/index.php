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
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }

        a {
            text-decoration: none;
            color: #007bff;
        }

        a:hover {
            text-decoration: underline;
        }

        .container {
            width: 90%;
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h3 {
            margin-bottom: 15px;
            color: #222;
        }

        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 20px;
            transition: background 0.3s;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .celengan-card {
            margin-bottom: 25px;
            padding: 20px;
            border-radius: 10px;
            background-color: #fafafa;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .celengan-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        }

        .celengan-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th {
            background-color: #007bff;
            color: white;
            text-align: left;
            padding: 10px;
            font-size: 14px;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }

        .progress-bar {
            background-color: #eee;
            border-radius: 10px;
            overflow: hidden;
            height: 10px;
            width: 100%;
            margin-top: 5px;
        }

        .progress {
            background: linear-gradient(90deg, #4CAF50, #81C784);
            height: 10px;
            transition: width 0.4s ease;
        }

        .empty-message {
            text-align: center;
            margin-top: 40px;
            color: #666;
            font-size: 16px;
        }

        /* Tombol aksi */
        td a {
            color: #007bff;
            font-weight: 500;
            transition: color 0.3s;
        }

        td a:hover {
            color: #0056b3;
        }

        /* Responsif */
        @media (max-width: 600px) {
            .container {
                width: 95%;
                padding: 15px;
            }

            table,
            th,
            td {
                font-size: 12px;
            }

            .celengan-title {
                font-size: 16px;
            }

            .btn {
                font-size: 12px;
                padding: 8px 10px;
            }
        }
    </style>

</head>

<body>
    <h2>Selamat datang, <?php echo $_SESSION['username']; ?></h2>
    <a href="../auth/logout.php">Logout</a>

    <div class="container">
        <h3>Daftar Celengan</h3>
        <a href="../data-celengan/tambah-celengan.php" class="btn">+ Buat Celengan baru</a>

        <?php if (count($celengan) > 0): ?>
            <?php
            $no = 1;
            foreach ($celengan as $c):
                $progress = $c['target'] > 0 ? round(($c['total'] / $c['target']) * 100) : 0;
            ?>
                <div class="celengan-card">
                    <a href="detail-celengan.php?id=<?= $c['id']; ?>">
                        <div class="celengan-title">
                            <?= $no++ . '. ' . htmlspecialchars($c['nama_celengan']); ?>
                        </div>
                    </a>

                    <table>
                        <tr>
                            <th>Total tabungan</th>
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
                                <a href="../data-celengan/hapus-celengan.php?id=<?php echo $c['id']; ?>" onclick="return confirm('Yakin ingin menghapus celengan ini?')">Hapus</a>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-message">
                Belum ada celengan yang dibuat.<br>
                <a href="../data-celengan/tambah-celengan.php">Buat celengan</a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>