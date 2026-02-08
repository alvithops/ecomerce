<?php
require_once 'inc/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login_pemesan.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_nama = $_SESSION['user_nama'] ?? 'User';

// Fetch Products from Database
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();

// Simulated notifications count
$notif_count = 3;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Luxury Shope</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            padding-bottom: 80px;
            background: #fdfdfd;
        }

        .header-main {
            background: var(--white);
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-soft);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-inner {
            background: #f1f1f1;
            padding: 10px 15px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            margin: 0 15px;
        }

        .search-inner input {
            border: none;
            background: none;
            outline: none;
            width: 100%;
            font-size: 14px;
        }

        /* Product Grid */
        .product-container {
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 15px;
        }

        .p-card {
            background: var(--white);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-soft);
            transition: var(--transition);
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
        }

        .p-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .p-img-box {
            width: 100%;
            height: 180px;
            background: #f8f8f8;
            position: relative;
        }

        .p-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .discount-tag {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff4757;
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 600;
        }

        .p-info {
            padding: 12px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .p-name {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            height: 40px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .p-price {
            font-size: 16px;
            font-weight: 600;
            color: #ff4757;
        }

        .p-rating {
            font-size: 11px;
            color: #777;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Bottom Nav */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: var(--white);
            display: flex;
            justify-content: space-around;
            padding: 12px 0;
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.05);
            z-index: 1000;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #bdc3c7;
            font-size: 11px;
            gap: 5px;
            transition: var(--transition);
        }

        .nav-item.active {
            color: var(--primary-main);
        }

        .nav-item i {
            font-size: 20px;
        }

        /* Section switcher */
        .dashboard-section {
            display: none;
        }

        .dashboard-section.active {
            display: block;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <header class="header-main glass">
        <div class="logo" style="font-weight: 600; font-size: 18px; color: var(--primary-main);">LuxuryShope</div>
        <div class="search-inner">
            <i class="fas fa-search" style="color: #999;"></i>
            <input type="text" placeholder="Cari di Luxury Shope...">
        </div>
        <div class="cart-icon" style="position: relative;">
            <i class="fas fa-shopping-cart" style="font-size: 20px; color: #555;"></i>
            <span
                style="position: absolute; top: -10px; right: -10px; background: #ff4757; color: white; width: 18px; height: 18px; border-radius: 50%; font-size: 10px; display: flex; align-items: center; justify-content: center;">2</span>
        </div>
    </header>

    <!-- SECTION: BERANDA -->
    <div id="beranda" class="dashboard-section active animate-fade">
        <div class="banner" style="padding: 20px;">
            <div
                style="width: 100%; height: 150px; background: linear-gradient(to right, var(--primary-main), var(--secondary-main)); border-radius: var(--radius-md); padding: 25px; color: var(--text-dark);">
                <h2 style="font-size: 20px;">Promo Gajian!</h2>
                <p style="font-size: 14px; opacity: 0.8;">Diskon hingga 50% untuk produk premium.</p>
                <button class="btn" style="background: white; margin-top: 15px; font-size: 12px;">Cek Sekarang</button>
            </div>
        </div>

        <div class="product-container">
            <?php if (empty($products)): ?>
                <p style="grid-column: 1/-1; text-align: center; color: #999; padding: 40px;">Belum ada produk yang
                    tersedia.</p>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                    <a href="product_detail.php?id=<?= $p['id'] ?>" class="p-card">
                        <div class="p-img-box">
                            <img src="<?= $p['image_url'] ?>" alt="<?= $p['name'] ?>">
                            <?php if ($p['discount_percent'] > 0): ?>
                                <span class="discount-tag"><?= $p['discount_percent'] ?>% OFF</span>
                            <?php endif; ?>
                        </div>
                        <div class="p-info">
                            <div class="p-name"><?= $p['name'] ?></div>
                            <div class="p-price">Rp <?= number_format($p['price'], 0, ',', '.') ?></div>
                            <div class="p-rating">
                                <i class="fas fa-star" style="color: #f1c40f;"></i>
                                <span><?= $p['rating'] ?> | Terjual 100+</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- SECTION: RIWAYAT -->
    <div id="riwayat" class="dashboard-section animate-fade">
        <div style="padding: 20px;">
            <h2>Histories Pemesanan</h2>
            <div style="margin-top: 20px; color: #999; text-align: center; padding: 50px;">
                <i class="fas fa-box-open" style="font-size: 50px; margin-bottom: 15px;"></i>
                <p>Belum ada riwayat pesanan.</p>
            </div>
        </div>
    </div>

    <!-- SECTION: NOTIFIKASI -->
    <div id="notif" class="dashboard-section animate-fade">
        <div style="padding: 20px;">
            <h2>Notifikasi</h2>
            <div class="notif-item animate-up"
                style="background:white; padding:15px; border-radius:12px; margin-top:15px; box-shadow: var(--shadow-soft);">
                <strong>Promo Akhir Bulan!</strong>
                <p style="font-size: 13px; color: #666; margin-top: 5px;">Dapatkan gratis ongkir ke seluruh Indonesia.
                </p>
                <span style="font-size: 10px; color: #999;">Baru saja</span>
            </div>
        </div>
    </div>

    <!-- SECTION: PROFILE -->
    <div id="profil" class="dashboard-section animate-fade">
        <div style="padding: 20px; text-align: center;">
            <div
                style="width: 100px; height: 100px; background: #eee; border-radius: 50%; margin: 0 auto 15px; overflow: hidden; border: 4px solid var(--white); box-shadow: var(--shadow-medium);">
                <img src="https://i.pravatar.cc/100" style="width: 100%;">
            </div>
            <h3><?= htmlspecialchars($user_nama) ?></h3>
            <p style="font-size: 13px; color: #777;">ID: #<?= $user_id ?></p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 30px;">
                <div class="btn glass" style="flex-direction: column; padding: 20px;">
                    <i class="fas fa-heart" style="color: #ff4757; font-size: 24px;"></i>
                    <span style="font-size: 12px; margin-top: 5px; color: #555;">Suka</span>
                </div>
                <div class="btn glass" style="flex-direction: column; padding: 20px;">
                    <i class="fas fa-bookmark" style="color: #2ecc71; font-size: 24px;"></i>
                    <span style="font-size: 12px; margin-top: 5px; color: #555;">Simpan</span>
                </div>
            </div>

            <div style="margin-top: 30px; text-align: left;">
                <div class="btn"
                    style="width: 100%; justify-content: space-between; background: white; margin-bottom: 10px; box-shadow: var(--shadow-soft);">
                    <span><i class="fas fa-cog" style="margin-right: 10px;"></i> Pengaturan</span>
                    <i class="fas fa-chevron-right"></i>
                </div>
                <div class="btn"
                    style="width: 100%; justify-content: space-between; background: white; margin-bottom: 10px; box-shadow: var(--shadow-soft);">
                    <span><i class="fas fa-comment-dots" style="margin-right: 10px;"></i> Hubungi Penjual</span>
                    <i class="fas fa-chevron-right"></i>
                </div>
                <a href="logout.php" class="btn"
                    style="width: 100%; background: #fff5f5; color: #ff4757; margin-top: 20px;">Keluar Akun</a>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="javascript:void(0)" onclick="switchSection('beranda', this)" class="nav-item active">
            <i class="fas fa-home"></i>
            <span>Beranda</span>
        </a>
        <a href="javascript:void(0)" onclick="switchSection('riwayat', this)" class="nav-item">
            <i class="fas fa-history"></i>
            <span>Riwayat</span>
        </a>
        <a href="javascript:void(0)" onclick="switchSection('notif', this)" class="nav-item">
            <div style="position: relative;">
                <i class="fas fa-bell"></i>
                <span
                    style="position: absolute; top: -5px; right: -5px; background: #ff4757; color: white; width: 14px; height: 14px; border-radius: 50%; font-size: 8px; display: flex; align-items: center; justify-content: center;">3</span>
            </div>
            <span>Notifikasi</span>
        </a>
        <a href="javascript:void(0)" onclick="switchSection('profil', this)" class="nav-item">
            <i class="fas fa-user"></i>
            <span>Profil</span>
        </a>
    </nav>

    <script>
        function switchSection(sectionId, element) {
            // Hide all sections
            document.querySelectorAll('.dashboard-section').forEach(sec => sec.classList.remove('active'));
            // Remove active from nav
            document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));

            // Show target
            document.getElementById(sectionId).classList.add('active');
            element.classList.add('active');
        }
    </script>
</body>

</html>