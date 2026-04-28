<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");
include("../includes/assign_staff.php");

processDueAssignments($conn);

$student_id = (int)$_SESSION['user_id'];

$sql = "SELECT requests.*, issue_types.issue_name,
        staff_user.name AS technician_name
        FROM requests
        JOIN issue_types ON requests.issue_type_id = issue_types.id
        LEFT JOIN staff ON requests.assigned_staff = staff.id
        LEFT JOIN users AS staff_user ON staff.user_id = staff_user.id
        WHERE student_id='$student_id'
        ORDER BY created_at DESC";

$result = $conn->query($sql);
$has_image_column = $conn->query("SHOW COLUMNS FROM requests LIKE 'image_path'");
$show_images = ($has_image_column && $has_image_column->num_rows > 0);
?>

<?php include("../includes/header.php"); ?>
<?php include("../includes/sidebar.php"); ?>

<div class="container-fluid">
<div class="page-header">
<div class="d-flex justify-content-between align-items-center">
<div>
<h3 class="mb-1 fw-bold text-white"><i class="bi bi-list-check text-warning"></i> My Maintenance Requests</h3>
<p class="mb-0 text-white">Track request status, reschedule conflicts, and submit feedback.</p>
</div>
<i class="bi bi-tools display-5 text-warning"></i>
</div>
</div>

<div class="card shadow border-0">
<div class="card-body">
<div class="table-responsive">
<table class="table table-hover align-middle">
<thead class="table-dark">
<tr>
<th>ID</th><th>Issue Type</th><th>Description</th><th>Location</th><th>Technician</th><th>Status</th><th>Available Time</th>
<?php if($show_images){ ?><th>Image</th><?php } ?>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php
if($result && $result->num_rows > 0){
while($row = $result->fetch_assoc()){
$current_time = date("Y-m-d H:i:s");
$feedback_time = date("Y-m-d H:i:s", strtotime($row['available_time'].' +1 hour'));
?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo htmlspecialchars($row['issue_name']); ?></td>
<td><?php echo htmlspecialchars($row['description']); ?></td>
<td><?php echo htmlspecialchars($row['hostel'])." / ".htmlspecialchars($row['room']); ?></td>
<td><?php echo $row['technician_name'] ? htmlspecialchars($row['technician_name']) : "<span class='text-muted'>Unassigned</span>"; ?></td>
<td>
<?php
if($row['status'] == "pending"){
    echo "<span class='badge bg-warning text-dark'>Pending</span>";
} elseif($row['status'] == "in_progress"){
    echo "<span class='badge bg-primary'>In Progress</span>";
} else {
    echo "<span class='badge bg-success'>Completed</span>";
}
?>
</td>
<td><?php echo $row['available_time']; ?></td>
<?php if($show_images){ ?>
<td>
<?php if(!empty($row['image_path'])){ ?>
<a href="../<?php echo htmlspecialchars($row['image_path']); ?>" target="_blank" class="btn btn-outline-secondary btn-sm">View</a>
<?php } else { echo "-"; } ?>
</td>
<?php } ?>
<td>
<?php if($row['status'] == 'pending' && !$row['assigned_staff']){ ?>
<a href="update_request_time.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Reschedule</a>
<?php } elseif($row['status'] == 'in_progress' && $current_time >= $feedback_time){ ?>
<a href="confirm_repair.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Give Feedback</a>
<?php } else { echo "-"; } ?>
</td>
</tr>
<?php }
}else{
$colspan = $show_images ? 9 : 8;
echo "<tr><td colspan='$colspan' class='text-center'>No requests submitted yet</td></tr>";
}
?>
</tbody>
</table>
</div>
</div>
</div>
<div class="mt-4"><a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a></div>
</div>

<?php include("../includes/sidebar_end.php"); ?>
<?php include("../includes/footer.php"); ?>