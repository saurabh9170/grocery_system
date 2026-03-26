<?php
include 'includes/session.php';
include 'includes/db.php';

if(isset($_GET['id'])){
    $id = intval($_GET['id']); // Safety: integer banado
    $delete = mysqli_query($conn, "DELETE FROM inventory WHERE id=$id");

    if($delete){
        header("Location: inventory.php");
        exit;
    } else {
        echo "Error deleting item: ".mysqli_error($conn);
    }
} else {
    header("Location: inventory.php"); // agar ID na ho to wapas inventory page
    exit;
}
?>