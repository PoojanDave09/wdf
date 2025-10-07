<?php
// Start session
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gaming_guild";

// First, connect without specifying database to create it
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    // Select the database
    $conn->select_db($dbname);
    
    // Create table if it doesn't exist
    $createTableSQL = "CREATE TABLE IF NOT EXISTS gamers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        gamer_tag VARCHAR(50) UNIQUE NOT NULL,
        real_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        guild VARCHAR(50) NOT NULL,
        specialization VARCHAR(100) NOT NULL,
        level_category VARCHAR(20) NOT NULL,
        player_id INT UNIQUE NOT NULL,
        xp INT DEFAULT 0,
        level INT DEFAULT 1,
        status ENUM('Online', 'Offline', 'In Game') DEFAULT 'Offline',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($createTableSQL)) {
        die("Error creating table: " . $conn->error);
    }
} else {
    die("Error creating database: " . $conn->error);
}

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to generate unique player ID
function generatePlayerId($conn) {
    do {
        $playerId = rand(1000, 9999);
        $query = "SELECT id FROM gamers WHERE player_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $playerId);
        $stmt->execute();
        $result = $stmt->get_result();
    } while ($result->num_rows > 0);
    
    return $playerId;
}

// Initialize variables
$error_message = "";
$success_message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize form data
    $gamer_tag = sanitizeInput($_POST['gamer_tag']);
    $real_name = sanitizeInput($_POST['real_name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $guild = sanitizeInput($_POST['guild']);
    $specialization = sanitizeInput($_POST['specialization']);
    $level = sanitizeInput($_POST['level']);
    
    // Validation
    $errors = [];
    
    // Check if all fields are filled
    if (empty($gamer_tag) || empty($real_name) || empty($email) || empty($password) || empty($guild) || empty($specialization) || empty($level)) {
        $errors[] = "All fields are required!";
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format!";
    }
    
    // Check password length
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long!";
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match!";
    }
    
    // Check if gamer tag already exists
    $stmt = $conn->prepare("SELECT id FROM gamers WHERE gamer_tag = ?");
    $stmt->bind_param("s", $gamer_tag);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Gamer tag already exists! Please choose another one.";
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM gamers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Email already registered! Please use another email.";
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate unique player ID
        $player_id = generatePlayerId($conn);
        
        // Set initial XP and level based on selection
        $initial_xp = 0;
        $initial_level = 1;
        switch($level) {
            case 'Beginner':
                $initial_xp = rand(50, 500);
                $initial_level = rand(1, 10);
                break;
            case 'Intermediate':
                $initial_xp = rand(1000, 2500);
                $initial_level = rand(11, 25);
                break;
            case 'Advanced':
                $initial_xp = rand(2500, 5000);
                $initial_level = rand(26, 40);
                break;
            case 'Expert':
                $initial_xp = rand(5000, 8000);
                $initial_level = rand(41, 50);
                break;
            case 'Master':
                $initial_xp = rand(8000, 12000);
                $initial_level = rand(50, 75);
                break;
        }
        
       
        $stmt = $conn->prepare("INSERT INTO gamers (gamer_tag, real_name, email, password, guild, specialization, level_category, player_id, xp, level) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssiii", $gamer_tag, $real_name, $email, $hashed_password, $guild, $specialization, $level, $player_id, $initial_xp, $initial_level);
        
        if ($stmt->execute()) {
            $success_message = "🎮 Registration successful! 🎮<br><br>Your Player ID is: <strong>#" . $player_id . "</strong><br>Level: " . $initial_level . "<br>XP: " . number_format($initial_xp) . "<br><br>Welcome to the " . $guild . " guild, " . $gamer_tag . "!";
            
            // Optional: Set session variables for auto-login
            $_SESSION['gamer_id'] = $conn->insert_id;
            $_SESSION['gamer_tag'] = $gamer_tag;
            $_SESSION['player_id'] = $player_id;
            
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        $error_message = implode("<br>", $errors);
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Result - Gaming Guild</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Rajdhani', sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .background-effects {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .grid-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(0, 212, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 212, 255, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: gridMove 20s linear infinite;
        }

        @keyframes gridMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        .result-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
            max-width: 600px;
            width: 100%;
            margin: 20px;
            text-align: center;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .success {
            border-color: rgba(0, 255, 136, 0.5);
            box-shadow: 0 15px 40px rgba(0, 255, 136, 0.1);
        }

        .error {
            border-color: rgba(255, 107, 107, 0.5);
            box-shadow: 0 15px 40px rgba(255, 107, 107, 0.1);
        }

        .result-title {
            font-family: 'Orbitron', monospace;
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 25px;
            letter-spacing: 2px;
            position: relative;
            z-index: 2;
        }

        .success .result-title {
            background: linear-gradient(45deg, #00ff88, #00d4ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 20px rgba(0, 255, 136, 0.5);
        }

        .error .result-title {
            color: #ff6b6b;
            text-shadow: 0 0 20px rgba(255, 107, 107, 0.5);
        }

        .result-message {
            font-size: 1.2rem;
            line-height: 1.8;
            margin-bottom: 35px;
            color: #ffffff;
            position: relative;
            z-index: 2;
        }

        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            position: relative;
            z-index: 2;
        }

        .back-btn {
            padding: 15px 30px;
            background: linear-gradient(45deg, #00d4ff, #00ff88);
            border: none;
            border-radius: 12px;
            color: #000;
            font-family: 'Orbitron', monospace;
            font-size: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            position: relative;
            overflow: hidden;
        }

        .back-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 212, 255, 0.4);
        }

        .error .back-btn {
            background: linear-gradient(45deg, #ff6b6b, #ff8e8e);
            color: #fff;
        }

        .error .back-btn:hover {
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.4);
        }

        .celebration {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .confetti {
            position: absolute;
            width: 6px;
            height: 6px;
            background: #00ff88;
            animation: confetti-fall 3s linear infinite;
        }

        .confetti:nth-child(2n) { background: #00d4ff; }
        .confetti:nth-child(3n) { background: #ff6b6b; }
        .confetti:nth-child(4n) { background: #ffd93d; }

        @keyframes confetti-fall {
            0% {
                transform: translateY(-100vh) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(720deg);
                opacity: 0;
            }
        }

        @media (max-width: 768px) {
            .result-container {
                padding: 30px 20px;
                margin: 10px;
            }

            .result-title {
                font-size: 1.8rem;
            }

            .result-message {
                font-size: 1.1rem;
            }

            .button-group {
                flex-direction: column;
                align-items: center;
            }

            .back-btn {
                width: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="background-effects">
        <div class="grid-overlay"></div>
    </div>

    <div class="result-container <?php echo !empty($success_message) ? 'success' : 'error'; ?>">
        <?php if (!empty($success_message)): ?>
            <div class="celebration">
                <?php for($i = 0; $i < 30; $i++): ?>
                    <div class="confetti" style="left: <?php echo rand(0, 100); ?>%; animation-delay: <?php echo rand(0, 3000); ?>ms;"></div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <h1 class="result-title">
            <?php echo !empty($success_message) ? 'QUEST COMPLETE!' : 'MISSION FAILED'; ?>
        </h1>
        
        <div class="result-message">
            <?php 
            if (!empty($success_message)) {
                echo $success_message;
            } elseif (!empty($error_message)) {
                echo "❌ " . $error_message;
            }
            ?>
        </div>
        
        <div class="button-group">
            <?php if (!empty($success_message)): ?>
                <a href="register.html" class="back-btn">Register Another</a>
                <a href="index.html" class="back-btn">Home</a>
            <?php else: ?>
                <a href="register.html" class="back-btn">Try Again</a>
                <a href="index.html" class="back-btn">Home</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
