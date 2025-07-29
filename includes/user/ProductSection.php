<?php
// includes/user/ProductSectionUser.php
// User-specific product section logic (can be extended for user personalization)
require_once __DIR__ . '/../../config/db.php';
class ProductSection {
    private $title;
    private $products;

    public function __construct($title, $products = null) {
        $this->title = $title;
        if ($products === null) {
            $this->products = $this->fetchProductsBySection($title);
        } else {
            $this->products = $products;
        }
    }

    public function render() {
        echo '<section class="container mx-auto px-4 py-12">';
        echo '<h2 class="text-2xl font-bold text-gray-800 mb-8 text-center">' . htmlspecialchars($this->title) . '</h2>';
        echo '<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">';
        foreach ($this->products as $product) {
            $this->renderProduct($product);
        }
        echo '</div>';
        echo '</section>';
    }

    private function renderProduct($product) {
        $img = htmlspecialchars($product['img']);
        $name = htmlspecialchars($product['name']);
        $price = htmlspecialchars($product['price']);
        $id = htmlspecialchars($product['id']);
        echo '<div class="bg-white rounded-xl shadow p-4 flex flex-col items-center">';
        echo '<img src="' . $img . '" alt="' . $name . '" class="h-32 object-contain mb-4">';
        echo '<h3 class="font-semibold text-lg mb-2">' . $name . '</h3>';
        echo '<p class="text-blue-600 font-bold mb-2">$' . $price . '</p>';
        echo '<a href="/SoleHub/product_detail.php?id=' . $id . '" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">View</a>';
        echo '</div>';
    }

    private function fetchProductsBySection($section) {
        global $pdo;
        $products = [];
        if ($section === 'New Arrivals') {
            $sql = "SELECT id, name, base_price as price, featured_image as img FROM products WHERE status='active' ORDER BY release_date DESC LIMIT 4";
        } elseif ($section === 'Best Sellers') {
            $sql = "SELECT id, name, base_price as price, featured_image as img FROM products WHERE status='active' ORDER BY id DESC LIMIT 4";
        } else {
            return $products;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $products = $stmt->fetchAll();
        foreach ($products as &$p) {
            if (empty($p['img'])) {
                $p['img'] = '/SoleHub/assets/img/no-image.png';
            }
        }
        return $products;
    }
}
