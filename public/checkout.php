<?php
require_once 'inc/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login_pemesan.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$checkout_items = $_SESSION['cart'] ?? [];

// Optional: if a single id is passed, add it to cart first (handled by cart_add now, but keeping as fallback)
if (isset($_GET['id']) && $_GET['id'] > 0) {
    // This part is now redundant because product_detail redirects to cart_add first,
    // but we can leave it or just rely on session.
}
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
        <!-- 1. Product Selection Section (Step 1) -->
        <div class="card-box animate-up">
            <h3 style="margin-bottom: 20px;"><i class="fas fa-shopping-basket" style="color: var(--primary-main);"></i>
                Daftar Produk & Pilihan</h3>
            <div id="itemList">
                <?php if (empty($checkout_items)): ?>
                    <p style="color:#999; text-align:center;">Produk tidak ditemukan di keranjang.</p>
                <?php else: ?>
                    <?php foreach ($checkout_items as $item): ?>
                        <div class="item-list-row" data-id="<?= $item['id'] ?>" data-price="<?= $item['price'] ?>"
                            data-qty="<?= $item['qty'] ?>">
                            <input type="checkbox" class="checkbox-custom" checked onchange="calculateTotal()">
                            <img src="<?= $item['image_url'] ?>" class="item-thumb">
                            <div style="flex:1">
                                <div style="font-weight: 600; font-size: 14px;" class="p-name-el"><?= $item['name'] ?></div>
                                <div style="color: #ff4757; font-weight: 600; font-size: 14px;" class="row-subtotal">Rp
                                    <?= number_format($item['price'] * $item['qty'], 0, ',', '.') ?>
                                </div>
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                                    <div class="qty-picker" style="transform: scale(0.8); transform-origin: left;">
                                        <button onclick="updateQty(this, -1)">-</button>
                                        <span class="qty-val"><?= $item['qty'] ?></span>
                                        <button onclick="updateQty(this, 1)">+</button>
                                    </div>
                                    <button class="btn glass" style="padding: 5px 10px; color: #ff4757; font-size: 12px;"
                                        onclick="removeItem(this)">Hapus</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-box animate-up">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <span style="color:#777;">Ringkasan Belanja</span>
                <span style="font-weight:600;" id="summaryTotal">Rp 0</span>
            </div>
            <p style="font-size:12px; color:#999;">*Centang produk yang ingin Anda beli sekarang.</p>
        </div>
    </div>

    <!-- Final Sticky Footer -->
    <div class="sticky-footer animate-up">
        <div class="total-info">
            <div style="font-size: 11px; color: #777;">Total Pembayaran</div>
            <h3 id="displayTotal">Rp 0</h3>
        </div>
        <button class="btn btn-primary" style="padding: 15px 40px; border-radius: 12px; font-weight: 600;"
            onclick="goNext()">PESAN SEKARANG</button>
    </div>

    <script>
        function updateQty(btn, delta) {
            const row = btn.closest('.item-list-row');
            let qty = parseInt(row.querySelector('.qty-val').innerText);
            qty += delta;
            if (qty < 1) qty = 1;

            row.querySelector('.qty-val').innerText = qty;
            row.dataset.qty = qty;

            const price = parseInt(row.dataset.price);
            row.querySelector('.row-subtotal').innerText = 'Rp ' + (price * qty).toLocaleString('id-ID');

            calculateTotal();
        }

        function removeItem(btn) {
            if (confirm('Hapus produk dari keranjang?')) {
                btn.closest('.item-list-row').remove();
                calculateTotal();
                if (document.querySelectorAll('.item-list-row').length === 0) {
                    location.reload();
                }
            }
        }

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
            const formatted = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('displayTotal').innerText = formatted;
            document.getElementById('summaryTotal').innerText = formatted;
        }

        function goNext() {
            const selectedItems = [];
            document.querySelectorAll('.item-list-row').forEach(row => {
                if (row.querySelector('.checkbox-custom').checked) {
                    selectedItems.push({
                        id: row.dataset.id,
                        name: row.querySelector('.p-name-el')?.innerText || 'Product',
                        qty: row.dataset.qty,
                        price: row.dataset.price
                    });
                }
            });

            if (selectedItems.length === 0) {
                alert("Pilih minimal satu produk!");
                return;
            }

            // Create dynamic form to send to shipping page
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'checkout_shipping.php';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'items';
            input.value = JSON.stringify(selectedItems);
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
        }

        // Initial Calculation
        calculateTotal();
    </script>
</body>

</html>