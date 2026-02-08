<?php
require_once 'inc/db.php';
session_start();

$error_message = "";
$success_message = "";

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    // REGISTRASI PEMESAN
    if ($action == 'register') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $nama = trim($_POST['nama_pengguna'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $usia = (int) ($_POST['usia'] ?? 0);
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $clue = trim($_POST['clue_password'] ?? '');

        if ($password !== $confirm_password) {
            $error_message = "Konfirmasi password tidak cocok!";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error_message = "Username atau Email sudah terdaftar!";
            } else {
                $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, nama_pengguna, alamat, usia, password, clue_password) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$username, $email, $nama, $alamat, $usia, $hashed_pass, $clue])) {
                    $success_message = "Registrasi Berhasil! Silakan Login.";
                } else {
                    $error_message = "Gagal mendaftarkan akun. Coba lagi.";
                }
            }
        }
    }

    // LOGIN PEMESAN
    else if ($action == 'login_user') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            $error_message = "Username dan Passwordmu Belum Terdaftar. Daftar Terlebih Dahulu";
        } else {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nama'] = $user['nama_pengguna'];
                header("Location: dashboard.php");
                exit();
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
    <title>Login Pemesan | Luxury Shope</title>
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
            background: linear-gradient(to right, var(--primary-main), var(--secondary-main));
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

        .alert-success {
            background: #f0fff4;
            color: var(--success);
            border: 1px solid #c6f6d5;
        }

        .auth-options {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }

        .auth-options a {
            color: var(--text-muted);
            text-decoration: none;
            transition: var(--transition);
        }

        .auth-options a:hover {
            color: var(--secondary-main);
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
            max-width: 600px;
            padding: 35px;
            border-radius: var(--radius-lg);
            background: var(--white);
            box-shadow: var(--shadow-medium);
        }

        .grid-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 500px) {
            .grid-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div id="mainContainer" class="animate-fade">
        <div class="login-wrapper animate-up" style="margin: 10vh auto;">
            <div class="login-card glass">
                <div class="brand-logo" style="font-size: 40px; margin-bottom: 10px; color: var(--primary-main);">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <h1 class="auth-title">Login Pemesan</h1>
                <p class="auth-subtitle">Selamat datang kembali di Luxury Shope</p>

                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>
                            <?= $error_message ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span>
                            <?= $success_message ?>
                        </span>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <input type="hidden" name="action" value="login_user">

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-input" placeholder="Masukkan username anda"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-input" placeholder="Masukkan password anda"
                            required>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Masuk Sekarang
                    </button>

                    <div class="auth-options">
                        <a href="javascript:void(0)" onclick="openModal('register')">Daftar Akun</a>
                        <a href="javascript:void(0)" onclick="openModal('forgot_user')">Lupa Password?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modals Overlay -->
    <div id="authOverlay">
        <div id="registerModal" class="modal-body animate-up" style="display: none;">
            <h2 class="auth-title">Daftar Akun</h2>
            <p class="auth-subtitle">Lengkapi data diri anda untuk mulai berbelanja</p>

            <form action="" method="POST">
                <input type="hidden" name="action" value="register">
                <div class="grid-form">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Pengguna</label>
                        <input type="text" name="nama_pengguna" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label>Usia</label>
                        <input type="number" name="usia" class="form-input" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" class="form-input" rows="2" required></textarea>
                </div>
                <div class="grid-form">
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password</label>
                        <input type="password" name="confirm_password" class="form-input" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Clue Lupa Password</label>
                    <input type="text" name="clue_password" class="form-input" required>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="flex: 2;">Daftar Sekarang</button>
                    <button type="button" class="btn" style="flex: 1; background: #eee;"
                        onclick="closeModal()">Kembali</button>
                </div>
            </form>
        </div>

        <div id="forgotUserModal" class="modal-body animate-up" style="display: none; max-width: 450px;">
            <h2 class="auth-title">Lupa Password</h2>
            <p class="auth-subtitle">Gunakan clue keamanan anda</p>
            <div class="form-group">
                <label>Username</label>
                <input type="text" id="forgot_username" class="form-input" placeholder="Username anda">
            </div>
            <button type="button" class="btn btn-primary" style="width:100%"
                onclick="alert('Fitur ini akan mengecek clue di database.')">Cek Clue</button>
            <button type="button" class="btn" style="width:100%; margin-top:10px; background:#eee"
                onclick="closeModal()">Tutup</button>
        </div>
    </div>

    <script>
        const overlay = document.getElementById('authOverlay');
        const mainCont = document.getElementById('mainContainer');

        function openModal(type) {
            overlay.style.display = 'flex';
            mainCont.classList.add('blur-bg');
            document.getElementById('registerModal').style.display = (type === 'register') ? 'block' : 'none';
            document.getElementById('forgotUserModal').style.display = (type === 'forgot_user') ? 'block' : 'none';
        }

        function closeModal() {
            overlay.style.display = 'none';
            mainCont.classList.remove('blur-bg');
        }
    </script>
</body>

</html>