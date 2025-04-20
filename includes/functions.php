<?php
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        header("Location: login.php?timeout=1");
        exit();
    }
    $_SESSION['last_activity'] = time();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function redirectIfNotAdmin() {
    redirectIfNotLoggedIn();
    if (!isAdmin()) {
        header("Location: ../user/dashboard.php");
        exit();
    }
}

function validatePassword($password) {
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return "Password must be at least ".PASSWORD_MIN_LENGTH." characters long.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return "Password must contain at least one uppercase letter.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        return "Password must contain at least one lowercase letter.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        return "Password must contain at least one number.";
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        return "Password must contain at least one special character.";
    }
    return true;
}

function logActivity($user_id, $action) {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    // Check if this session ID already exists
    $check = $pdo->prepare("SELECT 1 FROM sessions WHERE session_id = ?");
    $check->execute([session_id()]);

    if (!$check->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO sessions (session_id, user_id, ip_address, user_agent) VALUES (?, ?, ?, ?)");
        $stmt->execute([session_id(), $user_id, $ip, $user_agent]);
    }
}

function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message']['message'];
        $type = $_SESSION['flash_message']['type'];
        
        echo "<script>
            Swal.fire({
                icon: '$type',
                title: '$message',
                showConfirmButton: true,
                timer: 3000
            });
        </script>";
        
        unset($_SESSION['flash_message']);
    }
}

function getTodaysLog($user_id) {
    global $pdo;
    $today = date('Y-m-d');
    
    $stmt = $pdo->prepare("SELECT * FROM time_logs WHERE user_id = ? AND date = ?");
    $stmt->execute([$user_id, $today]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function timeIn($user_id) {
    global $pdo;
    $today = date('Y-m-d');
    $now = date('H:i:s');
    
    // Check if already timed in today
    $existing = getTodaysLog($user_id);
    if ($existing) {
        return false;
    }
    
    $stmt = $pdo->prepare("INSERT INTO time_logs (user_id, date, time_in) VALUES (?, ?, ?)");
    return $stmt->execute([$user_id, $today, $now]);
}

function timeOut($user_id) {
    global $pdo;
    $today = date('Y-m-d');
    $now = date('H:i:s');
    
    // Get today's log
    $log = getTodaysLog($user_id);
    if (!$log || $log['time_out']) {
        return false;
    }
    
    $stmt = $pdo->prepare("UPDATE time_logs SET time_out = ? WHERE user_id = ? AND date = ?");
    return $stmt->execute([$now, $user_id, $today]);
}
?>
