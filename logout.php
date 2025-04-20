<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    logActivity($_SESSION['user_id'], 'logout');
    
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    setFlashMessage('success', 'You have been logged out successfully.');
}

header("Location: login.php");
exit();
?>