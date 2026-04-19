<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

// Check if ID is passed in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Fetch current data for this specific supplier
    $fetch_res = mysqli_query($conn, "SELECT * FROM suppliers WHERE id = '$id'");
    $row = mysqli_fetch_assoc($fetch_res);

    if (!$row) {
        die("<div class='alert alert-danger'>Supplier not found in database!</div>");
    }
} else {
    // This runs if the URL does NOT have ?id=X
    die("<div class='alert alert-warning'>Error: No supplier ID was provided. Please go back to the list and click Edit.</div>");
}

// Handle the Form Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name    = mysqli_real_escape_string($conn, $_POST['supplier_name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact_name']);
    $email   = mysqli_real_escape_string($conn, $_POST['contact_email']);
    $phone   = mysqli_real_escape_string($conn, $_POST['contact_phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $sql = "UPDATE suppliers SET 
            supplier_name = '$name', 
            contact_name = '$contact', 
            contact_email = '$email', 
            contact_phone = '$phone', 
            address = '$address' 
            WHERE id = '$id'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Supplier Updated!'); window.location.href='supplier_performance.php';</script>";
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<div class="container mt-4">
    <h2>Update Supplier: <?php echo htmlspecialchars($row['supplier_name']); ?></h2>
    <form method="post" class="mt-3">
        <div class="mb-3">
            <label>Supplier Name</label>
            <input type="text" name="supplier_name" value="<?php echo $row['supplier_name']; ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Contact Person</label>
            <input type="text" name="contact_name" value="<?php echo $row['contact_name']; ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="contact_email" value="<?php echo $row['contact_email']; ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="contact_phone" value="<?php echo $row['contact_phone']; ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control"><?php echo $row['address']; ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-success">Save Changes</button>
        <a href="supplier_performance.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>