<?php
require_once('../config/auth_check.php');
include('../config/db.php');

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// 1. Ambil Summary Data
$sumStmt = $pdo->prepare("SELECT SUM(total) AS total_tabungan, SUM(target) AS total_target FROM celengan WHERE user_id = ?");
$sumStmt->execute([$user_id]);
$sum = $sumStmt->fetch(PDO::FETCH_ASSOC);
$total_tabungan = $sum['total_tabungan'] ?? 0;
$total_target = $sum['total_target'] ?? 0;

// 2. Logic Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'awal';

// Query untuk celengan yang di-pin (selalu di atas)
$sqlPinned = "SELECT * FROM celengan WHERE user_id = ? AND is_pinned = 1 ORDER BY created_at DESC";
$stmtPinned = $pdo->prepare($sqlPinned);
$stmtPinned->execute([$user_id]);
$celengan_pinned = $stmtPinned->fetchAll(PDO::FETCH_ASSOC);

// Query untuk celengan yang tidak di-pin
$sql = "SELECT * FROM celengan WHERE user_id = ? AND is_pinned = 0";

switch ($sort) {
    case 'akhir': $sql .= " ORDER BY created_at DESC"; break;
    case 'progress': $sql .= " ORDER BY (total/target) DESC"; break;
    case 'target': $sql .= " ORDER BY target DESC"; break;
    case 'total': $sql .= " ORDER BY total DESC"; break;
    default: $sql .= " ORDER BY created_at ASC"; break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$celengan_unpinned = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gabungkan: pinned di atas, unpinned di bawah
$celengan_list = array_merge($celengan_pinned, $celengan_unpinned);

function rupiah($angka) {
    return 'Rp' . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= htmlspecialchars($username); ?></title>
    
    <!-- Icons & Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: 
                radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3), transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(138, 92, 246, 0.3), transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(59, 130, 246, 0.2), transparent 50%);
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

        @media (max-width: 1600px) { .container { padding: 40px 15%; } }
        @media (max-width: 1024px) { .container { padding: 30px 5%; } }
        @media (max-width: 640px) { .container { padding: 20px 16px; } }

        /* Header */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .user-welcome h1 {
            font-size: 28px;
            font-weight: 800;
            margin: 0;
            color: #1F2937;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        
        .user-welcome span {
            font-size: 14px;
            color: #374151;
            font-weight: 600;
        }

        body.dark .user-welcome h1 { 
            color: #ffffff;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }
        body.dark .user-welcome span { 
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .theme-toggle {
            cursor: pointer;
            font-size: 20px;
            padding: 10px;
            border-radius: 50%;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: #1F2937;
            display: flex;
            align-items: center; 
            justify-content: center;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .theme-toggle:hover { 
            background: rgba(255, 255, 255, 1);
            transform: scale(1.1) rotate(20deg);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        body.dark .theme-toggle {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        body.dark .theme-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .btn-logout {
            text-decoration: none;
            color: #991B1B;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(239, 68, 68, 0.3);
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
        }
        .btn-logout:hover { 
            background: #DC2626;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(220, 38, 38, 0.4);
            border-color: #DC2626;
        }

        body.dark .btn-logout {
            color: #ffffff;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        body.dark .btn-logout:hover {
            background: rgba(239, 68, 68, 0.9);
        }

        /* Dashboard Global Stats */
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            padding: 24px;
            border-radius: 20px;
            box-shadow: 
                0 8px 32px 0 rgba(31, 38, 135, 0.2),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.6);
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 
                0 12px 40px 0 rgba(31, 38, 135, 0.3),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.6);
        }

        body.dark .stat-card {
            background: rgba(31, 41, 55, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stat-icon {
            width: 56px; 
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center; 
            justify-content: center;
            font-size: 26px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .stat-info .label { 
            font-size: 13px; 
            color: #4B5563;
            margin-bottom: 6px; 
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-info .value { 
            font-size: 22px; 
            font-weight: 800;
            color: #1F2937;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        body.dark .stat-info .label { 
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }
        body.dark .stat-info .value { 
            color: #ffffff;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Controls Area */
        .controls-area {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .btn-create {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 
                0 4px 20px rgba(102, 126, 234, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-create:hover { 
            transform: translateY(-2px) scale(1.02);
            box-shadow: 
                0 8px 30px rgba(102, 126, 234, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        .sort-filters {
            display: flex;
            flex-wrap: nowrap;
            gap: 8px;
            overflow-x: auto;
            overflow-y: hidden;
            width: 100%;
            padding-bottom: 4px;
            min-width: 0;
            -webkit-overflow-scrolling: touch;
        }

        .sort-filters span,
        .sort-filters .btn-filter {
            flex: 0 0 auto;
        }

        .btn-filter {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            color: #4B5563;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .btn-filter:hover { 
            background: rgba(255, 255, 255, 1);
            border-color: rgba(255, 255, 255, 0.8);
            transform: translateY(-1px);
            color: #1F2937;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-filter.active { 
            background: #667eea;
            color: #ffffff;
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        body.dark .btn-filter {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }
        body.dark .btn-filter:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
        }
        body.dark .btn-filter.active {
            background: rgba(255, 255, 255, 0.95);
            color: #667eea;
            border-color: rgba(255, 255, 255, 0.95);
        }

        /* Grid Celengan */
        .celengan-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .c-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-radius: 20px;
            box-shadow: 
                0 8px 32px 0 rgba(31, 38, 135, 0.2),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.6);
            padding: 24px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .c-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
            transition: left 0.5s;
        }

        .c-card:hover::before {
            left: 100%;
        }

        .c-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 
                0 16px 48px 0 rgba(31, 38, 135, 0.3),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.6);
            border-color: rgba(255, 255, 255, 0.8);
        }

        body.dark .c-card {
            background: rgba(31, 41, 55, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        body.dark .c-card::before {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        }

        .c-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .c-title {
            font-size: 17px;
            font-weight: 800;
            color: #1F2937;
            margin: 0;
            line-height: 1.4;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        body.dark .c-title { 
            color: #ffffff;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .pin-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 8px;
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            color: white;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);
        }

        .pin-badge i {
            font-size: 12px;
        }
        
        .c-subtitle {
            font-size: 12px;
            color: #6B7280;
            margin-top: 4px;
            font-weight: 600;
        }

        body.dark .c-subtitle { 
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
        }

        .c-actions {
            display: flex;
            gap: 8px;
        }

        .btn-icon {
            width: 32px; 
            height: 32px;
            border-radius: 10px;
            display: flex;
            align-items: center; 
            justify-content: center;
            color: #4B5563;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
        }
        .btn-icon:hover { 
            background: rgba(255, 255, 255, 0.95);
            color: #1F2937;
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        body.dark .btn-icon {
            color: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.05);
        }
        body.dark .btn-icon:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .c-progress-bg {
            height: 10px;
            background: rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            overflow: hidden;
            margin: 15px 0 10px 0;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        body.dark .c-progress-bg { background: rgba(255, 255, 255, 0.15); }

        .c-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10B981, #34D399, #6EE7B7);
            border-radius: 10px;
            box-shadow: 
                0 0 10px rgba(16, 185, 129, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .c-stats {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        body.dark .c-stats {
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .c-stat-group { display: flex; flex-direction: column; gap: 4px; }
        .c-stat-label { 
            color: #6B7280;
            font-size: 11px; 
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .c-stat-val { 
            font-weight: 800;
            color: #1F2937;
            font-size: 14px;
        }

        body.dark .c-stat-label { 
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
        }
        body.dark .c-stat-val { 
            color: #ffffff;
            font-weight: 700;
        }

        .btn-view {
            margin-top: 15px;
            display: block;
            text-align: center;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            color: #667eea;
            padding: 12px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .btn-view:hover { 
            background: #667eea;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            border-color: #667eea;
        }

        body.dark .btn-view {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        body.dark .btn-view:hover {
            background: rgba(255, 255, 255, 0.95);
            color: #667eea;
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: #6B7280;
            font-weight: 600;
        }

        body.dark .empty-state {
            color: var(--text-secondary);
            font-weight: 400;
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
        }

        body.dark .modal-header p {
            color: rgba(255, 255, 255, 0.7);
        }

        .modal-actions {
            display: flex;
            gap: 12px;
        }

        .btn-cancel,
        .btn-delete {
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

        .btn-delete {
            background: #EF4444;
            color: white;
            border: 1px solid #EF4444;
        }

        .btn-delete:hover {
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

        /* Notification Modal Styles */
        .notification-modal {
            max-width: 380px;
        }

        .notification-modal .modal-icon {
            background: rgba(16, 185, 129, 0.1);
            color: #10B981;
        }

        .notification-modal .modal-icon.error {
            background: rgba(239, 68, 68, 0.1);
            color: #EF4444;
        }

        body.dark .notification-modal .modal-icon {
            background: rgba(16, 185, 129, 0.2);
        }

        body.dark .notification-modal .modal-icon.error {
            background: rgba(239, 68, 68, 0.2);
        }

        .btn-ok {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-ok:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
        }
        
    </style>
</head>
<body>
    <div class="container">
        <!-- Top Nav -->
        <div class="top-nav">
            <div class="user-welcome">
                <h1>Hi, <?= htmlspecialchars($username); ?> 👋</h1>
                <span>Selamat datang kembali di Celengan Digital</span>
            </div>
            <div class="nav-actions">
                <div id="darkToggle" class="theme-toggle" title="Ganti Tema">
                    <i id="themeIcon" class="bi bi-moon"></i>
                </div>
                <a href="../auth/logout.php" class="btn-logout" title="Keluar">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Global Stats -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="stat-info">
                    <div class="label">Total Tabungan</div>
                    <div class="value"><?= rupiah($total_tabungan); ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;">
                    <i class="bi bi-bullseye"></i>
                </div>
                <div class="stat-info">
                    <div class="label">Total Target</div>
                    <div class="value"><?= rupiah($total_target); ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                    <i class="bi bi-pie-chart"></i>
                </div>
                <div class="stat-info">
                    <div class="label">Celengan Aktif</div>
                    <div class="value"><?= count($celengan_list); ?></div>
                </div>
            </div>
        </div>

        <!-- Actions & Filters -->
        <div class="controls-area">
            <a href="../data-celengan/tambah-celengan.php" class="btn-create">
                <i class="bi bi-plus-lg"></i> Buat Celengan Baru
            </a>
            
            <div class="sort-filters">
                <span style="font-size:13px; color:var(--text-secondary); align-self:center; margin-right:5px;">Urutkan:</span>
                <a href="?sort=awal" class="btn-filter <?= $sort == 'awal' ? 'active' : '' ?>">Terbaru</a>
                <a href="?sort=progress" class="btn-filter <?= $sort == 'progress' ? 'active' : '' ?>">Progress</a>
                <a href="?sort=total" class="btn-filter <?= $sort == 'total' ? 'active' : '' ?>">Saldo Tertinggi</a>
                <a href="?sort=target" class="btn-filter <?= $sort == 'target' ? 'active' : '' ?>">Target Terbesar</a>
            </div>
        </div>

        <!-- List Celengan -->
        <div class="celengan-list">
            <?php if (count($celengan_list) > 0): ?>
                <?php foreach ($celengan_list as $c): ?>
                    <?php 
                        $prog = $c['target'] > 0 ? round(($c['total'] / $c['target']) * 100) : 0; 
                        $prog = min(100, max(0, $prog)); // Clamp 0-100
                    ?>
                    <div class="c-card">
                        <div class="c-header">
                            <div>
                                <h3 class="c-title">
                                    <?= htmlspecialchars($c['nama_celengan']); ?>
                                    <?php if ($c['is_pinned'] == 1): ?>
                                        <span class="pin-badge"><i class="bi bi-pin-angle-fill"></i> Disematkan</span>
                                    <?php endif; ?>
                                </h3>
                                <div class="c-subtitle">Dibuat <?= date('d M Y', strtotime($c['created_at'])); ?></div>
                            </div>
                            <div class="c-actions">
                                <button 
                                    class="btn-icon btn-pin" 
                                    data-id="<?= $c['id']; ?>" 
                                    data-pinned="<?= $c['is_pinned']; ?>"
                                    title="<?= $c['is_pinned'] == 1 ? 'Lepas Sematan' : 'Sematkan'; ?>">
                                    <i class="bi bi-pin-angle<?= $c['is_pinned'] == 1 ? '-fill' : ''; ?>"></i>
                                </button>
                                <a href="../data-celengan/edit-celengan.php?id=<?= $c['id']; ?>" class="btn-icon" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn-icon" style="color:#EF4444; background: transparent; border: none; cursor: pointer;" 
                                   onclick="openDeleteModal(<?= $c['id']; ?>, '<?= htmlspecialchars($c['nama_celengan'], ENT_QUOTES); ?>')" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div style="display:flex; justify-content:space-between; font-size:12px; color:var(--text-secondary); margin-bottom:4px;">
                            <span>Progress</span>
                            <span style="font-weight:600; color:var(--primary-color);"><?= $prog; ?>%</span>
                        </div>
                        <div class="c-progress-bg">
                            <div class="c-progress-fill" style="width: <?= $prog; ?>%"></div>
                        </div>
                        
                        <div class="c-stats">
                            <div class="c-stat-group">
                                <span class="c-stat-label">Terkumpul</span>
                                <span class="c-stat-val" style="color:#10B981;"><?= rupiah($c['total']); ?></span>
                            </div>
                            <div class="c-stat-group" style="text-align:right;">
                                <span class="c-stat-label">Target</span>
                                <span class="c-stat-val"><?= rupiah($c['target']); ?></span>
                            </div>
                        </div>

                        <a href="detail-celengan.php?id=<?= $c['id']; ?>" class="btn-view">
                            Lihat Detail <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-piggy-bank" style="font-size: 48px; opacity: 0.3; margin-bottom: 20px; display: block;"></i>
                    <p>Belum ada celengan. Yuk mulai menabung!</p>
                </div>
            <?php endif; ?>
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
                <p id="modalMessage">Apakah Anda yakin ingin menghapus celengan ini?</p>
            </div>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeDeleteModal()">Batal</button>
                <a id="confirmDeleteBtn" href="#" class="btn-delete">Hapus</a>
            </div>
        </div>
    </div>

    <!-- Notification Modal -->
    <div id="notificationModal" class="modal-overlay">
        <div class="modal-content notification-modal">
            <div class="modal-header">
                <div class="modal-icon" id="notifIcon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <h3 id="notifTitle">Berhasil!</h3>
                <p id="notifMessage">Operasi berhasil dilakukan</p>
            </div>
            <div class="modal-actions">
                <button class="btn-ok" onclick="closeNotificationModal()">OK</button>
            </div>
        </div>
    </div>

    <script>
        // Dark Mode Logic (Consistent with detail-celengan.php)
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

        const savedTheme = localStorage.getItem("theme") === "dark";
        applyTheme(savedTheme);

        darkToggle.addEventListener("click", () => {
            const isDark = document.body.classList.toggle("dark");
            localStorage.setItem("theme", isDark ? "dark" : "light");
            applyTheme(isDark);
        });

        // Delete Modal Functions
        function openDeleteModal(id, name) {
            const modal = document.getElementById('deleteModal');
            const message = document.getElementById('modalMessage');
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            
            message.textContent = `Apakah Anda yakin ingin menghapus celengan "${name}"?`;
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
                closeNotificationModal();
            }
        });

        // Notification Modal Functions
        function showNotification(title, message, isError = false) {
            const modal = document.getElementById('notificationModal');
            const icon = document.getElementById('notifIcon');
            const titleEl = document.getElementById('notifTitle');
            const messageEl = document.getElementById('notifMessage');
            const iconEl = icon.querySelector('i');
            
            // Set content
            titleEl.textContent = title;
            messageEl.textContent = message;
            
            // Set icon based on type
            if (isError) {
                icon.classList.add('error');
                iconEl.className = 'bi bi-x-circle';
            } else {
                icon.classList.remove('error');
                iconEl.className = 'bi bi-check-circle';
            }
            
            // Show modal
            modal.classList.add('active');
        }

        function closeNotificationModal() {
            const modal = document.getElementById('notificationModal');
            modal.classList.remove('active');
        }

        // Close notification when clicking outside
        document.getElementById('notificationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeNotificationModal();
            }
        });

        // Toggle Pin Function
        document.querySelectorAll('.btn-pin').forEach(button => {
            button.addEventListener('click', function() {
                const celenganId = this.getAttribute('data-id');
                const isPinned = this.getAttribute('data-pinned');
                const icon = this.querySelector('i');
                
                // Disable button sementara
                this.disabled = true;
                
                // Kirim request AJAX
                fetch('../data-celengan/api/api-toggle-pin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `celengan_id=${celenganId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload halaman untuk update tampilan
                        window.location.reload();
                    } else {
                        // Tampilkan error notification
                        showNotification('Gagal', data.message, true);
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error', 'Terjadi kesalahan saat memproses permintaan', true);
                    this.disabled = false;
                });
            });
        });
    </script>
</body>
</html>