<?php
// api/filter_products.php
require_once '../config/db.php';
header('Content-Type: application/json');

if (isset($_GET['meta'])) {
    // Return brands and categories for filter dropdowns
    $brands = $pdo->query("SELECT DISTINCT name FROM brands ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    $categories = $pdo->query("SELECT DISTINCT name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['brands' => $brands, 'categories' => $categories]);
    exit;
}

$brand = $_GET['brand'] ?? null;
$category = $_GET['category'] ?? null;
$search = $_GET['search'] ?? null;
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

// --- CATEGORY FILTER: include children if parent selected ---
$categoryIds = [];
if ($category) {
    // Get category id by name
    $catStmt = $pdo->prepare('SELECT id FROM categories WHERE name = ?');
    $catStmt->execute([$category]);
    $catId = $catStmt->fetchColumn();
    if ($catId) {
        // Check for children
        $childStmt = $pdo->prepare('SELECT id FROM categories WHERE parent_id = ?');
        $childStmt->execute([$catId]);
        $children = $childStmt->fetchAll(PDO::FETCH_COLUMN);
        if ($children) {
            $categoryIds = array_merge([$catId], $children);
        } else {
            $categoryIds = [$catId];
        }
    }
}

// Count total
$sqlCount = "SELECT COUNT(*) FROM products p
        LEFT JOIN brands b ON p.brand_id = b.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active'";
$params = [];
if ($brand) {
    $sqlCount .= " AND b.name = ?";
    $params[] = $brand;
}
if ($category && $categoryIds) {
    $in = implode(',', array_fill(0, count($categoryIds), '?'));
    $sqlCount .= " AND c.id IN ($in)";
    $params = array_merge($params, $categoryIds);
}
if ($search) {
    $sqlCount .= " AND p.name LIKE ?";
    $params[] = "%$search%";
}
$stmt = $pdo->prepare($sqlCount);
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();

// Fetch products
$sql = "SELECT p.id, p.name, p.base_price as price, p.featured_image as img, b.name as brand, c.name as category
        FROM products p
        LEFT JOIN brands b ON p.brand_id = b.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active'";
$params2 = $params;
if ($brand) {
    $sql .= " AND b.name = ?";
}
if ($category && $categoryIds) {
    $in = implode(',', array_fill(0, count($categoryIds), '?'));
    $sql .= " AND c.id IN ($in)";
}
if ($search) {
    $sql .= " AND p.name LIKE ?";
}
$sql .= " ORDER BY p.id DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params2);
$products = $stmt->fetchAll();
foreach ($products as &$p) {
    if (empty($p['img'])) {
        $p['img'] = '/SoleHub/assets/img/no-image.png';
    }
}
echo json_encode([
    'products' => $products,
    'total' => $total,
    'perPage' => $perPage,
    'page' => $page,
    'totalPages' => ceil($total / $perPage)
]);
