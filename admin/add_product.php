<?php
include '../includes/auth.php';
include '../includes/admin_header.php';
require_once '../config/db.php';

$add_product_error = $add_product_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name'] ?? '');
    $brand_id = $_POST['brand_id'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $base_price = $_POST['base_price'] ?? '';
    $status = $_POST['status'] ?? 'inactive';
    $desc = trim($_POST['description'] ?? '');
    $image_path = null;
    // Validate fields
    if ($name === '' || !$brand_id || !$category_id || $base_price === '' || !is_numeric($base_price)) {
        $add_product_error = 'Please fill all required fields correctly.';
    } elseif (!isset($_FILES['featured_image']) || $_FILES['featured_image']['error'] === 4) {
        $add_product_error = 'Please upload a product image.';
    } else {
        // Handle image upload
        $img = $_FILES['featured_image'];
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $add_product_error = 'Invalid image type. Allowed: jpg, jpeg, png, webp.';
        } elseif ($img['size'] > 2 * 1024 * 1024) {
            $add_product_error = 'Image too large (max 2MB).';
        } else {
            $upload_dir = '../assets/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $filename = uniqid('prod_', true) . '.' . $ext;
            $target = $upload_dir . $filename;
            if (move_uploaded_file($img['tmp_name'], $target)) {
                $image_path = 'assets/uploads/' . $filename;
            } else {
                $add_product_error = 'Failed to upload image.';
            }
        }
    }
    // Insert if no error
    if (!$add_product_error && $image_path) {
        $sql = "INSERT INTO products (name, brand_id, category_id, base_price, status, description, featured_image) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$name, $brand_id, $category_id, $base_price, $status, $desc, $image_path])) {
            $add_product_success = 'Product added successfully!';
        } else {
            $add_product_error = 'Database error. Please try again.';
        }
    }
}
$brands = $pdo->query('SELECT id, name FROM brands ORDER BY name')->fetchAll();
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
?>
<main class="p-4 sm:p-8">
  <div class="flex items-center mb-6 gap-3">
    <a href="manage_products.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
      <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      Back
    </a>
    <h1 class="text-2xl font-bold text-gray-800">Add New Product</h1>
  </div>
  <?php if ($add_product_error): ?>
    <div id="notify-msg" class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4"><?= htmlspecialchars($add_product_error) ?></div>
  <?php elseif ($add_product_success): ?>
    <div id="notify-msg" class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4"><?= htmlspecialchars($add_product_success) ?></div>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8" autocomplete="off">
    <div>
      <label class="block text-gray-700 mb-1">Product Name <span class="text-red-500">*</span></label>
      <input type="text" name="name" class="border rounded px-3 py-2 w-full" required>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Brand <span class="text-red-500">*</span></label>
      <select name="brand_id" class="border rounded px-3 py-2 w-full" required>
        <option value="">Select Brand</option>
        <?php foreach ($brands as $brand): ?>
          <option value="<?= $brand['id'] ?>"><?= htmlspecialchars($brand['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
      <select name="category_id" class="border rounded px-3 py-2 w-full" required>
        <option value="">Select Category</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Base Price ($) <span class="text-red-500">*</span></label>
      <input type="number" name="base_price" min="0" step="0.01" class="border rounded px-3 py-2 w-full" required>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Status</label>
      <select name="status" class="border rounded px-3 py-2 w-full">
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Product Image <span class="text-red-500">*</span></label>
      <input type="file" name="featured_image" accept="image/*" class="border rounded px-3 py-2 w-full" required>
    </div>
    <div class="md:col-span-2">
      <label class="block text-gray-700 mb-1">Description</label>
      <textarea name="description" class="border rounded px-3 py-2 w-full" rows="3"></textarea>
    </div>
    <div class="md:col-span-2 flex justify-end">
      <button type="submit" name="add_product" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Add Product</button>
    </div>
  </form>
</main>
<?php include '../includes/admin_footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const msg = document.getElementById('notify-msg');
  if (msg) {
    setTimeout(() => { msg.style.display = 'none'; }, 3000);
  }
});
</script>
