<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Hostel Maintenance Portal</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Custom CSS -->
<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<!-- ===============================
     NAVBAR
================================ -->

<nav class="navbar navbar-expand-lg" style="background-color:#003366;">

<div class="container">

<a class="navbar-brand text-warning fw-bold" href="#">
HIT Maintenance Portal
</a>

<div class="ms-auto">

<a href="auth/login.php" class="btn btn-warning me-2">
Login
</a>

<a href="auth/register.php" class="btn btn-outline-light">
Register
</a>

</div>

</div>

</nav>


<!-- ===============================
     HERO SECTION
================================ -->

<section class="text-white text-center d-flex align-items-center"
style="height:90vh; background: linear-gradient(rgba(0,51,102,0.8), rgba(0,51,102,0.8)), url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2'); background-size: cover;">

<div class="container">

<h1 class="display-4 fw-bold">
Hostel Maintenance Made Easy
</h1>

<p class="lead mt-3">
Report issues, track repairs, and manage maintenance efficiently.
</p>

<div class="mt-4">

<a href="auth/register.php" class="btn btn-warning btn-lg me-3">
Get Started
</a>

<a href="auth/login.php" class="btn btn-outline-light btn-lg">
Login
</a>

</div>

</div>

</section>


<!-- ===============================
     FEATURES
================================ -->

<section class="py-5">

<div class="container">

<h2 class="text-center mb-5">System Features</h2>

<div class="row g-4">

<div class="col-md-4">
<div class="card shadow border-0 text-center p-4">

<i class="bi bi-tools display-4 text-primary"></i>

<h4 class="mt-3">Submit Requests</h4>

<p>Students can easily report maintenance issues.</p>

</div>
</div>

<div class="col-md-4">
<div class="card shadow border-0 text-center p-4">

<i class="bi bi-graph-up display-4 text-success"></i>

<h4 class="mt-3">Track Progress</h4>

<p>Monitor request status in real-time.</p>

</div>
</div>

<div class="col-md-4">
<div class="card shadow border-0 text-center p-4">

<i class="bi bi-people display-4 text-warning"></i>

<h4 class="mt-3">Manage Staff</h4>

<p>Supervisors assign and monitor technicians.</p>

</div>
</div>

</div>

</div>

</section>


<!-- ===============================
     FOOTER
================================ -->

<footer class="text-white text-center p-3" style="background-color:#003366;">

<p class="mb-0">
© <?php echo date("Y"); ?> Hostel Maintenance Portal | HIT
</p>

</footer>

</body>
</html>