<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';

if(isset($_POST['submit'])){
    $name = $_POST['supplier_name'];
    $contact = $_POST['contact_name'];
    $email = $_POST['contact_email'];
    $phone = $_POST['contact_phone'];
    $address = $_POST['address'];

    mysqli_query($conn, "INSERT INTO suppliers (supplier_name, contact_name, contact_email, contact_phone, address) 
                         VALUES ('$name', '$contact', '$email', '$phone', '$address')");
    header("Location: supplier_performance.php");
}
?>

<h2 class="dashboard-header">Add Supplier</h2>

<form method="post">
    <input type="text" name="supplier_name" placeholder="Supplier Name" class="form-control mb-2" required>
    <input type="text" name="contact_name" placeholder="Contact Name" class="form-control mb-2">
    <input type="email" name="contact_email" placeholder="Email" class="form-control mb-2">
    <input type="text" name="contact_phone" placeholder="Contact Number" class="form-control mb-2">
    <textarea name="address" placeholder="Address" class="form-control mb-2"></textarea>
    <button name="submit" class="btn btn-success">Add Supplier</button>
</form>

<?php include 'includes/footer.php'; ?>