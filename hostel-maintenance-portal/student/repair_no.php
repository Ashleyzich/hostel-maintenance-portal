<?php

session_start();
include("../config/database.php");
include("../includes/assign_staff.php");

$request_id = (int)$_GET['id'];

$request = $conn->query("SELECT student_id FROM requests WHERE id='$request_id'")->fetch_assoc();

$conn->query("UPDATE requests 
              SET technician_arrived=0, status='pending', assigned_staff=NULL
              WHERE id='$request_id'");

if($request){
    createNotification($conn, $request['student_id'], "Request #$request_id was marked as technician no-show. Please reschedule.");
}

header("Location: view_requests.php");