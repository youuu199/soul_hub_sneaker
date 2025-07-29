<?php
require_once '../config/db.php';
header('Content-Type: application/json');

class ManageOrders {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function fetchOrders($filters) {
        $page = max(1, (int)($filters['page'] ?? 1));
        $per_page = 15;
        $offset = ($page - 1) * $per_page;
        $where = [];
        $params = [];
        if (!empty($filters['order_id'])) {
            $where[] = 'o.id = ?';
            $params[] = $filters['order_id'];
        }
        if (!empty($filters['customer'])) {
            $where[] = '(u.name LIKE ? OR u.email LIKE ?)';

            $params[] = '%' . $filters['customer'] . '%';
            $params[] = '%' . $filters['customer'] . '%';
        }
        if (!empty($filters['status'])) {
            $where[] = 'o.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'DATE(o.created_at) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'DATE(o.created_at) <= ?';
            $params[] = $filters['date_to'];
        }
        $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $count_sql = "SELECT COUNT(*) FROM orders o LEFT JOIN users u ON o.user_id = u.id $where_sql";
        $count_stmt = $this->pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_orders = (int)$count_stmt->fetchColumn();
        $total_pages = max(1, ceil($total_orders / $per_page));
        $sql = "SELECT o.id, o.total, o.status, o.created_at, u.name as customer_name, u.email as customer_email FROM orders o LEFT JOIN users u ON o.user_id = u.id $where_sql ORDER BY o.created_at DESC LIMIT $per_page OFFSET $offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();
        return [
            'orders' => $orders,
            'total_pages' => $total_pages,
            'page' => $page
        ];
    }
    public function fetchOrder($order_id) {
        $stmt = $this->pdo->prepare('SELECT o.*, u.name as customer_name, u.email as customer_email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?');
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($order) {
            // Shipping address
            $order['shipping_address'] = '';
            if (!empty($order['shipping_address_id'])) {
                $addrStmt = $this->pdo->prepare('SELECT full_address, city, state, zip, country FROM addresses WHERE id = ?');
                $addrStmt->execute([$order['shipping_address_id']]);
                $addr = $addrStmt->fetch(PDO::FETCH_ASSOC);
                if ($addr) {
                    $order['shipping_address'] = $addr['full_address'] . ', ' . $addr['city'] . ', ' . $addr['state'] . ', ' . $addr['zip'] . ', ' . $addr['country'];
                }
            }
            // Order items
            $itemStmt = $this->pdo->prepare('SELECT oi.*, p.name as product_name, CONCAT(v.size, " / ", v.color) as variant FROM order_items oi INNER JOIN product_variants v ON oi.product_variant_id = v.id INNER JOIN products p ON v.product_id = p.id WHERE oi.order_id = ?');
            $itemStmt->execute([$order_id]);
            $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($items as &$item) {
                $item['qty'] = $item['quantity'];
                $item['total'] = $item['unit_price'] * $item['quantity'];
            }
            $order['items'] = $items;
        }
        return $order;
    }
    public function updateOrderStatus($order_id, $action) {
        $allowed = ['processing', 'shipped', 'cancelled'];
        if (!in_array($action, $allowed)) return ['message' => 'Invalid action.'];
        // If marking as processing, reduce stock for each item
        if ($action === 'processing') {
            $itemStmt = $this->pdo->prepare('SELECT product_variant_id, quantity FROM order_items WHERE order_id = ?');
            $itemStmt->execute([$order_id]);
            $items = $itemStmt->fetchAll();
            foreach ($items as $item) {
                $updateStock = $this->pdo->prepare('UPDATE product_variants SET stock = GREATEST(stock - ?, 0) WHERE id = ?');
                $updateStock->execute([$item['quantity'], $item['product_variant_id']]);
            }
        }
        $stmt = $this->pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$action, $order_id]);
        $msg = '';
        switch ($action) {
            case 'processing': $msg = 'Order marked as processing and stock updated.'; break;
            case 'shipped': $msg = 'Order marked as shipped.'; break;
            case 'cancelled': $msg = 'Order cancelled.'; break;
        }
        return ['message' => $msg];
    }
}

$ordersApi = new ManageOrders($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($_GET['order_id'])) {
        $order = $ordersApi->fetchOrder((int)$_GET['order_id']);
        echo json_encode($order);
        exit;
    }
    $filters = [
        'order_id' => $_GET['order_id'] ?? null,
        'customer' => $_GET['customer'] ?? null,
        'status' => $_GET['status'] ?? null,
        'date_from' => $_GET['date_from'] ?? null,
        'date_to' => $_GET['date_to'] ?? null,
        'page' => $_GET['page'] ?? 1
    ];
    $result = $ordersApi->fetchOrders($filters);
    echo json_encode($result);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $order_id = (int)($input['order_id'] ?? 0);
    $action = $input['action'] ?? '';
    if ($order_id && $action) {
        $result = $ordersApi->updateOrderStatus($order_id, $action);
        echo json_encode($result);
        exit;
    }
    echo json_encode(['message' => 'Invalid request.']);
    exit;
}
