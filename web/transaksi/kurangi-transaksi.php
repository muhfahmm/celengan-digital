<?php
require_once('../config/auth_check.php');

require '../config/db.php';

$celengan_id = $_GET['celengan_id'] ?? null;

// Ambil data celengan
if ($celengan_id) {
    $stmt = $pdo->prepare("SELECT * FROM celengan WHERE id = ?");
    $stmt->execute([$celengan_id]);
    $celengan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$celengan) {
        die("Data celengan tidak ditemukan.");
    }
} else {
    die("Parameter celengan_id tidak ditemukan.");
}

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nominalRaw = $_POST['nominal'];
    $keterangan = $_POST['keterangan'] ?? '';

    // Sanitasi input: ambil hanya digit
    $nominal = (int) preg_replace('/\D/', '', $nominalRaw);

    if ($nominal > 0) {
        // Kurangi total celengan
        $stmt = $pdo->prepare("UPDATE celengan SET total = total - ? WHERE id = ?");
        $stmt->execute([$nominal, $celengan_id]);

        // Simpan transaksi
        $stmt = $pdo->prepare("INSERT INTO transaksi (celengan_id, nominal, tipe, keterangan, tanggal) VALUES (?, ?, 'keluar', ?, NOW())");
        $stmt->execute([$celengan_id, $nominal, $keterangan]);

        header("Location: ../dashboard/detail-celengan.php?id=" . $celengan_id);
        exit;
    } else {
        $error = "Nominal harus lebih dari 0.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurangi Saldo - <?= htmlspecialchars($celengan['nama_celengan']); ?></title>
    
    <!-- Icons & Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --danger-color: #EF4444;
            --danger-hover: #DC2626;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
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

        .form-container {
            width: 100%;
            max-width: 450px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            padding: 40px;
            border-radius: 24px;
            box-shadow: 
                0 8px 32px 0 rgba(31, 38, 135, 0.2),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.6);
            position: relative;
            z-index: 1;
        }

        body.dark .form-container {
            background: rgba(31, 41, 55, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 32px;
        }

        .header h2 {
            font-size: 24px;
            font-weight: 800;
            color: #1F2937;
            margin-bottom: 8px;
        }

        body.dark .header h2 {
            color: #F3F4F6;
        }

        .header p {
            font-size: 14px;
            color: #6B7280;
            font-weight: 500;
        }

        body.dark .header p {
            color: rgba(255, 255, 255, 0.7);
        }

        .header p strong {
            color: var(--danger-color);
            font-weight: 700;
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #DC2626;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        body.dark .error {
            background: rgba(239, 68, 68, 0.2);
            color: #FCA5A5;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        body.dark label {
            color: rgba(255, 255, 255, 0.8);
        }

        input[type="text"],
        input[type="number"],
        textarea {
            width: 100% !important;
            box-sizing: border-box;
            padding: 12px 16px;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            background: rgba(255, 255, 255, 0.8);
            color: #1F2937;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            display: block;
        }

        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {-moz-appearance: textfield; appearance: textfield;}

        body.dark input[type="number"],
        body.dark input[type="text"],
        body.dark textarea {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            color: #F3F4F6;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus {
            outline: none;
            border-color: var(--danger-color);
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        body.dark input[type="number"]:focus,
        body.dark textarea:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--danger-color);
        }

        input::placeholder,
        textarea::placeholder {
            color: #9CA3AF;
        }

        /* Stronger selector to ensure full-width inputs inside the form container */
        .form-container .form-group input,
        .form-container input,
        .form-container textarea,
        .form-container select {
            width: 100% !important;
            display: block !important;
            box-sizing: border-box !important;
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #34D399 0%, #10B981 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 
                0 4px 20px rgba(239, 68, 68, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-family: 'Inter', sans-serif;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        button:hover {
            background: var(--danger-hover);
            transform: translateY(-2px);
            box-shadow: 
                0 8px 30px rgba(239, 68, 68, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        button:active {
            transform: translateY(0);
        }

        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .back-link:hover {
            gap: 10px;
            color: #5568d3;
        }

        body.dark .back-link {
            color: rgba(255, 255, 255, 0.9);
        }

        body.dark .back-link:hover {
            color: #ffffff;
        }

        .theme-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
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
            z-index: 10;
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

        @media (max-width: 480px) {
            .form-container {
                padding: 30px 24px;
            }

            .header h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div id="darkToggle" class="theme-toggle" title="Ganti Tema">
        <i id="themeIcon" class="bi bi-moon"></i>
    </div>

    <div class="form-container">
        <div class="header">
            <h2>Kurangi Saldo</h2>
            <p>Celengan: <strong><?= htmlspecialchars($celengan['nama_celengan']); ?></strong></p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error">
                <i class="bi bi-exclamation-circle"></i>
                <span><?= $error; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="nominal">Nominal (Rp)</label>
                <input 
                    type="text" 
                    id="nominal"
                    name="nominal" 
                    placeholder="Contoh: 1.000"
                    required
                    oninput="formatCurrency(this)"
                >
            </div>

            <div class="form-group">
                <label for="keterangan">Keterangan</label>
                <input 
                    type="text" 
                    id="keterangan"
                    name="keterangan" 
                    placeholder="Contoh: Pengeluaran harian"
                    autocomplete="off"
                >
            </div>

            <button type="submit">
                <i class="bi bi-dash-circle"></i>
                <span>Kurangi Saldo</span>
            </button>
        </form>

        <a href="../dashboard/detail-celengan.php?id=<?= $celengan_id; ?>" class="back-link">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>

    <script>
        // Dark Mode Logic
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

        // Number formatting for nominal input (same behavior as Tambah Transaksi)
        function formatCurrency(el) {
            const digits = el.value.replace(/\D/g, "");
            if (digits === "") {
                el.value = "";
                return;
            }
            el.value = digits.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Ensure raw number is submitted (remove thousand separator dots)
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const nominalInput = document.getElementById('nominal');
                if (nominalInput) {
                    nominalInput.value = nominalInput.value.replace(/\./g, '');
                }
            });
        }
    </script>
</body>
</html>
