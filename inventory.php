<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';
?>

<h2 class="dashboard-header mb-4">Inventory Management</h2>
<a href="inventory_add.php" class="btn btn-success mb-3">+ Add New Item</a>

<div class="table-responsive">
    <table class="table table-striped table-bordered table-sm">
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
      // Selecting all columns from the updated inventory table
      $query = mysqli_query($conn, "SELECT * FROM inventory ORDER BY id DESC");
      $i = 1;
      while($row = mysqli_fetch_assoc($query)){
          echo "<tr>
                  <td>{$i}</td>
                  <td><b>{$row['Product_Name']}</b></td>
                  <td>{$row['Catagory']}</td>
                  <td><small>{$row['Product_ID']}</small></td>
                  <td>{$row['Warehouse_Location']}</td>
                  <td><span class='badge bg-info text-dark'>{$row['Status']}</span></td>
                  <td>{$row['Stock_Quantity']}</td>
                  <td>$ " . $row['Unit_Price'] . "</td>
                  <td>{$row['Reorder_Level']}</td>
                  <td>{$row['Expiration_Date']}</td>
                  <td>{$row['Supplier_Name']}</td>
                  <td>{$row['Inventory_Turnover_Rate']}</td>
                  <td>
                    <div class='btn-group'>
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

<?php include 'includes/footer.php'; ?>