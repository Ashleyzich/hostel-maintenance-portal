<?php

session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");

$request_id = (int)$_GET['id'];
$student_id = (int)$_SESSION['user_id'];

$request = $conn->query("SELECT requests.id, issue_types.issue_name, requests.rating
                         FROM requests
                         JOIN issue_types ON requests.issue_type_id = issue_types.id
                         WHERE requests.id='$request_id' AND requests.student_id='$student_id'")->fetch_assoc();

if(!$request){
    header("Location: view_requests.php");
    exit();
}

if(isset($_POST['rate'])){
    $rating = (int)$_POST['rating'];

    if($rating < 1){
        $rating = 1;
    }

    if($rating > 10){
        $rating = 10;
    }

    $conn->query("UPDATE requests SET rating='$rating' WHERE id='$request_id' AND student_id='$student_id'");

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
<i class="bi bi-star-half text-warning"></i>
Rate Technician
</h3>
<p class="mb-0 text-white">
Give a score between 1 and 10 for request #<?php echo $request['id']; ?>.
</p>
</div>
<i class="bi bi-emoji-smile display-5 text-warning"></i>
</div>
</div>

<div class="card shadow border-0">
<div class="card-body">
<div class="mb-3">
<strong>Issue:</strong> <?php echo htmlspecialchars($request['issue_name']); ?>
</div>

<form method="POST" class="row g-3 align-items-end">
<div class="col-md-4">
<label class="form-label">Rating (1-10)</label>
<input type="number" name="rating" min="1" max="10" value="<?php echo $request['rating'] ? (int)$request['rating'] : 10; ?>" class="form-control" required>
</div>

<div class="col-md-8 d-flex gap-2 flex-wrap">
<button type="submit" name="rate" class="btn btn-primary">
<i class="bi bi-check2-circle me-1"></i> Submit Rating
</button>
<a href="view_requests.php" class="btn btn-secondary">Skip for now</a>
</div>
</form>

<div class="form-text mt-3">10 = excellent service, 1 = poor service.</div>
</div>
</div>

</div>

<?php include("../includes/sidebar_end.php"); ?>
<?php include("../includes/footer.php"); ?>