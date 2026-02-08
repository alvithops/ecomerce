<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['logged_in_user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['logged_in_user'];

/**
 * Simulasi Data Produk
 */
$products = [
    [
        'id' => 1,
        'name' => 'Premium Gaming Laptop Z1',
        'price' => 15000000,
        'discount' => '20%',
        'image' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?auto=format&fit=crop&w=300&q=80',
        'rating' => 4.8
    ],
    [
        'id' => 2,
        'name' => 'Wireless Headphones Noise-X',
        'price' => 1200000,
        'discount' => '10%',
        'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=300&q=80',
        'rating' => 4.5
    ],
    [
        'id' => 3,
        'name' => 'Smartwatch Series 7 Luxury',
        'price' => 3500000,
        'discount' => null,
        'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=300&q=80',
        'rating' => 4.6
    ],
    [
        'id' => 4,
        'name' => 'Ergonomic Desk Chair Pro',
        'price' => 2500000,
        'discount' => '15%',
        'image' => 'https://images.unsplash.com/photo-1505843490701-515a007bc0c5?auto=format&fit=crop&w=300&q=80',
        'rating' => 4.9
    ],
    [
        'id' => 5,
        'name' => 'Mechanical Keyboard RGB',
        'price' => 850000,
        'discount' => '5%',
        'image' => 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?auto=format&fit=crop&w=300&q=80',
        'rating' => 4.7
    ],
    [
        'id' => 6,
        'name' => '4K Ultra Slim Monitor 27"',
        'price' => 4200000,
        'discount' => null,
        'image' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=300&q=80',
        'rating' => 4.4
    ],
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Luxury Shope</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-light: #D0F0C0;
            --secondary-light: #E0F7FA;
            --primary-main: #90EE90;
            --secondary-main: #ADD8E6;
            --white: #ffffff;
            --text-dark: #2c3e50;
            --bg-gray: #f8f9fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-gray);
            padding-top: 70px;
            /* Header space */
            padding-bottom: 80px;
            /* Nav space */
            color: var(--text-dark);
            min-height: 100vh;
        }

        /* Header */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(to right, var(--primary-main), var(--secondary-main));
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-logo {
            font-weight: 600;
            font-size: 20px;
            color: var(--text-dark);
        }

        .search-container {
            flex: 1;
            margin: 0 20px;
            position: relative;
        }

        .search-container input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border-radius: 20px;
            border: none;
            outline: none;
            background: rgba(255, 255, 255, 0.8);
        }

        .search-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
        }

        /* Bottom Nav */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
            z-index: 1000;
        }

        .nav-item {
            text-align: center;
            color: #999;
            text-decoration: none;
            font-size: 11px;
            flex: 1;
            transition: all 0.3s;
        }

        .nav-item i {
            display: block;
            font-size: 20px;
            margin-bottom: 4px;
        }

        .nav-item.active {
            color: var(--primary-main);
        }

        .nav-item.active i {
            color: var(--primary-main);
        }

        /* Content Sections */
        .content-section {
            display: none;
            padding: 20px;
            animation: fadeIn 0.4s ease;
        }

        .content-section.active {
            display: block;
        }

        /* Beranda Products */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 15px;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
            cursor: pointer;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-3px);
        }

        .product-img {
            width: 100%;
            height: 150px;
            background-size: cover;
            background-position: center;
        }

        .product-info {
            padding: 10px;
        }

        .product-name {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-price {
            color: var(--primary-main);
            font-weight: 600;
            font-size: 15px;
        }

        .product-rating {
            font-size: 12px;
            color: #f1c40f;
            margin-top: 5px;
        }

        .discount-tag {
            position: absolute;
            top: 10px;
            right: 0;
            background: #ff4757;
            color: white;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 10px 0 0 10px;
        }

        /* Profile Styles */
        .profile-header {
            background: white;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--secondary-light);
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 15px;
            font-size: 30px;
            color: var(--secondary-main);
            border: 3px solid var(--primary-main);
        }

        .profile-menu {
            background: white;
            border-radius: 20px;
            overflow: hidden;
        }

        .menu-item {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background 0.2s;
        }

        .menu-item:hover {
            background: #f9f9f9;
        }

        .menu-item i {
            width: 30px;
            font-size: 18px;
            color: #777;
        }

        .menu-item .menu-text {
            flex: 1;
            font-size: 15px;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (min-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 25px;
            }

            .product-img {
                height: 200px;
            }
        }
    </style>
</head>

<body>

    <header>
        <div class="header-logo">LUXURY SHOPE</div>
        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Cari barang mewah anda...">
        </div>
        <a href="logout.php" style="color: var(--text-dark);"><i class="fas fa-sign-out-alt"></i></a>
    </header>

    <!-- Menu Sections -->
    <main id="mainContent">
        <!-- BERANDA -->
        <section id="beranda" class="content-section active">
            <h3 style="margin-bottom: 15px;">Rekomendasi Produk</h3>
            <div class="product-grid">
                <?php foreach ($products as $p): ?>
                    <div class="product-card" onclick="location.href='product_detail.php?id=<?php echo $p['id']; ?>'">
                        <div class="product-img" style="background-image: url('<?php echo $p['image']; ?>')"></div>
                        <?php if ($p['discount']): ?>
                            <div class="discount-tag">
                                <?php echo $p['discount']; ?> OFF
                            </div>
                        <?php endif; ?>
                        <div class="product-info">
                            <div class="product-name">
                                <?php echo $p['name']; ?>
                            </div>
                            <div class="product-price">Rp
                                <?php echo number_format($p['price'], 0, ',', '.'); ?>
                            </div>
                            <div class="product-rating">
                                <i class="fas fa-star"></i>
                                <?php echo $p['rating']; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- HISTORY -->
        <section id="history" class="content-section">
            <h3>Riwayat Pesanan</h3>
            <div style="text-align: center; margin-top: 50px; color: #999;">
                <i class="fas fa-receipt" style="font-size: 50px; margin-bottom: 15px; opacity: 0.5;"></i>
                <p>Belum ada riwayat pesanan.</p>
            </div>
        </section>

        <!-- NOTIFICATION -->
        <section id="notification" class="content-section">
            <h3>Notifikasi</h3>
            <div class="menu-item" style="border-radius: 12px; background: #fff; margin-bottom: 10px;">
                <i class="fas fa-tag" style="color: #ff4757;"></i>
                <div class="menu-text">
                    <strong>Diskon Spesial!</strong>
                    <p style="font-size: 12px; color: #777;">Dapatkan diskon 50% untuk laptop gaming.</p>
                </div>
            </div>
            <div class="menu-item" style="border-radius: 12px; background: #fff; margin-bottom: 10px;">
                <i class="fas fa-info-circle" style="color: var(--secondary-main);"></i>
                <div class="menu-text">
                    <strong>Info dari Admin</strong>
                    <p style="font-size: 12px; color: #777;">Selamat bergabung di Luxury Shope.</p>
                </div>
            </div>
        </section>

        <!-- PROFILE -->
        <section id="profile" class="content-section">
            <div class="profile-header">
                <div class="profile-img">
                    <i class="fas fa-user"></i>
                </div>
                <h2>
                    <?php echo htmlspecialchars($user['nama']); ?>
                </h2>
                <p style="color: #777; font-size: 14px;">@
                    <?php echo htmlspecialchars($user['username']); ?>
                </p>
            </div>

            <div class="profile-menu">
                <div class="menu-item">
                    <i class="fas fa-heart" style="color: #ff4757;"></i>
                    <div class="menu-text">Daftar Suka</div>
                    <span style="background: #eee; padding: 2px 8px; border-radius: 10px; font-size: 12px;">0</span>
                </div>
                <div class="menu-item">
                    <i class="fas fa-bookmark" style="color: #2ecc71;"></i>
                    <div class="menu-text">Daftar Simpan</div>
                    <span style="background: #eee; padding: 2px 8px; border-radius: 10px; font-size: 12px;">0</span>
                </div>
                <div class="menu-item">
                    <i class="fas fa-wallet" style="color: #f1c40f;"></i>
                    <div class="menu-text">Belum Dibayar</div>
                </div>
                <div class="menu-item">
                    <i class="fas fa-box" style="color: var(--secondary-main);"></i>
                    <div class="menu-text">Sedang Dikemas</div>
                </div>
                <div class="menu-item">
                    <i class="fas fa-truck" style="color: var(--primary-main);"></i>
                    <div class="menu-text">Dalam Pengiriman</div>
                </div>
                <div class="menu-item">
                    <i class="fas fa-cog"></i>
                    <div class="menu-text">Pengaturan Akun</div>
                </div>
            </div>
        </section>
    </main>

    <!-- Bottom Nav -->
    <nav class="bottom-nav">
        <a href="javascript:void(0)" class="nav-item active" onclick="showSection('beranda', this)">
            <i class="fas fa-home"></i>
            <span>Beranda</span>
        </a>
        <a href="javascript:void(0)" class="nav-item" onclick="showSection('history', this)">
            <i class="fas fa-history"></i>
            <span>Riwayat</span>
        </a>
        <a href="javascript:void(0)" class="nav-item" onclick="showSection('notification', this)">
            <i class="fas fa-bell"></i>
            <span>Notif</span>
        </a>
        <a href="javascript:void(0)" class="nav-item" onclick="showSection('profile', this)">
            <i class="fas fa-user-circle"></i>
            <span>Profil</span>
        </a>
    </nav>

    <script>
        function showSection(sectionId, element) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
            // Deactivate all nav items
            document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));

            // Show selected section
            document.getElementById(sectionId).classList.add('active');
            // Activate selected nav item
            element.classList.add('active');

            // Header dynamics
            const header = document.querySelector('header');
            if (sectionId === 'profile') {
                header.style.background = 'white';
            } else {
                header.style.background = 'linear-gradient(to right, var(--primary-main), var(--secondary-main))';
            }
        }
    </script>
</body>

</html>