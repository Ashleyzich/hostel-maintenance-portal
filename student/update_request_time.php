<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");
include("../includes/assign_staff.php");

$student_id = (int)$_SESSION['user_id'];
$request_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = "";

$request = $conn->query("SELECT * FROM requests WHERE id='$request_id' AND student_id='$student_id'")->fetch_assoc();

if(!$request){
    header("Location: view_requests.php");
    exit();
}

if(isset($_POST['reschedule'])){
    $new_time = $conn->real_escape_string($_POST['available_time']);
    $current_time = $request['available_time'];

    if($new_time === $current_time){
        $message = "<div class='alert alert-danger'>Please choose a different time from your current preferred time.</div>";
    } else {
        $conn->query("UPDATE requests
                      SET available_time='$new_time',
                          status='pending',
                          assigned_staff=NULL,
                          technician_arrived=NULL,
                          rating=NULL
                      WHERE id='$request_id'
                      AND student_id='$student_id'");

        createNotification($conn, $student_id, "Request #$request_id was rescheduled to $new_time. Assignment will be re-attempted at that time.");

        processDueAssignments($conn, $request_id);

        $message = "<div class='alert alert-success'>Request rescheduled. The system will match a technician in real time at your selected time.</div>";

        $request = $conn->query("SELECT * FROM requests WHERE id='$request_id' AND student_id='$student_id'")->fetch_assoc();
    }
}
?>

<?php include("../includes/header.php"); ?>
<?php include("../includes/sidebar.php"); ?>

<div class="container-fluid">
    <div class="card shadow border-0">
        <div class="card-body">
            <h4 class="mb-3">Reschedule Request #<?php echo $request_id; ?></h4>
            <?php echo $message; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">New Preferred Time</label>
                    <input type="datetime-local" name="available_time" class="form-control" required>
                </div>
                <button type="submit" name="reschedule" class="btn btn-primary">Save & Re-Assign</button>
                <a href="view_requests.php" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>

<?php include("../includes/sidebar_end.php"); ?>
<?php include("../includes/footer.php"); ?>