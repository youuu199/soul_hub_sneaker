# SOLEHUB Sneaker E-Commerce Platform

SOLEHUB is a premium sneaker e-commerce platform built with PHP, MySQL, HTML, TailwindCSS, and JavaScript. It features user authentication, product browsing, cart/checkout, admin management, and a clean, responsive UI.

## Structure
- **Public Pages:** index.php, products.php, product_detail.php, cart.php, checkout.php, login.php, register.php, wishlist.php, contact.php
- **Admin Pages:** admin/dashboard.php, admin/manage_products.php, admin/manage_orders.php, admin/manage_users.php, admin/categories.php, admin/brands.php
- **Shared Components:** includes/header.php, includes/footer.php, includes/auth.php, config/db.php
- **Assets:** assets/js/scripts.js, assets/css/styles.css (TailwindCSS)
- **APIs:** /api/cart.php, /api/filter_products.php, /api/wishlist.php, /api/get_variants.php, /api/checkout.php

## Getting Started
1. Place project in your web server root (e.g., XAMPP's htdocs).
2. Configure your database in `config/db.php`.
3. Run `composer install` if you add PHP dependencies.
4. Use `npm` or CDN for TailwindCSS.
5. Start building features as per the requirements in the specification document.

## Security
- Use prepared statements for all SQL queries.
- Hash passwords with bcrypt.
- Validate and sanitize all user input.
- Use CSRF protection on forms.

---
See `.github/copilot-instructions.md` for Copilot-specific guidance.
