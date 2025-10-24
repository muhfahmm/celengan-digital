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
$stmt_transaksi = $pdo->prepare("SELECT * FROM transaksi WHERE celengan_id = ? ORDER BY tanggal ASC");
$stmt_transaksi->execute([$celengan_id]);
$transaksi = $stmt_transaksi->fetchAll(PDO::FETCH_ASSOC);

// Hitung progress dan kekurangan
$progress = $celengan['target'] > 0 ? round(($celengan['total'] / $celengan['target']) * 100) : 0;
$kekurangan = $celengan['target'] - $celengan['total'];
if ($kekurangan < 0) $kekurangan = 0;

// Format rupiah
function rupiah($angka)
{
    return 'Rp' . number_format($angka, 0, ',', '.');
}

// Siapkan data untuk chart
$labels = [];
$data = [];
$colors = [];

$total = 0;
$labels = [];
$saldo_awal = [];
$saldo_akhir = [];
$colors = [];

foreach ($transaksi as $t) {
    $labels[] = $t['tanggal'];
    $saldo_awal[] = $total; // posisi sebelum transaksi

    $nominal = (float)$t['nominal'];
    if (strtolower($t['tipe']) == 'masuk') {
        $total += $nominal;
        $colors[] = 'rgba(0, 200, 83, 0.8)'; // hijau naik
    } else {
        $total -= $nominal;
        $colors[] = 'rgba(244, 67, 54, 0.8)'; // merah turun
    }

    $saldo_akhir[] = $total; // posisi setelah transaksi
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Celengan - <?= htmlspecialchars($celengan['nama_celengan']); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .detail-card {
            background: #fafafa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .progress-bar {
            background: #ddd;
            border-radius: 10px;
            overflow: hidden;
            height: 10px;
            margin-top: 5px;
        }

        .progress {
            background: linear-gradient(90deg, #4CAF50, #81C784);
            height: 10px;
            transition: width 0.4s ease;
        }

        .info-text {
            margin-top: 8px;
            font-size: 15px;
            color: #444;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border-bottom: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #007bff;
            color: white;
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

        .btn-edit,
        .btn-hapus {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 13px;
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
            color: #333;
        }

        h3 {
            margin-top: 30px;
            color: #333;
        }

        #chartContainer {
            margin-top: 40px;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="index.php" class="btn-back">← Kembali</a>

        <div class="detail-card">
            <h2><?= htmlspecialchars($celengan['nama_celengan']); ?></h2>

            <a href="../transaksi/tambah-transaksi.php?celengan_id=<?= $celengan['id']; ?>">Tambah Progress</a> |
            <a href="../transaksi/kurangi-transaksi.php?celengan_id=<?= $celengan['id']; ?>">Kurangi Progress</a>

            <p><b>Total:</b> <?= rupiah($celengan['total']); ?></p>
            <p><b>Target:</b> <?= rupiah($celengan['target']); ?></p>
            <p><b>Progress:</b> <?= $progress; ?>%</p>

            <div class="progress-bar">
                <div class="progress" style="width: <?= $progress; ?>%;"></div>
            </div>

            <p class="info-text"><b>Kekurangan:</b> <?= rupiah($kekurangan); ?></p>
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

        <div id="chartContainer">
            <h3>Grafik Pemasukan dan Pengeluaran</h3>
            <canvas id="chartTransaksi" height="100"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('chartTransaksi').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels); ?>,
                datasets: [{
                    label: 'Perubahan Saldo',
                    data: <?= json_encode(array_map(null, $saldo_awal, $saldo_akhir)); ?>,
                    backgroundColor: <?= json_encode($colors); ?>,
                    borderColor: <?= json_encode($colors); ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            display: false
                        },
                        border: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp' + value.toLocaleString('id-ID');
                            }
                        },
                        grid: {
                            color: 'rgba(220,220,220,0.3)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const start = context.raw[0];
                                const end = context.raw[1];
                                const diff = end - start;
                                return [
                                    'Sebelum: Rp' + start.toLocaleString('id-ID'),
                                    'Sesudah: Rp' + end.toLocaleString('id-ID'),
                                    (diff >= 0 ? 'Naik: +' : 'Turun: ') + 'Rp' + Math.abs(diff).toLocaleString('id-ID')
                                ];
                            }
                        }
                    }
                }
            }
        });
    </script>

</body>

</html>