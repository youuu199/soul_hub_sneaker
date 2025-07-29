<?php include '../includes/auth.php'; ?>
<?php include '../includes/admin_header.php'; ?>
<main class="p-8">
  <h1 class="text-2xl font-bold mb-6 text-gray-800">Order Management</h1>
  <div class="bg-white rounded shadow p-6 mb-8">
    <form class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4" method="get" action="">
      <div class="flex flex-col md:flex-row gap-2">
        <input type="text" name="order_id" placeholder="Order #" class="border rounded px-3 py-2 focus:outline-none w-32">
        <input type="text" name="customer" placeholder="Customer name/email" class="border rounded px-3 py-2 focus:outline-none w-48">
        <select name="status" class="border rounded px-3 py-2 focus:outline-none">
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="processing">Processing</option>
          <option value="shipped">Shipped</option>
          <option value="cancelled">Cancelled</option>
        </select>
        <input type="date" name="date_from" class="border rounded px-3 py-2 focus:outline-none">
        <input type="date" name="date_to" class="border rounded px-3 py-2 focus:outline-none">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Filter</button>
      </div>
    </form>
    <div class="overflow-x-auto rounded-xl shadow-lg bg-white">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gradient-to-r from-blue-100 to-indigo-100">
          <tr>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Order #</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Customer</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Total</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody id="orders-table-body" class="divide-y divide-gray-100"></tbody>
      </table>
    </div>
    <div class="flex justify-end mt-4">
      <nav id="orders-pagination" class="inline-flex rounded-md shadow-sm"></nav>
    </div>
    <div id="order-action-msg" class="hidden bg-green-100 text-green-700 px-4 py-2 rounded mb-4"></div>
  </div>
  <!-- Order Details Modal -->
  <div id="order-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative">
      <button onclick="document.getElementById('order-modal').classList.add('hidden')" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700">&times;</button>
      <div id="order-modal-msg" class="hidden bg-green-100 text-green-700 px-4 py-2 rounded mb-4 absolute top-2 left-1/2 transform -translate-x-1/2 w-3/4 text-center z-10"></div>
      <h2 class="text-xl font-semibold mb-4">Order Details</h2>
      <div class="mb-4">
        <div class="font-semibold">Customer:</div>
        <div></div>
        <div class="font-semibold mt-2">Shipping Address:</div>
        <div></div>
      </div>
      <table class="min-w-full divide-y divide-gray-200 mb-4">
        <thead class="bg-gradient-to-r from-blue-100 to-indigo-100">
          <tr>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Product</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Variant</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Qty</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Unit Price</th>
            <th class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100"></tbody>
      </table>
      <div class="flex justify-end font-bold text-lg">Total: $0.00</div>
      <div class="flex gap-2 mt-6 justify-end">
        <a href="#" id="order-processing-btn" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Mark as Processing</a>
        <a href="#" id="order-shipped-btn" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Mark as Shipped</a>
        <a href="#" id="order-cancel-btn" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700">Cancel Order</a>
      </div>
    </div>
  </div>
</main>
<?php include '../includes/admin_footer.php'; ?>
<script>
class OrderManager {
  static escapeHtml(text) {
    return String(text).replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','\'':'&#39;','"':'&quot;'}[c]));
  }
  static renderOrders(orders) {
    const tbody = document.getElementById('orders-table-body');
    tbody.innerHTML = '';
    if (!orders.length) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center text-gray-400 py-4">No orders found.</td></tr>`;
      return;
    }
    orders.forEach(order => {
      let statusHtml = '';
      if (order.status === 'shipped') statusHtml = '<span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Shipped</span>';
      else if (order.status === 'processing') statusHtml = '<span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">Processing</span>';
      else if (order.status === 'pending') statusHtml = '<span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">Pending</span>';
      else if (order.status === 'cancelled') statusHtml = '<span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Cancelled</span>';
      tbody.innerHTML += `
        <tr class="border-b hover:bg-blue-50 transition">
          <td class="py-3 px-4 font-medium text-gray-900">#${order.id}</td>
          <td class="py-3 px-4">${OrderManager.escapeHtml(order.customer_name || 'Guest')}<br><span class="text-xs text-gray-400">${OrderManager.escapeHtml(order.customer_email || '')}</span></td>
          <td class="py-3 px-4">$${Number(order.total).toFixed(2)}</td>
          <td class="py-3 px-4">${statusHtml}</td>
          <td class="py-3 px-4">${order.created_at ? order.created_at.substr(0,10) : ''}</td>
          <td class="py-3 px-4 flex gap-2">
            <a href="#" class="text-blue-600 hover:underline" onclick="OrderManager.openOrderModal(${order.id});return false;">View</a>
            <a href="#" class="text-yellow-600 hover:underline" onclick="OrderManager.orderAction(${order.id},'processing');return false;">Mark as Processing</a>
            <a href="#" class="text-green-600 hover:underline" onclick="OrderManager.orderAction(${order.id},'shipped');return false;">Mark as Shipped</a>
            <a href="#" class="text-red-500 hover:underline" onclick="OrderManager.orderAction(${order.id},'cancelled');return false;">Cancel</a>
          </td>
        </tr>
      `;
    });
  }
  static renderOrderPagination(page, total) {
    const nav = document.getElementById('orders-pagination');
    let html = '';
    for (let i = 1; i <= total; i++) {
      const active = i === page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50';
      html += `<a href="#" onclick="OrderManager.fetchOrders(${i});return false;" class="px-3 py-1 border border-gray-300 ${active}">${i}</a>`;
    }
    nav.innerHTML = html;
  }
  static showOrderMsg(msg) {
    const box = document.getElementById('order-action-msg');
    box.textContent = msg;
    box.classList.remove('hidden');
    setTimeout(()=>box.classList.add('hidden'), 2500);
  }
  static showOrderModalMsg(msg, action = '') {
    const box = document.getElementById('order-modal-msg');
    box.textContent = msg;
    box.classList.remove('hidden', 'bg-green-100', 'text-green-700', 'bg-yellow-100', 'text-yellow-700', 'bg-red-100', 'text-red-700');
    if (action === 'processing') {
      box.classList.add('bg-yellow-100', 'text-yellow-700');
    } else if (action === 'shipped') {
      box.classList.add('bg-green-100', 'text-green-700');
    } else if (action === 'cancelled') {
      box.classList.add('bg-red-100', 'text-red-700');
    } else {
      box.classList.add('bg-green-100', 'text-green-700');
    }
    setTimeout(()=>box.classList.add('hidden'), 2500);
  }
  static orderAction(order_id, action) {
    fetch('/SoleHub/api/manage_orders.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ order_id, action })
    })
    .then(res => res.json())
    .then(data => {
      OrderManager.showOrderMsg(data.message);
      OrderManager.showOrderModalMsg(data.message, action);
      OrderManager.fetchOrders();
    });
  }
  static fetchOrders(page=1) {
    const order_id = document.querySelector('input[name=order_id]').value;
    const customer = document.querySelector('input[name=customer]').value;
    const status = document.querySelector('select[name=status]').value;
    const date_from = document.querySelector('input[name=date_from]').value;
    const date_to = document.querySelector('input[name=date_to]').value;
    fetch(`/SoleHub/api/manage_orders.php?order_id=${encodeURIComponent(order_id)}&customer=${encodeURIComponent(customer)}&status=${encodeURIComponent(status)}&date_from=${encodeURIComponent(date_from)}&date_to=${encodeURIComponent(date_to)}&page=${page}`)
      .then(res => res.json())
      .then(data => {
        OrderManager.renderOrders(data.orders || []);
        OrderManager.renderOrderPagination(data.page || 1, data.total_pages || 1);
      })
      .catch(() => {
        OrderManager.renderOrders([]);
        OrderManager.renderOrderPagination(1, 1);
      });
  }
  static openOrderModal(orderId) {
    fetch(`/SoleHub/api/manage_orders.php?order_id=${orderId}`)
      .then(res => res.json())
      .then(order => {
        window.currentOrderId = order.id;
        document.querySelector('#order-modal h2').textContent = `Order #${order.id} Details`;
        const customerDivs = document.querySelectorAll('#order-modal .font-semibold + div');
        if (customerDivs.length > 0) {
          customerDivs[0].innerHTML = `${order.customer_name || 'Guest'} &lt;${order.customer_email || ''}&gt;`;
        }
        if (customerDivs.length > 1) {
          customerDivs[1].textContent = order.shipping_address || 'N/A';
        }
        const tbody = document.querySelector('#order-modal table tbody');
        tbody.innerHTML = '';
        let modalTotal = 0;
        if (order.items && order.items.length) {
          order.items.forEach(item => {
            modalTotal += Number(item.total);
            tbody.innerHTML += `
              <tr>
                <td class="py-2 px-3">${OrderManager.escapeHtml(item.product_name)}</td>
                <td class="py-2 px-3">${OrderManager.escapeHtml(item.variant)}</td>
                <td class="py-2 px-3">${item.qty}</td>
                <td class="py-2 px-3">$${Number(item.unit_price).toFixed(2)}</td>
                <td class="py-2 px-3">$${Number(item.total).toFixed(2)}</td>
              </tr>
            `;
          });
        } else {
          tbody.innerHTML = '<tr><td colspan="5" class="text-center text-gray-400 py-4">No items found.</td></tr>';
        }
        document.querySelector('#order-modal .font-bold.text-lg').textContent = `Total: $${modalTotal.toFixed(2)}`;
        document.getElementById('order-modal').classList.remove('hidden');
      });
  }
  static attachOrderModalActions() {
    document.getElementById('order-processing-btn').onclick = function(e) {
      e.preventDefault();
      if (window.currentOrderId) OrderManager.orderAction(window.currentOrderId, 'processing');
    };
    document.getElementById('order-shipped-btn').onclick = function(e) {
      e.preventDefault();
      if (window.currentOrderId) OrderManager.orderAction(window.currentOrderId, 'shipped');
    };
    document.getElementById('order-cancel-btn').onclick = function(e) {
      e.preventDefault();
      if (window.currentOrderId) OrderManager.orderAction(window.currentOrderId, 'cancelled');
    };
  }
}
document.querySelector('form[method=get]').onsubmit = function(e) {
  e.preventDefault();
  OrderManager.fetchOrders();
};
document.addEventListener('DOMContentLoaded', function() {
  OrderManager.fetchOrders();
  OrderManager.attachOrderModalActions();
});
</script>
