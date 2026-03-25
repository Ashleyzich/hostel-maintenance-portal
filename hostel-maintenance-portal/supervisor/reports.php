<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'supervisor'){
header("Location: ../auth/login.php");
exit();
}

include("../config/database.php");

/* ===============================
   REPORT DATA
================================*/

/* Requests by Issue Type */

$plumbing = $conn->query("SELECT COUNT(*) AS total FROM requests WHERE issue_type_id=1")->fetch_assoc()['total'];
$electrical = $conn->query("SELECT COUNT(*) AS total FROM requests WHERE issue_type_id=2")->fetch_assoc()['total'];
$furniture = $conn->query("SELECT COUNT(*) AS total FROM requests WHERE issue_type_id=3")->fetch_assoc()['total'];


/* Requests by Status */

$pending = $conn->query("SELECT COUNT(*) AS total FROM requests WHERE status='pending'")->fetch_assoc()['total'];
$progress = $conn->query("SELECT COUNT(*) AS total FROM requests WHERE status='in_progress'")->fetch_assoc()['total'];
$completed = $conn->query("SELECT COUNT(*) AS total FROM requests WHERE status='completed'")->fetch_assoc()['total'];


/* Monthly Requests */

$monthly = $conn->query("
SELECT MONTH(created_at) AS month, COUNT(*) AS total
FROM requests
GROUP BY MONTH(created_at)
");


/* Technician Workload */

$tech_workload = $conn->query("
SELECT users.name, COUNT(requests.id) AS total_jobs
FROM staff
JOIN users ON staff.user_id = users.id
LEFT JOIN requests ON staff.id = requests.assigned_staff
GROUP BY staff.id
");

?>

<?php include("../includes/header.php"); ?>
<?php include("../includes/sidebar.php"); ?>

<div class="container-fluid">

<!-- Page Header -->
<div class="page-header">

<div class="d-flex justify-content-between align-items-center">

<div>

<h3 class="mb-1 fw-bold text-white">
<i class="bi bi-bar-chart text-warning"></i>
Maintenance Reports
</h3>

<p class="mb-0 text-white">
View maintenance analytics and system performance.
</p>

</div>

<i class="bi bi-graph-up-arrow display-5 text-warning"></i>

</div>

</div>


<!-- Issue Type Report -->

<div class="card shadow border-0 mb-4">

<div class="card-header bg-primary text-white">
<h6 class="mb-0">Requests by Issue Type</h6>
</div>

<div class="card-body">
<canvas id="issueReport"></canvas>
</div>

</div>


<!-- Status Report -->

<div class="card shadow border-0 mb-4">

<div class="card-header bg-primary text-white">
<h6 class="mb-0">Requests by Status</h6>
</div>

<div class="card-body">
<canvas id="statusReport"></canvas>
</div>

</div>


<!-- Technician Workload -->

<div class="card shadow border-0">

<div class="card-header bg-primary text-white">
<i class="bi bi-person-workspace"></i> Technician Workload
</div>

<div class="card-body">

<table class="table table-hover">

<thead class="table-dark">

<tr>
<th>Technician</th>
<th>Total Jobs</th>
</tr>

</thead>

<tbody>

<?php while($row = $tech_workload->fetch_assoc()){ ?>

<tr>

<td><?php echo $row['name']; ?></td>

<td><?php echo $row['total_jobs']; ?></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>


<script>

/* ISSUE REPORT */

new Chart(document.getElementById("issueReport"), {
type: "bar",
data: {
labels: ["Plumbing","Electrical","Furniture"],
datasets: [{
label: "Requests",
data: [
<?php echo $plumbing ?>,
<?php echo $electrical ?>,
<?php echo $furniture ?>
]
}]
}
});


/* STATUS REPORT */

new Chart(document.getElementById("statusReport"), {
type: "pie",
data: {
labels: ["Pending","In Progress","Completed"],
datasets: [{
data: [
<?php echo $pending ?>,
<?php echo $progress ?>,
<?php echo $completed ?>
]
}]
}
});

</script>


<?php include("../includes/sidebar_end.php"); ?>
<?php include("../includes/footer.php"); ?>