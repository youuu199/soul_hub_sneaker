<?php include '../includes/auth.php';
include '../includes/admin_header.php';
require_once '../config/db.php';

// --- PAGINATION & FILTERS ---
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;
$where = [];
$params = [];
if (!empty($_GET['search'])) {
    $where[] = '(name LIKE ? OR email LIKE ?)';
    $params[] = '%' . $_GET['search'] . '%';
    $params[] = '%' . $_GET['search'] . '%';
}
if (!empty($_GET['status'])) {
    $where[] = 'status = ?';
    $params[] = $_GET['status'];
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// --- TOTAL COUNT FOR PAGINATION ---
$count_sql = "SELECT COUNT(*) FROM users $where_sql";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_users = (int)$count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_users / $per_page));

// --- FETCH USERS ---
$sql = "SELECT * FROM users $where_sql ORDER BY id DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// --- HANDLE USER ACTIONS (Suspend, Ban, Role Change, Delete) ---
$user_action_msg = '';
$current_admin_id = $_SESSION['user_id'] ?? null;
$current_admin_role = $_SESSION['user_role'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
    if ($uid && $uid == $current_admin_id && $current_admin_role !== 'superadmin') {
        $user_action_msg = 'You cannot perform actions on your own account.';
    } else {
        // Get target user's role
        $target_role = null;
        if ($uid) {
            $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
            $stmt->execute([$uid]);
            $target_role = $stmt->fetchColumn();
        }
        // Only superadmin can do anything
        if ($current_admin_role === 'superadmin') {
            if (isset($_POST['suspend_user'])) {
                $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
                if ($stmt->execute(['suspended', $uid])) $user_action_msg = 'User suspended.';
            } elseif (isset($_POST['ban_user'])) {
                $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
                if ($stmt->execute(['banned', $uid])) $user_action_msg = 'User banned.';
            } elseif (isset($_POST['activate_user'])) {
                $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
                if ($stmt->execute(['active', $uid])) $user_action_msg = 'User activated.';
            } elseif (isset($_POST['make_admin'])) {
                $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
                if ($stmt->execute(['admin', $uid])) $user_action_msg = 'User promoted to admin.';
            } elseif (isset($_POST['make_user'])) {
                $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
                if ($stmt->execute(['user', $uid])) $user_action_msg = 'User demoted to user.';
            } elseif (isset($_POST['make_superadmin'])) {
                $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
                if ($stmt->execute(['superadmin', $uid])) $user_action_msg = 'User promoted to superadmin.';
            } elseif (isset($_POST['delete_user'])) {
                $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
                if ($stmt->execute([$uid])) $user_action_msg = 'User deleted.';
            }
        } else if ($current_admin_role === 'admin') {
            // Admin cannot change other admins or superadmins, and cannot promote to superadmin
            if ($target_role === 'admin' || $target_role === 'superadmin') {
                $user_action_msg = 'You cannot modify other admins or superadmins.';
            } elseif (isset($_POST['make_admin']) || isset($_POST['make_superadmin'])) {
                $user_action_msg = 'You cannot promote users to admin or superadmin.';
            } else {
                if (isset($_POST['suspend_user'])) {
                    $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
                    if ($stmt->execute(['suspended', $uid])) $user_action_msg = 'User suspended.';
                } elseif (isset($_POST['ban_user'])) {
                    $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
                    if ($stmt->execute(['banned', $uid])) $user_action_msg = 'User banned.';
                } elseif (isset($_POST['activate_user'])) {
                    $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
                    if ($stmt->execute(['active', $uid])) $user_action_msg = 'User activated.';
                } elseif (isset($_POST['make_user'])) {
                    $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
                    if ($stmt->execute(['user', $uid])) $user_action_msg = 'User demoted to user.';
                } elseif (isset($_POST['delete_user'])) {
                    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
                    if ($stmt->execute([$uid])) $user_action_msg = 'User deleted.';
                }
            }
        }
    }
    // Refresh to avoid resubmission
    header('Location: manage_users.php?msg=' . urlencode($user_action_msg));
    exit;
}
if (isset($_GET['msg'])) {
    $user_action_msg = $_GET['msg'];
}
?>
<main class="p-8">
  <h1 class="text-2xl font-bold mb-6 text-gray-800">User Management</h1>
  <?php if ($user_action_msg): ?>
    <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4"><?= htmlspecialchars($user_action_msg) ?></div>
  <?php endif; ?>
  <div class="bg-white rounded shadow p-6 mb-8">
    <form class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4" method="get" action="">
      <div class="flex flex-col md:flex-row gap-2">
        <input type="text" name="search" placeholder="Search by name or email..." class="border rounded px-3 py-2 focus:outline-none w-48">
        <select name="status" class="border rounded px-3 py-2 focus:outline-none">
          <option value="">All Statuses</option>
          <option value="active">Active</option>
          <option value="banned">Banned</option>
          <option value="suspended">Suspended</option>
        </select>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Filter</button>
      </div>
    </form>
    <div class="overflow-x-auto rounded-xl shadow-lg bg-white">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gradient-to-r from-blue-100 to-indigo-100">
          <tr>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Name</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Email</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Role</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Registered</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody id="users-table-body" class="divide-y divide-gray-100">
          <!-- Populated by JS -->
        </tbody>
      </table>
    </div>
    <div class="flex justify-end mt-4">
      <nav id="users-pagination" class="inline-flex rounded-md shadow-sm"></nav>
    </div>
  </div>
  <div id="user-action-msg" class="hidden bg-green-100 text-green-700 px-4 py-2 rounded mb-4"></div>
  <!-- User Details Modal (optional, not implemented in backend) -->
  <!-- ...existing code... -->
</main>
<script>
function renderUsers(users) {
  const tbody = document.querySelector('#users-table-body');
  tbody.innerHTML = '';
  // Get current admin id and role from a JS variable injected by PHP
  const currentAdminId = window.currentAdminId;
  const currentAdminRole = window.currentAdminRole;
  if (!users.length) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-gray-400 py-4">No users found.</td></tr>`;
    return;
  }
  users.forEach(user => {
    let statusHtml = '';
    if (user.status === 'active') statusHtml = '<span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Active</span>';
    else if (user.status === 'banned') statusHtml = '<span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Banned</span>';
    else statusHtml = '<span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">Suspended</span>';
    let actions = '';
    // Disable actions for superadmin on self
    const isSelf = user.id == currentAdminId;
    const isSuperadmin = user.role === 'superadmin';
    if (isSuperadmin && isSelf) {
      actions = '<span class="text-gray-400 italic">No actions</span>';
    } else {
      if (user.status === 'active') {
        actions += `<button onclick="userAction(${user.id},'suspend')" class="text-yellow-600 hover:underline bg-transparent border-none p-0 m-0" ${isSuperadmin ? 'disabled style=\'opacity:.5;pointer-events:none\'' : ''}>Suspend</button> `;
        actions += `<button onclick="userAction(${user.id},'ban')" class="text-red-500 hover:underline bg-transparent border-none p-0 m-0" ${isSuperadmin ? 'disabled style=\'opacity:.5;pointer-events:none\'' : ''}>Ban</button> `;
      } else if (user.status === 'suspended') {
        actions += `<button onclick="userAction(${user.id},'activate')" class="text-green-600 hover:underline bg-transparent border-none p-0 m-0" ${isSuperadmin ? 'disabled style=\'opacity:.5;pointer-events:none\'' : ''}>Activate</button> `;
        actions += `<button onclick="userAction(${user.id},'ban')" class="text-red-500 hover:underline bg-transparent border-none p-0 m-0" ${isSuperadmin ? 'disabled style=\'opacity:.5;pointer-events:none\'' : ''}>Ban</button> `;
      } else if (user.status === 'banned') {
        actions += `<button onclick="userAction(${user.id},'activate')" class="text-green-600 hover:underline bg-transparent border-none p-0 m-0" ${isSuperadmin ? 'disabled style=\'opacity:.5;pointer-events:none\'' : ''}>Activate</button> `;
      }
      if (user.role === 'user') {
        actions += `<button onclick="userAction(${user.id},'make_admin')" class="text-indigo-600 hover:underline bg-transparent border-none p-0 m-0" ${isSuperadmin ? 'disabled style=\'opacity:.5;pointer-events:none\'' : ''}>Make Admin</button> `;
      } else if (user.role === 'admin') {
        actions += `<button onclick="userAction(${user.id},'make_user')" class="text-gray-600 hover:underline bg-transparent border-none p-0 m-0" ${isSuperadmin ? 'disabled style=\'opacity:.5;pointer-events:none\'' : ''}>Make User</button> `;
      }
      actions += `<button onclick="if(confirm('Delete this user?'))userAction(${user.id},'delete')" class="text-gray-400 hover:text-red-600 hover:underline bg-transparent border-none p-0 m-0" ${isSuperadmin ? 'disabled style=\'opacity:.5;pointer-events:none\'' : ''}>Delete</button>`;
    }
    tbody.innerHTML += `
      <tr class="border-b hover:bg-blue-50 transition">
        <td class="py-3 px-4 font-medium text-gray-900">${escapeHtml(user.name)}</td>
        <td class="py-3 px-4">${escapeHtml(user.email)}</td>
        <td class="py-3 px-4">${escapeHtml(user.role)}</td>
        <td class="py-3 px-4">${statusHtml}</td>
        <td class="py-3 px-4">${user.created_at ? user.created_at.substr(0,10) : ''}</td>
        <td class="py-3 px-4 flex gap-2">${actions}</td>
      </tr>
    `;
  });
}
function escapeHtml(text) {
  return text.replace(/[&<>'"]/g, function(c) {
    return {'&':'&amp;','<':'&lt;','>':'&gt;','\'':'&#39;','"':'&quot;'}[c];
  });
}
function fetchUsers(page=1) {
  const search = document.querySelector('input[name=search]').value;
  const status = document.querySelector('select[name=status]').value;
  fetch(`/SoleHub/api/manage_users.php?search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}&page=${page}`)
    .then(res => res.json())
    .then(data => {
      renderUsers(data.users || []);
      renderPagination(data.page || 1, data.total_pages || 1);
    })
    .catch(() => {
      renderUsers([]);
      renderPagination(1, 1);
    });
}
function userAction(user_id, action) {
  // Map action to backend POST keys for legacy PHP fallback
  let postData = {};
  if (['suspend','ban','activate','make_admin','make_user','delete'].includes(action)) {
    postData.user_id = user_id;
    postData.action = action;
  }
  fetch('/SoleHub/api/manage_users.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(postData)
  })
  .then(res => res.json())
  .then(data => {
    showUserMsg(data.message || 'Action completed.');
    fetchUsers();
  });
}
function renderPagination(page, total) {
  const nav = document.getElementById('users-pagination');
  let html = '';
  for (let i = 1; i <= total; i++) {
    const active = i === page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50';
    html += `<a href="#" onclick="fetchUsers(${i});return false;" class="px-3 py-1 border border-gray-300 ${active}">${i}</a>`;
  }
  nav.innerHTML = html;
}
function showUserMsg(msg) {
  const box = document.getElementById('user-action-msg');
  box.textContent = msg;
  box.classList.remove('hidden');
  setTimeout(()=>box.classList.add('hidden'), 2500);
}
document.querySelector('form[method=get]').onsubmit = function(e) {
  e.preventDefault();
  fetchUsers();
};
document.addEventListener('DOMContentLoaded', function() {
  fetchUsers();
});
</script>
<?php include '../includes/admin_footer.php'; ?>
