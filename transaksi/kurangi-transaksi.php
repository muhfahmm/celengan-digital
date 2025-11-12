<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

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
    $nominal = $_POST['nominal'];
    $keterangan = $_POST['keterangan'];

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
    <title>Kurangi Progress Celengan</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f0f2f5;
            font-family: Arial, sans-serif;
        }

        .container {
            width: 400px;
            background: #ffffff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h3 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        label {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            display: block;
            margin-bottom: 5px;
        }

        input,
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        input:focus,
        textarea:focus {
            border-color: #d9534f;
            outline: none;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #d9534f;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover {
            background: #c9302c;
        }

        a {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #d9534f;
            text-decoration: none;
            font-size: 14px;
        }

        a:hover {
            text-decoration: underline;
        }

        .error {
            color: #c9302c;
            background: #f9d6d5;
            border: 1px solid #f1b0b7;
            border-radius: 6px;
            padding: 8px;
            margin-bottom: 12px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h3>Kurangi Progress - <?= htmlspecialchars($celengan['nama_celengan']); ?></h3>

        <?php if (isset($error)): ?>
            <p class="error"><?= $error; ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Nominal (Rp)</label>
            <input type="number" name="nominal" min="1" required>

            <label>Keterangan</label>
            <textarea name="keterangan" rows="3" placeholder="Contoh: pengeluaran harian"></textarea>

            <button type="submit">Kurangi</button>
        </form>

        <a href="../dashboard/detail-celengan.php?id=<?= $celengan_id; ?>">Kembali</a>
    </div>
</body>

</html>
