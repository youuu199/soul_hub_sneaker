<?php include 'includes/auth.php'; ?><?php include 'includes/header.php'; ?>
<?php
$user_name = '';
$user_email = '';
if (isset($user_id) && $user_id) {
    $stmt = $pdo->prepare('SELECT name, email FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    if ($row) {
        $user_name = htmlspecialchars($row['name']);
        $user_email = htmlspecialchars($row['email']);
    }
}
?>
<main class="flex-1 bg-gradient-to-br from-blue-50 to-white min-h-screen">
  <section class="container mx-auto px-4 py-16 md:py-24 flex flex-col md:flex-row items-center gap-12 md:gap-20">
    <div class="flex-1 space-y-6 animate-fade-in-up">
      <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 mb-6 leading-tight tracking-tight">
        Contact <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-indigo-600">SoleHub</span>
      </h1>
      <p class="text-lg md:text-xl text-gray-600 mb-8 max-w-2xl">
        Have a question, need help, or want to give feedback? Our team is here for you! Reach out using the form below or through our contact details.
      </p>
      <ul class="list-disc pl-6 text-gray-700 space-y-2">
        <li>Email: <a href="mailto:support@solehub.com" class="text-blue-600 hover:underline">support@solehub.com</a></li>
        <li>Phone: <a href="tel:+1234567890" class="text-blue-600 hover:underline">+1 (234) 567-890</a></li>
        <li>Instagram: <a href="https://instagram.com/solehub" class="text-blue-600 hover:underline" target="_blank">@solehub</a></li>
      </ul>
    </div>
    <div class="flex-1 flex justify-center items-center">
      <form id="contactForm" class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md space-y-6" method="post" action="/SoleHub/api/contact.php">
        <div id="contactMsg" class="hidden mb-4"></div>
        <div>
          <label class="block text-gray-700 font-semibold mb-2" for="name">Name</label>
          <input type="text" id="name" name="name" required class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo $user_name; ?>" <?php if($user_name) echo 'readonly'; ?>>
        </div>
        <div>
          <label class="block text-gray-700 font-semibold mb-2" for="email">Email</label>
          <input type="email" id="email" name="email" required class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo $user_email; ?>" <?php if($user_email) echo 'readonly'; ?>>
        </div>
        <div>
          <label class="block text-gray-700 font-semibold mb-2" for="status">Status (optional)</label>
          <select id="status" name="status" class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Select status (if related)</option>
            <option value="review">Review</option>
            <option value="feedback">Feedback</option>
            <option value="warning">Warning</option>
            <option value="banned">Ban</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div>
          <label class="block text-gray-700 font-semibold mb-2" for="message">Message</label>
          <textarea id="message" name="message" rows="4" required class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        <button type="submit" class="bg-blue-600 text-white font-semibold px-6 py-3 rounded-lg shadow hover:bg-blue-700 transition w-full">Send Message</button>
      </form>
    </div>
  </section>
  <section class="container mx-auto px-4 py-12 text-center">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Our Office</h2>
    <p class="text-gray-700 text-lg mb-2">123 Sneaker St, Suite 100, New York, NY 10001, USA</p>
    <iframe class="w-full max-w-2xl h-64 mx-auto rounded-lg shadow" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3023.858019858978!2d-74.0059416845936!3d40.71277597933109!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzDCsDQyJzQ2LjAiTiA3NMKwMDAnMjAuMCJX!5e0!3m2!1sen!2sus!4v1620000000000!5m2!1sen!2sus" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
  </section>
</main>
<script>
// Contact form AJAX submit
const contactForm = document.getElementById('contactForm');
const contactMsg = document.getElementById('contactMsg');
if (contactForm) {
  contactForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    contactMsg.classList.add('hidden');
    contactMsg.textContent = '';
    const formData = new FormData(contactForm);
    try {
      const res = await fetch('/SoleHub/api/contact.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      if (data.success) {
        contactMsg.className = 'mb-4 p-3 rounded bg-green-100 text-green-800 text-center';
        contactMsg.textContent = data.message || 'Message sent successfully!';
        contactMsg.classList.remove('hidden');
        contactForm.reset();
      } else {
        contactMsg.className = 'mb-4 p-3 rounded bg-red-100 text-red-800 text-center';
        contactMsg.textContent = data.message || 'Failed to send message.';
        contactMsg.classList.remove('hidden');
      }
    } catch (err) {
      contactMsg.className = 'mb-4 p-3 rounded bg-red-100 text-red-800 text-center';
      contactMsg.textContent = 'An error occurred. Please try again.';
      contactMsg.classList.remove('hidden');
    }
    setTimeout(() => contactMsg.classList.add('hidden'), 4000);
  });
}
</script>
<?php include 'includes/footer.php'; ?>
