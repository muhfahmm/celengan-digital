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
</head>

<body>
    <div class="form-container">
        <h2>Tambah Celengan Baru</h2>
        <form action="api/api-tambah-celengan.php" method="POST">
            <input type="text" name="nama_celengan" placeholder="Nama Celengan" required><br>
            <input type="number" name="target" placeholder="Target Uang (opsional)"><br>
            <button type="submit">Simpan</button>
        </form>
        <a href="../dashboard/index.php">Kembali</a>
    </div>
</body>

</html>