<?php
require_once 'inc/db.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$title = $_POST['title'] ?? '';
$message = $_POST['message'] ?? '';
$type = $_POST['type'] ?? 'Pengumuman';

if (empty($title) || empty($message)) {
    die(json_encode(['success' => false, 'message' => 'Title and Message are required']));
}

try {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (NULL, ?, ?, ?)");
    $stmt->execute([$title, $message, $type]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>