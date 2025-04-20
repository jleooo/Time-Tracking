<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // ✅ Start session only if none is active
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'time_tracking_system');

// Application settings
define('APP_NAME', 'Time Tracking System');
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds
define('PASSWORD_MIN_LENGTH', 8); // Define the minimum password length as 8 characters (or any value you prefer)

// Create database connection
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Include functions
require_once 'functions.php';

// Check for session timeout
checkSessionTimeout();
?>
