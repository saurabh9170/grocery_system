<?php
include 'includes/session.php';
include 'includes/db.php';

$action = $_GET['action'];
$id = intval($_GET['id']);

if($action == 'delete'){
    mysqli_query($conn,"DELETE FROM inventory WHERE id='$id'");
    header("Location: inventory.php");
}
?>