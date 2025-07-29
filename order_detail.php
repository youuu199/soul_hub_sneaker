<?php
include 'includes/auth.php';
include 'includes/header.php';
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$user_id = $_SESSION['user_id'] ?? null;
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$user_id || !$order_id) {
    header('Location: /SoleHub/orders.php');
    exit;
}
// Fetch order
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();
if (!$order) {
    echo '<div class="container mx-auto px-4 py-16 text-center text-lg text-red-600">Order not found. <a href="/SoleHub/orders.php" class="text-blue-600 underline">Back to Orders</a></div>';
    include 'includes/footer.php';
    exit;
}
// Fetch order items
$stmt = $pdo->prepare('SELECT oi.*, p.name, v.size, v.color FROM order_items oi JOIN product_variants v ON oi.product_variant_id = v.id JOIN products p ON v.product_id = p.id WHERE oi.order_id = ?');
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>
<main class="flex-1 bg-gradient-to-br from-blue-50 to-white min-h-screen">
  <section class="container mx-auto px-4 py-8 max-w-3xl">
    <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">Order #<?= htmlspecialchars($order['id']) ?></h1>
    <div class="mb-6 p-4 bg-white rounded shadow">
      <div class="mb-2"><span class="font-medium text-gray-700">Date:</span> <?= htmlspecialchars(date('Y-m-d H:i', strtotime($order['created_at']))) ?></div>
      <div class="mb-2"><span class="font-medium text-gray-700">Status:</span> <span class="px-2 py-1 rounded text-xs font-semibold <?php
        switch($order['status']) {
          case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
          case 'processing': echo 'bg-blue-100 text-blue-800'; break;
          case 'shipped': echo 'bg-green-100 text-green-800'; break;
          case 'cancelled': echo 'bg-red-100 text-red-800'; break;
          default: echo 'bg-gray-100 text-gray-800';
        }
      ?>"><?= htmlspecialchars(ucfirst($order['status'])) ?></span></div>
      <div class="mb-2"><span class="font-medium text-gray-700">Total:</span> $<?= number_format($order['total'], 2) ?></div>
    </div>
    <div class="mb-8">
      <h2 class="text-xl font-semibold mb-4 text-gray-800">Order Items</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-xl shadow">
          <thead>
            <tr class="bg-gray-100 text-gray-700">
              <th class="p-3 text-left">Product</th>
              <th class="p-3 text-left">Variant</th>
              <th class="p-3 text-center">Quantity</th>
              <th class="p-3 text-right">Unit Price</th>
              <th class="p-3 text-right">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php $all_total = 0; foreach ($items as $item): $row_total = $item['unit_price'] * $item['quantity']; $all_total += $row_total; ?>
            <tr class="border-b">
              <td class="p-3"><?= htmlspecialchars($item['name']) ?></td>
              <td class="p-3">
                <?php if ($item['size']): ?>Size: <?= htmlspecialchars($item['size']) ?><br><?php endif; ?>
                <?php if ($item['color']): ?>Color: <?= htmlspecialchars($item['color']) ?><?php endif; ?>
              </td>
              <td class="p-3 text-center"><?= (int)$item['quantity'] ?></td>
              <td class="p-3 text-right">$<?= number_format($item['unit_price'], 2) ?></td>
              <td class="p-3 text-right">$<?= number_format($row_total, 2) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4" class="p-3 text-right font-bold">All Total:</td>
              <td class="p-3 text-right font-bold">$<?= number_format($all_total, 2) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    <div class="text-center mt-8">
      <a href="/SoleHub/orders.php" class="text-blue-600 underline">&larr; Back to Orders</a>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
