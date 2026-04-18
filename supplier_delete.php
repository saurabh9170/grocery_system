<?php
include 'includes/session.php';
include 'includes/db.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // Step 1: Extract supplier name before deleting to maintain inventory integrity if needed
    $nameQuery = mysqli_query($conn, "SELECT supplier_name FROM suppliers WHERE id = '$id'");
    $supplier = mysqli_fetch_assoc($nameQuery);
    
    if ($supplier) {
        $supplierName = mysqli_real_escape_string($conn, $supplier['supplier_name']);

        // Step 2: Update inventory to remove links to this supplier
        // We use the Name or the ID string found in the inventory table
        mysqli_query($conn, "UPDATE inventory 
                            SET Supplier_ID = NULL, Supplier_Name = 'Unassigned' 
                            WHERE Supplier_Name = '$supplierName'");
    }

    // Step 3: Delete the supplier record
    $delete = mysqli_query($conn, "DELETE FROM suppliers WHERE id = '$id'");

    if ($delete) {
        header("Location: supplier_performance.php?status=deleted");
    } else {
        header("Location: supplier_performance.php?status=error");
    }
    exit;
}
?>