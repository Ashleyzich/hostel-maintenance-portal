<?php

function issueTypeToSpecialization($issue_type){
    if($issue_type == 1){
        return 'plumber';
    }

    if($issue_type == 2){
        return 'electrician';
    }

    return 'carpenter';
}

function createNotificationIfMissing($conn, $user_id, $message){
    $safe_user_id = (int)$user_id;
    $safe_message = $conn->real_escape_string($message);

    $exists = $conn->query("SELECT id FROM notifications WHERE user_id='$safe_user_id' AND message='$safe_message' LIMIT 1");

    if($exists && $exists->num_rows == 0){
        $conn->query("INSERT INTO notifications (user_id, message) VALUES ('$safe_user_id', '$safe_message')");
    }
}

function notifySupervisors($conn, $message){
    $supervisors = $conn->query("SELECT id FROM users WHERE role='supervisor'");

    if(!$supervisors){
        return;
    }

    while($row = $supervisors->fetch_assoc()){
        createNotificationIfMissing($conn, $row['id'], $message);
    }
}

function assignTechnician($conn, $issue_type, $request_id, $available_time){
    $specialization = issueTypeToSpecialization($issue_type);

    $safe_time = $conn->real_escape_string($available_time);
    $safe_request_id = (int)$request_id;

    $sql = "SELECT staff.id
            FROM staff
            WHERE staff.specialization='$specialization'
            AND staff.status='free'
            AND staff.id NOT IN (
                SELECT assigned_staff
                FROM requests
                WHERE available_time='$safe_time'
                AND status='in_progress'
                AND assigned_staff IS NOT NULL
            )
            ORDER BY staff.id ASC
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

        return $staff_id;
    }

    return false;
}

function runPendingAssignments($conn){
    $due = $conn->query("SELECT id, student_id, issue_type_id, available_time
                         FROM requests
                         WHERE status='pending'
                         AND available_time <= NOW()
                         ORDER BY available_time ASC");

    if(!$due){
        return;
    }

    while($request = $due->fetch_assoc()){
        $request_id = (int)$request['id'];
        $student_id = (int)$request['student_id'];
        $available_time = $request['available_time'];

        $staff_id = assignTechnician($conn, $request['issue_type_id'], $request_id, $available_time);

        if($staff_id){
            createNotificationIfMissing(
                $conn,
                $student_id,
                "Request #$request_id is now in progress. Technician assignment completed for your selected time slot."
            );
            continue;
        }

        $conflict_message = "Scheduling conflict for request #$request_id: the request is logged but cannot be fulfilled at your chosen time ($available_time) due to technician unavailability. Please update your preferred interval.";
        createNotificationIfMissing($conn, $student_id, $conflict_message);

        $supervisor_message = "No technician available for request #$request_id at $available_time. Student has been asked to provide a new preferred interval.";
        notifySupervisors($conn, $supervisor_message);
    }
}

?>