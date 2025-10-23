<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include('../config/db.php');

// Ambil ID celengan dari URL
if (!isset($_GET['celengan_id'])) {
    header("Location: ../dashboard/index.php");
    exit;
}
$celengan_id = $_GET['celengan_id'];
$user_id = $_SESSION['user_id'];

// Ambil data celengan berdasarkan id dan user
$stmt = $pdo->prepare("SELECT * FROM celengan WHERE id = ? AND user_id = ?");
$stmt->execute([$celengan_id, $user_id]);
$celengan = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika celengan tidak ditemukan
if (!$celengan) {
    echo "<script>alert('Celengan tidak ditemukan'); window.location='../dashboard/index.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Tambah Transaksi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .form-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background: #f9f9f9;
        }

        input,
        select,
        button {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h2>Tambah Transaksi</h2>
        <p>Celengan: <strong><?php echo htmlspecialchars($celengan['nama_celengan']); ?></strong></p>

        <form action="api/api-tambah-transaksi.php" method="POST">
            <input type="hidden" name="celengan_id" value="<?php echo $celengan['id']; ?>">

            <label>Nominal</label>
            <input type="number" name="nominal" required>

            <label>Tipe Transaksi</label>
            <select name="tipe" required>
                <option value="masuk">Masuk</option>
                <option value="keluar">Keluar</option>
            </select>

            <label>Keterangan</label>
            <input type="text" name="keterangan">

            <button type="submit">Simpan</button>
        </form>

        <a href="../dashboard/index.php">Kembali</a>
    </div>
</body>

</html>