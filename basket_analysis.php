<?php include 'includes/session.php'; ?>
<?php include 'includes/db.php'; ?>
<?php include 'includes/header.php'; ?>

<h2 class="dashboard-header">Basket Analysis</h2>

<?php
$basket = mysqli_query($conn,"SELECT b.product_name, COUNT(*) AS freq FROM sales s JOIN inventory b ON s.product_id=b.id GROUP BY b.id ORDER BY freq DESC LIMIT 10");
?>

<ul class="list-group mt-3">
<?php while($row=mysqli_fetch_assoc($basket)){ ?>
<li class="list-group-item"><?php echo $row['product_name']." - Purchased ".$row['freq']." times"; ?></li>
<?php } ?>
</ul>

<?php include 'includes/footer.php'; ?>