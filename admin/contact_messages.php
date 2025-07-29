<?php
// Admin page to display contact messages
include '../includes/auth.php';
if (!in_array($user_role, ['admin', 'superadmin'])) {
    header('Location: /SoleHub/index.php');
    exit;
}
include '../includes/admin_header.php';

$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];
if ($search !== '') {
    $where = "WHERE cm.name LIKE ? OR cm.email LIKE ? OR cm.status LIKE ? OR cm.message LIKE ?";
    $params = array_fill(0, 4, "%$search%");
}
$sql = "SELECT cm.*, u.name AS user_name FROM contact_messages cm LEFT JOIN users u ON cm.user_id = u.id $where ORDER BY cm.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();
?>
<main class="p-8 bg-gray-50 min-h-screen">
  <h1 class="text-3xl font-bold mb-8">Contact Messages</h1>
  <form method="get" class="mb-6 flex flex-wrap gap-2 items-center">
    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name, email, status, or message..." class="border rounded px-4 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Search</button>
    <?php if ($search !== ''): ?>
      <a href="contact_messages.php" class="ml-2 text-blue-600 hover:underline">Clear</a>
    <?php endif; ?>
  </form>
  <div class="overflow-x-auto rounded-xl shadow-lg bg-white">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gradient-to-r from-blue-100 to-indigo-100">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">ID</th>
          <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">User</th>
          <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Name</th>
          <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Email</th>
          <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Content</th>
          <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Message</th>
          <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($messages as $msg): ?>
        <tr class="hover:bg-blue-50 transition">
          <td class="px-6 py-4 text-center text-sm text-gray-700 font-semibold"><?php echo $msg['id']; ?></td>
          <td class="px-6 py-4 text-sm"><?php echo $msg['user_name'] ? '<span class=\'font-medium text-blue-700\'>' . htmlspecialchars($msg['user_name']) . '</span>' : '<span class=\'text-gray-400 italic\'>Guest</span>'; ?></td>
          <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($msg['name']); ?></td>
          <td class="px-6 py-4 text-sm"><a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($msg['email']); ?></a></td>
          <td class="px-6 py-4 text-sm">
            <span class="inline-block px-2 py-1 rounded text-xs font-semibold
              <?php
                switch ($msg['status']) {
                  case 'review': echo 'bg-yellow-100 text-yellow-800'; break;
                  case 'feedback': echo 'bg-green-100 text-green-800'; break;
                  case 'warning': echo 'bg-orange-100 text-orange-800'; break;
                  case 'banned': echo 'bg-red-100 text-red-800'; break;
                  case 'other': echo 'bg-gray-200 text-gray-700'; break;
                  default: echo 'bg-gray-100 text-gray-400';
                }
              ?>"><?php echo htmlspecialchars($msg['status'] ?: '—'); ?></span>
          </td>
          <td class="px-6 py-4 text-sm max-w-xs break-words whitespace-pre-line"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></td>
          <td class="px-6 py-4 text-xs text-gray-500"><?php echo date('Y-m-d H:i', strtotime($msg['created_at'])); ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($messages)): ?>
        <tr><td colspan="7" class="text-center py-8 text-gray-400">No contact messages found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include '../includes/admin_footer.php'; ?>
