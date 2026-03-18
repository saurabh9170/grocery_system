<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

$total_sales = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(quantity*price) as total FROM inventory"));
?>

<h2 class="mb-4">Sales Analytics</h2>
<div class="card p-3 bg-info text-white mb-4">
  <h5>Total Potential Sales Revenue</h5>
  <p class="fs-3">$<?php echo number_format($total_sales['total'],2); ?></p>
</div>

<div class="table-responsive">
  <table class="table table-striped table-bordered">
    <thead>
      <tr>
        <th>Product</th>
        <th>Quantity</th>
        <th>Price ($)</th>
        <th>Total ($)</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $query = mysqli_query($conn,"SELECT * FROM inventory");
      while($row = mysqli_fetch_assoc($query)){
          $total = $row['quantity'] * $row['price'];
          echo "<tr>
                  <td>{$row['product_name']}</td>
                  <td>{$row['quantity']}</td>
                  <td>{$row['price']}</td>
                  <td>{$total}</td>
                </tr>";
      }
      ?>
    </tbody>
  </table>
</div>
</div>
</body>
</html>