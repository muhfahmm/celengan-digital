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
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f0f2f5;
            font-family: Arial, sans-serif;
        }

        .form-container {
            width: 350px;
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
            font-size: 20px;
        }

        input,
        select {
            width: 100%;
            margin-bottom: 12px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        input:focus,
        select:focus {
            border-color: #4CAF50;
            outline: none;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover {
            background: #43a047;
        }

        a {
            display: inline-block;
            margin-top: 15px;
            color: #4CAF50;
            text-decoration: none;
            font-size: 14px;
        }

        a:hover {
            text-decoration: underline;
        }

        .error {
            color: #d32f2f;
            background: #fdecea;
            border: 1px solid #f5c6cb;
            border-radius: 6px;
            padding: 8px;
            margin-bottom: 12px;
            font-size: 14px;
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