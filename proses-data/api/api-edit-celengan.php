<?php
session_start();
include('../../config/db.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$celengan_id = $_POST['celengan_id'] ?? null;
$action = $_POST['action'] ?? '';

if (!$celengan_id) {
    header('Location: ../../index.php?error=no_id');
    exit;
}

try {
    // 1. TAMBAH PENGISIAN (DEPOSIT)
    if ($action === 'tambah_pengisian') {
        $jumlah = filter_var($_POST['jumlah_pengisian'] ?? 0, FILTER_VALIDATE_FLOAT);

        if ($jumlah <= 0) {
            header('Location: ../edit-celengan.php?id=' . $celengan_id . '&error=invalid_amount');
            exit;
        }

        // Mulai transaksi database
        $pdo->beginTransaction();

        // A. Update total terkumpul di tabel celengan
        $stmt_update = $pdo->prepare("UPDATE celengan SET terkumpul = terkumpul + ? WHERE id = ? AND user_id = ?");
        $stmt_update->execute([$jumlah, $celengan_id, $user_id]);

        // B. Masukkan ke tabel transaksi_pengisian (untuk riwayat)
        $stmt_insert = $pdo->prepare("INSERT INTO transaksi_pengisian (celengan_id, jumlah) VALUES (?, ?)");
        $stmt_insert->execute([$celengan_id, $jumlah]);

        // C. Cek apakah target sudah tercapai
        $stmt_check = $pdo->prepare("SELECT target_tabungan, terkumpul FROM celengan WHERE id = ?");
        $stmt_check->execute([$celengan_id]);
        $celengan_data = $stmt_check->fetch();

        if ($celengan_data && $celengan_data['terkumpul'] >= $celengan_data['target_tabungan']) {
             $stmt_status = $pdo->prepare("UPDATE celengan SET status = 'Tercapai' WHERE id = ?");
             $stmt_status->execute([$celengan_id]);
        }

        $pdo->commit();

        header('Location: ../edit-celengan.php?id=' . $celengan_id . '&success=deposit_added');
        exit;
    } 
    
    // 2. EDIT RENCANA (TARGET/NOMINAL)
    elseif ($action === 'edit_rencana') {
        $nama_tabungan = $_POST['nama_tabungan'] ?? '';
        $target_tabungan = filter_var($_POST['target_tabungan'] ?? 0, FILTER_VALIDATE_FLOAT);
        $nominal_pengisian = filter_var($_POST['nominal_pengisian'] ?? 0, FILTER_VALIDATE_FLOAT);

        if (empty($nama_tabungan) || $target_tabungan <= 0 || $nominal_pengisian <= 0) {
            header('Location: ../edit-celengan.php?id=' . $celengan_id . '&error=invalid_data');
            exit;
        }
        
        // Asumsi: Kita perlu mengambil data lama untuk menghitung ulang estimasi
        $stmt_old = $pdo->prepare("SELECT terkumpul, rencana_pengisian FROM celengan WHERE id = ?");
        $stmt_old->execute([$celengan_id]);
        $old_data = $stmt_old->fetch();
        
        $terkumpul = $old_data['terkumpul'];
        $rencana_pengisian = $old_data['rencana_pengisian'];

        // Hitung ulang tanggal estimasi
        $nominal_harian = $nominal_pengisian;
        if ($rencana_pengisian == 'Mingguan') {
            $nominal_harian = $nominal_pengisian / 7;
        } elseif ($rencana_pengisian == 'Bulanan') {
            $nominal_harian = $nominal_pengisian / 30; 
        }

        $sisa_kebutuhan = $target_tabungan - $terkumpul;
        $jumlah_hari_dibutuhkan = ceil($sisa_kebutuhan / $nominal_harian);
        $tanggal_estimasi = date('Y-m-d', strtotime("+$jumlah_hari_dibutuhkan days"));
        
        // Update data celengan
        $stmt = $pdo->prepare("UPDATE celengan SET 
            nama_tabungan = ?, 
            target_tabungan = ?, 
            nominal_pengisian = ?,
            tanggal_estimasi = ?,
            status = ?
            WHERE id = ? AND user_id = ?");
            
        $status = ($terkumpul >= $target_tabungan) ? 'Tercapai' : 'Berlangsung';

        $stmt->execute([
            $nama_tabungan, 
            $target_tabungan, 
            $nominal_pengisian, 
            $tanggal_estimasi,
            $status,
            $celengan_id, 
            $user_id
        ]);
        
        header('Location: ../edit-celengan.php?id=' . $celengan_id . '&success=updated');
        exit;
    }

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // error_log($e->getMessage());
    header('Location: ../edit-celengan.php?id=' . $celengan_id . '&error=db_fail');
    exit;
}
?>