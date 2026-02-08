<?php
/**
 * Database Configuration
 * Professional Standard
 */

$host = 'localhost';
$db = 'luxury_shope';
$user = 'root';
$pass = ''; // Default XAMPP
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    if ($e->getCode() == 1049) {
        die("Database 'luxury_shope' belum dibuat. Silakan buat database 'luxury_shope' dulu di phpMyAdmin atau jalankan query: CREATE DATABASE luxury_shope;");
    }
    error_log($e->getMessage());
    die("Sistem sedang mengalami gangguan teknis. Mohon hubungi admin.");
}
?>