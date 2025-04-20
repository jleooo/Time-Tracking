<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = "Login";
require_once 'includes/header.php';
?>

<div class="row justify-content-center align-items-center min-vh-100" style="background: #f4f6f9;">
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-lg rounded-4">
            <!-- Card header with centered logo -->
            <div class="card-header bg-transparent text-center border-0 pt-4 pb-0">
                <img src="assets/img/log.png" alt="Logo" class="img-fluid mb-2" style="max-width: 120px; height: auto;">
                <h3 class="mt-3 fw-bold text-dark">LOGIN</h3>
            </div>

            <div class="card-body px-4 pb-4 pt-2">
                <form action="includes/auth.php" method="POST">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control rounded-3" id="username" name="username" placeholder="Username" required>
                        <label for="username">Username</label>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="password" class="form-control rounded-3" id="password" name="password" placeholder="Password" required>
                        <label for="password">Password</label>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="login" class="btn btn-primary btn-lg rounded-pill shadow-sm">Login</button>
                        <a href="register.php" class="btn btn-outline-secondary btn-lg rounded-pill">Register</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
