<?php
include __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: /SoleHub/login.php');
    exit;
}
// Fetch user info
$stmt = $pdo->prepare('SELECT name, email, phone, address FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$user_name = $user['name'] ?? '';
$user_email = $user['email'] ?? '';
$user_phone = $user['phone'] ?? '';
$user_address = $user['address'] ?? '';
$profile_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['name'] ?? '');
    $new_phone = trim($_POST['phone'] ?? '');
    $new_address = trim($_POST['address'] ?? '');
    $update = false;
    if ($new_name !== '' && $new_name !== $user_name) {
        $user_name = $new_name;
        $update = true;
    }
    if ($new_phone !== $user_phone) {
        $user_phone = $new_phone;
        $update = true;
    }
    if ($new_address !== $user_address) {
        $user_address = $new_address;
        $update = true;
    }
    if ($update) {
        $stmt = $pdo->prepare('UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?');
        if ($stmt->execute([$user_name, $user_phone, $user_address, $user_id])) {
            $_SESSION['user_name'] = $user_name;
            $profile_msg = 'Profile updated successfully!';
        } else {
            $profile_msg = 'Failed to update profile.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<main class="flex-1 flex items-center justify-center bg-gray-50 min-h-screen">
  <div class="bg-white rounded shadow p-8 w-full max-w-lg mt-8 mb-8">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">My Profile</h1>
    <?php if ($profile_msg): ?>
      <div class="mb-4 px-4 py-2 rounded <?php echo strpos($profile_msg, 'success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>"><?php echo htmlspecialchars($profile_msg); ?></div>
    <?php endif; ?>
    <form method="post" action="profile.php" class="space-y-4">
      <div>
        <label class="block text-gray-700 mb-1">Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user_name) ?>" class="border rounded px-3 py-2 w-full focus:outline-none" required>
      </div>
      <div>
        <label class="block text-gray-700 mb-1">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user_email) ?>" class="border rounded px-3 py-2 w-full focus:outline-none bg-gray-100" required disabled>
      </div>
      <div>
        <label class="block text-gray-700 mb-1">Phone</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($user_phone) ?>" class="border rounded px-3 py-2 w-full focus:outline-none" placeholder="e.g. 0123456789" required>
      </div>
      <div>
        <label class="block text-gray-700 mb-1">Address</label>
        <textarea name="address" class="border rounded px-3 py-2 w-full focus:outline-none" rows="2" placeholder="Your shipping address" required><?= htmlspecialchars($user_address) ?></textarea>
      </div>
      <div class="flex justify-end gap-2">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Changes</button>
      </div>
    </form>
  </div>
</main>
<?php include 'includes/footer.php'; ?>
