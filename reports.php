<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

$query = mysqli_query($conn,"SELECT * FROM inventory");
?>

<h2 class="mb-4">Export Reports</h2>
<a href="#" class="btn btn-primary mb-3">Export CSV</a>
<a href="#" class="btn btn-secondary mb-3">Export PDF</a>

<table class="table table-bordered">
  <thead>
    <tr>
      <th>Product</th>
      <th>Quantity</th>
      <th>Price ($)</th>
      <th>Expiry Date</th>
    </tr>
  </thead>
  <tbody>
  <?php
  while($row = mysqli_fetch_assoc($query)){
      echo "<tr>
              <td>{$row['product_name']}</td>
              <td>{$row['quantity']}</td>
              <td>{$row['price']}</td>
              <td>{$row['expiry_date']}</td>
            </tr>";
  }
  ?>
  </tbody>
</table>
</div>
</body>
</html>