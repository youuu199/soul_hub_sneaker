<?php
session_start();
if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    header('Location: /SoleHub/admin/dashboard.php');
    exit;
}

include 'includes/header.php';
include_once 'includes/user/ProductSection.php';
require_once 'config/db.php';

if (isset($_GET['login_required'])) {
    echo '
    <div id="login-alert" class="fixed top-4 inset-x-0 flex justify-center z-50">
        <div class="bg-red-500 text-white px-6 py-3 rounded shadow-lg animate-fade-in">
            <strong>Please login to access that page.</strong>
        </div>
    </div>
    <script>
        setTimeout(() => {
            const alert = document.getElementById("login-alert");
            if (alert) alert.style.display = "none";
        }, 2500); // auto-hide after 2.5 seconds
    </script>
    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in {
            animation: fade-in 0.5s ease-out;
        }
    </style>';
}

// Fetch featured brands (5 random with logo)
$featuredBrands = $pdo->query("SELECT name, logo FROM brands WHERE logo IS NOT NULL AND logo != '' ORDER BY RAND() LIMIT 5")->fetchAll();
?>
<style>
    .animate-fade-in-up {
        animation: fadeInUp 0.8s ease-out forwards;
    }
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<main class="flex-1 bg-gradient-to-br from-blue-50 to-white min-h-screen">
    <section class="container mx-auto px-4 py-16 md:py-24 flex flex-col md:flex-row items-center gap-12 md:gap-20">
        <div class="flex-1 space-y-6 animate-fade-in-up">
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 mb-6 leading-tight tracking-tight">
                Elevate Your Footwear Game<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-indigo-600">With
                    SoleHub</span>
            </h1>
            <p class="text-lg md:text-xl text-gray-600 mb-8 max-w-xl">
                Curating the most exclusive sneaker drops from top brands. Limited editions, collector's items, and
                everyday essentials—all authenticated.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="/SoleHub/products.php"
                    class="relative overflow-hidden group bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold px-8 py-3.5 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <span class="relative z-10">Shop Collection</span>
                    <span
                        class="absolute inset-0 bg-gradient-to-r from-blue-500 to-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                </a>
                <a href="/SoleHub/about.php"
                    class="bg-white border-2 border-gray-200 hover:border-blue-500 text-gray-800 hover:text-blue-600 font-semibold px-8 py-3.5 rounded-lg hover:bg-blue-50 transition-all duration-300">
                    Our Story
                </a>
            </div>
        </div>
    </section>
    <section class="container mx-auto px-4 py-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-8 text-center">Featured Brands</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-6 items-center justify-center">
            <?php foreach ($featuredBrands as $brand): ?>
                <img src="/SoleHub/<?= htmlspecialchars($brand['logo']) ?>" alt="<?= htmlspecialchars($brand['name']) ?>" class="h-16 object-contain mx-auto" loading="lazy">
            <?php endforeach; ?>
        </div>
    </section>
    <section class="container mx-auto px-4 py-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-8 text-center">Why SoleHub?</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center text-center">
                <svg class="w-12 h-12 text-blue-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 0V4m0 8v8m8-8h-8m8 0a8 8 0 11-16 0 8 8 0 0116 0z" />
                </svg>
                <h3 class="font-semibold text-lg mb-2">Curated Selection</h3>
                <p class="text-gray-600">Handpicked sneakers from top brands and exclusive releases you won’t find
                    anywhere else.</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center text-center">
                <svg class="w-12 h-12 text-blue-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h2l1 2h13a1 1 0 001-1V7a1 1 0 00-1-1H6.42l-1-2H3" />
                </svg>
                <h3 class="font-semibold text-lg mb-2">Fast Shipping</h3>
                <p class="text-gray-600">Get your kicks delivered quickly and securely, right to your doorstep.</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center text-center">
                <svg class="w-12 h-12 text-blue-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <h3 class="font-semibold text-lg mb-2">Trusted by Sneakerheads</h3>
                <p class="text-gray-600">Join a community of sneaker lovers who trust SoleHub for quality and
                    authenticity.</p>
            </div>
        </div>
    </section>
    <!-- New Arrivals Section -->
    <?php (new ProductSection('New Arrivals'))->render(); ?>
    <!-- Whart We Provide Section -->
    <section class="container mx-auto px-4 py-12">
        <div class="grid md:grid-cols-2 gap-8">
            <div class="bg-white rounded-2xl shadow-lg p-8 flex flex-col justify-center items-center text-center">
                <svg class="w-14 h-14 text-blue-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                </svg>
                <h3 class="font-bold text-xl mb-2">100% Authenticity Guarantee</h3>
                <p class="text-gray-600">Every pair is verified by our team of experts. Shop with confidence—no fakes,
                    ever.</p>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-8 flex flex-col justify-center items-center text-center">
                <svg class="w-14 h-14 text-blue-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2a4 4 0 018 0v2m-4-4v4m0 0v4m0-4h4m-4 0H7" />
                </svg>
                <h3 class="font-bold text-xl mb-2">Flexible Payment Options</h3>
                <p class="text-gray-600">Pay your way: credit, debit, PayPal, or buy now, pay later. Shopping made easy.
                </p>
            </div>
        </div>
    </section>
    <!-- Best Sellers Section -->
    <?php (new ProductSection('Best Sellers'))->render(); ?>
    <!-- Customer Reviews Section -->
    <section class="container mx-auto px-4 py-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-8 text-center">What Our Customers Say</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center text-center">
                <svg class="w-10 h-10 text-yellow-400 mb-2" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z" />
                </svg>
                <p class="text-gray-700 mb-2">“Fast shipping and the shoes are 100% authentic. Will buy again!”</p>
                <span class="font-semibold text-blue-600">— Alex P.</span>
            </div>
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center text-center">
                <svg class="w-10 h-10 text-yellow-400 mb-2" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z" />
                </svg>
                <p class="text-gray-700 mb-2">“Best selection of sneakers online. Customer service is top notch.”</p>
                <span class="font-semibold text-blue-600">— Jamie L.</span>
            </div>
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center text-center">
                <svg class="w-10 h-10 text-yellow-400 mb-2" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z" />
                </svg>
                <p class="text-gray-700 mb-2">“Love the deals and the easy checkout process. Highly recommend!”</p>
                <span class="font-semibold text-blue-600">— Morgan S.</span>
            </div>
        </div>
    </section>
    <!-- Newsletter Signup Section -->
    <section class="container mx-auto px-4 py-12">
        <div class="bg-blue-50 rounded-xl p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Stay in the Loop</h2>
                <p class="text-gray-600 mb-4">Sign up for our newsletter to get the latest drops and exclusive offers.
                </p>
            </div>
            <form class="flex flex-col sm:flex-row gap-2 w-full max-w-md">
                <input type="email" placeholder="Your email address"
                    class="border rounded px-4 py-2 flex-1 focus:outline-none" required>
                <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Subscribe</button>
            </form>
        </div>
    </section>
    <!-- How It Works Section -->
    <section class="container mx-auto px-4 py-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-8 text-center">How It Works</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center text-center">
                <svg class="w-12 h-12 text-blue-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h2l1 2h13a1 1 0 001-1V7a1 1 0 00-1-1H6.42l-1-2H3" />
                </svg>
                <h3 class="font-semibold text-lg mb-2">Browse & Discover</h3>
                <p class="text-gray-600">Explore our curated collection and find your perfect pair.</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center text-center">
                <svg class="w-12 h-12 text-blue-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 0V4m0 8v8m8-8h-8m8 0a8 8 0 11-16 0 8 8 0 0116 0z" />
                </svg>
                <h3 class="font-semibold text-lg mb-2">Order & Checkout</h3>
                <p class="text-gray-600">Easy, secure checkout and fast shipping to your door.</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center text-center">
                <svg class="w-12 h-12 text-blue-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <h3 class="font-semibold text-lg mb-2">Enjoy & Share</h3>
                <p class="text-gray-600">Rock your new kicks and share your style with the world.</p>
            </div>
        </div>
    </section>
    <!-- Instagram Feed Section (static demo) -->
    <section class="container mx-auto px-4 py-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-8 text-center">Follow Us on Instagram</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
            <img src="/SoleHub/assets/img/insta1.jpg" alt="Insta 1" class="rounded-lg object-cover h-32 w-full"
                onerror="this.style.display='none'">
            <img src="/SoleHub/assets/img/insta2.jpg" alt="Insta 2" class="rounded-lg object-cover h-32 w-full"
                onerror="this.style.display='none'">
            <img src="/SoleHub/assets/img/insta3.jpg" alt="Insta 3" class="rounded-lg object-cover h-32 w-full"
                onerror="this.style.display='none'">
            <img src="/SoleHub/assets/img/insta4.jpg" alt="Insta 4" class="rounded-lg object-cover h-32 w-full"
                onerror="this.style.display='none'">
            <img src="/SoleHub/assets/img/insta5.jpg" alt="Insta 5" class="rounded-lg object-cover h-32 w-full"
                onerror="this.style.display='none'">
            <img src="/SoleHub/assets/img/insta6.jpg" alt="Insta 6" class="rounded-lg object-cover h-32 w-full"
                onerror="this.style.display='none'">
        </div>
        <div class="text-center mt-6">
            <a href="#" class="text-blue-600 hover:underline font-semibold">@solehub</a>
        </div>
    </section>
    <!-- Call to Action Section -->
    <section class="container mx-auto px-4 py-12 text-center">
        <div class="bg-blue-600 rounded-xl p-8 text-white flex flex-col items-center gap-4">
            <h2 class="text-2xl font-bold">Ready to Find Your Next Pair?</h2>
            <p class="text-blue-100 mb-4">Join the SoleHub community and never miss a drop.</p>
            <a href="/SoleHub/register.php"
                class="bg-white text-blue-600 font-semibold px-6 py-3 rounded-lg shadow hover:bg-blue-50 transition">Sign
                Up Now</a>
        </div>
    </section>
</main>
<?php include 'includes/footer.php'; ?>