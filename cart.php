<?php include 'includes/auth.php'; ?>
<?php include 'includes/header.php'; ?>
<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? '';
$cart = $_SESSION['cart'] ?? [];
$now = time();
$cart_items = [];
$total = 0;
$cart_expiry_warning = false;

// Remove expired items and build cart items with product info
foreach ($cart as $key => $item) {
    if (isset($item['added_at']) && $now - $item['added_at'] > 3600) {
        unset($_SESSION['cart'][$key]);
        continue;
    }
    // Fetch product and variant info
    $product_id = (int)$item['product_id'];
    $variant_id = $item['variant_id'] ?? null;
    $stmt = $pdo->prepare("SELECT p.name, p.featured_image, p.base_price, v.size, v.color, v.price_override, v.stock FROM products p LEFT JOIN product_variants v ON v.id = ? WHERE p.id = ?");
    $stmt->execute([$variant_id, $product_id]);
    $row = $stmt->fetch();
    if (!$row) continue;
    $price = $row['price_override'] ?? $row['base_price'];
    $subtotal = $price * $item['quantity'];
    $total += $subtotal;
    $cart_items[] = [
        'key' => $key,
        'product_id' => $product_id,
        'variant_id' => $variant_id,
        'name' => $row['name'],
        'img' => $row['featured_image'] ?: '/SoleHub/assets/img/no-image.png',
        'size' => $row['size'],
        'color' => $row['color'],
        'price' => $price,
        'quantity' => $item['quantity'],
        'stock' => $row['stock'],
        'subtotal' => $subtotal,
        'added_at' => $item['added_at']
    ];
    if ($now - $item['added_at'] > 3300 && $now - $item['added_at'] < 3600) {
        $cart_expiry_warning = true;
    }
}

// Fetch address and phone for user from users table
$address = '';
$phone = '';
if ($user_id) {
    $stmt = $pdo->prepare("SELECT phone, address FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    if ($row) {
        $phone = $row['phone'] ?? '';
        $address = $row['address'] ?? '';
    }
}
$missing_info = empty($address) || empty($phone);
?>
<main class="flex-1 bg-gradient-to-br from-blue-50 to-white min-h-screen">
  <section class="container mx-auto px-4 py-8 max-w-4xl">
    <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">Your Cart</h1>
    <?php if ($cart_expiry_warning): ?>
      <div class="mb-6 p-3 bg-yellow-100 text-yellow-800 rounded text-center">Some items in your cart will expire soon. Please checkout to avoid losing them.</div>
    <?php endif; ?>
    <div class="mb-8 max-w-lg mx-auto bg-white rounded shadow p-6">
      <h2 class="text-xl font-semibold mb-4 text-gray-800 flex items-center gap-2"><svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12c2.7 0 8 1.34 8 4v2H4v-2c0-2.66 5.3-4 8-4zm0-2a4 4 0 100-8 4 4 0 000 8z"/></svg>Check Your Info Before Checkout</h2>
      <div class="mb-2"><span class="font-medium text-gray-700">Name:</span> <?= htmlspecialchars($user_name) ?></div>
      <div class="mb-2"><span class="font-medium text-gray-700">Phone:</span> <?= $phone ? htmlspecialchars($phone) : '<span class=\'text-red-500\'>Not set</span>' ?></div>
      <div class="mb-2"><span class="font-medium text-gray-700">Address:</span> <?= $address ? htmlspecialchars($address) : '<span class=\'text-red-500\'>Not set</span>' ?></div>
      <a href="/SoleHub/profile.php" class="text-blue-600 underline text-sm">Edit Profile</a>
      <?php if ($missing_info): ?>
        <div class="mt-3 text-red-600 text-sm">Please fill in your address and phone before checkout.</div>
      <?php endif; ?>
    </div>
    <?php if (empty($cart_items)): ?>
      <div class="text-center text-gray-500 py-16 text-lg">Your cart is empty.<br><a href="/SoleHub/products.php" class="text-blue-600 underline">Continue Shopping</a></div>
    <?php else: ?>
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-xl shadow">
        <thead>
          <tr class="bg-gray-100 text-gray-700">
            <th class="p-3 text-left">Product</th>
            <th class="p-3 text-left">Variant</th>
            <th class="p-3 text-right">Price</th>
            <th class="p-3 text-center">Quantity</th>
            <th class="p-3 text-right">Subtotal</th>
            <th class="p-3 text-center">Remove</th>
          </tr>
        </thead>
        <tbody id="cart-table-body">
          <?php foreach ($cart_items as $item): ?>
          <tr class="border-b">
            <td class="p-3 flex items-center gap-3">
              <img src="<?= htmlspecialchars($item['img']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="h-16 w-16 object-contain rounded">
              <span><?= htmlspecialchars($item['name']) ?></span>
            </td>
            <td class="p-3">
              <?php if ($item['size']): ?>Size: <?= htmlspecialchars($item['size']) ?><br><?php endif; ?>
              <?php if ($item['color']): ?>Color: <?= htmlspecialchars($item['color']) ?><?php endif; ?>
            </td>
            <td class="p-3 text-right">$<?= number_format($item['price'], 2) ?></td>
            <td class="p-3 text-center">
              <input type="number" min="1" max="<?= (int)$item['stock'] ?>" value="<?= (int)$item['quantity'] ?>" data-key="<?= htmlspecialchars($item['key']) ?>" class="cart-qty-input w-16 border rounded px-2 py-1 text-center">
            </td>
            <td class="p-3 text-right">$<?= number_format($item['subtotal'], 2) ?></td>
            <td class="p-3 text-center">
              <button class="remove-cart-item text-red-500 hover:text-red-700" data-key="<?= htmlspecialchars($item['key']) ?>" title="Remove">
                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="flex flex-col md:flex-row justify-between items-center mt-8 gap-4">
      <div class="text-xl font-bold">Total: <span id="cart-total">$<?= number_format($total, 2) ?></span></div>
      <a href="/SoleHub/checkout.php" id="checkout-btn" class="bg-blue-600 text-white px-8 py-3 rounded hover:bg-blue-700 transition text-lg<?php if ($missing_info): ?> opacity-50 cursor-not-allowed pointer-events-none<?php endif; ?>">Proceed to Checkout</a>
    </div>
    <?php endif; ?>
  </section>
</main>
<script>
// Update quantity
function updateCartQuantity(key, qty) {
  fetch('/SoleHub/api/cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=update&product_id=${encodeURIComponent(key.split('_')[0])}&variant_id=${encodeURIComponent(key.split('_')[1] || '')}&quantity=${qty}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      location.reload();
    }
  });
}
// Remove item
function removeCartItem(key) {
  fetch('/SoleHub/api/cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=remove&product_id=${encodeURIComponent(key.split('_')[0])}&variant_id=${encodeURIComponent(key.split('_')[1] || '')}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      location.reload();
    }
  });
}
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.cart-qty-input').forEach(function(input) {
    input.addEventListener('change', function() {
      let qty = Math.max(1, parseInt(this.value));
      updateCartQuantity(this.dataset.key, qty);
    });
  });
  document.querySelectorAll('.remove-cart-item').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      removeCartItem(this.dataset.key);
    });
  });
  // Checkout button validation
  var checkoutBtn = document.getElementById('checkout-btn');
  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', function(e) {
      <?php if ($missing_info): ?>
      e.preventDefault();
      alert('Please fill in your address and phone in your profile before checkout.');
      <?php endif; ?>
    });
  }
});
</script>
<?php include 'includes/footer.php'; ?>
