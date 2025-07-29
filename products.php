<?php
// Only output header HTML for normal GET requests (not AJAX)
$isAjax = ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);
if (!$isAjax) {
    include 'includes/auth.php';
    include 'includes/header.php';
}
include_once 'includes/user/ProductGrid.php';
if ($isAjax) {
    $input = json_decode(file_get_contents('php://input'), true);
    $products = $input['products'] ?? [];
    $grid = new ProductGrid($products);
    $grid->render();
    exit;
}
?>
<main class="flex-1 bg-gradient-to-br from-blue-50 to-white min-h-screen">
  <section class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">Shop Sneakers</h1>
    <div class="flex flex-col md:flex-row gap-8">
      <!-- Filter Sidebar -->
      <aside class="md:w-1/4 w-full bg-white rounded-xl shadow p-6 mb-8 md:mb-0">
        <h2 class="text-xl font-semibold mb-4">Filter</h2>
        <form id="filterForm" class="space-y-4">
          <div>
            <label class="block text-gray-700 mb-1">Brand</label>
            <select name="brand" class="w-full border rounded px-3 py-2">
              <option value="">All Brands</option>
            </select>
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Category</label>
            <select name="category" class="w-full border rounded px-3 py-2">
              <option value="">All Categories</option>
            </select>
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Search</label>
            <input type="text" name="search" class="w-full border rounded px-3 py-2" placeholder="Search sneakers...">
          </div>
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded w-full hover:bg-blue-700 transition">Apply Filter</button>
        </form>
      </aside>
      <!-- Product Grid -->
      <section class="flex-1">
        <div id="productGrid"></div>
        <div id="noResults" class="text-center text-gray-500 mt-8 hidden">No products found.</div>
        <div id="pagination" class="flex justify-center items-center gap-2 mt-8"></div>
      </section>
    </div>
  </section>
</main>
<script src="/SoleHub/assets/js/scripts.js"></script>
<script>
let currentPage = 1;

fetch('/SoleHub/api/filter_products.php?meta=1')
  .then(res => res.json())
  .then(data => {
    if (data.brands) {
      const brandSel = document.querySelector('select[name="brand"]');
      data.brands.forEach(b => {
        brandSel.innerHTML += `<option value="${b}">${b}</option>`;
      });
    }
    if (data.categories) {
      const catSel = document.querySelector('select[name="category"]');
      data.categories.forEach(c => {
        catSel.innerHTML += `<option value="${c}">${c}</option>`;
      });
    }
  });

function loadProducts(page = 1) {
  currentPage = page;
  const form = document.getElementById('filterForm');
  const params = new URLSearchParams(new FormData(form));
  params.set('page', page);
  fetch('/SoleHub/api/filter_products.php?' + params)
    .then(res => res.json())
    .then(data => {
      const grid = document.getElementById('productGrid');
      grid.innerHTML = '';
      if (!data.products || data.products.length === 0) {
        document.getElementById('noResults').classList.remove('hidden');
        document.getElementById('pagination').innerHTML = '';
        return;
      }
      document.getElementById('noResults').classList.add('hidden');
      // Use PHP class for rendering via AJAX to this same file
      fetch('/SoleHub/products.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ products: data.products })
      })
      .then(r => r.text())
      .then(html => {
        grid.innerHTML = html;
      });
      renderPagination(data.page, data.totalPages);
    });
}
document.getElementById('filterForm').addEventListener('submit', function(e) {
  e.preventDefault();
  loadProducts(1);
});
window.addEventListener('DOMContentLoaded', () => loadProducts(1));

function renderPagination(page, totalPages) {
  const pag = document.getElementById('pagination');
  pag.innerHTML = '';
  if (totalPages <= 1) return;
  if (page > 1) {
    pag.innerHTML += `<button class='px-3 py-1 rounded bg-gray-200 hover:bg-gray-300' onclick='loadProducts(${page-1})'>&laquo; Prev</button>`;
  }
  for (let i = 1; i <= totalPages; i++) {
    pag.innerHTML += `<button class='px-3 py-1 rounded ${i === page ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300'}' onclick='loadProducts(${i})'>${i}</button>`;
  }
  if (page < totalPages) {
    pag.innerHTML += `<button class='px-3 py-1 rounded bg-gray-200 hover:bg-gray-300' onclick='loadProducts(${page+1})'>Next &raquo;</button>`;
  }
}

document.addEventListener('click', function(e) {
 // Wishlist toggle (no reload, instant color change)
  const btn = e.target.closest('.wishlist-btn');
  if (btn) {
    const id = btn.getAttribute('data-id');
    const svg = btn.querySelector('svg');
    let isFilled = svg.getAttribute('data-filled') == '1';
    let action = isFilled ? 'remove' : 'add';
    fetch('/SoleHub/includes/user/wishlist_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=${action}&product_id=${id}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        // Toggle UI only if backend succeeded
        if (action === 'add') {
          svg.setAttribute('fill', 'currentColor');
          svg.setAttribute('data-filled', '1');
          svg.classList.add('fill-pink-500');
        } else if (action === 'remove') {
          svg.setAttribute('fill', 'none');
          svg.setAttribute('data-filled', '0');
          svg.classList.remove('fill-pink-500');
        }
        showWishlistToast(data.message, action);
      } else {
        showWishlistToast(data.message, 'error');
      }
    });
  }
});

function showWishlistToast(message, type) {
  let toast = document.getElementById('wishlist-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'wishlist-toast';
    toast.className = 'fixed top-6 right-6 z-50 px-4 py-2 rounded shadow text-white text-sm font-semibold';
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.style.backgroundColor = type === 'add' ? '#db2777' : (type === 'remove' ? '#64748b' : '#dc2626');
  toast.style.opacity = '1';
  setTimeout(() => { toast.style.opacity = '0'; }, 1800);
}
</script>
<?php if (!$isAjax) include 'includes/footer.php'; ?>
