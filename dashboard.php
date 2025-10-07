<?php
session_start();

// Check if logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: secure_login.php');
    exit;
}

$username = $_SESSION['username'] ?? 'User';
$gamer_tag = $_SESSION['gamer_tag'] ?? 'Player';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GamerZone Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #0a0a0a, #1a1a2e);
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255,255,255,0.1);
            border: 1px solid #00d4ff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #00d4ff;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .welcome {
            background: rgba(0,255,136,0.1);
            border: 1px solid #00ff88;
            color: #00ff88;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .user-info {
            background: rgba(0,0,0,0.3);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .info-item:last-child { border-bottom: none; }
        
        .info-label {
            color: #00d4ff;
            font-weight: bold;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #00d4ff, #00ff88);
            color: #000;
        }
        
        .btn-danger {
            background: linear-gradient(45deg, #ff6b6b, #ff8e8e);
            color: #fff;
        }
        
        .btn:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎮 GAMERZONE DASHBOARD</h1>
        </div>
        
        <div class="welcome">
            <h2>Welcome back, <?= htmlspecialchars($gamer_tag) ?>! 🎉</h2>
            <p>You have successfully logged into your secure GamerZone account.</p>
        </div>
        
        <div class="user-info">
            <h3 style="color: #00d4ff; margin-bottom: 15px;">Your Account Info</h3>
            <div class="info-item">
                <span class="info-label">Username:</span>
                <span><?= htmlspecialchars($username) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Gamer Tag:</span>
                <span><?= htmlspecialchars($gamer_tag) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Account Status:</span>
                <span style="color: #00ff88;">✅ Active</span>
            </div>
            <div class="info-item">
                <span class="info-label">Login Time:</span>
                <span><?= date('Y-m-d H:i:s') ?></span>
            </div>
        </div>
        
        <div class="actions">
            <a href="secure_register.php" class="btn btn-primary">🎮 Register New User</a>
            <a href="admin_dashboard.php" class="btn btn-primary">👑 Admin Panel</a>
            <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
        </div>
    </div>
</body>
</html>
