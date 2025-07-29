<?php
// includes/user/wishlist_api.php
require_once __DIR__ . '/../../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

class WishlistApi {
    private $pdo;
    private $user_id;
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->user_id = $_SESSION['user_id'] ?? null;
    }
    public function handle() {
        if (!$this->user_id) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            return;
        }
        $action = $_POST['action'] ?? '';
        $product_id = (int)($_POST['product_id'] ?? 0);
        if (!$product_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid product']);
            return;
        }
        switch ($action) {
            case 'add':
                $this->add($product_id);
                break;
            case 'remove':
                $this->remove($product_id);
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    }
    private function add($product_id) {
        $stmt = $this->pdo->prepare("SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$this->user_id, $product_id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => true, 'message' => 'Already in wishlist']);
            return;
        }
        $stmt = $this->pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$this->user_id, $product_id]);
        echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
    }
    private function remove($product_id) {
        $stmt = $this->pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$this->user_id, $product_id]);
        if ($stmt->rowCount() > 0) {
            // Successfully removed
            echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Item was not in wishlist']);
        }
    }
}

$wishlistApi = new WishlistApi($pdo);
$wishlistApi->handle();
