<?php
require_once 'inc/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login_pemesan.php");
    exit();
}

$user_id = $_SESSION['user_id'];
// In this complex version, we check if there are multiple items or just one from query
$p_id = $_GET['id'] ?? 0;
$qty = $_GET['qty'] ?? 1;

$checkout_items = [];
if ($p_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$p_id]);
    $p = $stmt->fetch();
    if ($p) {
        $p['selected_qty'] = $qty;
        $checkout_items[] = $p;
    }
}

// Fallback: If no single item, maybe fetch from a 'cart' table (simulated here with session if needed)
// For this rewrite, we'll assume the user might have previously "selected" items.
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
            padding-bottom: 120px;
            overflow-x: hidden;
        }

        .checkout-container {
            padding: 20px;
        }

        .card-box {
            background: white;
            border-radius: var(--radius-lg);
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-soft);
        }

        .item-list-row {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .item-list-row:last-child {
            border-bottom: none;
        }

        .checkbox-custom {
            width: 22px;
            height: 22px;
            cursor: pointer;
            accent-color: var(--primary-main);
        }

        .item-thumb {
            width: 70px;
            height: 70px;
            border-radius: 10px;
            object-fit: cover;
        }

        .map-box {
            width: 100%;
            height: 280px;
            border-radius: 15px;
            overflow: hidden;
            margin-top: 15px;
            border: 2px solid #eee;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 15px;
        }

        .pay-tile {
            padding: 15px;
            border: 2px solid #eee;
            border-radius: 15px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            color: #777;
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
        <h1 style="font-size: 18px;">Checkout Pesanan</h1>
    </header>

    <div class="checkout-container animate-fade">
        <!-- 1. Product Selection Section -->
        <div class="card-box animate-up">
            <h3 style="margin-bottom: 20px;"><i class="fas fa-shopping-basket" style="color: var(--primary-main);"></i>
                Daftar Produk</h3>
            <div id="itemList">
                <?php if (empty($checkout_items)): ?>
                    <p style="color:#999; text-align:center;">Produk tidak ditemukan.</p>
                <?php else: ?>
                    <?php foreach ($checkout_items as $item): ?>
                        <div class="item-list-row" data-price="<?= $item['price'] ?>" data-qty="<?= $item['selected_qty'] ?>">
                            <input type="checkbox" class="checkbox-custom" checked onchange="calculateTotal()">
                            <img src="<?= $item['image_url'] ?>" class="item-thumb">
                            <div style="flex:1">
                                <div style="font-weight: 600; font-size: 14px;"><?= $item['name'] ?></div>
                                <div style="font-size: 12px; color: #777;">Jumlah: <?= $item['selected_qty'] ?></div>
                                <div style="color: #ff4757; font-weight: 600;">Rp
                                    <?= number_format($item['price'] * $item['selected_qty'], 0, ',', '.') ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2. Address Section with Maps -->
        <div class="card-box animate-up">
            <h3 style="margin-bottom: 10px;"><i class="fas fa-map-marked-alt" style="color: var(--secondary-main);"></i>
                Masukkan Alamat Pengiriman</h3>
            <div class="form-group">
                <textarea id="addressInput" class="form-input"
                    placeholder="Tandai lokasi Anda di peta di bawah ini untuk mengisi otomatis atau isi manual..."
                    rows="2"></textarea>
            </div>
            <div id="map" class="map-box"></div>
            <p style="font-size: 11px; color: #999; margin-top: 8px;">*Gunakan peta untuk akurasi pengiriman yang
                maksimal.</p>
        </div>

        <!-- 3. Payment Method -->
        <div class="card-box animate-up">
            <h3><i class="fas fa-wallet" style="color: #f1c40f;"></i> Opsi Pembayaran</h3>
            <div class="payment-grid">
                <div class="pay-tile active" onclick="setPay('COD', this)">
                    <i class="fas fa-hand-holding-usd" style="font-size: 20px; display: block; margin-bottom: 5px;"></i>
                    COD
                </div>
                <div class="pay-tile" onclick="setPay('COD Cek Dulu', this)">
                    <i class="fas fa-search-dollar" style="font-size: 20px; display: block; margin-bottom: 5px;"></i>
                    Cek Dulu
                </div>
                <div class="pay-tile" onclick="setPay('BCA', this)">
                    <i class="fas fa-university" style="font-size: 20px; display: block; margin-bottom: 5px;"></i>
                    BCA
                </div>
                <div class="pay-tile" onclick="setPay('BRI', this)">
                    <i class="fas fa-university" style="font-size: 20px; display: block; margin-bottom: 5px;"></i>
                    BRI
                </div>
                <div class="pay-tile" onclick="setPay('Shopee Pay', this)">
                    <i class="fas fa-mobile-alt" style="font-size: 20px; display: block; margin-bottom: 5px;"></i>
                    Shopee
                </div>
                <div class="pay-tile" onclick="setPay('BNI', this)">
                    <i class="fas fa-university" style="font-size: 20px; display: block; margin-bottom: 5px;"></i>
                    BNI
                </div>
                <div class="pay-tile" onclick="setPay('BTN', this)">
                    <i class="fas fa-university" style="font-size: 20px; display: block; margin-bottom: 5px;"></i>
                    BTN
                </div>
                <div class="pay-tile" onclick="setPay('Mandiri', this)">
                    <i class="fas fa-university" style="font-size: 20px; display: block; margin-bottom: 5px;"></i>
                    Mandiri
                </div>
                <div class="pay-tile" onclick="setPay('Dana', this)">
                    <i class="fas fa-wallet" style="font-size: 20px; display: block; margin-bottom: 5px;"></i>
                    Dana
                </div>
                <div class="pay-tile" onclick="setPay('GoPay', this)">
                    <i class="fas fa-wallet" style="font-size: 20px; display: block; margin-bottom: 5px;"></i>
                    GoPay
                </div>
            </div>

            <!-- Account Number Input (Hidden by default) -->
            <div id="accNumberBox" style="display: none; margin-top: 20px;" class="animate-fade">
                <div class="form-group">
                    <label id="accLabel">Nomor Rekening / HP</label>
                    <input type="text" id="accNumberInput" class="form-input" placeholder="Masukkan nomor anda...">
                </div>
            </div>

            <input type="hidden" id="payMethodId" value="COD">
        </div>
    </div>

    <!-- Final Sticky Footer -->
    <div class="sticky-footer animate-up">
        <div class="total-info">
            <div style="font-size: 11px; color: #777;">Total Pembayaran</div>
            <h3 id="displayTotal">Rp 0</h3>
        </div>
        <button class="btn btn-primary" style="padding: 15px 40px; border-radius: 12px; font-weight: 600;"
            onclick="submitOrder()">PESAN SEKARANG</button>
    </div>

    <!-- Map Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Calculate Total Logic
        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.item-list-row').forEach(row => {
                const cb = row.querySelector('.checkbox-custom');
                if (cb.checked) {
                    const price = parseInt(row.dataset.price);
                    const qty = parseInt(row.dataset.qty);
                    total += (price * qty);
                }
            });
            document.getElementById('displayTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
        }

        // Init Map Logic
        const map = L.map('map', { scrollWheelZoom: false }).setView([-6.200000, 106.816666], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

        let marker;
        map.on('click', function (e) {
            if (marker) map.removeLayer(marker);
            marker = L.marker(e.latlng).addTo(map);

            // Reverse geocoding simulation or just coordinates
            document.getElementById('addressInput').value = `[LAT: ${e.latlng.lat.toFixed(6)}, LNG: ${e.latlng.lng.toFixed(6)}] - Lokasi Terpilih di Peta. Alamat detail...`;
        });

        // Payment Toggle
        function setPay(method, el) {
            document.getElementById('payMethodId').value = method;
            document.querySelectorAll('.pay-tile').forEach(t => t.classList.remove('active'));
            el.classList.add('active');

            // Show/Hide Account Number Box
            const accBox = document.getElementById('accNumberBox');
            if (method !== 'COD' && method !== 'COD Cek Dulu') {
                accBox.style.display = 'block';
                document.getElementById('accLabel').innerText = `Nomor Rekening / HP (${method})`;
            } else {
                accBox.style.display = 'none';
            }
        }

        // Final Submission
        function submitOrder() {
            const addr = document.getElementById('addressInput').value;
            const pm = document.getElementById('payMethodId').value;
            const accNum = document.getElementById('accNumberInput').value;
            const selectedItems = [];

            // Validation for Bank/E-Wallet
            if (pm !== 'COD' && pm !== 'COD Cek Dulu' && !accNum) {
                alert("Masukkan Nomor Rekening / HP untuk pembayaran via " + pm);
                return;
            }

            document.querySelectorAll('.item-list-row').forEach((row) => {
                if (row.querySelector('.checkbox-custom').checked) {
                    selectedItems.push({
                        id: <?= $p_id ?>,
                        qty: row.dataset.qty,
                        price: row.dataset.price
                    });
                }
            });

            if (!addr || selectedItems.length === 0) {
                alert("Lengkapi Data Anda Terlebih Dahulu, Dan Pastikan Sudah Terisi Semua");
                return;
            }

            // In real app, AJAX POST to order_process.php
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'order_process.php';

            const fields = {
                address: addr,
                payment_method: pm,
                acc_number: accNum,
                items: JSON.stringify(selectedItems)
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

        // Initial Calculation
        calculateTotal();

        // Ensure maps works when scroll
        map.on('focus', function () { map.scrollWheelZoom.enable(); });
        map.on('blur', function () { map.scrollWheelZoom.disable(); });

    </script>
</body>

</html>