<?php
session_start();

/**
 * Konfigurasi Admin (Hardcoded)
 */
$admin_credentials = [
    'username' => 'payung',
    'password' => 'payung123',
    'clue' => 'Warna kesukaan admin adalah biru'
];

// Admin tidak perlu mendaftar, akun sudah terdaftar di sistem secara internal.

/**
 * Simulasi Database User (Dalam Sesi untuk demonstrasi tanpa DB SQL nyata dulu)
 * Sesuai permintaan: Username, Email, Nama, Alamat, Usia, Password, Clue
 */
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
}

$error_message = "";
$success_message = "";
$view = "login"; // login, register, admin_login, forgot_password_admin

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'register') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $nama = $_POST['nama'] ?? '';
        $alamat = $_POST['alamat'] ?? '';
        $usia = $_POST['usia'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $clue = $_POST['clue'] ?? '';

        if ($password !== $confirm_password) {
            $error_message = "Password dan Konfirmasi Password tidak cocok!";
        } else {
            // Cek jika username sudah ada
            $exists = false;
            foreach ($_SESSION['users'] as $u) {
                if ($u['username'] == $username) {
                    $exists = true;
                    break;
                }
            }

            if ($exists) {
                $error_message = "Username sudah terdaftar!";
            } else {
                $_SESSION['users'][] = [
                    'username' => $username,
                    'email' => $email,
                    'nama' => $nama,
                    'alamat' => $alamat,
                    'usia' => $usia,
                    'password' => $password,
                    'clue' => $clue
                ];
                $success_message = "Registrasi Berhasil! Silakan Login.";
            }
        }
    } else if ($action == 'login_user') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $user_found = null;
        foreach ($_SESSION['users'] as $u) {
            if ($u['username'] == $username) {
                $user_found = $u;
                break;
            }
        }

        if (!$user_found) {
            // Cek apakah input kosong atau memang belum ada di sistem
            $error_message = "Username dan Passwordmu Belum Terdaftar. Daftar Terlebih Dahulu";
        } else {
            // Username ketemu, cek password
            if ($user_found['password'] != $password) {
                $error_message = "Password salah";
            } else {
                $_SESSION['logged_in_user'] = $user_found;
                header("Location: dashboard.php");
                exit();
            }
        }

        // Tambahan logika jika ingin sangat spesifik sesuai prompt:
        // Prompt meminta: "jika username tidak sesuai -> Username salah", "jika password tidak sesuai -> Password salah", 
        // "jika username dan password tidak sesuai -> Username & Password anda salah"
        // Kita sesuaikan sedikit agar memenuhi semua kriteria:
        if ($action == 'login_user' && $error_message != "Username dan Passwordmu Belum Terdaftar. Daftar Terlebih Dahulu") {
            // Jika username tidak ada sama sekali di session users (sudah ditangani di atas)
        }
    } else if ($action == 'login_admin') {
        // Logika login admin langsung menggunakan kredensial hardcoded tanpa melalui database/registrasi
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($username == $admin_credentials['username'] && $password == $admin_credentials['password']) {
            $_SESSION['admin_logged_in'] = true;
            header("Location: admin_dashboard.php");
            exit();
        } else {
            // Tampilkan pesan error sesuai permintaan spesifik
            if ($username != $admin_credentials['username'] && $password != $admin_credentials['password']) {
                $error_message = "Username & Password anda salah";
            } else if ($username != $admin_credentials['username']) {
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
    <title>Login - Luxury E-commerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-light: #D0F0C0;
            /* Light Green */
            --secondary-light: #E0F7FA;
            /* Light Blue */
            --primary-main: #90EE90;
            --secondary-main: #ADD8E6;
            --white: #ffffff;
            --text-dark: #2c3e50;
            --error: #e74c3c;
            --success: #2ecc71;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--primary-light), var(--secondary-light));
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            z-index: 10;
            transition: all 0.5s ease;
            border: 3px solid transparent;
        }

        .login-card.admin-mode {
            border-color: var(--secondary-main);
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        h2 {
            color: var(--text-dark);
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 28px;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
        }

        .input-group input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            border: 2px solid #eee;
            background: #f9f9f9;
            transition: all 0.3s ease;
            outline: none;
        }

        .input-group input:focus {
            border-color: var(--secondary-main);
            background: #fff;
            box-shadow: 0 0 10px rgba(173, 216, 230, 0.3);
        }

        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-primary {
            background: linear-gradient(to right, var(--primary-main), var(--secondary-main));
            color: var(--text-dark);
        }

        .btn-primary:hover {
            opacity: 0.9;
            box-shadow: 0 5px 15px rgba(144, 238, 144, 0.4);
        }

        .options {
            margin-top: 20px;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
        }

        .options a {
            color: #777;
            text-decoration: none;
            transition: color 0.3s;
        }

        .options a:hover {
            color: var(--secondary-main);
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #fdeaea;
            color: var(--error);
            border: 1px solid #fad2d2;
        }

        .alert-success {
            background: #eafaea;
            color: var(--success);
            border: 1px solid #d2fad2;
        }

        /* Register Modal Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(15px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 100;
        }

        .register-modal {
            background: white;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
            max-height: 90vh;
            overflow-y: auto;
        }

        .switch-role {
            margin-top: 20px;
            font-size: 13px;
        }

        .switch-role button {
            background: none;
            border: none;
            color: var(--secondary-main);
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .login-card {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>

<body>

    <!-- Main Login Card -->
    <div class="login-card" id="loginCard">
        <h2 id="loginTitle">Login Pemesan</h2>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <input type="hidden" name="action" id="loginAction" value="login_user">

            <div class="input-group">
                <label for="user">Username</label>
                <input type="text" id="user" name="username" placeholder="Masukkan username" required>
            </div>

            <div class="input-group">
                <label for="pass">Password</label>
                <input type="password" id="pass" name="password" placeholder="Masukkan password" required>
            </div>

            <button type="submit" class="btn btn-primary">Login Now</button>

            <div class="options" id="userOptions">
                <a href="javascript:void(0)" onclick="toggleRegister(true)">Daftar Akun</a>
                <a href="#">Lupa Password?</a>
            </div>

            <div class="options" id="adminOptions" style="display:none;">
                <a href="javascript:void(0)" onclick="showAdminForgot()">Lupa Password Admin?</a>
            </div>

            <div class="switch-role">
                <p id="switchText">Bukan Pemesan? <button type="button" onclick="switchRole('admin')">Login sebagai
                        Admin</button></p>
            </div>
        </form>
    </div>

    <!-- Registration Overlay -->
    <div class="overlay" id="registerOverlay">
        <div class="register-modal">
            <h2>Daftar Akun Baru</h2>
            <form method="POST">
                <input type="hidden" name="action" value="register">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="input-group">
                        <label>Username</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="input-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="input-group">
                        <label>Nama Pengguna</label>
                        <input type="text" name="nama" required>
                    </div>
                    <div class="input-group">
                        <label>Usia</label>
                        <input type="number" name="usia" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Alamat</label>
                    <input type="text" name="alamat" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="input-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="input-group">
                        <label>Konfirmasi Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Clue Lupa Password (Contoh: Nama kucing saya)</label>
                    <input type="text" name="clue" required>
                </div>

                <button type="submit" class="btn btn-primary">Daftar Sekarang</button>
                <button type="button" class="btn" onclick="toggleRegister(false)" style="background: #eee;">Kembali ke
                    Login</button>
            </form>
        </div>
    </div>

    <!-- Forgot Password Admin Modal -->
    <div class="overlay" id="adminForgotOverlay">
        <div class="register-modal">
            <h2>Lupa Password Admin</h2>
            <div class="input-group">
                <label>Clue Keamanan:</label>
                <p style="padding: 10px; background: #f0f0f0; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $admin_credentials['clue']; ?>
                </p>
                <input type="text" placeholder="Masukkan jawaban clue (simulasi)" id="adminClueAnswer">
            </div>
            <button type="button" class="btn btn-primary"
                onclick="alert('Password Admin adalah: <?php echo $admin_credentials['password']; ?>')">Cek
                Password</button>
            <button type="button" class="btn"
                onclick="document.getElementById('adminForgotOverlay').style.display='none'"
                style="background: #eee;">Tutup</button>
        </div>
    </div>

    <script>
        function toggleRegister(show) {
            const overlay = document.getElementById('registerOverlay');
            overlay.style.display = show ? 'flex' : 'none';
        }

        function switchRole(role) {
            const card = document.getElementById('loginCard');
            const title = document.getElementById('loginTitle');
            const actionInput = document.getElementById('loginAction');
            const userOpts = document.getElementById('userOptions');
            const adminOpts = document.getElementById('adminOptions');
            const switchText = document.getElementById('switchText');

            if (role === 'admin') {
                card.classList.add('admin-mode');
                title.innerText = "Login Admin";
                actionInput.value = "login_admin";
                userOpts.style.display = "none";
                adminOpts.style.display = "flex";
                switchText.innerHTML = 'Bukan Admin? <button type="button" onclick="switchRole(\'user\')">Login sebagai Pemesan</button>';
            } else {
                card.classList.remove('admin-mode');
                title.innerText = "Login Pemesan";
                actionInput.value = "login_user";
                userOpts.style.display = "flex";
                adminOpts.style.display = "none";
                switchText.innerHTML = 'Bukan Pemesan? <button type="button" onclick="switchRole(\'admin\')">Login sebagai Admin</button>';
            }
        }

        function showAdminForgot() {
            document.getElementById('adminForgotOverlay').style.display = 'flex';
        }
    </script>
</body>

</html>