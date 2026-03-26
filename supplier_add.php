<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

// Handle form submit
if($_SERVER['REQUEST_METHOD']=='POST'){
    $supplier_name = mysqli_real_escape_string($conn,$_POST['supplier_name']);
    $contact_name = mysqli_real_escape_string($conn,$_POST['contact_name']);
    $contact_email = mysqli_real_escape_string($conn,$_POST['contact_email']);
    $contact_phone = mysqli_real_escape_string($conn,$_POST['contact_phone']);
    $address = mysqli_real_escape_string($conn,$_POST['address']);

    mysqli_query($conn, "INSERT INTO suppliers (supplier_name, contact_name, contact_email, contact_phone, address) 
        VALUES ('$supplier_name','$contact_name','$contact_email','$contact_phone','$address')");

    header("Location: supplier_performance.php");
    exit;
}
?>

<h2 class="dashboard-header">Add New Supplier</h2>
<form method="post">
<input type="text" name="supplier_name" placeholder="Supplier Name" class="form-control mb-2" required>
<input type="text" name="contact_name" placeholder="Contact Name" class="form-control mb-2">
<input type="email" name="contact_email" placeholder="Email" class="form-control mb-2">
<input type="text" name="contact_phone" placeholder="Phone" class="form-control mb-2">
<input type="text" name="address" placeholder="Address" class="form-control mb-2">
<button class="btn btn-success">Add Supplier</button>
</form>

<?php include 'includes/footer.php'; ?>