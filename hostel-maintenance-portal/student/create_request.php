<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");
include("../includes/assign_staff.php");

$message = "";

if(isset($_POST['submit_request'])){

    $student_id = (int)$_SESSION['user_id'];
    $issue_type = (int)$_POST['issue_type'];
    $description = $conn->real_escape_string($_POST['description']);
    $available_time = $conn->real_escape_string($_POST['available_time']);

    $submission_method = $_POST['submission_method'];

    if($submission_method === 'qr'){
        $location_code = trim($_POST['location_code']);

        if(preg_match('/^([A-Za-z0-9\-]+)\-([A-Za-z0-9\-]+)$/', $location_code, $matches)){
            $hostel = $conn->real_escape_string($matches[1]);
            $room = $conn->real_escape_string($matches[2]);
        } else {
            $message = "<div class='alert alert-danger'>Invalid QR location code format. Use HOSTEL-ROOM.</div>";
        }
    } else {
        $hostel = $conn->real_escape_string($_POST['hostel']);
        $room = $conn->real_escape_string($_POST['room']);
    }

    if($message === ""){
        $image_path_sql = "NULL";

        if(isset($_FILES['issue_image']) && $_FILES['issue_image']['error'] === UPLOAD_ERR_OK){
            $upload_dir = "../assets/uploads/requests";

            if(!is_dir($upload_dir)){
                mkdir($upload_dir, 0775, true);
            }

            $tmp_name = $_FILES['issue_image']['tmp_name'];
            $original = basename($_FILES['issue_image']['name']);
            $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if(in_array($ext, $allowed)){
                $file_name = "request_".$student_id."_".time().".".$ext;
                $dest = $upload_dir."/".$file_name;

                if(move_uploaded_file($tmp_name, $dest)){
                    $db_path = "assets/uploads/requests/".$file_name;
                    $image_path_sql = "'".$conn->real_escape_string($db_path)."'";
                }
            }
        }

        $has_image_column = $conn->query("SHOW COLUMNS FROM requests LIKE 'image_path'");

        if($has_image_column && $has_image_column->num_rows > 0){
            $sql = "INSERT INTO requests
                    (student_id, issue_type_id, hostel, room, description, available_time, status, image_path)
                    VALUES
                    ('$student_id', '$issue_type', '$hostel', '$room', '$description', '$available_time', 'pending', $image_path_sql)";
        } else {
            $sql = "INSERT INTO requests
                    (student_id, issue_type_id, hostel, room, description, available_time, status)
                    VALUES
                    ('$student_id', '$issue_type', '$hostel', '$room', '$description', '$available_time', 'pending')";
        }

        if($conn->query($sql)){
            $request_id = (int)$conn->insert_id;

            $assigned = assignTechnician($conn, $issue_type, $request_id, $available_time);

            if($assigned){
                createNotification($conn, $student_id, "Request #$request_id has been assigned to a technician.");
                $message = "<div class='alert alert-success'>Request submitted and technician assigned.</div>";
            } else {
                createNotification($conn, $student_id, "Scheduling conflict for Request #$request_id at your preferred time. Please reschedule.");
                $message = "<div class='alert alert-warning'>Request submitted, but no technician is available at that time. Please reschedule from My Requests.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Unable to submit request. Please try again.</div>";
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
<i class="bi bi-tools text-warning"></i>
Submit Maintenance Request
</h3>
<p class="mb-0 text-white">
Report an issue using a form or hostel QR location code.
</p>
</div>
<i class="bi bi-wrench-adjustable display-5 text-warning"></i>
</div>
</div>

<div class="card shadow border-0">
<div class="card-body">

<?php echo $message; ?>

<form method="POST" enctype="multipart/form-data">

<div class="row g-3">
<div class="col-md-6">
<label class="form-label">Submission Method</label>
<select name="submission_method" id="submission_method" class="form-select" required onchange="toggleMethodFields()">
<option value="manual">Manual Form</option>
<option value="qr">QR Location Code</option>
</select>
</div>

<div class="col-md-6 method-manual">
<label class="form-label">Hostel</label>
<input type="text" name="hostel" class="form-control">
</div>

<div class="col-md-6 method-manual">
<label class="form-label">Room Number</label>
<input type="text" name="room" class="form-control">
</div>

<div class="col-md-6 d-none" id="qr_field_wrap">
<label class="form-label">QR Location Code (HOSTEL-ROOM)</label>
<input type="text" name="location_code" class="form-control" placeholder="A-204">
</div>

<div class="col-md-6">
<label class="form-label">Issue Type</label>
<select name="issue_type" class="form-select" required>
<option value="1">Plumbing</option>
<option value="2">Electrical</option>
<option value="3">Broken Furniture</option>
</select>
</div>

<div class="col-md-12">
<label class="form-label">Description of Problem</label>
<textarea name="description" class="form-control" rows="4" required></textarea>
</div>

<div class="col-md-6">
<label class="form-label">Preferred Repair Time</label>
<input type="datetime-local" name="available_time" class="form-control" required>
</div>

<div class="col-md-6">
<label class="form-label">Issue Image (Optional)</label>
<input type="file" name="issue_image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
</div>
</div>

<div class="mt-4">
<button type="submit" name="submit_request" class="btn btn-primary">Submit Request</button>
<a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

</form>

</div>
</div>

</div>

<script>
function toggleMethodFields(){
    const method = document.getElementById('submission_method').value;
    const manualFields = document.querySelectorAll('.method-manual input');
    const qrWrap = document.getElementById('qr_field_wrap');

    if(method === 'qr'){
        qrWrap.classList.remove('d-none');
        manualFields.forEach(field => field.removeAttribute('required'));
    }else{
        qrWrap.classList.add('d-none');
        manualFields.forEach(field => field.setAttribute('required', 'required'));
    }
}

toggleMethodFields();
</script>

<?php include("../includes/sidebar_end.php"); ?>
<?php include("../includes/footer.php"); ?>