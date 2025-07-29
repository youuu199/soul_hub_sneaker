<?php
require_once '../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get all variants for a product
    $product_id = $_GET['product_id'] ?? null;
    if (!$product_id) {
        echo json_encode(['error' => 'Missing product_id']);
        exit;
    }
    $stmt = $pdo->prepare('SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC');
    $stmt->execute([$product_id]);
    $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['variants' => $variants]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = '';
    $error = '';
    // Add variant
    if (isset($_POST['add_variant'])) {
        $product_id = $_POST['product_id'] ?? '';
        $size = trim($_POST['size'] ?? '');
        $color = trim($_POST['color'] ?? '');
        $stock = $_POST['stock'] ?? '';
        $sku = trim($_POST['sku'] ?? '');
        if (!$product_id || $size === '' || $color === '' || $stock === '' || $sku === '') {
            $error = 'All fields are required.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO product_variants (product_id, size, color, stock, sku) VALUES (?, ?, ?, ?, ?)');
            if ($stmt->execute([$product_id, $size, $color, $stock, $sku])) {
                $msg = 'Variant added.';
            } else {
                $error = 'Failed to add variant.';
            }
        }
        echo json_encode(['message' => $msg, 'error' => $error]);
        exit;
    }
    // Edit variant
    if (isset($_POST['edit_variant'])) {
        $id = $_POST['id'] ?? '';
        $size = trim($_POST['size'] ?? '');
        $color = trim($_POST['color'] ?? '');
        $stock = $_POST['stock'] ?? '';
        $sku = trim($_POST['sku'] ?? '');
        if (!$id || $size === '' || $color === '' || $stock === '' || $sku === '') {
            $error = 'All fields are required.';
        } else {
            $stmt = $pdo->prepare('UPDATE product_variants SET size=?, color=?, stock=?, sku=? WHERE id=?');
            if ($stmt->execute([$size, $color, $stock, $sku, $id])) {
                $msg = 'Variant updated.';
            } else {
                $error = 'Failed to update variant.';
            }
        }
        echo json_encode(['message' => $msg, 'error' => $error]);
        exit;
    }
    // Delete variant
    if (isset($_POST['delete_variant'])) {
        $id = $_POST['id'] ?? '';
        if (!$id) {
            $error = 'Invalid variant ID.';
        } else {
            $stmt = $pdo->prepare('DELETE FROM product_variants WHERE id = ?');
            if ($stmt->execute([$id])) {
                $msg = 'Variant deleted.';
            } else {
                $error = 'Failed to delete variant.';
            }
        }
        echo json_encode(['message' => $msg, 'error' => $error]);
        exit;
    }
}
