<?php

session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");

$request_id = (int)$_GET['id'];

?>

<?php include("../includes/header.php"); ?>
<?php include("../includes/sidebar.php"); ?>

<div class="container-fluid">
<div class="card shadow border-0">
<div class="card-body">
<h4 class="mb-3">Feedback for Request #<?php echo $request_id; ?></h4>
<p class="text-muted">Choose what happened so the supervisor can track service quality.</p>

<a href="repair_yes.php?id=<?php echo $request_id; ?>" class="btn btn-success">
Staff Arrived & Work Done
</a>

<a href="repair_no.php?id=<?php echo $request_id; ?>" class="btn btn-danger">
Staff Did Not Show Up
</a>
</div>
</div>
</div>

<?php include("../includes/sidebar_end.php"); ?>
<?php include("../includes/footer.php"); ?>