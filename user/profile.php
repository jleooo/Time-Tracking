<?php
$page_title = "User Profile";
require_once '../includes/config.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];

// Handle profile update
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validate inputs
    if (empty($name) || empty($username) || empty($email)) {
        setFlashMessage('error', 'Name, username, and email are required.');
        header("Location: profile.php");
        exit();
    }
    
    // Get current user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        setFlashMessage('error', 'User not found.');
        header("Location: profile.php");
        exit();
    }
    
    // Check if username or email exists (except for current user)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$username, $email, $user_id]);
    if ($stmt->fetch()) {
        setFlashMessage('error', 'Username or email already exists.');
        header("Location: profile.php");
        exit();
    }
    
    // Password change is optional
    $password_changed = false;
    if (!empty($new_password)) {
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            setFlashMessage('error', 'Current password is incorrect.');
            header("Location: profile.php");
            exit();
        }
        
        if ($new_password !== $confirm_password) {
            setFlashMessage('error', 'New passwords do not match.');
            header("Location: profile.php");
            exit();
        }
        
        $password_validation = validatePassword($new_password);
        if ($password_validation !== true) {
            setFlashMessage('error', $password_validation);
            header("Location: profile.php");
            exit();
        }
        
        $password_changed = true;
    }
    
    try {
        if ($password_changed) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $username, $email, $hashed_password, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $username, $email, $user_id]);
        }
        
        // Update session username
        $_SESSION['username'] = $username;
        
        setFlashMessage('success', 'Profile updated successfully.');
        header("Location: profile.php");
        exit();
    } catch (PDOException $e) {
        setFlashMessage('error', 'Database error: ' . $e->getMessage());
        header("Location: profile.php");
        exit();
    }
}

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    setFlashMessage('error', 'User not found.');
    header("Location: dashboard.php");
    exit();
}

require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">User Profile</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                        <small class="form-text text-muted">Required only if changing password</small>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                        <small class="form-text text-muted">
                            Password must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters with uppercase, lowercase, number, and special character.
                        </small>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
