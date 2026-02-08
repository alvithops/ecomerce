<?php
require_once 'inc/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login_pemesan.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_nama = $_SESSION['user_nama'] ?? 'User';

// 1. Fetch Products for Beranda (Limited to 510 to show volume)
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 510");
$products = $stmt->fetchAll();

// 2. Fetch Orders for Histories
$stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt_orders->execute([$user_id]);
$orders = $stmt_orders->fetchAll();

// 3. Fetch Notifications
$stmt_notif = $pdo->prepare("SELECT * FROM notifications WHERE (user_id = ? OR user_id IS NULL) ORDER BY created_at DESC");
$stmt_notif->execute([$user_id]);
$notifications = $stmt_notif->fetchAll();

// 4. Fetch Profile Lists (Likes, Saves, etc)
$stmt_likes = $pdo->prepare("SELECT p.* FROM products p JOIN user_likes l ON p.id = l.product_id WHERE l.user_id = ?");
$stmt_likes->execute([$user_id]);
$liked_products = $stmt_likes->fetchAll();

$stmt_saves = $pdo->prepare("SELECT p.* FROM products p JOIN user_saves s ON p.id = s.product_id WHERE s.user_id = ?");
$stmt_saves->execute([$user_id]);
$saved_products = $stmt_saves->fetchAll();

// Categorized Orders for Profile
$stmt_unpaid = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND status = 'Belum Dibayar'");
$stmt_unpaid->execute([$user_id]);
$unpaid_list = $stmt_unpaid->fetchAll();

$stmt_packing = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND status = 'Dikemas'");
$stmt_packing->execute([$user_id]);
$packing_list = $stmt_packing->fetchAll();

$stmt_shipping = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND status = 'Dikirim'");
$stmt_shipping->execute([$user_id]);
$shipping_list = $stmt_shipping->fetchAll();

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
            overflow-x: hidden;
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

        /* Profile Sub-sections */
        .profile-list-item {
            background: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 12px;
            display: flex;
            gap: 15px;
            box-shadow: var(--shadow-soft);
            align-items: center;
        }

        .profile-list-item img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
        }

        .status-pill {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
        }

        .status-unpaid {
            background: #fff5f5;
            color: #ff4757;
        }

        .status-packing {
            background: #fff8e1;
            color: #ffa000;
        }

        .status-shipping {
            background: #e8f5e9;
            color: #2e7d32;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <header class="header-main glass">
        <div class="logo" style="font-weight: 600; font-size: 18px; color: var(--primary-main);">LuxuryShope</div>
        <div class="search-inner">
            <i class="fas fa-search" style="color: #999;"></i>
            <input type="text" placeholder="Cari 500+ produk mewah...">
        </div>
        <div class="cart-icon" style="position: relative; cursor: pointer;" onclick="location.href='checkout.php'">
            <i class="fas fa-shopping-cart" style="font-size: 20px; color: #555;"></i>
            <span
                style="position: absolute; top: -10px; right: -10px; background: #ff4757; color: white; width: 18px; height: 18px; border-radius: 50%; font-size: 10px; display: flex; align-items: center; justify-content: center;">2</span>
        </div>
    </header>

    <!-- SECTION: BERANDA -->
    <div id="beranda" class="dashboard-section active animate-fade">
        <div class="banner" style="padding: 20px;">
            <div
                style="width: 100%; height: 150px; background: linear-gradient(to right, var(--primary-main), var(--secondary-main)); border-radius: var(--radius-lg); padding: 25px; color: var(--text-dark); position: relative; overflow: hidden;">
                <h2 style="font-size: 22px; z-index: 1; position: relative;">Premium Collection 2026</h2>
                <p style="font-size: 14px; opacity: 0.8; z-index: 1; position: relative;">Eksplorasi 500+ barang mewah
                    terbaik.</p>
                <button class="btn"
                    style="background: white; margin-top: 15px; font-size: 12px; z-index: 1; position: relative;">Lihat
                    Katalog</button>
                <i class="fas fa-shopping-bag"
                    style="position: absolute; right: -20px; bottom: -20px; font-size: 150px; color: rgba(255,255,255,0.2);"></i>
            </div>
        </div>

        <div class="product-container">
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
                            <span><?= $p['rating'] ?> | Terjual <?= rand(10, 500) ?>+</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- SECTION: HISTORIES -->
    <div id="riwayat" class="dashboard-section animate-fade">
        <div style="padding: 20px;">
            <h2>Histories Pemesanan</h2>
            <div style="margin-top: 20px;">
                <?php if (empty($orders)): ?>
                    <div style="text-align: center; padding: 50px; color: #999;">
                        <i class="fas fa-box-open" style="font-size: 50px; display: block; margin-bottom: 10px;"></i>
                        Belum ada riwayat pesanan.
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <div class="profile-list-item">
                            <i class="fas fa-receipt" style="font-size: 30px; color: var(--secondary-main);"></i>
                            <div style="flex:1">
                                <div style="font-weight: 600;">Pesanan #<?= $o['id'] ?></div>
                                <div style="font-size: 12px; color: #777;">Total: Rp
                                    <?= number_format($o['total_price'], 0, ',', '.') ?></div>
                                <div style="font-size: 11px; color: #999;"><?= $o['created_at'] ?></div>
                            </div>
                            <span class="status-pill <?= strtolower(str_replace(' ', '', 'status-' . $o['status'])) ?>">
                                <?= $o['status'] ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SECTION: NOTIFIKASI -->
    <div id="notif" class="dashboard-section animate-fade">
        <div style="padding: 20px;">
            <h2>Notifikasi</h2>
            <?php if (empty($notifications)): ?>
                <div style="text-align: center; padding: 50px; color: #999;">Tak ada notifikasi baru.</div>
            <?php else: ?>
                <?php foreach ($notifications as $n): ?>
                    <div class="profile-list-item" style="border-left: 4px solid var(--primary-main);">
                        <div style="flex:1">
                            <strong
                                style="color: var(--primary-main); font-size: 12px; display: block; margin-bottom: 5px;"><?= $n['type'] ?></strong>
                            <div style="font-weight: 600;"><?= $n['title'] ?></div>
                            <p style="font-size: 13px; color: #555; margin-top: 5px;"><?= $n['message'] ?></p>
                            <span style="font-size: 10px; color: #999;"><?= $n['created_at'] ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- SECTION: PROFILE -->
    <div id="profil" class="dashboard-section animate-fade">
        <div style="padding: 20px; text-align: center;">
            <div
                style="width: 100px; height: 100px; background: #eee; border-radius: 50%; margin: 0 auto 15px; overflow: hidden; border: 4px solid var(--white); box-shadow: var(--shadow-medium);">
                <img src="https://i.pravatar.cc/150?u=<?= $user_id ?>" style="width: 100%;">
            </div>
            <h3><?= htmlspecialchars($user_nama) ?></h3>
            <p style="font-size: 13px; color: #777;">ID: #LXS-<?= str_pad($user_id, 4, '0', STR_PAD_LEFT) ?></p>

            <!-- Profile Summary Grid -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 30px;">
                <div onclick="showSub('liked')" class="btn glass"
                    style="flex-direction: column; padding: 15px; font-size: 11px;">
                    <i class="fas fa-heart" style="color: #ff4757; margin-bottom: 5px;"></i>
                    Suka (<?= count($liked_products) ?>)
                </div>
                <div onclick="showSub('saved')" class="btn glass"
                    style="flex-direction: column; padding: 15px; font-size: 11px;">
                    <i class="fas fa-bookmark" style="color: #2ecc71; margin-bottom: 5px;"></i>
                    Simpan (<?= count($saved_products) ?>)
                </div>
                <div onclick="showSub('unpaid')" class="btn glass"
                    style="flex-direction: column; padding: 15px; font-size: 11px;">
                    <i class="fas fa-wallet" style="color: #ffa000; margin-bottom: 5px;"></i>
                    Belum Bayar (<?= count($unpaid_list) ?>)
                </div>
            </div>

            <!-- Tracking Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
                <div onclick="showSub('packing')" class="btn glass" style="padding: 15px; font-size: 11px; gap: 8px;">
                    <i class="fas fa-box" style="color: #9b59b6;"></i> Dikemas (<?= count($packing_list) ?>)
                </div>
                <div onclick="showSub('shipping')" class="btn glass" style="padding: 15px; font-size: 11px; gap: 8px;">
                    <i class="fas fa-truck" style="color: #3498db;"></i> Dikirim (<?= count($shipping_list) ?>)
                </div>
            </div>

            <!-- Profile Action Options -->
            <div style="margin-top: 30px; text-align: left;">
                <div class="btn"
                    style="width: 100%; justify-content: space-between; background: white; margin-bottom: 10px; box-shadow: var(--shadow-soft);"
                    onclick="alert('Buka Pengaturan Akun...')">
                    <span><i class="fas fa-cog" style="margin-right: 10px;"></i> Pengaturan Akun</span>
                    <i class="fas fa-chevron-right"></i>
                </div>
                <div class="btn"
                    style="width: 100%; justify-content: space-between; background: white; margin-bottom: 10px; box-shadow: var(--shadow-soft);"
                    onclick="location.href='chat.php'">
                    <span><i class="fas fa-comment-dots" style="margin-right: 10px;"></i> Chat Hubungi Penjual</span>
                    <i class="fas fa-chevron-right"></i>
                </div>
                <a href="logout.php" class="btn"
                    style="width: 100%; background: #fff5f5; color: #ff4757; margin-top: 20px;">Keluar Sistem</a>
            </div>
        </div>
    </div>

    <!-- Profile SUB-CONTENT Modals (Simulated via Toggle) -->
    <div id="subOverlay"
        style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); backdrop-filter:blur(10px); display:none; z-index:2000; justify-content:center; align-items:flex-end;">
        <div style="width:100%; max-height:80vh; background:white; border-radius:24px 24px 0 0; padding:30px; overflow-y:auto;"
            class="animate-up">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 id="subTitle">List Data</h3>
                <button onclick="document.getElementById('subOverlay').style.display='none'" class="btn"
                    style="padding:5px 15px;">Tutup</button>
            </div>
            <div id="subListContainer"></div>
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
            <span>Histories</span>
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
            <span>Profile</span>
        </a>
    </nav>

    <script>
        function switchSection(sectionId, element) {
            document.querySelectorAll('.dashboard-section').forEach(sec => sec.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
            document.getElementById(sectionId).classList.add('active');
            element.classList.add('active');
            window.scrollTo(0, 0);
        }

        function showSub(type) {
            const overlay = document.getElementById('subOverlay');
            const title = document.getElementById('subTitle');
            const container = document.getElementById('subListContainer');
            overlay.style.display = 'flex';

            let html = '';
            if (type === 'liked') {
                title.innerText = "Produk Disukai";
                <?php if (empty($liked_products)): ?> html = '<p style="text-align:center; color:#999;">Belum ada produk disukai.</p>'; <?php else: ?>
                    <?php foreach ($liked_products as $lp): ?>
                        html += `<div class="profile-list-item"><img src="<?= $lp['image_url'] ?>"><div style="flex:1"><strong><?= $lp['name'] ?></strong><br><span style="color:#ff4757">Rp <?= number_format($lp['price'], 0, ',', '.') ?></span></div></div>`;
                    <?php endforeach; ?>
                <?php endif; ?>
            } else if (type === 'saved') {
                title.innerText = "Produk Disimpan";
                <?php if (empty($saved_products)): ?> html = '<p style="text-align:center; color:#999;">Belum ada produk disimpan.</p>'; <?php else: ?>
                    <?php foreach ($saved_products as $sp): ?>
                        html += `<div class="profile-list-item"><img src="<?= $sp['image_url'] ?>"><div style="flex:1"><strong><?= $sp['name'] ?></strong><br><span style="color:#ff4757">Rp <?= number_format($sp['price'], 0, ',', '.') ?></span></div></div>`;
                    <?php endforeach; ?>
                <?php endif; ?>
            } else if (type === 'unpaid') {
                title.innerText = "Belum Dibayar";
                 <?php if (empty($unpaid_list)): ?> html = '<p style="text-align:center; color:#999;">Tak ada tagihan tertunda.</p>'; <?php else: ?>
                    <?php foreach ($unpaid_list as $ul): ?>
                        html += `<div class="profile-list-item"><i class="fas fa-money-bill-wave" style="font-size:30px; color:#ffa000;"></i><div style="flex:1"><strong>Order #<?= $ul['id'] ?></strong><br><span>Total: Rp <?= number_format($ul['total_price'], 0, ',', '.') ?></span></div></div>`;
                    <?php endforeach; ?>
                <?php endif; ?>
            } else if (type === 'packing') {
                title.innerText = "Sedang Dikemas";
                 <?php if (empty($packing_list)): ?> html = '<p style="text-align:center; color:#999;">Belum ada pesanan dikemas.</p>'; <?php else: ?>
                    <?php foreach ($packing_list as $pl): ?>
                        html += `<div class="profile-list-item"><i class="fas fa-archive" style="font-size:30px; color:#9b59b6;"></i><div style="flex:1"><strong>Order #<?= $pl['id'] ?></strong><br><span>Status: Menyiapkan barang...</span></div></div>`;
                    <?php endforeach; ?>
                <?php endif; ?>
            } else if (type === 'shipping') {
                title.innerText = "Dalam Pengiriman";
                 <?php if (empty($shipping_list)): ?> html = '<p style="text-align:center; color:#999;">Belum ada pesanan dikirim.</p>'; <?php else: ?>
                    <?php foreach ($shipping_list as $sl): ?>
                        html += `<div class="profile-list-item"><i class="fas fa-truck-moving" style="font-size:30px; color:#3498db;"></i><div style="flex:1"><strong>Order #<?= $sl['id'] ?></strong><br><span>Resi: <?= $sl['tracking_code'] ?: 'Sedang diproses' ?></span></div><button class="btn" style="font-size:10px;" onclick="alert('Lacak posisi kurir...')">Lacak</button></div>`;
                    <?php endforeach; ?>
                <?php endif; ?>
            }
            container.innerHTML = html;
        }
    </script>
</body>

</html>