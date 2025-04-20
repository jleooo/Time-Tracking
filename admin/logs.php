<?php
$page_title = "Time Logs";
require_once '../includes/config.php';
redirectIfNotAdmin();

// Handle CSV export
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="time_logs_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['User ID', 'Username', 'Date', 'Time In', 'Time Out']);
    
    $stmt = $pdo->prepare("SELECT t.*, u.username 
                          FROM time_logs t 
                          JOIN users u ON t.user_id = u.id 
                          ORDER BY t.date DESC, t.time_in DESC");
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['user_id'],
            $row['username'],
            $row['date'],
            $row['time_in'] ?? '--:--:--',
            $row['time_out'] ?? '--:--:--'
        ]);
    }
    
    fclose($output);
    exit();
}

// Get all time logs with pagination and search
$search = $_GET['search'] ?? '';
$user_filter = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$date_filter = $_GET['date'] ?? '';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$query = "SELECT t.*, u.username 
          FROM time_logs t 
          JOIN users u ON t.user_id = u.id 
          WHERE (u.username LIKE ? OR u.email LIKE ?)";
$params = ["%$search%", "%$search%"];

if ($user_filter) {
    $query .= " AND t.user_id = ?";
    $params[] = $user_filter;
}

if ($date_filter) {
    $query .= " AND t.date = ?";
    $params[] = $date_filter;
}

// ✅ FIX: Directly inject validated integers for LIMIT and OFFSET
$query_with_pagination = "$query ORDER BY t.date DESC, t.time_in DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query_with_pagination);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total logs
$count_query = "SELECT COUNT(*) FROM ($query) as total";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_logs = $stmt->fetchColumn();
$total_pages = ceil($total_logs / $limit);

// Get all users for filter dropdown
$stmt = $pdo->prepare("SELECT id, username FROM users ORDER BY username");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<style>
    /* Ensure the page uses the full height of the screen */
    html, body {
        height: 100%;
        margin: 0;
    }

    /* Card body should fill available space */
    .card-body {
        min-height: calc(100vh - 70px); /* Adjust based on header height */
        display: flex;
        flex-direction: column;
    }

    /* Pagination and footer */
    .pagination {
        margin-top: auto;
    }

    /* Optional: Margin to add spacing for a nice layout */
    .table-responsive {
        margin-bottom: 30px;
    }

    footer {
        margin-top: auto;
    }
</style>

<div class="card shadow">
    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Time Logs</h5>
        <div>
            <a href="logs.php?export=1" class="btn btn-success btn-sm me-1">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search by username" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select name="user_id" class="form-select">
                    <option value="">All Users</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo $user_filter == $user['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date_filter); ?>">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="logs.php" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['username']); ?></td>
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
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&user_id=<?php echo $user_filter; ?>&date=<?php echo $date_filter; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&user_id=<?php echo $user_filter; ?>&date=<?php echo $date_filter; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&user_id=<?php echo $user_filter; ?>&date=<?php echo $date_filter; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
