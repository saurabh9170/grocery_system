<?php 
include 'includes/session.php'; 
include 'includes/db.php'; 
include 'includes/header.php'; 

// --- CONFIGURATION ---
$threshold = 10; 
$debug_mode = false; // Set to true to test the UI if your database is empty

// 1. Inventory Summary Query
$inv_query = mysqli_query($conn,"SELECT COUNT(*) AS total_products, SUM(Stock_Quantity) AS total_quantity FROM inventory");
$inv = mysqli_fetch_assoc($inv_query);

// 2. Sales Summary Query
$sales_query = mysqli_query($conn,"SELECT SUM(quantity_sold) AS total_sold, SUM(sale_price*quantity_sold) AS total_revenue FROM sales");
$sales = mysqli_fetch_assoc($sales_query);

// 3. LOW STOCK LOGIC
$low_stock_query = mysqli_query($conn, "SELECT Product_Name, Stock_Quantity FROM inventory WHERE Stock_Quantity <= $threshold");
$low_stock_items = [];

if($low_stock_query) {
    while($row = mysqli_fetch_assoc($low_stock_query)) {
        $low_stock_items[] = $row;
    }
}

// 4. Top Selling Products Query
$top_products = [];
$top = mysqli_query($conn,"SELECT i.Product_Name, SUM(s.quantity_sold) AS total_sold
                           FROM sales s JOIN inventory i ON s.product_id=i.id
                           GROUP BY s.product_id ORDER BY total_sold DESC LIMIT 5");

if($top) {
    while($row = mysqli_fetch_assoc($top)) {
        $top_products[] = $row;
    }
}
?>

<div class="container-fluid px-4">
    <h2 class="dashboard-header my-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>

    <?php if (!empty($low_stock_items) || $debug_mode): ?>
        <?php 
            // If debug mode is on and list is empty, create a fake item
            if($debug_mode && empty($low_stock_items)) {
                $low_stock_items[] = ['Product_Name' => 'Debug Sample Item', 'Stock_Quantity' => 5];
            }
        ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-left: 8px solid #dc3545;">
            <div class="d-flex align-items-center mb-2">
                <strong class="fs-5"><i class="bi bi-speedometer2 me-2"></i> Stock Alert Levels</strong>
            </div>
            <p class="mb-3">The following products have dropped below the minimum threshold of <strong><?php echo $threshold; ?></strong> units:</p>
            
            <div class="row g-3">
                <?php foreach ($low_stock_items as $item): ?>
                    <div class="col-md-3">
                        <div class="card border-danger bg-light">
                            <div class="card-body p-2 d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark"><?php echo htmlspecialchars($item['Product_Name']); ?></span>
                                <span class="badge bg-danger rounded-pill"><?php echo $item['Stock_Quantity']; ?> left</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0 bg-primary text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small">Total Products</h6>
                    <h2 class="display-6 fw-bold"><?php echo $inv['total_products']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0 bg-info text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small">Total Stock Units</h6>
                    <h2 class="display-6 fw-bold"><?php echo number_format($inv['total_quantity'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0 bg-success text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small">Total Units Sold</h6>
                    <h2 class="display-6 fw-bold"><?php echo number_format($sales['total_sold'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0 bg-warning text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small">Total Revenue</h6>
                    <h2 class="display-6 fw-bold">$<?php echo number_format($sales['total_revenue'] ?? 0, 2); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4 p-4 shadow-sm border-0">
        <h5 class="mb-3 fw-bold text-secondary">Top 5 Selling Products</h5>
        <div style="height: 350px;">
            <canvas id="topProductsChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<script>
const ctx = document.getElementById('topProductsChart').getContext('2d');
const topProductsChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [<?php foreach($top_products as $p) echo "'".addslashes($p['Product_Name'])."',"; ?>],
        datasets: [{ 
            label: 'Total Units Sold', 
            data: [<?php foreach($top_products as $p) echo $p['total_sold'].","; ?>], 
            backgroundColor: 'rgba(13, 110, 253, 0.75)',
            borderColor: 'rgb(13, 110, 253)',
            borderWidth: 1,
            borderRadius: 5
        }]
    },
    options: { 
        maintainAspectRatio: false,
        responsive: true, 
        plugins: { 
            legend: { display: false } 
        }, 
        scales: { 
            y: { 
                beginAtZero: true,
                grid: { display: false }
            },
            x: {
                grid: { display: false }
            }
        } 
    }
});
</script>

<?php include 'includes/footer.php'; ?>