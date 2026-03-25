<?php

session_start();
include("../config/database.php");

$request_id = $_GET['id'];

if(isset($_POST['rate'])){

$rating = $_POST['rating'];

$conn->query("UPDATE requests SET rating='$rating' WHERE id='$request_id'");

header("Location: view_requests.php");

}

?>

<form method="POST">

<label>Rate Technician (1-10)</label>

<input type="number" name="rating" min="1" max="10" required>

<button type="submit" name="rate">Submit Rating</button>

</form>