<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Panel - GamerZone</title>
    <meta charset="UTF-8" />
    <!-- Add your gamer theme styles here -->
</head>
<body>
    <h1>Welcome Admin <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
    <p>This is the admin dashboard.</p>
    <a href="logout.php">Logout</a>
</body>
</html>
