<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'supervisor'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");
include("../includes/assign_staff.php");

if(!isset($_GET['id'])){
    header("Location: view_requests.php");
    exit();
}

$request_id = (int)$_GET['id'];

$request = $conn->query("SELECT assigned_staff, student_id FROM requests WHERE id='$request_id'")->fetch_assoc();

if($request){
    $conn->query("UPDATE requests SET status='pending', assigned_staff=NULL WHERE id='$request_id'");
    logRequestActivity($conn, $request_id, 'incomplete', 'Technician did not show up. Awaiting reassignment.');

    if(!empty($request['assigned_staff'])){
        $staff_id = (int)$request['assigned_staff'];
        $conn->query("UPDATE staff SET status='free' WHERE id='$staff_id'");
    }

    createNotification($conn, (int)$request['student_id'], "Request #$request_id delayed: assigned technician did not show up. Supervisor will reassign shortly.");
}

header("Location: view_requests.php");
exit();
?>