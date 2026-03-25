<?php
session_start();
include("../config/database.php");

$message = "";

if(isset($_POST['login'])){

$email = $_POST['email'];
$password = $_POST['password'];
$role = $_POST['role'];

$sql = "SELECT * FROM users WHERE email='$email' AND role='$role'";
$result = $conn->query($sql);

if($result->num_rows > 0){

$user = $result->fetch_assoc();

if(password_verify($password,$user['password'])){

$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];
$_SESSION['name'] = $user['name'];

if($role == "student"){
header("Location: ../student/dashboard.php");
}
elseif($role == "staff"){
header("Location: ../staff/dashboard.php");
}
elseif($role == "supervisor"){
header("Location: ../supervisor/dashboard.php");
}

exit();

}else{
$message = "<div class='alert alert-danger'>Incorrect password</div>";
}

}else{
$message = "<div class='alert alert-danger'>User not found</div>";
}

}
?>

<?php include("../includes/header.php"); ?>

<div class="container-fluid vh-100">
<div class="row h-100">

<!-- LEFT SIDE -->
<div class="col-md-6 d-none d-md-flex align-items-center justify-content-center text-white"
style="background: linear-gradient(rgba(0,51,102,0.9), rgba(0,51,102,0.9)), url('https://images.unsplash.com/photo-1521791136064-7986c2920216'); background-size: cover;">

<div class="text-center px-4">
<h1 class="fw-bold text-warning">HIT Maintenance Portal</h1>
<p class="lead">Manage hostel maintenance efficiently.</p>
</div>

</div>

<!-- RIGHT SIDE -->
<div class="col-md-6 d-flex align-items-center justify-content-center">

<div class="card shadow border-0 p-4" style="max-width:400px; width:100%;">

<h3 class="text-center mb-3">Login</h3>

<?php echo $message; ?>

<form method="POST">

<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" required>
</div>

<div class="mb-3">
<label>Password</label>
<input type="password" name="password" class="form-control" required>
</div>

<div class="mb-3">
<label>Login As</label>
<select name="role" class="form-select">
<option value="student">Student</option>
<option value="staff">Maintenance Staff</option>
<option value="supervisor">Supervisor</option>
</select>
</div>

<button class="btn btn-primary w-100" name="login">Login</button>

</form>

<p class="text-center mt-3">
Don't have an account? <a href="register.php">Register</a>
</p>

</div>

</div>

</div>
</div>

<?php include("../includes/footer.php"); ?>