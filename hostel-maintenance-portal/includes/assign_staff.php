<?php

function createNotification($conn, $user_id, $message){
    $safe_user_id = (int)$user_id;
    $safe_message = $conn->real_escape_string($message);

    $conn->query("INSERT INTO notifications (user_id, message) VALUES ('$safe_user_id', '$safe_message')");
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
                   WHERE id='$safe_request_id'";

        $conn->query($assign);

        $update = "UPDATE staff
                   SET status='occupied'
                   WHERE id='$staff_id'";

        $conn->query($update);

        createNotification($conn, $staff['user_id'], "New maintenance task assigned (Request #$safe_request_id) for $safe_time.");

        return true;
    }

    return false;
}

?>