<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/assign_staff.php";

/** @var mysqli $conn */
$student_id = (int) $_SESSION['user_id'];

runPendingAssignments($conn);

$requests = [];
$stmt = $conn->prepare(
    "SELECT r.*, it.issue_name
     FROM requests r
     JOIN issue_types it ON r.issue_type_id = it.id
     WHERE r.student_id = ?
     ORDER BY r.created_at DESC"
);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result instanceof mysqli_result) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}

$alerts = [];
$alertStmt = $conn->prepare(
    "SELECT id, message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5"
);
$alertStmt->bind_param("i", $student_id);
$alertStmt->execute();
$alertResult = $alertStmt->get_result();
if ($alertResult instanceof mysqli_result) {
    while ($notice = $alertResult->fetch_assoc()) {
        $alerts[] = $notice;
    }
}

$pending_count = 0;
$in_progress_count = 0;
$completed_count = 0;
$countStmt = $conn->prepare("SELECT status, COUNT(*) AS total FROM requests WHERE student_id = ? GROUP BY status");
$countStmt->bind_param("i", $student_id);
$countStmt->execute();
$countResult = $countStmt->get_result();
if ($countResult instanceof mysqli_result) {
    while ($count = $countResult->fetch_assoc()) {
        if ($count['status'] === 'pending') {
            $pending_count = (int) $count['total'];
        } elseif ($count['status'] === 'in_progress') {
            $in_progress_count = (int) $count['total'];
        } elseif ($count['status'] === 'completed') {
            $completed_count = (int) $count['total'];
        }
    }
}
?>

<?php include("../includes/header.php"); ?>
<?php include("../includes/sidebar.php"); ?>

<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-1 fw-bold text-white"><i class="bi bi-list-check text-warning"></i> My Maintenance Requests</h3>
                <p class="mb-0 text-white">Track the status of your maintenance requests.</p>
            </div>
            <i class="bi bi-tools display-5 text-warning"></i>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Pending</div><h4 class="mb-0"><?php echo $pending_count; ?></h4></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">In Progress</div><h4 class="mb-0"><?php echo $in_progress_count; ?></h4></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Completed</div><h4 class="mb-0"><?php echo $completed_count; ?></h4></div></div></div>
    </div>

    <?php if (!empty($alerts)): ?>
    <div class="card border-0 shadow mb-3">
        <div class="card-body">
            <h5 class="mb-3"><i class="bi bi-bell me-1"></i>Recent System Alerts</h5>
            <?php foreach ($alerts as $notice): ?>
                <div class="alert alert-warning mb-2">
                    <div><?php echo htmlspecialchars($notice['message']); ?></div>
                    <small class="text-muted"><?php echo date('d M Y, H:i', strtotime((string) $notice['created_at'])); ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="card shadow border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Issue Type</th>
                            <th>Description</th>
                            <th>Hostel</th>
                            <th>Room</th>
                            <th>Status</th>
                            <th>Available Time</th>
                            <th>Modify Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($requests)): ?>
                            <?php foreach ($requests as $row): ?>
                                <tr>
                                    <td><?php echo (int) $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['issue_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td><?php echo htmlspecialchars($row['hostel']); ?></td>
                                    <td><?php echo htmlspecialchars($row['room']); ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'pending'): ?>
                                            <span class='badge bg-warning'>Pending</span>
                                        <?php elseif ($row['status'] === 'in_progress'): ?>
                                            <span class='badge bg-primary'>In Progress</span>
                                        <?php else: ?>
                                            <span class='badge bg-success'>Completed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d M Y, H:i', strtotime((string) $row['available_time'])); ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'pending'): ?>
                                            <form method="POST" action="update_request_time.php" class="d-flex gap-2">
                                                <input type="hidden" name="request_id" value="<?php echo (int) $row['id']; ?>">
                                                <input type="datetime-local" name="available_time" class="form-control form-control-sm" required>
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                            </form>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $current_time = date('Y-m-d H:i:s');
                                        $isConfirmable = $row['status'] === 'in_progress'
                                            && $current_time >= date('Y-m-d H:i:s', strtotime($row['available_time'] . ' +1 hour'));
                                        ?>
                                        <?php if ($isConfirmable): ?>
                                            <a href="confirm_repair.php?id=<?php echo (int) $row['id']; ?>" class="btn btn-primary btn-sm">Confirm Repair</a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan='9' class='text-center'>No requests submitted yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="form-text mt-2">If a technician does not show up, use <strong>Confirm Repair</strong> after one hour to report it and reschedule.</div>
        </div>
    </div>

    <div class="mt-4">
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<?php include("../includes/sidebar_end.php"); ?>
<?php include("../includes/footer.php"); ?>