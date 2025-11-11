<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include('../config/db.php');

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM celengan WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$celengan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$celengan) {
    die("Data tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Celengan</title>
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

        input {
            width: 100%;
            margin-bottom: 12px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        input:focus {
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
    </style>
</head>

<body>
    <div class="form-container">
        <h2>Edit Celengan</h2>
        <form action="api/api-edit-celengan.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $celengan['id']; ?>">
            <input type="text" name="nama_celengan" value="<?php echo htmlspecialchars($celengan['nama_celengan']); ?>" required><br>
            <input type="number" name="target" value="<?php echo htmlspecialchars($celengan['target']); ?>" required><br>
            <button type="submit">Simpan Perubahan</button>
        </form>
        <a href="../dashboard/index.php">Kembali</a>
    </div>
</body>

</html>
