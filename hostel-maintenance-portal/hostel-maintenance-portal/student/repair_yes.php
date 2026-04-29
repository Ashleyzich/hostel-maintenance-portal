<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");
include("../includes/assign_staff.php");

$request_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$student_id = (int)$_SESSION['user_id'];

$request = $conn->query("SELECT assigned_staff FROM requests WHERE id='$request_id' AND student_id='$student_id'")->fetch_assoc();

if($request){
    $assigned_staff = (int)$request['assigned_staff'];

    $conn->query("UPDATE requests SET technician_arrived=1, status='completed' WHERE id='$request_id'");
    logRequestActivity($conn, $request_id, "completed", "Student confirmed repair completion.");

    if($assigned_staff > 0){
        $conn->query("UPDATE staff SET status='free' WHERE id='$assigned_staff'");
    }

    header("Location: rate_technician.php?id=$request_id");
    exit();
}

header("Location: view_requests.php");
exit();
?>