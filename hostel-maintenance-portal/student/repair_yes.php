<?php

session_start();
include("../config/database.php");

$request_id = $_GET['id'];

$conn->query("UPDATE requests 
              SET technician_arrived=1, status='completed' 
              WHERE id='$request_id'");

header("Location: rate_technician.php?id=$request_id");