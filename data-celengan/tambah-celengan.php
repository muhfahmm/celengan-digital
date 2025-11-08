<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Tambah Celengan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .form-container {
            width: 300px;
            margin: 80px auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        input, select {
            width: 90%;
            margin-bottom: 10px;
            padding: 8px;
        }

        button {
            padding: 8px 15px;
            background: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #45a049;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h2>Tambah Celengan Baru</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="api/api-tambah-celengan.php" method="POST">
            <input type="text" name="nama_celengan" placeholder="Nama Celengan" required autocomplete="off"><br>
            <input type="number" name="target" placeholder="Target Uang" min="1" required><br>

            <!-- Tambahan: pilihan pengisian -->
            <select name="pengisian" required>
                <option value="harian">Harian</option>
                <option value="mingguan">Mingguan</option>
                <option value="bulanan">Bulanan</option>
            </select><br>

            <button type="submit">Simpan</button>
        </form>
        <a href="../dashboard/index.php">Kembali</a>
    </div>
</body>

</html>
