<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'gamerzone_secure';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Create admin if doesn't exist
try {
    $admin_check = $pdo->query("SELECT COUNT(*) FROM users WHERE username = 'admin'")->fetchColumn();
    if ($admin_check == 0) {
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, gamer_tag, full_name, password_hash) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@gamerzone.com', 'AdminMaster', 'Administrator', $admin_password]);
    }
} catch (PDOException $e) {
    // Table might not exist, ignore for now
}

// Handle admin login
if (!isset($_SESSION['admin_logged_in'])) {
    if (isset($_POST['admin_login'])) {
        $input_username = $_POST['username'] ?? '';
        $input_password = $_POST['password'] ?? '';
        
        if ($input_username === 'admin' && $input_password === 'admin123') {
            $_SESSION['admin_logged_in'] = true;
            header('Location: admin_dashboard.php'); // Redirect after login
            exit;
        } else {
            $login_error = "❌ Invalid admin credentials!";
        }
    }
    
    // Show login form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Login</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { 
                font-family: Arial, sans-serif; 
                background: linear-gradient(135deg, #0a0a0a, #1a1a2e); 
                color: #fff; 
                min-height: 100vh; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
            }
            .login-container {
                background: rgba(255,255,255,0.1);
                padding: 40px;
                border-radius: 15px;
                border: 2px solid #ff6b6b;
                box-shadow: 0 10px 30px rgba(0,0,0,0.7);
                min-width: 350px;
            }
            .login-container h1 {
                color: #ff6b6b;
                text-align: center;
                margin-bottom: 30px;
                font-size: 1.8rem;
            }
            .form-input {
                width: 100%;
                padding: 15px;
                background: rgba(0,0,0,0.4);
                border: 2px solid rgba(255,107,107,0.3);
                border-radius: 8px;
                color: #fff;
                margin-bottom: 20px;
                font-size: 16px;
            }
            .form-input:focus {
                outline: none;
                border-color: #ff6b6b;
                box-shadow: 0 0 10px rgba(255,107,107,0.5);
            }
            .submit-btn {
                width: 100%;
                padding: 15px;
                background: linear-gradient(45deg, #ff6b6b, #ff8e8e);
                border: none;
                border-radius: 8px;
                color: #fff;
                font-weight: bold;
                cursor: pointer;
                font-size: 16px;
                text-transform: uppercase;
            }
            .submit-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(255,107,107,0.4);
            }
            .error {
                color: #ff6b6b;
                text-align: center;
                margin-bottom: 20px;
                background: rgba(255,107,107,0.1);
                padding: 10px;
                border-radius: 5px;
                border: 1px solid rgba(255,107,107,0.3);
            }
            .credentials {
                text-align: center;
                margin-top: 20px;
                color: #aaa;
                font-size: 14px;
                background: rgba(0,0,0,0.3);
                padding: 15px;
                border-radius: 8px;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h1>🔒 ADMIN LOGIN</h1>
            
            <?php if (isset($login_error)): ?>
                <div class="error"><?= $login_error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="text" name="username" class="form-input" 
                       placeholder="Admin Username" required value="admin">
                <input type="password" name="password" class="form-input" 
                       placeholder="Admin Password" required>
                <button type="submit" name="admin_login" class="submit-btn">
                    🎮 Login as Admin
                </button>
            </form>
            
            <div class="credentials">
                <strong>Default Credentials:</strong><br>
                Username: <code>admin</code><br>
                Password: <code>admin123</code>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Admin is logged in - show dashboard
$message = '';

// Handle user actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $user_id = $_POST['user_id'] ?? 0;
    
    try {
        switch ($action) {
            case 'add_user':
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $gamer_tag = trim($_POST['gamer_tag']);
                $full_name = trim($_POST['full_name']);
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users (username, email, gamer_tag, full_name, password_hash) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $gamer_tag, $full_name, $password]);
                $message = "✅ User '$username' added successfully!";
                break;
                
            case 'delete_user':
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND username != 'admin'");
                $stmt->execute([$user_id]);
                $message = "✅ User deleted successfully!";
                break;
                
            case 'toggle_status':
                $stmt = $pdo->prepare("UPDATE users SET account_status = CASE WHEN account_status = 'Active' THEN 'Suspended' ELSE 'Active' END WHERE id = ?");
                $stmt->execute([$user_id]);
                $message = "✅ User status updated!";
                break;
        }
    } catch (PDOException $e) {
        $message = "❌ Error: " . $e->getMessage();
    }
}

// Get all users
try {
    $users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
} catch (PDOException $e) {
    $users = [];
    $message = "❌ Error loading users: " . $e->getMessage();
}

$total_users = count($users);
$active_users = count(array_filter($users, fn($u) => ($u['account_status'] ?? 'Active') === 'Active'));

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GamerZone Admin Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #0a0a0a, #1a1a2e);
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container { max-width: 1200px; margin: 0 auto; }
        
        .header {
            background: rgba(255,255,255,0.1);
            border: 2px solid #ff6b6b;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 { color: #ff6b6b; font-size: 2rem; }
        
        .logout-btn {
            padding: 10px 20px;
            background: linear-gradient(45deg, #ff6b6b, #ff8e8e);
            border: none;
            border-radius: 8px;
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,107,107,0.3);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #ff6b6b;
            margin-bottom: 10px;
        }
        
        .stat-label { color: #aaa; font-size: 1.1rem; }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        
        .message.success {
            background: rgba(0,255,136,0.1);
            border: 1px solid #00ff88;
            color: #00ff88;
        }
        
        .message.error {
            background: rgba(255,107,107,0.1);
            border: 1px solid #ff6b6b;
            color: #ff6b6b;
        }
        
        .content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }
        
        .form-section, .users-section {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,107,107,0.3);
            border-radius: 15px;
            padding: 25px;
        }
        
        .section-title {
            color: #ff6b6b;
            margin-bottom: 20px;
            font-size: 1.3rem;
        }
        
        .form-group { margin-bottom: 15px; }
        
        .form-label {
            display: block;
            color: #ff6b6b;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        
        .form-input {
            width: 100%;
            padding: 12px;
            background: rgba(0,0,0,0.3);
            border: 2px solid rgba(255,107,107,0.3);
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #ff6b6b;
        }
        
        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(45deg, #ff6b6b, #ff8e8e);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            font-size: 1rem;
            text-transform: uppercase;
        }
        
        .submit-btn:hover { transform: translateY(-2px); }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .users-table th, .users-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .users-table th {
            background: rgba(255,107,107,0.2);
            color: #ff6b6b;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        
        .users-table tr:hover { background: rgba(255,255,255,0.05); }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
            margin: 2px;
            font-weight: bold;
        }
        
        .btn-danger { background: #ff6b6b; color: #fff; }
        .btn-warning { background: #ffd93d; color: #333; }
        
        .status-active { color: #00ff88; }
        .status-suspended { color: #ff6b6b; }
        
        @media (max-width: 768px) {
            .header { flex-direction: column; gap: 15px; }
            .content { grid-template-columns: 1fr; }
            .stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎮 GAMERZONE ADMIN DASHBOARD</h1>
            <a href="?logout=1" class="logout-btn">🚪 Logout</a>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= $total_users ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $active_users ?></div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $total_users - $active_users ?></div>
                <div class="stat-label">Suspended</div>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <div class="content">
            <!-- Add User Form -->
            <div class="form-section">
                <h2 class="section-title">➕ ADD NEW USER</h2>
                
                <form method="POST">
                    <input type="hidden" name="action" value="add_user">
                    
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-input" required 
                               placeholder="Enter username">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" required 
                               placeholder="Enter email">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Gamer Tag</label>
                        <input type="text" name="gamer_tag" class="form-input" required 
                               placeholder="Enter gamer tag">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-input" required 
                               placeholder="Enter full name">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input" required 
                               placeholder="Enter password">
                    </div>
                    
                    <button type="submit" class="submit-btn">🎮 Add User</button>
                </form>
            </div>
            
            <!-- Users List -->
            <div class="users-section">
                <h2 class="section-title">👥 ALL USERS (<?= $total_users ?>)</h2>
                
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Gamer Tag</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['gamer_tag']) ?></td>
                                <td class="status-<?= strtolower($user['account_status'] ?? 'active') ?>">
                                    <?= $user['account_status'] ?? 'Active' ?>
                                </td>
                                <td>
                                    <?php if ($user['username'] !== 'admin'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="action-btn btn-warning" 
                                                    title="Toggle status">
                                                <?= ($user['account_status'] ?? 'Active') === 'Active' ? '⏸️' : '▶️' ?>
                                            </button>
                                        </form>
                                        
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="action-btn btn-danger" 
                                                    onclick="return confirm('Delete user permanently?')"
                                                    title="Delete user">🗑️</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: #00ff88;">👑 ADMIN</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
