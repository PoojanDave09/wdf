<?php
require_once 'config.php';

$event_id = $_GET['id'] ?? 0;

if (!$event_id) {
    header('Location: events.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    header('Location: events.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['event_name']); ?> - Event Details</title>
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

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #00d4ff;
            text-decoration: none;
            font-family: 'Orbitron', monospace;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #00ff88;
            text-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
        }

        .event-detail-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
            position: relative;
            overflow: hidden;
        }

        .event-detail-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 212, 255, 0.05), transparent);
            animation: shimmer 4s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .event-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 2;
        }

        .event-title {
            font-family: 'Orbitron', monospace;
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, #00d4ff, #00ff88);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 20px rgba(0, 212, 255, 0.5);
            margin-bottom: 15px;
            letter-spacing: 2px;
        }

        .event-type {
            font-size: 1.2rem;
            color: #a0a0a0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .event-badges {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .badge {
            padding: 8px 20px;
            border-radius: 25px;
            font-family: 'Orbitron', monospace;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .event-content {
            position: relative;
            z-index: 2;
        }

        .event-description {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            font-size: 1.1rem;
            line-height: 1.6;
            border: 1px solid rgba(0, 212, 255, 0.2);
        }

        .event-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .detail-section {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid rgba(0, 212, 255, 0.2);
        }

        .detail-section h3 {
            font-family: 'Orbitron', monospace;
            color: #00d4ff;
            font-size: 1.1rem;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #a0a0a0;
            font-weight: 500;
        }

        .detail-value {
            color: #ffffff;
            font-weight: 600;
        }

        .progress-bar {
            margin-top: 10px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .progress-container {
            background: rgba(0, 0, 0, 0.5);
            border-radius: 10px;
            padding: 2px;
            border: 1px solid rgba(0, 212, 255, 0.3);
        }

        .progress-fill {
            height: 8px;
            background: linear-gradient(90deg, #00d4ff, #00ff88);
            border-radius: 6px;
            transition: width 1s ease;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 25px;
            background: linear-gradient(45deg, #00d4ff, #00ff88);
            border: none;
            border-radius: 8px;
            color: #000;
            font-family: 'Orbitron', monospace;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 212, 255, 0.4);
        }

        .btn-warning {
            background: linear-gradient(45deg, #ffd93d, #ffb74d);
            color: #333;
        }

        .btn-warning:hover {
            box-shadow: 0 5px 15px rgba(255, 217, 61, 0.4);
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .event-title {
                font-size: 2rem;
            }
            
            .event-details-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="background-effects">
        <div class="grid-overlay"></div>
    </div>

    <div class="container">
        <a href="events.php" class="back-link">← Back to Events</a>

        <div class="event-detail-card">
            <div class="event-header">
                <h1 class="event-title"><?php echo htmlspecialchars($event['event_name']); ?></h1>
                <div class="event-type"><?php echo $event['event_type']; ?></div>
                
                <div class="event-badges">
                    <span class="badge" style="background: <?php echo getStatusColor($event['status']); ?>; color: <?php echo $event['status'] == 'Completed' ? '#333' : '#fff'; ?>;">
                        <?php echo $event['status']; ?>
                    </span>
                    <span class="badge" style="background: <?php echo getDifficultyColor($event['difficulty_level']); ?>; color: <?php echo in_array($event['difficulty_level'], ['Intermediate', 'Completed']) ? '#333' : '#fff'; ?>;">
                        <?php echo $event['difficulty_level']; ?>
                    </span>
                </div>
            </div>

            <div class="event-content">
                <div class="event-description">
                    <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                </div>

                <div class="event-details-grid">
                    <div class="detail-section">
                        <h3>📅 Schedule</h3>
                        <div class="detail-row">
                            <span class="detail-label">Start Date</span>
                            <span class="detail-value"><?php echo date('F j, Y', strtotime($event['start_date'])); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Start Time</span>
                            <span class="detail-value"><?php echo date('g:i A', strtotime($event['start_date'])); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">End Date</span>
                            <span class="detail-value"><?php echo date('F j, Y', strtotime($event['end_date'])); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">End Time</span>
                            <span class="detail-value"><?php echo date('g:i A', strtotime($event['end_date'])); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Duration</span>
                            <span class="detail-value">
                                <?php 
                                $start = new DateTime($event['start_date']);
                                $end = new DateTime($event['end_date']);
                                $interval = $start->diff($end);
                                if ($interval->days > 0) {
                                    echo $interval->days . ' day' . ($interval->days > 1 ? 's' : '') . ' ';
                                }
                                echo $interval->h . 'h ' . $interval->i . 'm';
                                ?>
                            </span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3>🎯 Event Details</h3>
                        <div class="detail-row">
                            <span class="detail-label">Location</span>
                            <span class="detail-value"><?php echo htmlspecialchars($event['location']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Prize Pool</span>
                            <span class="detail-value">$<?php echo number_format($event['prize_pool'], 2); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Max Participants</span>
                            <span class="detail-value"><?php echo $event['max_participants']; ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Current Participants</span>
                            <span class="detail-value"><?php echo $event['current_participants']; ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Available Spots</span>
                            <span class="detail-value"><?php echo max(0, $event['max_participants'] - $event['current_participants']); ?></span>
                        </div>
                        
                        <?php if ($event['max_participants'] > 0): ?>
                            <div class="progress-bar">
                                <div class="progress-label">
                                    <span>Registration Progress</span>
                                    <span><?php echo round(($event['current_participants'] / $event['max_participants']) * 100); ?>%</span>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-fill" style="width: <?php echo min(100, ($event['current_participants'] / $event['max_participants']) * 100); ?>%;"></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="detail-section">
                        <h3>ℹ️ Meta Information</h3>
                        <div class="detail-row">
                            <span class="detail-label">Created By</span>
                            <span class="detail-value"><?php echo htmlspecialchars($event['created_by']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Created At</span>
                            <span class="detail-value"><?php echo date('F j, Y g:i A', strtotime($event['created_at'])); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Last Updated</span>
                            <span class="detail-value"><?php echo date('F j, Y g:i A', strtotime($event['updated_at'])); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Event ID</span>
                            <span class="detail-value">#<?php echo $event['id']; ?></span>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="events.php?edit=<?php echo $event['id']; ?>" class="btn btn-warning">
                        ✏️ Edit Event
                    </a>
                    <a href="events.php" class="btn">
                        📋 Back to Events
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animate progress bar
            const progressBar = document.querySelector('.progress-fill');
            if (progressBar) {
                const targetWidth = progressBar.style.width;
                progressBar.style.width = '0%';
                setTimeout(() => {
                    progressBar.style.width = targetWidth;
                }, 500);
            }
        });
    </script>
</body>
</html>
