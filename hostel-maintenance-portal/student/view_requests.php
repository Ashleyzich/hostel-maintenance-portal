<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");

$student_id = $_SESSION['user_id'];

$sql = "SELECT requests.*, issue_types.issue_name
        FROM requests
        JOIN issue_types ON requests.issue_type_id = issue_types.id
        WHERE student_id='$student_id'
        ORDER BY created_at DESC";

$result = $conn->query($sql);
?>

<?php include("../includes/header.php"); ?>
<?php include("../includes/sidebar.php"); ?>

<div class="container-fluid">

<!-- Page Header -->

<div class="page-header">

<div class="d-flex justify-content-between align-items-center">

<div>

<h3 class="mb-1 fw-bold text-white">
<i class="bi bi-list-check text-warning"></i>
My Maintenance Requests
</h3>

<p class="mb-0 text-white">
Track the status of your maintenance requests.
</p>

</div>

<i class="bi bi-tools display-5 text-warning"></i>

</div>

</div>

<!-- Requests Table -->

<div class="card shadow border-0">

<div class="card-body">

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>Issue Type</th>
<th>Description</th>
<th>Hostel</th>
<th>Room</th>
<th>Status</th>
<th>Available Time</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php

if($result->num_rows > 0){

while($row = $result->fetch_assoc()){

?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['issue_name']; ?></td>

<td><?php echo $row['description']; ?></td>

<td><?php echo $row['hostel']; ?></td>

<td><?php echo $row['room']; ?></td>

<td>

<?php

if($row['status'] == "pending"){
echo "<span class='badge bg-warning'>Pending</span>";
}
elseif($row['status'] == "in_progress"){
echo "<span class='badge bg-primary'>In Progress</span>";
}
else{
echo "<span class='badge bg-success'>Completed</span>";
}

?>

</td>


<td><?php echo $row['available_time']; ?></td>

<td>

<?php

$current_time = date("Y-m-d H:i:s");

/* Show confirmation button only if:
   - request is in progress
   - 1 hour has passed after available_time
*/

if($row['status'] == "in_progress" && 
   $current_time >= date("Y-m-d H:i:s", strtotime($row['available_time'].' +1 hour'))){

?>

<a href="confirm_repair.php?id=<?php echo $row['id']; ?>"
class="btn btn-primary btn-sm">
Confirm Repair
</a>

<?php
}else{

echo "-";

}

?>

</td>

</tr>

<?php
}

}else{

echo "<tr><td colspan='7' class='text-center'>No requests submitted yet</td></tr>";

}

?>

</tbody>

</table>

</div>

</div>

</div>

<div class="mt-4">

<a href="dashboard.php" class="btn btn-secondary">
Back to Dashboard
</a>

</div>

</div>

<?php include("../includes/sidebar_end.php"); ?>
<?php include("../includes/footer.php"); ?>