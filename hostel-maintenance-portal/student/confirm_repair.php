<?php

session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");

$request_id = $_GET['id'];

?>

<?php include("../includes/header.php"); ?>
<?php include("../includes/sidebar.php"); ?>

<div class="container-fluid">

<div class="card shadow border-0">

<div class="card-body">

<h4>Did the technician arrive?</h4>

<a href="repair_yes.php?id=<?php echo $request_id; ?>" class="btn btn-success">
Yes
</a>

<a href="repair_no.php?id=<?php echo $request_id; ?>" class="btn btn-danger">
No
</a>

</div>

</div>

</div>

<?php include("../includes/sidebar_end.php"); ?>
<?php include("../includes/footer.php"); ?>