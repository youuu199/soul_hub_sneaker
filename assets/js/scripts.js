// Cart count AJAX update for header and mobile menu
function updateCartCount() {
  fetch('/SoleHub/api/cart_count.php')
    .then(res => res.json())
    .then(data => {
      const count = data.count ?? 0;
      const cartCount = document.getElementById('cart-count');
      const cartCountMobile = document.getElementById('cart-count-mobile');
      if (cartCount) cartCount.textContent = count;
      if (cartCountMobile) cartCountMobile.textContent = count;
    });
}

document.addEventListener('DOMContentLoaded', () => {
  updateCartCount();
  window.updateCartCount = updateCartCount;
});