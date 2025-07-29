<?php
require_once '../config/db.php';
session_start();
header('Content-Type: application/json');

class UserManager {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function getUsers($filters) {
        $page = max(1, (int)($filters['page'] ?? 1));
        $per_page = 15;
        $offset = ($page - 1) * $per_page;
        $where = [];
        $params = [];
        if (!empty($filters['search'])) {
            $where[] = '(name LIKE ? OR email LIKE ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }
        $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $count_sql = "SELECT COUNT(*) FROM users $where_sql";
        $count_stmt = $this->pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_users = (int)$count_stmt->fetchColumn();
        $total_pages = max(1, ceil($total_users / $per_page));
        $sql = "SELECT * FROM users $where_sql ORDER BY id DESC LIMIT $per_page OFFSET $offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        return [
            'users' => $users,
            'total_pages' => $total_pages,
            'page' => $page
        ];
    }
    public function userAction($current_id, $current_role, $uid, $action) {
        $msg = '';
        if (!$current_id || !$uid || !$action) return ['message' => 'Unauthorized or invalid request.'];
        // Prevent self-action unless superadmin
        if ($uid == $current_id && $current_role !== 'superadmin') {
            return ['message' => 'You cannot perform actions on your own account.'];
        }
        // Get target user's role
        $stmt = $this->pdo->prepare('SELECT role FROM users WHERE id = ?');
        $stmt->execute([$uid]);
        $target_role = $stmt->fetchColumn();
        if (!$target_role) return ['message' => 'Target user not found.'];
        // Superadmin can do all actions
        if ($current_role === 'superadmin') {
            return $this->doAction($uid, $action);
        }
        // Admin restrictions
        if ($current_role === 'admin') {
            if ($target_role === 'admin' || $target_role === 'superadmin') {
                return ['message' => 'You cannot modify other admins or superadmins.'];
            }
            if ($action === 'make_admin' || $action === 'make_superadmin') {
                return ['message' => 'You cannot promote users to admin or superadmin.'];
            }
            return $this->doAction($uid, $action);
        }
        // Default: not allowed
        return ['message' => 'Unauthorized.'];
    }
    private function doAction($uid, $action) {
        $msg = '';
        switch ($action) {
            case 'suspend':
                $stmt = $this->pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
                $stmt->execute(['suspended', $uid]);
                $msg = 'User suspended.';
                break;
            case 'ban':
                $stmt = $this->pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
                $stmt->execute(['banned', $uid]);
                $msg = 'User banned.';
                break;
            case 'activate':
                $stmt = $this->pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
                $stmt->execute(['active', $uid]);
                $msg = 'User activated.';
                break;
            case 'make_admin':
                $stmt = $this->pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
                $stmt->execute(['admin', $uid]);
                $msg = 'User promoted to admin.';
                break;
            case 'make_user':
                $stmt = $this->pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
                $stmt->execute(['user', $uid]);
                $msg = 'User demoted to user.';
                break;
            case 'make_superadmin':
                $stmt = $this->pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
                $stmt->execute(['superadmin', $uid]);
                $msg = 'User promoted to superadmin.';
                break;
            case 'delete':
                $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
                $stmt->execute([$uid]);
                $msg = 'User deleted.';
                break;
            default:
                $msg = 'Invalid action.';
        }
        return ['message' => $msg];
    }
}

$userApi = new UserManager($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filters = [
        'search' => $_GET['search'] ?? null,
        'status' => $_GET['status'] ?? null,
        'page' => $_GET['page'] ?? 1
    ];
    $result = $userApi->getUsers($filters);
    echo json_encode($result);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $uid = (int)($input['user_id'] ?? 0);
    $action = $input['action'] ?? '';
    $current_id = $_SESSION['user_id'] ?? null;
    $current_role = null;
    if ($current_id) {
        $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
        $stmt->execute([$current_id]);
        $current_role = $stmt->fetchColumn();
    }
    $result = $userApi->userAction($current_id, $current_role, $uid, $action);
    echo json_encode($result);
    exit;
}
