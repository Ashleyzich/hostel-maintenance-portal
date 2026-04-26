<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");
include("../includes/assign_staff.php");

$student_id = $_SESSION['user_id'];

runPendingAssignments($conn);

$sql = "SELECT requests.*, issue_types.issue_name
        FROM requests
        JOIN issue_types ON requests.issue_type_id = issue_types.id
        WHERE student_id='$student_id'
        ORDER BY created_at DESC";

$result = $conn->query($sql);

$alerts = $conn->query("SELECT id, message, created_at FROM notifications WHERE user_id='$student_id' ORDER BY created_at DESC LIMIT 5");

$pending_count = 0;
$in_progress_count = 0;
$completed_count = 0;

$count_result = $conn->query("SELECT status, COUNT(*) AS total FROM requests WHERE student_id='$student_id' GROUP BY status");
if($count_result){
    while($count = $count_result->fetch_assoc()){
        if($count['status'] == 'pending'){
            $pending_count = (int)$count['total'];
        } elseif($count['status'] == 'in_progress'){
            $in_progress_count = (int)$count['total'];
        } elseif($count['status'] == 'completed'){
            $completed_count = (int)$count['total'];
        }
    }
}
?>

<?php include("../includes/header.php"); ?>
<?php include("../includes/sidebar.php"); ?>

<div class="container-fluid">

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

<div class="row g-3 mb-3">
<div class="col-md-4">
<div class="card border-0 shadow-sm">
<div class="card-body">
<div class="text-muted small">Pending</div>
<h4 class="mb-0"><?php echo $pending_count; ?></h4>
</div>
</div>
</div>
<div class="col-md-4">
<div class="card border-0 shadow-sm">
<div class="card-body">
<div class="text-muted small">In Progress</div>
<h4 class="mb-0"><?php echo $in_progress_count; ?></h4>
</div>
</div>
</div>
<div class="col-md-4">
<div class="card border-0 shadow-sm">
<div class="card-body">
<div class="text-muted small">Completed</div>
<h4 class="mb-0"><?php echo $completed_count; ?></h4>
</div>
</div>
</div>
</div>

<?php if($alerts && $alerts->num_rows > 0){ ?>
<div class="card border-0 shadow mb-3">
<div class="card-body">
<h5 class="mb-3"><i class="bi bi-bell me-1"></i>Recent System Alerts</h5>
<?php while($notice = $alerts->fetch_assoc()){ ?>
<div class="alert alert-warning mb-2">
<div><?php echo htmlspecialchars($notice['message']); ?></div>
<small class="text-muted"><?php echo date('d M Y, H:i', strtotime($notice['created_at'])); ?></small>
</div>
<?php } ?>
</div>
</div>
<?php } ?>

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
<th>Modify Time</th>
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
<td><?php echo htmlspecialchars($row['issue_name']); ?></td>
<td><?php echo htmlspecialchars($row['description']); ?></td>
<td><?php echo htmlspecialchars($row['hostel']); ?></td>
<td><?php echo htmlspecialchars($row['room']); ?></td>
<td>
<?php
if($row['status'] == 'pending'){
    echo "<span class='badge bg-warning'>Pending</span>";
}elseif($row['status'] == 'in_progress'){
    echo "<span class='badge bg-primary'>In Progress</span>";
}else{
    echo "<span class='badge bg-success'>Completed</span>";
}
?>
</td>
<td><?php echo date('d M Y, H:i', strtotime($row['available_time'])); ?></td>
<td>
<?php if($row['status'] == 'pending'){ ?>
<form method="POST" action="update_request_time.php" class="d-flex gap-2">
<input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
<input type="datetime-local" name="available_time" class="form-control form-control-sm" required>
<button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
</form>
<?php } else { echo "-"; } ?>
</td>
<td>
<?php
$current_time = date('Y-m-d H:i:s');
if($row['status'] == 'in_progress' &&
   $current_time >= date('Y-m-d H:i:s', strtotime($row['available_time'].' +1 hour'))){
?>
<a href="confirm_repair.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
Confirm Repair
</a>
<?php } else { echo '-'; } ?>
</td>
</tr>
<?php
}
}else{
echo "<tr><td colspan='9' class='text-center'>No requests submitted yet</td></tr>";
}
?>

</tbody>
</table>
</div>
<div class="form-text mt-2">If a technician does not show up, use <strong>Confirm Repair</strong> after one hour to report it and reschedule.</div>
</div>
</div>

<div class="mt-4">
<a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

</div>

<?php include("../includes/sidebar_end.php"); ?>
<?php include("../includes/footer.php"); ?>