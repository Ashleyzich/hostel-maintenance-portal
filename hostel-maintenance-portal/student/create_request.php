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

if (isset($_POST['submit_request'])) {

    $student_id = (int) $_SESSION['user_id'];
    $issue_type = (int) $_POST['issue_type'];
    $description = trim($_POST['description']);
    $available_time = trim($_POST['available_time']);
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
            $stmt = $conn->prepare("
                INSERT INTO requests 
                (student_id, issue_type_id, hostel, room, description, available_time, status, image_path)
                VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)
            ");

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
            $stmt = $conn->prepare("
                INSERT INTO requests 
                (student_id, issue_type_id, hostel, room, description, available_time, status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");

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