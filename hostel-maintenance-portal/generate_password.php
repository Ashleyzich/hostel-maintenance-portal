<?php
// This script generates a secure password hash

$password = "123456";  // this will be the login password

$hash = password_hash($password, PASSWORD_DEFAULT);

echo $hash;
?>