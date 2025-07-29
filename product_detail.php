<?php include 'includes/auth.php'; ?>
<?php include 'includes/header.php'; ?>
<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$product_id) {
    echo '<div class="container mx-auto px-4 py-16 text-center text-lg text-red-600">Product not found. <a href="/SoleHub/products.php" class="text-blue-600 underline">Back to Shop</a></div>';
    include 'includes/footer.php';
    exit;
}
// Fetch product details
$stmt = $pdo->prepare("SELECT p.*, b.name AS brand, c.name AS category FROM products p LEFT JOIN brands b ON p.brand_id = b.id LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();
if (!$product) {
    echo '<div class="container mx-auto px-4 py-16 text-center text-lg text-red-600">Product not found. <a href="/SoleHub/products.php" class="text-blue-600 underline">Back to Shop</a></div>';
    include 'includes/footer.php';
    exit;
}
// Fetch variants (size, color, stock)
$stmt = $pdo->prepare("SELECT id, size, color, stock, price_override FROM product_variants WHERE product_id = ?");
$stmt->execute([$product_id]);
$variants = $stmt->fetchAll();
?>
<main class="flex-1 bg-gradient-to-br from-blue-50 to-white min-h-screen">
  <section class="container mx-auto px-4 py-8 max-w-4xl">
    <button onclick="window.location.href='/SoleHub/products.php'" class="mb-6 flex items-center text-blue-600 hover:underline"><svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>Back to Shop</button>
    <div class="bg-white rounded-xl shadow p-6 flex flex-col md:flex-row gap-8">
      <img src="<?= htmlspecialchars($product['featured_image'] ?: '/SoleHub/assets/img/no-image.png') ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="h-64 w-64 object-contain mx-auto md:mx-0 mb-4 md:mb-0">
      <div class="flex-1 flex flex-col gap-4">
        <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($product['name']) ?></h1>
        <div class="text-gray-600">Brand: <?= htmlspecialchars($product['brand']) ?> | Category: <?= htmlspecialchars($product['category']) ?></div>
        <div class="text-blue-600 text-xl font-bold">$<?= htmlspecialchars($product['base_price']) ?></div>
        <div class="text-gray-700">Status: <span class="<?= $product['status'] === 'active' ? 'text-green-600' : 'text-red-600' ?> font-semibold"><?= ucfirst($product['status']) ?></span></div>
        <form id="variantForm" class="space-y-4" action="#" autocomplete="off" onsubmit="return false;">
          <div>
            <label class="block text-gray-700 mb-1">Size</label>
            <select name="size" class="w-full border rounded px-3 py-2" required>
              <option value="">Select size</option>
              <?php foreach (array_unique(array_column($variants, 'size')) as $size): ?>
                <option value="<?= htmlspecialchars($size) ?>"><?= htmlspecialchars($size) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Color  <span class="text-xs text-gray-500">(choose size first)</span></label>
            <select name="color" class="w-full border rounded px-3 py-2" required>
              <option value="">Select color</option>
              <?php foreach (array_unique(array_column($variants, 'color')) as $color): ?>
                <option value="<?= htmlspecialchars($color) ?>"><?= htmlspecialchars($color) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Quantity</label>
            <input type="number" name="quantity" min="1" value="1" class="w-24 border rounded px-3 py-2" required>
          </div>
          <div id="stockStatus" class="text-sm"></div>
          <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Add to Cart</button>
        </form>
        <div class="mt-4">
          <h2 class="text-lg font-semibold mb-1">Product Details</h2>
          <p class="text-gray-700"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        </div>
      </div>
    </div>
  </section>
</main>
<script>
const variants = <?php echo json_encode($variants); ?>;
const sizeSel = document.querySelector('select[name="size"]');
const colorSel = document.querySelector('select[name="color"]');
const stockStatus = document.getElementById('stockStatus');
const variantForm = document.getElementById('variantForm');

function updateStock() {
  const size = sizeSel.value;
  const color = colorSel.value;
  const variant = variants.find(v => v.size === size && v.color === color);
  if (variant) {
    stockStatus.textContent = variant.stock > 0 ? `Available (${variant.stock} in stock)` : 'Out of stock';
    stockStatus.className = variant.stock > 0 ? 'text-green-600' : 'text-red-600';
  } else {
    stockStatus.textContent = '';
    stockStatus.className = '';
  }
}

function getUniqueColorsForSize(size) {
  return [...new Set(variants.filter(v => v.size === size).map(v => v.color))];
}
function updateColorOptions() {
  const size = sizeSel.value;
  const colors = size ? getUniqueColorsForSize(size) : [];
  colorSel.innerHTML = '<option value="">Select color</option>';
  colors.forEach(color => {
    const opt = document.createElement('option');
    opt.value = color;
    opt.textContent = color;
    colorSel.appendChild(opt);
  });
  colorSel.disabled = !size;
}

sizeSel.addEventListener('change', function() {
  updateColorOptions();
  updateStock();
});
colorSel.addEventListener('change', updateStock);

// Initial population
updateColorOptions();
updateStock();

// AJAX Add to Cart
variantForm.addEventListener('submit', function(e) {
  e.preventDefault();
  e.stopPropagation();
  const size = sizeSel.value;
  const color = colorSel.value;
  const quantity = variantForm.quantity.value;
  if (!size || !color) {
    stockStatus.textContent = 'Please select size and color.';
    stockStatus.className = 'text-red-600';
    return false;
  }
  const variant = variants.find(v => v.size === size && v.color === color);
  if (!variant) {
    stockStatus.textContent = 'Variant not found.';
    stockStatus.className = 'text-red-600';
    return false;
  }
  fetch('/SoleHub/api/cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=add&product_id=<?php echo $product_id; ?>&variant_id=${variant.id}&quantity=${quantity}`
  })
  .then(res => res.json())
  .then(data => {
    stockStatus.textContent = data.message;
    stockStatus.className = data.success ? 'text-green-600' : 'text-red-600';
    if (data.success) {
      showCartToast('Item added to cart!');
      if (typeof updateCartCount === 'function') updateCartCount();
    }
  });
  return false;
});

function showCartToast(message) {
  let toast = document.getElementById('cart-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'cart-toast';
    toast.className = 'fixed top-6 right-6 z-50 px-4 py-2 rounded shadow text-white text-sm font-semibold bg-blue-600';
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.style.opacity = '1';
  setTimeout(() => { toast.style.opacity = '0'; }, 1800);
}
</script>
<?php include 'includes/footer.php'; ?>
