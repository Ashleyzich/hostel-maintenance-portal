<?php

session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");

$request_id = (int)$_GET['id'];

$request = $conn->query("SELECT requests.id, issue_types.issue_name, requests.hostel, requests.room, requests.available_time
                         FROM requests
                         JOIN issue_types ON requests.issue_type_id = issue_types.id
                         WHERE requests.id='$request_id' AND requests.student_id='".(int)$_SESSION['user_id']."'")->fetch_assoc();

if(!$request){
    header("Location: view_requests.php");
    exit();
}

?>

<?php include("../includes/header.php"); ?>
<?php include("../includes/sidebar.php"); ?>

<div class="container-fluid">

<div class="page-header">
<div class="d-flex justify-content-between align-items-center">
<div>
<h3 class="mb-1 fw-bold text-white">
<i class="bi bi-person-check text-warning"></i>
Repair Attendance Confirmation
</h3>
<p class="mb-0 text-white">
Confirm whether the assigned technician arrived for your request.
</p>
</div>
<i class="bi bi-clipboard-check display-5 text-warning"></i>
</div>
</div>

<div class="card shadow border-0">
<div class="card-body">
<div class="row g-3 mb-3">
<div class="col-md-3"><strong>Request ID:</strong> #<?php echo $request['id']; ?></div>
<div class="col-md-3"><strong>Issue:</strong> <?php echo htmlspecialchars($request['issue_name']); ?></div>
<div class="col-md-3"><strong>Location:</strong> Hostel <?php echo htmlspecialchars($request['hostel']); ?> / Room <?php echo htmlspecialchars($request['room']); ?></div>
<div class="col-md-3"><strong>Preferred Time:</strong> <?php echo date('d M Y, H:i', strtotime($request['available_time'])); ?></div>
</div>

<div class="alert alert-info mb-4">
This prompt appears 1 hour after your preferred availability time to confirm technician attendance.
</div>

<div class="d-flex gap-2 flex-wrap">
<a href="repair_yes.php?id=<?php echo $request_id; ?>" class="btn btn-success px-4">
<i class="bi bi-check-circle me-1"></i> Yes, staff came
</a>

<a href="repair_no.php?id=<?php echo $request_id; ?>" class="btn btn-danger px-4">
<i class="bi bi-x-circle me-1"></i> No, staff did not come
</a>

<a href="view_requests.php" class="btn btn-secondary px-4">
Back
</a>
</div>

</div>
</div>

</div>

<?php include("../includes/sidebar_end.php"); ?>
<?php include("../includes/footer.php"); ?>