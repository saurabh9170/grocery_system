<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

$total_inventory = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM inventory")); 
$low_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM inventory WHERE quantity < 10"));
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT product_name) as total FROM inventory"));
?>

<h2 class="mb-4">Dashboard</h2>
<div class="row g-3">
  <div class="col-md-4">
    <div class="card text-white bg-primary h-100 position-relative">
      <div class="card-body">
        <h5 class="card-title">Total Inventory Items</h5>
        <p class="card-text fs-3"><?php echo $total_inventory['total']; ?></p>
        <a href="inventory_add.php" class="btn btn-light position-absolute bottom-0 end-0 m-3">+ Add Item</a>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card text-white bg-danger h-100">
      <div class="card-body">
        <h5 class="card-title">Low Stock Items (&lt;10)</h5>
        <p class="card-text fs-3"><?php echo $low_stock['total']; ?></p>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card text-white bg-success h-100">
      <div class="card-body">
        <h5 class="card-title">Distinct Products</h5>
        <p class="card-text fs-3"><?php echo $total_products['total']; ?></p>
      </div>
    </div>
  </div>
</div>

<div class="mt-4">
  <h4>Recent Inventory</h4>
  <table class="table table-striped">
    <thead>
      <tr>
        <th>#</th>
        <th>Product</th>
        <th>Quantity</th>
        <th>Price ($)</th>
        <th>Expiry</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $query = mysqli_query($conn,"SELECT * FROM inventory ORDER BY id DESC LIMIT 5");
      $i = 1;
      while($row = mysqli_fetch_assoc($query)){
          echo "<tr>
                  <td>{$i}</td>
                  <td>{$row['product_name']}</td>
                  <td>{$row['quantity']}</td>
                  <td>{$row['price']}</td>
                  <td>{$row['expiry_date']}</td>
                </tr>";
          $i++;
      }
      ?>
    </tbody>
  </table>
</div>

</div>
</body>
</html>