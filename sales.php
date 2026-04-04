<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

// 1. Total Potential Sales Revenue (Updated: Stock_Quantity * Unit_Price)
$total_sales_query = mysqli_query($conn,"SELECT SUM(Stock_Quantity * Unit_Price) as total FROM inventory");
$total_sales = mysqli_fetch_assoc($total_sales_query);

// 2. Inventory Alerts (Updated: Stock_Quantity)
$low_stock_items = mysqli_query($conn,"SELECT * FROM inventory WHERE Stock_Quantity < 10");
$stockout_items = mysqli_query($conn,"SELECT * FROM inventory WHERE Stock_Quantity = 0");

// 3. Top Selling Products (Updated: Product_Name, Catagory)
$top_products_query = mysqli_query($conn,"
    SELECT i.Product_Name, i.Catagory, SUM(s.quantity_sold) as total_sold
    FROM sales s
    JOIN inventory i ON s.product_id = i.id
    WHERE s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY s.product_id
    ORDER BY total_sold DESC
    LIMIT 5
");

// 4. Daily Sales (Updated: quantity_sold * sale_price)
$daily_sales_query = mysqli_query($conn,"
    SELECT DATE(sale_date) as day, SUM(quantity_sold * sale_price) as revenue
    FROM sales
    WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(sale_date)
");

// 5. Monthly Sales
$monthly_sales_query = mysqli_query($conn,"
    SELECT MONTH(sale_date) as month, SUM(quantity_sold * sale_price) as revenue
    FROM sales
    WHERE YEAR(sale_date) = YEAR(CURDATE())
    GROUP BY MONTH(sale_date)
");

// 6. Category Performance (Updated: Catagory)
$category_perf_query = mysqli_query($conn,"
    SELECT i.Catagory, SUM(s.quantity_sold) as total_sold
    FROM sales s
    JOIN inventory i ON s.product_id = i.id
    WHERE s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY i.Catagory
");

// 7. Inventory Turnover (Updated: Stock_Quantity)
$inventory_turnover_query = mysqli_query($conn,"
    SELECT SUM(s.quantity_sold) / NULLIF(AVG(i.Stock_Quantity),0) as turnover
    FROM sales s
    JOIN inventory i ON s.product_id = i.id
");
$inventory_turnover = mysqli_fetch_assoc($inventory_turnover_query);
?>

<div class="container mt-4">
    <h2 class="mb-4">Sales Analytics Dashboard</h2>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card p-3 bg-info text-white h-100">
                <h5>Total Potential Inventory Value</h5>
                <p class="fs-3">$<?php echo number_format($total_sales['total'] ?? 0, 2); ?></p>
                <small>Value of all current stock at unit price</small>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card p-3 bg-success text-white h-100">
                <h5>Inventory Turnover Ratio</h5>
                <p class="fs-3"><?php echo number_format($inventory_turnover['turnover'] ?? 0, 2); ?></p>
                <small>Higher ratio indicates better sales performance</small>
            </div>
        </div>
    </div>

    <?php if(mysqli_num_rows($low_stock_items) > 0): ?>
    <div class="alert alert-warning">
        <strong>Low Stock Alert!</strong> Products below 10 units:
        <?php while($item = mysqli_fetch_assoc($low_stock_items)){ echo $item['Product_Name'] . " (" . $item['Stock_Quantity'] . "), "; } ?>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card p-3 mb-4">
                <h5>Daily Sales Revenue (Last 30 Days)</h5>
                <canvas id="dailySalesChart"></canvas>
            </div>
            <div class="card p-3 mb-4">
                <h5>Monthly Sales Revenue (This Year)</h5>
                <canvas id="monthlySalesChart"></canvas>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card p-3 mb-4">
                <h5>Top 5 Selling Products</h5>
                <canvas id="topProductsChart"></canvas>
            </div>
            <div class="card p-3 mb-4">
                <h5>Category Performance</h5>
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// 1. Top Products Chart
const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
new Chart(topProductsCtx, {
    type: 'bar',
    data: {
        labels: [<?php while($row = mysqli_fetch_assoc($top_products_query)){ echo "'".$row['Product_Name']."',"; } ?>],
        datasets: [{
            label: 'Quantity Sold',
            data: [<?php mysqli_data_seek($top_products_query, 0); while($row = mysqli_fetch_assoc($top_products_query)){ echo $row['total_sold'].","; } ?>],
            backgroundColor: 'rgba(255, 99, 132, 0.6)'
        }]
    }
});

// 2. Daily Sales Chart
const dailySalesCtx = document.getElementById('dailySalesChart').getContext('2d');
new Chart(dailySalesCtx, {
    type: 'line',
    data: {
        labels: [<?php while($row = mysqli_fetch_assoc($daily_sales_query)){ echo "'".$row['day']."',"; } ?>],
        datasets: [{
            label: 'Revenue ($)',
            data: [<?php mysqli_data_seek($daily_sales_query, 0); while($row = mysqli_fetch_assoc($daily_sales_query)){ echo $row['revenue'].","; } ?>],
            borderColor: '#36A2EB',
            fill: false
        }]
    }
});

// 3. Monthly Sales Chart
const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
new Chart(monthlySalesCtx, {
    type: 'bar',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{
            label: 'Revenue ($)',
            data: [<?php 
                $m_data = array_fill(1, 12, 0);
                while($row = mysqli_fetch_assoc($monthly_sales_query)){ $m_data[$row['month']] = $row['revenue']; }
                echo implode(',', $m_data);
            ?>],
            backgroundColor: '#4BC0C0'
        }]
    }
});

// 4. Category Performance Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'pie',
    data: {
        labels: [<?php while($row = mysqli_fetch_assoc($category_perf_query)){ echo "'".$row['Catagory']."',"; } ?>],
        datasets: [{
            data: [<?php mysqli_data_seek($category_perf_query, 0); while($row = mysqli_fetch_assoc($category_perf_query)){ echo $row['total_sold'].","; } ?>],
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
        }]
    }
});
</script>

<?php include 'includes/footer.php'; ?>