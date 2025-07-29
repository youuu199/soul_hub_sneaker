<?php 
include '../includes/auth.php'; 
include '../includes/admin_header.php';
require_once '../config/db.php';
?>
<main class="p-8">
  <h1 class="text-3xl font-bold mb-6 text-gray-800">Admin Dashboard</h1>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" id="dashboard-cards">
    <div class="bg-white rounded shadow p-6 flex flex-col items-center">
      <div class="text-gray-500 text-sm">Total Sales</div>
      <div class="text-2xl font-bold mt-2" id="total-sales">$0.00</div>
    </div>
    <div class="bg-white rounded shadow p-6 flex flex-col items-center">
      <div class="text-gray-500 text-sm">Orders Today</div>
      <div class="text-2xl font-bold mt-2" id="orders-today">0</div>
    </div>
    <div class="bg-white rounded shadow p-6 flex flex-col items-center">
      <div class="text-gray-500 text-sm">Low Stock Alerts</div>
      <div class="text-2xl font-bold mt-2" id="low-stock">0</div>
      <a href="manage_products.php?low_stock=1" class="text-red-500 text-xs mt-1 underline">View products</a>
    </div>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white rounded shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Recent Orders</h2>
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-gray-500 border-b">
            <th class="py-2 text-left">Order #</th>
            <th class="py-2 text-left">Customer</th>
            <th class="py-2 text-left">Total</th>
            <th class="py-2 text-left">Status</th>
            <th class="py-2 text-left">Date</th>
          </tr>
        </thead>
        <tbody id="recent-orders-tbody">
          <!-- Populated by JS -->
        </tbody>
      </table>
      <a href="manage_orders.php" class="block text-blue-600 hover:underline mt-4 text-sm">View all orders</a>
    </div>
    <div class="bg-white rounded shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Sales Trends</h2>
      <canvas id="salesChart" class="w-full h-40"></canvas>
    </div>
  </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
class AdminDashboard {
  constructor() {
    this.salesChart = null;
    this.ctx = document.getElementById('salesChart').getContext('2d');
    this.init();
  }
  static escapeHtml(text) {
    return String(text).replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','\'':'&#39;','"':'&quot;'}[c]));
  }
  init() {
    this.loadStats();
    this.loadSalesTrends();
  }
  loadStats() {
    fetch('/SoleHub/api/dashboard_stats.php')
      .then(res => res.json())
      .then(data => {
        document.getElementById('total-sales').textContent = `$${Number(data.total_sales).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`;
        document.getElementById('orders-today').textContent = data.orders_today;
        document.getElementById('low-stock').textContent = data.low_stock;
        this.renderRecentOrders(data.recent_orders);
      });
  }
  renderRecentOrders(orders) {
    const tbody = document.getElementById('recent-orders-tbody');
    tbody.innerHTML = '';
    orders.forEach(order => {
      let statusHtml = '';
      if (order.status === 'shipped') statusHtml = '<span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Shipped</span>';
      else if (order.status === 'processing') statusHtml = '<span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">Processing</span>';
      else if (order.status === 'pending') statusHtml = '<span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">Pending</span>';
      else if (order.status === 'cancelled') statusHtml = '<span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Cancelled</span>';
      tbody.innerHTML += `
        <tr>
          <td class="py-2">#${order.id}</td>
          <td class="py-2">${AdminDashboard.escapeHtml(order.customer_name || 'Guest')}</td>
          <td class="py-2">$${Number(order.total).toFixed(2)}</td>
          <td class="py-2">${statusHtml}</td>
          <td class="py-2">${order.created_at ? order.created_at.substr(0,10) : ''}</td>
        </tr>
      `;
    });
  }
  loadSalesTrends() {
    fetch('/SoleHub/api/sales_trends.php')
      .then(res => res.json())
      .then(({labels, data}) => {
        if (this.salesChart) {
          this.salesChart.data.labels = labels;
          this.salesChart.data.datasets[0].data = data;
          this.salesChart.update();
        } else {
          this.salesChart = new Chart(this.ctx, {
            type: 'line',
            data: {
              labels: labels,
              datasets: [{
                label: 'Sales ($)',
                data: data,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#2563eb',
              }]
            },
            options: {
              responsive: true,
              plugins: {
                legend: { display: false },
              },
              scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, grid: { color: '#f3f4f6' } }
              }
            }
          });
        }
      });
  }
}
document.addEventListener('DOMContentLoaded', () => new AdminDashboard());
</script>
<?php include '../includes/admin_footer.php'; ?>
