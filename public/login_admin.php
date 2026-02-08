<?php
session_start();

/**
 * Hardcoded Admin Credentials
 */
define('ADMIN_USER', 'payung');
define('ADMIN_PASS', 'payung123');
define('ADMIN_CLUE', 'Warna kesukaan admin adalah biru');

$error_message = "";

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'login_admin') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === ADMIN_USER && $password === ADMIN_PASS) {
            $_SESSION['admin_logged_in'] = true;
            header("Location: admin_dashboard.php");
            exit();
        } else {
            if ($username !== ADMIN_USER && $password !== ADMIN_PASS) {
                $error_message = "Username & Password anda salah";
            } else if ($username !== ADMIN_USER) {
                $error_message = "Username salah";
            } else {
                $error_message = "Password salah";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | Luxury Shope</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            padding: 40px;
            border-radius: var(--radius-lg);
            text-align: center;
            box-shadow: var(--shadow-medium);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--secondary-main), var(--primary-main));
        }

        .auth-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-dark);
        }

        .auth-subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 30px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
        }

        .alert-error {
            background: #fff5f5;
            color: var(--error);
            border: 1px solid #fed7d7;
        }

        #authOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(15px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            overflow-y: auto;
            padding: 20px;
        }

        .modal-body {
            width: 100%;
            max-width: 400px;
            padding: 35px;
            border-radius: var(--radius-lg);
            background: var(--white);
            box-shadow: var(--shadow-medium);
            text-align: center;
        }
    </style>
</head>

<body>

    <div id="mainContainer" class="animate-fade">
        <div class="login-wrapper animate-up" style="margin: 15vh auto;">
            <div class="login-card glass">
                <div class="brand-logo" style="font-size: 40px; margin-bottom: 10px; color: var(--secondary-main);">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h1 class="auth-title">Login Admin</h1>
                <p class="auth-subtitle">Panel Kontrol Master Luxury Shope</p>

                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>
                            <?= $error_message ?>
                        </span>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <input type="hidden" name="action" value="login_admin">

                    <div class="form-group">
                        <label>Username Admin</label>
                        <input type="text" name="username" class="form-input" placeholder="Masukkan username admin"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Password Admin</label>
                        <input type="password" name="password" class="form-input" placeholder="Masukkan password admin"
                            required>
                    </div>

                    <button type="submit" class="btn btn-primary"
                        style="width: 100%; background: var(--secondary-main);">
                        Masuk Panel Admin
                    </button>

                    <div style="margin-top: 20px; font-size: 14px;">
                        <a href="javascript:void(0)" onclick="openModal()"
                            style="color: var(--text-muted); text-decoration: none;">Lupa Password Admin?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Forgot Admin Modal -->
    <div id="authOverlay">
        <div class="modal-body animate-up">
            <h2 class="auth-title">Lupa Password Admin</h2>
            <p class="auth-subtitle">Masukkan clue keamanan anda</p>

            <div class="form-group">
                <p
                    style="padding: 15px; background: #f8f9fa; border-radius: var(--radius-md); font-size: 14px; margin-bottom: 20px; border-left: 4px solid var(--secondary-main); text-align: left;">
                    <strong>Clue Keamanan:</strong>
                    <?= ADMIN_CLUE ?>
                </p>
                <input type="text" id="adminClueInput" class="form-input" placeholder="Jawaban clue">
            </div>

            <button type="button" class="btn btn-primary" style="width: 100%;" onclick="checkAdminClue()">Cari
                Password</button>
            <button type="button" class="btn" style="width: 100%; margin-top: 10px; background: #eee;"
                onclick="closeModal()">Tutup</button>
        </div>
    </div>

    <script>
        const overlay = document.getElementById('authOverlay');
        const mainCont = document.getElementById('mainContainer');

        function openModal() {
            overlay.style.display = 'flex';
            mainCont.classList.add('blur-bg');
        }

        function closeModal() {
            overlay.style.display = 'none';
            mainCont.classList.remove('blur-bg');
        }

        function checkAdminClue() {
            const val = document.getElementById('adminClueInput').value;
            if (val.toLowerCase().includes('biru')) {
                alert("Password Admin adalah: <?= ADMIN_PASS ?>");
            } else {
                alert("Jawaban clue salah!");
            }
        }
    </script>
</body>

</html>