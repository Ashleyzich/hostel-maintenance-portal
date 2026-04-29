<?php

session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'supervisor'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");

$id = $_GET['id'];
$status = $_GET['status'];

$sql = "UPDATE staff SET status='$status' WHERE id='$id'";

$conn->query($sql);

header("Location: manage_staff.php");

?>