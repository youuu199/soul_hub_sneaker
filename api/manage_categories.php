<?php
require_once '../config/db.php';
header('Content-Type: application/json');

class CategoryManager {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function getCategories($search = null) {
        $where = [];
        $params = [];
        if (!empty($search)) {
            $where[] = 'c.name LIKE ?';
            $params[] = '%' . $search . '%';
        }
        $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT c.*, p.name as parent_name FROM categories c LEFT JOIN categories p ON c.parent_id = p.id $where_sql ORDER BY c.id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public function addCategory($name, $parent_id) {
        if ($name === '') {
            return ['error' => 'Category name required.'];
        }
        $stmt = $this->pdo->prepare('INSERT INTO categories (name, parent_id) VALUES (?, ?)');
        if ($stmt->execute([$name, $parent_id])) {
            return ['message' => 'Category added!'];
        }
        return ['error' => 'Error adding category.'];
    }
    public function editCategory($id, $name, $parent_id) {
        if ($name === '') {
            return ['error' => 'Category name required.'];
        }
        $stmt = $this->pdo->prepare('UPDATE categories SET name=?, parent_id=? WHERE id=?');
        if ($stmt->execute([$name, $parent_id, $id])) {
            return ['message' => 'Category updated!'];
        }
        return ['error' => 'Error updating category.'];
    }
    public function deleteCategory($id) {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM categories WHERE id=?');
            if ($stmt->execute([$id])) {
                return ['message' => 'Category deleted!'];
            } else {
                return ['error' => 'Error deleting category.'];
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                return ['error' => 'Cannot delete: Category is in use (has subcategories or products).'];
            }
            return ['error' => 'Error deleting category.'];
        }
    }
}

$catApi = new CategoryManager($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search = $_GET['search'] ?? null;
    $categories = $catApi->getCategories($search);
    echo json_encode(['categories' => $categories]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $result = [];
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $parent_id = $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;
        $result = $catApi->addCategory($name, $parent_id);
    } elseif ($action === 'edit') {
        $id = (int)$_POST['category_id'];
        $name = trim($_POST['name'] ?? '');
        $parent_id = $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;
        $result = $catApi->editCategory($id, $name, $parent_id);
    } elseif ($action === 'delete') {
        $id = (int)$_POST['category_id'];
        $result = $catApi->deleteCategory($id);
    }
    if (!isset($result['message'])) $result['message'] = '';
    if (!isset($result['error'])) $result['error'] = '';
    echo json_encode($result);
    exit;
}
