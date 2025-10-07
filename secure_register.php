<?php
session_start();

// Simple database connection
$host = 'localhost';
$dbname = 'gamerzone_secure';
$username = 'root';
$password = '';

try {
    // Try to connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Create database and table if they don't exist
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
        $pdo->exec("USE $dbname");
        
        // Create users table
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            gamer_tag VARCHAR(50) UNIQUE NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

// Initialize variables
$errors = [];
$success = '';

// Simple CAPTCHA
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = rand(1000, 9999);
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Invalid request. Please try again.";
    }
    
    // Check CAPTCHA
    if (!isset($_POST['captcha']) || $_POST['captcha'] != $_SESSION['captcha']) {
        $errors[] = "Invalid CAPTCHA. Please try again.";
        $_SESSION['captcha'] = rand(1000, 9999);
    }
    
    if (empty($errors)) {
        // Get form data
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $gamer_tag = trim($_POST['gamer_tag'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Basic validation
        if (strlen($username) < 3) {
            $errors[] = "Username must be at least 3 characters";
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        
        if (strlen($gamer_tag) < 3) {
            $errors[] = "Gamer tag must be at least 3 characters";
        }
        
        if (strlen($full_name) < 2) {
            $errors[] = "Full name must be at least 2 characters";
        }
        
        if (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters";
        }
        
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
        
        // Check for existing users
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? OR gamer_tag = ?");
                $stmt->execute([$username, $email, $gamer_tag]);
                if ($stmt->fetch()) {
                    $errors[] = "Username, email, or gamer tag already exists";
                }
            } catch (PDOException $e) {
                $errors[] = "Database error occurred";
            }
        }
        
        // Register user
        if (empty($errors)) {
            try {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users (username, email, gamer_tag, full_name, password_hash) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $gamer_tag, $full_name, $password_hash]);
                
                $success = "Registration successful! You can now login.";
                $_SESSION['captcha'] = rand(1000, 9999); // New CAPTCHA
                
                // Clear form data on success
                $_POST = [];
                
            } catch (PDOException $e) {
                $errors[] = "Registration failed. Please try again.";
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
    <title>GamerZone Registration</title>
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
            max-width: 500px;
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
        
        .success {
            background: rgba(0,255,136,0.1);
            border: 1px solid #00ff88;
            color: #00ff88;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
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
            box-shadow: 0 0 10px rgba(0,212,255,0.3);
        }
        
        .captcha-container {
            display: flex;
            align-items: center;
            gap: 15px;
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
            transition: transform 0.2s;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #aaa;
        }
        
        .login-link a {
            color: #00ff88;
            text-decoration: none;
        }
        
        .login-link a:hover {
            color: #00d4ff;
        }
        
        @media (max-width: 768px) {
            .container { padding: 20px; }
            .header h1 { font-size: 1.5rem; }
            .captcha-container { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎮 GAMERZONE REGISTRATION</h1>
            <p>Join the ultimate gaming community</p>
        </div>

        <?php if ($success): ?>
            <div class="success">✅ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <strong>Please fix these errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" required 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                       placeholder="Enter username (min 3 characters)">
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       placeholder="Enter your email address">
            </div>

            <div class="form-group">
                <label class="form-label">Gamer Tag</label>
                <input type="text" name="gamer_tag" class="form-input" required 
                       value="<?php echo htmlspecialchars($_POST['gamer_tag'] ?? ''); ?>"
                       placeholder="Enter gamer tag (min 3 characters)">
            </div>

            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-input" required 
                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                       placeholder="Enter your full name">
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required 
                       placeholder="Enter password (min 6 characters)">
            </div>

            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-input" required 
                       placeholder="Confirm your password">
            </div>

            <div class="form-group">
                <label class="form-label">Security Verification</label>
                <div class="captcha-container">
                    <div class="captcha-display"><?php echo $_SESSION['captcha']; ?></div>
                    <input type="number" name="captcha" class="form-input captcha-input" 
                           placeholder="Enter the number" required>
                </div>
            </div>

            <button type="submit" class="submit-btn">
                🎮 Create Account
            </button>
        </form>

        <div class="login-link">
            <p>Already have an account? <a href="secure_login.php">Login here</a></p>
        </div>
    </div>

    <script>
        // Simple form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters!');
                return false;
            }
        });
    </script>
</body>
</html>
<?php
