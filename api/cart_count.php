<?php
// api/cart_count.php
session_start();
$count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    // Remove expired cart items (older than 1 hour)
    $now = time();
    foreach ($_SESSION['cart'] as $key => $item) {
        if (isset($item['added_at']) && $now - $item['added_at'] > 3600) {
            unset($_SESSION['cart'][$key]);
        }
    }
    $count = array_sum(array_column($_SESSION['cart'], 'quantity'));
}
header('Content-Type: application/json');
echo json_encode(['count' => $count]);
