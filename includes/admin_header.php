<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$admin_name = $_SESSION['user_name'] ?? 'Admin';
$admin_role = $_SESSION['user_role'] ?? 'admin';
?>
<!-- Admin Header with Sidebar Layout and Hamburger Nav for Tablet/Mobile -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SOLEHUB Admin</title>
  <!-- TailwindCSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="flex min-h-screen">
  <!-- Sidebar (Desktop) -->
  <aside class="w-64 bg-white border-r hidden md:block min-h-screen">
    <div class="p-6">
      <div class="font-bold text-lg mb-4">Admin Menu</div>
      <ul class="space-y-2">
        <li><a href="/SoleHub/admin/dashboard.php" class="block px-3 py-2 rounded hover:bg-blue-50 text-gray-700">Dashboard</a></li>
        <li><a href="/SoleHub/admin/manage_products.php" class="block px-3 py-2 rounded hover:bg-blue-50 text-gray-700">Products</a></li>
        <li><a href="/SoleHub/admin/manage_orders.php" class="block px-3 py-2 rounded hover:bg-blue-50 text-gray-700">Orders</a></li>
        <li><a href="/SoleHub/admin/manage_users.php" class="block px-3 py-2 rounded hover:bg-blue-50 text-gray-700">Users</a></li>
        <li><a href="/SoleHub/admin/categories.php" class="block px-3 py-2 rounded hover:bg-blue-50 text-gray-700">Categories</a></li>
        <li><a href="/SoleHub/admin/brands.php" class="block px-3 py-2 rounded hover:bg-blue-50 text-gray-700">Brands</a></li>
        <li><a href="/SoleHub/admin/sales_analytics.php" class="block px-3 py-2 rounded hover:bg-blue-50 text-gray-700">Sales Analytics</a></li>
        <li><a href="/SoleHub/admin/contact_messages.php" class="block px-3 py-2 rounded hover:bg-blue-50 text-gray-700">Contact Messages</a></li>
        <li><a href="/SoleHub/logout.php" class="block px-3 py-2 rounded hover:bg-red-100 text-red-500">Logout</a></li>
      </ul>
    </div>
  </aside>
  <!-- Main Content Wrapper -->
  <div class="flex-1 flex flex-col min-h-screen">
    <!-- Top Header -->
    <header class="bg-white shadow p-4 flex items-center justify-between">
      <div class="text-2xl font-bold text-gray-800">SOLEHUB Admin</div>
      <!-- Admin Profile (Desktop only) -->
      <div class="hidden md:flex flex-col items-end space-x-3">
        <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($admin_name); ?></span>
        <span class="text-xs text-gray-500 -mt-1"><?php echo htmlspecialchars(ucfirst($admin_role)); ?></span>
      </div>
      <!-- Hamburger for Tablet/Mobile -->
      <div class="md:hidden">
        <button id="admin-menu-btn" class="text-gray-700 focus:outline-none" aria-label="Open menu">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
      </div>
    </header>
    <!-- Hamburger Nav Drawer (Tablet/Mobile) -->
    <nav id="admin-mobile-nav" class="fixed top-0 left-0 w-64 h-full bg-white shadow-lg z-50 transform -translate-x-full transition-transform duration-200 md:hidden">
      <div class="p-6 flex flex-col space-y-4">
        <div class="flex flex-col items-start space-y-0 mb-4">
          <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($admin_name); ?></span>
          <span class="text-xs text-gray-500 -mt-1"><?php echo htmlspecialchars(ucfirst($admin_role)); ?></span>
        </div>
        <div class="font-bold text-lg mb-4">Admin Menu</div>
        <a href="/SoleHub/admin/dashboard.php" class="block px-3 py-2 rounded hover:bg-blue-50 text-gray-700">Dashboard</a>
        <a href="/SoleHub/admin/manage_products.php" class="block px-3 py-2 rounded hover:bg-blue-50 text-gray-700">Products</a>
        <a href="/SoleHub/admin/manage_orders.php" class="block px-3 py-2 rounded hover:bg-blue-50 text-gray-700">Orders</a>
        <a href="/SoleHub/admin/manage_users.php" class="block px-3 py-2 rounded hover:bg-blue-50 text-gray-700">Users</a>
        <a href="/SoleHub/admin/categories.php" class="block px-3 py-2 rounded hover:bg-blue-50 text-gray-700">Categories</a>
        <a href="/SoleHub/admin/brands.php" class="block px-3 py-2 rounded hover:bg-blue-50 text-gray-700">Brands</a>
        <a href="/SoleHub/admin/sales_analytics.php" class="block px-3 py-2 rounded hover:bg-blue-50 text-gray-700">Sales Analytics</a>
                <a href="/SoleHub/admin/contact_messages.php" class="block px-3 py-2 rounded hover:bg-blue-50 text-gray-700">Contact Messages</a>
        <a href="/SoleHub/logout.php" class="block px-3 py-2 rounded hover:bg-red-100 text-red-500">Logout</a>
        <button id="admin-menu-close" class="mt-8 text-gray-400 hover:text-gray-700">Close</button>
      </div>
    </nav>
    <script>
    // Hamburger menu toggle
    const menuBtn = document.getElementById('admin-menu-btn');
    const mobileNav = document.getElementById('admin-mobile-nav');
    const closeBtn = document.getElementById('admin-menu-close');
    if(menuBtn && mobileNav) {
      menuBtn.addEventListener('click', () => {
        mobileNav.classList.remove('-translate-x-full');
      });
    }
    if(closeBtn && mobileNav) {
      closeBtn.addEventListener('click', () => {
        mobileNav.classList.add('-translate-x-full');
      });
    }
    window.addEventListener('click', function(e) {
      if (mobileNav && !mobileNav.contains(e.target) && !menuBtn.contains(e.target)) {
        mobileNav.classList.add('-translate-x-full');
      }
    });
    </script>
    <!-- Page Content starts in each admin page after this include -->
