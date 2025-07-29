<?php
require_once '../config/db.php';
header('Content-Type: application/json');

class BrandManager {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function getBrands($search = null) {
        $where = [];
        $params = [];
        if (!empty($search)) {
            $where[] = 'name LIKE ?';
            $params[] = '%' . $search . '%';
        }
        $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT * FROM brands $where_sql ORDER BY id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public function addBrand($name, $logoFile) {
        $error = '';
        $logo_path = null;
        if ($logoFile && $logoFile['error'] !== 4) {
            $upload = $this->handleLogoUpload($logoFile);
            if (isset($upload['error'])) $error = $upload['error'];
            else $logo_path = $upload['path'];
        }
        if ($name === '') {
            $error = 'Brand name required.';
        }
        if ($error) return ['error' => $error];
        $stmt = $this->pdo->prepare('INSERT INTO brands (name, logo) VALUES (?, ?)');
        if ($stmt->execute([$name, $logo_path])) {
            return ['message' => 'Brand added!'];
        }
        return ['error' => 'Error adding brand.'];
    }
    public function editBrand($id, $name, $logoFile, $current_logo) {
        $error = '';
        $logo_path = $current_logo;
        if ($logoFile && $logoFile['error'] !== 4) {
            $upload = $this->handleLogoUpload($logoFile);
            if (isset($upload['error'])) $error = $upload['error'];
            else $logo_path = $upload['path'];
        }
        if ($name === '') {
            $error = 'Brand name required.';
        }
        if ($error) return ['error' => $error];
        $stmt = $this->pdo->prepare('UPDATE brands SET name=?, logo=? WHERE id=?');
        if ($stmt->execute([$name, $logo_path, $id])) {
            return ['message' => 'Brand updated!'];
        }
        return ['error' => 'Error updating brand.'];
    }
    public function deleteBrand($id) {
        $stmt = $this->pdo->prepare('DELETE FROM brands WHERE id=?');
        if ($stmt->execute([$id])) {
            return ['message' => 'Brand deleted!'];
        }
        return ['error' => 'Error deleting brand.'];
    }
    private function handleLogoUpload($img) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            return ['error' => 'Invalid logo type.'];
        } elseif ($img['size'] > 2 * 1024 * 1024) {
            return ['error' => 'Logo too large (max 2MB).'];
        } else {
            $upload_dir = '../assets/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $filename = uniqid('brand_', true) . '.' . $ext;
            $target = $upload_dir . $filename;
            if (move_uploaded_file($img['tmp_name'], $target)) {
                return ['path' => 'assets/uploads/' . $filename];
            } else {
                return ['error' => 'Failed to upload logo.'];
            }
        }
    }
}

$brandApi = new BrandManager($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search = $_GET['search'] ?? null;
    $brands = $brandApi->getBrands($search);
    echo json_encode(['brands' => $brands]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $result = [];
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $logoFile = $_FILES['logo'] ?? null;
        $result = $brandApi->addBrand($name, $logoFile);
    } elseif ($action === 'edit') {
        $id = (int)$_POST['brand_id'];
        $name = trim($_POST['name'] ?? '');
        $logoFile = $_FILES['logo'] ?? null;
        $current_logo = $_POST['current_logo'] ?? null;
        $result = $brandApi->editBrand($id, $name, $logoFile, $current_logo);
    } elseif ($action === 'delete') {
        $id = (int)$_POST['brand_id'];
        $result = $brandApi->deleteBrand($id);
    }
    if (!isset($result['message'])) $result['message'] = '';
    if (!isset($result['error'])) $result['error'] = '';
    echo json_encode($result);
    exit;
}
