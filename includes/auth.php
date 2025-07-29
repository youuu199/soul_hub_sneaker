<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';

$user_id = $_SESSION['user_id'] ?? null;
$user_role = null;

if ($user_id) {
    // Query the database for the latest user role
    $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ? LIMIT 1' );
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    if ($row) {
        $user_role = $row['role'];
        $_SESSION['user_role'] = $user_role; // keep session in sync
    } else {
        // User not found, treat as not logged in
        $user_id = null;
        unset($_SESSION['user_id'], $_SESSION['user_role']);
    }
}

// Get current script name
$current = basename($_SERVER['SCRIPT_NAME']);
$uri = $_SERVER['REQUEST_URI'];
$is_admin_page = strpos($uri, '/admin/') !== false;
$is_user_page = !$is_admin_page && !in_array($current, ['index.php', 'login.php', 'register.php']);

// 1. If not logged in, only allow index, login, register
if (!$user_id) {
    if (!in_array($current, ['index.php', 'login.php', 'register.php'])) {
        header("Location: /SoleHub/index.php?login_required=1");
        exit;
    }
}
// 2. If logged in as user, block admin pages
elseif (!in_array($user_role, ['admin', 'superadmin']) && $is_admin_page) {
    header('Location: /SoleHub/index.php');
    exit;
}
// 3. If logged in as admin, block user pages (except index, login, register)
elseif ($user_role === 'admin' && $is_user_page || $user_role === 'superadmin' && $is_user_page) {
    header('Location: /SoleHub/admin/dashboard.php');
    exit;
}
?>
