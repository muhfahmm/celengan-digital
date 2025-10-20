<?php
session_start();
include('../../config/db_connect.php');

// Pastikan hanya POST request yang diterima dan user sudah login
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil dan bersihkan data dari POST
$nama_tabungan = $_POST['nama_tabungan'] ?? '';
$target_tabungan = filter_var($_POST['target_tabungan'] ?? 0, FILTER_VALIDATE_FLOAT);
$mata_uang = $_POST['mata_uang'] ?? 'IDR';
$rencana_pengisian = $_POST['rencana_pengisian'] ?? '';
$nominal_pengisian = filter_var($_POST['nominal_pengisian'] ?? 0, FILTER_VALIDATE_FLOAT);

// Validasi dasar
if (empty($nama_tabungan) || $target_tabungan <= 0 || $nominal_pengisian <= 0) {
    // Lebih baik kembali ke form dengan pesan error
    header('Location: ../tambah-celengan.php?error=invalid_data');
    exit;
}

// Hitung estimasi tanggal tercapai (sederhana: hanya berdasarkan harian)
// Asumsi: jika rencana_pengisian bukan harian, nominal_pengisian dikonversi ke harian
$nominal_harian = $nominal_pengisian;
if ($rencana_pengisian == 'Mingguan') {
    $nominal_harian = $nominal_pengisian / 7;
} elseif ($rencana_pengisian == 'Bulanan') {
    $nominal_harian = $nominal_pengisian / 30; // Asumsi 30 hari/bulan
}

$sisa_kebutuhan = $target_tabungan;
$jumlah_hari_dibutuhkan = ceil($sisa_kebutuhan / $nominal_harian);
$tanggal_estimasi = date('Y-m-d', strtotime("+$jumlah_hari_dibutuhkan days"));


// Query untuk memasukkan data
$sql = "INSERT INTO celengan 
        (user_id, nama_tabungan, target_tabungan, mata_uang, rencana_pengisian, nominal_pengisian, tanggal_estimasi) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "isdsdds", 
    $user_id, 
    $nama_tabungan, 
    $target_tabungan, 
    $mata_uang, 
    $rencana_pengisian, 
    $nominal_pengisian, 
    $tanggal_estimasi
);

if ($stmt->execute()) {
    // Berhasil disimpan, arahkan kembali ke halaman utama
    header('Location: ../../index.php?success=added');
    exit;
} else {
    // Gagal menyimpan, kembali ke form
    // echo "Error: " . $stmt->error;
    header('Location: ../tambah-celengan.php?error=db_fail');
    exit;
}

$stmt->close();
$conn->close();
?>