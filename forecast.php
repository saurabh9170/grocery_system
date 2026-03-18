<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

$query = mysqli_query($conn,"SELECT product_name, quantity FROM inventory");
?>

<h2 class="mb-4">Forecasting</h2>
<p>This is a simple demand forecast based on current inventory levels.</p>

<table class="table table-bordered">
  <thead>
    <tr>
      <th>Product</th>
      <th>Current Quantity</th>
      <th>Recommended Order</th>
    </tr>
  </thead>
  <tbody>
  <?php
  while($row = mysqli_fetch_assoc($query)){
      $recommended = ($row['quantity'] < 20) ? 50 - $row['quantity'] : 0;
      echo "<tr>
              <td>{$row['product_name']}</td>
              <td>{$row['quantity']}</td>
              <td>{$recommended}</td>
            </tr>";
  }
  ?>
  </tbody>
</table>
</div>
</body>
</html>