<?php
// api/cart.php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
$action = $_POST['action'] ?? '';
$product_id = $_POST['product_id'] ?? 0;
$variant_id = $_POST['variant_id'] ?? null;
$quantity = max(1, (int)($_POST['quantity'] ?? 1));
$key = $variant_id ? $product_id . '_' . $variant_id : $product_id;
$now = time();
// Remove expired items
foreach ($_SESSION['cart'] as $k => $item) {
    if (isset($item['added_at']) && $now - $item['added_at'] > 3600) {
        unset($_SESSION['cart'][$k]);
    }
}

switch ($action) {
    case 'add':
        if (!isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key] = [
                'product_id' => $product_id,
                'variant_id' => $variant_id,
                'quantity' => $quantity,
                'added_at' => $now
            ];
        } else {
            $_SESSION['cart'][$key]['quantity'] += $quantity;
            $_SESSION['cart'][$key]['added_at'] = $now;
        }
        echo json_encode(['success' => true, 'message' => 'Added to cart', 'expires_in' => 3600]);
        break;
    case 'remove':
        unset($_SESSION['cart'][$key]);
        echo json_encode(['success' => true, 'message' => 'Removed from cart']);
        break;
    case 'update':
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['quantity'] = $quantity;
            $_SESSION['cart'][$key]['added_at'] = $now;
            echo json_encode(['success' => true, 'message' => 'Cart updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Item not found in cart']);
        }
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
