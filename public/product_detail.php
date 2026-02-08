<?php
require_once 'inc/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login_pemesan.php");
    exit();
}

$product_id = $_GET['id'] ?? 0;

// Fetch Product Details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    die("Produk tidak ditemukan.");
}

// Fetch Reviews
$stmt = $pdo->prepare("SELECT r.*, u.nama_pengguna FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$product_id]);
$reviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $product['name'] ?> | Luxury Shope</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: #fff;
            padding-bottom: 90px;
        }

        .top-action {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            z-index: 1000;
        }

        .back-circle {
            width: 40px;
            height: 40px;
            background: rgba(0, 0, 0, 0.4);
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            backdrop-filter: blur(5px);
        }

        .product-hero {
            width: 100%;
            height: 400px;
            position: relative;
            background: #eee;
        }

        .product-hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .content-main {
            background: white;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            margin-top: -30px;
            position: relative;
            padding: 25px;
            min-height: 500px;
        }

        .price-badge {
            font-size: 28px;
            font-weight: 700;
            color: #ff4757;
            margin-bottom: 10px;
        }

        .stats-row {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 13px;
            color: #777;
            margin-bottom: 20px;
        }

        .action-float {
            display: flex;
            gap: 12px;
        }

        .floating-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: none;
            background: #f8f9fa;
            color: #555;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .floating-btn.active-like {
            color: #ff4757;
            background: #fff1f2;
        }

        .floating-btn.active-save {
            color: #2ecc71;
            background: #f0fdf4;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin: 25px 0 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        /* Video Section */
        .video-box {
            width: 100%;
            height: 210px;
            background: #000;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 25px;
        }

        /* Bottom Bar */
        .bottom-checkout {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: white;
            padding: 15px 20px;
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 15px;
            z-index: 1000;
        }

        .qty-picker {
            display: flex;
            align-items: center;
            background: #f1f1f1;
            padding: 8px 15px;
            border-radius: 30px;
            gap: 15px;
            font-weight: 600;
        }

        .qty-picker button {
            border: none;
            background: none;
            font-size: 18px;
            cursor: pointer;
            color: #555;
        }
    </style>
</head>

<body>

    <div class="top-action">
        <a href="dashboard.php" class="back-circle"><i class="fas fa-arrow-left"></i></a>
        <a href="#" class="back-circle"><i class="fas fa-share-alt"></i></a>
    </div>

    <div class="product-hero animate-fade">
        <img src="<?= $product['image_url'] ?>" alt="<?= $product['name'] ?>">
    </div>

    <div class="content-main animate-up">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div class="price-badge">Rp <?= number_format($product['price'], 0, ',', '.') ?></div>
                <h1 style="font-size: 22px; font-weight: 600;"><?= $product['name'] ?></h1>
            </div>
            <div class="action-float">
                <button id="likeBtn" class="floating-btn" onclick="toggleAction('like')"><i
                        class="fas fa-heart"></i></button>
                <button id="saveBtn" class="floating-btn" onclick="toggleAction('save')"><i
                        class="fas fa-bookmark"></i></button>
            </div>
        </div>

        <div class="stats-row">
            <span><i class="fas fa-star" style="color: #f1c40f;"></i> <?= $product['rating'] ?></span>
            <span>| 1.2k Terjual</span>
            <span>| <?= count($reviews) ?> Ulasan</span>
        </div>

        <div class="section-title">Deskripsi Produk</div>
        <p style="color: #555; text-align: justify; font-size: 14px; line-height: 1.8;">
            <?= $product['description'] ?>
        </p>

        <?php if ($product['video_url']): ?>
            <div class="section-title">Video Produk</div>
            <div class="video-box">
                <video src="<?= $product['video_url'] ?>" style="width: 100%; height: 100%;" controls></video>
            </div>
        <?php endif; ?>

        <div class="section-title">Ulasan Pemesan</div>
        <?php if (empty($reviews)): ?>
            <p style="color: #999; font-size: 13px;">Belum ada ulasan untuk produk ini.</p>
        <?php else: ?>
            <?php foreach ($reviews as $r): ?>
                <div
                    style="background: #fdfdfd; padding: 15px; border-radius: 12px; margin-bottom: 12px; border: 1px solid #f1f1f1;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <strong><?= htmlspecialchars($r['nama_pengguna']) ?></strong>
                        <div style="color: #f1c40f; font-size: 12px;">
                            <?php for ($i = 0; $i < $r['rating']; $i++)
                                echo '<i class="fas fa-star"></i>'; ?>
                        </div>
                    </div>
                    <p style="font-size: 13px; color: #555;"><?= htmlspecialchars($r['comment']) ?></p>
                    <span style="font-size: 10px; color: #999; display: block; margin-top: 8px;"><?= $r['created_at'] ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="bottom-checkout animate-up">
        <button class="btn glass" onclick="location.href='chat.php'" style="padding: 12px;"><i
                class="fas fa-comment-dots"></i></button>
        <div class="qty-picker">
            <button onclick="changeQty(-1)">-</button>
            <span id="qtyVal">1</span>
            <button onclick="changeQty(1)">+</button>
        </div>
        <button class="btn btn-primary" style="flex: 1;" onclick="buyNow()">Beli Sekarang</button>
    </div>

    <script>
        function toggleAction(type) {
            const btn = document.getElementById(type + 'Btn');
            btn.classList.toggle('active-' + type);
            // In a real app, send AJAX log
        }

        function changeQty(n) {
            let val = parseInt(document.getElementById('qtyVal').innerText);
            val += n;
            if (val < 1) val = 1;
            document.getElementById('qtyVal').innerText = val;
        }

        function buyNow() {
            const qty = document.getElementById('qtyVal').innerText;
            location.href = `checkout.php?id=<?= $product_id ?>&qty=${qty}`;
        }
    </script>
</body>

</html>