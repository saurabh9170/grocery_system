<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

// --- 1. Key Metrics (KPIs) ---
$total_inv_query = mysqli_query($conn,"SELECT SUM(Stock_Quantity * Unit_Price) as total FROM inventory");
$total_inv = mysqli_fetch_assoc($total_inv_query);

$actual_rev_query = mysqli_query($conn,"SELECT SUM(quantity_sold * sale_price) as total_rev FROM sales");
$actual_rev = mysqli_fetch_assoc($actual_rev_query);

$total_qty_query = mysqli_query($conn,"SELECT SUM(quantity_sold) as total_qty FROM sales");
$total_qty = mysqli_fetch_assoc($total_qty_query);

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

// --- 6. Category Performance ---
$cat_labels = [];
$cat_data = [];
$best_cat = "N/A";
$max_cat_sale = 0;

$category_perf_query = mysqli_query($conn,"
    SELECT i.Catagory, IFNULL(SUM(s.quantity_sold), 0) as total_sold
    FROM inventory i
    LEFT JOIN sales s ON i.id = s.product_id
    GROUP BY i.Catagory
");
while($row = mysqli_fetch_assoc($category_perf_query)) {
    $cat_labels[] = $row['Catagory'];
    $cat_data[] = (int)$row['total_sold'];
    if($row['total_sold'] > $max_cat_sale) {
        $max_cat_sale = $row['total_sold'];
        $best_cat = $row['Catagory'];
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Sales Analytics Dashboard</h2>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm d-print-none">Download Report</button>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card p-3 bg-primary text-white shadow-sm border-0 text-center">
                <small class="text-uppercase opacity-75">Actual Revenue</small>
                <h3>$<?php echo number_format($actual_rev['total_rev'] ?? 0, 2); ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 bg-success text-white shadow-sm border-0 text-center">
                <small class="text-uppercase opacity-75">Units Sold</small>
                <h3><?php echo number_format($total_qty['total_qty'] ?? 0); ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 bg-info text-white shadow-sm border-0 text-center">
                <small class="text-uppercase opacity-75">Best Category</small>
                <h3><?php echo $best_cat; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <?php if(mysqli_num_rows($low_stock_items) > 0): ?>
                <div class="card p-3 bg-danger text-white shadow-sm border-0 text-center">
                    <small class="text-uppercase opacity-75">Stock Alerts</small>
                    <h3><?php echo mysqli_num_rows($low_stock_items); ?> Low</h3>
                </div>
            <?php else: ?>
                <div class="card p-3 bg-secondary text-white shadow-sm border-0 text-center">
                    <small class="text-uppercase opacity-75">Stock Status</small>
                    <h3>Healthy</h3>
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
                <h5 class="text-secondary">Top 5 Products (Units)</h5>
                <canvas id="topProductsChart"></canvas>
            </div>
            <div class="card p-4 mb-4 shadow-sm border-0">
                <h5 class="text-secondary">Category Performance</h5>
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card p-4 mb-4 shadow-sm border-0">
        <div class="d-flex justify-content-between mb-3">
            <h5 class="text-secondary">Recent Transactions</h5>
            <a href="inventory_sale.php" class="btn btn-sm btn-success">+ New Sale</a>
        </div>
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $recent_sales = mysqli_query($conn, "SELECT s.*, i.Product_Name FROM sales s JOIN inventory i ON s.product_id = i.id ORDER BY s.sale_date DESC LIMIT 5");
                if(mysqli_num_rows($recent_sales) > 0){
                    while($sale = mysqli_fetch_assoc($recent_sales)){
                        echo "<tr>
                                <td><small class='text-muted'>".date('M d, H:i', strtotime($sale['sale_date']))."</small></td>
                                <td><b>{$sale['Product_Name']}</b></td>
                                <td>{$sale['quantity_sold']}</td>
                                <td>$".number_format($sale['sale_price'], 2)."</td>
                                <td class='fw-bold text-success'>$".number_format($sale['quantity_sold'] * $sale['sale_price'], 2)."</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center text-muted'>No sales recorded yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Data Setup
const topLabels = <?php echo json_encode($top_prod_names); ?>;
const topValues = <?php echo json_encode($top_prod_sales); ?>;
const dailyLabels = <?php echo json_encode($daily_labels); ?>;
const dailyValues = <?php echo json_encode($daily_data); ?>;
const monthlyValues = <?php echo json_encode(array_values($monthly_revenue)); ?>;
const catLabels = <?php echo json_encode($cat_labels); ?>;
const catValues = <?php echo json_encode($cat_data); ?>;

// Theme Colors
const colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];

// 1. Top Products (Doughnut)
new Chart(document.getElementById('topProductsChart'), {
    type: 'doughnut',
    data: {
        labels: topLabels,
        datasets: [{
            data: topValues,
            backgroundColor: colors,
            hoverOffset: 4
        }]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
});

// 2. Daily Sales (Line)
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
            tension: 0.3
        }]
    }
});

// 3. Monthly Sales (Bar)
new Chart(document.getElementById('monthlySalesChart'), {
    type: 'bar',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{
            label: 'Monthly Revenue',
            data: monthlyValues,
            backgroundColor: '#4BC0C0',
            borderRadius: 4
        }]
    }
});

// 4. Category Performance (Pie)
if(catLabels.length > 0) {
    new Chart(document.getElementById('categoryChart'), {
        type: 'pie',
        data: {
            labels: catLabels,
            datasets: [{
                data: catValues,
                backgroundColor: colors
            }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
    });
}
</script>

<?php include 'includes/footer.php'; ?>