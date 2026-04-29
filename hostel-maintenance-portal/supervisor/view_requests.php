<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'supervisor'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");
include("../includes/assign_staff.php");

processDueAssignments($conn);
ensureRequestActivityTable($conn);

$issue_filter = isset($_GET['issue_type']) ? (int)$_GET['issue_type'] : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$allowed_status = ['pending', 'in_progress', 'completed'];
if(!in_array($status_filter, $allowed_status)){
    $status_filter = '';
}
$filter_sql = "";
if($issue_filter > 0){
    $filter_sql .= " AND requests.issue_type_id=$issue_filter";
}
if($status_filter !== ''){
    $safe_status = $conn->real_escape_string($status_filter);
    $filter_sql .= " AND requests.status='$safe_status'";
}

$sql = "SELECT requests.*, users.name AS student_name,
        issue_types.issue_name,
        staff_users.name AS technician,
        ratings.feedback AS service_feedback,
        SUM(CASE WHEN request_activity.status='incomplete' THEN 1 ELSE 0 END) AS incomplete_count,
        SUM(CASE WHEN request_activity.status='completed' THEN 1 ELSE 0 END) AS completed_count
        FROM requests
        JOIN users ON requests.student_id = users.id
        JOIN issue_types ON requests.issue_type_id = issue_types.id
        LEFT JOIN staff ON requests.assigned_staff = staff.id
        LEFT JOIN users AS staff_users ON staff.user_id = staff_users.id
        LEFT JOIN ratings ON ratings.request_id = requests.id
        LEFT JOIN request_activity ON request_activity.request_id = requests.id
        WHERE 1=1$filter_sql
        GROUP BY requests.id
        ORDER BY requests.created_at DESC";

$result = $conn->query($sql);

$issue_summary = $conn->query("SELECT issue_types.issue_name, COUNT(requests.id) AS total
    FROM requests
    JOIN issue_types ON requests.issue_type_id = issue_types.id
    WHERE 1=1$filter_sql
    GROUP BY issue_types.id, issue_types.issue_name
    ORDER BY total DESC");

$status_summary = $conn->query("SELECT requests.status, COUNT(requests.id) AS total
    FROM requests
    WHERE 1=1$filter_sql
    GROUP BY requests.status
    ORDER BY total DESC");
?>

<?php include("../includes/header.php"); ?>
<?php include("../includes/sidebar.php"); ?>

<div class="container-fluid">
<div class="card shadow border-0 mb-4 page-header">
<div class="card-body d-flex justify-content-between align-items-center">
<div>
<h3 class="mb-1 text-white">
<i class="bi bi-list-check text-warning"></i> Maintenance Requests
</h3>
<p class="mb-0 text-white">Monitor all hostel maintenance requests.</p>
</div>
<i class="bi bi-tools display-5 text-warning"></i>
</div>
</div>

<form method="GET" class="card shadow border-0 mb-4">
<div class="card-body row g-3 align-items-end">
<div class="col-md-4">
<label class="form-label">Filter by Issue Type</label>
<select name="issue_type" class="form-select">
<option value="0">All Issue Types</option>
<option value="1" <?php echo $issue_filter === 1 ? 'selected' : ''; ?>>Plumbing</option>
<option value="2" <?php echo $issue_filter === 2 ? 'selected' : ''; ?>>Electrical</option>
<option value="3" <?php echo $issue_filter === 3 ? 'selected' : ''; ?>>Furniture</option>
</select>
</div>
<div class="col-md-4">
<label class="form-label">Filter by Status</label>
<select name="status" class="form-select">
<option value="">All Statuses</option>
<option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
<option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
<option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
</select>
</div>
<div class="col-md-4">
<button class="btn btn-primary w-100">Apply Filters</button>
</div>
</div>
</form>
<?php
$active_filters = [];
if($issue_filter > 0){
    $active_filters[] = "Issue Type";
}
if($status_filter !== ''){
    $active_filters[] = "Status";
}
?>
<?php if(count($active_filters) > 0){ ?>
<div class="alert alert-info mb-4">
Showing filtered requests by: <?php echo htmlspecialchars(implode(', ', $active_filters)); ?>.
</div>
<?php } ?>

<div class="row g-4 mb-4">
<div class="col-md-6">
<div class="card shadow border-0">
<div class="card-header bg-primary text-white"><h6 class="mb-0">Issue Categories</h6></div>
<div class="card-body">
<ul class="mb-0">
<?php if($issue_summary && $issue_summary->num_rows > 0){ while($issue_row = $issue_summary->fetch_assoc()){ ?>
<li><?php echo htmlspecialchars($issue_row['issue_name']); ?>: <?php echo (int)$issue_row['total']; ?></li>
<?php }}else{ ?>
<li class="text-muted">No issue category data.</li>
<?php } ?>
</ul>
</div>
</div>
</div>
<div class="col-md-6">
<div class="card shadow border-0">
<div class="card-header bg-primary text-white"><h6 class="mb-0">Status Categories</h6></div>
<div class="card-body">
<ul class="mb-0">
<?php if($status_summary && $status_summary->num_rows > 0){ while($status_row = $status_summary->fetch_assoc()){ ?>
<li><?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($status_row['status']))); ?>: <?php echo (int)$status_row['total']; ?></li>
<?php }}else{ ?>
<li class="text-muted">No status category data.</li>
<?php } ?>
</ul>
</div>
</div>
</div>
</div>

<div class="card shadow border-0">
<div class="card-body">
<div class="table-responsive">
<table class="table table-hover align-middle">
<thead>
<tr>
<th>ID</th>
<th>Student</th>
<th>Issue</th>
<th>Description</th>
<th>Hostel</th>
<th>Room</th>
<th>Technician</th>
<th>Status</th>
<th>Available Time</th>
<th>Rating</th>
<th>Feedback</th>
<th>Tracking</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php
if($result && $result->num_rows > 0){
    while($row = $result->fetch_assoc()){
?>
<tr>
<td><?php echo (int)$row['id']; ?></td>
<td><?php echo htmlspecialchars($row['student_name']); ?></td>
<td><?php echo htmlspecialchars($row['issue_name']); ?></td>
<td><?php echo htmlspecialchars($row['description']); ?></td>
<td><?php echo htmlspecialchars($row['hostel']); ?></td>
<td><?php echo htmlspecialchars($row['room']); ?></td>
<td>
<?php
if($row['technician']){
    echo "<span class='badge bg-success'>".htmlspecialchars($row['technician'])."</span>";
}else{
    echo "<span class='badge bg-danger'>Unassigned</span>";
}
?>
</td>
<td>
<?php
if($row['status'] == "pending"){
    echo "<span class='badge bg-warning text-dark'>Pending</span>";
}elseif($row['status'] == "in_progress"){
    echo "<span class='badge bg-primary'>In Progress</span>";
}else{
    echo "<span class='badge bg-success'>Completed</span>";
}
?>
</td>
<td><?php echo htmlspecialchars($row['available_time']); ?></td>
<td><?php echo $row['rating'] ? (int)$row['rating'].'/10' : '-'; ?></td>
<td><?php echo $row['service_feedback'] ? htmlspecialchars($row['service_feedback']) : '-'; ?></td>
<td>
Incomplete: <?php echo (int)$row['incomplete_count']; ?><br>
Complete: <?php echo (int)$row['completed_count']; ?>
</td>
<td>
<?php if($row['status'] == "in_progress"){ ?>
<a href="mark_no_show.php?id=<?php echo (int)$row['id']; ?>"
class="btn btn-warning btn-sm mb-1"
onclick="return confirm('Mark this request as incomplete due to no-show?')">
No-Show
</a><br>
<?php } ?>
<a href="delete_request.php?id=<?php echo (int)$row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this request?')">
Delete
</a>
</td>
</tr>
<?php
    }
}else{
    echo "<tr><td colspan='13' class='text-center'>No maintenance requests found</td></tr>";
}
?>
</tbody>
</table>
</div>
</div>
</div>

<div class="mt-4">
<a href="dashboard.php" class="btn btn-primary">← Back to Dashboard</a>
</div>
</div>

<?php include("../includes/sidebar_end.php"); ?>
<?php include("../includes/footer.php"); ?>