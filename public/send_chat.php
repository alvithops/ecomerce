<?php
require_once 'inc/db.php';
session_start();

header('Content-Type: application/json');

$isAdmin = isset($_SESSION['admin_logged_in']);
$userId = $_SESSION['user_id'] ?? null;

if (!$isAdmin && !$userId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$productId = $_POST['product_id'] ?? null;
$message = trim($_POST['message'] ?? '');
$targetUserId = $isAdmin ? ($_POST['user_id'] ?? null) : $userId;

if ($productId === null || $message === '' || $targetUserId === null) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO chats (user_id, product_id, message, is_admin) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$targetUserId, $productId, $message, $isAdmin ? 1 : 0]);

    echo json_encode(['success' => true, 'message' => 'Message sent']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>