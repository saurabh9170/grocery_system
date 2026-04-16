<?php 
include 'includes/session.php'; 
include 'includes/db.php'; 
include 'includes/header.php'; 

$query = "SELECT 
            i.Supplier_ID, 
            i.Supplier_Name, 
            i.id AS product_id, 
            i.Product_Name,
            i.Stock_Quantity
          FROM inventory i
          LEFT JOIN suppliers s ON i.Supplier_ID = s.id
          ORDER BY i.Supplier_ID ASC";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2>Supplier-Product Distribution</h2>
        <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
            <i class="bi bi-printer"></i> Print Report
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">Supplier ID</th>
                            <th>Supplier Name</th>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Current Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">
                                        #<?php echo htmlspecialchars($row['Supplier_ID'] ?? 'N/A'); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['Supplier_Name'] ?? 'Unassigned'); ?></td>
                                    <td><?php echo $row['product_id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['Product_Name']); ?></td>
                                    <td><?php echo $row['Stock_Quantity']; ?></td>
                                    <td>
                                        <?php if($row['Stock_Quantity'] <= 10): ?>
                                            <span class="badge bg-danger">Low Stock</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">In Stock</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>