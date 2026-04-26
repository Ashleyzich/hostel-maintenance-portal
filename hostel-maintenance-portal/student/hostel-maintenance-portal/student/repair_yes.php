<?php

session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");

$request_id = (int)$_GET['id'];
$student_id = (int)$_SESSION['user_id'];

$request = $conn->query("SELECT assigned_staff FROM requests WHERE id='$request_id' AND student_id='$student_id'")->fetch_assoc();

if(!$request){
    header("Location: view_requests.php");
    exit();
}

$conn->query("UPDATE requests
              SET technician_arrived=1,
                  status='completed'
              WHERE id='$request_id' AND student_id='$student_id'");

if($request['assigned_staff']){
    $staff_id = (int)$request['assigned_staff'];
    $conn->query("UPDATE staff SET status='free' WHERE id='$staff_id'");
}

header("Location: rate_technician.php?id=$request_id");
exit();