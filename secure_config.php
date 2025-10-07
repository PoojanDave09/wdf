<?php
// Secure Database Configuration for GamerZone
class SecureDB {
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "gamerzone_secure";
    private $pdo;
    
    public function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host={$this->servername};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            $this->createDatabase();
        }
    }
    
    private function createDatabase() {
        try {
            $pdo = new PDO("mysql:host={$this->servername}", $this->username, $this->password);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS {$this->dbname}");
            $pdo->exec("USE {$this->dbname}");
            
            // Create users table
            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                gamer_tag VARCHAR(50) UNIQUE NOT NULL,
                full_name VARCHAR(100) NOT NULL,
                account_status ENUM('Active', 'Suspended', 'Pending') DEFAULT 'Active',
                failed_login_attempts INT DEFAULT 0,
                account_locked_until DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_ip VARCHAR(45)
            )");
            
            $pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                action VARCHAR(100) NOT NULL,
                ip_address VARCHAR(45),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            $this->pdo = $pdo;
        } catch(PDOException $e) {
            die("Database setup failed: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function logAudit($userId, $action, $ip) {
        $stmt = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, ip_address) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $action, $ip]);
    }
}

// Security utilities
class SecurityUtils {
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public static function getClientIP() {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    public static function sanitizeInput($input) {
        return htmlspecialchars(trim(stripslashes($input)), ENT_QUOTES, 'UTF-8');
    }
    
    public static function validatePassword($password) {
        $errors = [];
        if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters";
        if (!preg_match('/[A-Z]/', $password)) $errors[] = "Must contain uppercase letter";
        if (!preg_match('/[a-z]/', $password)) $errors[] = "Must contain lowercase letter";
        if (!preg_match('/[0-9]/', $password)) $errors[] = "Must contain number";
        if (!preg_match('/[^A-Za-z0-9]/', $password)) $errors[] = "Must contain special character";
        return $errors;
    }
}

$secureDB = new SecureDB();
$pdo = $secureDB->getConnection();
?>
