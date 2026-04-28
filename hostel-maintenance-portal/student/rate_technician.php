<?php

session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");

$request_id = (int)$_GET['id'];
$message = "";

$request = $conn->query("SELECT assigned_staff FROM requests WHERE id='$request_id'")->fetch_assoc();
$staff_id = $request ? (int)$request['assigned_staff'] : 0;

if(isset($_POST['rate'])){
    $rating = (int)$_POST['rating'];
    $feedback_option = $conn->real_escape_string($_POST['feedback_option']);

    $allowed_feedback = ['work_completed_well', 'work_poorly_done'];

    if($rating < 1 || $rating > 10 || !in_array($feedback_option, $allowed_feedback)){
        $message = "<div class='alert alert-danger'>Please provide a valid rating and feedback option.</div>";
    } else {
        $feedback_text = ($feedback_option === 'work_poorly_done') ? 'Work was poorly done' : 'Work completed well';

        $conn->query("UPDATE requests SET rating='$rating' WHERE id='$request_id'");

        if($staff_id > 0){
            $conn->query("INSERT INTO ratings (request_id, staff_id, rating, feedback)
                          VALUES ('$request_id', '$staff_id', '$rating', '$feedback_text')");
        }

        header("Location: view_requests.php");
        exit();
    }
}
?>

<?php include("../includes/header.php"); ?>
<?php include("../includes/sidebar.php"); ?>

<div class="container-fluid">
<div class="card shadow border-0">
<div class="card-body">
    <h4>Rate Service Quality</h4>
    <p class="text-muted">Select feedback and rate the quality from 1 to 10.</p>

    <?php echo $message; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Feedback Option</label>
            <select name="feedback_option" class="form-select" required>
                <option value="work_completed_well">Work completed well</option>
                <option value="work_poorly_done">Work was poorly done</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Rating (1-10)</label>
            <input type="number" name="rating" min="1" max="10" class="form-control" required>
        </div>

        <button type="submit" name="rate" class="btn btn-primary">Submit Feedback</button>
        <a href="view_requests.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</div>
</div>

<?php include("../includes/sidebar_end.php"); ?>
<?php include("../includes/footer.php"); ?>