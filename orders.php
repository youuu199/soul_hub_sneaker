<?php
include 'includes/auth.php';
include 'includes/header.php';
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: /SoleHub/login.php');
    exit;
}
// Fetch orders for user
$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<main class="flex-1 bg-gradient-to-br from-blue-50 to-white min-h-screen">
  <section class="container mx-auto px-4 py-8 max-w-4xl">
    <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">My Orders</h1>
    <?php if (empty($orders)): ?>
      <div class="text-center text-gray-500 py-16 text-lg">You have no orders yet.<br><a href="/SoleHub/products.php" class="text-blue-600 underline">Shop now</a></div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-xl shadow">
          <thead>
            <tr class="bg-gray-100 text-gray-700">
              <th class="p-3 text-left">Order #</th>
              <th class="p-3 text-left">Date</th>
              <th class="p-3 text-right">Total</th>
              <th class="p-3 text-center">Status</th>
              <th class="p-3 text-center">Details</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $order): ?>
            <tr class="border-b">
              <td class="p-3">#<?= htmlspecialchars($order['id']) ?></td>
              <td class="p-3"><?= htmlspecialchars(date('Y-m-d H:i', strtotime($order['created_at']))) ?></td>
              <td class="p-3 text-right">$<?= number_format($order['total'], 2) ?></td>
              <td class="p-3 text-center">
                <span class="px-2 py-1 rounded text-xs font-semibold <?php
                  switch($order['status']) {
                    case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                    case 'processing': echo 'bg-blue-100 text-blue-800'; break;
                    case 'shipped': echo 'bg-green-100 text-green-800'; break;
                    case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                    default: echo 'bg-gray-100 text-gray-800';
                  }
                ?>"><?= htmlspecialchars(ucfirst($order['status'])) ?></span>
              </td>
              <td class="p-3 text-center">
                <a href="/SoleHub/order_detail.php?id=<?= htmlspecialchars($order['id']) ?>" class="text-blue-600 underline">View</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
