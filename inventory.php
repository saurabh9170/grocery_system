<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

// --- NEW: Count items at or below reorder levels for the alert banner ---
$low_stock_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM inventory WHERE Stock_Quantity <= Reorder_Level");
$low_stock_data = mysqli_fetch_assoc($low_stock_query);
$num_low_stock = $low_stock_data['total'] ?? 0;
?>

<div class="container-fluid mt-4">
    <h2 class="dashboard-header mb-4">Inventory Management</h2>

    <?php if($num_low_stock > 0): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            <strong>Attention!</strong> You have <?php echo $num_low_stock; ?> item(s) at or below reorder levels.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between mb-3">
        <a href="inventory_add.php" class="btn btn-success">+ Add New Item</a>
        <a href="sales.php" class="btn btn-outline-primary">View Sales Dashboard</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered table-sm">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Product Name</th>
              <th>Category</th>
              <th>ID (SKU)</th>
              <th>Warehouse</th>
              <th>Status</th>
              <th>Stock</th>
              <th>Price</th>
              <th>Reorder Lvl</th>
              <th>Expiry</th>
              <th>Supplier</th>
              <th>Turnover</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $query = mysqli_query($conn, "SELECT * FROM inventory ORDER BY id DESC");
          $i = 1;
          while($row = mysqli_fetch_assoc($query)){
              // Logic for dynamic stock status
              $stock = $row['Stock_Quantity'];
              $reorder = $row['Reorder_Level'];
              
              if($stock <= 0) {
                  $status_badge = "<span class='badge bg-danger'>Out of Stock</span>";
                  $row_class = "table-danger";
              } elseif($stock <= $reorder) {
                  $status_badge = "<span class='badge bg-warning text-dark'>Low Stock</span>";
                  $row_class = "table-warning";
              } else {
                  $status_badge = "<span class='badge bg-success'>Healthy</span>";
                  $row_class = "";
              }

              echo "<tr class='{$row_class}'>
                      <td>{$i}</td>
                      <td><b>{$row['Product_Name']}</b></td>
                      <td>{$row['Catagory']}</td>
                      <td><small>{$row['Product_ID']}</small></td>
                      <td>{$row['Warehouse_Location']}</td>
                      <td>{$status_badge}</td>
                      <td class='fw-bold'>{$stock}</td>
                      <td>$" . number_format($row['Unit_Price'], 2) . "</td>
                      <td>{$reorder}</td>
                      <td>{$row['Expiration_Date']}</td>
                      <td>{$row['Supplier_Name']}</td>
                      <td>{$row['Inventory_Turnover_Rate']}</td>
                      <td>
                        <div class='btn-group'>
                            <a href='inventory_sell.php?id={$row['id']}' class='btn btn-sm btn-success'>Sale</a>
                            <a href='inventory_edit.php?id={$row['id']}' class='btn btn-sm btn-primary'>Edit</a>
                            <a href='inventory_delete.php?id={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'>Del</a>
                        </div>
                      </td>
                    </tr>";
              $i++;
          }
          ?>
          </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>