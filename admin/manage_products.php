<?php include '../includes/auth.php'; ?>
<?php include '../includes/admin_header.php';
require_once '../config/db.php';

// --- PAGINATION ---
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

// --- FILTERS ---
$where = [];
$params = [];
if (!empty($_GET['search'])) {
    $where[] = 'p.name LIKE ?';
    $params[] = '%' . $_GET['search'] . '%';
}
if (!empty($_GET['brand'])) {
    $where[] = 'p.brand_id = ?';
    $params[] = $_GET['brand'];
}
if (!empty($_GET['category'])) {
    $where[] = 'p.category_id = ?';
    $params[] = $_GET['category'];
}
if (!empty($_GET['status'])) {
    $where[] = 'p.status = ?';
    $params[] = $_GET['status'];
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// --- TOTAL COUNT FOR PAGINATION ---
$count_sql = "SELECT COUNT(*) FROM products p $where_sql";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_products = (int)$count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_products / $per_page));

// --- FETCH PRODUCTS ---
$sql = "SELECT p.*, b.name as brand_name, c.name as category_name FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.id 
        LEFT JOIN categories c ON p.category_id = c.id 
        $where_sql ORDER BY p.id DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// --- FETCH BRANDS & CATEGORIES FOR FILTERS ---
$brands = $pdo->query('SELECT id, name FROM brands ORDER BY name')->fetchAll();
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

// --- HANDLE ADD PRODUCT ---
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
            // Clear POST to prevent resubmission
            header('Location: manage_products.php?added=1');
            exit;
        } else {
            $add_product_error = 'Database error. Please try again.';
        }
    }
}
if (isset($_GET['added'])) {
    $add_product_success = 'Product added successfully!';
}

// --- LOW STOCK FILTER ---
$showing_low_stock = false;
if (isset($_GET['low_stock']) && $_GET['low_stock'] == '1') {
    $where[] = 'p.id IN (SELECT product_id FROM product_variants WHERE stock <= 5)';
    $showing_low_stock = true;
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// --- TOTAL COUNT FOR PAGINATION (LOW STOCK) ---
$count_sql = "SELECT COUNT(*) FROM products p $where_sql";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_products = (int)$count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_products / $per_page));

// --- FETCH PRODUCTS (LOW STOCK) ---
$sql = "SELECT p.*, b.name as brand_name, c.name as category_name FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.id 
        LEFT JOIN categories c ON p.category_id = c.id 
        $where_sql ORDER BY p.id DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// --- FETCH BRANDS & CATEGORIES FOR FILTERS ---
$brands = $pdo->query('SELECT id, name FROM brands ORDER BY name')->fetchAll();
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

// --- HANDLE ADD PRODUCT ---
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
            // Clear POST to prevent resubmission
            header('Location: manage_products.php?added=1');
            exit;
        } else {
            $add_product_error = 'Database error. Please try again.';
        }
    }
}
if (isset($_GET['added'])) {
    $add_product_success = 'Product added successfully!';
}
?>
<main class="p-4 sm:p-8">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Product Management</h1>
    <a href="add_product.php" class="inline-block bg-blue-600 text-white px-6 py-2 rounded shadow hover:bg-blue-700 transition">+ Add Product</a>
  </div>
  <?php if ($showing_low_stock): ?>
    <div class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded mb-4 flex items-center gap-2">
      <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/></svg>
      Showing only low stock products (≤ 5 in any variant)
      <a href="manage_products.php" class="ml-auto text-blue-600 underline text-sm">Show all</a>
    </div>
  <?php endif; ?>
  <?php if (isset($_GET['success'])): ?>
    <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">Product added successfully!</div>
  <?php endif; ?>
  <div class="bg-white rounded shadow p-2 sm:p-6 mb-8 overflow-x-auto">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4">
      <form class="flex flex-col md:flex-row gap-2 w-full md:w-auto" method="get" action="">
        <input type="text" name="search" placeholder="Search products..." class="border rounded px-3 py-2 focus:outline-none focus:ring w-full md:w-48" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <select name="brand" class="border rounded px-3 py-2 focus:outline-none w-full md:w-auto">
          <option value="">All Brands</option>
          <?php foreach ($brands as $brand): ?>
            <option value="<?= $brand['id'] ?>" <?= (($_GET['brand'] ?? '') == $brand['id']) ? 'selected' : '' ?>><?= htmlspecialchars($brand['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="category" class="border rounded px-3 py-2 focus:outline-none w-full md:w-auto">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= (($_GET['category'] ?? '') == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="status" class="border rounded px-3 py-2 focus:outline-none w-full md:w-auto">
          <option value="">All Statuses</option>
          <option value="active" <?= (($_GET['status'] ?? '') == 'active') ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= (($_GET['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>Inactive</option>
        </select>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full md:w-auto">Filter</button>
      </form>
    </div>
    <div class="overflow-x-auto rounded-xl shadow-lg bg-white">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gradient-to-r from-blue-100 to-indigo-100">
          <tr>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Image</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Name</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Brand</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Category</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Base Price</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody id="products-table-body" class="divide-y divide-gray-100">
          <!-- Populated by JS -->
        </tbody>
      </table>
    </div>
    <div id="product-debug-msg" class="hidden bg-yellow-100 text-yellow-800 px-4 py-2 rounded mb-4 mt-2"></div>
    <div class="flex flex-col sm:flex-row justify-end mt-4">
      <nav id="products-pagination" class="inline-flex rounded-md shadow-sm"></nav>
    </div>
    <div id="product-action-msg" class="hidden bg-green-100 text-green-700 px-4 py-2 rounded mb-4"></div>
  </div>
  <!-- Product Variants Modal -->
  <div id="variant-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative">
      <button onclick="document.getElementById('variant-modal').classList.add('hidden')" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700">&times;</button>
      <h2 class="text-xl font-semibold mb-4">Manage Variants (Size, Color, Stock)</h2>
      <table class="min-w-full text-sm mb-4">
        <thead>
          <tr class="bg-gray-50 text-gray-600">
            <th class="py-2 px-3 text-left">Size</th>
            <th class="py-2 px-3 text-left">Color</th>
            <th class="py-2 px-3 text-left">Stock</th>
            <th class="py-2 px-3 text-left">SKU</th>
            <th class="py-2 px-3 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- Variants will be loaded dynamically via JS -->
        </tbody>
      </table>
      <form class="flex flex-col md:flex-row gap-2 items-end" id="add-variant-form">
        <input type="text" name="size" placeholder="Size" class="border rounded px-3 py-2 focus:outline-none w-24">
        <input type="text" name="color" placeholder="Color" class="border rounded px-3 py-2 focus:outline-none w-32">
        <input type="number" name="stock" placeholder="Stock" class="border rounded px-3 py-2 focus:outline-none w-24">
        <input type="text" name="sku" placeholder="SKU" class="border rounded px-3 py-2 focus:outline-none w-32">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Variant</button>
      </form>
    </div>
  </div>
  <!-- Add Product Modal -->
  <div id="add-product-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
      <button onclick="document.getElementById('add-product-modal').classList.add('hidden')" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
      <h2 class="text-xl font-semibold mb-4">Add Product</h2>
      <form method="post" enctype="multipart/form-data" class="flex flex-col gap-4">
        <input type="hidden" name="add_product" value="1">
        <div>
          <label class="block text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
          <input type="text" name="name" required class="border rounded px-3 py-2 w-full focus:outline-none focus:ring">
        </div>
        <div class="flex gap-2">
          <div class="flex-1">
            <label class="block text-gray-700 mb-1">Brand <span class="text-red-500">*</span></label>
            <select name="brand_id" required class="border rounded px-3 py-2 w-full focus:outline-none">
              <option value="">Select Brand</option>
              <?php foreach ($brands as $brand): ?>
                <option value="<?= $brand['id'] ?>"><?= htmlspecialchars($brand['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="flex-1">
            <label class="block text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
            <select name="category_id" required class="border rounded px-3 py-2 w-full focus:outline-none">
              <option value="">Select Category</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="flex gap-2">
          <div class="flex-1">
            <label class="block text-gray-700 mb-1">Base Price ($) <span class="text-red-500">*</span></label>
            <input type="number" name="base_price" min="0" step="0.01" required class="border rounded px-3 py-2 w-full focus:outline-none">
          </div>
          <div class="flex-1">
            <label class="block text-gray-700 mb-1">Status</label>
            <select name="status" class="border rounded px-3 py-2 w-full focus:outline-none">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Featured Image</label>
          <input type="file" name="featured_image" accept="image/*" class="border rounded px-3 py-2 w-full focus:outline-none">
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Description</label>
          <textarea name="description" rows="3" class="border rounded px-3 py-2 w-full focus:outline-none"></textarea>
        </div>
        <div class="flex justify-end gap-2">
          <button type="button" onclick="document.getElementById('add-product-modal').classList.add('hidden')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Product</button>
        </div>
      </form>
    </div>
  </div>
  <!-- Edit Product Modal -->
  <div id="edit-product-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
      <button onclick="document.getElementById('edit-product-modal').classList.add('hidden')" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
      <h2 class="text-xl font-semibold mb-4">Edit Product</h2>
      <form id="edit-product-form" enctype="multipart/form-data" class="flex flex-col gap-4">
        <input type="hidden" name="edit_product" value="1">
        <input type="hidden" name="id" id="edit-product-id">
        <div>
          <label class="block text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
          <input type="text" name="name" id="edit-product-name" required class="border rounded px-3 py-2 w-full focus:outline-none focus:ring">
        </div>
        <div class="flex gap-2">
          <div class="flex-1">
            <label class="block text-gray-700 mb-1">Brand <span class="text-red-500">*</span></label>
            <select name="brand_id" id="edit-product-brand" required class="border rounded px-3 py-2 w-full focus:outline-none">
              <option value="">Select Brand</option>
              <?php foreach ($brands as $brand): ?>
                <option value="<?= $brand['id'] ?>"><?= htmlspecialchars($brand['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="flex-1">
            <label class="block text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
            <select name="category_id" id="edit-product-category" required class="border rounded px-3 py-2 w-full focus:outline-none">
              <option value="">Select Category</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="flex gap-2">
          <div class="flex-1">
            <label class="block text-gray-700 mb-1">Base Price ($) <span class="text-red-500">*</span></label>
            <input type="number" name="base_price" id="edit-product-price" min="0" step="0.01" required class="border rounded px-3 py-2 w-full focus:outline-none">
          </div>
          <div class="flex-1">
            <label class="block text-gray-700 mb-1">Status</label>
            <select name="status" id="edit-product-status" class="border rounded px-3 py-2 w-full focus:outline-none">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Featured Image</label>
          <input type="file" name="featured_image" id="edit-product-image" accept="image/*" class="border rounded px-3 py-2 w-full focus:outline-none">
          <div id="edit-product-image-preview" class="mt-2"></div>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Description</label>
          <textarea name="description" id="edit-product-desc" rows="3" class="border rounded px-3 py-2 w-full focus:outline-none"></textarea>
        </div>
        <div class="flex justify-end gap-2">
          <button type="button" onclick="document.getElementById('edit-product-modal').classList.add('hidden')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</main>
<?php include '../includes/admin_footer.php'; ?>
<script>
function renderProducts(products) {
  const tbody = document.getElementById('products-table-body');
  tbody.innerHTML = '';
  const debugBox = document.getElementById('product-debug-msg');
  debugBox.classList.add('hidden');
  if (!products.length) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-gray-400 py-4">No products found.</td></tr>`;
    debugBox.textContent = 'No products found or API returned empty data.';
    debugBox.classList.remove('hidden');
    return;
  }
  products.forEach(product => {
    let imgHtml = product.featured_image ? `<img src='/SoleHub/${escapeHtml(product.featured_image)}' alt='${escapeHtml(product.name)}' class='w-20 h-20 object-fit  rounded shadow'>` : '<span class="text-gray-400">No image</span>';
    let statusHtml = product.status === 'active' ? '<span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Active</span>' : '<span class="bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs">Inactive</span>';
    let rowColor = '';
    if (typeof product.lowest_stock === 'number' && product.lowest_stock !== null) {
      if (product.lowest_stock === 0) rowColor = 'bg-red-100';
      else if (product.lowest_stock === 1 || product.lowest_stock === 2) rowColor = 'bg-orange-100';
      else if (product.lowest_stock === 3 || product.lowest_stock === 4 || product.lowest_stock === 5) rowColor = 'bg-yellow-100';
    }
    tbody.innerHTML += `
      <tr class="border-b ${rowColor} hover:bg-blue-50 transition">
        <td class="py-3 px-4">${imgHtml}</td>
        <td class="py-3 px-4 font-medium text-gray-900">${escapeHtml(product.name)}</td>
        <td class="py-3 px-4">${escapeHtml(product.brand_name || '-') }</td>
        <td class="py-3 px-4">${escapeHtml(product.category_name || '-') }</td>
        <td class="py-3 px-4">$${Number(product.base_price).toFixed(2)}</td>
        <td class="py-3 px-4">
          <select class="status-select border rounded px-2 py-1 text-xs focus:outline-none bg-white" data-id="${product.id}">
            <option value="active" ${product.status === 'active' ? 'selected' : ''}>Active</option>
            <option value="inactive" ${product.status === 'inactive' ? 'selected' : ''}>Inactive</option>
          </select>
        </td>
        <td class="py-3 px-4 flex gap-2">
          <a href="#" class="text-blue-600 hover:underline product-edit" data-id="${product.id}">Edit</a>
          <a href="#" class="text-red-500 hover:underline product-delete" data-id="${product.id}">Delete</a>
          <a href="#" class="text-indigo-600 hover:underline product-variants" data-id="${product.id}">Variants</a>
        </td>
      </tr>
    `;
  });
  // Attach event listeners after rendering
  document.querySelectorAll('.product-delete').forEach(btn => {
    btn.onclick = function(e) {
      e.preventDefault();
      const id = this.getAttribute('data-id');
      if (!confirm('Are you sure you want to delete this product?')) return;
      fetch('/SoleHub/api/manage_products.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `delete_product=1&id=${encodeURIComponent(id)}`
      })
      .then(res => res.json())
      .then(data => {
        showProductMsg(data.message, data.error);
        if (!data.error) fetchProducts();
      });
    };
  });
  document.querySelectorAll('.product-edit').forEach(btn => {
    btn.onclick = function(e) {
      e.preventDefault();
      const id = this.getAttribute('data-id');
      // Fetch product data via AJAX
      fetch(`/SoleHub/api/manage_products.php?product_id=${id}`)
        .then(res => res.json())
        .then(product => {
          document.getElementById('edit-product-id').value = product.id;
          document.getElementById('edit-product-name').value = product.name;
          document.getElementById('edit-product-brand').value = product.brand_id;
          document.getElementById('edit-product-category').value = product.category_id;
          document.getElementById('edit-product-price').value = product.base_price;
          document.getElementById('edit-product-status').value = product.status;
          document.getElementById('edit-product-desc').value = product.description || '';
          // Image preview
          const preview = document.getElementById('edit-product-image-preview');
          if (product.featured_image) {
            preview.innerHTML = `<img src='/${escapeHtml(product.featured_image)}' alt='Current Image' class='w-20 h-20 object-cover rounded'>`;
          } else {
            preview.innerHTML = '<span class="text-gray-400">No image</span>';
          }
          document.getElementById('edit-product-modal').classList.remove('hidden');
        });
    };
  });
  document.querySelectorAll('.product-variants').forEach(btn => {
    btn.onclick = function(e) {
      e.preventDefault();
      const id = this.getAttribute('data-id');
      document.getElementById('variant-modal').classList.remove('hidden');
      loadVariants(id);
    };
  });
  document.querySelectorAll('.status-select').forEach(sel => {
    sel.onchange = function() {
      const id = this.getAttribute('data-id');
      const status = this.value;
      // Fetch current product data first
      fetch(`/SoleHub/api/manage_products.php?product_id=${id}`)
        .then(res => res.json())
        .then(product => {
          // Send all required fields with updated status
          const body = `edit_product=1&id=${encodeURIComponent(id)}&name=${encodeURIComponent(product.name)}&brand_id=${encodeURIComponent(product.brand_id)}&category_id=${encodeURIComponent(product.category_id)}&base_price=${encodeURIComponent(product.base_price)}&status=${encodeURIComponent(status)}&description=${encodeURIComponent(product.description || '')}`;
          fetch('/SoleHub/api/manage_products.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body
          })
          .then(res => res.json())
          .then(data => {
            showProductMsg(data.message, data.error);
            if (!data.error) {
              sel.classList.remove('bg-red-100', 'bg-green-100');
              sel.classList.add(status === 'active' ? 'bg-green-100' : 'bg-red-100');
            }
          });
        });
    };
  });
}
function escapeHtml(text) {
  return String(text).replace(/[&<>'"]/g, function(c) {
    return {'&':'&amp;','<':'&lt;','>':'&gt;','\'':'&#39;','"':'&quot;'}[c];
  });
}
function fetchProducts(page=1, lowStock=false) {
  const search = document.querySelector('input[name=search]').value;
  const brand = document.querySelector('select[name=brand]').value;
  const category = document.querySelector('select[name=category]').value;
  const status = document.querySelector('select[name=status]').value;
  let url = `/SoleHub/api/manage_products.php?search=${encodeURIComponent(search)}&brand=${encodeURIComponent(brand)}&category=${encodeURIComponent(category)}&status=${encodeURIComponent(status)}&page=${page}`;
  if (lowStock || (new URLSearchParams(window.location.search).get('low_stock') === '1')) {
    url += '&low_stock=1';
  }
  fetch(url)
    .then(res => res.json())
    .then(function(data) {
      // Debug output
      if (window.console) {
        console.log('API Response:', data);
      }
      renderProducts(Array.isArray(data.products) ? data.products : []);
      renderProductPagination(data.page || 1, data.total_pages || 1);
    })
    .catch(function(err) {
      if (window.console) {
        console.error('API Fetch Error:', err);
      }
      renderProducts([]);
      renderProductPagination(1, 1);
    });
}
function renderProductPagination(page, total) {
  const nav = document.getElementById('products-pagination');
  let html = '';
  for (let i = 1; i <= total; i++) {
    const active = i === page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50';
    html += `<a href="#" onclick="fetchProducts(${i});return false;" class="px-3 py-1 border border-gray-300 ${active}">${i}</a>`;
  }
  nav.innerHTML = html;
}
function showProductMsg(msg, error) {
  const box = document.getElementById('product-action-msg');
  box.textContent = error ? error : msg;
  box.classList.remove('hidden', 'bg-green-100', 'text-green-700', 'bg-red-100', 'text-red-700');
  if (error) {
    box.classList.add('bg-red-100', 'text-red-700');
  } else {
    box.classList.add('bg-green-100', 'text-green-700');
  }
  setTimeout(()=>box.classList.add('hidden'), 2500);
}
document.querySelector('form[method=get]').onsubmit = function(e) {
  e.preventDefault();
  fetchProducts();
};
document.addEventListener('DOMContentLoaded', function() {
  // If low_stock=1 in URL, pass it to fetchProducts
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('low_stock') === '1') {
    fetchProducts(1, true);
  } else {
    fetchProducts();
  }

  // Attach add product form handler (ensure only one listener)
  const addProductForm = document.querySelector('#add-product-modal form');
  addProductForm.onsubmit = function(e) {
    e.preventDefault();
    const formData = new FormData(addProductForm);
    formData.append('add_product', '1');
    fetch('/SoleHub/api/manage_products.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      showProductMsg(data.message, data.error);
      if (!data.error) {
        document.getElementById('add-product-modal').classList.add('hidden');
        addProductForm.reset();
        fetchProducts();
      }
    });
  };

  // Attach add variant form handler (ensure only one listener)
  const variantForm = document.querySelector('#variant-modal form');
  variantForm.onsubmit = function(e) {
    e.preventDefault();
    const formData = new FormData(variantForm);
    formData.append('add_variant', '1');
    formData.append('product_id', currentVariantProductId);
    fetch('/SoleHub/api/get_variants.php', {
      method: 'POST',
      body: new URLSearchParams([...formData])
    })
    .then(res => res.json())
    .then(data => {
      showProductMsg(data.message, data.error);
      if (!data.error) {
        variantForm.reset();
        loadVariants(currentVariantProductId);
      }
    });
  };
});
// AJAX edit product
const editProductForm = document.getElementById('edit-product-form');
editProductForm.onsubmit = function(e) {
  e.preventDefault();
  const formData = new FormData(editProductForm);
  formData.append('edit_product', '1');
  fetch('/SoleHub/api/manage_products.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    showProductMsg(data.message, data.error);
    if (!data.error) {
      document.getElementById('edit-product-modal').classList.add('hidden');
      editProductForm.reset();
      fetchProducts();
    }
  });
};
// --- VARIANTS MODAL LOGIC ---
let currentVariantProductId = null;
function loadVariants(productId) {
  currentVariantProductId = productId;
  fetch(`/SoleHub/api/get_variants.php?product_id=${productId}`)
    .then(res => res.json())
    .then(data => {
      const tbody = document.querySelector('#variant-modal table tbody');
      tbody.innerHTML = '';
      if (!data.variants || !data.variants.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-gray-400 py-4">No variants found.</td></tr>';
        return;
      }
      data.variants.forEach(variant => {
        let stockColor = '';
        if (typeof variant.stock === 'number' || !isNaN(variant.stock)) {
          const stock = Number(variant.stock);
          if (stock === 0) stockColor = 'bg-red-100';
          else if (stock === 1 || stock === 2) stockColor = 'bg-orange-100';
          else if (stock >= 3 && stock <= 5) stockColor = 'bg-yellow-100';
        }
        tbody.innerHTML += `
          <tr data-variant-id="${variant.id}" class="${stockColor}">
            <td class="py-2 px-3">${escapeHtml(variant.size)}</td>
            <td class="py-2 px-3">${escapeHtml(variant.color)}</td>
            <td class="py-2 px-3">${variant.stock}</td>
            <td class="py-2 px-3">${escapeHtml(variant.sku)}</td>
            <td class="py-2 px-3 flex gap-2">
              <a href="#" class="text-blue-600 hover:underline variant-edit" data-id="${variant.id}">Edit</a>
              <a href="#" class="text-red-500 hover:underline variant-delete" data-id="${variant.id}">Delete</a>
            </td>
          </tr>
        `;
      });
      attachVariantActions();
    });
}
function attachVariantActions() {
  document.querySelectorAll('.variant-delete').forEach(btn => {
    btn.onclick = function(e) {
      e.preventDefault();
      const id = this.getAttribute('data-id');
      if (!confirm('Delete this variant?')) return;
      fetch('/SoleHub/api/get_variants.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `delete_variant=1&id=${encodeURIComponent(id)}`
      })
      .then(res => res.json())
      .then(data => {
        showProductMsg(data.message, data.error);
        if (!data.error) loadVariants(currentVariantProductId);
      });
    };
  });
  document.querySelectorAll('.variant-edit').forEach(btn => {
    btn.onclick = function(e) {
      e.preventDefault();
      const tr = this.closest('tr');
      const id = this.getAttribute('data-id');
      // Inline edit: replace row with editable fields
      const size = tr.children[0].textContent;
      const color = tr.children[1].textContent;
      const stock = tr.children[2].textContent;
      const sku = tr.children[3].textContent;
      tr.innerHTML = `
        <td class="py-2 px-3"><input type="text" value="${escapeHtml(size)}" class="border rounded px-2 py-1 w-16"></td>
        <td class="py-2 px-3"><input type="text" value="${escapeHtml(color)}" class="border rounded px-2 py-1 w-20"></td>
        <td class="py-2 px-3"><input type="number" value="${stock}" class="border rounded px-2 py-1 w-16"></td>
        <td class="py-2 px-3"><input type="text" value="${escapeHtml(sku)}" class="border rounded px-2 py-1 w-24"></td>
        <td class="py-2 px-3 flex gap-2">
          <a href="#" class="text-green-600 hover:underline variant-save" data-id="${id}">Save</a>
          <a href="#" class="text-gray-500 hover:underline variant-cancel">Cancel</a>
        </td>
      `;
      tr.querySelector('.variant-save').onclick = function(ev) {
        ev.preventDefault();
        const inputs = tr.querySelectorAll('input');
        const [size, color, stock, sku] = Array.from(inputs).map(i => i.value);
        fetch('/SoleHub/api/get_variants.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `edit_variant=1&id=${id}&size=${encodeURIComponent(size)}&color=${encodeURIComponent(color)}&stock=${encodeURIComponent(stock)}&sku=${encodeURIComponent(sku)}`
        })
        .then(res => res.json())
        .then(data => {
          showProductMsg(data.message, data.error);
          if (!data.error) loadVariants(currentVariantProductId);
        });
      };
      tr.querySelector('.variant-cancel').onclick = function(ev) {
        ev.preventDefault();
        loadVariants(currentVariantProductId);
      };
    };
  });
}
// Add variant
const variantForm = document.getElementById('add-variant-form');
variantForm.onsubmit = function(e) {
  e.preventDefault();
  const formData = new FormData(variantForm);
  formData.append('add_variant', '1');
  formData.append('product_id', currentVariantProductId);
  fetch('/SoleHub/api/get_variants.php', {
    method: 'POST',
    body: new URLSearchParams([...formData])
  })
  .then(res => res.json())
  .then(data => {
    showProductMsg(data.message, data.error);
    if (!data.error) {
      variantForm.reset();
      loadVariants(currentVariantProductId);
    }
  });
};
document.addEventListener('DOMContentLoaded', function() {
  const addForm = document.querySelector('form[method="post"][enctype="multipart/form-data"]');
  if (addForm) {
    addForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(addForm);
      formData.append('add_product', '1');
      fetch('manage_products.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.text())
      .then(html => {
        // Parse returned HTML for success/error message and product table
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const msg = doc.querySelector('.bg-green-100, .bg-red-100');
        const table = doc.querySelector('table');
        if (msg) {
          const msgBox = document.querySelector('.bg-green-100, .bg-red-100');
          if (msgBox) msgBox.outerHTML = msg.outerHTML;
          else addForm.insertAdjacentElement('beforebegin', msg);
        }
        if (table) {
          const oldTable = document.querySelector('table');
          if (oldTable) oldTable.outerHTML = table.outerHTML;
        }
        if (msg && msg.classList.contains('bg-green-100')) {
          addForm.reset();
          // Move the new product row to the top of the table
          const newDocRows = doc.querySelectorAll('table tbody tr');
          const curTable = document.querySelector('table');
          if (curTable && newDocRows.length) {
            const curTbody = curTable.querySelector('tbody');
            curTbody.innerHTML = '';
            newDocRows.forEach(row => curTbody.appendChild(row.cloneNode(true)));
          }
        }
      });
    });
  }
});
</script>
