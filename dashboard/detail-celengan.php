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

$stmt = $pdo->prepare("SELECT * FROM celengan WHERE id = ? AND user_id = ?");
$stmt->execute([$celengan_id, $user_id]);
$celengan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$celengan) {
    die("Data celengan tidak ditemukan");
}

$stmt_transaksi = $pdo->prepare("SELECT * FROM transaksi WHERE celengan_id = ? ORDER BY tanggal ASC");
$stmt_transaksi->execute([$celengan_id]);
$transaksi = $stmt_transaksi->fetchAll(PDO::FETCH_ASSOC);

$progress = $celengan['target'] > 0 ? round(($celengan['total'] / $celengan['target']) * 100) : 0;
$kekurangan = $celengan['target'] - $celengan['total'];
if ($kekurangan < 0) $kekurangan = 0;

function rupiah($angka)
{
    return 'Rp' . number_format($angka, 0, ',', '.');
}

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
    $saldo_awal[] = $total;

    $nominal = (float)$t['nominal'];
    if (strtolower($t['tipe']) == 'masuk') {
        $total += $nominal;
        $colors[] = '#41A67E';
    } else {
        $total -= $nominal;
        $colors[] = '#BF1A1A';
    }

    $saldo_akhir[] = $total;
}

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE celengan_id = ?");
$count_stmt->execute([$celengan_id]);
$total_transaksi = $count_stmt->fetchColumn();
$total_pages = ceil($total_transaksi / $limit);

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
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
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .detail-card {
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
        <div style="display: flex; justify-content:space-between;">
            <a href="index.php" class="btn-back">← Kembali</a>
            <div id="darkToggle" style="cursor: pointer; font-size: 22px;">
                <i id="themeIcon" class="bi bi-moon" style="font-size: 25px;"></i>
                <style>
                    body {
                        color: #333;
                        background-color: #f0f2f5;
                    }

                    body.dark {
                        background: #1e1e1e;
                        color: #e0e0e0;
                    }

                    body.dark h2,
                    body.dark h3,
                    body.dark p,
                    body.dark .info-text,
                    body.dark th,
                    body.dark td,
                    body.dark .detail-card,
                    body.dark table {
                        color: #e0e0e0;
                    }

                    body.dark th {
                        background: #333;
                        color: #fff;
                    }

                    body.dark td,
                    body.dark th {
                        border-color: #444;
                    }

                    body.dark .btn-edit,
                    body.dark .btn-hapus,
                    body.dark .btn-back {
                        filter: brightness(1.2);
                    }

                    body.dark .info-text {
                        color: #ccc;
                    }
                </style>

                <script>
                    const body = document.body;
                    const toggleBtn = document.getElementById("darkToggle");
                    const themeIcon = document.getElementById("themeIcon");

                    // === 1. CEK LOCALSTORAGE SAAT HALAMAN DIBUKA ===
                    const savedTheme = localStorage.getItem("theme");

                    if (savedTheme === "dark") {
                        body.classList.add("dark");
                        themeIcon.classList.replace("bi-moon", "bi-brightness-high");
                    }

                    // === 2. KLIK TOGGLE ===
                    toggleBtn.onclick = function() {
                        body.classList.toggle("dark");

                        const isDark = body.classList.contains("dark");

                        // Ubah ikon
                        if (isDark) {
                            themeIcon.classList.replace("bi-moon", "bi-brightness-high");
                            localStorage.setItem("theme", "dark");
                        } else {
                            themeIcon.classList.replace("bi-brightness-high", "bi-moon");
                            localStorage.setItem("theme", "light");
                        }
                    };
                </script>
            </div>
        </div>

        <div class="detail-card">
            <h2><?= htmlspecialchars($celengan['nama_celengan']); ?></h2>

            <div class="action-buttons">
                <div class="button-group">
                    <a href="../transaksi/tambah-transaksi.php?celengan_id=<?= $celengan['id']; ?>" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Tambah Progress
                    </a>
                    <a href="../transaksi/kurangi-transaksi.php?celengan_id=<?= $celengan['id']; ?>" class="btn btn-warning">
                        <i class="bi bi-dash-circle"></i> Kurangi Progress
                    </a>
                </div>

                <div class="button-group">
                    <a href="../data-celengan/edit-celengan.php?id=<?= $celengan['id']; ?>" class="btn btn-edit">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="../data-celengan/hapus-celengan.php?id=<?= $celengan['id']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus celengan ini?')">
                        <i class="bi bi-trash"></i> Hapus
                    </a>
                </div>
            </div>

            <style>
                .action-buttons {
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                    margin: 20px 0;
                }

                .button-group {
                    display: flex;
                    gap: 8px;
                    flex-wrap: wrap;
                }

                .btn {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    padding: 10px 16px;
                    border: none;
                    border-radius: 8px;
                    text-decoration: none;
                    font-size: 14px;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    cursor: pointer;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                }

                .btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
                    text-decoration: none;
                }

                .btn:active {
                    transform: translateY(0);
                }

                .btn-success {
                    background: linear-gradient(135deg, #28a745, #20c997);
                    color: white;
                }

                .btn-success:hover {
                    background: linear-gradient(135deg, #218838, #1e9e8a);
                    color: white;
                }

                .btn-warning {
                    background: linear-gradient(135deg, #ffc107, #fd7e14);
                    color: white;
                }

                .btn-warning:hover {
                    background: linear-gradient(135deg, #e0a800, #e55a08);
                    color: white;
                }

                .btn-danger {
                    background: linear-gradient(135deg, #dc3545, #e83e8c);
                    color: white;
                }

                .btn-danger:hover {
                    background: linear-gradient(135deg, #c82333, #d91a7a);
                    color: white;
                }

                @media (max-width: 768px) {
                    .action-buttons {
                        gap: 8px;
                    }

                    .button-group {
                        flex-direction: column;
                    }

                    .btn {
                        justify-content: center;
                        padding: 12px 16px;
                    }
                }

                .bi {
                    font-size: 16px;
                }

                body.dark .btn {
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
                }

                body.dark .btn:hover {
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
                    filter: brightness(1.1);
                }

                body.dark .btn-success {
                    background: linear-gradient(135deg, #2ecc71, #1abc9c);
                }

                body.dark .btn-success:hover {
                    background: linear-gradient(135deg, #27ae60, #16a085);
                }

                body.dark .btn-warning {
                    background: linear-gradient(135deg, #f39c12, #e67e22);
                }

                body.dark .btn-warning:hover {
                    background: linear-gradient(135deg, #d35400, #d35400);
                }

                body.dark .btn-danger {
                    background: linear-gradient(135deg, #e74c3c, #e84393);
                }

                body.dark .btn-danger:hover {
                    background: linear-gradient(135deg, #c0392b, #fd79a8);
                }
            </style>

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
                        <td class="jenis-transaksi <?= strtolower($t['tipe']) == 'masuk' ? 'masuk' : 'keluar'; ?>">
                            <?= htmlspecialchars($t['tipe']); ?>
                        </td>
                        <style>
                            .jenis-transaksi.masuk {
                                color: #4CAF50;
                            }

                            .jenis-transaksi.keluar {
                                color: #f44336;
                            }

                            body.dark .jenis-transaksi.masuk {
                                color: #81C784;
                            }

                            body.dark .jenis-transaksi.keluar {
                                color: #EF9A9A;
                            }
                        </style>
                        <td><?= htmlspecialchars($t['keterangan']); ?></td>
                        <td>
                            <a href="../transaksi/edit-transaksi.php?id=<?= $t['id']; ?>&celengan_id=<?= $celengan_id; ?>" class="btn-edit">Edit</a>
                            <a href="../transaksi/hapus-transaksi.php?id=<?= $t['id']; ?>&celengan_id=<?= $celengan_id; ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus transaksi ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($total_pages > 1): ?>
                    <div style="margin-top: 15px; text-align: center;">

                        <?php
                        $max_buttons = 5;

                        $start = max(1, $page - 2);
                        $end   = min($total_pages, $start + $max_buttons - 1);

                        if ($end - $start < $max_buttons - 1) {
                            $start = max(1, $end - $max_buttons + 1);
                        }

                        if ($page > 1): ?>
                            <a href="?id=<?= $celengan_id ?>&page=<?= $page - 1 ?>" style="margin-right: 5px; text-decoration:none; color:#007bff;">&laquo;</a>
                        <?php endif; ?>

                        <?php if ($start > 1): ?>
                            <a href="?id=<?= $celengan_id ?>&page=1" style="padding:5px 10px; border-radius:5px; background:#f0f0f0; color:black; text-decoration:none;">1</a>
                            <?php if ($start > 2): ?>
                                <span style="margin:0 5px;">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <a href="?id=<?= $celengan_id ?>&page=<?= $i ?>"
                                style="padding:5px 10px; border-radius:5px; margin:0 2px; text-decoration:none;
                <?= $i == $page ? 'background:#007bff; color:white;' : 'background:#f0f0f0; color:black;' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($end < $total_pages): ?>
                            <?php if ($end < $total_pages - 1): ?>
                                <span style="margin:0 5px;">...</span>
                            <?php endif; ?>
                            <a href="?id=<?= $celengan_id ?>&page=<?= $total_pages ?>"
                                style="padding:5px 10px; border-radius:5px; background:#f0f0f0; color:black; text-decoration:none;">
                                <?= $total_pages ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?id=<?= $celengan_id ?>&page=<?= $page + 1 ?>" style="margin-left: 5px; text-decoration:none; color:#007bff;">&raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </table>
        <?php endif; ?>

        <div id="chartContainer">
            <div style="display: flex;  justify-content:space-between;">
                <h3 style="margin-top: 0;">Grafik Pemasukan dan Pengeluaran</h3>
                <div class="chart-filter">
                    <button id="btnBatang" class="filter-btn active"><i class="bi bi-bar-chart" style="font-size: 22px;"></i></button>
                    <button id="btnGaris" class="filter-btn"><i class="bi bi-graph-up" style="font-size: 22px;"></i></button>
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
            </div>

            <canvas id="chartTransaksi" height="100"></canvas>

            <div style="text-align:center; margin-bottom:10px;">
                <button onclick="chart.resetZoom()" class="filter-btn">Reset Zoom</button>
            </div>
        </div>

        <div style="text-align:center; margin-bottom:15px;">
            <button class="filter-btn-range" data-range="1D">1D</button>
            <button class="filter-btn-range" data-range="1W">1W</button>
            <button class="filter-btn-range" data-range="1M">1M</button>
            <button class="filter-btn-range" data-range="3M">3M</button>
            <button class="filter-btn-range" data-range="1Y">1Y</button>
            <button class="filter-btn-range" data-range="ALL">All</button>
            <script>
                const css = `
                .filter-btn-range {
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
                .filter-btn-range:hover {
                    background: #007bff;
                }
                .filter-btn-range.active {
                    background: #007bff;
                    color: white;
                }
            `;

                const styleTag = document.createElement("style");
                styleTag.innerHTML = css;
                document.head.appendChild(styleTag);

                const rangeBar = ["1D", "1W", "1M", "2M", "3M", "ALL"];

                const rangeLine = ["1D", "1W", "1M", "3M", "6M", "1Y", "ALL"];

                const rangeBox = document.querySelector("div > .filter-btn-range")?.parentElement;

                function generateRangeButtons(chartType) {
                    if (!rangeBox) return;

                    rangeBox.innerHTML = "";

                    const list = (chartType === "bar") ? rangeBar : rangeLine;

                    list.forEach(range => {
                        const btn = document.createElement("button");
                        btn.className = "filter-btn-range";
                        btn.dataset.range = range;
                        btn.innerText = range;

                        btn.addEventListener("click", () => handleRangeFilter(range));

                        if (range === currentRange) {
                            btn.classList.add("active");
                        }

                        rangeBox.appendChild(btn);
                    });
                }

                document.getElementById("btnBatang").addEventListener("click", () => {
                    setTimeout(() => generateRangeButtons("bar"), 10);
                });

                document.getElementById("btnGaris").addEventListener("click", () => {
                    setTimeout(() => generateRangeButtons("line"), 10);
                });

                setTimeout(() => generateRangeButtons(currentType), 50);
            </script>
        </div>

        <script>
            const rawLabels = <?= json_encode($labels); ?>;
            const rawSaldoAwal = <?= json_encode($saldo_awal); ?>;
            const rawSaldoAkhir = <?= json_encode($saldo_akhir); ?>;
            const rawColors = <?= json_encode($colors); ?>;

            const ctx = document.getElementById('chartTransaksi').getContext('2d');
            let chart;

            let currentType = localStorage.getItem("chartTypeLastUsed") || 'bar';

            document.getElementById('btnBatang').classList.toggle('active', currentType === 'bar');
            document.getElementById('btnGaris').classList.toggle('active', currentType === 'line');

            function toDate(str) {
                const [y, m, d] = str.split('-').map(Number);
                return new Date(y, m - 1, d);
            }

            function getSaldoSebelumTanggal(targetDate) {
                let saldo = 0;
                for (let i = 0; i < rawLabels.length; i++) {
                    if (toDate(rawLabels[i]) < targetDate) {
                        saldo = rawSaldoAkhir[i];
                    } else break;
                }
                return saldo;
            }

            function colorForDiff(diff) {
                if (diff > 0) return '#41A67E';
                if (diff < 0) return '#BF1A1A';
                return 'rgba(180,180,180,0.6)';
            }

            function mergeByDate(labels, awal, akhir, colors) {
                const map = Object.create(null);
                labels.forEach((tgl, i) => {
                    const diff = akhir[i] - awal[i];
                    if (!map[tgl]) {
                        map[tgl] = {
                            startSaldo: awal[i],
                            totalDiff: 0
                        };
                    }
                    map[tgl].totalDiff += diff;
                });

                const mergedLabels = [];
                const mergedAwal = [];
                const mergedAkhir = [];
                const mergedColors = [];

                Object.keys(map).sort((a, b) => new Date(a) - new Date(b)).forEach(tgl => {
                    const start = map[tgl].startSaldo;
                    const totalDiff = map[tgl].totalDiff;
                    mergedLabels.push(tgl);
                    mergedAwal.push(start);
                    mergedAkhir.push(start + totalDiff);
                    mergedColors.push(colorForDiff(totalDiff));
                });

                return {
                    labels: mergedLabels,
                    awal: mergedAwal,
                    akhir: mergedAkhir,
                    colors: mergedColors
                };
            }

            function filterData(range) {
                const now = new Date();
                let startDate;

                switch (range) {
                    case '1D':
                        startDate = new Date(now.getTime() - 1 * 86400000);
                        break;
                    case '1W':
                        startDate = new Date(now.getTime() - 7 * 86400000);
                        break;
                    case '1M':
                        startDate = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                        break;
                    case '3M':
                        startDate = new Date(now.getFullYear(), now.getMonth() - 3, now.getDate());
                        break;
                    case '1Y':
                        startDate = new Date(now.getFullYear() - 1, now.getMonth(), now.getDate());
                        break;
                    case 'ALL':
                        startDate = new Date(0);
                        break;
                    default:
                        startDate = new Date(0);
                }

                const saldoAwal = getSaldoSebelumTanggal(startDate);
                let currentSaldo = saldoAwal;

                const labels = [];
                const awal = [];
                const akhir = [];
                const colors = [];

                for (let i = 0; i < rawLabels.length; i++) {
                    const tgl = toDate(rawLabels[i]);
                    if (tgl >= startDate) {
                        labels.push(rawLabels[i]);
                        awal.push(currentSaldo);
                        const diff = rawSaldoAkhir[i] - rawSaldoAwal[i];
                        currentSaldo += diff;
                        akhir.push(currentSaldo);
                        colors.push(rawColors[i]);
                    }
                }

                if (labels.length === 0) {
                    labels.push("Tidak ada transaksi");
                    awal.push(saldoAwal);
                    akhir.push(saldoAwal);
                    colors.push("rgba(180,180,180,0.5)");
                }

                let data;
                if (range === '1D' || range === '1W') {
                    data = {
                        labels,
                        awal,
                        akhir,
                        colors
                    };
                } else {
                    data = mergeByDate(labels, awal, akhir, colors);
                }

                return data;
            }

            function updateChart(type, data) {
                if (chart) chart.destroy();

                const allVals = [...data.awal, ...data.akhir];
                const minY = Math.min(...allVals);
                const maxY = Math.max(...allVals);

                let datasets;
                if (type === 'bar') {
                    datasets = [{
                        label: 'Perubahan Saldo',
                        data: data.awal.map((v, i) => [v, data.akhir[i]]),
                        backgroundColor: data.colors,
                        borderColor: data.colors,
                        borderWidth: 1
                    }];
                } else if (type === 'line') {
                    const lineData = data.akhir;
                    const lineColors = data.colors.map(c => c);
                    const backgroundColors = data.colors.map(c => c + "33");

                    datasets = [{
                        label: 'Saldo Harian',
                        data: lineData,
                        borderColor: lineColors,
                        backgroundColor: backgroundColors,
                        borderWidth: 2,
                        tension: 0.4,
                        pointRadius: 2
                    }];
                }

                chart = new Chart(ctx, {
                    type: type,
                    data: {
                        labels: data.labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            zoom: {
                                zoom: {
                                    wheel: {
                                        enabled: true
                                    },
                                    pinch: {
                                        enabled: true
                                    },
                                    mode: 'x'
                                },
                                pan: {
                                    enabled: true,
                                    mode: 'x'
                                }
                            },
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        if (type === 'bar') {
                                            const s = context.raw[0];
                                            const e = context.raw[1];
                                            const diff = e - s;
                                            return [
                                                "Sebelum: Rp" + s.toLocaleString("id-ID"),
                                                "Sesudah: Rp" + e.toLocaleString("id-ID"),
                                                (diff >= 0 ? "Naik: +" : "Turun: ") +
                                                "Rp" + Math.abs(diff).toLocaleString("id-ID")
                                            ];
                                        } else {
                                            const val = context.raw;
                                            return "Saldo: Rp" + val.toLocaleString("id-ID");
                                        }
                                    }
                                }
                            }
                        },
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
                                beginAtZero: (type === 'bar' && currentRange === 'ALL'),
                                min: (type === 'bar' && currentRange !== 'ALL') ? minY - (maxY - minY) * 0.1 : undefined,
                                max: maxY + (maxY - minY) * 0.1,
                                ticks: {
                                    callback: v => "Rp" + v.toLocaleString("id-ID")
                                },
                                grid: {
                                    color: "rgba(220,220,220,0.3)"
                                }
                            }
                        }
                    }
                });
            }

            let currentRange = 'ALL';

            function switchChartType(type) {
                currentType = type;
                localStorage.setItem("chartTypeLastUsed", type);

                document.getElementById('btnBatang').classList.toggle('active', type === 'bar');
                document.getElementById('btnGaris').classList.toggle('active', type === 'line');

                const data = filterData(currentRange);
                updateChart(type, data);
                chart.resetZoom();
            }

            function handleRangeFilter(range) {
                currentRange = range;
                document.querySelectorAll('.filter-btn-range').forEach(b => b.classList.remove('active'));
                document.querySelector(`.filter-btn-range[data-range="${range}"]`).classList.add('active');

                const data = filterData(range);
                updateChart(currentType, data);
                chart.resetZoom();
            }

            document.getElementById('btnBatang').addEventListener('click', () => switchChartType('bar'));
            document.getElementById('btnGaris').addEventListener('click', () => switchChartType('line'));

            document.querySelectorAll('.filter-btn-range').forEach(btn => {
                btn.addEventListener('click', () => handleRangeFilter(btn.dataset.range));
            });

            handleRangeFilter('1W');

            function autoRefreshDaily() {
                const last = localStorage.getItem("lastRefresh");
                const now = new Date().toDateString();
                if (last !== now) {
                    localStorage.setItem("lastRefresh", now);
                    location.reload();
                }
            }
            autoRefreshDaily();
        </script>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
    </div>

</body>

</html>