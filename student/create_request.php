<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/assign_staff.php";
require_once __DIR__ . "/../includes/send_email.php";

/** @var mysqli $conn */
$message = "";
$min_available_time = date("Y-m-d\\TH:i");
$issueTypes = [];
$issueTypesResult = $conn->query("SELECT id, issue_name FROM issue_types ORDER BY issue_name");
if ($issueTypesResult instanceof mysqli_result) {
    while ($row = $issueTypesResult->fetch_assoc()) {
        $issueTypes[] = $row;
    }
}

if (isset($_POST['submit_request'])) {

    $student_id = (int) $_SESSION['user_id'];
    $issue_type = (int) ($_POST['issue_type'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $available_time = trim($_POST['available_time'] ?? '');
    $submission_method = $_POST['submission_method'] ?? 'manual';

    $hostel = "";
    $room = "";

    if ($submission_method === 'qr') {
        $location_code = trim($_POST['location_code'] ?? '');

        if (preg_match('/^([A-Za-z0-9\-]+)\-([A-Za-z0-9\-]+)$/', $location_code, $matches)) {
            $hostel = $matches[1];
            $room = $matches[2];
        } else {
            $message = "<div class='alert alert-danger'>Invalid QR location code format. Use HOSTEL-ROOM.</div>";
        }
    } else {
        $hostel = trim($_POST['hostel'] ?? '');
        $room = trim($_POST['room'] ?? '');
    }

    if ($issue_type <= 0 || $description === '' || $available_time === '' || $hostel === '' || $room === '') {
        $message = "<div class='alert alert-danger'>Please complete all required fields.</div>";
    }

    if ($message === "") {

        $image_path = null;

        if (isset($_FILES['issue_image']) && $_FILES['issue_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . "/../assets/uploads/requests";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0775, true);
            }

            $original = basename($_FILES['issue_image']['name']);
            $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowed, true)) {
                $file_name = "request_" . $student_id . "_" . time() . "." . $ext;
                $dest = $upload_dir . "/" . $file_name;

                if (move_uploaded_file($_FILES['issue_image']['tmp_name'], $dest)) {
                    $image_path = "assets/uploads/requests/" . $file_name;
                }
            }
        }

        $has_image_column = $conn->query("SHOW COLUMNS FROM requests LIKE 'image_path'");

        if ($has_image_column && $has_image_column->num_rows > 0) {
            $stmt = $conn->prepare("\n                INSERT INTO requests \n                (student_id, issue_type_id, hostel, room, description, available_time, status, image_path)\n                VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)\n            ");

            $stmt->bind_param(
                "iisssss",
                $student_id,
                $issue_type,
                $hostel,
                $room,
                $description,
                $available_time,
                $image_path
            );
        } else {
            $stmt = $conn->prepare("\n                INSERT INTO requests \n                (student_id, issue_type_id, hostel, room, description, available_time, status)\n                VALUES (?, ?, ?, ?, ?, ?, 'pending')\n            ");

            $stmt->bind_param(
                "iissss",
                $student_id,
                $issue_type,
                $hostel,
                $room,
                $description,
                $available_time
            );
        }

        if ($stmt && $stmt->execute()) {

            $request_id = (int) $conn->insert_id;

            if (function_exists('createNotification')) {
                createNotification(
                    $conn,
                    $student_id,
                    "Request #$request_id was logged. Technician assignment will be attempted at your preferred time ($available_time)."
                );
            }

            $studentQuery = $conn->prepare("SELECT name, email FROM users WHERE id = ? LIMIT 1");
            $studentQuery->bind_param("i", $student_id);
            $studentQuery->execute();

            $studentResult = $studentQuery->get_result();

            if ($studentResult && $studentResult->num_rows > 0) {
                $student = $studentResult->fetch_assoc();

                if (!empty($student['email']) && function_exists('sendEmail')) {
                    sendEmail(
                        $student['email'],
                        $student['name'],
                        "Maintenance Request Submitted",
                        "
                        <h3>Maintenance Request Submitted</h3>
                        <p>Hello {$student['name']},</p>
                        <p>Your maintenance request has been successfully logged.</p>
                        <p><b>Request ID:</b> #$request_id</p>
                        <p><b>Hostel:</b> $hostel</p>
                        <p><b>Room:</b> $room</p>
                        <p><b>Preferred Repair Time:</b> $available_time</p>
                        <p><b>Status:</b> Pending</p>
                        <p>A technician will be assigned at your preferred time based on real-time staff availability.</p>
                        "
                    );
                }
            }

            $message = "<div class='alert alert-success'>Request submitted successfully.</div>";

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
                <h3 class="mb-1 fw-bold text-white"><i class="bi bi-tools text-warning"></i> Submit Maintenance Request</h3>
                <p class="mb-0 text-white">Report a maintenance issue and choose a preferred repair interval.</p>
            </div>
            <i class="bi bi-wrench-adjustable display-5 text-warning"></i>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-body">
            <?php echo $message; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Location Entry Method</label>
                        <select name="submission_method" id="submission_method" class="form-select" onchange="toggleLocationFields()">
                            <option value="manual">Manual (enter hostel and room)</option>
                            <option value="qr">QR code text (HOSTEL-ROOM)</option>
                        </select>
                    </div>

                    <div class="col-md-6 manual-location">
                        <label class="form-label">Hostel</label>
                        <input type="text" name="hostel" class="form-control" placeholder="e.g. Hostel 4">
                    </div>

                    <div class="col-md-6 manual-location">
                        <label class="form-label">Room Number</label>
                        <input type="text" name="room" class="form-control" placeholder="e.g. 211">
                    </div>

                    <div class="col-md-12 d-none" id="qr_location_block">
                        <label class="form-label">QR Location Code</label>
                        <input type="text" name="location_code" class="form-control" placeholder="e.g. HOSTEL4-211">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Issue Type</label>
                        <select name="issue_type" class="form-select" required>
                            <option value="">Select issue type</option>
                            <?php foreach ($issueTypes as $type): ?>
                                <option value="<?php echo (int) $type['id']; ?>"><?php echo htmlspecialchars($type['issue_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Available Time for Repair</label>
                        <input type="datetime-local" name="available_time" min="<?php echo $min_available_time; ?>" class="form-control" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Description of Problem</label>
                        <textarea name="description" class="form-control" rows="4" required></textarea>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Issue Image (optional)</label>
                        <input type="file" name="issue_image" class="form-control" accept="image/png,image/jpeg,image/webp">
                    </div>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i> The system waits until your chosen time, then assigns an available skilled technician.
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
function toggleLocationFields() {
    const method = document.getElementById('submission_method').value;
    const manual = document.querySelectorAll('.manual-location');
    const qrBlock = document.getElementById('qr_location_block');

    manual.forEach((el) => {
        el.classList.toggle('d-none', method === 'qr');
    });

    qrBlock.classList.toggle('d-none', method !== 'qr');
}

document.addEventListener('DOMContentLoaded', toggleLocationFields);
</script>

<?php include("../includes/sidebar_end.php"); ?>
<?php include("../includes/footer.php"); ?>