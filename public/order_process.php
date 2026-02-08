<?php
require_once 'inc/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login_pemesan.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$address = $_POST['address'] ?? '';
$payment_method = $_POST['payment_method'] ?? 'COD';
$items_json = $_POST['items'] ?? '[]';
$items = json_decode($items_json, true);

if (empty($address) || empty($items)) {
    die("Data tidak lengkap.");
}

try {
    $pdo->beginTransaction();

    // Calculate Total
    $total_final = 0;
    foreach ($items as $item) {
        $total_final += ($item['price'] * $item['qty']);
    }

    // 1. Insert Order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, address, payment_method, status) VALUES (?, ?, ?, ?, 'Belum Dibayar')");
    $stmt->execute([$user_id, $total_final, $address, $payment_method]);
    $order_id = $pdo->lastInsertId();

    // 2. Insert Items & Update Stock
    $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
    $stmt_stock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

    foreach ($items as $item) {
        $stmt_item->execute([$order_id, $item['id'], $item['qty'], $item['price']]);
        $stmt_stock->execute([$item['qty'], $item['id']]);
    }

    // 3. Create Notification for User
    $stmt_notif = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'Status Pesanan')");
    $stmt_notif->execute([
        $user_id,
        "Pesanan Baru #$order_id Berhasil",
        "Pesanan Anda sedang diproses oleh penjual. Anda memilih metode pembayaran $payment_method."
    ]);

    $pdo->commit();

    // Redirect to Histories section on Dashboard
    header("Location: dashboard.php#riwayat"); // Note: JavaScript in dashboard.php should handle hash to switch section
    exit();

} catch (PDOException $e) {
    $pdo->rollBack();
    die("Terjadi kesalahan sistem: " . $e->getMessage());
}
?>