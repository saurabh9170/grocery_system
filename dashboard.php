<?php include 'includes/session.php'; ?>
<?php include 'includes/db.php'; ?>
<?php include 'includes/header.php'; ?>

<?php
$inv = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total_products, SUM(quantity) AS total_quantity FROM inventory"));
$sales = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(quantity_sold) AS total_sold, SUM(sale_price*quantity_sold) AS total_revenue FROM sales"));

$top_products = [];
$top = mysqli_query($conn,"SELECT i.product_name, SUM(s.quantity_sold) AS total_sold
                          FROM sales s JOIN inventory i ON s.product_id=i.id
                          GROUP BY s.product_id ORDER BY total_sold DESC LIMIT 5");
while($row = mysqli_fetch_assoc($top)) $top_products[] = $row;
?>

<h2 class="dashboard-header">Welcome, <?php echo $_SESSION['username']; ?></h2>

<div class="row g-3">
  <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>Total Products</h5><h2><?php echo $inv['total_products']; ?></h2></div></div></div>
  <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>Total Quantity</h5><h2><?php echo $inv['total_quantity']; ?></h2></div></div></div>
  <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>Total Sold</h5><h2><?php echo $sales['total_sold'] ?? 0; ?></h2></div></div></div>
  <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>Total Revenue</h5><h2>$<?php echo number_format($sales['total_revenue'] ?? 0,2); ?></h2></div></div></div>
</div>

<div class="card mt-4 p-3">
  <h5>Top 5 Selling Products</h5>
  <canvas id="topProductsChart"></canvas>
</div>

<script>
const ctx = document.getElementById('topProductsChart').getContext('2d');
const topProductsChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: [<?php foreach($top_products as $p) echo "'".$p['product_name']."',"; ?>],
    datasets: [{ label: 'Quantity Sold', data: [<?php foreach($top_products as $p) echo $p['total_sold'].","; ?>], backgroundColor: 'rgba(75,192,192,0.7)' }]
  },
  options: { responsive:true, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true } } }
});
</script>

<?php include 'includes/footer.php'; ?>