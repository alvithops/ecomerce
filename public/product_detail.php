<?php
session_start();

if (!isset($_SESSION['logged_in_user'])) {
    header("Location: login.php");
    exit();
}

/**
 * Simulasi Data Produk (Sama dengan dashboard.php)
 */
$products = [
    1 => [
        'name' => 'Premium Gaming Laptop Z1',
        'price' => 15000000,
        'discount' => '20%',
        'image' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?auto=format&fit=crop&w=600&q=80',
        'rating' => 4.8,
        'video' => 'https://www.w3schools.com/html/mov_bbb.mp4',
        'desc' => 'Laptop gaming dengan performa tinggi, processor i9 generasi terbaru, dan kartu grafis RTX 4090. Sangat mewah dan elegan.',
        'reviews' => [
            ['user' => 'Andi', 'msg' => 'Gila mancep banget!', 'rating' => 5],
            ['user' => 'Budi', 'msg' => 'Harganya sebanding dengan kualitas.', 'rating' => 4]
        ]
    ],
    // ... data lainnya bisa ditambahkan
];

$id = $_GET['id'] ?? 1;
$product = $products[$id] ?? $products[1];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $product['name']; ?> - Detail Produk
    </title>
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
            padding-bottom: 90px;
        }

        .top-nav {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            z-index: 1000;
        }

        .back-btn {
            background: rgba(0, 0, 0, 0.3);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
        }

        .product-img {
            width: 100%;
            height: 400px;
            background: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .container {
            padding: 20px;
            background: white;
            border-radius: 24px 24px 0 0;
            margin-top: -30px;
            position: relative;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .price {
            font-size: 24px;
            font-weight: 600;
            color: #ff4757;
        }

        .action-btns {
            display: flex;
            gap: 15px;
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: #f0f0f0;
            cursor: pointer;
            transition: 0.3s;
        }

        .action-btn.active-like {
            color: #ff4757;
            background: #ffebeb;
        }

        .action-btn.active-save {
            color: #2ecc71;
            background: #eaffea;
        }

        .p-name {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .p-stats {
            display: flex;
            gap: 15px;
            font-size: 13px;
            color: #777;
            margin-bottom: 20px;
        }

        .video-section {
            margin: 20px 0;
            border-radius: 12px;
            overflow: hidden;
            background: black;
        }

        .review-card {
            padding: 15px;
            background: #f9f9f9;
            border-radius: 12px;
            margin-bottom: 10px;
        }

        /* Bottom Control Bar */
        .bottom-bar {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.05);
            z-index: 1000;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            background: #f0f0f0;
            border-radius: 20px;
            padding: 5px 15px;
            gap: 15px;
        }

        .qty-btn {
            border: none;
            background: none;
            font-size: 18px;
            cursor: pointer;
        }

        .buy-now {
            flex: 1;
            padding: 14px;
            background: linear-gradient(to right, var(--primary-main), var(--secondary-main));
            border: none;
            border-radius: 12px;
            color: var(--text-dark);
            font-weight: 600;
            cursor: pointer;
        }

        .msg-btn {
            width: 50px;
            height: 50px;
            background: #f0f0f0;
            border: none;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #777;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <nav class="top-nav">
        <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
        <a href="#" class="back-btn"><i class="fas fa-share-alt"></i></a>
    </nav>

    <div class="product-img">
        <img src="<?php echo $product['image']; ?>" alt="">
    </div>

    <div class="container">
        <div class="price-row">
            <div class="price">Rp
                <?php echo number_format($product['price'], 0, ',', '.'); ?>
            </div>
            <div class="action-btns">
                <button class="action-btn" id="likeBtn" onclick="toggleAction('like')"><i
                        class="fas fa-heart"></i></button>
                <button class="action-btn" id="saveBtn" onclick="toggleAction('save')"><i
                        class="fas fa-bookmark"></i></button>
            </div>
        </div>

        <h1 class="p-name">
            <?php echo $product['name']; ?>
        </h1>

        <div class="p-stats">
            <span><i class="fas fa-star" style="color:#f1c40f"></i>
                <?php echo $product['rating']; ?>
            </span>
            <span>| 1.2k Terjual</span>
            <span>| 450 Ulasan</span>
        </div>

        <div style="border-top: 1px solid #eee; padding-top: 15px;">
            <h3 style="font-size: 16px; margin-bottom: 10px;">Deskripsi Produk</h3>
            <p style="font-size: 14px; color: #555; line-height: 1.6;">
                <?php echo $product['desc']; ?>
            </p>
        </div>

        <div class="video-section">
            <video width="100%" controls>
                <source src="<?php echo $product['video']; ?>" type="video/mp4">
            </video>
        </div>

        <h3>Ulasan Pemesan</h3>
        <div style="margin-top: 15px;">
            <?php foreach ($product['reviews'] as $rev): ?>
                <div class="review-card">
                    <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                        <strong>
                            <?php echo $rev['user']; ?>
                        </strong>
                        <div style="color:#f1c40f">
                            <?php for ($i = 0; $i < $rev['rating']; $i++)
                                echo '<i class="fas fa-star"></i>'; ?>
                        </div>
                    </div>
                    <p style="font-size:13px; color:#666;">
                        <?php echo $rev['msg']; ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="bottom-bar">
        <button class="msg-btn" onclick="location.href='chat.php'"><i class="fas fa-comment-dots"></i></button>
        <div class="quantity-control">
            <button class="qty-btn" onclick="changeQty(-1)">-</button>
            <span id="qty">1</span>
            <button class="qty-btn" onclick="changeQty(1)">+</button>
        </div>
        <button class="buy-now"
            onclick="location.href='checkout.php?id=<?php echo $id; ?>&qty='+document.getElementById('qty').innerText">Beli
            Sekarang</button>
    </div>

    <script>
        function toggleAction(type) {
            const btn = document.getElementById(type + 'Btn');
            btn.classList.toggle('active-' + type);
            // In real app, this would send an AJAX request to server
            if (btn.classList.contains('active-' + type)) {
                alert('Produk berhasil di' + (type === 'like' ? 'sukai' : 'simpan') + '!');
            }
        }

        function changeQty(n) {
            let qty = parseInt(document.getElementById('qty').innerText);
            qty += n;
            if (qty < 1) qty = 1;
            document.getElementById('qty').innerText = qty;
        }
    </script>
</body>

</html>