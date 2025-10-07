<?php
require_once 'config.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // For demo purpose, direct password compare. Use password_hash and verify in production
        if ($password === $row['password']) {
            session_regenerate_id(true);
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'] ?? 'user';
            $_SESSION['last_activity'] = time();

            if ($_SESSION['role'] === 'admin') {
                header('Location: admin.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "User not found";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>GamerZone Login</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');
    body {
        margin: 0; padding: 0; height: 100vh;
        background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
        font-family: 'Press Start 2P', monospace;
        display: flex;
        justify-content: center;
        align-items: center;
        color: #00ff00;
    }
    .login-container {
        background: rgba(0, 0, 0, 0.85);
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 0 30px #00ff00;
        width: 350px;
        text-align: center;
    }
    h1 {
        margin-bottom: 30px;
        font-size: 22px;
        letter-spacing: 2px;
        color: #00ff00;
        text-shadow: 0 0 6px #00ff00;
    }
    input[type="text"], input[type="password"] {
        background: black;
        border: 2px solid #00ff00;
        border-radius: 6px;
        color: #00ff00;
        padding: 12px 10px;
        margin: 15px 0;
        width: 90%;
        font-family: 'Press Start 2P', monospace;
        font-size: 14px;
    }
    input[type="text"]:focus, input[type="password"]:focus {
        outline: none;
        box-shadow: 0 0 10px #00ff00;
        border-color: #00ff00;
    }
    button {
        background: #00ff00;
        border: none;
        padding: 14px 0;
        width: 95%;
        border-radius: 6px;
        font-family: 'Press Start 2P', monospace;
        font-weight: bold;
        color: black;
        font-size: 18px;
        cursor: pointer;
        text-transform: uppercase;
        box-shadow: 0 0 20px #00ff00;
        transition: background-color 0.3s ease;
        margin-top: 20px;
    }
    button:hover {
        background-color: #00cc00;
    }
    .error-message {
        color: #ff3333;
        font-weight: bold;
        margin-top: 15px;
        text-shadow: 0 0 10px #ff3333;
    }
    a {
        color: #00ff00;
        font-size: 12px;
        text-decoration: none;
        margin-top: 15px;
        display: inline-block;
        transition: color 0.3s ease;
    }
    a:hover {
        color: #00cc00;
    }
</style>
</head>
<body>
<div class="login-container">
    <h1>GAMERZONE LOGIN</h1>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Enter gamer tag" autocomplete="off" required />
        <input type="password" name="password" placeholder="Enter password" required />
        <button type="submit">Login</button>
    </form>
    <?php if (!empty($error)): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <a href="register.html">Create an Account</a>
</div>
</body>
</html>
