<?php include 'includes/auth.php'; ?>
<?php include 'includes/header.php'; ?>
<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$user_id = $_SESSION['user_id'] ?? null;
$cart = $_SESSION['cart'] ?? [];
$order_id = null;
$order_success = false;
$order_msg = '';

// Fetch user info for address/phone and status
$stmt = $pdo->prepare('SELECT name, email, phone, address, status FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$user_status = $user['status'] ?? 'active';
$missing_info = empty($user['phone']) || empty($user['address']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$missing_info && !empty($cart) && $user_status !== 'banned') {
    // Calculate total
    $total = 0;
    $order_items = [];
    foreach ($cart as $item) {
        $product_id = (int)$item['product_id'];
        $variant_id = $item['variant_id'] ?? null;
        $quantity = (int)$item['quantity'];
        $stmt = $pdo->prepare("SELECT price_override, base_price FROM product_variants v LEFT JOIN products p ON v.product_id = p.id WHERE v.id = ? AND p.id = ?");
        $stmt->execute([$variant_id, $product_id]);
        $row = $stmt->fetch();
        $price = $row && $row['price_override'] ? $row['price_override'] : ($row['base_price'] ?? 0);
        $subtotal = $price * $quantity;
        $total += $subtotal;
        $order_items[] = [
            'variant_id' => $variant_id,
            'quantity' => $quantity,
            'unit_price' => $price
        ];
    }
    // Insert order
    $stmt = $pdo->prepare('INSERT INTO orders (user_id, total, status) VALUES (?, ?, "pending")');
    if ($stmt->execute([$user_id, $total])) {
        $order_id = $pdo->lastInsertId();
        // Insert order items (skip if variant_id is null)
        $stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_variant_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
        foreach ($order_items as $oi) {
            if ($oi['variant_id']) {
                $stmt->execute([$order_id, $oi['variant_id'], $oi['quantity'], $oi['unit_price']]);
            }
        }
        // Clear cart
        $_SESSION['cart'] = [];
        $order_success = true;
        $order_msg = 'Order placed successfully! Your order is now pending.';
    } else {
        $order_msg = 'Failed to place order. Please try again.';
    }
}
?>
<main class="flex-1 bg-gradient-to-br from-blue-50 to-white min-h-screen">
  <section class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">Checkout</h1>
    <?php if ($user_status === 'banned'): ?>
      <div class="mb-6 p-4 bg-red-100 text-red-700 rounded text-center text-lg">
        Your account is <b>banned</b>. You cannot place orders. Please contact support if you believe this is a mistake.
      </div>
    <?php elseif ($user_status === 'suspended'): ?>
      <div class="mb-6 p-4 bg-yellow-100 text-yellow-800 rounded text-center text-lg">
        <b>Warning:</b> Your account is <b>suspended</b>. You can place orders, but please <a href="/SoleHub/contact.php" class="underline text-blue-600">contact the admin</a> to resolve your account status.
      </div>
    <?php endif; ?>
    <?php if ($order_success): ?>
      <div class="mb-6 p-4 bg-green-100 text-green-800 rounded text-center text-lg"><?= htmlspecialchars($order_msg) ?></div>
      <div class="text-center"><a href="/SoleHub/orders.php" class="text-blue-600 underline">View My Orders</a></div>
    <?php else: ?>
      <?php if ($missing_info): ?>
        <div class="mb-6 p-4 bg-red-100 text-red-700 rounded text-center">Please complete your phone and address in your <a href="/SoleHub/profile.php" class="underline">profile</a> before checking out.</div>
      <?php elseif (empty($cart)): ?>
        <div class="mb-6 p-4 bg-yellow-100 text-yellow-800 rounded text-center">Your cart is empty. <a href="/SoleHub/products.php" class="text-blue-600 underline">Shop now</a></div>
      <?php elseif ($user_status === 'banned'): ?>
        <!-- Block form for banned users -->
      <?php else: ?>
        <form method="post" id="checkout-form">
          <div class="mb-6 p-4 bg-white rounded shadow">
            <h2 class="text-xl font-semibold mb-2 text-gray-800">Confirm Your Order</h2>
            <div class="mb-2"><span class="font-medium text-gray-700">Name:</span> <?= htmlspecialchars($user['name']) ?></div>
            <div class="mb-2"><span class="font-medium text-gray-700">Phone:</span> <?= htmlspecialchars($user['phone']) ?></div>
            <div class="mb-2"><span class="font-medium text-gray-700">Address:</span> <?= htmlspecialchars($user['address']) ?></div>
            <div class="mb-2"><span class="font-medium text-gray-700">Email:</span> <?= htmlspecialchars($user['email']) ?></div>
            <div class="mt-4">
              <h3 class="font-semibold text-gray-800 mb-2">Order Items</h3>
              <ul class="divide-y divide-gray-200">
                <?php
                $total = 0;
                foreach ($cart as $item):
                  $product_id = (int)$item['product_id'];
                  $variant_id = $item['variant_id'] ?? null;
                  $quantity = (int)$item['quantity'];
                  $stmt = $pdo->prepare("SELECT p.name, v.size, v.color, v.price_override, p.base_price FROM products p LEFT JOIN product_variants v ON v.id = ? WHERE p.id = ?");
                  $stmt->execute([$variant_id, $product_id]);
                  $row = $stmt->fetch();
                  $name = $row['name'] ?? 'Product';
                  $size = $row['size'] ?? '';
                  $color = $row['color'] ?? '';
                  $price = $row && $row['price_override'] ? $row['price_override'] : ($row['base_price'] ?? 0);
                  $subtotal = $price * $quantity;
                  $total += $subtotal;
                ?>
                <li class="py-2 flex justify-between items-center">
                  <div>
                    <span class="font-medium text-gray-900"><?= htmlspecialchars($name) ?></span>
                    <?php if ($size): ?> <span class="text-xs text-gray-500 ml-2">Size: <?= htmlspecialchars($size) ?></span><?php endif; ?>
                    <?php if ($color): ?> <span class="text-xs text-gray-500 ml-2">Color: <?= htmlspecialchars($color) ?></span><?php endif; ?>
                    <span class="text-xs text-gray-500 ml-2">x<?= $quantity ?></span>
                  </div>
                  <div class="text-right text-gray-700">$<?= number_format($subtotal, 2) ?></div>
                </li>
                <?php endforeach; ?>
              </ul>
              <div class="mt-4 text-right font-bold text-lg">Total: $<?= number_format($total, 2) ?></div>
            </div>
          </div>
          <button type="submit" class="w-full bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700 text-lg">Place Order</button>
        </form>
      <?php endif; ?>
    <?php endif; ?>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  <?php if ($order_success): ?>
    setTimeout(function() {
      window.location.href = '/SoleHub/orders.php';
    }, 3000);
    if (window.Notification && Notification.permission === 'granted') {
      new Notification('Order placed! Your order is now pending.');
    } else if (window.Notification && Notification.permission !== 'denied') {
      Notification.requestPermission().then(function(permission) {
        if (permission === 'granted') new Notification('Order placed! Your order is now pending.');
      });
    }
  <?php endif; ?>
});
</script>
