<?php
require_once 'inc/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false]));
}

$user_id = $_SESSION['user_id'];
$enabled = $_POST['enabled'] === 'true' ? 1 : 0;

try {
    $stmt = $pdo->prepare("UPDATE users SET notifications_enabled = ? WHERE id = ?");
    $stmt->execute([$enabled, $user_id]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>