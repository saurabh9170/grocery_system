<?php
session_start();
include '../includes/db.php';

if(isset($_SESSION['user_id'])){
    header("Location: ../dashboard.php");
    exit();
}

$error = "";
$success = "";

if(isset($_POST['login'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $query = "SELECT * FROM users WHERE email='$email' AND password='$password' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) == 1){
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: ../dashboard.php");
        exit();
    } else {
        $error = "Invalid Email or Password!";
    }
}

if(isset($_POST['signup'])){
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if(mysqli_num_rows($check) > 0){
        $error = "This email is already registered!";
    } else {
        $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
        if(mysqli_query($conn, $sql)){
            $success = "Account created! You can now Sign In.";
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GSIAS | Authentication</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style2.css">
</head>
<body>

<div class="auth-card">
    <div class="brand-logo"><span>G</span>SIAS</div>
    <div class="auth-subtitle">Inventory & Sales Management</div>

    <?php if(!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if(!empty($success)) echo "<div class='alert alert-success' style='background:rgba(16,185,129,0.2); border-color:rgba(16,185,129,0.4); color:#4ade80;'>$success</div>"; ?>

    <div id="loginForm">
        <form method="POST">
            <input type="email" name="email" class="form-control" placeholder="Email address" required>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <button type="submit" name="login" class="btn btn-primary">Sign In</button>
            <button type="button" class="btn btn-ghost" onclick="showForm('signup')">Create Account</button>
            <a href="javascript:void(0)" class="btn-link-custom" onclick="showForm('forgot')">Forgot password?</a>
        </form>
    </div>

    <div id="signupForm" style="display:none;">
        <form method="POST">
            <input type="text" name="username" class="form-control" placeholder="Full Name" required>
            <input type="email" name="email" class="form-control" placeholder="Email address" required>
            <input type="password" name="password" class="form-control" placeholder="Create Password" required>
            <button type="submit" name="signup" class="btn btn-primary">Create Account</button>
            <button type="button" class="btn btn-ghost" onclick="showForm('login')">Back to Login</button>
        </form>
    </div>

    <div id="forgotForm" style="display:none;">
        <form method="POST">
            <p class="text-center small mb-3" style="color: var(--text-dim);">Enter your email to reset password.</p>
            <input type="email" name="email" class="form-control" placeholder="Registered Email" required>
            <button type="submit" name="forgot" class="btn btn-primary">Send Reset Link</button>
            <button type="button" class="btn btn-ghost" onclick="showForm('login')">Back to Login</button>
        </form>
    </div>
</div>

<script>
    function showForm(form){
        const forms = ['loginForm', 'signupForm', 'forgotForm'];
        forms.forEach(f => document.getElementById(f).style.display = 'none');
        document.getElementById(form + 'Form').style.display = 'block';
    }
</script>

</body>
</html>