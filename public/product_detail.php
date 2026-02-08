<?php
require_once 'inc/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login_pemesan.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_GET['id'] ?? 0;

// Fetch Product Details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    die("Produk tidak ditemukan.");
}

// Fetch Reviews (Rich: includes image/video)
$stmt_rev = $pdo->prepare("SELECT r.*, u.nama_pengguna FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$stmt_rev->execute([$product_id]);
$reviews = $stmt_rev->fetchAll();

// Check if liked/saved
$stmt_l = $pdo->prepare("SELECT 1 FROM user_likes WHERE user_id = ? AND product_id = ?");
$stmt_l->execute([$user_id, $product_id]);
$is_liked = $stmt_l->fetch();

$stmt_s = $pdo->prepare("SELECT 1 FROM user_saves WHERE user_id = ? AND product_id = ?");
$stmt_s->execute([$user_id, $product_id]);
$is_saved = $stmt_s->fetch();

// Dynamic AVG Rating
$stmt_avg = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE product_id = ?");
$stmt_avg->execute([$product_id]);
$rating_data = $stmt_avg->fetch();
$avg_rating = $rating_data['avg_rating'] ?: $product['rating'];
$review_count = $rating_data['count'];
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
            overflow-x: hidden;
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
            height: 450px;
            position: relative;
            background: #f8f8f8;
        }

        .product-hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .content-main {
            background: white;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            margin-top: -40px;
            position: relative;
            padding: 25px;
            min-height: 500px;
            box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.05);
        }

        .price-badge {
            font-size: 32px;
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
            margin-bottom: 25px;
        }

        .action-float {
            display: flex;
            gap: 12px;
        }

        .floating-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            background: #f8f9fa;
            color: #555;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
        }

        .floating-btn.active-like {
            color: #ff4757;
            background: #fff1f2;
            transform: scale(1.1);
        }

        .floating-btn.active-save {
            color: #2ecc71;
            background: #f0fdf4;
            transform: scale(1.1);
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin: 30px 0 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f1f1;
        }

        /* Video Section */
        .video-box {
            width: 100%;
            height: 220px;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 25px;
            position: relative;
        }

        .video-box video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Review Styles */
        .rev-card {
            background: #fdfdfd;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 15px;
            border: 1px solid #f1f1f1;
        }

        .rev-user {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .rev-media {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .rev-media img,
        .rev-media video {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            background: #eee;
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
            padding: 10px 20px;
            border-radius: 30px;
            gap: 20px;
            font-weight: 600;
        }

        .qty-picker button {
            border: none;
            background: none;
            font-size: 20px;
            cursor: pointer;
            color: #555;
            width: 30px;
        }
    </style>
</head>

<body>

    <div class="top-action">
        <a href="dashboard.php" class="back-circle"><i class="fas fa-arrow-left"></i></a>
        <a href="javascript:void(0)" onclick="shareProduct()" class="back-circle"><i class="fas fa-share-alt"></i></a>
    </div>

    <!-- Product Image Hero -->
    <div class="product-hero animate-fade">
        <img src="<?= $product['image_url'] ?>" alt="<?= $product['name'] ?>">
    </div>

    <div class="content-main animate-up">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div class="price-badge">Rp <?= number_format($product['price'], 0, ',', '.') ?></div>
                <h1 style="font-size: 24px; font-weight: 600;"><?= $product['name'] ?></h1>
            </div>
            <div class="action-float">
                <button id="likeBtn" class="floating-btn <?= $is_liked ? 'active-like' : '' ?>"
                    onclick="toggleAction('like')"><i class="fas fa-heart"></i></button>
                <button id="saveBtn" class="floating-btn <?= $is_saved ? 'active-save' : '' ?>"
                    onclick="toggleAction('save')"><i class="fas fa-bookmark"></i></button>
            </div>
        </div>

        <div class="stats-row">
            <span><i class="fas fa-star" style="color: #f1c40f;"></i> <?= number_format($avg_rating, 1) ?></span>
            <span>| <?= rand(100, 2000) ?> Terjual</span>
            <span>| <?= $review_count ?> Ulasan</span>
        </div>

        <div class="section-title">Deskripsi Produk Eksklusif</div>
        <div
            style="background: #fdfdfd; padding: 20px; border-radius: 15px; border: 1px solid #f8f8f8; margin-bottom: 20px;">
            <p style="color: #444; text-align: justify; font-size: 15px; line-height: 1.8;">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </p>
        </div>

        <!-- Video Demo Section -->
        <div class="section-title">Video Preview Produk (Premium 5s)</div>
        <div class="video-box" style="box-shadow: var(--shadow-soft); border: 1px solid #eee;">
            <?php if ($product['video_url']): ?>
                <video src="<?= $product['video_url'] ?>" autoplay muted loop playsinline controls
                    style="width: 100%; object-fit: cover;"></video>
            <?php else: ?>
                <div
                    style="width:100%; height:100%; display:flex; justify-content:center; align-items:center; color:#777; font-size:14px; background:#f0f0f0;">
                    <i class="fas fa-video-slash" style="margin-right:10px;"></i> Video belum tersedia
                </div>
            <?php endif; ?>
        </div>

        <!-- Reviews Section -->
        <div class="section-title">Ulasan Pemesan</div>
        <?php if (empty($reviews)): ?>
            <div style="text-align: center; padding: 30px; background: #f9f9f9; border-radius: 12px; color: #999;">
                <i class="fas fa-comment-slash" style="font-size: 30px; display: block; margin-bottom: 10px;"></i>
                Belum ada ulasan untuk produk ini.
            </div>
        <?php else: ?>
            <?php foreach ($reviews as $r): ?>
                <div class="rev-card animate-up">
                    <div class="rev-user">
                        <strong><?= htmlspecialchars($r['nama_pengguna']) ?></strong>
                        <div style="color: #f1c40f; font-size: 12px;">
                            <?php for ($i = 0; $i < 5; $i++)
                                echo ($i < $r['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                        </div>
                    </div>
                    <p style="font-size: 14px; color: #555; line-height: 1.6;"><?= htmlspecialchars($r['comment']) ?></p>

                    <!-- Review Multimedia -->
                    <div class="rev-media">
                        <?php if ($r['review_image']): ?>
                            <img src="<?= $r['review_image'] ?>" onclick="zoomMedia(this.src)">
                        <?php endif; ?>
                        <?php if ($r['review_video']): ?>
                            <video src="<?= $r['review_video'] ?>" onclick="zoomMedia(this.src, true)"></video>
                        <?php endif; ?>
                    </div>
                    <span style="font-size: 11px; color: #999; display: block; margin-top: 15px;"><?= $r['created_at'] ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Bottom Action Bar -->
    <div class="bottom-checkout animate-up" style="flex-wrap: wrap; height: auto; gap: 10px; padding: 10px 15px;">
        <div style="flex: 1 1 100%; display: flex; justify-content: center; margin-bottom: 5px;">
            <div class="qty-picker" style="padding: 5px 15px; transform: scale(0.9);">
                <button onclick="changeQty(-1)">-</button>
                <span id="qtyVal">1</span>
                <button onclick="changeQty(1)">+</button>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1.2fr 1.2fr; width: 100%; gap: 8px;">
            <button class="btn glass" onclick="location.href='chat.php?product_id=<?= $product_id ?>'"
                style="padding: 12px 5px; font-size: 13px; font-weight: 600; color: #3498db; border: 1px solid #3498db;">
                <i class="fas fa-comment"></i> Chat
            </button>

            <button class="btn" onclick="buyNow()"
                style="padding: 12px 5px; font-size: 13px; font-weight: 600; background: #ff4757; color: white;">
                <i class="fas fa-shopping-bag"></i> Pesan Skrg
            </button>

            <button class="btn btn-primary" onclick="buyNow()"
                style="padding: 12px 5px; font-size: 13px; font-weight: 600;">
                <i class="fas fa-receipt"></i> Checkout
            </button>
        </div>
    </div>

    <script>
        function toggleAction(type) {
            const btn = document.getElementById(type + 'Btn');
            const isActive = btn.classList.contains('active-' + type);

            // Call API
            fetch('toggle_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `type=${type}&product_id=<?= $product_id ?>`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        btn.classList.toggle('active-' + type);
                    } else {
                        alert('Gagal memproses permintaan.');
                    }
                });
        }

        function changeQty(n) {
            let val = parseInt(document.getElementById('qtyVal').innerText);
            val += n;
            if (val < 1) val = 1;
            if (val > <?= $product['stock'] ?>) {
                alert('Stok produk terbatas!');
                val = <?= $product['stock'] ?>;
            }
            document.getElementById('qtyVal').innerText = val;
        }

        function buyNow() {
            const qty = document.getElementById('qtyVal').innerText;
            location.href = `checkout.php?id=<?= $product_id ?>&qty=${qty}`;
        }

        function shareProduct() {
            if (navigator.share) {
                navigator.share({ title: '<?= $product['name'] ?>', url: window.location.href });
            } else {
                alert('Link disalin ke clipboard!');
            }
        }

        function zoomMedia(src, isVideo = false) {
            // Basic zoom logic or open in new tab
            window.open(src, '_blank');     }
    </script>
</body>

</html>