<?php
require_once '../config/db.php';
header('Content-Type: application/json');

class SalesAnalytics {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getTop($type = 'product') {
        switch ($type) {
            case 'category':
                $sql = "SELECT c.name, SUM(oi.unit_price * oi.quantity) as total_sales
                    FROM order_items oi
                    JOIN product_variants pv ON oi.product_variant_id = pv.id
                    JOIN products p ON pv.product_id = p.id
                    JOIN categories c ON p.category_id = c.id
                    JOIN orders o ON oi.order_id = o.id
                    WHERE o.status = 'shipped'
                    GROUP BY c.id
                    ORDER BY total_sales DESC LIMIT 5";
                break;
            case 'brand':
                $sql = "SELECT b.name, SUM(oi.unit_price * oi.quantity) as total_sales
                    FROM order_items oi
                    JOIN product_variants pv ON oi.product_variant_id = pv.id
                    JOIN products p ON pv.product_id = p.id
                    JOIN brands b ON p.brand_id = b.id
                    JOIN orders o ON oi.order_id = o.id
                    WHERE o.status = 'shipped'
                    GROUP BY b.id
                    ORDER BY total_sales DESC LIMIT 5";
                break;
            default:
                $sql = "SELECT p.name, SUM(oi.unit_price * oi.quantity) as total_sales
                    FROM order_items oi
                    JOIN product_variants pv ON oi.product_variant_id = pv.id
                    JOIN products p ON pv.product_id = p.id
                    JOIN orders o ON oi.order_id = o.id
                    WHERE o.status = 'shipped'
                    GROUP BY pv.product_id
                    ORDER BY total_sales DESC LIMIT 5";
        }
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function getSummary() {
        $sql = "SELECT SUM(oi.unit_price * oi.quantity) as total_sales, COUNT(DISTINCT o.id) as orders
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status = 'shipped'";
        return $this->pdo->query($sql)->fetch();
    }

    public function getBestDay() {
        $sql = "SELECT DAYNAME(o.created_at) as day, SUM(oi.unit_price * oi.quantity) as total_sales
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status = 'shipped'
            GROUP BY day
            ORDER BY total_sales DESC LIMIT 1";
        return $this->pdo->query($sql)->fetchColumn();
    }

    public static function getAnalytics($pdo, $type = 'product') {
        $analytics = new self($pdo);
        $rows = $analytics->getTop($type);
        $summary = $analytics->getSummary();
        $best_day = $analytics->getBestDay();
        $labels = array_column($rows, 'name');
        $data = array_map('floatval', array_column($rows, 'total_sales'));
        $top = $labels[0] ?? '-';
        $topList = array_map(function($row) {
            return $row['name'] . ' <span class=\'text-blue-700 font-bold\'>$' . number_format($row['total_sales'], 2) . '</span>';
        }, $rows);
        return [
            'labels' => $labels,
            'data' => $data,
            'summary' => [
                'total_sales' => number_format($summary['total_sales'] ?? 0, 2),
                'orders' => $summary['orders'] ?? 0,
                'top' => $top,
                'best_day' => $best_day ?: '-',
            ],
            'topList' => $topList,
        ];
    }
}

$type = $_GET['type'] ?? 'product';
echo json_encode(SalesAnalytics::getAnalytics($pdo, $type));
