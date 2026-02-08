<?php
require_once 'inc/db.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$product_id = $_POST['product_id'] ?? 0;
$stock = $_POST['stock'] ?? 0;
$action = $_POST['action'] ?? ''; // 'update' or 'out'

if (!$product_id) {
    die(json_encode(['success' => false, 'message' => 'Invalid product ID']));
}

try {
    if ($action === 'out') {
        $stmt = $pdo->prepare("UPDATE products SET stock = 0 WHERE id = ?");
        $stmt->execute([$product_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $stmt->execute([$stock, $product_id]);
    }
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>