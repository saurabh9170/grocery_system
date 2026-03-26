<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

if($_SERVER['REQUEST_METHOD']=='POST'){
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $supplier_id = $_POST['supplier_id']; // supplier select
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);
    $expiry_date = $_POST['expiry_date'];

    // Get supplier name from suppliers table
    $supplier_query = mysqli_query($conn,"SELECT supplier_name FROM suppliers WHERE id=$supplier_id");
    $supplier = mysqli_fetch_assoc($supplier_query);
    $supplier_name = $supplier['supplier_name'];

    // Insert into inventory
    mysqli_query($conn,"INSERT INTO inventory (product_name, category, supplier_id, supplier_name, quantity, price, expiry_date)
        VALUES ('$product_name','$category','$supplier_id','$supplier_name','$quantity','$price','$expiry_date')");
    
    header("Location: inventory.php");
    exit;
}

// Fetch all suppliers for dropdown
$suppliers = mysqli_query($conn,"SELECT * FROM suppliers");
?>

<h2 class="dashboard-header">Add New Item</h2>
<form method="post">
    <input type="text" name="product_name" placeholder="Product Name" class="form-control mb-2" required>
    <input type="text" name="category" placeholder="Category" class="form-control mb-2" required>

    <!-- Supplier Dropdown -->
    <select name="supplier_id" class="form-control mb-2" required>
        <option value="">-- Select Supplier --</option>
        <?php while($row = mysqli_fetch_assoc($suppliers)){ ?>
            <option value="<?php echo $row['id']; ?>"><?php echo $row['supplier_name']; ?></option>
        <?php } ?>
    </select>

    <input type="number" name="quantity" placeholder="Quantity" class="form-control mb-2" required>
    <input type="number" step="0.01" name="price" placeholder="Price" class="form-control mb-2" required>
    <input type="date" name="expiry_date" class="form-control mb-2" required>

    <button class="btn btn-success">Add Item</button>
</form>

<?php include 'includes/footer.php'; ?>