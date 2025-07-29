<?php
require_once '../config/db.php';
header('Content-Type: application/json');

// Total Sales
$stmt = $pdo->query('SELECT SUM(total) as total_sales FROM orders WHERE status != "cancelled"');
$total_sales = $stmt->fetch()['total_sales'] ?? 0;
// Orders Today
$stmt = $pdo->prepare('SELECT COUNT(*) as orders_today FROM orders WHERE DATE(created_at) = CURDATE()');
$stmt->execute();
$orders_today = $stmt->fetch()['orders_today'] ?? 0;
// Low Stock Alerts
$stmt = $pdo->query('SELECT COUNT(*) as low_stock FROM product_variants WHERE stock <= 5');
$low_stock = $stmt->fetch()['low_stock'] ?? 0;
// Recent Orders
$stmt = $pdo->query('SELECT o.id, o.total, o.status, o.created_at, u.name as customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5');
$recent_orders = $stmt->fetchAll();

echo json_encode([
  'total_sales' => $total_sales,
  'orders_today' => $orders_today,
  'low_stock' => $low_stock,
  'recent_orders' => $recent_orders
]);
