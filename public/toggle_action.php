<?php
require_once 'inc/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$type = $_POST['type'] ?? ''; // 'like' or 'save'
$product_id = (int) ($_POST['product_id'] ?? 0);

if (!$type || !$product_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$table = ($type === 'like') ? 'user_likes' : 'user_saves';

try {
    // Check if exists
    $stmt = $pdo->prepare("SELECT 1 FROM $table WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);

    if ($stmt->fetch()) {
        // Remove
        $stmt = $pdo->prepare("DELETE FROM $table WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        // Add
        $stmt = $pdo->prepare("INSERT INTO $table (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $product_id]);
        echo json_encode(['success' => true, 'action' => 'added']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>