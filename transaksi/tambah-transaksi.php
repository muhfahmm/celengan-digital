<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}
include('../config/db.php');

$user_id = $_SESSION['user_id'];
$celengan = $pdo->prepare("SELECT * FROM celengan WHERE user_id = ?");
$celengan->execute([$user_id]);
$listCelengan = $celengan->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Tambah Transaksi</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <div class="form-container">
    <h2>Tambah Transaksi</h2>
    <form action="api/api-tambah-transaksi.php" method="POST">
      <label>Pilih Celengan</label><br>
      <select name="celengan_id" required>
        <option value="">Pilih Celengan</option>
        <?php foreach ($listCelengan as $c): ?>
          <option value="<?php echo $c['id']; ?>"><?php echo $c['nama_celengan']; ?></option>
        <?php endforeach; ?>
      </select><br>

      <label>Nominal</label><br>
      <input type="number" name="nominal" required><br>

      <label>Tipe</label><br>
      <select name="tipe" required>
        <option value="masuk">Masuk</option>
        <option value="keluar">Keluar</option>
      </select><br>

      <label>Keterangan</label><br>
      <input type="text" name="keterangan"><br>

      <button type="submit">Simpan</button>
    </form>
    <a href="../dashboard/index.php">Kembali</a>
  </div>
</body>
</html>
