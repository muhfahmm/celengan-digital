<?php
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/index.php");
    exit;
}

// Tentukan pesan berdasarkan status/error
$message = '';
$messageType = '';

if (isset($_GET['status'])) {
    if ($_GET['status'] == 'failed') {
        $message = 'Username atau password salah!';
        $messageType = 'error';
    } elseif ($_GET['status'] == 'success') {
        $message = 'Akun berhasil dibuat! Silakan login.';
        $messageType = 'success';
    }
}

if (isset($_GET['error'])) {
    $message = $_GET['error'];
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Celengan Digital</title>
    
    <!-- Icons & Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #667eea;
            --primary-hover: #5568d3;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3), transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(138, 92, 246, 0.3), transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(59, 130, 246, 0.2), transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        body.dark {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        }

        body.dark::before {
            background: 
                radial-gradient(circle at 20% 50%, rgba(59, 130, 246, 0.15), transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(139, 92, 246, 0.15), transparent 50%);
        }

        .form-container {
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            padding: 40px;
            border-radius: 24px;
            box-shadow: 
                0 8px 32px 0 rgba(31, 38, 135, 0.2),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.6);
            position: relative;
            z-index: 1;
        }

        body.dark .form-container {
            background: rgba(31, 41, 55, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.4);
        }

        .header h2 {
            font-size: 28px;
            font-weight: 800;
            color: #1F2937;
            margin-bottom: 8px;
        }

        body.dark .header h2 {
            color: #F3F4F6;
        }

        .header p {
            font-size: 14px;
            color: #6B7280;
            font-weight: 500;
        }

        body.dark .header p {
            color: rgba(255, 255, 255, 0.7);
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #DC2626;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10B981;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        body.dark .error {
            background: rgba(239, 68, 68, 0.2);
            color: #FCA5A5;
        }

        body.dark .success {
            background: rgba(16, 185, 129, 0.2);
            color: #6EE7B7;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        body.dark label {
            color: rgba(255, 255, 255, 0.8);
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 18px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px 12px 48px;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            background: rgba(255, 255, 255, 0.8);
            color: #1F2937;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
        }

        body.dark input[type="text"],
        body.dark input[type="email"],
        body.dark input[type="password"] {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            color: #F3F4F6;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--primary-color);
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        body.dark input[type="text"]:focus,
        body.dark input[type="email"]:focus,
        body.dark input[type="password"]:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary-color);
        }

        input::placeholder {
            color: #9CA3AF;
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 
                0 4px 20px rgba(102, 126, 234, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-family: 'Inter', sans-serif;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 
                0 8px 30px rgba(102, 126, 234, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        button:active {
            transform: translateY(0);
        }

        .footer-text {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: #6B7280;
        }

        body.dark .footer-text {
            color: rgba(255, 255, 255, 0.7);
        }

        .footer-text a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .footer-text a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        .theme-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            cursor: pointer;
            font-size: 20px;
            padding: 10px;
            border-radius: 50%;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: #1F2937;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }

        .theme-toggle:hover {
            background: rgba(255, 255, 255, 1);
            transform: scale(1.1) rotate(20deg);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        body.dark .theme-toggle {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        body.dark .theme-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        @media (max-width: 480px) {
            .form-container {
                padding: 30px 24px;
            }

            .header h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div id="darkToggle" class="theme-toggle" title="Ganti Tema">
        <i id="themeIcon" class="bi bi-moon"></i>
    </div>

    <div class="form-container">
        <div class="header">
            <div class="logo">
                <i class="bi bi-piggy-bank-fill"></i>
            </div>
            <h2>Selamat Datang</h2>
            <p>Masuk ke akun Celengan Digital Anda</p>
        </div>

        <?php if ($message): ?>
            <div class="<?= $messageType === 'success' ? 'success' : 'error' ?>">
                <i class="bi <?= $messageType === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle' ?>"></i>
                <span><?= htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <form action="api/proses-login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <i class="bi bi-person input-icon"></i>
                    <input 
                        type="text" 
                        id="username"
                        name="username" 
                        placeholder="Masukkan username"
                        required
                        autocomplete="username"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <i class="bi bi-lock input-icon"></i>
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        placeholder="Masukkan password"
                        required
                        autocomplete="current-password"
                    >
                </div>
            </div>

            <button type="submit">
                <i class="bi bi-box-arrow-in-right"></i>
                <span>Masuk</span>
            </button>
        </form>

        <p class="footer-text">
            Belum punya akun? <a href="register.php">Daftar Sekarang</a>
        </p>
    </div>

    <script>
        // Dark Mode Logic
        const themeIcon = document.getElementById("themeIcon");
        const darkToggle = document.getElementById("darkToggle");
        
        function applyTheme(isDark) {
            if (isDark) {
                document.body.classList.add("dark");
                themeIcon.classList.replace("bi-moon", "bi-sun");
            } else {
                document.body.classList.remove("dark");
                themeIcon.classList.replace("bi-sun", "bi-moon");
            }
        }

        const savedTheme = localStorage.getItem("theme") === "dark";
        applyTheme(savedTheme);

        darkToggle.addEventListener("click", () => {
            const isDark = document.body.classList.toggle("dark");
            localStorage.setItem("theme", isDark ? "dark" : "light");
            applyTheme(isDark);
        });
    </script>
</body>
</html>