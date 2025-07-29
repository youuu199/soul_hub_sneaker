<?php include '../includes/auth.php'; ?>
<?php include '../includes/admin_header.php';
require_once '../config/db.php';

// --- FETCH BRANDS ---
$where = [];
$params = [];
if (!empty($_GET['search'])) {
    $where[] = 'name LIKE ?';
    $params[] = '%' . $_GET['search'] . '%';
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT * FROM brands $where_sql ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$brands = $stmt->fetchAll();
?>
<main class="p-8">
  <h1 class="text-2xl font-bold mb-6 text-gray-800">Brand Management</h1>
  <div id="brand-action-msg" class="hidden bg-green-100 text-green-700 px-4 py-2 rounded mb-4"></div>
  <div class="bg-white rounded shadow p-6 mb-8">
    <form class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4" method="get" action="">
      <input type="text" name="search" id="brand-search" placeholder="Search brands..." class="border rounded px-3 py-2 focus:outline-none w-48">
      <div class="flex gap-2">
        <a href="#" onclick="document.getElementById('add-brand-modal').classList.remove('hidden');return false;" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">+ Add Brand</a>
      </div>
    </form>
    <div class="overflow-x-auto rounded-xl shadow-lg bg-white">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gradient-to-r from-blue-100 to-indigo-100">
          <tr>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Logo</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Name</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody id="brands-table-body" class="divide-y divide-gray-100">
          <!-- Populated by JS -->
        </tbody>
      </table>
    </div>
  </div>
  <!-- Add Brand Modal -->
  <div id="add-brand-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
      <button onclick="document.getElementById('add-brand-modal').classList.add('hidden')" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
      <h2 class="text-xl font-semibold mb-4">Add Brand</h2>
      <form id="add-brand-form" enctype="multipart/form-data" class="flex flex-col gap-4">
        <div>
          <label class="block text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
          <input type="text" name="name" required class="border rounded px-3 py-2 w-full focus:outline-none focus:ring">
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Logo</label>
          <input type="file" name="logo" accept="image/*" class="border rounded px-3 py-2 w-full focus:outline-none">
        </div>
        <div class="flex justify-end gap-2">
          <button type="button" onclick="document.getElementById('add-brand-modal').classList.add('hidden')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Brand</button>
        </div>
      </form>
    </div>
  </div>
  <!-- Edit Brand Modal -->
  <div id="edit-brand-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
      <button onclick="document.getElementById('edit-brand-modal').classList.add('hidden')" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
      <h2 class="text-xl font-semibold mb-4">Edit Brand</h2>
      <form id="edit-brand-form" enctype="multipart/form-data" class="flex flex-col gap-4">
        <input type="hidden" name="brand_id" id="edit-brand-id">
        <input type="hidden" name="current_logo" id="edit-brand-current-logo">
        <div>
          <label class="block text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
          <input type="text" name="name" id="edit-brand-name" required class="border rounded px-3 py-2 w-full focus:outline-none focus:ring">
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Logo</label>
          <input type="file" name="logo" accept="image/*" class="border rounded px-3 py-2 w-full focus:outline-none">
        </div>
        <div class="flex justify-end gap-2">
          <button type="button" onclick="document.getElementById('edit-brand-modal').classList.add('hidden')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
  <script>
// Utility to show feedback
function showBrandMsg(msg, error) {
  const box = document.getElementById('brand-action-msg');
  box.textContent = error ? error : msg;
  box.classList.remove('hidden', 'bg-green-100', 'text-green-700', 'bg-red-100', 'text-red-700');
  if (error) {
    box.classList.add('bg-red-100', 'text-red-700');
  } else {
    box.classList.add('bg-green-100', 'text-green-700');
  }
  setTimeout(()=>box.classList.add('hidden'), 2500);
}
// Fetch and render brands
function fetchBrands() {
  const search = document.getElementById('brand-search').value;
  fetch(`/SoleHub/api/manage_brands.php?search=${encodeURIComponent(search)}`)
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById('brands-table-body');
      tbody.innerHTML = '';
      if (!data.brands.length) {
        tbody.innerHTML = `<tr><td colspan="3" class="text-center text-gray-400 py-4">No brands found.</td></tr>`;
        return;
      }
      data.brands.forEach(brand => {
        tbody.innerHTML += `
          <tr class="border-b hover:bg-blue-50 transition">
            <td class="py-3 px-4">${brand.logo ? `<img src=\"/SoleHub/${escapeHtml(brand.logo)}\" alt=\"${escapeHtml(brand.name)}\" class=\"w-12 h-12 object-contain\">` : '<span class=\"text-gray-400\">No logo</span>'}</td>
            <td class="py-3 px-4 font-medium text-gray-900">${escapeHtml(brand.name)}</td>
            <td class="py-3 px-4 flex gap-2">
              <button class="text-blue-600 hover:underline bg-transparent border-none p-0 m-0 brand-edit" data-id="${brand.id}" data-name="${escapeHtml(brand.name)}" data-logo="${escapeHtml(brand.logo || '')}">Edit</button>
              <button class="text-red-500 hover:underline bg-transparent border-none p-0 m-0 brand-delete" data-id="${brand.id}">Delete</button>
            </td>
          </tr>
        `;
      });
      attachBrandActions();
    });
}
function escapeHtml(text) {
  return String(text).replace(/[&<>'"]/g, function(c) {
    return {'&':'&amp;','<':'&lt;','>':'&gt;','\'':'&#39;','"':'&quot;'}[c];
  });
}
// Attach edit/delete handlers
function attachBrandActions() {
  document.querySelectorAll('.brand-edit').forEach(btn => {
    btn.onclick = function() {
      document.getElementById('edit-brand-id').value = this.dataset.id;
      document.getElementById('edit-brand-name').value = this.dataset.name;
      document.getElementById('edit-brand-current-logo').value = this.dataset.logo;
      document.getElementById('edit-brand-modal').classList.remove('hidden');
    };
  });
  document.querySelectorAll('.brand-delete').forEach(btn => {
    btn.onclick = function() {
      if (!confirm('Delete this brand?')) return;
      const formData = new FormData();
      formData.append('action', 'delete');
      formData.append('brand_id', this.dataset.id);
      fetch('/SoleHub/api/manage_brands.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        showBrandMsg(data.message, data.error);
        if (!data.error) fetchBrands();
      });
    };
  });
}
document.getElementById('brand-search').oninput = function() { fetchBrands(); };
document.addEventListener('DOMContentLoaded', function() {
  fetchBrands();
  // Add brand
  document.getElementById('add-brand-form').onsubmit = function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'add');
    fetch('/SoleHub/api/manage_brands.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      showBrandMsg(data.message, data.error);
      if (!data.error) {
        document.getElementById('add-brand-modal').classList.add('hidden');
        this.reset();
        fetchBrands();
      }
    });
  };
  // Edit brand
  document.getElementById('edit-brand-form').onsubmit = function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'edit');
    fetch('/SoleHub/api/manage_brands.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      showBrandMsg(data.message, data.error);
      if (!data.error) {
        document.getElementById('edit-brand-modal').classList.add('hidden');
        this.reset();
        fetchBrands();
      }
    });
  };
});
  </script>
</main>
<?php include '../includes/admin_footer.php'; ?>
