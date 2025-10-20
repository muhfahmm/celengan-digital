<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Masuk CelenganKu</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dark-mode">
    <main class="auth-container">
        <h1>Masuk Akun</h1>
        <?php if (isset($_GET['success']) && $_GET['success'] == 'registered'): ?>
            <p style="color: #4CAF50; text-align: center;">Pendaftaran berhasil! Silakan masuk.</p>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <p style="color: red; text-align: center;">Username atau password salah.</p>
        <?php endif; ?>

        <form action="api/proses-login.php" method="POST">
            <label for="username">Username/Email</label>
            <input type="text" id="username" name="username_email" required>
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit" class="save-button">Masuk</button>
        </form>
        <p style="text-align: center; margin-top: 15px;">Belum punya akun? <a href="register.php" style="color: #4CAF50;">Daftar di sini</a></p>
    </main>
</body>
</html>