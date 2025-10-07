<?php
session_start();

// Simple database connection
$host = 'localhost';
$dbname = 'gamerzone_secure';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$errors = [];

// Simple CAPTCHA
if (!isset($_SESSION['login_captcha'])) {
    $_SESSION['login_captcha'] = rand(1000, 9999);
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Check CSRF
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Invalid request";
    }
    
    // Check CAPTCHA
    if ($_POST['captcha'] != $_SESSION['login_captcha']) {
        $errors[] = "Invalid CAPTCHA";
        $_SESSION['login_captcha'] = rand(1000, 9999);
    }
    
    if (empty($errors)) {
        $login = trim($_POST['login']);
        $password = $_POST['password'];
        
        if (empty($login) || empty($password)) {
            $errors[] = "Please enter username/email and password";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$login, $login]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password_hash'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['gamer_tag'] = $user['gamer_tag'];
                    $_SESSION['logged_in'] = true;
                    
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $errors[] = "Invalid username/email or password";
                    $_SESSION['login_captcha'] = rand(1000, 9999);
                }
            } catch (PDOException $e) {
                $errors[] = "Login failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GamerZone Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 100%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: rgba(255,255,255,0.1);
            border: 1px solid #00d4ff;
            border-radius: 15px;
            padding: 30px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 2rem;
            color: #00d4ff;
            margin-bottom: 10px;
        }
        
        .errors {
            background: rgba(255,107,107,0.1);
            border: 1px solid #ff6b6b;
            color: #ff6b6b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            color: #00d4ff;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .form-input {
            width: 100%;
            padding: 12px;
            background: rgba(0,0,0,0.3);
            border: 2px solid rgba(0,212,255,0.3);
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #00d4ff;
        }
        
        .captcha-container {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 10px;
        }
        
        .captcha-display {
            background: linear-gradient(45deg, #00d4ff, #00ff88);
            color: #000;
            padding: 15px 20px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.5rem;
            min-width: 100px;
            text-align: center;
        }
        
        .captcha-input {
            flex: 1;
        }
        
        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(45deg, #00d4ff, #00ff88);
            border: none;
            border-radius: 8px;
            color: #000;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #aaa;
        }
        
        .register-link a {
            color: #00ff88;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎮 GAMERZONE LOGIN</h1>
            <p>Access your gaming account</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p>⚠️ <?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label class="form-label">Username or Email</label>
                <input type="text" name="login" class="form-input" required 
                       placeholder="Enter username or email">
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required 
                       placeholder="Enter your password">
            </div>

            <div class="form-group">
                <label class="form-label">Security Code</label>
                <div class="captcha-container">
                    <div class="captcha-display"><?php echo $_SESSION['login_captcha']; ?></div>
                    <input type="number" name="captcha" class="form-input captcha-input" 
                           placeholder="Enter number" required>
                </div>
            </div>

            <button type="submit" class="submit-btn">
                🎮 Login
            </button>
        </form>

        <div class="register-link">
            <p>New to GamerZone? <a href="secure_register.php">Create account</a></p>
        </div>
    </div>
</body>
</html>
