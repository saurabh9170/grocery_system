<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

// Check for ID
if (!isset($_GET['id'])) {
    header("Location: inventory.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$item_query = mysqli_query($conn, "SELECT * FROM inventory WHERE id = '$id'");
$item = mysqli_fetch_assoc($item_query);

if (!$item) {
    die("<div class='container mt-5'><div class='alert alert-danger'>Item not found.</div></div>");
}

// Process Form
if (isset($_POST['process_sale'])) {
    $qty = intval($_POST['quantity']);
    $price = $item['Unit_Price'];
    $current_stock = $item['Stock_Quantity'];

    if ($qty > 0 && $qty <= $current_stock) {
        mysqli_begin_transaction($conn);
        try {
            // 1. Update Stock
            mysqli_query($conn, "UPDATE inventory SET Stock_Quantity = Stock_Quantity - $qty WHERE id = '$id'");

            // 2. Insert Sale Record
            $sale_sql = "INSERT INTO sales (customer_id, product_id, quantity_sold, sale_price, sale_date) 
                         VALUES (1, '$id', '$qty', '$price', NOW())";
            
            if (!mysqli_query($conn, $sale_sql)) {
                throw new Exception(mysqli_error($conn));
            }

            mysqli_commit($conn);
            // Redirect back to inventory with success status
            header("Location: inventory.php?status=sale_complete");
            exit();

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Transaction Error: " . $e->getMessage();
        }
    } else {
        $error = "Invalid quantity. Only " . $current_stock . " units available.";
    }
}
?>

<div class="container mt-5">
    <div class="card shadow border-0 mx-auto" style="max-width: 450px;">
        <div class="card-header bg-success text-white py-3">
            <h5 class="mb-0 text-center">Process New Sale</h5>
        </div>
        <div class="card-body p-4">
            <h4 class="text-center mb-1"><?php echo htmlspecialchars($item['Product_Name']); ?></h4>
            <p class="text-center text-muted small mb-4">SKU: <?php echo $item['Product_ID']; ?></p>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger small"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Unit Price</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="text" class="form-control bg-light" value="<?php echo number_format($item['Unit_Price'], 2); ?>" readonly>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Quantity to Sell (Max: <?php echo $item['Stock_Quantity']; ?>)</label>
                    <input type="number" name="quantity" class="form-control form-control-lg" min="1" max="<?php echo $item['Stock_Quantity']; ?>" value="1" required autofocus>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" name="process_sale" class="btn btn-success btn-lg">Complete Transaction</button>
                    <a href="inventory.php" class="btn btn-light border">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>