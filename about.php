<?php include 'includes/header.php'; ?>
<main class="flex-1 bg-gradient-to-br from-blue-50 to-white min-h-screen">
  <section class="container mx-auto px-4 py-16 md:py-24 flex flex-col md:flex-row items-center gap-12 md:gap-20">
    <div class="flex-1 space-y-6 animate-fade-in-up">
      <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 mb-6 leading-tight tracking-tight">
        About <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-indigo-600">SoleHub</span>
      </h1>
      <p class="text-lg md:text-xl text-gray-600 mb-8 max-w-2xl">
        SoleHub was founded by sneaker enthusiasts for sneaker enthusiasts. Our mission is to connect the global sneaker community with the most exclusive, authentic, and exciting footwear releases. We believe sneakers are more than just shoes—they're a culture, a passion, and a statement.
      </p>
      <ul class="list-disc pl-6 text-gray-700 space-y-2">
        <li>Curated selection of the latest and rarest sneakers from top brands</li>
        <li>100% authenticity guarantee on every pair</li>
        <li>Fast, secure shipping worldwide</li>
        <li>Dedicated customer support and a thriving community</li>
      </ul>
    </div>
    <div class="flex-1 flex justify-center items-center">
      <img src="/SoleHub/assets/img/sustainable-sneakers-MAIN-2023.webp" alt="About SoleHub" class="w-full max-w-md rounded-xl shadow-lg" onerror="this.style.display='none'">
    </div>
  </section>
  <section class="container mx-auto px-4 py-12">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Our Story</h2>
    <div class="max-w-3xl mx-auto text-gray-700 text-lg leading-relaxed">
      <p>
        What started as a small group of collectors and sneakerheads has grown into a trusted platform for thousands of customers worldwide. We partner directly with brands, boutiques, and verified resellers to bring you the best selection and peace of mind with every purchase.
      </p>
      <p class="mt-4">
        Our team is passionate about sneakers and committed to delivering a seamless shopping experience. Whether you're hunting for a grail, building your collection, or just looking for your next favorite pair, SoleHub is here for you.
      </p>
    </div>
  </section>
  <section class="container mx-auto px-4 py-12">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Meet the Team</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 justify-center">
      <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center text-center">
        <img src="/SoleHub/assets/img/team1.jpg" alt="Founder" class="w-24 h-24 rounded-full mb-4 object-cover" onerror="this.style.display='none'">
        <h3 class="font-semibold text-lg mb-1">Alex Kim</h3>
        <span class="text-blue-600 font-medium mb-2">Founder & CEO</span>
        <p class="text-gray-600">Sneaker collector, entrepreneur, and community builder.</p>
      </div>
      <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center text-center">
        <img src="/SoleHub/assets/img/team2.jpg" alt="Head of Ops" class="w-24 h-24 rounded-full mb-4 object-cover" onerror="this.style.display='none'">
        <h3 class="font-semibold text-lg mb-1">Jamie Lee</h3>
        <span class="text-blue-600 font-medium mb-2">Head of Operations</span>
        <p class="text-gray-600">Ensuring every order is smooth, fast, and secure.</p>
      </div>
      <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center text-center">
        <img src="/SoleHub/assets/img/team3.jpg" alt="Customer Support" class="w-24 h-24 rounded-full mb-4 object-cover" onerror="this.style.display='none'">
        <h3 class="font-semibold text-lg mb-1">Morgan Smith</h3>
        <span class="text-blue-600 font-medium mb-2">Customer Support Lead</span>
        <p class="text-gray-600">Here to help you with any questions or issues.</p>
      </div>
    </div>
  </section>
  <section class="container mx-auto px-4 py-12 text-center">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Join the SoleHub Community</h2>
    <p class="text-gray-700 text-lg mb-6">Follow us on social media, sign up for our newsletter, and never miss a drop or exclusive offer!</p>
    <a href="/SoleHub/register.php" class="bg-blue-600 text-white font-semibold px-8 py-3 rounded-lg shadow hover:bg-blue-700 transition">Sign Up Now</a>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
