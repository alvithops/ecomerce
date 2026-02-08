<?php
require_once 'inc/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login_pemesan.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$items_json = $_POST['items'] ?? '[]';
$selected_items = json_decode($items_json, true);

if (empty($selected_items)) {
    header("Location: checkout.php");
    exit();
}

// Calculate total for display
$final_total = 0;
foreach ($selected_items as $si) {
    $final_total += ($si['price'] * $si['qty']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi Pengiriman | Luxury Shope</title>
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

        .map-box {
            width: 100%;
            height: 300px;
            border-radius: 15px;
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
            transition: 0.3s;
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
        <h1 style="font-size: 18px;">Informasi Pengiriman & Pembayaran</h1>
    </header>

    <div class="animate-fade">
        <!-- 0. Ordered Items List (Daftar Checkout) -->
        <div class="card-box animate-up" style="padding-bottom: 15px;">
            <h3 style="margin-bottom: 15px;"><i class="fas fa-receipt" style="color: var(--primary-main);"></i> Daftar Checkout</h3>
            <div id="orderedItems">
                <?php foreach ($selected_items as $si): ?>
                    <div style="display: flex; gap: 12px; margin-bottom: 12px; align-items: center; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px;">
                        <img src="<?= htmlspecialchars($si['image_url'] ?? 'https://via.placeholder.com/60') ?>" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover;">
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 13px;"><?= htmlspecialchars($si['name'] ?? 'Produk') ?></div>
                            <div style="font-size: 11px; color: #777;">Jumlah: <?= $si['qty'] ?></div>
                        </div>
                        <div style="font-weight: 600; color: #ff4757; font-size: 13px;">
                            Rp <?= number_format($si['price'] * $si['qty'], 0, ',', '.') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 15px; margin-top: 10px; color: #333;">
                <span>Total Barang (<?= count($selected_items) ?>)</span>
                <span style="color: #ff4757;">Rp <?= number_format($final_total, 0, ',', '.') ?></span>
            </div>
        </div>

        <!-- 1. Address Section with Maps -->
        <div class="card-box animate-up">
            <h3 style="margin-bottom: 10px;"><i class="fas fa-map-marked-alt" style="color: var(--secondary-main);"></i>
                Masukkan Alamat Pengiriman</h3>
            <div class="form-group">
                <textarea id="addressInput" class="form-input"
                    placeholder="Tandai lokasi Anda di peta di bawah ini atau isi manual..." rows="3"></textarea>
            </div>
            <div id="map" class="map-box"></div>
            <p style="font-size: 11px; color: #999; margin-top: 8px;">*Gunakan peta untuk akurasi pengiriman yang
                maksimal.</p>
        </div>

        <!-- 2. Payment Method -->
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

            <p
                style="font-size: 12px; color: #777; margin-top: 15px; background: #f9f9f9; padding: 10px; border-radius: 10px; border-left: 4px solid var(--primary-main);">
                <i class="fas fa-info-circle"></i> Pembayaran via Bank/E-Wallet akan divalidasi oleh admin. Sertakan
                nomor pengirim yang valid.
            </p>
            <input type="hidden" id="payMethodId" value="COD">
        </div>
    </div>

    <!-- Final Sticky Footer -->
    <div class="sticky-footer animate-up">
        <div class="total-info">
            <div style="font-size: 11px; color: #777;">Total Tagihan</div>
            <h3>Rp
                <?= number_format($final_total, 0, ',', '.') ?>
            </h3>
        </div>
        <button class="btn btn-primary" style="padding: 15px 40px; border-radius: 12px; font-weight: 600;"
            onclick="submitFinal()">PESAN SEKARANG</button>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Init Map Logic
        const map = L.map('map', { scrollWheelZoom: false }).setView([-6.200000, 106.816666], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

        let marker;
        map.on('click', function (e) {
            if (marker) map.removeLayer(marker);
            marker = L.marker(e.latlng).addTo(map);

            // Reverse geocoding simulation
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${e.latlng.lat}&lon=${e.latlng.lng}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('addressInput').value = data.display_name;
                })
                .catch(() => {
                    document.getElementById('addressInput').value = `[LAT: ${e.latlng.lat.toFixed(6)}, LNG: ${e.latlng.lng.toFixed(6)}] - Lokasi Terpilih.`;
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
                document.getElementById('accNumberInput').value = ''; // Clear input when hidden
            }
        }

        function submitFinal() {
            const addr = document.getElementById('addressInput').value.trim();
            const pm = document.getElementById('payMethodId').value;
            const accNum = document.getElementById('accNumberInput').value.trim();
            const items = <?= $items_json ?>;

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