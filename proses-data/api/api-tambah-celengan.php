<?php
session_start();
include('../../config/db.php');

// Pastikan hanya POST request yang diterima dan user sudah login
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil dan bersihkan data dari POST
$nama_tabungan = trim($_POST['nama_tabungan'] ?? '');
$target_tabungan = filter_var($_POST['target_tabungan'] ?? 0, FILTER_VALIDATE_FLOAT);
$mata_uang = $_POST['mata_uang'] ?? 'IDR';
$rencana_pengisian = $_POST['rencana_pengisian'] ?? '';
$nominal_pengisian = filter_var($_POST['nominal_pengisian'] ?? 0, FILTER_VALIDATE_FLOAT);

// Validasi dasar
if (empty($nama_tabungan) || $target_tabungan <= 0 || $nominal_pengisian <= 0 || !in_array($rencana_pengisian, ['Harian', 'Mingguan', 'Bulanan'])) {
    header('Location: ../tambah-celengan.php?error=invalid_data');
    exit;
}

// --- Hitung Estimasi Tanggal Tercapai ---
$nominal_harian_estimasi = $nominal_pengisian;
if ($rencana_pengisian == 'Mingguan') {
    $nominal_harian_estimasi = $nominal_pengisian / 7;
} elseif ($rencana_pengisian == 'Bulanan') {
    $nominal_harian_estimasi = $nominal_pengisian / 30.437; // Rata-rata hari per bulan
}

// Cek jika nominal harian terlalu kecil
if ($nominal_harian_estimasi <= 0) {
    header('Location: ../tambah-celengan.php?error=zero_deposit');
    exit;
}

$sisa_kebutuhan = $target_tabungan; // Saat baru dibuat, terkumpul = 0
$jumlah_hari_dibutuhkan = ceil($sisa_kebutuhan / $nominal_harian_estimasi);
$tanggal_estimasi = date('Y-m-d', strtotime("+$jumlah_hari_dibutuhkan days"));

// --- Query Database ---
try {
    $sql = "INSERT INTO celengan 
            (user_id, nama_tabungan, target_tabungan, mata_uang, rencana_pengisian, nominal_pengisian, terkumpul, tanggal_estimasi) 
            VALUES (:user_id, :nama, :target, :mata_uang, :rencana, :nominal, :terkumpul, :estimasi)";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'user_id' => $user_id, 
        'nama' => $nama_tabungan, 
        'target' => $target_tabungan, 
        'mata_uang' => $mata_uang, 
        'rencana' => $rencana_pengisian, 
        'nominal' => $nominal_pengisian, 
        'terkumpul' => 0.00,
        'estimasi' => $tanggal_estimasi
    ]);

    // Berhasil disimpan, arahkan kembali ke halaman utama
    header('Location: ../../index.php?success=added');
    exit;

} catch (PDOException $e) {
    // Log error
    // error_log("DB Error: " . $e->getMessage());
    header('Location: ../tambah-celengan.php?error=db_fail');
    exit;
}
?>