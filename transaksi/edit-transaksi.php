<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}
include('../config/db.php');

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT t.*, c.nama_celengan 
  FROM transaksi t JOIN celengan c ON t.celengan_id = c.id 
  WHERE t.id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
  die("Transaksi tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit Transaksi</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <div class="form-container">
    <h2>Edit Transaksi</h2>
    <form action="api/api-edit-transaksi.php" method="POST">
      <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
      <p>Celengan: <?php echo $data['nama_celengan']; ?></p>
      <input type="number" name="nominal" value="<?php echo $data['nominal']; ?>" required><br>
      <select name="tipe" required>
        <option value="masuk" <?php if($data['tipe']=='masuk') echo 'selected'; ?>>Masuk</option>
        <option value="keluar" <?php if($data['tipe']=='keluar') echo 'selected'; ?>>Keluar</option>
      </select><br>
      <input type="text" name="keterangan" value="<?php echo $data['keterangan']; ?>"><br>
      <button type="submit">Simpan</button>
    </form>
    <a href="../dashboard/index.php">Kembali</a>
  </div>
</body>
</html>
