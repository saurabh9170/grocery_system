<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

if($_SERVER['REQUEST_METHOD']=='POST'){
    // Capture all fields from the form
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $warehouse_location = mysqli_real_escape_string($conn, $_POST['warehouse_location']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $product_id_val = mysqli_real_escape_string($conn, $_POST['product_id']); // External ID
    $supplier_id = intval($_POST['supplier_id']);
    
    $date_received = $_POST['date_received'];
    $last_order_date = $_POST['last_order_date'];
    $expiration_date = $_POST['expiration_date'];
    
    $stock_quantity = intval($_POST['stock_quantity']);
    $reorder_level = intval($_POST['reorder_level']);
    $reorder_quantity = intval($_POST['reorder_quantity']);
    $unit_price = floatval($_POST['unit_price']);
    $sales_volume = floatval($_POST['sales_volume']);
    $inventory_turnover = floatval($_POST['inventory_turnover']);
    $percentage = mysqli_real_escape_string($conn, $_POST['percentage']);

    // Get supplier name from suppliers table using the ID
    $supplier_query = mysqli_query($conn,"SELECT supplier_name FROM suppliers WHERE id=$supplier_id");
    $supplier = mysqli_fetch_assoc($supplier_query);
    $supplier_name = mysqli_real_escape_string($conn, $supplier['supplier_name']);

    // Insert into inventory using all columns from the CSV structure
    $query = "INSERT INTO inventory (
        Product_Name, Catagory, Supplier_Name, Warehouse_Location, Status, 
        Product_ID, Supplier_ID, Date_Received, Last_Order_Date, Expiration_Date, 
        Stock_Quantity, Reorder_Level, Reorder_Quantity, Unit_Price, 
        Sales_Volume, Inventory_Turnover_Rate, percentage
    ) VALUES (
        '$product_name', '$category', '$supplier_name', '$warehouse_location', '$status', 
        '$product_id_val', '$supplier_id', '$date_received', '$last_order_date', '$expiration_date', 
        '$stock_quantity', '$reorder_level', '$reorder_quantity', '$unit_price', 
        '$sales_volume', '$inventory_turnover', '$percentage'
    )";

    if(mysqli_query($conn, $query)){
        header("Location: inventory.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Fetch all suppliers for dropdown
$suppliers = mysqli_query($conn,"SELECT * FROM suppliers");
?>

<h2 class="dashboard-header">Add New Inventory Item</h2>
<form method="post" class="p-4 border rounded bg-light">
    <div class="row">
        <div class="col-md-6">
            <label>Product Name</label>
            <input type="text" name="product_name" class="form-control mb-2" required>
            
            <label>Category</label>
            <input type="text" name="category" class="form-control mb-2" required>
            
            <label>Product ID (SKU/Code)</label>
            <input type="text" name="product_id" class="form-control mb-2" required>
            
            <label>Supplier</label>
            <select name="supplier_id" class="form-control mb-2" required>
                <option value="">-- Select Supplier --</option>
                <?php while($row = mysqli_fetch_assoc($suppliers)){ ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['supplier_name']; ?></option>
                <?php } ?>
            </select>

            <label>Warehouse Location</label>
            <input type="text" name="warehouse_location" class="form-control mb-2">

            <label>Status</label>
            <select name="status" class="form-control mb-2">
                <option value="Active">Active</option>
                <option value="Discontinued">Discontinued</option>
                <option value="Backordered">Backordered</option>
            </select>
        </div>

        <div class="col-md-6">
            <label>Stock Quantity</label>
            <input type="number" name="stock_quantity" class="form-control mb-2" required>

            <label>Unit Price</label>
            <input type="number" step="0.01" name="unit_price" class="form-control mb-2" required>

            <label>Reorder Level</label>
            <input type="number" name="reorder_level" class="form-control mb-2">

            <label>Reorder Quantity</label>
            <input type="number" name="reorder_quantity" class="form-control mb-2">

            <label>Date Received</label>
            <input type="date" name="date_received" class="form-control mb-2">

            <label>Last Order Date</label>
            <input type="date" name="last_order_date" class="form-control mb-2">

            <label>Expiration Date</label>
            <input type="date" name="expiration_date" class="form-control mb-2">
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <label>Sales Volume</label>
            <input type="number" step="0.01" name="sales_volume" class="form-control mb-2">
        </div>
        <div class="col-md-4">
            <label>Turnover Rate</label>
            <input type="number" step="0.01" name="inventory_turnover" class="form-control mb-2">
        </div>
        <div class="col-md-4">
            <label>Percentage (%)</label>
            <input type="text" name="percentage" class="form-control mb-2">
        </div>
    </div>

    <div class="mt-4">
        <button class="btn btn-primary btn-lg w-100">Save Item to Inventory</button>
    </div>
</form>

<?php include 'includes/footer.php'; ?>