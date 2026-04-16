<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

$inventory_query = mysqli_query($conn,"SELECT * FROM inventory");
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

$graph_labels = [];
$graph_values = [];
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
    $avg = ($days_counted > 0) ? round($total / $days_counted, 2) : 0;
    $graph_values[] = $avg;
}
?>

<div class="container mt-4">
    <h2 class="mb-2">Demand Forecasting</h2>
    <p class="text-muted mb-4">Predictions based on current stock levels and 30-day sales trends.</p>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <h4 class="mb-4">Inventory & Recommended Orders</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
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
                        mysqli_data_seek($inventory_query, 0);
                        while($row = mysqli_fetch_assoc($inventory_query)){
                            $stock = $row['Stock_Quantity'];
                            $level = $row['Reorder_Level'];
                            $reorder_qty = $row['Reorder_Quantity'];

                            if ($stock <= $level) {
                                $action = "<span class='badge bg-danger'>Order " . $reorder_qty . " Units</span>";
                                $status = "<span class='text-danger'>Low Stock</span>";
                            } else {
                                $action = "<span>No action needed</span>";
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
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <h4>Sales Trend (Moving Average)</h4>
                <p class="small text-muted">Daily units sold for top 5 products</p>
                <canvas id="salesTrendChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('salesTrendChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($graph_labels); ?>,
        datasets: [{
            label: 'Avg Daily Sales',
            data: <?php echo json_encode($graph_values); ?>,
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            borderWidth: 3,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { labels: { color: '#f8fafc' } }
        },
        scales: {
            y: { 
                beginAtZero: true,
                ticks: { color: '#94a3b8' },
                grid: { color: 'rgba(255, 255, 255, 0.05)' }
            },
            x: { 
                ticks: { color: '#94a3b8' },
                grid: { display: false }
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>