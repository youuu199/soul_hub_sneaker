<?php
require_once '../config/db.php';
header('Content-Type: application/json');

class ProductManager {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function getProduct($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getProducts($filters) {
        $page = max(1, (int)($filters['page'] ?? 1));
        $per_page = 15;
        $offset = ($page - 1) * $per_page;
        $where = [];
        $params = [];
        if (!empty($filters['search'])) {
            $where[] = 'p.name LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['brand'])) {
            $where[] = 'p.brand_id = ?';
            $params[] = $filters['brand'];
        }
        // --- CATEGORY FILTER: include children if parent selected ---
        if (!empty($filters['category'])) {
            // Check if this category is a parent (has children)
            $catId = $filters['category'];
            $childStmt = $this->pdo->prepare('SELECT id FROM categories WHERE parent_id = ?');
            $childStmt->execute([$catId]);
            $children = $childStmt->fetchAll(PDO::FETCH_COLUMN);
            if ($children) {
                // Show products in this parent and all its children
                $in = implode(',', array_fill(0, count($children) + 1, '?'));
                $where[] = 'p.category_id IN (' . $in . ')';
                $params = array_merge($params, array_merge([$catId], $children));
            } else {
                // No children, filter as usual
                $where[] = 'p.category_id = ?';
                $params[] = $catId;
            }
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = 'p.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['low_stock']) && $filters['low_stock'] == '1') {
            $where[] = 'p.id IN (SELECT product_id FROM product_variants WHERE stock < 6)';
        }
        $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $count_sql = "SELECT COUNT(*) FROM products p $where_sql";
        $count_stmt = $this->pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_products = (int)$count_stmt->fetchColumn();
        $total_pages = max(1, ceil($total_products / $per_page));
        $sql = "SELECT p.*, b.name as brand_name, c.name as category_name, (SELECT MIN(stock) FROM product_variants v WHERE v.product_id = p.id) as lowest_stock FROM products p LEFT JOIN brands b ON p.brand_id = b.id LEFT JOIN categories c ON p.category_id = c.id $where_sql ORDER BY p.id DESC LIMIT $per_page OFFSET $offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($products as &$prod) {
            if (isset($prod['lowest_stock'])) {
                $prod['lowest_stock'] = is_null($prod['lowest_stock']) ? null : (int)$prod['lowest_stock'];
            }
        }
        return [
            'products' => $products,
            'total_pages' => $total_pages,
            'page' => $page
        ];
    }
    private function validateProductData($data, $requireImage = false) {
        $name = trim($data['name'] ?? '');
        $brand_id = $data['brand_id'] ?? '';
        $category_id = $data['category_id'] ?? '';
        $base_price = $data['base_price'] ?? '';
        if ($name === '' || !$brand_id || !$category_id || $base_price === '' || !is_numeric($base_price)) {
            return ['error' => 'Please fill all required fields correctly.'];
        }
        return null;
    }
    private function handleImageUpload($file) {
        if (!$file || $file['error'] === 4) {
            return ['error' => 'Please upload a product image.'];
        }
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            return ['error' => 'Invalid image type. Allowed: jpg, jpeg, png, webp.'];
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            return ['error' => 'Image too large (max 2MB).'];
        }
        $upload_dir = '../assets/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = uniqid('prod_', true) . '.' . $ext;
        $target = $upload_dir . $filename;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            return ['path' => 'assets/uploads/' . $filename];
        } else {
            return ['error' => 'Failed to upload image.'];
        }
    }
    public function addProduct($data, $file) {
        $validation = $this->validateProductData($data, true);
        if ($validation) return $validation;
        $imgResult = $this->handleImageUpload($file);
        if (isset($imgResult['error'])) return $imgResult;
        $image_path = $imgResult['path'];
        $sql = "INSERT INTO products (name, brand_id, category_id, base_price, status, description, featured_image) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $name = trim($data['name'] ?? '');
        $brand_id = $data['brand_id'] ?? '';
        $category_id = $data['category_id'] ?? '';
        $base_price = $data['base_price'] ?? '';
        $status = $data['status'] ?? 'inactive';
        $desc = trim($data['description'] ?? '');
        if ($stmt->execute([$name, $brand_id, $category_id, $base_price, $status, $desc, $image_path])) {
            return ['message' => 'Product added successfully!'];
        }
        return ['error' => 'Database error. Please try again.'];
    }
    public function editProduct($data, $file) {
        $id = $data['id'] ?? '';
        $validation = $this->validateProductData($data);
        if ($validation) return $validation;
        $image_path = null;
        $old_image = null;
        if ($file && $file['error'] !== 4) {
            // Get old image path
            $stmt = $this->pdo->prepare('SELECT featured_image FROM products WHERE id = ?');
            $stmt->execute([$id]);
            $old_image = $stmt->fetchColumn();
            $imgResult = $this->handleImageUpload($file);
            if (isset($imgResult['error'])) return $imgResult;
            $image_path = $imgResult['path'];
        }
        $name = trim($data['name'] ?? '');
        $brand_id = $data['brand_id'] ?? '';
        $category_id = $data['category_id'] ?? '';
        $base_price = $data['base_price'] ?? '';
        $status = $data['status'] ?? 'inactive';
        $desc = trim($data['description'] ?? '');
        if ($image_path) {
            $sql = "UPDATE products SET name=?, brand_id=?, category_id=?, base_price=?, status=?, description=?, featured_image=? WHERE id=?";
            $params = [$name, $brand_id, $category_id, $base_price, $status, $desc, $image_path, $id];
        } else {
            $sql = "UPDATE products SET name=?, brand_id=?, category_id=?, base_price=?, status=?, description=? WHERE id=?";
            $params = [$name, $brand_id, $category_id, $base_price, $status, $desc, $id];
        }
        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($params)) {
            // Remove old image if new one uploaded and old exists
            if ($image_path && $old_image && file_exists(__DIR__ . '/../' . $old_image)) {
                @unlink(__DIR__ . '/../' . $old_image);
            }
            return ['message' => 'Product updated successfully!'];
        }
        return ['error' => 'Database error. Please try again.'];
    }
    public function deleteProduct($id) {
        $this->pdo->prepare('DELETE FROM product_variants WHERE product_id = ?')->execute([$id]);
        $stmt = $this->pdo->prepare('DELETE FROM products WHERE id = ?');
        if ($stmt->execute([$id])) {
            return ['message' => 'Product deleted.'];
        }
        return ['error' => 'Failed to delete product.'];
    }
}

$productApi = new ProductManager($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['product_id'])) {
        $id = (int)$_GET['product_id'];
        $product = $productApi->getProduct($id);
        echo json_encode($product);
        exit;
    }
    $filters = [
        'search' => $_GET['search'] ?? null,
        'brand' => $_GET['brand'] ?? null,
        'category' => $_GET['category'] ?? null,
        'status' => $_GET['status'] ?? null,
        'page' => $_GET['page'] ?? 1,
        'low_stock' => $_GET['low_stock'] ?? null
    ];
    $result = $productApi->getProducts($filters);
    echo json_encode($result);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $result = $productApi->addProduct($_POST, $_FILES['featured_image'] ?? null);
        echo json_encode($result);
        exit;
    }
    if (isset($_POST['edit_product'])) {
        $result = $productApi->editProduct($_POST, $_FILES['featured_image'] ?? null);
        echo json_encode($result);
        exit;
    }
    if (isset($_POST['delete_product'])) {
        $id = $_POST['id'] ?? '';
        $result = $productApi->deleteProduct($id);
        echo json_encode($result);
        exit;
    }
}
