<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");
require_once __DIR__ . "/../includes/assign_staff.php";

if(!isset($_POST['request_id']) || !isset($_POST['available_time'])){
    header("Location: view_requests.php");
    exit();
}

$request_id = (int)$_POST['request_id'];
$student_id = (int)$_SESSION['user_id'];
$new_available_time = date('Y-m-d H:i:s', strtotime($_POST['available_time']));

$request = $conn->query("SELECT available_time, technician_arrived, status
                         FROM requests
                         WHERE id='$request_id' AND student_id='$student_id'")->fetch_assoc();

if(!$request || $request['status'] != 'pending'){
    header("Location: view_requests.php");
    exit();
}

$existing_time = $request['available_time'];

if($existing_time == $new_available_time){
    createNotificationIfMissing(
        $conn,
        $student_id,
        "Request #$request_id was not updated because the new preferred interval matches the previous one. Please choose a different time."
    );
    header("Location: view_requests.php");
    exit();
}

$conn->query("UPDATE requests
              SET available_time='$new_available_time',
                  assigned_staff=NULL
              WHERE id='$request_id' AND student_id='$student_id'");

createNotificationIfMissing(
    $conn,
    $student_id,
    "Request #$request_id has been rescheduled to $new_available_time. The assignment engine will retry at the revised interval."
);

header("Location: view_requests.php");
exit();