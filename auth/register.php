<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar CelenganKu</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dark-mode">
    <main class="auth-container">
        <h1>Daftar Akun</h1>
        <?php if (isset($_GET['error'])): ?>
            <p style="color: red; text-align: center;">Pendaftaran gagal! Coba lagi.</p>
        <?php endif; ?>

        <form action="api/proses-register.php" method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
            
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit" class="save-button">Daftar</button>
        </form>
        <p style="text-align: center; margin-top: 15px;">Sudah punya akun? <a href="login.php" style="color: #4CAF50;">Masuk di sini</a></p>
    </main>
</body>
</html>