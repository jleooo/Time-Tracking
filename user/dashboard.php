<?php
$page_title = "User Dashboard";
require_once '../includes/config.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$now = date('H:i:s');

// Get user details
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_name = $user['name'] ?? 'User'; // Default to 'User' if no name is found

// Set the allowed time intervals for Time In and Time Out
$time_in_intervals = [
    ['start' => '07:00:00', 'end' => '08:30:00'],
    ['start' => '13:00:00', 'end' => '13:45:00']
];

$time_out_intervals = [
    ['start' => '11:30:00', 'end' => '12:30:00'],
    ['start' => '16:30:00', 'end' => '18:00:00']
];

// Function to check if current time is within allowed intervals
function isTimeWithinInterval($current_time, $intervals) {
    foreach ($intervals as $interval) {
        if ($current_time >= $interval['start'] && $current_time <= $interval['end']) {
            return true;
        }
    }
    return false;
}

// Handle time in/out
if (isset($_POST['time_in'])) {
    if (isset($_SESSION['time_in_count']) && $_SESSION['time_in_count'] >= 2) {
        setFlashMessage('error', 'You have already timed in twice today.');
    } else {
        if (timeIn($user_id)) {
            $_SESSION['time_in_count'] = isset($_SESSION['time_in_count']) ? $_SESSION['time_in_count'] + 1 : 1;
            setFlashMessage('success', 'Time in recorded successfully.');
        } else {
            setFlashMessage('error', 'You have already timed in today.');
        }
    }
    header("Location: dashboard.php");
    exit();
}

if (isset($_POST['time_out'])) {
    if (isset($_SESSION['time_out_count']) && $_SESSION['time_out_count'] >= 2) {
        setFlashMessage('error', 'You have already timed out twice today.');
    } else {
        if (timeOut($user_id)) {
            $_SESSION['time_out_count'] = isset($_SESSION['time_out_count']) ? $_SESSION['time_out_count'] + 1 : 1;
            setFlashMessage('success', 'Time out recorded successfully.');
        } else {
            setFlashMessage('error', 'You need to time in first or have already timed out today.');
        }
    }
    header("Location: dashboard.php");
    exit();
}

// Get today's log
$today_log = getTodaysLog($user_id);

// Get user's recent logs
$stmt = $pdo->prepare("SELECT * FROM time_logs WHERE user_id = ? ORDER BY date DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="row">
    <!-- Welcome Card -->
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0 text-center">Welcome, <?php echo htmlspecialchars($user_name); ?></h5>
            </div>
            <div class="card-body text-center">
                <p>This is the Time In and Time Out System for the Nightingale Student Circle Students for MURSO 2025.</p>
            </div>
        </div>
    </div>

    <!-- Today's Time Card -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm"> <!-- Added 'shadow-sm' for a subtle card effect -->
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Today's Time</h5>
            </div>
            <div class="card-body text-center">
                <h4 id="current-date" class="mb-3"><?php echo $today; ?></h4>
                <h1 id="current-time" class="display-4 mb-4"><?php echo $now; ?></h1>
                
                <div class="d-flex justify-content-center gap-3">
                    <?php 
                    $can_time_in = isTimeWithinInterval($now, $time_in_intervals) && (!$today_log || !$today_log['time_in']);
                    if ($can_time_in): ?>
                        <form method="POST">
                            <button type="submit" name="time_in" class="btn btn-success btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Time In
                            </button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-success btn-lg" disabled>Time In</button>
                    <?php endif; ?>
                    
                    <?php 
                    $can_time_out = isTimeWithinInterval($now, $time_out_intervals) && ($today_log && $today_log['time_in'] && !$today_log['time_out']);
                    if ($can_time_out): ?>
                        <form method="POST">
                            <button type="submit" name="time_out" class="btn btn-danger btn-lg">
                                <i class="fas fa-sign-out-alt me-2"></i>Time Out
                            </button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-danger btn-lg" disabled>Time Out</button>
                    <?php endif; ?>
                </div>

                <div class="mt-3">
                    <p class="text-muted">
                        <strong>Time In:</strong> This button will only be visible between 7:00 AM to 8:30 AM or 1:00 PM to 1:45 PM.<br>
                        <strong>Time Out:</strong> This button will only be visible between 11:30 AM to 12:30 PM or 4:30 PM to 6:00 PM.
                    </p>
                </div>

                <?php if ($today_log): ?>
                    <div class="mt-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Time In</h5>
                                <p class="fs-4"><?php echo $today_log['time_in'] ?? '--:--:--'; ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Time Out</h5>
                                <p class="fs-4"><?php echo $today_log['time_out'] ?? '--:--:--'; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Time Logs Card -->
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Recent Time Logs</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['date']); ?></td>
                                    <td><?php echo htmlspecialchars($log['time_in'] ?? '--:--:--'); ?></td>
                                    <td><?php echo htmlspecialchars($log['time_out'] ?? '--:--:--'); ?></td>
                                    <td>
                                        <?php 
                                        if ($log['time_in'] && $log['time_out']) {
                                            $time_in = new DateTime($log['time_in']);
                                            $time_out = new DateTime($log['time_out']);
                                            $interval = $time_in->diff($time_out);
                                            echo $interval->format('%H:%I:%S');
                                        } else {
                                            echo '--:--:--';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
