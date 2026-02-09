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

$action = $_GET['action'] ?? 'get_messages';

try {
    if ($action === 'get_conversations' && $isAdmin) {
        // Get list of conversations for admin (latest message from each user per product)
        $stmt = $pdo->prepare("
            SELECT c.*, u.nama_pengguna as user_name, p.name as product_name 
            FROM chats c
            JOIN users u ON c.user_id = u.id
            JOIN products p ON c.product_id = p.id
            WHERE c.id IN (
                SELECT MAX(id) FROM chats GROUP BY user_id, product_id
            )
            ORDER BY c.created_at DESC
        ");
        $stmt->execute();
        $conversations = $stmt->fetchAll();
        echo json_encode(['success' => true, 'conversations' => $conversations]);
    } else {
        // Get messages for a specific conversation
        $targetUserId = $isAdmin ? ($_GET['user_id'] ?? null) : $userId;
        $productId = $_GET['product_id'] ?? null;

        if (!$targetUserId || !$productId) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            exit();
        }

        $stmt = $pdo->prepare("
            SELECT * FROM chats 
            WHERE user_id = ? AND product_id = ? 
            ORDER BY created_at ASC
        ");
        $stmt->execute([$targetUserId, $productId]);
        $messages = $stmt->fetchAll();

        // Mark as read if admin is viewing or user is viewing admin replies
        if ($isAdmin) {
            $update = $pdo->prepare("UPDATE chats SET is_read = 1 WHERE user_id = ? AND product_id = ? AND is_admin = 0");
            $update->execute([$targetUserId, $productId]);
        } else {
            $update = $pdo->prepare("UPDATE chats SET is_read = 1 WHERE user_id = ? AND product_id = ? AND is_admin = 1");
            $update->execute([$userId, $productId]);
        }

        echo json_encode(['success' => true, 'messages' => $messages]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>