<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';
?>

<h2 class="dashboard-header">Suppliers</h2>
<a href="supplier_add.php" class="btn btn-success mb-3">+ Add New Supplier</a>

<table class="table table-striped table-bordered">
    <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>Supplier Name</th>
            <th>Contact Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $query = mysqli_query($conn, "SELECT * FROM suppliers ORDER BY id DESC");
        $i = 1;
        while($row = mysqli_fetch_assoc($query)){
            echo "<tr>
                    <td>{$i}</td>
                    <td>{$row['supplier_name']}</td>
                    <td>{$row['contact_name']}</td>
                    <td>{$row['contact_email']}</td>
                    <td>{$row['contact_phone']}</td>
                    <td>{$row['address']}</td>
                    <td>
                        <a href='supplier_delete.php?id={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this supplier?\")'>Delete</a>
                    </td>
                  </tr>";
            $i++;
        }
        ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>