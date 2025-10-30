<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

include('../config/db.php');

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
            font-family: Arial, sans-serif;
            margin: 30px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 450px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 5px #ccc;
        }

        input,
        textarea,
        button {
            width: 100%;
            margin-top: 10px;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            background-color: #d9534f;
            color: white;
            cursor: pointer;
            border: none;
        }

        button:hover {
            background-color: #c9302c;
        }

        .error {
            color: red;
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
            <input type="number" name="nominal" required>

            <label>keterangan</label>
            <textarea name="keterangan" rows="3" placeholder="Contoh: pengeluaran harian"></textarea>

            <button type="submit">Kurangi</button>
        </form>

        <br>
        <a href="../dashboard/detail-celengan.php?id=<?= $celengan_id; ?>">Kembali</a>
    </div>
</body>

</html>