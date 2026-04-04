<?php include 'includes/session.php'; ?>
<?php include 'includes/db.php'; ?>
<?php include 'includes/header.php'; ?>

<?php
// UPDATED: 'quantity' changed to 'Stock_Quantity'
$inv_query = mysqli_query($conn,"SELECT COUNT(*) AS total_products, SUM(Stock_Quantity) AS total_quantity FROM inventory");
$inv = mysqli_fetch_assoc($inv_query);

// Sales query remains mostly the same, ensuring we handle NULLs with ?? 0
$sales_query = mysqli_query($conn,"SELECT SUM(quantity_sold) AS total_sold, SUM(sale_price*quantity_sold) AS total_revenue FROM sales");
$sales = mysqli_fetch_assoc($sales_query);

$top_products = [];
// UPDATED: 'i.product_name' changed to 'i.Product_Name'
$top = mysqli_query($conn,"SELECT i.Product_Name, SUM(s.quantity_sold) AS total_sold
                           FROM sales s JOIN inventory i ON s.product_id=i.id
                           GROUP BY s.product_id ORDER BY total_sold DESC LIMIT 5");

while($row = mysqli_fetch_assoc($top)) {
    $top_products[] = $row;
}
?>

<h2 class="dashboard-header mb-4">Welcome, <?php echo $_SESSION['username']; ?></h2>

<div class="row g-3">
  <div class="col-md-3">
    <div class="card text-center shadow-sm border-0 bg-primary text-white">
      <div class="card-body">
        <h5>Total Products</h5>
        <h2><?php echo $inv['total_products']; ?></h2>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-center shadow-sm border-0 bg-info text-white">
      <div class="card-body">
        <h5>Total Stock</h5>
        <h2><?php echo number_format($inv['total_quantity'] ?? 0); ?></h2>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-center shadow-sm border-0 bg-success text-white">
      <div class="card-body">
        <h5>Total Sold</h5>
        <h2><?php echo number_format($sales['total_sold'] ?? 0); ?></h2>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-center shadow-sm border-0 bg-warning text-white">
      <div class="card-body">
        <h5>Total Revenue</h5>
        <h2>$<?php echo number_format($sales['total_revenue'] ?? 0, 2); ?></h2>
      </div>
    </div>
  </div>
</div>

<div class="card mt-4 p-4 shadow-sm border-0">
  <h5 class="mb-3">Top 5 Selling Products</h5>
  <div style="height: 300px;">
    <canvas id="topProductsChart"></canvas>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('topProductsChart').getContext('2d');
const topProductsChart = new Chart(ctx, {
  type: 'bar',
  data: {
    // UPDATED: changed to match the array key 'Product_Name'
    labels: [<?php foreach($top_products as $p) echo "'".addslashes($p['Product_Name'])."',"; ?>],
    datasets: [{ 
      label: 'Quantity Sold', 
      data: [<?php foreach($top_products as $p) echo $p['total_sold'].","; ?>], 
      backgroundColor: 'rgba(54, 162, 235, 0.7)',
      borderColor: 'rgba(54, 162, 235, 1)',
      borderWidth: 1
    }]
  },
  options: { 
    maintainAspectRatio: false,
    responsive: true, 
    plugins: { legend: { display: false } }, 
    scales: { y: { beginAtZero: true } } 
  }
});
</script>

<?php include 'includes/footer.php'; ?>