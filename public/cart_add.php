<?php
require_once 'inc/db.php';
session_start();

$product_id = $_GET['id'] ?? 0;
$qty = $_GET['qty'] ?? 1;

if ($product_id > 0) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if product already in cart
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $product_id) {
            $item['qty'] += $qty;
            $found = true;
            break;
        }
    }

    if (!$found) {
        // Fetch product details to store in cart (minimal)
        $stmt = $pdo->prepare("SELECT name, price, image_url, stock FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $p = $stmt->fetch();
        if ($p) {
            $_SESSION['cart'][] = [
                'id' => $product_id,
                'name' => $p['name'],
                'price' => $p['price'],
                'image_url' => $p['image_url'],
                'qty' => (int) $qty,
                'stock' => $p['stock']
            ];
        }
    }
}

header("Location: checkout.php");
exit();
