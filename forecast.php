<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

// Fetch inventory data
$inventory_query = mysqli_query($conn,"SELECT * FROM inventory");

// Fetch last 30 days sales for forecasting
$sales_query = mysqli_query($conn,"
    SELECT product_id, SUM(quantity_sold) as total_sold, DATE(sale_date) as sale_day
    FROM sales
    WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY product_id, DATE(sale_date)
");
$sales_data = [];
while($row = mysqli_fetch_assoc($sales_query)){
    $sales_data[$row['product_id']][$row['sale_day']] = $row['total_sold'];
}

// Prepare data for graph (Moving Average)
$graph_labels = [];
$graph_values = [];
$graph_products = mysqli_query($conn,"SELECT DISTINCT product_name, id FROM inventory LIMIT 5"); // top 5 products for simplicity
while($prod = mysqli_fetch_assoc($graph_products)){
    $graph_labels[] = $prod['product_name'];
    $total = 0;
    $days_counted = 0;
    if(isset($sales_data[$prod['id']])){
        foreach($sales_data[$prod['id']] as $day => $sold){
            $total += $sold;
            $days_counted++;
        }
    }
    // Moving average for last 30 days
    $avg = ($days_counted > 0) ? round($total / $days_counted, 2) : 0;
    $graph_values[] = $avg;
}
?>

<h2 class="mb-4">Forecasting</h2>
<p>This is a simple demand forecast based on current inventory levels and moving average of last 30 days sales.</p>

<h4>Inventory & Recommended Orders</h4>
<table class="table table-bordered mb-4">
  <thead>
    <tr>
      <th>Product</th>
      <th>Current Quantity</th>
      <th>Recommended Order</th>
    </tr>
  </thead>
  <tbody>
  <?php
  while($row = mysqli_fetch_assoc($inventory_query)){
      $recommended = ($row['quantity'] < 20) ? 50 - $row['quantity'] : 0;
      echo "<tr>
              <td>{$row['product_name']}</td>
              <td>{$row['quantity']}</td>
              <td>{$recommended}</td>
            </tr>";
  }
  ?>
  </tbody>
</table>

<h4>Sales Trend (Moving Average)</h4>
<canvas id="salesTrendChart" height="100"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('salesTrendChart').getContext('2d');
const salesTrendChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($graph_labels); ?>,
        datasets: [{
            label: 'Avg Daily Sales (Last 30 Days)',
            data: <?php echo json_encode($graph_values); ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 2,
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: true } },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
</div>
</body>
</html>