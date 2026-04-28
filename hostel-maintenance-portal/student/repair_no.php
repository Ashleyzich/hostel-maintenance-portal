<?php

session_start();
include("../config/database.php");
include("../includes/assign_staff.php");

$request_id = (int)$_GET['id'];

$request = $conn->query("SELECT student_id, assigned_staff FROM requests WHERE id='$request_id'")->fetch_assoc();

if($request){
    $assigned_staff = (int)$request['assigned_staff'];

    $conn->query("UPDATE requests
                  SET technician_arrived=0,
                      status='pending',
                      assigned_staff=NULL
                  WHERE id='$request_id'");

    if($assigned_staff > 0){
        $conn->query("UPDATE staff SET status='free' WHERE id='$assigned_staff'");
    }

    createNotification($conn, $request['student_id'], "Request #$request_id was marked as technician no-show. Please choose a new preferred time so reassignment can run.");

    $supervisors = $conn->query("SELECT id FROM users WHERE role='supervisor'");

    if($supervisors){
        while($supervisor = $supervisors->fetch_assoc()){
            createNotification($conn, (int)$supervisor['id'], "No-show alert: Technician did not arrive for Request #$request_id. Student must reschedule.");
        }
    }
}

header("Location: update_request_time.php?id=$request_id");
exit();