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
</head>

<body>
    <div class="form-container">
        <h2>Edit Celengan</h2>
        <form action="api/api-edit-celengan.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $celengan['id']; ?>">
            <input type="text" name="nama_celengan" value="<?php echo $celengan['nama_celengan']; ?>" required><br>
            <input type="number" name="target" value="<?php echo $celengan['target']; ?>"><br>
            <button type="submit">Simpan Perubahan</button>
        </form>
        <a href="../dashboard/index.php">Kembali</a>
    </div>
</body>

</html>