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

// --- Pagination setup ---
$limit = 10; // jumlah data per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Hitung total transaksi
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE celengan_id = ?");
$count_stmt->execute([$celengan_id]);
$total_transaksi = $count_stmt->fetchColumn();
$total_pages = ceil($total_transaksi / $limit);

// Ambil daftar transaksi per halaman
$stmt_transaksi = $pdo->prepare("SELECT * FROM transaksi WHERE celengan_id = ? ORDER BY tanggal ASC LIMIT ? OFFSET ?");
$stmt_transaksi->bindValue(1, $celengan_id, PDO::PARAM_INT);
$stmt_transaksi->bindValue(2, $limit, PDO::PARAM_INT);
$stmt_transaksi->bindValue(3, $offset, PDO::PARAM_INT);
$stmt_transaksi->execute();
$transaksi = $stmt_transaksi->fetchAll(PDO::FETCH_ASSOC);

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
            <br>
            <a href="../data-celengan/edit-celengan.php?id=<?= $celengan['id']; ?>">Edit</a> |
            <a href="../data-celengan/hapus-celengan.php?id=<?= $celengan['id']; ?>" onclick="return confirm('Yakin ingin menghapus celengan ini?')">Hapus</a>

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
                <?php if ($total_pages > 1): ?>
                    <div style="margin-top: 15px; text-align: center;">
                        <?php if ($page > 1): ?>
                            <a href="?id=<?= $celengan_id ?>&page=<?= $page - 1 ?>" style="margin-right: 5px; text-decoration:none; color:#007bff;">&laquo; Sebelumnya</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?id=<?= $celengan_id ?>&page=<?= $i ?>"
                                style="padding:5px 10px; border-radius:5px; text-decoration:none;
                <?= $i == $page ? 'background:#007bff; color:white;' : 'background:#f0f0f0; color:black;' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?id=<?= $celengan_id ?>&page=<?= $page + 1 ?>" style="margin-left: 5px; text-decoration:none; color:#007bff;">Selanjutnya &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </table>
        <?php endif; ?>

        <div id="chartContainer">
            <h3>Grafik Pemasukan dan Pengeluaran</h3>
            <canvas id="chartTransaksi" height="100"></canvas>
        </div>
        <div style="text-align:center; margin-bottom:15px;">
            <button class="filter-btn" data-range="1D">1D</button>
            <button class="filter-btn" data-range="1W">1W</button>
            <button class="filter-btn" data-range="1M">1M</button>
            <button class="filter-btn" data-range="3M">3M</button>
            <button class="filter-btn" data-range="1Y">1Y</button>
            <button class="filter-btn" data-range="ALL">All</button>
            <style>
                .filter-btn {
                    background: #222;
                    color: #fff;
                    border: none;
                    padding: 6px 12px;
                    border-radius: 4px;
                    margin: 0 4px;
                    cursor: pointer;
                    font-size: 13px;
                    transition: 0.2s;
                }

                .filter-btn:hover {
                    background: #007bff;
                }

                .filter-btn.active {
                    background: #007bff;
                    color: white;
                }
            </style>
        </div>
        <script>
            const rawLabels = <?= json_encode($labels); ?>;
            const rawSaldoAwal = <?= json_encode($saldo_awal); ?>;
            const rawSaldoAkhir = <?= json_encode($saldo_akhir); ?>;
            const rawColors = <?= json_encode($colors); ?>;

            const ctx = document.getElementById('chartTransaksi').getContext('2d');
            let chart;

            function filterData(range) {
                const now = new Date();
                const filteredLabels = [];
                const filteredData = [];
                const filteredColors = [];

                for (let i = 0; i < rawLabels.length; i++) {
                    const tgl = new Date(rawLabels[i]);
                    let include = false;

                    switch (range) {
                        case '1D':
                            include = (now - tgl) / (1000 * 60 * 60 * 24) <= 1;
                            break;
                        case '1W':
                            include = (now - tgl) / (1000 * 60 * 60 * 24 * 7) <= 1;
                            break;
                        case '1M':
                            include = (now - tgl) / (1000 * 60 * 60 * 24 * 30) <= 1;
                            break;
                        case '3M':
                            include = (now - tgl) / (1000 * 60 * 60 * 24 * 90) <= 1;
                            break;
                        case '1Y':
                            include = (now - tgl) / (1000 * 60 * 60 * 24 * 365) <= 1;
                            break;
                        case 'ALL':
                            include = true;
                            break;
                    }

                    if (include) {
                        filteredLabels.push(rawLabels[i]);
                        filteredData.push([rawSaldoAwal[i], rawSaldoAkhir[i]]);
                        filteredColors.push(rawColors[i]);
                    }
                }

                chart.data.labels = filteredLabels;
                chart.data.datasets[0].data = filteredData;
                chart.data.datasets[0].backgroundColor = filteredColors;
                chart.update();
            }

            function initChart() {
                chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: rawLabels,
                        datasets: [{
                            label: 'Perubahan Saldo',
                            data: rawSaldoAwal.map((v, i) => [v, rawSaldoAkhir[i]]),
                            backgroundColor: rawColors,
                            borderColor: rawColors,
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
            }

            initChart();

            // Event untuk tombol filter
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    filterData(this.dataset.range);
                });
            });

            // Set default aktif di "ALL"
            document.querySelector('.filter-btn[data-range="ALL"]').classList.add('active');
        </script>
    </div>


</body>

</html>