<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // ✅ Start session only if none is active
}

// Database configuration using Railway environment variables
define('DB_HOST', getenv('mysql.railway.internal'));
define('DB_USER', getenv('root'));
define('DB_PASS', getenv('YIBhzPkjVzIuispoAqRrYbxYeJMApaZh'));
define('DB_NAME', getenv('railway'));
define('DB_PORT', getenv('3306') ?: 3306); // fallback to 3306 if not set

// Application settings
define('APP_NAME', 'Time Tracking System');
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds
define('PASSWORD_MIN_LENGTH', 8); // Define the minimum password length as 8 characters

// Create database connection using PDO
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected to Railway MySQL!"; // Optional: uncomment for debugging
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Include functions
require_once 'functions.php';

// Check for session timeout
checkSessionTimeout();
?>
