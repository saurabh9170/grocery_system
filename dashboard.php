<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

// Inventory Summary
$inv_query = "SELECT COUNT(*) as total_products, SUM(quantity) as total_quantity FROM inventory";
$inv_result = mysqli_query($conn, $inv_query);
$inv_data = mysqli_fetch_assoc($inv_result);

// Sales Summary
$sales_query = "SELECT SUM(quantity_sold) as total_sold, SUM(sale_price*quantity_sold) as total_revenue FROM sales";
$sales_result = mysqli_query($conn, $sales_query);
$sales_data = mysqli_fetch_assoc($sales_result);

// Top 5 Selling Products
$top_query = "SELECT i.product_name, SUM(s.quantity_sold) as total_sold
              FROM sales s 
              JOIN inventory i ON s.product_id=i.id 
              GROUP BY s.product_id 
              ORDER BY total_sold DESC 
              LIMIT 5";
$top_result = mysqli_query($conn, $top_query);
$top_products = [];
while($row = mysqli_fetch_assoc($top_result)){
    $top_products[] = $row;
}
?>

<div class="container mt-4">
    <h2>Welcome, <?php echo $_SESSION['username']; ?></h2>

    <div class="row mt-4">
        <div class="col-md-3 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5>Total Products</h5>
                    <h2><?php echo $inv_data['total_products']; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5>Total Quantity</h5>
                    <h2><?php echo $inv_data['total_quantity']; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5>Total Sold</h5>
                    <h2><?php echo $sales_data['total_sold'] ?? 0; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5>Total Revenue</h5>
                    <h2>$<?php echo number_format($sales_data['total_revenue'] ?? 0,2); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4 p-3 shadow-sm">
        <h5>Top 5 Selling Products</h5>
        <canvas id="topProductsChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('topProductsChart').getContext('2d');
const topProductsChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [<?php foreach($top_products as $p){ echo "'".$p['product_name']."',"; } ?>],
        datasets: [{
            label: 'Quantity Sold',
            data: [<?php foreach($top_products as $p){ echo $p['total_sold'].","; } ?>],
            backgroundColor: 'rgba(75, 192, 192, 0.7)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>