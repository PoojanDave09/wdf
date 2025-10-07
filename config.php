<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_guild";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Try to create database if it doesn't exist
    try {
        $pdo = new PDO("mysql:host=$servername", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
        $pdo->exec("USE $dbname");
        
        // Create table
        $createTable = "CREATE TABLE IF NOT EXISTS events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_name VARCHAR(255) NOT NULL,
            event_type ENUM('Tournament', 'Quest', 'PvP Battle', 'Guild War', 'Training', 'Special Event') NOT NULL,
            description TEXT,
            start_date DATETIME NOT NULL,
            end_date DATETIME NOT NULL,
            location VARCHAR(255),
            max_participants INT DEFAULT 0,
            current_participants INT DEFAULT 0,
            prize_pool DECIMAL(10,2) DEFAULT 0.00,
            status ENUM('Upcoming', 'Active', 'Completed', 'Cancelled') DEFAULT 'Upcoming',
            difficulty_level ENUM('Beginner', 'Intermediate', 'Advanced', 'Expert', 'Master') DEFAULT 'Beginner',
            created_by VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $pdo->exec($createTable);
        
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Function to get status color
function getStatusColor($status) {
    switch($status) {
        case 'Upcoming': return '#00d4ff';
        case 'Active': return '#00ff88';
        case 'Completed': return '#ffd93d';
        case 'Cancelled': return '#ff6b6b';
        default: return '#a0a0a0';
    }
}

// Function to get difficulty color
function getDifficultyColor($difficulty) {
    switch($difficulty) {
        case 'Beginner': return '#74b9ff';
        case 'Intermediate': return '#ffd93d';
        case 'Advanced': return '#ff8e8e';
        case 'Expert': return '#9c27b0';
        case 'Master': return '#e91e63';
        default: return '#a0a0a0';
    }
}
?>
