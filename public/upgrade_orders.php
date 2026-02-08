<?php
require_once 'inc/db.php';

try {
    echo "Upgrading 'orders' table...<br>";

    // 1. Update ENUM for payment_method
    $pdo->exec("ALTER TABLE orders MODIFY COLUMN payment_method VARCHAR(50) NOT NULL");
    echo "Updated payment_method column to VARCHAR to support multiple options.<br>";

    // 2. Add acc_number column if not exists
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'acc_number'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN acc_number VARCHAR(100) DEFAULT NULL AFTER payment_method");
        echo "Added 'acc_number' column.<br>";
    } else {
        echo "'acc_number' column already exists.<br>";
    }

    echo "Upgrade completed. <a href='checkout.php'>Back to Checkout</a>";
} catch (PDOException $e) {
    die("Error upgrading database: " . $e->getMessage());
}
?>