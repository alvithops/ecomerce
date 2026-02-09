<?php
require_once 'inc/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? 'all';

try {
    $data = ['success' => true];

    if ($action === 'all' || $action === 'records') {
        // Fetch latest 10 orders
        $stmt = $pdo->query("
            SELECT o.*, u.nama_pengguna 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC 
            LIMIT 10
        ");
        $data['records'] = $stmt->fetchAll();
    }

    if ($action === 'all' || $action === 'messages') {
        // Fetch latest 5 messages from buyers
        $stmt = $pdo->query("
            SELECT c.*, u.nama_pengguna 
            FROM chats c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.is_admin = 0 
            ORDER BY c.created_at DESC 
            LIMIT 5
        ");
        $data['messages'] = $stmt->fetchAll();
    }

    if ($action === 'all' || $action === 'stats') {
        // Best Selling Products (by quantity)
        $stmt = $pdo->query("
            SELECT p.name, SUM(oi.quantity) as total_sold 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            GROUP BY oi.product_id 
            ORDER BY total_sold DESC 
            LIMIT 5
        ");
        $data['stats']['best_selling'] = $stmt->fetchAll();

        // Weekly Stats (last 7 days profit)
        $weekly = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $stmt = $pdo->prepare("SELECT SUM(total_price) as total FROM orders WHERE DATE(created_at) = ? AND status = 'Selesai'");
            $stmt->execute([$date]);
            $res = $stmt->fetch();
            $weekly[] = ['date' => date('D', strtotime($date)), 'total' => (float) ($res['total'] ?? 0)];
        }
        $data['stats']['weekly'] = $weekly;

        // Monthly Stats (last 4 weeks profit)
        $monthly = [];
        for ($i = 3; $i >= 0; $i--) {
            $start = date('Y-m-d', strtotime("-$i week -" . date('w') . " days"));
            $end = date('Y-m-d', strtotime("$start +6 days"));
            $stmt = $pdo->prepare("SELECT SUM(total_price) as total FROM orders WHERE DATE(created_at) BETWEEN ? AND ? AND status = 'Selesai'");
            $stmt->execute([$start, $end]);
            $res = $stmt->fetch();
            $monthly[] = ['label' => "Minggu " . (4 - $i), 'total' => (float) ($res['total'] ?? 0)];
        }
        $data['stats']['monthly'] = $monthly;

        // Yearly Stats (by Quarter)
        $yearly = [];
        $year = date('Y');
        $quarters = [
            ['1', '3', 'Jan-Mar'],
            ['4', '6', 'Apr-Jun'],
            ['7', '9', 'Jul-Sep'],
            ['10', '12', 'Okt-Des']
        ];
        foreach ($quarters as $q) {
            $stmt = $pdo->prepare("SELECT SUM(total_price) as total FROM orders WHERE YEAR(created_at) = ? AND MONTH(created_at) BETWEEN ? AND ? AND status = 'Selesai'");
            $stmt->execute([$year, $q[0], $q[1]]);
            $res = $stmt->fetch();
            $yearly[] = ['label' => $q[2], 'total' => (float) ($res['total'] ?? 0)];
        }
        $data['stats']['yearly'] = $yearly;
    }

    echo json_encode($data);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>