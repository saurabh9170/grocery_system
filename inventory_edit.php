<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

$id = intval($_GET['id']);
$item = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM inventory WHERE id='$id'"));

if(isset($_POST['update'])){
    $product = mysqli_real_escape_string($conn, $_POST['product_name']);
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);
    $expiry = $_POST['expiry_date'];

    mysqli_query($conn,"UPDATE inventory SET product_name='$product', quantity='$quantity', price='$price', expiry_date='$expiry' WHERE id='$id'");
    header("Location: inventory.php");
}
?>

<h2 class="mb-4">Edit Inventory Item</h2>
<form method="POST" class="w-50">
  <div class="mb-3">
    <label>Product Name</label>
    <input type="text" name="product_name" class="form-control" value="<?php echo $item['product_name'];?>" required>
  </div>
  <div class="mb-3">
    <label>Quantity</label>
    <input type="number" name="quantity" class="form-control" value="<?php echo $item['quantity'];?>" required>
  </div>
  <div class="mb-3">
    <label>Price ($)</label>
    <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $item['price'];?>" required>
  </div>
  <div class="mb-3">
    <label>Expiry Date</label>
    <input type="date" name="expiry_date" class="form-control" value="<?php echo $item['expiry_date'];?>" required>
  </div>
  <button type="submit" name="update" class="btn btn-primary">Update Item</button>
</form>
</div>
</body>
</html>