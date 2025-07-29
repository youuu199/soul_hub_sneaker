<?php include 'includes/header.php'; ?>
<main class="flex items-center justify-center min-h-screen bg-gray-50">
  <form id="login-form" class="bg-white shadow rounded-lg p-8 w-full max-w-md space-y-6">
    <h2 class="text-2xl font-bold text-gray-800 text-center">Sign In</h2>
    <div>
      <label class="block text-gray-700 mb-1">Email</label>
      <input type="email" name="email" required class="border rounded px-3 py-2 w-full focus:outline-none focus:ring" />
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Password</label>
      <input type="password" name="password" required class="border rounded px-3 py-2 w-full focus:outline-none focus:ring" />
    </div>
    <div id="login-msg" class="hidden text-center px-4 py-2 rounded"></div>
    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-semibold">Login</button>
    <div class="text-center text-sm mt-2">
      Don't have an account? <a href="register.php" class="text-blue-600 hover:underline">Register</a>
    </div>
  </form>
</main>
<script>
if (window.location.search.includes('login_required=1')) {
  document.getElementById('login-msg').textContent = 'Please log in to continue.';
  document.getElementById('login-msg').classList.remove('hidden', 'bg-green-100', 'text-green-700');
  document.getElementById('login-msg').classList.add('bg-red-100', 'text-red-700');
}
document.getElementById('login-form').onsubmit = function(e) {
  e.preventDefault();
  const form = e.target;
  const data = {
    action: 'login',
    email: form.email.value,
    password: form.password.value
  };
  const msg = document.getElementById('login-msg');
  msg.classList.add('hidden');
  msg.textContent = '';
  msg.classList.remove('bg-green-100', 'text-green-700', 'bg-red-100', 'text-red-700');
  fetch('/SoleHub/api/auth.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(data)
  })
  .then(res => res.json())
  .then(res => {
    msg.classList.remove('hidden', 'bg-green-100', 'text-green-700', 'bg-red-100', 'text-red-700');
    if (res.success) {
      msg.textContent = res.message;
      msg.classList.add('bg-green-100', 'text-green-700');
      let user = null;
      if (res.user) {
        try {
          user = JSON.parse(atob(res.user));
        } catch (e) { user = null; }
      }
      if (user && (user.role === 'superadmin' || user.role === 'admin')) {
        setTimeout(() => window.location = '/SoleHub/admin/dashboard.php', 1200);
      } else {
        setTimeout(() => window.location = 'index.php', 1200);
      }
    } else {
      msg.textContent = (res.errors || ['Login failed.']).join(' ');
      msg.classList.add('bg-red-100', 'text-red-700');
    }
  });
};
</script>
<?php include 'includes/footer.php'; ?>
