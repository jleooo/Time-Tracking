<?php
require_once 'includes/config.php';  // Make sure this is at the top
$page_title = "Register";
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title text-center">Create Account</h3>
            </div>
            <div class="card-body">
                <form action="includes/auth.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="form-text text-muted">
                            Password must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.
                        </small>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" name="register" class="btn btn-primary">Register</button>
                        <a href="login.php" class="btn btn-outline-secondary">Already have an account? Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
