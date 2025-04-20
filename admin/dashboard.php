<?php
session_start(); // ✅ Added this line to fix session-related issues
$page_title = "Admin Dashboard";
require_once '../includes/config.php';
redirectIfNotAdmin();

// Get stats
$stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users");
$stmt->execute();
$total_users = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) as active_users FROM users WHERE is_active = TRUE");
$stmt->execute();
$active_users = $stmt->fetchColumn();

$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) as todays_logs FROM time_logs WHERE date = ?");
$stmt->execute([$today]);
$todays_logs = $stmt->fetchColumn();

require_once '../includes/header.php';
?>

<!-- Page Wrapper -->
<div class="container-fluid mt-4">
    <div class="row">
        <!-- Stats Cards -->
        <div class="col-md-4 mb-4">
            <div class="card stat-card shadow-sm border-0 text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <h2 class="card-text"><?php echo $total_users; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card stat-card shadow-sm border-0 text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Active Users</h5>
                    <h2 class="card-text"><?php echo $active_users; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card stat-card shadow-sm border-0 text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Today's Logs</h5>
                    <h2 class="card-text"><?php echo $todays_logs; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Logs Table -->
    <div class="card shadow mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="card-title mb-0">Recent Time Logs</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Date</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->prepare("SELECT t.*, u.username 
                                              FROM time_logs t 
                                              JOIN users u ON t.user_id = u.id 
                                              ORDER BY t.date DESC, t.time_in DESC 
                                              LIMIT 10");
                        $stmt->execute();
                        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['username']); ?></td>
                                <td><?php echo htmlspecialchars($log['date']); ?></td>
                                <td><?php echo htmlspecialchars($log['time_in'] ?? '--:--:--'); ?></td>
                                <td><?php echo htmlspecialchars($log['time_out'] ?? '--:--:--'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> <!-- End Page Wrapper -->

<?php
require_once '../includes/footer.php';
?>
