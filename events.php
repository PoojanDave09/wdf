<?php
require_once 'config.php';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            $stmt = $pdo->prepare("INSERT INTO events (event_name, event_type, description, start_date, end_date, location, max_participants, prize_pool, status, difficulty_level, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['event_name'],   
                $_POST['event_type'],
                $_POST['description'],
                $_POST['start_date'],
                $_POST['end_date'],
                $_POST['location'],
                $_POST['max_participants'],
                $_POST['prize_pool'],
                $_POST['status'],
                $_POST['difficulty_level'],
                $_POST['created_by']
            ]);
            $success_message = "Event created successfully!";
            break;

        case 'update':
            $stmt = $pdo->prepare("UPDATE events SET event_name=?, event_type=?, description=?, start_date=?, end_date=?, location=?, max_participants=?, current_participants=?, prize_pool=?, status=?, difficulty_level=?, created_by=? WHERE id=?");
            $stmt->execute([
                $_POST['event_name'],
                $_POST['event_type'],
                $_POST['description'],
                $_POST['start_date'],
                $_POST['end_date'],
                $_POST['location'],
                $_POST['max_participants'],
                $_POST['current_participants'],
                $_POST['prize_pool'],
                $_POST['status'],
                $_POST['difficulty_level'],
                $_POST['created_by'],
                $_POST['event_id']
            ]);
            $success_message = "Event updated successfully!";
            break;

        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
            $stmt->execute([$_POST['event_id']]);
            $success_message = "Event deleted successfully!";
            break;
    }
}

// Fetch all events
$stmt = $pdo->query("SELECT * FROM events ORDER BY start_date DESC");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get event for editing
$edit_event = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_event = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gaming Events Management</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
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
            0% {
                transform: translate(0, 0);
            }

            100% {
                transform: translate(50px, 50px);
            }
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            background: linear-gradient(45deg, #0f3460, #16537e);
            border: 1px solid #00d4ff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 212, 255, 0.1), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                left: -100%;
            }

            100% {
                left: 100%;
            }
        }

        .header h1 {
            font-family: 'Orbitron', monospace;
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(45deg, #00d4ff, #00ff88);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 30px rgba(0, 212, 255, 0.5);
            margin-bottom: 15px;
            letter-spacing: 3px;
            position: relative;
            z-index: 2;
        }

        .header p {
            font-size: 1.3rem;
            color: #a0a0a0;
            position: relative;
            z-index: 2;
        }

        .success-message {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid rgba(0, 255, 136, 0.3);
            color: #00ff88;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .form-section,
        .events-list {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .section-title {
            font-family: 'Orbitron', monospace;
            font-size: 1.5rem;
            color: #00d4ff;
            margin-bottom: 25px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-family: 'Orbitron', monospace;
            color: #00d4ff;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 12px 15px;
            background: rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(0, 212, 255, 0.3);
            border-radius: 8px;
            color: #ffffff;
            font-size: 1rem;
            font-family: 'Rajdhani', sans-serif;
            transition: all 0.3s ease;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
        }

        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-select option {
            background: #1a1a2e;
            color: #ffffff;
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
            text-align: center;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 212, 255, 0.4);
        }

        .btn-danger {
            background: linear-gradient(45deg, #ff6b6b, #ff8e8e);
            color: #fff;
        }

        .btn-danger:hover {
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        .btn-warning {
            background: linear-gradient(45deg, #ffd93d, #ffb74d);
            color: #333;
        }

        .btn-warning:hover {
            box-shadow: 0 5px 15px rgba(255, 217, 61, 0.4);
        }

        .events-grid {
            display: grid;
            gap: 20px;
        }

        .event-card {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 12px;
            padding: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .event-card:hover {
            border-color: rgba(0, 212, 255, 0.6);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .event-name {
            font-family: 'Orbitron', monospace;
            font-size: 1.3rem;
            color: #00ff88;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .event-type {
            font-size: 0.9rem;
            color: #a0a0a0;
            text-transform: uppercase;
        }

        .event-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .event-description {
            color: #ffffff;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .event-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
        }

        .detail-label {
            color: #a0a0a0;
        }

        .detail-value {
            color: #ffffff;
            font-weight: 600;
        }

        .event-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 8px 15px;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 2rem;
            }

            .event-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="background-effects">
        <div class="grid-overlay"></div>
    </div>

    <div class="container">
        <div class="header">
            <h1>GAMING EVENTS</h1>
            <p>Manage your epic gaming events and tournaments</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="success-message">
                ✅ <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <div class="content-grid">
            <!-- Event Form -->
            <div class="form-section">
                <h2 class="section-title"><?php echo $edit_event ? 'Update Event' : 'Create New Event'; ?></h2>

                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $edit_event ? 'update' : 'create'; ?>">
                    <?php if ($edit_event): ?>
                        <input type="hidden" name="event_id" value="<?php echo $edit_event['id']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Event Name</label>
                        <input type="text" name="event_name" class="form-input"
                            value="<?php echo $edit_event['event_name'] ?? ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Event Type</label>
                        <select name="event_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="Tournament" <?php echo ($edit_event['event_type'] ?? '') == 'Tournament' ? 'selected' : ''; ?>>Tournament</option>
                            <option value="Quest" <?php echo ($edit_event['event_type'] ?? '') == 'Quest' ? 'selected' : ''; ?>>Quest</option>
                            <option value="PvP Battle" <?php echo ($edit_event['event_type'] ?? '') == 'PvP Battle' ? 'selected' : ''; ?>>PvP Battle</option>
                            <option value="Guild War" <?php echo ($edit_event['event_type'] ?? '') == 'Guild War' ? 'selected' : ''; ?>>Guild War</option>
                            <option value="Training" <?php echo ($edit_event['event_type'] ?? '') == 'Training' ? 'selected' : ''; ?>>Training</option>
                            <option value="Special Event" <?php echo ($edit_event['event_type'] ?? '') == 'Special Event' ? 'selected' : ''; ?>>Special Event</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-textarea"
                            required><?php echo $edit_event['description'] ?? ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="datetime-local" name="start_date" class="form-input"
                            value="<?php echo isset($edit_event['start_date']) ? date('Y-m-d\TH:i', strtotime($edit_event['start_date'])) : ''; ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="datetime-local" name="end_date" class="form-input"
                            value="<?php echo isset($edit_event['end_date']) ? date('Y-m-d\TH:i', strtotime($edit_event['end_date'])) : ''; ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-input"
                            value="<?php echo $edit_event['location'] ?? ''; ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Max Participants</label>
                        <input type="number" name="max_participants" class="form-input"
                            value="<?php echo $edit_event['max_participants'] ?? '0'; ?>" min="0">
                    </div>

                    <?php if ($edit_event): ?>
                        <div class="form-group">
                            <label class="form-label">Current Participants</label>
                            <input type="number" name="current_participants" class="form-input"
                                value="<?php echo $edit_event['current_participants'] ?? '0'; ?>" min="0">
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Prize Pool ($)</label>
                        <input type="number" name="prize_pool" class="form-input"
                            value="<?php echo $edit_event['prize_pool'] ?? '0'; ?>" min="0" step="0.01">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Upcoming" <?php echo ($edit_event['status'] ?? '') == 'Upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="Active" <?php echo ($edit_event['status'] ?? '') == 'Active' ? 'selected' : ''; ?>>Active</option>
                            <option value="Completed" <?php echo ($edit_event['status'] ?? '') == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="Cancelled" <?php echo ($edit_event['status'] ?? '') == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Difficulty Level</label>
                        <select name="difficulty_level" class="form-select" required>
                            <option value="Beginner" <?php echo ($edit_event['difficulty_level'] ?? '') == 'Beginner' ? 'selected' : ''; ?>>Beginner</option>
                            <option value="Intermediate" <?php echo ($edit_event['difficulty_level'] ?? '') == 'Intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                            <option value="Advanced" <?php echo ($edit_event['difficulty_level'] ?? '') == 'Advanced' ? 'selected' : ''; ?>>Advanced</option>
                            <option value="Expert" <?php echo ($edit_event['difficulty_level'] ?? '') == 'Expert' ? 'selected' : ''; ?>>Expert</option>
                            <option value="Master" <?php echo ($edit_event['difficulty_level'] ?? '') == 'Master' ? 'selected' : ''; ?>>Master</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Created By</label>
                        <input type="text" name="created_by" class="form-input"
                            value="<?php echo $edit_event['created_by'] ?? ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn" style="width: 100%;">
                            <?php echo $edit_event ? '🎯 Update Event' : '⚡ Create Event'; ?>
                        </button>

                        <?php if ($edit_event): ?>
                            <a href="events.php" class="btn btn-warning" style="width: 100%; margin-top: 10px;">Cancel
                                Edit</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Events List -->
            <div class="events-list">
                <h2 class="section-title">All Events (<?php echo count($events); ?>)</h2>

                <div class="events-grid">
                    <?php foreach ($events as $event): ?>
                        <div class="event-card">
                            <div class="event-header">
                                <div>
                                    <div class="event-name"><?php echo htmlspecialchars($event['event_name']); ?></div>
                                    <div class="event-type"><?php echo $event['event_type']; ?></div>
                                </div>
                                <div class="event-badges">
                                    <span class="badge"
                                        style="background: <?php echo getStatusColor($event['status']); ?>; color: <?php echo $event['status'] == 'Completed' ? '#333' : '#fff'; ?>;">
                                        <?php echo $event['status']; ?>
                                    </span>
                                    <span class="badge"
                                        style="background: <?php echo getDifficultyColor($event['difficulty_level']); ?>; color: <?php echo in_array($event['difficulty_level'], ['Intermediate', 'Completed']) ? '#333' : '#fff'; ?>;">
                                        <?php echo $event['difficulty_level']; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="event-description">
                                <?php echo htmlspecialchars(substr($event['description'], 0, 120)) . (strlen($event['description']) > 120 ? '...' : ''); ?>
                            </div>

                            <div class="event-details">
                                <div class="detail-item">
                                    <span class="detail-label">Start:</span>
                                    <span
                                        class="detail-value"><?php echo date('M d, Y H:i', strtotime($event['start_date'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">End:</span>
                                    <span
                                        class="detail-value"><?php echo date('M d, Y H:i', strtotime($event['end_date'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Location:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($event['location']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Participants:</span>
                                    <span
                                        class="detail-value"><?php echo $event['current_participants']; ?>/<?php echo $event['max_participants']; ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Prize Pool:</span>
                                    <span class="detail-value">$<?php echo number_format($event['prize_pool'], 2); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Created By:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($event['created_by']); ?></span>
                                </div>
                            </div>

                            <div class="event-actions">
                                <a href="events.php?edit=<?php echo $event['id']; ?>" class="btn btn-warning btn-sm">
                                    ✏️ Edit
                                </a>

                                <form method="POST" style="display: inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this event?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        🗑️ Delete
                                    </button>
                                </form>

                                <a href="event_details.php?id=<?php echo $event['id']; ?>" class="btn btn-sm">
                                    👁️ View
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add interactive effects
        document.addEventListener('DOMContentLoaded', function () {
            const cards = document.querySelectorAll('.event-card');

            cards.forEach(card => {
                card.addEventListener('mouseenter', function () {
                    this.style.boxShadow = '0 15px 35px rgba(0, 212, 255, 0.2)';
                });

                card.addEventListener('mouseleave', function () {
                    this.style.boxShadow = '';
                });
            });

            // Form validation
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function (e) {
                    const startDate = new Date(document.querySelector('[name="start_date"]').value);
                    const endDate = new Date(document.querySelector('[name="end_date"]').value);

                    if (endDate <= startDate) {
                        e.preventDefault();
                        alert('End date must be after start date!');
                        return false;
                    }
                });
            }
        });
    </script>
</body>

</html>