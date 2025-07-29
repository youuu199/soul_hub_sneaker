<?php include '../includes/auth.php'; ?>
<?php include '../includes/admin_header.php'; ?>
<main class="p-8">
  <h1 class="text-2xl font-bold mb-6 text-gray-800">Sales Analytics</h1>
  <div class="bg-white rounded shadow p-6 mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4">
      <div class="flex gap-2">
        <button onclick="setSalesType('product')" id="btn-product" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Product Sales</button>
        <button onclick="setSalesType('category')" id="btn-category" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Category Sales</button>
        <button onclick="setSalesType('brand')" id="btn-brand" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Brand Sales</button>
      </div>
      <form class="flex gap-2" onsubmit="filterSales(event)">
        <input type="text" id="sales-search" placeholder="Search..." class="border rounded px-3 py-2 focus:outline-none w-48">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Filter</button>
      </form>
    </div>
    <h2 class="text-xl font-semibold mb-4" id="sales-title">Product Sales Comparison</h2>
    <canvas id="salesChart" class="w-full h-64"></canvas>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white rounded shadow p-6">
      <h3 class="text-lg font-semibold mb-2">Summary</h3>
      <ul class="text-gray-700 space-y-2" id="sales-summary"></ul>
    </div>
    <div class="bg-white rounded shadow p-6">
      <h3 class="text-lg font-semibold mb-2" id="top-title">Top 5 Products</h3>
      <ol class="list-decimal ml-6 text-gray-700 space-y-1" id="top-list"></ol>
    </div>
  </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let currentType = 'product';
const ctx = document.getElementById('salesChart').getContext('2d');
let salesChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: [],
    datasets: [{
      label: 'Sales ($)',
      data: [],
      backgroundColor: [
        '#2563eb', '#60a5fa', '#818cf8', '#fbbf24', '#f87171'
      ],
      borderRadius: 8,
      maxBarThickness: 48
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: { enabled: true }
    },
    scales: {
      x: { grid: { display: false } },
      y: { beginAtZero: true, grid: { color: '#f3f4f6' } }
    }
  }
});

function updateAnalytics(type) {
  fetch(`/SoleHub/api/sales_analytics.php?type=${type}`)
    .then(res => res.json())
    .then(data => {
      salesChart.data.labels = data.labels;
      salesChart.data.datasets[0].data = data.data;
      salesChart.update();
      // Update summary
      let summaryHtml = `
        <li>Total Sales: <span class=\"font-bold text-blue-700\">$${data.summary.total_sales}</span></li>
        <li>Orders: <span class=\"font-bold text-blue-700\">${data.summary.orders}</span></li>
        <li>Top ${type.charAt(0).toUpperCase() + type.slice(1)}: <span class=\"font-bold text-blue-700\">${data.summary.top}</span></li>
        <li>Best Day: <span class=\"font-bold text-blue-700\">${data.summary.best_day}</span></li>
      `;
      document.getElementById('sales-summary').innerHTML = summaryHtml;
      // Update top list
      document.getElementById('top-title').textContent = `Top 5 ${type.charAt(0).toUpperCase() + type.slice(1)}s`;
      document.getElementById('top-list').innerHTML = data.topList.map(item => `<li>${item}</li>`).join('');
      // Update title
      document.getElementById('sales-title').textContent = `${type.charAt(0).toUpperCase() + type.slice(1)} Sales Comparison`;
      // Button styles
      document.getElementById('btn-product').className = type === 'product' ? 'bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700' : 'bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300';
      document.getElementById('btn-category').className = type === 'category' ? 'bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700' : 'bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300';
      document.getElementById('btn-brand').className = type === 'brand' ? 'bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700' : 'bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300';
    });
}

function setSalesType(type) {
  currentType = type;
  updateAnalytics(type);
}

function filterSales(e) {
  e.preventDefault();
  const search = document.getElementById('sales-search').value.trim().toLowerCase();
  fetch(`/SoleHub/api/sales_analytics.php?type=${currentType}`)
    .then(res => res.json())
    .then(data => {
      let filtered = data.labels.map((label, i) => ({ label, value: data.data[i] }))
        .filter(item => item.label.toLowerCase().includes(search));
      if (filtered.length === 0) {
        salesChart.data.labels = [];
        salesChart.data.datasets[0].data = [];
        document.getElementById('top-list').innerHTML = '<li class="text-gray-400">No results found</li>';
      } else {
        salesChart.data.labels = filtered.map(item => item.label);
        salesChart.data.datasets[0].data = filtered.map(item => item.value);
        document.getElementById('top-list').innerHTML = filtered.map(item => `<li>${item.label}</li>`).join('');
      }
      salesChart.update();
    });
}
// Initial load
updateAnalytics('product');
</script>
<?php include '../includes/admin_footer.php'; ?>
