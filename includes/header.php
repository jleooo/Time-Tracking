<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define a fallback app name if not defined
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Time System');
}

// Optional: Include functions file if needed
// require_once 'functions.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> | <?= $page_title ?? 'Welcome'; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
           <!-- Header -->
<header class="col-12 bg-primary text-white py-2 shadow-sm">
    <div class="d-flex justify-content-between align-items-center px-3">
        <h1 class="m-0 fs-4 fw-bold"><?= APP_NAME ?></h1>
        <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
            <div class="d-flex align-items-center">
                <span class="me-3 small">
                    Welcome, <strong><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></strong>
                </span>
                <a href="../logout.php" class="btn btn-light btn-sm text-primary fw-semibold border">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        <?php endif; ?>
    </div>
</header>

         <!-- Navbar and Main Content -->
<div class="d-flex">
    <?php if (function_exists('isLoggedIn') && isLoggedIn()) include 'navbar.php'; ?>

    <main class="flex-grow-1 p-4 bg-light rounded shadow-sm">
        <?php if (function_exists('displayFlashMessage')) displayFlashMessage(); ?>
