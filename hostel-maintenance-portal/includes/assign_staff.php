<?php

function assignTechnician($conn, $issue_type, $request_id, $available_time){

    // determine specialization
    if($issue_type == 1){
        $specialization = "plumber";
    }
    elseif($issue_type == 2){
        $specialization = "electrician";
    }
    else{
        $specialization = "carpenter";
    }

    /* Find technician free at that time */

    $sql = "SELECT staff.id
            FROM staff

            WHERE staff.specialization='$specialization'
            AND staff.status='free'

            AND staff.id NOT IN (
                SELECT assigned_staff
                FROM requests
                WHERE available_time='$available_time'
                AND status='in_progress'
            )

            LIMIT 1";

    $result = $conn->query($sql);

    if($result->num_rows > 0){

        $staff = $result->fetch_assoc();
        $staff_id = $staff['id'];

        // assign technician
        $assign = "UPDATE requests
                   SET assigned_staff='$staff_id',
                       status='in_progress'
                   WHERE id='$request_id'";

        $conn->query($assign);

        // mark technician occupied
        $update = "UPDATE staff
                   SET status='occupied'
                   WHERE id='$staff_id'";

        $conn->query($update);

        return true;

    }

    return false;

}

?>