<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

// Total Potential Sales Revenue
$total_sales = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(quantity*price) as total FROM inventory"));

// Inventory Alerts
$low_stock_items = mysqli_query($conn,"SELECT * FROM inventory WHERE quantity < 10");
$stockout_items = mysqli_query($conn,"SELECT * FROM inventory WHERE quantity = 0");

// Top Selling Products (last 30 days)
$top_products_query = mysqli_query($conn,"
    SELECT i.product_name, i.category, SUM(s.quantity_sold) as total_sold
    FROM sales s
    JOIN inventory i ON s.product_id = i.id
    WHERE s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY s.product_id
    ORDER BY total_sold DESC
    LIMIT 5
");

// Daily Sales (last 30 days)
$daily_sales_query = mysqli_query($conn,"
    SELECT DATE(sale_date) as day, SUM(quantity_sold*sale_price) as revenue
    FROM sales
    WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(sale_date)
");

// Monthly Sales (current year)
$monthly_sales_query = mysqli_query($conn,"
    SELECT MONTH(sale_date) as month, SUM(quantity_sold*sale_price) as revenue
    FROM sales
    WHERE YEAR(sale_date) = YEAR(CURDATE())
    GROUP BY MONTH(sale_date)
");

// Category Performance (last 30 days)
$category_perf_query = mysqli_query($conn,"
    SELECT i.category, SUM(s.quantity_sold) as total_sold
    FROM sales s
    JOIN inventory i ON s.product_id=i.id
    WHERE s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY i.category
");

// Inventory Turnover (Total Sold / Avg Stock)
$inventory_turnover = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT SUM(quantity_sold) / NULLIF(AVG(i.quantity),0) as turnover
    FROM sales s
    JOIN inventory i ON s.product_id=i.id
"));
?>

<h2 class="mb-4">Sales Analytics</h2>

<!-- Total Revenue -->
<div class="card p-3 bg-info text-white mb-4">
  <h5>Total Potential Sales Revenue</h5>
  <p class="fs-3">$<?php echo number_format($total_sales['total'],2); ?></p>
</div>

<!-- Low Stock Alerts -->
<?php if(mysqli_num_rows($low_stock_items) > 0): ?>
<div class="alert alert-warning">
    <strong>Low Stock Alert!</strong> Products below 10 units:
    <?php while($item = mysqli_fetch_assoc($low_stock_items)){ echo $item['product_name'] . " (" . $item['quantity'] . "), "; } ?>
</div>
<?php endif; ?>

<?php if(mysqli_num_rows($stockout_items) > 0): ?>
<div class="alert alert-danger">
    <strong>Stockout Alert!</strong> Products out of stock:
    <?php while($item = mysqli_fetch_assoc($stockout_items)){ echo $item['product_name'] . ", "; } ?>
</div>
<?php endif; ?>

<!-- Inventory Turnover -->
<div class="card p-3 bg-success text-white mb-4">
  <h5>Inventory Turnover</h5>
  <p class="fs-3"><?php echo number_format($inventory_turnover['turnover'] ?? 0,2); ?></p>
</div>

<!-- Top 5 Products Chart -->
<div class="card p-3 mb-4">
    <h5>Top 5 Selling Products (Last 30 Days)</h5>
    <canvas id="topProductsChart"></canvas>
</div>

<!-- Daily Sales Chart -->
<div class="card p-3 mb-4">
    <h5>Daily Sales Revenue (Last 30 Days)</h5>
    <canvas id="dailySalesChart"></canvas>
</div>

<!-- Monthly Sales Chart -->
<div class="card p-3 mb-4">
    <h5>Monthly Sales Revenue (This Year)</h5>
    <canvas id="monthlySalesChart"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Top Products Chart
const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
const topProductsChart = new Chart(topProductsCtx, {
    type: 'bar',
    data: {
        labels: [<?php while($row = mysqli_fetch_assoc($top_products_query)){ echo "'".$row['product_name']."',"; } ?>],
        datasets: [{
            label: 'Quantity Sold',
            data: [<?php
            mysqli_data_seek($top_products_query, 0);
            while($row = mysqli_fetch_assoc($top_products_query)){ echo $row['total_sold'].","; }
            ?>],
            backgroundColor: 'rgba(255, 99, 132, 0.6)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }]
    },
    options: { responsive:true }
});

// Daily Sales Chart
const dailySalesLabels = [<?php while($row = mysqli_fetch_assoc($daily_sales_query)){ echo "'".$row['day']."',"; } ?>];
const dailySalesData = [<?php
mysqli_data_seek($daily_sales_query, 0);
while($row = mysqli_fetch_assoc($daily_sales_query)){ echo $row['revenue'].","; }
?>];

const dailySalesCtx = document.getElementById('dailySalesChart').getContext('2d');
const dailySalesChart = new Chart(dailySalesCtx, {
    type: 'line',
    data: {
        labels: dailySalesLabels,
        datasets: [{
            label: 'Revenue ($)',
            data: dailySalesData,
            backgroundColor: 'rgba(54, 162, 235,0.2)',
            borderColor: 'rgba(54, 162, 235,1)',
            borderWidth: 2,
            fill: true,
            tension:0.3
        }]
    },
    options:{ responsive:true }
});

// Monthly Sales Chart
const monthlyLabels = [<?php while($row = mysqli_fetch_assoc($monthly_sales_query)){ echo "'".$row['month']."',"; } ?>];
const monthlyData = [<?php
mysqli_data_seek($monthly_sales_query, 0);
while($row = mysqli_fetch_assoc($monthly_sales_query)){ echo $row['revenue'].","; }
?>];

const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
const monthlySalesChart = new Chart(monthlySalesCtx, {
    type:'bar',
    data:{
        labels: monthlyLabels,
        datasets:[{
            label:'Revenue ($)',
            data:monthlyData,
            backgroundColor:'rgba(75,192,192,0.6)',
            borderColor:'rgba(75,192,192,1)',
            borderWidth:1
        }]
    },
    options:{ responsive:true }
});

// Category Performance Chart
const categoryLabels = [<?php while($row = mysqli_fetch_assoc($category_perf_query)){ echo "'".$row['category']."',"; } ?>];
const categoryData = [<?php
mysqli_data_seek($category_perf_query,0);
while($row = mysqli_fetch_assoc($category_perf_query)){ echo $row['total_sold'].","; }
?>];

const categoryCtx = document.getElementById('categoryChart').getContext('2d');
const categoryChart = new Chart(categoryCtx, {
    type:'pie',
    data:{
        labels: categoryLabels,
        datasets:[{
            label:'Quantity Sold',
            data: categoryData,
            backgroundColor:[
                'rgba(255,99,132,0.6)',
                'rgba(54,162,235,0.6)',
                'rgba(255,206,86,0.6)',
                'rgba(75,192,192,0.6)',
                'rgba(153,102,255,0.6)'
            ]
        }]
    },
    options:{ responsive:true }
});
</script>

</div>
</body>
</html>