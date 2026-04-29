<?php

function createNotification($conn, $user_id, $message){
    $safe_user_id = (int)$user_id;
    $safe_message = $conn->real_escape_string($message);

    $conn->query("INSERT INTO notifications (user_id, message) VALUES ($safe_user_id, '$safe_message')");
}

function ensureRequestActivityTable($conn){
    static $checked = false;
    if($checked){
        return;
    }
    $conn->query("CREATE TABLE IF NOT EXISTS request_activity (
        id INT AUTO_INCREMENT PRIMARY KEY,
        request_id INT NOT NULL,
        status VARCHAR(50) NOT NULL,
        remarks TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $checked = true;
}

function logRequestActivity($conn, $request_id, $status, $remarks = null){
    ensureRequestActivityTable($conn);
    $safe_request_id = (int)$request_id;
    $safe_status = $conn->real_escape_string($status);
    $safe_remarks = $remarks !== null ? "'".$conn->real_escape_string($remarks)."'" : "NULL";
    $conn->query("INSERT INTO request_activity (request_id, status, remarks) VALUES ($safe_request_id, '$safe_status', $safe_remarks)");
}

function getSpecializationByIssueType($issue_type){
    if($issue_type == 1){
        return "plumber";
    }

    if($issue_type == 2){
        return "electrician";
    }

    return "carpenter";
}

function assignTechnician($conn, $issue_type, $request_id, $available_time){
    $specialization = getSpecializationByIssueType($issue_type);
    $safe_time = $conn->real_escape_string($available_time);
    $safe_request_id = (int)$request_id;

    $sql = "SELECT staff.id, staff.user_id, COUNT(active_requests.id) AS active_count
            FROM staff
            LEFT JOIN requests AS active_requests
                ON active_requests.assigned_staff = staff.id
                AND active_requests.status = 'in_progress'
            WHERE staff.specialization='$specialization'
            AND staff.status='free'
            AND staff.id NOT IN (
                SELECT assigned_staff
                FROM requests
                WHERE available_time='$safe_time'
                AND status='in_progress'
                AND assigned_staff IS NOT NULL
            )
            GROUP BY staff.id, staff.user_id
            ORDER BY active_count ASC, staff.id ASC
            LIMIT 1";

    $result = $conn->query($sql);

    if($result && $result->num_rows > 0){
        $staff = $result->fetch_assoc();
        $staff_id = (int)$staff['id'];

        $assign = "UPDATE requests
                   SET assigned_staff='$staff_id',
                       status='in_progress'
                   WHERE id=$safe_request_id";

        $conn->query($assign);
        logRequestActivity($conn, $safe_request_id, "in_progress", "Technician assigned.");

        $update = "UPDATE staff
                   SET status='occupied'
                   WHERE id=$staff_id";

        $conn->query($update);

        createNotification($conn, $staff['user_id'], "New maintenance task assigned (Request #$safe_request_id) for $safe_time.");

        return true;
    }

    return false;
}

function notifySchedulingConflictOnce($conn, $request_id, $student_id, $available_time){
    $safe_request_id = (int)$request_id;
    $safe_student_id = (int)$student_id;
    $safe_time = $conn->real_escape_string($available_time);

    $marker = "[SCHEDULING_CONFLICT][Request #$safe_request_id][Time $safe_time]";
    $safe_marker = $conn->real_escape_string($marker);

    $exists = $conn->query("SELECT id FROM notifications
                            WHERE user_id=$safe_student_id
                            AND message LIKE '%$safe_marker%'
                            LIMIT 1");

    if(!$exists || $exists->num_rows === 0){
        $message = "Request #$safe_request_id cannot be assigned at $safe_time due to resource unavailability. Please update your preferred time. $marker";
        createNotification($conn, $safe_student_id, $message);
    }
}

function processDueAssignments($conn, $request_id = null){
    $where_request = "";

    if($request_id !== null){
        $safe_request_id = (int)$request_id;
        $where_request = " AND requests.id='$safe_request_id'";
    }

    $sql = "SELECT requests.id, requests.student_id, requests.issue_type_id, requests.available_time
            FROM requests
            WHERE requests.status='pending'
            AND requests.assigned_staff IS NULL
            AND requests.available_time <= NOW()".$where_request;

    $result = $conn->query($sql);

    if(!$result){
        return;
    }

    while($row = $result->fetch_assoc()){
        $assigned = assignTechnician(
            $conn,
            (int)$row['issue_type_id'],
            (int)$row['id'],
            $row['available_time']
        );

        if($assigned){
            createNotification($conn, (int)$row['student_id'], "Request #".(int)$row['id']." has now been assigned and is in progress.");
        } else {
            notifySchedulingConflictOnce($conn, (int)$row['id'], (int)$row['student_id'], $row['available_time']);
        }
    }
}

?>