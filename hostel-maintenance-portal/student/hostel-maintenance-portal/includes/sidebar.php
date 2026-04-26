<?php
$role = $_SESSION['role'];
?>

<div class="d-flex">

<!-- Sidebar -->

<div class="text-white p-3" style="width:250px; min-height:100vh; background-color:#003366;">

<h4 class="text-center text-warning fw-bold">
HIT Maintenance
</h4>

<hr class="bg-light">

<ul class="nav flex-column">

<?php if($role == "student"){ ?>

<li class="nav-item">
<a class="nav-link text-white" href="../student/dashboard.php">
<i class="bi bi-speedometer2"></i> Dashboard
</a>
</li>

<li class="nav-item">
<a class="nav-link text-white" href="../student/create_request.php">
<i class="bi bi-tools"></i> Submit Request
</a>
</li>

<li class="nav-item">
<a class="nav-link text-white" href="../student/view_requests.php">
<i class="bi bi-list-check"></i> My Requests
</a>
</li>

<?php } ?>


<?php if($role == "staff"){ ?>

<li class="nav-item">
<a class="nav-link text-white" href="../staff/dashboard.php">
<i class="bi bi-speedometer2"></i> Dashboard
</a>
</li>

<li class="nav-item">
<a class="nav-link text-white" href="../staff/dashboard.php">
<i class="bi bi-tools"></i> Assigned Tasks
</a>
</li>

<?php } ?>


<?php if($role == "supervisor"){ ?>

<li class="nav-item">
<a class="nav-link text-white" href="../supervisor/dashboard.php">
<i class="bi bi-speedometer2"></i> Dashboard
</a>
</li>

<li class="nav-item">
<a class="nav-link text-white" href="../supervisor/manage_staff.php">
<i class="bi bi-people"></i> Manage Technicians
</a>
</li>

<li class="nav-item">
<a class="nav-link text-white" href="../supervisor/view_requests.php">
<i class="bi bi-list-check"></i> View Requests
</a>
</li>

<li class="nav-item">
<a class="nav-link text-white" href="../supervisor/manage_users.php">
<i class="bi bi-people"></i> Manage Users
</a>
</li>

<li class="nav-item">
<a class="nav-link text-white" href="../supervisor/reports.php">
<i class="bi bi-bar-chart"></i> Reports
</a>
</li>



<?php } ?>


<li class="nav-item mt-4">
<a class="nav-link text-danger" href="../auth/logout.php">
<i class="bi bi-box-arrow-right"></i> Logout
</a>
</li>

</ul>

</div>

<!-- Main Content -->

<div class="flex-grow-1 p-4">