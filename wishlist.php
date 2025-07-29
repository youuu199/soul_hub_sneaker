<?php include 'includes/auth.php'; ?><?php
include 'includes/header.php';
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo '<div class="container mx-auto px-4 py-16 text-center text-lg text-red-600">Please <a href="/SoleHub/login.php" class="text-blue-600 underline">login</a> to view your wishlist.</div>';
    include 'includes/footer.php';
    exit;
}
// Wishlist table now uses product_id
$stmt = $pdo->prepare("SELECT p.id, p.name, p.base_price as price, p.featured_image as img
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ?");
$stmt->execute([$user_id]);
$products = $stmt->fetchAll();

// AJAX partial rendering for wishlist grid
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    if (empty($products)) {
        echo '<div class="text-center text-gray-500">Your wishlist is empty.</div>';
    } else {
        echo '<div id="wishlist-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">';
        foreach ($products as $product) {
            echo '<div class="bg-white rounded-xl shadow p-4 flex flex-col items-center">';
            echo '<img src="' . htmlspecialchars($product['img'] ?: '/SoleHub/assets/img/no-image.png') . '" alt="' . htmlspecialchars($product['name']) . '" class="h-32 object-contain mb-4">';
            echo '<h3 class="font-semibold text-lg mb-2">' . htmlspecialchars($product['name']) . '</h3>';
            echo '<p class="text-blue-600 font-bold mb-2">$' . htmlspecialchars($product['price']) . '</p>';
            echo '<button class="remove-wishlist-btn w-full bg-pink-100 text-pink-600 px-4 py-2 rounded hover:bg-pink-200 transition" data-id="' . htmlspecialchars($product['id']) . '">Remove</button>';
            echo '<a href="/SoleHub/product_detail.php?id=' . htmlspecialchars($product['id']) . '" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition w-full text-center mt-2">View</a>';
            echo '</div>';
        }
        echo '</div>';
    }
    exit;
}
?>
<main class="flex-1 bg-gradient-to-br from-blue-50 to-white min-h-screen">
  <section class="container mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">My Wishlist</h1>
    <?php if (empty($products)): ?>
      <div class="text-center text-gray-500">Your wishlist is empty.</div>
    <?php else: ?>
      <div id="wishlist-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
        <?php foreach ($products as $product): ?>
          <div class="bg-white rounded-xl shadow p-4 flex flex-col items-center">
            <img src="<?= htmlspecialchars($product['img'] ?: '/SoleHub/assets/img/no-image.png') ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="h-32 object-contain mb-4">
            <h3 class="font-semibold text-lg mb-2"><?= htmlspecialchars($product['name']) ?></h3>
            <p class="text-blue-600 font-bold mb-2">$<?= htmlspecialchars($product['price']) ?></p>
            <button class="remove-wishlist-btn w-full bg-pink-100 text-pink-600 px-4 py-2 rounded hover:bg-pink-200 transition" data-id="<?= htmlspecialchars($product['id']) ?>">Remove</button>
            <a href="/SoleHub/product_detail.php?id=<?= htmlspecialchars($product['id']) ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition w-full text-center mt-2">View</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
<script>
document.addEventListener('click', function(e) {
  const btn = e.target.closest('.remove-wishlist-btn');
  if (btn) {
    const id = btn.getAttribute('data-id');
    btn.disabled = true;
    fetch('/SoleHub/includes/user/wishlist_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=remove&product_id=${id}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        // Reload wishlist grid via AJAX
        fetch(window.location.pathname + '?ajax=1')
          .then(r => r.text())
          .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newGrid = doc.getElementById('wishlist-grid');
            const grid = document.getElementById('wishlist-grid');
            if (newGrid && grid) {
              grid.outerHTML = newGrid.outerHTML;
            } else if (!newGrid && grid) {
              // If no grid returned, show empty message
              grid.outerHTML = '<div class="text-center text-gray-500">Your wishlist is empty.</div>';
            }
          });
      } else {
        btn.disabled = false;
        alert(data.message);
      }
    });
  }
});
</script>
