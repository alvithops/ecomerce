<?php
require_once 'inc/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login_pemesan.php");
    exit();
}

$p_id = $_GET['id'] ?? 0;
$qty = $_GET['qty'] ?? 1;

// Fetch product for checkout
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$p_id]);
$product = $stmt->fetch();

if (!$product) {
    die("Produk tidak valid.");
}

$total = $product['price'] * $qty;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Luxury Shope</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: #f8f9fa;
            padding-bottom: 90px;
        }

        .checkout-box {
            padding: 20px;
            background: white;
            border-radius: var(--radius-lg);
            margin: 20px;
            box-shadow: var(--shadow-soft);
        }

        .item-row {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .item-img {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            object-fit: cover;
        }

        .map-container {
            width: 100%;
            height: 250px;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 15px;
            border: 2px solid #eee;
        }

        .step-title {
            font-size: 16px;
            font-weight: 600;
            margin: 20px 0 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .step-title i {
            color: var(--primary-main);
        }

        .payment-opt {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 10px;
        }

        .pay-card {
            padding: 15px;
            border: 2px solid #eee;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .pay-card.active {
            border-color: var(--secondary-main);
            background: #f0f7ff;
        }

        .bottom-pay {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>

    <header class="header-main glass" style="justify-content: flex-start; gap: 15px;">
        <a href="product_detail.php?id=<?= $p_id ?>" style="color: #333;"><i class="fas fa-arrow-left"></i></a>
        <h1 style="font-size: 18px;">Konfirmasi Pesanan</h1>
    </header>

    <div class="checkout-box animate-up">
        <div class="step-title"><i class="fas fa-shopping-basket"></i> Produk yang Dipesan</div>
        <div class="item-row">
            <img src="<?= $product['image_url'] ?>" class="item-img">
            <div style="flex:1">
                <div style="font-weight: 500; font-size: 14px;"><?= $product['name'] ?></div>
                <div style="color: #777; font-size: 12px; margin: 5px 0;">Jumlah: <?= $qty ?></div>
                <div style="font-weight: 600; color: #ff4757;">Rp <?= number_format($product['price'], 0, ',', '.') ?>
                </div>
            </div>
        </div>

        <div class="step-title"><i class="fas fa-map-marker-alt"></i> Alamat Pengiriman</div>
        <div class="form-group" style="margin-bottom: 0;">
            <input type="text" id="addressInput" class="form-input"
                placeholder="Tandai lokasi di peta atau isi manual..." required>
        </div>
        <div id="map" class="map-container"></div>
        <p style="font-size: 10px; color: #999; margin-top: 5px;">*Tandai lokasi Anda pada peta untuk mengisi alamat
            secara otomatis.</p>

        <div class="step-title"><i class="fas fa-wallet"></i> Metode Pembayaran</div>
        <div class="payment-opt">
            <div class="pay-card active" onclick="setPayment('COD', this)">
                <i class="fas fa-hand-holding-usd" style="font-size: 20px; display: block; margin-bottom: 5px;"></i>
                <span style="font-size: 13px;">COD</span>
            </div>
            <div class="pay-card" onclick="setPayment('COD Cek Dulu', this)">
                <i class="fas fa-search-dollar" style="font-size: 20px; display: block; margin-bottom: 5px;"></i>
                <span style="font-size: 13px;">COD Cek Dulu</span>
            </div>
        </div>
        <input type="hidden" id="paymentMethod" value="COD">
    </div>

    <div class="bottom-pay animate-up">
        <div>
            <div style="font-size: 11px; color: #777;">Total Pembayaran</div>
            <div style="font-size: 18px; font-weight: 700; color: #ff4757;">Rp <?= number_format($total, 0, ',', '.') ?>
            </div>
        </div>
        <button class="btn btn-primary" style="padding: 15px 30px;" onclick="placeOrder()">PESAN SEKARANG</button>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Init Map
        const map = L.map('map').setView([-6.200000, 106.816666], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);

        let marker;
        map.on('click', function (e) {
            if (marker) map.removeLayer(marker);
            marker = L.marker(e.latlng).addTo(map);
            document.getElementById('addressInput').value = `Lat: ${e.latlng.lat.toFixed(6)}, Lng: ${e.latlng.lng.toFixed(6)} (Lokasi Terpilih)`;
        });

        function setPayment(method, element) {
            document.getElementById('paymentMethod').value = method;
            document.querySelectorAll('.pay-card').forEach(c => c.classList.remove('active'));
            element.classList.add('active');
        }

        function placeOrder() {
            const addr = document.getElementById('addressInput').value;
            if (!addr) {
                alert("Lengkapi Data Anda Terlebih Dahulu, Dan Pastikan Sudah Terisi Semua");
                return;
            }

            // In real app, send to order_process.php via POST
            alert("Pesanan Berhasil Diproses! Mengalihkan ke riwayat...");
            location.href = "dashboard.php";
        }
    </script>
</body>

</html>