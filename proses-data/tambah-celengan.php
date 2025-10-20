<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Celengan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dark-mode">
    <header>
        <a href="../index.php" class="back-link" style="color: #e0e0e0; text-decoration: none; font-size: 1.5em; margin-right: 15px;">←</a>
        <h2>Tambah Celengan Baru</h2>
        </header>

    <main class="form-container">
        <?php if (isset($_GET['error'])): ?>
            <p style="color: red; text-align: center;">Gagal menyimpan data. Pastikan semua kolom terisi dengan benar.</p>
        <?php endif; ?>
        
        <form action="api/api-tambah-celengan.php" method="POST">
            
            <label for="nama_tabungan">Nama Tabungan</label>
            <input type="text" id="nama_tabungan" name="nama_tabungan" placeholder="Misal: Beli Laptop Baru" required>
            
            <label for="target_tabungan">Target Tabungan</label>
            <input type="number" id="target_tabungan" name="target_tabungan" min="1000" placeholder="Rp10.000.000" required>
            
            <label for="mata_uang">Mata Uang</label>
            <select id="mata_uang" name="mata_uang">
                <option value="IDR">Indonesia Rupiah (Rp)</option>
            </select>
            
            <label>Rencana Pengisian</label>
            <div class="tab-options">
                <input type="radio" id="harian" name="rencana_pengisian" value="Harian" checked>
                <label for="harian">Harian</label>
                
                <input type="radio" id="mingguan" name="rencana_pengisian" value="Mingguan">
                <label for="mingguan">Mingguan</label>
                
                <input type="radio" id="bulanan" name="rencana_pengisian" value="Bulanan">
                <label for="bulanan">Bulanan</label>
            </div>
            
            <label for="nominal_pengisian">Nominal Pengisian</label>
            <input type="number" id="nominal_pengisian" name="nominal_pengisian" min="1000" placeholder="Rp20.000" required>
            
            <button type="submit" class="save-button">Simpan</button>
        </form>
    </main>

</body>
</html>