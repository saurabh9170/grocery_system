<?php include 'includes/session.php'; ?>
<?php include 'includes/db.php'; ?>
<?php include 'includes/header.php'; ?>

<h2 class="dashboard-header">Customer Loyalty Trends</h2>

<?php
$loyalty = mysqli_query($conn,"SELECT customer_id, COUNT(*) AS visits FROM sales GROUP BY customer_id ORDER BY visits DESC LIMIT 10");
?>

<ul class="list-group mt-3">
<?php while($row=mysqli_fetch_assoc($loyalty)){ ?>
<li class="list-group-item">Customer #<?php echo $row['customer_id']; ?> - Visits: <?php echo $row['visits']; ?></li>
<?php } ?>
</ul>

<?php include 'includes/footer.php'; ?>