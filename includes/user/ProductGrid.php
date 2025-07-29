<?php
// includes/user/ProductGrid.php
require_once __DIR__ . '/../../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
class ProductGrid {
    private $products;
    private $wishlistIds;
    public function __construct($products) {
        $this->products = $products;
        $this->wishlistIds = $this->getUserWishlistProductIds($GLOBALS['pdo']);
    }
    private function getUserWishlistProductIds($pdo) {
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) return [];
        $stmt = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return array_column($stmt->fetchAll(), 'product_id');
    }
    public function render() {

        echo '<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-8 custom-md">';
        foreach ($this->products as $product) {
            $this->renderProduct($product);
        }
        echo '</div>';
        // Add custom style for md breakpoint at 834px
        echo '<style>@media (min-width: 834px) { .custom-md.md\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); } }</style>';
    }
    private function renderProduct($product) {
        $img = htmlspecialchars($product['img']);
        $name = htmlspecialchars($product['name']);
        $price = htmlspecialchars($product['price']);
        $id = htmlspecialchars($product['id']);
        $inWishlist = in_array($product['id'], $this->wishlistIds);
        echo '<div class="bg-white rounded-xl shadow p-4 flex flex-col items-center">';
        echo '<img src="' . $img . '" alt="' . $name . '" class="h-32 object-contain mb-4">';
        echo '<h3 class="font-semibold text-lg mb-2">' . $name . '</h3>';
        echo '<p class="text-blue-600 font-bold mb-2">$' . $price . '</p>';
        echo '<div class="flex gap-2 mb-2">';
        echo '<button class="wishlist-btn" data-id="' . $id . '" title="Add to Wishlist">';
        if ($inWishlist) {
            echo '<svg class="w-6 h-6 text-pink-500" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24" data-filled="1"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 21.364l-7.682-7.682a4.5 4.5 0 010-6.364z"/></svg>';
        } else {
            echo '<svg class="w-6 h-6 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" data-filled="0"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 21.364l-7.682-7.682a4.5 4.5 0 010-6.364z"/></svg>';
        }
        echo '</button>';
        echo '</div>';
        echo '<a href="/SoleHub/product_detail.php?id=' . $id . '" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition w-full text-center">View</a>';
        echo '</div>';
    }
}
