<?php
require_once('../config/auth_check.php');
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
$transaksi_all = $stmt_transaksi->fetchAll(PDO::FETCH_ASSOC);

$progress = $celengan['target'] > 0 ? round(($celengan['total'] / $celengan['target']) * 100) : 0;
$kekurangan = $celengan['target'] - $celengan['total'];
if ($kekurangan < 0) $kekurangan = 0;

function rupiah($angka)
{
    return 'Rp' . number_format($angka, 0, ',', '.');
}

// Persiapan Data Chart (Semua data untuk perhitungan saldo)
$total = 0;
$ath = 0; // All Time High Variable
$labels = [];
$saldo_awal = [];
$saldo_akhir = [];
$colors = [];

foreach ($transaksi_all as $t) {
    $labels[] = $t['tanggal'];
    $saldo_awal[] = $total;

    $nominal = (float)$t['nominal'];
    if (strtolower($t['tipe']) == 'masuk') {
        $total += $nominal;
        $colors[] = '#10B981'; // Modern Green (Emerald-500)
    } else {
        $total -= $nominal;
        $colors[] = '#EF4444'; // Modern Red (Red-500)
    }

    $saldo_akhir[] = $total;

    // Cek All Time High
    if ($total > $ath) {
        $ath = $total;
    }
}

// Pagination Logic untuk Tabel
$limit = 10;

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE celengan_id = ?");
$count_stmt->execute([$celengan_id]);
$total_transaksi = $count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_transaksi / $limit));

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : $total_pages;
if ($page < 1) {
    $page = 1;
} elseif ($page > $total_pages) {
    $page = $total_pages;
}

$offset = ($page - 1) * $limit;

$stmt_page = $pdo->prepare("SELECT * FROM transaksi WHERE celengan_id = ? ORDER BY tanggal ASC LIMIT ? OFFSET ?");
$stmt_page->bindValue(1, $celengan_id, PDO::PARAM_INT);
$stmt_page->bindValue(2, $limit, PDO::PARAM_INT);
$stmt_page->bindValue(3, $offset, PDO::PARAM_INT);
$stmt_page->execute();
$transaksi_page = $stmt_page->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Celengan - <?= htmlspecialchars($celengan['nama_celengan']); ?></title>
    <!-- Ikon Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Font Inter for Modern Look -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #3B82F6;
            --primary-hover: #2563EB;
            --bg-body: #F3F4F6;
            --bg-card: #FFFFFF;
            --text-main: #1F2937;
            --text-secondary: #6B7280;
            --border-color: #E5E7EB;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --radius: 12px;
        }

        /* Dark Mode Variables */
        body.dark {
            --bg-body: #111827;
            --bg-card: #1F2937;
            --text-main: #F3F4F6;
            --text-secondary: #9CA3AF;
            --border-color: #374151;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            transition: all 0.3s ease;
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: none;
            pointer-events: none;
            z-index: 0;
        }

        body.dark {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        }

        body.dark::before {
            background: 
                radial-gradient(circle at 20% 50%, rgba(59, 130, 246, 0.15), transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(139, 92, 246, 0.15), transparent 50%);
        }

        .container {
            width: 100%;
            box-sizing: border-box;
            margin: 0 auto;
            padding: 40px 20%;
            position: relative;
            z-index: 1;
        }

        @media (max-width: 1600px) {
            .container { padding: 40px 15%; }
        }

        @media (max-width: 1024px) {
            .container { padding: 30px 5%; }
        }

        /* Header Navigation */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .btn-back {
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: color 0.2s;
        }

        .btn-back:hover {
            color: var(--primary-color);
        }

        .theme-toggle {
            cursor: pointer;
            font-size: 20px;
            padding: 8px;
            border-radius: 50%;
            transition: background 0.2s;
            color: var(--text-main);
        }

        .theme-toggle:hover {
            background: rgba(128, 128, 128, 0.1);
        }

        /* Cards */
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-radius: 20px;
            padding: 28px;
            box-shadow: 
                0 8px 32px 0 rgba(31, 38, 135, 0.2),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.5);
            margin-bottom: 24px;
            border: 1px solid rgba(255, 255, 255, 0.6);
        }

        body.dark .card {
            background: rgba(31, 41, 55, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        h2 { 
            margin: 0 0 8px 0; 
            font-size: 24px; 
            font-weight: 800;
            color: #1F2937;
        }
        
        h3 { 
            font-size: 18px; 
            font-weight: 700; 
            margin: 30px 0 16px 0;
            color: #1F2937;
        }

        body.dark h2,
        body.dark h3 {
            color: #F3F4F6;
        }

        /* Action Buttons */
        .action-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 20px 0;
            justify-content: space-between;
        }

        .btn-group {
            display: flex;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            box-shadow: var(--shadow-sm);
        }

        .btn:hover { transform: translateY(-1px); box-shadow: var(--shadow-md); }
        .btn:active { transform: translateY(0); }

        .btn-primary { background: var(--primary-color); color: white; }
        .btn-primary:hover { background: var(--primary-hover); }

        .btn-success { background: #10B981; color: white; }
        .btn-success:hover { background: #059669; }

        .btn-warning { background: #F59E0B; color: white; }
        .btn-warning:hover { background: #D97706; }

        .btn-danger { background: #EF4444; color: white; }
        .btn-danger:hover { background: #DC2626; }

        .btn-edit { background: #3B82F6; color: white; padding: 6px 12px; font-size: 13px; border-radius: 6px; }
        .btn-edit:hover { background: #2563EB; }

        .btn-delete { 
            background: #EF4444; 
            color: white; 
            padding: 6px 12px; 
            font-size: 13px; 
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-delete:hover { background: #DC2626; }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        
        .stat-item {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            padding: 20px;
            border-radius: 16px;
            box-shadow: 
                0 8px 32px 0 rgba(31, 38, 135, 0.2),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.6);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-item:hover {
            transform: translateY(-2px);
            box-shadow: 
                0 12px 40px 0 rgba(31, 38, 135, 0.25),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.6);
        }

        body.dark .stat-item {
            background: rgba(31, 41, 55, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stat-label { 
            font-size: 13px; 
            color: #4B5563;
            margin-bottom: 6px; 
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-value { 
            font-size: 20px; 
            font-weight: 800; 
            color: #1F2937;
        }

        body.dark .stat-label { color: rgba(255, 255, 255, 0.8); font-weight: 500; }
        body.dark .stat-value { color: #F3F4F6; }

        /* Progress Bar */
        .progress-container {
            background: #E5E7EB;
            height: 12px;
            border-radius: 20px;
            overflow: hidden;
            margin: 24px 0 12px 0;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #10B981, #34D399);
            border-radius: 20px;
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.dark .progress-container { background: #374151; }

        /* Table */
        .table-responsive {
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th {
            background: rgba(128, 128, 128, 0.05);
            text-align: left;
            padding: 12px 16px;
            font-weight: 600;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border-color);
        }

        td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
        }

        tr:last-child td { border-bottom: none; }

        .type-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .type-in { background: rgba(16, 185, 129, 0.1); color: #10B981; }
        .type-out { background: rgba(239, 68, 68, 0.1); color: #EF4444; }

        /* Chart Controls */
        .chart-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn-filter, .btn-range {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        body.dark .btn-filter, 
        body.dark .btn-range {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-filter:hover, .btn-range:hover {
            background: rgba(59, 130, 246, 0.1);
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.2);
        }

        .btn-filter.active, .btn-range.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: transparent;
            font-weight: 600;
            transform: scale(1.05);
            box-shadow: 
                0 4px 12px rgba(102, 126, 234, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        body.dark .btn-filter:hover,
        body.dark .btn-range:hover {
            background: rgba(59, 130, 246, 0.2);
        }

        body.dark .btn-filter.active,
        body.dark .btn-range.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 
                0 4px 12px rgba(102, 126, 234, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        .range-group { 
            display: flex; 
            gap: 4px; 
            overflow-x: auto;
            overflow-y: hidden;
            white-space: nowrap; 
            padding: 12px 0 8px; 
            -webkit-overflow-scrolling: touch; 
            justify-content: flex-start;
            width: 100%;
            scrollbar-width: thin; 
            scrollbar-color: rgba(102, 126, 234, 0.3) transparent; 
        }
        .range-group::-webkit-scrollbar { height: 8px; }
        .range-group::-webkit-scrollbar-track { background: transparent; }
        .range-group::-webkit-scrollbar-thumb { background: rgba(102, 126, 234, 0.4); border-radius: 4px; }
        .range-group::-webkit-scrollbar-thumb:hover { background: rgba(102, 126, 234, 0.6); }
        .chart-range { flex: 0 0 auto; min-width: 40px; padding: 6px 6px; font-size: 11px; }
        .chart-range .mobile-text { display: inline; }
        .chart-range .desktop-text { display: none; }
        @media (min-width: 768px) {
            .range-group { gap: 8px; }
            .chart-range { min-width: 100px; padding: 8px 14px; font-size: 13px; }
            .chart-range .mobile-text { display: none; }
            .chart-range .desktop-text { display: inline; }
        }

        .header-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 20px;
            align-items: center;
            margin-bottom: 16px;
        }

        .header-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
        }

        .subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-top: 8px;
        }

        .stat-item {
            padding: 16px;
            min-height: 92px;
        }

        .editable-date {
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .editable-date:hover {
            background: rgba(59, 130, 246, 0.08);
        }

        .inline-date-edit {
            width: 100%;
            min-width: 120px;
            border: 1px solid rgba(59, 130, 246, 0.35);
            border-radius: 8px;
            padding: 4px 8px;
            font-size: 0.95rem;
            background: var(--bg-body);
            color: var(--text-main);
        }

        /* Pagination Wrapper */
        .pagination-wrapper {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            padding: 16px;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        body.dark .pagination-wrapper {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }


        /* Delete Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.2s ease;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-radius: 20px;
            padding: 32px;
            max-width: 400px;
            width: 90%;
            box-shadow: 
                0 8px 32px 0 rgba(31, 38, 135, 0.3),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.6);
            animation: slideUp 0.3s ease;
        }

        body.dark .modal-content {
            background: rgba(31, 41, 55, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .modal-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #EF4444;
        }

        body.dark .modal-icon {
            background: rgba(239, 68, 68, 0.2);
        }

        .modal-header h3 {
            font-size: 20px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 8px;
        }

        body.dark .modal-header h3 {
            color: #F3F4F6;
        }

        .modal-header p {
            font-size: 14px;
            color: #6B7280;
            font-weight: 500;
            line-height: 1.5;
        }

        body.dark .modal-header p {
            color: rgba(255, 255, 255, 0.7);
        }

        .modal-actions {
            display: flex;
            gap: 12px;
        }

        .btn-cancel,
        .btn-modal-delete {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: block;
            border: none;
        }

        .btn-cancel {
            background: rgba(0, 0, 0, 0.05);
            color: #4B5563;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .btn-cancel:hover {
            background: rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        body.dark .btn-cancel {
            background: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        body.dark .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .btn-modal-delete {
            background: #EF4444;
            color: white;
            border: 1px solid #EF4444;
        }

        .btn-modal-delete:hover {
            background: #DC2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 640px) {
            .container { padding: 20px 16px; }
            .action-bar { flex-direction: column; }
            .btn-group { width: 100%; }
            .btn { flex: 1; justify-content: center; }
            .chart-controls { flex-direction: column; align-items: flex-start; gap: 10px; }
            .range-group { width: 100%; justify-content: flex-start; }
            .header-grid { grid-template-columns: 1fr; gap: 14px; }
            .header-actions { justify-content: flex-start; }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Top Navigation -->
        <div class="top-nav">
            <a href="index.php" class="btn-back">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
            <div id="darkToggle" class="theme-toggle" title="Ganti Tema">
                <i id="themeIcon" class="bi bi-moon"></i>
            </div>
        </div>

        <!-- Main Card: Detail & Stats -->
        <div class="card">
            <div class="header-grid">
                <div class="header-info">
                    <h2><?= htmlspecialchars($celengan['nama_celengan']); ?></h2>
                    <div class="subtitle">Dibuat pada: <?= date('d M Y', strtotime($celengan['created_at'])); ?></div>
                </div>
                <div class="header-actions">
                    <a href="../transaksi/tambah-transaksi.php?celengan_id=<?= $celengan['id']; ?>" class="btn btn-success">
                        <i class="bi bi-plus-lg"></i> Tabung
                    </a>
                    <a href="../transaksi/kurangi-transaksi.php?celengan_id=<?= $celengan['id']; ?>" class="btn btn-warning">
                        <i class="bi bi-dash-lg"></i> Tarik
                    </a>
                    <a href="../data-celengan/edit-celengan.php?id=<?= $celengan['id']; ?>" class="btn btn-edit">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>
                    <button class="btn btn-delete" onclick="openDeleteModal(<?= $celengan['id']; ?>, '<?= htmlspecialchars($celengan['nama_celengan'], ENT_QUOTES); ?>')">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-label">Total Terkumpul</div>
                    <div class="stat-value" style="color: #10B981;" id="valTotal"><?= rupiah($celengan['total']); ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Target Akhir</div>
                    <div class="stat-value" style="color: #3B82F6;" id="valTarget"><?= rupiah($celengan['target']); ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Rekor Tertinggi <i class="bi bi-trophy-fill" style="color: #F59E0B; font-size: 14px;"></i></div>
                    <div class="stat-value" style="color: #8B5CF6;" id="valATH"><?= rupiah($ath); ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Kekurangan</div>
                    <div class="stat-value" style="color: #EF4444;" id="valKekurangan"><?= rupiah($kekurangan); ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Pertumbuhan (View)</div>
                    <div class="stat-value" id="valGrowth">-</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Progress</div>
                    <div class="stat-value" id="valProgress"><?= $progress; ?>%</div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="progress-container">
                <div class="progress-bar" id="barProgress" style="width: <?= $progress; ?>%;"></div>
            </div>
        </div>

        <!-- Transaksi History -->
        <div class="card">
            <h3>Riwayat Transaksi</h3>
            <?php if (empty($transaksi_page)): ?>
                <div style="text-align: center; color: var(--text-secondary); padding: 20px;">
                    <i class="bi bi-receipt" style="font-size: 32px; display: block; margin-bottom: 10px; opacity: 0.5;"></i>
                    Belum ada transaksi.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nominal</th>
                                <th>Tipe</th>
                                <th>Keterangan</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transaksi_page as $t): ?>
                            <tr>
                                <td class="editable-date" data-id="<?= $t['id']; ?>" data-date="<?= htmlspecialchars($t['tanggal'], ENT_QUOTES); ?>" title="Klik untuk edit tanggal">
                                    <?= date('d/m/Y', strtotime($t['tanggal'])); ?>
                                </td>
                                <td style="font-weight: 500; font-family: monospace;">
                                    <?= rupiah($t['nominal']); ?>
                                </td>
                                <td>
                                    <?php $isMasuk = strtolower($t['tipe']) == 'masuk'; ?>
                                    <span class="type-badge <?= $isMasuk ? 'type-in' : 'type-out'; ?>">
                                        <?= $isMasuk ? '<i class="bi bi-arrow-down"></i> Masuk' : '<i class="bi bi-arrow-up"></i> Keluar'; ?>
                                    </span>
                                </td>
                                <td style="color: var(--text-secondary);"><?= htmlspecialchars($t['keterangan']); ?></td>
                                <td style="text-align: right;">
                                    <a href="../transaksi/edit-transaksi.php?id=<?= $t['id']; ?>&celengan_id=<?= $celengan_id; ?>" 
                                       style="color: var(--primary-color); margin-right: 8px;">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="../transaksi/hapus-transaksi.php?id=<?= $t['id']; ?>&celengan_id=<?= $celengan_id; ?>" 
                                       style="color: #EF4444;"
                                       onclick="return confirm('Hapus transaksi ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>


                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-wrapper">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php $isActive = ($i === $page); ?>
                            <a href="?id=<?= $celengan_id ?>&page=<?= $i ?>" 
                               class="btn-range <?= $isActive ? 'active' : '' ?>"
                               style="text-decoration: none;"
                               title="Halaman <?= $i ?><?= $isActive ? ' (Aktif)' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Chart Section -->
        <div class="card">
            <div class="chart-controls">
                <h3 style="margin: 0;">Analisis Keuangan</h3>
                <div style="display: flex; gap: 8px;">
                    <button class="btn-filter active" id="btnBatang" title="Grafik Batang"><i class="bi bi-bar-chart"></i></button>
                    <button class="btn-filter" id="btnGaris" title="Grafik Garis"><i class="bi bi-graph-up"></i></button>
                    <button class="btn-filter" onclick="resetScale()" title="Reset Zoom"><i class="bi bi-arrows-move"></i></button>
                    <button class="btn-filter active" id="btnAutoFit" title="Auto Fit Y-Axis"><i class="bi bi-lock-fill"></i></button>
                </div>
            </div>
            
            <div class="range-group" style="margin-bottom: 20px; justify-content: flex-start; width: calc(100% + 0px);">
                <!-- Buttons with mobile abbreviations and desktop full text -->
                <button class="btn-range chart-range" data-range="1D"><span class="mobile-text">1D</span><span class="desktop-text">1 Hari</span></button>
                <button class="btn-range chart-range" data-range="1W"><span class="mobile-text">1W</span><span class="desktop-text">1 Minggu</span></button>
                <button class="btn-range chart-range" data-range="1M"><span class="mobile-text">1M</span><span class="desktop-text">1 Bulan</span></button>

                <button class="btn-range chart-range" data-range="1Y"><span class="mobile-text">1Y</span><span class="desktop-text">1 Tahun</span></button>
                <button class="btn-range chart-range" data-range="ALL"><span class="mobile-text">ALL</span><span class="desktop-text">Semua</span></button>
            </div>

            <div style="position: relative; height: 550px; width: 100%;">
                <canvas id="chartTransaksi"></canvas>
                <div id="chartEmptyMessage" style="display:none; position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); text-align:center; color: #6B7280; font-size: 1rem; pointer-events:none;">
                    Tidak ada pemasukan di rentang waktu ini.
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h3>Hapus Celengan?</h3>
                <p id="modalMessage">Yakin ingin menghapus celengan ini? Semua data transaksi juga akan terhapus.</p>
            </div>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeDeleteModal()">Batal</button>
                <a id="confirmDeleteBtn" href="#" class="btn-modal-delete">Hapus</a>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
    
    <script>
        // --- 1. Theme Logic ---
        const themeIcon = document.getElementById("themeIcon");
        const darkToggle = document.getElementById("darkToggle");
        
        function applyTheme(isDark) {
            if (isDark) {
                document.body.classList.add("dark");
                themeIcon.classList.replace("bi-moon", "bi-sun");
            } else {
                document.body.classList.remove("dark");
                themeIcon.classList.replace("bi-sun", "bi-moon");
            }
        }

        // Init
        const savedTheme = localStorage.getItem("theme") === "dark";
        applyTheme(savedTheme);

        darkToggle.addEventListener("click", () => {
            const isDark = document.body.classList.toggle("dark");
            localStorage.setItem("theme", isDark ? "dark" : "light");
            applyTheme(isDark);
        });

        // --- 2. Chart Logic ---
        // --- 2. Chart Logic ---
        const targetAmount = <?= $celengan['target']; ?>;
        const rawLabels = <?= json_encode($labels); ?>;
        const rawSaldoAwal = <?= json_encode($saldo_awal); ?>;
        const rawSaldoAkhir = <?= json_encode($saldo_akhir); ?>;
        const rawColors = <?= json_encode($colors); ?>;

        const ctxElement = document.getElementById('chartTransaksi');
        let ctx;
        if(ctxElement) {
            ctx = ctxElement.getContext('2d');
        }
        
        let chart;
        let currentType = 'bar'; // default
        let isAutoFit = true; // State untuk Auto Fit Y-Axis
        
        function toDate(str) {
            const [y, m, d] = str.split('-').map(Number);
            return new Date(y, m - 1, d);
        }

        // Format Rupiah Helper
        function formatRupiah(angka) {
            return 'Rp' + Number(angka).toLocaleString('id-ID');
        }

        // --- Logic Cari Index Start berdasarkan Range Waktu ---
        function getRangeStartDate(range) {
            const now = new Date();

            if (range === '1D') return new Date(now.getTime() - 24 * 60 * 60 * 1000);
            if (range === '1W') return new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
            if (range === '1M') return new Date(now.getFullYear(), now.getMonth() - 1, now.getDate());
            if (range === '3M') return new Date(now.getFullYear(), now.getMonth() - 3, now.getDate());
            if (range === '6M') return new Date(now.getFullYear(), now.getMonth() - 6, now.getDate());
            if (range === '9M') return new Date(now.getFullYear(), now.getMonth() - 9, now.getDate());
            if (range === '1Y') return new Date(now.getFullYear() - 1, now.getMonth(), now.getDate());
            return null; // ALL
        }

        function filterDataByRange(range) {
            if (range === 'ALL') {
                return {
                    labels: rawLabels,
                    saldoAwal: rawSaldoAwal,
                    saldoAkhir: rawSaldoAkhir,
                    colors: rawColors
                };
            }

            const startDate = getRangeStartDate(range);
            if (!startDate) {
                return {
                    labels: rawLabels,
                    saldoAwal: rawSaldoAwal,
                    saldoAkhir: rawSaldoAkhir,
                    colors: rawColors
                };
            }

            const labels = [];
            const saldoAwal = [];
            const saldoAkhir = [];
            const colors = [];

            for (let i = 0; i < rawLabels.length; i++) {
                if (toDate(rawLabels[i]) >= startDate) {
                    labels.push(rawLabels[i]);
                    saldoAwal.push(rawSaldoAwal[i]);
                    saldoAkhir.push(rawSaldoAkhir[i]);
                    colors.push(rawColors[i]);
                }
            }

            return { labels, saldoAwal, saldoAkhir, colors };
        }

        function resetStatsForEmptyRange() {
            const totalEl = document.getElementById('valTotal');
            const athEl = document.getElementById('valATH');
            const kekEl = document.getElementById('valKekurangan');
            const progressEl = document.getElementById('valProgress');
            const growthEl = document.getElementById('valGrowth');
            const barProgress = document.getElementById('barProgress');

            // Tampilkan total akhir dari semua raw data, bukan 0
            const lastRawTotal = rawSaldoAkhir.length > 0 ? rawSaldoAkhir[rawSaldoAkhir.length - 1] : 0;

            if (totalEl) totalEl.innerText = formatRupiah(lastRawTotal);
            if (athEl) athEl.innerText = formatRupiah(0);
            if (kekEl) kekEl.innerText = formatRupiah(targetAmount - lastRawTotal);
            if (progressEl) {
                let prog = (targetAmount > 0) ? Math.round((lastRawTotal / targetAmount) * 100) : 0;
                progressEl.innerText = prog + '%';
            }
            if (growthEl) {
                growthEl.innerText = '-';
                growthEl.style.color = '#6B7280';
            }
            if (barProgress) {
                let prog = (targetAmount > 0) ? Math.round((lastRawTotal / targetAmount) * 100) : 0;
                barProgress.style.width = prog + '%';
            }
        }

        // --- Logic Update Statistik Berdasarkan View ---
        function syncStatsAndView({chart}) {
            if (!chart || !chart.data || !chart.data.labels || chart.data.labels.length === 0) {
                resetStatsForEmptyRange();
                return;
            }

            const xScale = chart.scales.x;
            const datasets = chart.data.datasets[0];
            if (!datasets || !datasets.data || datasets.data.length === 0) {
                resetStatsForEmptyRange();
                return;
            }

            const dataPoints = datasets.data;
            const labels = chart.data.labels;

            // Dapatkan index data yang terlihat
            // Karena Category Axis, min/max bisa berupa float saat zoom, kita round
            let startIndex = Math.round(xScale.min);
            let endIndex = Math.round(xScale.max);

            // Clamp agar tidak error array index
            startIndex = Math.max(0, startIndex);
            endIndex = Math.min(labels.length - 1, endIndex);
            
            // Validasi jika terjadi zoom out kejauhan
            if (startIndex > endIndex) {
                startIndex = 0; 
                endIndex = labels.length - 1;
            }

            let visibleMin = Infinity;
            let visibleMax = -Infinity;
            let currentATH = 0;

            // Loop range yang terlihat
            for (let i = startIndex; i <= endIndex; i++) {
                let valHigh, valLow;

                if (currentType === 'bar') {
                    // [awal, akhir]
                    const valAwal = dataPoints[i][0];
                    const valAkhir = dataPoints[i][1];
                    valHigh = Math.max(valAwal, valAkhir);
                    valLow = Math.min(valAwal, valAkhir);
                } else {
                    valHigh = dataPoints[i];
                    valLow = dataPoints[i];
                }

                if (valHigh > currentATH) currentATH = valHigh;
                if (valHigh > visibleMax) visibleMax = valHigh;
                if (valLow < visibleMin) visibleMin = valLow;
            }

            // Jika tidak ada data valid
            if (visibleMin === Infinity) return;

            // 1. UPDATE STATISTIK UI
            // Ambil "Final Balance" di periode ini (data terakhir di kanan)
            let lastVal = (currentType === 'bar') ? dataPoints[endIndex][1] : dataPoints[endIndex];
            
            // Update teks
            document.getElementById('valTotal').innerText = formatRupiah(lastVal);
            document.getElementById('valATH').innerText = formatRupiah(currentATH);
            
            let kek = targetAmount - lastVal;
            if(kek < 0) kek = 0;
            document.getElementById('valKekurangan').innerText = formatRupiah(kek);
            
            let prog = (targetAmount > 0) ? Math.round((lastVal / targetAmount) * 100) : 0;
            document.getElementById('valProgress').innerText = prog + '%';
            document.getElementById('barProgress').style.width = prog + '%';

            // Update Pertumbuhan (Net Change in View)
            if (rawSaldoAwal && rawSaldoAkhir) {
                let startBal = rawSaldoAwal[startIndex];
                let endBal = rawSaldoAkhir[endIndex];
                let growth = endBal - startBal;
                
                let pct = 0;
                if (startBal !== 0) {
                    pct = (growth / startBal) * 100;
                } else if (growth > 0) {
                    pct = 100; // Asumsi 100% jika mulai dari 0
                }
                
                let elGrowth = document.getElementById('valGrowth');
                if(elGrowth) {
                    let sign = growth >= 0 ? '+' : '';
                    // Tampilkan: +Rp 100.000 (10.5%)
                    elGrowth.innerText = `${sign}${formatRupiah(growth)} (${pct.toFixed(1)}%)`;
                    elGrowth.style.color = (growth >= 0) ? '#10B981' : '#EF4444';
                }
            }

            // 2. UPDATE Y-AXIS SCALE
            // Hanya jalankan logika "Left at bottom" jika Auto Fit AKTIF
            if (isAutoFit) {
                // User Request: "The one on the left should always be at the bottom"
                // User Request: "Tidak terpotong tapi tetap dimulai dari bawah"
                // Solusi: Gunakan nilai TERENDAH dari seluruh view (visibleMin) sebagai anchor.
                // Ini memastikan:
                // 1. Data drop (red bar) tidak terpotong (karena min mengikuti dia).
                // 2. Tampilan tetap tight ("mulai dari bawah") karena tidak ada padding berlebih.
                
                // Gunakan visibleMin, dan pastikan tidak minus di bawah 0
                let floor = visibleMin;
                
                // Opsional: Sedikit padding bawah agar stroke tidak kepotong (misal Rp 1.000 atau 0)
                // Kita set ke floor langsung agar "nempel" sesuai request sebelumnya, 
                // chart.js biasanya handle stroke well enough.
                
                chart.options.scales.y.min = 0;
                chart.options.scales.y.max = visibleMax;
                
                if (chart.options.scales.y.min < 0) chart.options.scales.y.min = 0;

                // PENTING: Force update agar scale diterapkan visualnya
                chart.update('none');
            }
        }

        // Fungsi Reset Zoom & Stats
        function resetScale() {
            if(!chart) return;
            chart.resetZoom();
            
            // Reset ke mode Auto Fit
            isAutoFit = true;
            document.getElementById('btnAutoFit').classList.add('active');
            document.getElementById('btnAutoFit').innerHTML = '<i class="bi bi-lock-fill"></i>';
            chart.options.plugins.zoom.pan.mode = 'x'; // Lock Y back
            
            chart.options.scales.y.min = undefined; // Reset ke auto
            chart.options.scales.y.max = undefined;
            chart.update();
            
            // Kembalikan stats ke nilai akhir asli (global)
            // (Sebenarnya syncStatsAndView akan dipanggil saat reset zoom juga oleh plugin, tapi kita paksa update chart dulu)
        }
        
        // Fungsi Toggle Auto Fit
        function toggleAutoFit() {
            isAutoFit = !isAutoFit;
            const btn = document.getElementById('btnAutoFit');
            
            if (isAutoFit) {
                btn.classList.add('active');
                btn.innerHTML = '<i class="bi bi-lock-fill"></i>';
                btn.title = "Auto Fit ON (Locked Y)";
                
                if(chart) {
                    chart.options.plugins.zoom.pan.mode = 'x'; 
                    chart.update(); // Update config
                    syncStatsAndView({chart}); 
                }
            } else {
                btn.classList.remove('active');
                btn.innerHTML = '<i class="bi bi-unlock-fill"></i>';
                btn.title = "Free Move (Unlocked)";
                
                if(chart) {
                    chart.options.plugins.zoom.pan.mode = 'xy';
                    chart.update(); // Update config enabling XY
                }
            }
        }

        function createGradient(ctx, colorStart, colorEnd) {
            const gradient = ctx.createLinearGradient(0, 0, 0, 500);
            gradient.addColorStop(0, colorStart);
            gradient.addColorStop(1, colorEnd);
            return gradient;
        }

        // Hapus filterData lama yg memotong array
        
        function updateChart(initialRange = 'ALL') {
            if (!ctx) return; // Prevent error if canvas not found
            if (chart) chart.destroy();

            const { labels, saldoAwal, saldoAkhir, colors } = filterDataByRange(initialRange);
            const hasData = labels && labels.length > 0;

            const chartEmptyMessage = document.getElementById('chartEmptyMessage');
            if (chartEmptyMessage) {
                chartEmptyMessage.style.display = hasData ? 'none' : 'block';
            }

            if (!hasData) {
                resetStatsForEmptyRange();
            }

            if (!rawLabels || rawLabels.length === 0) {
                return;
            }

            const isDark = document.body.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.05)';
            const textColor = isDark ? '#9CA3AF' : '#6B7280';

            let datasets = [];
            if (hasData) {
                if (currentType === 'bar') {
                    datasets = [{
                        label: 'Perubahan Saldo',
                        data: saldoAwal.map((v, i) => [v, saldoAkhir[i]]),
                        backgroundColor: colors,
                        borderRadius: 4,
                        barPercentage: 0.96,
                        categoryPercentage: 0.96
                    }];
                } else {
                    datasets = [{
                        label: 'Total Saldo',
                        data: saldoAkhir,
                        borderColor: '#3B82F6',
                        backgroundColor: createGradient(ctx, 'rgba(59, 130, 246, 0.5)', 'rgba(59, 130, 246, 0.0)'),
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#FFFFFF',
                        pointBorderColor: '#3B82F6',
                        pointBorderWidth: 2
                    }];
                }
            }

            const maxIndex = labels.length - 1;
            const showTooltip = hasData;

            let allValues = [];
            if (hasData) {
                allValues = [...saldoAwal, ...saldoAkhir];
            } else {
                allValues = [0];
            }
            let globalMax = Math.max(...allValues);
            if (!isFinite(globalMax) || globalMax < 0) globalMax = 0;

            chart = new Chart(ctx, {
                type: currentType,
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: showTooltip,
                            mode: 'index',
                            intersect: false,
                            backgroundColor: isDark ? '#1F2937' : '#FFFFFF',
                            titleColor: isDark ? '#F3F4F6' : '#111827',
                            bodyColor: isDark ? '#D1D5DB' : '#4B5563',
                            borderColor: isDark ? '#374151' : '#E5E7EB',
                            borderWidth: 1,
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    let val = context.raw;
                                    if (currentType === 'bar') {
                                        let diff = val[1] - val[0];
                                        return ` ${diff > 0 ? '+' : ''}Rp${Math.abs(diff).toLocaleString('id-ID')}`;
                                    } else {
                                        return ` Rp${val.toLocaleString('id-ID')}`;
                                    }
                                }
                            }
                        },
                        zoom: {
                            limits: {
                                x: {min: 0, max: Math.max(maxIndex, 0)}, 
                                y: {min: 0, max: globalMax}
                            },
                            zoom: {
                                wheel: { enabled: true, speed: 0.1 }, 
                                pinch: { enabled: true },
                                mode: 'x',
                                onZoom: syncStatsAndView
                            },
                            pan: { 
                                enabled: true, 
                                mode: 'x', 
                                threshold: 0, 
                                onPan: syncStatsAndView 
                            }
                        }
                    },
                    scales: {
                        x: {
                            min: 0,
                            max: Math.max(maxIndex, 0),
                            grid: { display: false },
                            ticks: { 
                                display: false,
                                color: textColor,
                                maxTicksLimit: 10,
                                font: {
                                    size: 13,
                                    family: "'Inter', sans-serif"
                                }
                            }
                        },
                        y: {
                            grid: { 
                                color: gridColor,
                                borderDash: [5, 5]
                            },
                            ticks: {
                                color: textColor,
                                callback: (val) => 'Rp' + val.toLocaleString('id-ID')
                            },
                            border: { display: false }
                        }
                    }
                }
            });

            if (hasData) {
                setTimeout(() => syncStatsAndView({chart: chart}), 100);
            }
        }

        // Event Listeners
        document.getElementById('btnBatang').addEventListener('click', function() {
            currentType = 'bar';
            this.classList.add('active');
            document.getElementById('btnGaris').classList.remove('active');
            const activeRangeBtn = document.querySelector('.chart-range.active');
            updateChart(activeRangeBtn ? activeRangeBtn.dataset.range : 'ALL');
        });

        document.getElementById('btnGaris').addEventListener('click', function() {
            currentType = 'line';
            this.classList.add('active');
            document.getElementById('btnBatang').classList.remove('active');
            const activeRangeBtn = document.querySelector('.chart-range.active');
            updateChart(activeRangeBtn ? activeRangeBtn.dataset.range : 'ALL');
        });
        
        document.getElementById('btnAutoFit').addEventListener('click', toggleAutoFit);

        // Range Buttons: Kini hanya mengubah Scale
        document.querySelectorAll('.chart-range').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.chart-range').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const range = this.dataset.range;
                localStorage.setItem('chartRange', range);
                updateChart(range);
            });
        });

        // Muat pilihan rentang waktu dari localStorage
        const savedRange = localStorage.getItem('chartRange') || 'ALL';
        
        // Set tombol aktif sesuai pilihan tersimpan
        document.querySelectorAll('.chart-range').forEach(btn => {
            if (btn.dataset.range === savedRange) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // Initial Render dengan pilihan tersimpan
        updateChart(savedRange);

        // Dark Mode Chart Update Listener
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === "class") {
                    updateChart(); // Redraw chart colors on theme change
                }
            });
        });
        observer.observe(document.body, { attributes: true });

        function pad(num) {
            return num.toString().padStart(2, '0');
        }

        function formatDisplayDate(dateStr) {
            if (!dateStr) return '';
            const [year, month, day] = dateStr.split('-');
            return `${pad(day)}/${pad(month)}/${year}`;
        }

        function cancelInlineDateEdit(cell, originalDate) {
            cell.textContent = formatDisplayDate(originalDate);
        }

        function submitInlineDate(cell, id, newDate, originalDate) {
            const params = new URLSearchParams();
            params.append('id', id);
            params.append('tanggal', newDate);

            fetch('../transaksi/api/api-update-transaksi-date.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: params.toString()
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cell.dataset.date = data.tanggal;
                    cell.textContent = formatDisplayDate(data.tanggal);
                } else {
                    alert(data.error || 'Gagal menyimpan tanggal.');
                    cancelInlineDateEdit(cell, originalDate);
                }
            })
            .catch(() => {
                alert('Gagal menyimpan tanggal. Periksa koneksi.');
                cancelInlineDateEdit(cell, originalDate);
            });
        }

        function startInlineDateEdit(cell) {
            if (cell.querySelector('input.inline-date-edit')) return;

            const originalDate = cell.dataset.date || '';
            const input = document.createElement('input');
            input.type = 'date';
            input.className = 'inline-date-edit';
            input.value = originalDate;

            cell.textContent = '';
            cell.appendChild(input);
            input.focus();

            input.addEventListener('blur', function() {
                const selectedDate = input.value;
                if (!selectedDate) {
                    alert('Tanggal tidak boleh kosong.');
                    input.focus();
                    return;
                }
                if (selectedDate === originalDate) {
                    cancelInlineDateEdit(cell, originalDate);
                    return;
                }
                submitInlineDate(cell, cell.dataset.id, selectedDate, originalDate);
            });

            input.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    input.blur();
                }
                if (event.key === 'Escape') {
                    event.preventDefault();
                    cancelInlineDateEdit(cell, originalDate);
                }
            });
        }

        document.addEventListener('click', function(event) {
            const cell = event.target.closest('td.editable-date');
            if (!cell) return;
            startInlineDateEdit(cell);
        });

        // Delete Modal Functions
        function openDeleteModal(id, name) {
            const modal = document.getElementById('deleteModal');
            const message = document.getElementById('modalMessage');
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            
            message.textContent = `Yakin ingin menghapus celengan "${name}"? Semua data transaksi juga akan terhapus.`;
            confirmBtn.href = `../data-celengan/hapus-celengan.php?id=${id}`;
            
            modal.classList.add('active');
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.remove('active');
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });

    </script>
</body>
</html>