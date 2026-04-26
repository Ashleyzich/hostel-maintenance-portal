<?php

session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");
require_once __DIR__ . "/../includes/assign_staff.php";

$request_id = (int)$_GET['id'];
$student_id = (int)$_SESSION['user_id'];

$request = $conn->query("SELECT assigned_staff FROM requests WHERE id='$request_id' AND student_id='$student_id'")->fetch_assoc();

if(!$request){
    header("Location: view_requests.php");
    exit();
}

$assigned_staff = $request['assigned_staff'];

$conn->query("UPDATE requests
              SET technician_arrived=0,
                  status='pending',
                  assigned_staff=NULL
              WHERE id='$request_id' AND student_id='$student_id'");

if($assigned_staff){
    $assigned_staff = (int)$assigned_staff;
    $conn->query("UPDATE staff SET status='free' WHERE id='$assigned_staff'");
}

notifySupervisors($conn, "No-show reported for request #$request_id. Student must submit a new availability interval before reassignment.");
createNotificationIfMissing($conn, $student_id, "Request #$request_id was returned to pending after a no-show. Please update your preferred interval to trigger reassignment.");

header("Location: view_requests.php");
exit();