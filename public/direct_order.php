<?php
require_once 'inc/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login_pemesan.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$p_id = $_GET['id'] ?? 0;
$qty = $_GET['qty'] ?? 1;

// Fetch Product Details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$p_id]);
$product = $stmt->fetch();

if (!$product) {
    die("Produk tidak ditemukan.");
}

$total_price = $product['price'] * $qty;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Sekarang | Luxury Shope</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: #f8f9fa;
            padding-bottom: 120px;
        }

        .card-box {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin: 20px;
            box-shadow: var(--shadow-soft);
        }

        .product-summary {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .product-summary img {
            width: 100px;
            height: 100px;
            border-radius: 12px;
            object-fit: cover;
        }

        .map-box {
            width: 100%;
            height: 280px;
            border-radius: 15px;
            margin-top: 15px;
            border: 2px solid #eee;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 15px;
        }

        .pay-tile {
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
            font-size: 13px;
            color: #555;
        }

        .pay-tile.active {
            border-color: var(--secondary-main);
            background: #f0f7ff;
            color: var(--secondary-main);
            font-weight: 600;
        }

        .sticky-footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: white;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.05);
            z-index: 1000;
        }

        .total-info h3 {
            color: #ff4757;
            font-size: 22px;
            margin-top: 5px;
        }
    </style>
</head>

<body>

    <header class="header-main glass" style="justify-content: flex-start; gap: 15px;">
        <a href="javascript:history.back()" style="color: #333;"><i class="fas fa-arrow-left"></i></a>
        <h1 style="font-size: 18px;">Pesan Sekarang</h1>
    </header>

    <div class="animate-fade">
        <!-- 1. Product Summary Section -->
        <div class="card-box animate-up">
            <div class="product-summary">
                <img src="<?= $product['image_url'] ?>" alt="<?= $product['name'] ?>">
                <div>
                    <h2 style="font-size: 16px; font-weight: 600;">
                        <?= $product['name'] ?>
                    </h2>
                    <p style="font-size: 12px; color: #777;">Jumlah:
                        <?= $qty ?>
                    </p>
                    <p style="font-size: 18px; color: #ff4757; font-weight: 700; margin-top: 5px;">Rp
                        <?= number_format($total_price, 0, ',', '.') ?>
                    </p>
                </div>
            </div>

            <h3 style="margin-bottom: 10px;"><i class="fas fa-map-marker-alt" style="color: var(--secondary-main);"></i>
                Alamat Pengiriman</h3>
            <div class="form-group">
                <textarea id="addressInput" class="form-input"
                    placeholder="Tandai lokasi Anda di peta di bawah ini untuk mengisi otomatis..." rows="2"></textarea>
            </div>
            <div id="map" class="map-box"></div>
        </div>

        <!-- 2. Payment Section -->
        <div class="card-box animate-up">
            <h3><i class="fas fa-wallet" style="color: #f1c40f;"></i> Opsi Pembayaran</h3>
            <div class="payment-grid">
                <div class="pay-tile active" onclick="setPay('COD', this)">COD</div>
                <div class="pay-tile" onclick="setPay('COD Cek Dulu', this)">COD Cek Dulu</div>
                <div class="pay-tile" onclick="setPay('BCA', this)">BCA</div>
                <div class="pay-tile" onclick="setPay('BRI', this)">BRI</div>
                <div class="pay-tile" onclick="setPay('Mandiri', this)">Mandiri</div>
                <div class="pay-tile" onclick="setPay('BNI', this)">BNI</div>
                <div class="pay-tile" onclick="setPay('BTN', this)">BTN</div>
                <div class="pay-tile" onclick="setPay('BSI', this)">BSI</div>
                <div class="pay-tile" onclick="setPay('Dana', this)">Dana</div>
                <div class="pay-tile" onclick="setPay('GoPay', this)">GoPay</div>
                <div class="pay-tile" onclick="setPay('Shopee Pay', this)">Shopee Pay</div>
            </div>

            <div id="accNumberBox" style="display: none; margin-top: 20px;" class="animate-fade">
                <div class="form-group">
                    <label id="accLabel">Nomor Rekening / HP</label>
                    <input type="text" id="accNumberInput" class="form-input" placeholder="Masukkan nomor anda...">
                </div>
            </div>
        </div>
        <input type="hidden" id="payMethodId" value="COD">
    </div>

    <!-- Final Sticky Footer -->
    <div class="sticky-footer animate-up">
        <div class="total-info">
            <div style="font-size: 11px; color: #777;">Total Pembayaran</div>
            <h3 id="displayTotal">Rp
                <?= number_format($total_price, 0, ',', '.') ?>
            </h3>
        </div>
        <button class="btn btn-primary" style="padding: 15px 40px; border-radius: 12px; font-weight: 600;"
            onclick="submitOrder()">PESAN</button>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Init Map
        const map = L.map('map', { scrollWheelZoom: false }).setView([-6.200000, 106.816666], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

        let marker;
        map.on('click', function (e) {
            if (marker) map.removeLayer(marker);
            marker = L.marker(e.latlng).addTo(map);
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${e.latlng.lat}&lon=${e.latlng.lng}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('addressInput').value = data.display_name;
                })
                .catch(() => {
                    document.getElementById('addressInput').value = `Loc: ${e.latlng.lat.toFixed(6)}, ${e.latlng.lng.toFixed(6)}`;
                });
        });

        function setPay(method, el) {
            document.getElementById('payMethodId').value = method;
            document.querySelectorAll('.pay-tile').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
            const accBox = document.getElementById('accNumberBox');
            if (method !== 'COD' && method !== 'COD Cek Dulu') {
                accBox.style.display = 'block';
                document.getElementById('accLabel').innerText = `Nomor Rekening / HP (${method})`;
            } else {
                accBox.style.display = 'none';
            }
        }

        function submitOrder() {
            const addr = document.getElementById('addressInput').value.trim();
            const pm = document.getElementById('payMethodId').value;
            const accNum = document.getElementById('accNumberInput').value;

            if (!addr) {
                alert("Lengkapi Data Anda Terlebih Dahulu, Dan Pastikan Sudah Terisi Semua");
                return;
            }

            if (pm !== 'COD' && pm !== 'COD Cek Dulu' && !accNum) {
                alert("Masukkan Nomor Rekening / HP untuk pembayaran via " + pm);
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'order_process.php';

            const items = [{ id: <?= $p_id ?>, qty: <?= $qty ?>, price: <?= $product['price'] ?> }];
            const fields = {
                address: addr,
                payment_method: pm,
                acc_number: accNum,
                items: JSON.stringify(items)
            };

            for (const key in fields) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key];
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        }

        map.on('focus', function () { map.scrollWheelZoom.enable(); });
        map.on('blur', function () { map.scrollWheelZoom.disable(); });
    </script>
</body>

</html>