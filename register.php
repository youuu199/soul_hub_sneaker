<?php include 'includes/header.php'; ?>
<main class="flex items-center justify-center min-h-screen bg-gray-50">
  <form id="register-form" class="bg-white shadow rounded-lg p-8 w-full max-w-md space-y-6">
    <h2 class="text-2xl font-bold text-gray-800 text-center">Create Account</h2>
    <div>
      <label class="block text-gray-700 mb-1">Name</label>
      <input type="text" name="name" required class="border rounded px-3 py-2 w-full focus:outline-none focus:ring" />
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Email</label>
      <input type="email" name="email" required class="border rounded px-3 py-2 w-full focus:outline-none focus:ring" />
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Password</label>
      <input type="password" name="password" id="password" required minlength="6" class="border rounded px-3 py-2 w-full focus:outline-none focus:ring" />
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Re-enter Password</label>
      <input type="password" name="password2" id="password2" required minlength="6" class="border rounded px-3 py-2 w-full focus:outline-none focus:ring" />
    </div>
    <div id="register-msg" class="hidden text-center px-4 py-2 rounded"></div>
    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-semibold">Register</button>
    <div class="text-center text-sm mt-2">
      Already have an account? <a href="login.php" class="text-blue-600 hover:underline">Login</a>
    </div>
  </form>
</main>
<script>
function showNotification(msg) {
  if (window.Notification && Notification.permission === "granted") {
    new Notification(msg);
  } else if (window.Notification && Notification.permission !== "denied") {
    Notification.requestPermission().then(permission => {
      if (permission === "granted") new Notification(msg);
    });
  }
}
document.getElementById('register-form').onsubmit = function(e) {
  e.preventDefault();
  const form = e.target;
  const msg = document.getElementById('register-msg');
  msg.classList.add('hidden');
  msg.textContent = '';
  msg.classList.remove('bg-green-100', 'text-green-700', 'bg-red-100', 'text-red-700');

  // Client-side validation for re-enter password
  if (form.password.value !== form.password2.value) {
    msg.textContent = "Passwords do not match.";
    msg.classList.remove('hidden');
    msg.classList.add('bg-red-100', 'text-red-700');
    showNotification('Passwords do not match.');
    return;
  }

  const data = {
    action: 'register',
    name: form.name.value,
    email: form.email.value,
    password: form.password.value,
    password2: form.password2.value
  };
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
      showNotification(res.message || 'Registration successful!');
      setTimeout(() => window.location = 'login.php', 1500);
    } else {
      msg.textContent = (res.errors || ['Registration failed.']).join(' ');
      msg.classList.add('bg-red-100', 'text-red-700');
      showNotification(msg.textContent);
    }
  });
};
</script>
<?php include 'includes/footer.php'; ?>
