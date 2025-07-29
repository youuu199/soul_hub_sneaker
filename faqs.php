<?php
include 'includes/header.php';
?>
<div class="max-w-2xl mx-auto px-4 py-12">
  <h1 class="text-3xl font-bold mb-8 text-center">Frequently Asked Questions</h1>
  <div class="space-y-6">
    <!-- FAQ 1 -->
    <div class="bg-white rounded-lg shadow p-6">
      <button class="w-full text-left flex justify-between items-center font-semibold text-lg focus:outline-none faq-toggle">
        How do I place an order?
        <svg class="w-5 h-5 ml-2 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="faq-answer mt-2 text-gray-600 hidden">
        Browse our shop, select your preferred sneakers, choose size and color, add to cart, and proceed to checkout. You’ll need to log in or create an account to complete your order.
      </div>
    </div>
    <!-- FAQ 2 -->
    <div class="bg-white rounded-lg shadow p-6">
      <button class="w-full text-left flex justify-between items-center font-semibold text-lg focus:outline-none faq-toggle">
        What payment methods do you accept?
        <svg class="w-5 h-5 ml-2 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="faq-answer mt-2 text-gray-600 hidden">
        We accept all major credit cards, PayPal, and secure online payments. All transactions are encrypted for your safety.
      </div>
    </div>
    <!-- FAQ 3 -->
    <div class="bg-white rounded-lg shadow p-6">
      <button class="w-full text-left flex justify-between items-center font-semibold text-lg focus:outline-none faq-toggle">
        How can I track my order?
        <svg class="w-5 h-5 ml-2 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="faq-answer mt-2 text-gray-600 hidden">
        After your order is shipped, you’ll receive a tracking link via email and in your order details page.
      </div>
    </div>
    <!-- FAQ 4 -->
    <div class="bg-white rounded-lg shadow p-6">
      <button class="w-full text-left flex justify-between items-center font-semibold text-lg focus:outline-none faq-toggle">
        How do I contact customer support?
        <svg class="w-5 h-5 ml-2 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="faq-answer mt-2 text-gray-600 hidden">
        You can reach us via the Contact page or email us at <a href="mailto:support@solehub.com" class="text-blue-600 underline">support@solehub.com</a>. We’re here to help!
      </div>
    </div>
  </div>
</div>
<script>
  document.querySelectorAll('.faq-toggle').forEach(btn => {
    btn.addEventListener('click', function() {
      const answer = this.parentElement.querySelector('.faq-answer');
      const icon = this.querySelector('svg');
      if (answer.classList.contains('hidden')) {
        answer.classList.remove('hidden');
        icon.classList.add('rotate-180');
      } else {
        answer.classList.add('hidden');
        icon.classList.remove('rotate-180');
      }
    });
  });
</script>
<?php include 'includes/footer.php'; ?>
