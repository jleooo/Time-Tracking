<?php
$page_title = "Manage User";
require_once '../includes/config.php';
redirectIfNotAdmin();

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$is_edit = $user_id > 0;

// Handle form submission
if (isset($_POST['save'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Password fields are optional for edit
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validate inputs
    if (empty($username) || empty($email) || empty($role)) {
        setFlashMessage('error', 'All fields except password are required.');
        header("Location: manage_user.php?user_id=$user_id");
        exit();
    }
    
    if (!empty($password) && $password !== $confirm_password) {
        setFlashMessage('error', 'Passwords do not match.');
        header("Location: manage_user.php?user_id=$user_id");
        exit();
    }
    
    if (!empty($password)) {
        $password_validation = validatePassword($password);
        if ($password_validation !== true) {
            setFlashMessage('error', $password_validation);
            header("Location: manage_user.php?user_id=$user_id");
            exit();
        }
    }
    
    // Check if username or email exists (except for current user)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$username, $email, $user_id]);
    if ($stmt->fetch()) {
        setFlashMessage('error', 'Username or email already exists.');
        header("Location: manage_user.php?user_id=$user_id");
        exit();
    }
    
    try {
        if ($is_edit) {
            // Update existing user
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, is_active = ?, password = ? WHERE id = ?");
                $stmt->execute([$username, $email, $role, $is_active, $hashed_password, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$username, $email, $role, $is_active, $user_id]);
            }
            
            setFlashMessage('success', 'User updated successfully.');
        } else {
            // Create new user (password is required)
            if (empty($password)) {
                setFlashMessage('error', 'Password is required for new users.');
                header("Location: manage_user.php");
                exit();
            }
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password, $role, $is_active]);
            
            setFlashMessage('success', 'User created successfully.');
            $user_id = $pdo->lastInsertId();
        }
        
        header("Location: users.php");
        exit();
    } catch (PDOException $e) {
        setFlashMessage('error', 'Database error: ' . $e->getMessage());
        header("Location: manage_user.php?user_id=$user_id");
        exit();
    }
}

// Get user data if editing
$user = null;
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        setFlashMessage('error', 'User not found.');
        header("Location: users.php");
        exit();
    }
}

require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-secondary text-white">
                <h5 class="card-title mb-0"><?php echo $is_edit ? 'Edit User' : 'Add New User'; ?></h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="form-text text-muted">
                                <?php echo $is_edit ? 'Leave blank to keep current password.' : 'Password must be at least '.PASSWORD_MIN_LENGTH.' characters with uppercase, lowercase, number, and special character.'; ?>
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="user" <?php echo isset($user['role']) && $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo isset($user['role']) && $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       <?php echo !$is_edit || (isset($user['is_active']) && $user['is_active']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="users.php" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" name="save" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>