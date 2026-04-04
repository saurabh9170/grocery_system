<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

// 1. Fetch inventory data (Updated column names)
$inventory_query = mysqli_query($conn,"SELECT * FROM inventory");

// 2. Fetch last 30 days sales for forecasting
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

// 3. Prepare data for graph (Moving Average)
$graph_labels = [];
$graph_values = [];
// Updated: Product_Name
$graph_products = mysqli_query($conn,"SELECT DISTINCT Product_Name, id FROM inventory LIMIT 5"); 

while($prod = mysqli_fetch_assoc($graph_products)){
    $graph_labels[] = $prod['Product_Name'];
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

<div class="container mt-4">
    <h2 class="mb-4">Demand Forecasting</h2>
    <p class="text-muted">Predictions based on current stock levels vs. your defined reorder points and 30-day sales trends.</p>

    <div class="row">
        <div class="col-md-12">
            <div class="card p-3 mb-4">
                <h4>Inventory & Recommended Orders</h4>
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Current Stock</th>
                            <th>Reorder Level</th>
                            <th>Status</th>
                            <th>Recommended Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    mysqli_data_seek($inventory_query, 0); // Reset pointer
                    while($row = mysqli_fetch_assoc($inventory_query)){
                        // NEW LOGIC: Use the Reorder_Level from your database
                        $stock = $row['Stock_Quantity'];
                        $level = $row['Reorder_Level'];
                        $reorder_qty = $row['Reorder_Quantity'];

                        if ($stock <= $level) {
                            $action = "<span class='badge bg-danger'>Order " . $reorder_qty . " Units</span>";
                            $status = "<span class='text-danger'>Low Stock</span>";
                        } else {
                            $action = "<span class='text-muted'>No action needed</span>";
                            $status = "<span class='text-success'>Healthy</span>";
                        }

                        echo "<tr>
                                <td>{$row['Product_Name']}</td>
                                <td>{$stock}</td>
                                <td>{$level}</td>
                                <td>{$status}</td>
                                <td>{$action}</td>
                              </tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card p-3">
                <h4>Sales Trend (Moving Average)</h4>
                <p class="small text-muted">Average daily units sold for top 5 products</p>
                <canvas id="salesTrendChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('salesTrendChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($graph_labels); ?>,
        datasets: [{
            label: 'Avg Daily Sales (Units)',
            data: <?php echo json_encode($graph_values); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 3,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { 
                beginAtZero: true,
                title: { display: true, text: 'Units Sold' }
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>