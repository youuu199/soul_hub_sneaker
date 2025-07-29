<?php include __DIR__ . '/auth.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$user_name = $_SESSION['user_name'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;

// Cart count for header (session-based, no DB)
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $now = time();
    foreach ($_SESSION['cart'] as $key => $item) {
        if (isset($item['added_at']) && $now - $item['added_at'] > 3600) {
            unset($_SESSION['cart'][$key]);
        }
    }
    $cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));
}

// Cart expiry notification
$cart_expiry_warning = false;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $now = time();
    foreach ($_SESSION['cart'] as $item) {
        if (isset($item['added_at']) && $now - $item['added_at'] > 3300 && $now - $item['added_at'] < 3600) {
            $cart_expiry_warning = true;
            break;
        }
    }
}

$current = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SoleHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- TailwindCSS CDN (for development, use compiled in production) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="/SoleHub/assets/js/scripts.js"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
  <!-- Header/Navbar -->
  <header class="bg-white shadow sticky top-0 z-40">
    <nav class="container mx-auto px-4 flex items-center justify-between h-16">
      <!-- Logo -->
      <a href="/SoleHub/index.php" class="flex items-center gap-2 font-bold text-xl text-blue-700">
        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-9 2v8m0 0h4m-4 0a2 2 0 01-2-2v-4a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2h-4z"/>
        </svg>
        SoleHub
      </a>
      <!-- Desktop Menu -->
      <ul class="hidden md:flex items-center gap-6 font-medium text-gray-700">
        <li><a href='/SoleHub/index.php' class='hover:text-blue-600 transition<?php if($current==='index.php') echo " text-blue-600 font-bold"; ?>'>Home</a></li>
        <li><a href='/SoleHub/products.php' class='hover:text-blue-600 transition<?php if($current==='products.php') echo " text-blue-600 font-bold"; ?>'>Shop</a></li>
        <li><a href='/SoleHub/about.php' class='hover:text-blue-600 transition<?php if($current==='about.php') echo " text-blue-600 font-bold"; ?>'>About</a></li>
        <li><a href='/SoleHub/contact.php' class='hover:text-blue-600 transition<?php if($current==='contact.php') echo " text-blue-600 font-bold"; ?>'>Contact</a></li>
        <li>
          <a href='/SoleHub/wishlist.php' class='relative group<?php if($current==='wishlist.php') echo " text-pink-600 font-bold"; ?>' title='Wishlist'>
            <svg class='w-6 h-6 text-pink-500 group-hover:text-pink-600 transition' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 21.364l-7.682-7.682a4.5 4.5 0 010-6.364z'/></svg>
          </a>
        </li>
        <?php if ($user_role === 'admin'): ?>
          <li><a href='/SoleHub/admin/dashboard.php' class='text-indigo-600 hover:underline'>Admin</a></li>
        <?php endif; ?>
        <?php if ($user_name): ?>
          <li class='relative' id='profile-menu-parent'>
            <button id='profile-menu-btn' class='flex items-center gap-2 hover:text-blue-600 focus:outline-none<?php if($current==='profile.php'||$current==='orders.php') echo " text-blue-600 font-bold"; ?>'>
              <svg class='w-6 h-6 text-blue-500' fill='currentColor' viewBox='0 0 24 24'><path d='M12 12c2.7 0 8 1.34 8 4v2H4v-2c0-2.66 5.3-4 8-4zm0-2a4 4 0 100-8 4 4 0 000 8z'/></svg>
              <?= htmlspecialchars($user_name) ?>
              <svg class='w-4 h-4 ml-1' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/></svg>
            </button>
            <div id='profile-menu-dropdown' class='absolute right-0 mt-2 w-44 bg-white border rounded shadow-lg opacity-0 pointer-events-none transition z-50'>
              <a href='/SoleHub/profile.php' class='block px-4 py-2 hover:bg-gray-100<?php if($current==='profile.php') echo " text-blue-600 font-bold"; ?>'>Profile</a>
              <a href='/SoleHub/orders.php' class='block px-4 py-2 hover:bg-gray-100<?php if($current==='orders.php') echo " text-blue-600 font-bold"; ?>'>My Orders</a>
              <form action='/SoleHub/logout.php' method='post'>
                <button type='submit' class='w-full text-left px-4 py-2 hover:bg-gray-100'>Logout</button>
              </form>
            </div>
            <script>
            // Profile dropdown: open on click, close on outside click
            const btn = document.getElementById('profile-menu-btn');
            const dropdown = document.getElementById('profile-menu-dropdown');
            btn.addEventListener('click', function(e) {
              e.stopPropagation();
              dropdown.classList.toggle('opacity-0');
              dropdown.classList.toggle('pointer-events-none');
            });
            document.addEventListener('click', function(e) {
              if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('opacity-0');
                dropdown.classList.add('pointer-events-none');
              }
            });
            </script>
          </li>
        <?php else: ?>
          <li><a href='/SoleHub/login.php' class='bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition'>Login</a></li>
        <?php endif; ?>
        <li>
          <a href='/SoleHub/cart.php' class='relative flex items-center hover:text-blue-600<?php if($current==='cart.php') echo " text-blue-600 font-bold"; ?>'>
            <svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
              <circle cx='9' cy='21' r='1'/><circle cx='20' cy='21' r='1'/>
              <path d='M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6'/>
            </svg>
            <span id='cart-count' class='absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5'><?php echo $cart_count; ?></span>
          </a>
        </li>
      </ul>
      <!-- Hamburger Button -->
      <button id="menu-btn" class="md:hidden flex items-center px-2 py-1 border rounded text-gray-700 border-gray-300 focus:outline-none" aria-label="Open Menu">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
    </nav>
    <!-- Mobile Menu -->
    <div id="mobile-menu" class="md:hidden fixed inset-0 bg-black bg-opacity-40 z-50 transition-all duration-200 hidden">
      <div class="absolute top-0 right-0 w-72 max-w-full bg-white h-full shadow-lg flex flex-col animate-slideIn">
        <button id="close-menu-btn" class="self-end m-4 text-gray-400 hover:text-gray-700" aria-label="Close Menu">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
        <nav class='flex flex-col gap-2 px-6'>
          <a href='/SoleHub/index.php' class='py-2 border-b hover:text-blue-600<?php if($current==='index.php') echo " text-blue-600 font-bold"; ?>'>Home</a>
          <a href='/SoleHub/products.php' class='py-2 border-b hover:text-blue-600<?php if($current==='products.php') echo " text-blue-600 font-bold"; ?>'>Shop</a>
          <a href='/SoleHub/about.php' class='py-2 border-b hover:text-blue-600<?php if($current==='about.php') echo " text-blue-600 font-bold"; ?>'>About</a>
          <a href='/SoleHub/contact.php' class='py-2 border-b hover:text-blue-600<?php if($current==='contact.php') echo " text-blue-600 font-bold"; ?>'>Contact</a>
          <a href='/SoleHub/wishlist.php' class='py-2 border-b flex items-center gap-2<?php if($current==='wishlist.php') echo " text-pink-600 font-bold"; ?>'>
            <svg class='w-6 h-6 text-pink-500' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 21.364l-7.682-7.682a4.5 4.5 0 010-6.364z'/></svg>
            Wishlist
          </a>
          <?php if ($user_role === 'admin'): ?>
            <a href='/SoleHub/admin/dashboard.php' class='py-2 border-b text-indigo-600'>Admin</a>
          <?php endif; ?>
          <?php if ($user_name): ?>
            <a href='/SoleHub/profile.php' class='py-2 border-b<?php if($current==='profile.php') echo " text-blue-600 font-bold"; ?>'>Profile</a>
            <a href='/SoleHub/orders.php' class='py-2 border-b<?php if($current==='orders.php') echo " text-blue-600 font-bold"; ?>'>My Orders</a>
            <form action='/SoleHub/logout.php' method='post' class='py-2 border-b'>
              <button type='submit' class='w-full text-left hover:text-red-600'>Logout</button>
            </form>
          <?php else: ?>
            <a href='/SoleHub/login.php' class='py-2 border-b text-blue-600'>Login</a>
          <?php endif; ?>
          <a href='/SoleHub/cart.php' class='py-2 border-b flex items-center gap-2<?php if($current==='cart.php') echo " text-blue-600 font-bold"; ?>'>
            <svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
              <circle cx='9' cy='21' r='1'/><circle cx='20' cy='21' r='1'/>
              <path d='M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6'/>
            </svg>
            Cart <span id='cart-count-mobile' class='ml-1 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5'>0</span>
          </a>
        </nav>
      </div>
    </div>
    <!-- Cart Expiry Warning -->
    <?php if ($cart_expiry_warning): ?>
      <div id="cart-expiry-warning" class="fixed top-0 left-0 w-full bg-yellow-100 text-yellow-800 text-center py-2 z-50">
        Items in your cart will expire soon if you do not complete payment.
      </div>
      <script>
        setTimeout(() => {
          const warn = document.getElementById('cart-expiry-warning');
          if (warn) warn.style.display = 'none';
        }, 8000);
      </script>
    <?php endif; ?>
  </header>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Hamburger menu toggle
      var menuBtn = document.getElementById('menu-btn');
      var mobileMenu = document.getElementById('mobile-menu');
      var closeMenuBtn = document.getElementById('close-menu-btn');
      if (menuBtn && mobileMenu && closeMenuBtn) {
        menuBtn.addEventListener('click', function() {
          mobileMenu.classList.remove('hidden');
          document.body.classList.add('overflow-hidden');
        });
        closeMenuBtn.addEventListener('click', function() {
          mobileMenu.classList.add('hidden');
          document.body.classList.remove('overflow-hidden');
        });
        mobileMenu.addEventListener('click', function(e) {
          if (e.target === mobileMenu) {
            mobileMenu.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
          }
        });
      }
      // Cart count sync (example, replace with AJAX if needed)
      function syncCartCount() {
        var count = localStorage.getItem('cart_count') || 0;
        var cartCount = document.getElementById('cart-count');
        var cartCountMobile = document.getElementById('cart-count-mobile');
        if (cartCount) cartCount.textContent = count;
        if (cartCountMobile) cartCountMobile.textContent = count;
      }
      syncCartCount();
      window.addEventListener('storage', syncCartCount);
    });
    function updateCartCount() {
      fetch('/SoleHub/api/cart_count.php')
        .then(res => res.json())
        .then(data => {
          document.querySelectorAll('#cart-count').forEach(el => el.textContent = data.count);
        });
    }
  </script>
  <style>
    @media (max-width: 768px) {
      .animate-slideIn {
        animation: slideIn .2s cubic-bezier(.4,0,.2,1);
      }
      @keyframes slideIn {
        from { transform: translateX(100%);}
        to { transform: translateX(0);}
      }
    }
  </style>
</body>
</html>