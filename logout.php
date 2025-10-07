<?php
session_start();
require_once 'secure_config.php';

if (isset($_SESSION['user_id'])) {
    // Log the logout
    $secureDB->logAudit($_SESSION['user_id'], 'User Logout', SecurityUtils::getClientIP());
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login
header('Location: secure_login.php');
exit;
?>
