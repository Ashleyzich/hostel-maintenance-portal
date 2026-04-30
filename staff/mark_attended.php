<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'staff'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");
include("../includes/assign_staff.php");

$request_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = (int)$_SESSION['user_id'];

$staff = $conn->query("SELECT id FROM staff WHERE user_id='$user_id'")->fetch_assoc();
$staff_id = $staff ? (int)$staff['id'] : 0;

if($request_id > 0 && $staff_id > 0){
    $check = $conn->query("SELECT id FROM requests WHERE id='$request_id' AND assigned_staff='$staff_id' AND status='in_progress'");
    if($check && $check->num_rows > 0){
        $conn->query("UPDATE requests SET technician_arrived=1 WHERE id='$request_id'");
        logRequestActivity($conn, $request_id, "in_progress", "Technician marked request as attended.");
    }
}

header("Location: dashboard.php");
exit();
?>