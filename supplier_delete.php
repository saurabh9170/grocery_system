<?php
include 'includes/session.php';
include 'includes/db.php';

if(isset($_GET['id'])){
    $id = intval($_GET['id']);

    // Delete supplier
    mysqli_query($conn, "DELETE FROM suppliers WHERE id=$id");

    // Optional: Remove supplier reference from inventory
    mysqli_query($conn, "UPDATE inventory SET supplier_id=NULL, supplier_name=NULL WHERE supplier_id=$id");

    header("Location: supplier_performance.php");
    exit;
}
?>