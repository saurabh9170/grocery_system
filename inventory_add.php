<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

if(isset($_POST['add'])){
    $product = mysqli_real_escape_string($conn, $_POST['product_name']);
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);
    $expiry = $_POST['expiry_date'];

    mysqli_query($conn,"INSERT INTO inventory (product_name, quantity, price, expiry_date) VALUES ('$product', '$quantity', '$price', '$expiry')");
    header("Location: inventory.php");
}
?>

<h2 class="mb-4">Add Inventory Item</h2>
<form method="POST" class="w-50">
  <div class="mb-3">
    <label>Product Name</label>
    <input type="text" name="product_name" class="form-control" required>
  </div>
  <div class="mb-3">
    <label>Quantity</label>
    <input type="number" name="quantity" class="form-control" required>
  </div>
  <div class="mb-3">
    <label>Price ($)</label>
    <input type="number" step="0.01" name="price" class="form-control" required>
  </div>
  <div class="mb-3">
    <label>Expiry Date</label>
    <input type="date" name="expiry_date" class="form-control" required>
  </div>
  <button type="submit" name="add" class="btn btn-success">Add Item</button>
</form>
</div>
</body>
</html>