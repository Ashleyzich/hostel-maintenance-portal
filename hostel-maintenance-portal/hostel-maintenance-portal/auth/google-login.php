<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("../config/database.php");

// Get token
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['token'])) {
    echo json_encode(["status"=>"error","message"=>"No token"]);
    exit();
}

$token = $data['token'];

// Verify token with Google
$response = file_get_contents("https://oauth2.googleapis.com/tokeninfo?id_token=".$token);

if (!$response) {
    echo json_encode(["status"=>"error","message"=>"Token verification failed"]);
    exit();
}

$payload = json_decode($response);

// Validate payload
if (!$payload || !isset($payload->email)) {
    echo json_encode(["status"=>"error","message"=>"Invalid Google response"]);
    exit();
}

$email = $payload->email;
$name = $payload->name;
$google_id = $payload->sub;
$picture = $payload->picture;

// 🔒 LOCK TO SCHOOL EMAILS
if (!str_ends_with($email, "@hit.ac.zw")) {
    echo json_encode([
        "status"=>"error",
        "message"=>"Only HIT emails allowed"
    ]);
    exit();
}

// 🧠 AUTO ROLE DETECTION
if ($email == "admin@hit.ac.zw") {
    $role = "supervisor";
}
elseif (str_contains($email, ".staff@")) {
    $role = "staff";
}
else {
    $role = "student";
}

// Check user
$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $user = $result->fetch_assoc();

    // Update role + google info
    $stmt = $conn->prepare("UPDATE users SET role=?, google_id=?, profile_pic=? WHERE id=?");
    $stmt->bind_param("sssi", $role, $google_id, $picture, $user['id']);
    $stmt->execute();

    $user['role'] = $role;

} else {

    // New user
    $stmt = $conn->prepare("INSERT INTO users (name,email,google_id,profile_pic,role) VALUES (?,?,?,?,?)");
    $stmt->bind_param("sssss", $name, $email, $google_id, $picture, $role);
    $stmt->execute();

    $user = [
        "id"=>$conn->insert_id,
        "name"=>$name,
        "email"=>$email,
        "role"=>$role
    ];
}

// ✅ CREATE SESSION
$_SESSION['user_id'] = $user['id'];
$_SESSION['name'] = $user['name'];
$_SESSION['role'] = $user['role'];
$_SESSION['profile_pic'] = $picture;

// 🎯 REDIRECT
if ($user['role'] == "student") {
    $redirect = "../student/dashboard.php";
}
elseif ($user['role'] == "staff") {
    $redirect = "../staff/dashboard.php";
}
else {
    $redirect = "../supervisor/dashboard.php";
}

echo json_encode([
    "status"=>"success",
    "redirect"=>$redirect
]);