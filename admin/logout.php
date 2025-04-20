<?php
// Include the config.php file with an absolute path
require_once __DIR__ . '/../includes/config.php'; // Go one directory up from 'admin'

// Check if the user is logged in
if (isLoggedIn()) {
    logActivity($_SESSION['user_id'], 'logout');
    
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    setFlashMessage('success', 'You have been logged out successfully.');
}

// Redirect to the correct logout page (root logout.php)
header("Location: /logout.php");
exit();
?>
