<?php
require_once '../config/db.php';
header('Content-Type: application/json');

// Get sales for the last 7 days
$sales_trend = [];
$labels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('D', strtotime($date));
    $stmt = $pdo->prepare('SELECT SUM(total) as day_total FROM orders WHERE DATE(created_at) = ? AND status != "cancelled"');
    $stmt->execute([$date]);
    $sales_trend[] = (float)($stmt->fetch()['day_total'] ?? 0);
}
echo json_encode([
    'labels' => $labels,
    'data' => $sales_trend
]);
