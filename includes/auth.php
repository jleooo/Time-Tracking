<?php
require_once 'config.php';

// Handle login
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = TRUE");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        
        logActivity($user['id'], 'login');
        
        if ($user['role'] === 'admin') {
            setFlashMessage('success', 'Login successful! Welcome Admin.');
            header("Location: /time_tracking_system/admin/dashboard.php");
        } else {
            setFlashMessage('success', 'Login successful!');
            header("Location: /time_tracking_system/user/dashboard.php");
        }
        exit();
    } else {
        setFlashMessage('error', 'Invalid username or password.');
        header("Location: /time_tracking_system/login.php");
        exit();
    }
}

// Handle registration
if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate password match
    if ($password !== $confirm_password) {
        setFlashMessage('error', 'Passwords do not match.');
        header('Location: ../register.php');
        exit();
    }

    // Validate password strength
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{' . PASSWORD_MIN_LENGTH . ',}$/', $password)) {
        setFlashMessage('error', 'Password does not meet the strength requirements.');
        header('Location: ../register.php');
        exit();
    }

    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->rowCount() > 0) {
        setFlashMessage('error', 'Username or email already exists.');
        header('Location: ../register.php');
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password, role) VALUES (?, ?, ?, ?, 'user')");
    if ($stmt->execute([$name, $username, $email, $hashed_password])) {
        setFlashMessage('success', 'Account created successfully. Please log in.');
        header('Location: ../login.php');
        exit();
    } else {
        setFlashMessage('error', 'Something went wrong. Please try again.');
        header('Location: ../register.php');
        exit();
    }
}
?>
