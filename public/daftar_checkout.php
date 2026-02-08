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

$total_price = 0;
foreach ($selected_items as $si) {
    $total_price += ($si['price'] * $si['qty']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Checkout | PayBag</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .ordered-item {
            display: flex;
            gap: 15px;
            align-items: center;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .ordered-item img {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            object-fit: cover;
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
        <h1 style="font-size: 18px;">Daftar Checkout</h1>
    </header>

    <div class="animate-fade">
        <div class="card-box animate-up">
            <h3 style="margin-bottom: 20px;"><i class="fas fa-list-ul" style="color: var(--primary-main);"></i> Rincian
                Barang</h3>
            <div id="itemList">
                <?php foreach ($selected_items as $item): ?>
                    <div class="ordered-item">
                        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        <div style="flex:1">
                            <div style="font-weight: 600; font-size: 14px;">
                                <?= htmlspecialchars($item['name']) ?>
                            </div>
                            <div style="font-size: 12px; color: #777;">Kuantitas:
                                <?= $item['qty'] ?>
                            </div>
                            <div style="color: #ff4757; font-weight: 600; margin-top: 5px;">
                                Rp
                                <?= number_format($item['price'] * $item['qty'], 0, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div
                style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 15px; border-top: 2px dashed #eee;">
                <span style="font-weight: 600;">Total Pesanan</span>
                <span style="font-weight: 700; color: #ff4757; font-size: 18px;">Rp
                    <?= number_format($total_price, 0, ',', '.') ?>
                </span>
            </div>
        </div>

        <div class="card-box animate-up" style="text-align: center; background: #fff9f0; border: 1px solid #ffe8cc;">
            <p style="font-size: 13px; color: #7a5a33;">
                <i class="fas fa-info-circle"></i> Pastikan daftar barang di atas sudah sesuai. Klik tombol dibawah
                untuk menyelesaikan rincian pengiriman.
            </p>
        </div>
    </div>

    <!-- Final Sticky Footer -->
    <div class="sticky-footer animate-up">
        <div class="total-info">
            <div style="font-size: 11px; color: #777;">Ringkasan Total</div>
            <h3>Rp
                <?= number_format($total_price, 0, ',', '.') ?>
            </h3>
        </div>
        <button class="btn btn-primary" style="padding: 15px 40px; border-radius: 12px; font-weight: 600;"
            onclick="goNext()">CHECKOUT SELESAI</button>
    </div>

    <script>
        function goNext() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'checkout_shipping.php';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'items';
            input.value = '<?= addslashes($items_json) ?>';
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>

</html>