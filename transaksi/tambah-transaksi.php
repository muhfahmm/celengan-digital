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
            width: 380px;
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: left;
        }

        h2 {
            text-align: center;
            margin-bottom: 15px;
            color: #333;
        }

        p {
            text-align: center;
            font-size: 15px;
            margin-bottom: 20px;
            color: #444;
        }

        label {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            display: block;
            margin-bottom: 5px;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
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
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #4CAF50;
            text-decoration: none;
            font-size: 14px;
        }

        a:hover {
            text-decoration: underline;
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
            <input type="number" name="nominal" min="1" required>

            <label>Tipe Transaksi</label>
            <select name="tipe" required>
                <option value="masuk">Masuk</option>
                <option value="keluar">Keluar</option>
            </select>

            <label>Keterangan</label>
            <input type="text" name="keterangan" autocomplete="off" placeholder="Opsional">

            <button type="submit">Simpan</button>
        </form>

        <a href="../dashboard/index.php">Kembali</a>
    </div>
</body>

</html>
