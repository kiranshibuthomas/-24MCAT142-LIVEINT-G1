<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Redirect to dashboard if authenticated
header('Location: dashboard.php');
exit();
?> 