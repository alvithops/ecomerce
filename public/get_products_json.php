<?php
require_once 'inc/db.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    die(json_encode([]));
}

try {
    $stmt = $pdo->query("SELECT id, name, stock FROM products ORDER BY name ASC");
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    echo json_encode([]);
}
?>