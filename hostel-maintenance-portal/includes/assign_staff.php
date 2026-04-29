<?php

require_once __DIR__ . "/send_email.php";

/* ===================== NOTIFICATIONS ===================== */

function createNotification($conn, $user_id, $message){
    $user_id = (int)$user_id;
    $message = $conn->real_escape_string($message);

    $conn->query("INSERT INTO notifications (user_id, message) VALUES ($user_id, '$message')");
}

/* ===================== ACTIVITY LOG ===================== */

function logRequestActivity($conn, $request_id, $status, $remarks = null){
    $request_id = (int)$request_id;
    $status = $conn->real_escape_string($status);
    $remarks = $remarks ? "'" . $conn->real_escape_string($remarks) . "'" : "NULL";

    $conn->query("
        INSERT INTO request_activity (request_id, status, remarks)
        VALUES ($request_id, '$status', $remarks)
    ");
}

/* ===================== SPECIALIZATION ===================== */

function getSpecializationByIssueType($issue_type){
    if($issue_type == 1) return "plumber";
    if($issue_type == 2) return "electrician";
    return "carpenter";
}

/* ===================== ASSIGN TECHNICIAN ===================== */

function assignTechnician($conn, $issue_type, $request_id, $available_time){

    $specialization = getSpecializationByIssueType($issue_type);
    $request_id = (int)$request_id;
    $available_time = $conn->real_escape_string($available_time);

    $sql = "SELECT staff.id, staff.user_id, users.name, users.email
            FROM staff
            INNER JOIN users ON users.id = staff.user_id
            WHERE staff.specialization = '$specialization'
            AND staff.status = 'free'
            LIMIT 1";

    $result = $conn->query($sql);

    if($result && $result->num_rows > 0){

        $staff = $result->fetch_assoc();
        $staff_id = (int)$staff['id'];

        // assign
        $conn->query("
            UPDATE requests
            SET assigned_staff = $staff_id,
                status = 'in_progress'
            WHERE id = $request_id
        ");

        // mark busy
        $conn->query("UPDATE staff SET status='occupied' WHERE id=$staff_id");

        logRequestActivity($conn, $request_id, "in_progress", "Technician assigned");

        /* ===== EMAIL TECHNICIAN ===== */
        if(!empty($staff['email'])){
            sendEmail(
                $staff['email'],
                $staff['name'],
                "New Maintenance Task Assigned",
                "
                <h3>New Task Assigned</h3>
                <p>Hello {$staff['name']},</p>
                <p>You have been assigned a maintenance request.</p>
                <p><b>Request ID:</b> #$request_id</p>
                <p><b>Scheduled Time:</b> $available_time</p>
                "
            );
        }

        return true;
    }

    return false;
}

/* ===================== CONFLICT NOTIFICATION ===================== */

function notifySchedulingConflictOnce($conn, $request_id, $student_id, $available_time){

    $request_id = (int)$request_id;
    $student_id = (int)$student_id;

    createNotification(
        $conn,
        $student_id,
        "Request #$request_id could not be assigned at $available_time. Please update your time."
    );
}

/* ===================== MAIN PROCESS ===================== */

function processDueAssignments($conn){

    $sql = "SELECT requests.id, requests.student_id, requests.issue_type_id, requests.available_time,
                   users.name AS student_name, users.email AS student_email
            FROM requests
            INNER JOIN users ON users.id = requests.student_id
            WHERE requests.status='pending'
            AND requests.available_time <= NOW()";

    $result = $conn->query($sql);

    if(!$result) return;

    while($row = $result->fetch_assoc()){

        $assigned = assignTechnician(
            $conn,
            (int)$row['issue_type_id'],
            (int)$row['id'],
            $row['available_time']
        );

        if($assigned){

            /* ===== SYSTEM NOTIFICATION ===== */
            createNotification(
                $conn,
                (int)$row['student_id'],
                "Request #{$row['id']} is now in progress."
            );

            /* ===== EMAIL STUDENT ===== */
            if(!empty($row['student_email'])){
                sendEmail(
                    $row['student_email'],
                    $row['student_name'],
                    "Technician Assigned",
                    "
                    <h3>Technician Assigned</h3>
                    <p>Hello {$row['student_name']},</p>
                    <p>Your request is now being handled.</p>
                    <p><b>Request ID:</b> #{$row['id']}</p>
                    <p><b>Status:</b> In Progress</p>
                    "
                );
            }

        } else {

            notifySchedulingConflictOnce(
                $conn,
                (int)$row['id'],
                (int)$row['student_id'],
                $row['available_time']
            );

            /* ===== EMAIL FAILURE ===== */
            if(!empty($row['student_email'])){
                sendEmail(
                    $row['student_email'],
                    $row['student_name'],
                    "No Technician Available",
                    "
                    <h3>No Technician Available</h3>
                    <p>Hello {$row['student_name']},</p>
                    <p>No technician is available at your selected time.</p>
                    <p><b>Request ID:</b> #{$row['id']}</p>
                    <p>Please update your preferred time.</p>
                    "
                );
            }
        }
    }
}
?>