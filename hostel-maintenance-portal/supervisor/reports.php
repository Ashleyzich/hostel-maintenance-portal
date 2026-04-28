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


/* Technician Performance Dashboard */

$tech_performance = $conn->query("
SELECT 
    users.name,
    COUNT(requests.id) AS total_jobs,
    SUM(CASE WHEN requests.status='completed' THEN 1 ELSE 0 END) AS completed_jobs,
    ROUND(AVG(requests.rating), 2) AS average_rating,
    COUNT(requests.rating) AS ratings_count
FROM staff
JOIN users ON staff.user_id = users.id
LEFT JOIN requests ON staff.id = requests.assigned_staff
GROUP BY staff.id, users.name
ORDER BY average_rating DESC, completed_jobs DESC, total_jobs DESC
");

$top_rating_data = $conn->query("
SELECT ROUND(AVG(requests.rating), 2) AS overall_avg_rating,
       COUNT(requests.rating) AS total_ratings
FROM requests
WHERE requests.rating IS NOT NULL
")->fetch_assoc();

$overall_avg_rating = $top_rating_data['overall_avg_rating'] ? $top_rating_data['overall_avg_rating'] : 0;
$total_ratings = $top_rating_data['total_ratings'];
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


<!-- Technician Performance KPIs -->

<div class="row g-4 mb-4">

<div class="col-md-4">
<div class="card shadow border-0 bg-success text-white">
<div class="card-body">
<h6 class="mb-1">Average Technician Rating</h6>
<h2 class="mb-0"><?php echo number_format((float)$overall_avg_rating, 2); ?>/10</h2>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card shadow border-0 bg-primary text-white">
<div class="card-body">
<h6 class="mb-1">Submitted Ratings</h6>
<h2 class="mb-0"><?php echo (int)$total_ratings; ?></h2>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card shadow border-0 bg-dark text-white">
<div class="card-body">
<h6 class="mb-1">Completed Requests</h6>
<h2 class="mb-0"><?php echo (int)$completed; ?></h2>
</div>
</div>
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


<!-- Technician Performance Table -->

<div class="card shadow border-0 mb-4">

<div class="card-header bg-primary text-white">
<i class="bi bi-person-workspace"></i> Technician Performance Dashboard
</div>

<div class="card-body">

<div class="table-responsive">
<table class="table table-hover align-middle">

<thead class="table-dark">

<tr>
<th>Technician</th>
<th>Total Jobs</th>
<th>Completed Jobs</th>
<th>Avg Rating</th>
<th>Ratings Count</th>
<th>Completion Rate</th>
</tr>

</thead>

<tbody>

<?php
$chart_labels = [];
$chart_ratings = [];
$has_tech = false;

if($tech_performance && $tech_performance->num_rows > 0){
while($row = $tech_performance->fetch_assoc()){
$has_tech = true;
$total_jobs = (int)$row['total_jobs'];
$completed_jobs = (int)$row['completed_jobs'];
$completion_rate = $total_jobs > 0 ? round(($completed_jobs / $total_jobs) * 100, 1) : 0;
$avg_rating = $row['average_rating'] !== null ? number_format((float)$row['average_rating'], 2) : 'N/A';

if($row['average_rating'] !== null){
    $chart_labels[] = $row['name'];
    $chart_ratings[] = (float)$row['average_rating'];
}
?>

<tr>
<td><?php echo htmlspecialchars($row['name']); ?></td>
<td><?php echo $total_jobs; ?></td>
<td><?php echo $completed_jobs; ?></td>
<td><?php echo $avg_rating; ?></td>
<td><?php echo (int)$row['ratings_count']; ?></td>
<td><?php echo $completion_rate; ?>%</td>
</tr>

<?php
}
}

if(!$has_tech){
echo "<tr><td colspan='6' class='text-center'>No technician performance data available.</td></tr>";
}
?>

</tbody>

</table>
</div>

</div>

</div>


<!-- Technician Rating Chart -->

<div class="card shadow border-0">
<div class="card-header bg-primary text-white">
<h6 class="mb-0">Average Rating by Technician</h6>
</div>
<div class="card-body">
<?php if(count($chart_labels) > 0){ ?>
<canvas id="techRatingReport"></canvas>
<?php }else{ ?>
<p class="mb-0 text-muted">No ratings yet. This chart will appear once students submit ratings.</p>
<?php } ?>
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

<?php if(count($chart_labels) > 0){ ?>
new Chart(document.getElementById("techRatingReport"), {
type: "bar",
data: {
labels: <?php echo json_encode($chart_labels); ?>,
datasets: [{
label: "Average Rating (1-10)",
data: <?php echo json_encode($chart_ratings); ?>,
backgroundColor: "rgba(13, 110, 253, 0.7)",
borderColor: "rgba(13, 110, 253, 1)",
borderWidth: 1
}]
},
options: {
scales: {
y: {
beginAtZero: true,
max: 10
}
}
}
});
<?php } ?>

</script>


<?php include("../includes/sidebar_end.php"); ?>
<?php include("../includes/footer.php"); ?>