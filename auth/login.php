<?php
session_start();
include '../includes/db.php';

if(isset($_SESSION['user_id'])){
    header("Location: ../dashboard.php");
    exit();
}

// LOGIN
if(isset($_POST['login'])){
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $password = mysqli_real_escape_string($conn,$_POST['password']);
    $result = mysqli_query($conn,"SELECT * FROM users WHERE email='$email' LIMIT 1");

    if(mysqli_num_rows($result) == 1){
        $user = mysqli_fetch_assoc($result);
        if($password === $user['password']){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: ../dashboard.php");
            exit();
        } else $error = "Invalid email or password.";
    } else $error = "Invalid email or password.";
}

// SIGNUP
if(isset($_POST['signup'])){
    $username = mysqli_real_escape_string($conn,$_POST['username']);
    $email    = mysqli_real_escape_string($conn,$_POST['email']);
    $password = mysqli_real_escape_string($conn,$_POST['password']);

    $check = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");
    if(mysqli_num_rows($check) > 0){
        $error = "Email already registered!";
    } else {
        mysqli_query($conn,"INSERT INTO users (username,email,password) VALUES ('$username','$email','$password')");
        $success = "Account created! You can login now.";
    }
}

// FORGOT PASSWORD
if(isset($_POST['forgot'])){
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $check = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");
    if(mysqli_num_rows($check) > 0){
        $success = "Password reset link sent to your email (simulate).";
    } else $error = "Email not registered!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GSIAS Auth</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css?v=1.2">
<link rel="stylesheet" href="../assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg,#667eea,#764ba2);
    display: flex; justify-content:center; align-items:center; min-height:100vh;
}
.card { background:#fff; border-radius:20px; box-shadow:0 15px 40px rgba(0,0,0,0.3); padding:30px; width:400px; }
h4 { text-align:center; font-weight:bold; margin-bottom:20px; color:#333; }
.form-control { border-radius:50px; padding:12px 20px; margin-bottom:15px; }
.btn { border-radius:50px; font-weight:600; padding:10px; width:100%; margin-bottom:10px; }
.btn-primary { background:#667eea; border:none; }
.btn-primary:hover { background:#5a67d8; }
.btn-success { background:#48bb78; border:none; }
.btn-success:hover { background:#38a169; }
.btn-warning { background:#ed8936; border:none; }
.btn-warning:hover { background:#dd6b20; }
.alert { border-radius:15px; }
</style>
</head>
<body>

<div class="card shadow">
<h4>GSIAS</h4>

<?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

<!-- LOGIN FORM -->
<div id="loginForm">
<form method="POST">
<input type="email" name="email" class="form-control" placeholder="Email" required>
<input type="password" name="password" class="form-control" placeholder="Password" required>
<button type="submit" name="login" class="btn btn-primary">Login</button>
<button type="button" class="btn btn-success" onclick="showForm('signup')">Sign Up</button>
<button type="button" class="btn btn-warning" onclick="showForm('forgot')">Forgot Password</button>
</form>
</div>

<!-- SIGNUP FORM -->
<div id="signupForm" style="display:none;">
<form method="POST">
<input type="text" name="username" class="form-control" placeholder="Username" required>
<input type="email" name="email" class="form-control" placeholder="Email" required>
<input type="password" name="password" class="form-control" placeholder="Password" required>
<button type="submit" name="signup" class="btn btn-success">Sign Up</button>
<button type="button" class="btn btn-primary" onclick="showForm('login')">Login</button>
</form>
</div>

<!-- FORGOT FORM -->
<div id="forgotForm" style="display:none;">
<form method="POST">
<input type="email" name="email" class="form-control" placeholder="Registered Email" required>
<button type="submit" name="forgot" class="btn btn-warning">Reset Password</button>
<button type="button" class="btn btn-primary" onclick="showForm('login')">Login</button>
</form>
</div>

<script>
function showForm(form){
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('signupForm').style.display = 'none';
    document.getElementById('forgotForm').style.display = 'none';
    if(form==='login') document.getElementById('loginForm').style.display='block';
    if(form==='signup') document.getElementById('signupForm').style.display='block';
    if(form==='forgot') document.getElementById('forgotForm').style.display='block';
}
</script>

</div>
</body>
</html>