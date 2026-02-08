<?php
session_start();

if (!isset($_SESSION['logged_in_user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['logged_in_user'];

// Simulasi Keranjang (biasanya dari DB, tapi kita simulasi)
$cart_items = [
    [
        'id' => 1,
        'name' => 'Premium Gaming Laptop Z1',
        'price' => 15000000,
        'qty' => $_GET['qty'] ?? 1,
        'image' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?auto=format&fit=crop&w=100&q=80'
    ]
];

// Handle Order Placement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $alamat = $_POST['alamat'] ?? '';
    $pembayaran = $_POST['pembayaran'] ?? '';

    if (empty($alamat) || empty($pembayaran)) {
        $error = "Lengkapi Data Anda Terlebih Dahulu, Dan Pastikan Sudah Terisi Semua";
    } else {
        // Simpan ke history (simulasi)
        if (!isset($_SESSION['order_history']))
            $_SESSION['order_history'] = [];
        $_SESSION['order_history'][] = [
            'items' => $cart_items,
            'total' => $_POST['total_price'],
            'address' => $alamat,
            'payment' => $pembayaran,
            'status' => 'Sedang Diproses',
            'date' => date('Y-m-d H:i')
        ];

        // Redirect ke history menu dashboard
        header("Location: dashboard.php?view=history&success=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Luxury Shope</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        :root {
            --primary-light: #D0F0C0;
            --secondary-light: #E0F7FA;
            --primary-main: #90EE90;
            --secondary-main: #ADD8E6;
            --white: #ffffff;
            --text-dark: #2c3e50;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: #f8f9fa;
            padding-bottom: 90px;
        }

        header {
            padding: 20px;
            background: white;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .container {
            padding: 20px;
        }

        .checkout-item {
            background: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
        }

        .item-img {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 5px;
        }

        .item-price {
            color: #ff4757;
            font-weight: 600;
        }

        .checkbox {
            width: 20px;
            height: 20px;
            accent-color: var(--primary-main);
        }

        .summary-bar {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.05);
        }

        .total-info {
            font-size: 14px;
        }

        .total-price {
            display: block;
            font-size: 18px;
            font-weight: 600;
            color: #ff4757;
        }

        .btn-order {
            padding: 12px 30px;
            background: linear-gradient(to right, var(--primary-main), var(--secondary-main));
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        /* Fullscreen Step 2 */
        #step2 {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            z-index: 2000;
            overflow-y: auto;
        }

        #map {
            height: 250px;
            width: 100%;
            border-radius: 12px;
            margin: 15px 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            outline: none;
        }

        .payment-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .pay-opt {
            padding: 15px;
            border: 2px solid #eee;
            border-radius: 12px;
            cursor: pointer;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
        }

        .pay-opt.active {
            border-color: var(--primary-main);
            background: var(--secondary-light);
        }

        .alert {
            padding: 15px;
            background: #fee;
            color: #e74c3c;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 14px;
            display: none;
        }
    </style>
</head>

<body>

    <header id="checkoutHeader">
        <a href="dashboard.php" style="color:var(--text-dark)"><i class="fas fa-arrow-left"></i></a>
        <h2 style="font-size: 18px;">Checkout</h2>
    </header>

    <div class="container" id="step1">
        <?php foreach ($cart_items as $item): ?>
            <div class="checkout-item">
                <input type="checkbox" class="checkbox" checked onchange="updateTotal()">
                <img src="<?php echo $item['image']; ?>" class="item-img">
                <div class="item-info">
                    <div class="item-name">
                        <?php echo $item['name']; ?>
                    </div>
                    <div style="font-size: 12px; color: #777;">Jumlah:
                        <?php echo $item['qty']; ?>
                    </div>
                    <div class="item-price">Rp
                        <?php echo number_format($item['price'] * $item['qty'], 0, ',', '.'); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Step 2: Order Details (Address & Payment) -->
    <div id="step2">
        <header>
            <button onclick="toggleStep(1)" style="border:none;background:none;font-size:18px;"><i
                    class="fas fa-arrow-left"></i></button>
            <h2 style="font-size: 18px;">Alamat & Pembayaran</h2>
        </header>

        <div class="container">
            <div id="errorAlert" class="alert">Lengkapi Data Anda Terlebih Dahulu, Dan Pastikan Sudah Terisi Semua</div>

            <form id="orderForm" method="POST">
                <input type="hidden" name="place_order" value="1">
                <input type="hidden" name="total_price" id="finalTotalPrice">
                <input type="hidden" name="pembayaran" id="selectedPayment">

                <div class="form-group">
                    <label>Alamat Pengiriman</label>
                    <textarea name="alamat" id="addressInput" rows="3"
                        placeholder="Masukkan alamat lengkap atau pilih dari map..."></textarea>
                </div>

                <label style="font-weight: 600; font-size: 14px;">Pilih Lokasi di Map</label>
                <div id="map"></div>

                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <div class="payment-options">
                        <div class="pay-opt" onclick="setPayment('COD', this)">COD (Bayar di Tempat)</div>
                        <div class="pay-opt" onclick="setPayment('COD Cek Dulu', this)">COD Cek Dulu</div>
                    </div>
                </div>

                <div
                    style="background: white; padding: 15px; border-radius: 12px; border: 1px dashed #ddd; margin-bottom: 80px;">
                    <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 5px;">
                        <span>Subtotal Produk</span>
                        <span id="subtotalLabel"></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 5px;">
                        <span>Biaya Pengiriman</span>
                        <span style="color: #2ecc71;">Gratis</span>
                    </div>
                    <hr style="margin: 10px 0; border: none; border-top: 1px solid #eee;">
                    <div style="display: flex; justify-content: space-between; font-weight: 600;">
                        <span>Total Pembayaran</span>
                        <span id="totalLabel" style="color: #ff4757;"></span>
                    </div>
                </div>

                <button type="button" class="btn-order"
                    style="width: 100%; position: fixed; bottom: 20px; left: 0; border-radius: 0;"
                    onclick="submitOrder()">PESAN SEKARANG</button>
            </form>
        </div>
    </div>

    <!-- Step 1 Summary bar -->
    <div class="summary-bar" id="step1Bar">
        <div class="total-info">
            Total
            <span class="total-price" id="mainTotalPrice">Rp 0</span>
        </div>
        <button class="btn-order" onclick="toggleStep(2)">PESAN</button>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let totalPrice = <?php echo $cart_items[0]['price'] * $cart_items[0]['qty']; ?>;

        function updateTotal() {
            // Simplified for the single item demo
            const checked = document.querySelector('.checkbox').checked;
            const currentTotal = checked ? totalPrice : 0;
            document.getElementById('mainTotalPrice').innerText = 'Rp ' + currentTotal.toLocaleString('id-ID');
            document.getElementById('subtotalLabel').innerText = 'Rp ' + currentTotal.toLocaleString('id-ID');
            document.getElementById('totalLabel').innerText = 'Rp ' + currentTotal.toLocaleString('id-ID');
            document.getElementById('finalTotalPrice').value = currentTotal;
        }

        updateTotal();

        function toggleStep(step) {
            if (step === 2) {
                document.getElementById('step1').style.display = 'none';
                document.getElementById('step1Bar').style.display = 'none';
                document.getElementById('step2').style.display = 'block';
                initMap();
            } else {
                document.getElementById('step1').style.display = 'block';
                document.getElementById('step1Bar').style.display = 'flex';
                document.getElementById('step2').style.display = 'none';
            }
        }

        let map, marker;
        function initMap() {
            if (map) return;
            map = L.map('map').setView([-6.200000, 106.816666], 13); // Jakarta
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            map.on('click', function (e) {
                if (marker) map.removeLayer(marker);
                marker = L.marker(e.latlng).addTo(map);
                document.getElementById('addressInput').value = "Lat: " + e.latlng.lat + ", Lng: " + e.latlng.lng + " (Lokasi dari Map)";
            });
        }

        function setPayment(type, el) {
            document.querySelectorAll('.pay-opt').forEach(opt => opt.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('selectedPayment').value = type;
        }

        function submitOrder() {
            const addr = document.getElementById('addressInput').value;
            const pay = document.getElementById('selectedPayment').value;

            if (!addr || !pay) {
                document.getElementById('errorAlert').style.display = 'block';
                window.scrollTo(0, 0);
            } else {
                document.getElementById('orderForm').submit();
            }
        }
    </script>
</body>

</html>