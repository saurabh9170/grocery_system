<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

// --- 1. Total Inventory Value ---
$total_sales_query = mysqli_query($conn,"SELECT SUM(Stock_Quantity * Unit_Price) as total FROM inventory");
$total_sales = mysqli_fetch_assoc($total_sales_query);

// --- 2. Low Stock Alerts ---
$low_stock_items = mysqli_query($conn,"SELECT Product_Name, Stock_Quantity FROM inventory WHERE Stock_Quantity < 10");

// --- 3. Top Selling Products ---
$top_prod_names = [];
$top_prod_sales = [];
$top_products_query = mysqli_query($conn,"
    SELECT i.Product_Name, SUM(s.quantity_sold) as total_sold
    FROM sales s
    JOIN inventory i ON s.product_id = i.id
    GROUP BY s.product_id
    ORDER BY total_sold DESC
    LIMIT 5
");
while($row = mysqli_fetch_assoc($top_products_query)) {
    $top_prod_names[] = $row['Product_Name'];
    $top_prod_sales[] = (int)$row['total_sold'];
}

// --- 4. Daily Sales (Last 30 Days) ---
$daily_labels = [];
$daily_data = [];
$daily_sales_query = mysqli_query($conn,"
    SELECT DATE(sale_date) as day, SUM(quantity_sold * sale_price) as revenue
    FROM sales
    WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(sale_date)
    ORDER BY day ASC
");
while($row = mysqli_fetch_assoc($daily_sales_query)) {
    $daily_labels[] = $row['day'];
    $daily_data[] = (float)$row['revenue'];
}

// --- 5. Monthly Sales (Current Year) ---
$monthly_revenue = array_fill(1, 12, 0);
$monthly_sales_query = mysqli_query($conn,"
    SELECT MONTH(sale_date) as month, SUM(quantity_sold * sale_price) as revenue
    FROM sales
    WHERE YEAR(sale_date) = YEAR(CURDATE())
    GROUP BY MONTH(sale_date)
");
while($row = mysqli_fetch_assoc($monthly_sales_query)) {
    $monthly_revenue[(int)$row['month']] = (float)$row['revenue'];
}

// --- 6. Category Performance (FIXED: LEFT JOIN ensures visibility) ---
$cat_labels = [];
$cat_data = [];
$category_perf_query = mysqli_query($conn,"
    SELECT i.Catagory, IFNULL(SUM(s.quantity_sold), 0) as total_sold
    FROM inventory i
    LEFT JOIN sales s ON i.id = s.product_id
    GROUP BY i.Catagory
");
while($row = mysqli_fetch_assoc($category_perf_query)) {
    $cat_labels[] = $row['Catagory'];
    $cat_data[] = (int)$row['total_sold'];
}
?>

<div class="container mt-4">
    <h2 class="mb-4">Sales Analytics Dashboard</h2>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card p-3 bg-info text-white shadow-sm border-0">
                <h5>Total Potential Inventory Value</h5>
                <p class="display-6">$<?php echo number_format($total_sales['total'] ?? 0, 2); ?></p>
                <small>Current stock value at unit price</small>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <?php if(mysqli_num_rows($low_stock_items) > 0): ?>
            <div class="alert alert-warning shadow-sm border-0 h-100">
                <h5 class="alert-heading">Low Stock Alerts</h5>
                <hr>
                <?php 
                mysqli_data_seek($low_stock_items, 0);
                while($item = mysqli_fetch_assoc($low_stock_items)){ 
                    echo "<span class='badge bg-danger p-2 mb-1'>".$item['Product_Name']." (".$item['Stock_Quantity'].")</span> "; 
                } ?>
            </div>
            <?php else: ?>
            <div class="alert alert-success shadow-sm border-0 h-100 d-flex align-items-center">
                <strong>All stock levels are currently good!</strong>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card p-4 mb-4 shadow-sm border-0">
                <h5 class="text-secondary">Daily Sales Revenue (Last 30 Days)</h5>
                <canvas id="dailySalesChart" style="max-height: 250px;"></canvas>
            </div>
            <div class="card p-4 mb-4 shadow-sm border-0">
                <h5 class="text-secondary">Monthly Revenue Breakdown</h5>
                <canvas id="monthlySalesChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card p-4 mb-4 shadow-sm border-0">
                <h5 class="text-secondary">Top 5 Products</h5>
                <canvas id="topProductsChart"></canvas>
            </div>
            <div class="card p-4 mb-4 shadow-sm border-0">
                <h5 class="text-secondary">Category Performance</h5>
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Data Passed from PHP to JavaScript
const topLabels = <?php echo json_encode($top_prod_names); ?>;
const topValues = <?php echo json_encode($top_prod_sales); ?>;

const dailyLabels = <?php echo json_encode($daily_labels); ?>;
const dailyValues = <?php echo json_encode($daily_data); ?>;

const monthlyValues = <?php echo json_encode(array_values($monthly_revenue)); ?>;

const catLabels = <?php echo json_encode($cat_labels); ?>;
const catValues = <?php echo json_encode($cat_data); ?>;

// 1. Top Products (Bar Chart)
new Chart(document.getElementById('topProductsChart'), {
    type: 'bar',
    data: {
        labels: topLabels,
        datasets: [{
            label: 'Total Sold',
            data: topValues,
            backgroundColor: '#FF6384',
            borderRadius: 5
        }]
    }
});

// 2. Daily Sales (Line Chart)
new Chart(document.getElementById('dailySalesChart'), {
    type: 'line',
    data: {
        labels: dailyLabels,
        datasets: [{
            label: 'Revenue ($)',
            data: dailyValues,
            borderColor: '#36A2EB',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            fill: true,
            tension: 0.4
        }]
    }
});

// 3. Monthly Sales (Bar Chart)
new Chart(document.getElementById('monthlySalesChart'), {
    type: 'bar',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{
            label: 'Revenue ($)',
            data: monthlyValues,
            backgroundColor: '#4BC0C0',
            borderRadius: 5
        }]
    }
});

// 4. Category Performance (Pie Chart)
if(catLabels.length > 0) {
    new Chart(document.getElementById('categoryChart'), {
        type: 'pie',
        data: {
            labels: catLabels,
            datasets: [{
                data: catValues,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
            }]
        },
        options: {
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>