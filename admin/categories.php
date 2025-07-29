<?php include '../includes/auth.php'; ?>
<?php include '../includes/admin_header.php';
require_once '../config/db.php';

// --- FETCH CATEGORIES ---
$where = [];
$params = [];
if (!empty($_GET['search'])) {
    $where[] = 'c.name LIKE ?';
    $params[] = '%' . $_GET['search'] . '%';
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT c.*, p.name as parent_name FROM categories c LEFT JOIN categories p ON c.parent_id = p.id $where_sql ORDER BY c.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$categories = $stmt->fetchAll();
?>
<main class="p-8">
  <div id="category-action-msg" class="hidden bg-green-100 text-green-700 px-4 py-2 rounded mb-4"></div>
  <div class="bg-blue-50 border border-blue-200 text-blue-900 px-4 py-2 rounded mb-4 flex items-center gap-2">
    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/></svg>
    <span>Highlight: <span class="font-semibold">Root categories</span> (no parent) in <span class="bg-blue-100 px-2 py-1 rounded">blue</span>, <span class="font-semibold">subcategories</span> in <span class="bg-yellow-100 px-2 py-1 rounded">yellow</span>.</span>
  </div>
  <h1 class="text-2xl font-bold mb-6 text-gray-800">Category Management</h1>
  <div class="bg-white rounded shadow p-6 mb-8">
    <form class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4" method="get" action="">
      <input type="text" name="search" placeholder="Search categories..." class="border rounded px-3 py-2 focus:outline-none w-48" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
      <div class="flex gap-2">
        <a href="#" onclick="document.getElementById('add-category-modal').classList.remove('hidden');return false;" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">+ Add Category</a>
      </div>
    </form>
    <div class="overflow-x-auto rounded-xl shadow-lg bg-white">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gradient-to-r from-blue-100 to-indigo-100">
          <tr>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Name</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Parent</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody id="categories-table-body" class="divide-y divide-gray-100">
          <!-- Populated by JS -->
        </tbody>
      </table>
    </div>
  </div>
  <!-- Add Category Modal -->
  <div id="add-category-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
      <button onclick="document.getElementById('add-category-modal').classList.add('hidden')" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
      <h2 class="text-xl font-semibold mb-4">Add Category</h2>
      <form class="flex flex-col gap-4">
        <input type="hidden" name="add_category" value="1">
        <div>
          <label class="block text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
          <input type="text" name="name" required class="border rounded px-3 py-2 w-full focus:outline-none focus:ring">
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Parent Category</label>
          <select name="parent_id" class="border rounded px-3 py-2 w-full focus:outline-none">
            <option value="">None</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="flex justify-end gap-2">
          <button type="button" onclick="document.getElementById('add-category-modal').classList.add('hidden')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Category</button>
        </div>
      </form>
    </div>
  </div>
  <!-- Edit Category Modal (JS populates fields) -->
  <div id="edit-category-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
      <button onclick="document.getElementById('edit-category-modal').classList.add('hidden')" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
      <h2 class="text-xl font-semibold mb-4">Edit Category</h2>
      <form class="flex flex-col gap-4">
        <input type="hidden" name="edit_category" value="1">
        <input type="hidden" name="category_id" id="edit-category-id">
        <div>
          <label class="block text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
          <input type="text" name="name" id="edit-category-name" required class="border rounded px-3 py-2 w-full focus:outline-none focus:ring">
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Parent Category</label>
          <select name="parent_id" id="edit-category-parent" class="border rounded px-3 py-2 w-full focus:outline-none">
            <option value="">None</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="flex justify-end gap-2">
          <button type="button" onclick="document.getElementById('edit-category-modal').classList.add('hidden')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Cancel</button></div>
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</main>

<script>
function renderCategories(categories) {
  const tbody = document.getElementById('categories-table-body');
  tbody.innerHTML = '';
  if (!categories.length) {
    tbody.innerHTML = `<tr><td colspan="3" class="text-center text-gray-400 py-4">No categories found.</td></tr>`;
    return;
  }
  categories.forEach(cat => {
    let rowColor = '';
    // Example: highlight root categories (no parent) in blue, subcategories in yellow
    if (!cat.parent_id) {
      rowColor = 'bg-blue-50'; // Root category
    } else {
      rowColor = 'bg-yellow-50'; // Subcategory
    }
    tbody.innerHTML += `
      <tr class="border-b ${rowColor} hover:bg-blue-50 transition">
        <td class="py-3 px-4 font-medium text-gray-900">${escapeHtml(cat.name)}</td>
        <td class="py-3 px-4">${escapeHtml(cat.parent_name || '-') }</td>
        <td class="py-3 px-4 flex gap-2">
          <button onclick="openEditCategoryModal(${cat.id}, '${escapeHtml(cat.name)}', ${cat.parent_id || 'null'})" class="text-blue-600 hover:underline bg-transparent border-none p-0 m-0">Edit</button>
          <button onclick="deleteCategory(${cat.id})" class="text-red-500 hover:underline bg-transparent border-none p-0 m-0">Delete</button>
        </td>
      </tr>
    `;
  });
}
function escapeHtml(text) {
  return String(text).replace(/[&<>'"]/g, function(c) {
    return {'&':'&amp;','<':'&lt;','>':'&gt;','\'':'&#39;','"':'&quot;'}[c];
  });
}
function fetchCategories() {
  const search = document.querySelector('input[name=search]').value;
  fetch(`/SoleHub/api/manage_categories.php?search=${encodeURIComponent(search)}`)
    .then(res => res.json())
    .then(data => {
      renderCategories(data.categories);
      updateCategoryDropdowns(data.categories);
    });
}
function showCategoryMsg(msg, error) {
  const box = document.getElementById('category-action-msg');
  box.textContent = error ? error : msg;
  box.classList.remove('hidden', 'bg-green-100', 'text-green-700', 'bg-red-100', 'text-red-700');
  if (error) {
    box.classList.add('bg-red-100', 'text-red-700');
  } else {
    box.classList.add('bg-green-100', 'text-green-700');
  }
  box.scrollIntoView({behavior: 'smooth', block: 'start'});
  setTimeout(()=>box.classList.add('hidden'), 2500);
}
document.querySelector('form[method=get]').onsubmit = function(e) {
  e.preventDefault();
  fetchCategories();
};
document.addEventListener('DOMContentLoaded', function() {
  fetchCategories();
});
// Add category
const addCategoryForm = document.querySelector('#add-category-modal form');
addCategoryForm.onsubmit = function(e) {
  e.preventDefault();
  const formData = new FormData(addCategoryForm);
  formData.append('action', 'add');
  fetch('/SoleHub/api/manage_categories.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    showCategoryMsg(data.message, data.error);
    if (!data.error) {
      document.getElementById('add-category-modal').classList.add('hidden');
      addCategoryForm.reset();
      fetchCategories();
    }
  });
};
// Edit category
const editCategoryForm = document.querySelector('#edit-category-modal form');
editCategoryForm.onsubmit = function(e) {
  e.preventDefault();
  const formData = new FormData(editCategoryForm);
  formData.append('action', 'edit');
  fetch('/SoleHub/api/manage_categories.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    showCategoryMsg(data.message, data.error);
    if (!data.error) {
      document.getElementById('edit-category-modal').classList.add('hidden');
      fetchCategories();
    }
  });
};
function deleteCategory(id) {
  if (!confirm('Delete this category?')) return;
  const formData = new FormData();
  formData.append('action', 'delete');
  formData.append('category_id', id);
  fetch('/SoleHub/api/manage_categories.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    showCategoryMsg(data.message, data.error);
    fetchCategories();
  });
}
function updateCategoryDropdowns(categories) {
  // Update parent dropdowns in add/edit modals
  const addParent = document.querySelector('#add-category-modal select[name=parent_id]');
  const editParent = document.querySelector('#edit-category-modal select[name=parent_id]');
  if (addParent) {
    addParent.innerHTML = '<option value="">None</option>' + categories.map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');
  }
  if (editParent) {
    editParent.innerHTML = '<option value="">None</option>' + categories.map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');
  }
}
function openEditCategoryModal(id, name, parentId) {
  document.getElementById('edit-category-id').value = id;
  document.getElementById('edit-category-name').value = name;
  document.getElementById('edit-category-parent').value = parentId || '';
  document.getElementById('edit-category-modal').classList.remove('hidden');
}
  </script>
