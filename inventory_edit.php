<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

$id = intval($_GET['id']);
$item_query = mysqli_query($conn, "SELECT * FROM inventory WHERE id='$id'");
$item = mysqli_fetch_assoc($item_query);

// 1. Check if the button was clicked
if(isset($_POST['update'])){
    
    // 2. Map form names to variables and sanitize
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $warehouse = mysqli_real_escape_string($conn, $_POST['warehouse_location']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $p_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    
    $stock = intval($_POST['stock_quantity']);
    $price = floatval($_POST['unit_price']);
    $reorder_lvl = intval($_POST['reorder_level']);
    $reorder_qty = intval($_POST['reorder_quantity']);
    
    $d_received = $_POST['date_received'];
    $expiry = $_POST['expiration_date'];
    $sales = floatval($_POST['sales_volume']);
    $turnover = floatval($_POST['inventory_turnover']);
    $perc = mysqli_real_escape_string($conn, $_POST['percentage']);

    // 3. The SQL Update Query (Note the exact Column Names)
    $sql = "UPDATE inventory SET 
            Product_Name = '$product_name', 
            Catagory = '$category', 
            Warehouse_Location = '$warehouse', 
            Status = '$status', 
            Product_ID = '$p_id', 
            Date_Received = '$d_received', 
            Expiration_Date = '$expiry', 
            Stock_Quantity = '$stock', 
            Reorder_Level = '$reorder_lvl', 
            Reorder_Quantity = '$reorder_qty', 
            Unit_Price = '$price', 
            Sales_Volume = '$sales', 
            Inventory_Turnover_Rate = '$turnover', 
            percentage = '$perc' 
            WHERE id = $id";

    if(mysqli_query($conn, $sql)){
        echo "<script>alert('Update Successful!'); window.location='inventory.php';</script>";
        exit;
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
?>

<h2 class="mb-4">Edit Item: <?php echo $item['Product_Name']; ?></h2>

<form method="POST">
    <div class="row">
        <div class="col-md-6">
            <label>Product Name</label>
            <input type="text" name="product_name" class="form-control mb-2" value="<?php echo $item['Product_Name']; ?>" required>
            
            <label>Category</label>
            <input type="text" name="category" class="form-control mb-2" value="<?php echo $item['Catagory']; ?>" required>
            
            <label>Status</label>
            <select name="status" class="form-control mb-2">
                <option value="Active" <?php echo ($item['Status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                <option value="Discontinued" <?php echo ($item['Status'] == 'Discontinued') ? 'selected' : ''; ?>>Discontinued</option>
                <option value="Backordered" <?php echo ($item['Status'] == 'Backordered') ? 'selected' : ''; ?>>Backordered</option>
            </select>

            <label>Unit Price ($)</label>
            <input type="number" step="0.01" name="unit_price" class="form-control mb-2" value="<?php echo $item['Unit_Price']; ?>" required>
        </div>

        <div class="col-md-6">
            <label>Stock Quantity</label>
            <input type="number" name="stock_quantity" class="form-control mb-2" value="<?php echo $item['Stock_Quantity']; ?>" required>

            <label>Warehouse Location</label>
            <input type="text" name="warehouse_location" class="form-control mb-2" value="<?php echo $item['Warehouse_Location']; ?>">

            <label>Expiration Date</label>
            <input type="date" name="expiration_date" class="form-control mb-2" value="<?php echo $item['Expiration_Date']; ?>">

            <input type="hidden" name="product_id" value="<?php echo $item['Product_ID']; ?>">
            <input type="hidden" name="reorder_level" value="<?php echo $item['Reorder_Level']; ?>">
            <input type="hidden" name="reorder_quantity" value="<?php echo $item['Reorder_Quantity']; ?>">
            <input type="hidden" name="date_received" value="<?php echo $item['Date_Received']; ?>">
            <input type="hidden" name="sales_volume" value="<?php echo $item['Sales_Volume']; ?>">
            <input type="hidden" name="inventory_turnover" value="<?php echo $item['Inventory_Turnover_Rate']; ?>">
            <input type="hidden" name="percentage" value="<?php echo $item['percentage']; ?>">
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" name="update" class="btn btn-primary btn-lg w-100">Save Changes</button>
        <a href="inventory.php" class="btn btn-link w-100 mt-2">Back to List</a>
    </div>
</form>

<?php include 'includes/footer.php'; ?>