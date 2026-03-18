<?php
session_start();
if(isset($_SESSION['user_id'])){
    header("Location: ../dashboard.php");
    exit();
}
include '../includes/db.php';
if(isset($_POST['login'])){
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $query = "SELECT * FROM users WHERE username='$username' LIMIT 1";
    $result = mysqli_query($conn, $query);
    if(mysqli_num_rows($result) == 1){
        $user = mysqli_fetch_assoc($result);
        if($password=== $user['password']){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: ../dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | GSIAS</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
<div class="card p-4 shadow-sm" style="width: 350px;">
<h4 class="mb-3 text-center">Login</h4>
<?php if(isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
<form method="POST">
<input type="text" name="username" class="form-control mb-3" placeholder="Username" required>
<input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
<button type="submit" name="login" class="btn btn-primary w-100">Login</button>
</form>
</div>
</body>
</html>