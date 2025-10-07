<?php
require_once 'config.php';

// Handle operations
if ($_POST['action'] ?? '' == 'update') {
    $stmt = $pdo->prepare("UPDATE events SET event_name=?, event_type=?, description=?, start_date=?, end_date=?, location=?, max_participants=?, current_participants=?, prize_pool=?, status=?, difficulty_level=?, created_by=? WHERE id=?");
    $stmt->execute([$_POST['event_name'], $_POST['event_type'], $_POST['description'], $_POST['start_date'], $_POST['end_date'], $_POST['location'], $_POST['max_participants'], $_POST['current_participants'], $_POST['prize_pool'], $_POST['status'], $_POST['difficulty_level'], $_POST['created_by'], $_POST['event_id']]);
    $success = "Event updated successfully!";
}

if ($_POST['action'] ?? '' == 'delete') {
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$_POST['event_id']]);
    $success = "Event deleted!";
}

// Get events
$events = $pdo->query("SELECT * FROM events ORDER BY start_date DESC")->fetchAll();

// Get event for editing
$edit_event = null;
if ($_GET['edit'] ?? '') {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_event = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Gaming Events</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700&family=Rajdhani:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Rajdhani', sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 100%);
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            background: rgba(255,255,255,0.05);
            border: 1px solid #00d4ff;
            border-radius: 15px;
            padding: 20px;
        }
        
        .header h1 {
            font-family: 'Orbitron', monospace;
            font-size: 2.5rem;
            background: linear-gradient(45deg, #00d4ff, #00ff88);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
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
        
        .container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .form-section, .events-section {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(0,212,255,0.3);
            border-radius: 15px;
            padding: 25px;
        }
        
        .section-title {
            font-family: 'Orbitron', monospace;
            color: #00d4ff;
            font-size: 1.3rem;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-label {
            display: block;
            color: #00d4ff;
            font-weight: 600;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 10px 12px;
            background: rgba(0,0,0,0.3);
            border: 2px solid rgba(0,212,255,0.3);
            border-radius: 6px;
            color: #fff;
            font-family: 'Rajdhani', sans-serif;
        }
        
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 10px rgba(0,212,255,0.3);
        }
        
        .form-textarea { min-height: 80px; resize: vertical; }
        .form-select option { background: #1a1a2e; }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-family: 'Orbitron', monospace;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #00d4ff, #00ff88);
            color: #000;
            width: 100%;
        }
        
        .btn-edit {
            background: linear-gradient(45deg, #ffd93d, #ffb74d);
            color: #333;
            margin-right: 10px;
        }
        
        .btn-delete {
            background: linear-gradient(45deg, #ff6b6b, #ff8e8e);
            color: #fff;
        }
        
        .btn:hover { transform: translateY(-2px); }
        
        .event-card {
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(0,212,255,0.3);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .event-name {
            font-family: 'Orbitron', monospace;
            color: #00ff88;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }
        
        .event-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 10px 0;
            font-size: 0.9rem;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-right: 5px;
        }
        
        .status-upcoming { background: #00d4ff; color: #000; }
        .status-active { background: #00ff88; color: #000; }
        .status-completed { background: #ffd93d; color: #333; }
        .status-cancelled { background: #ff6b6b; color: #fff; }
        
        @media (max-width: 768px) {
            .container { grid-template-columns: 1fr; }
            .header h1 { font-size: 2rem; }
            .event-info { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>EDIT GAMING EVENTS</h1>
        <p>Manage your gaming events</p>
    </div>

    <?php if (isset($success)): ?>
        <div class="success">✅ <?php echo $success; ?></div>
    <?php endif; ?>

    <div class="container">
        <!-- Edit Form -->
        <div class="form-section">
            <h2 class="section-title"><?php echo $edit_event ? 'Update Event' : 'Create Event'; ?></h2>
            
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $edit_event ? 'update' : 'create'; ?>">
                <?php if ($edit_event): ?>
                    <input type="hidden" name="event_id" value="<?php echo $edit_event['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Event Name</label>
                    <input type="text" name="event_name" class="form-input" value="<?php echo $edit_event['event_name'] ?? ''; ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Event Type</label>
                    <select name="event_type" class="form-select" required>
                        <option value="Tournament" <?php echo ($edit_event['event_type'] ?? '') == 'Tournament' ? 'selected' : ''; ?>>Tournament</option>
                        <option value="Quest" <?php echo ($edit_event['event_type'] ?? '') == 'Quest' ? 'selected' : ''; ?>>Quest</option>
                        <option value="PvP Battle" <?php echo ($edit_event['event_type'] ?? '') == 'PvP Battle' ? 'selected' : ''; ?>>PvP Battle</option>
                        <option value="Guild War" <?php echo ($edit_event['event_type'] ?? '') == 'Guild War' ? 'selected' : ''; ?>>Guild War</option>
                        <option value="Training" <?php echo ($edit_event['event_type'] ?? '') == 'Training' ? 'selected' : ''; ?>>Training</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" required><?php echo $edit_event['description'] ?? ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input type="datetime-local" name="start_date" class="form-input" value="<?php echo isset($edit_event['start_date']) ? date('Y-m-d\TH:i', strtotime($edit_event['start_date'])) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">End Date</label>
                    <input type="datetime-local" name="end_date" class="form-input" value="<?php echo isset($edit_event['end_date']) ? date('Y-m-d\TH:i', strtotime($edit_event['end_date'])) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-input" value="<?php echo $edit_event['location'] ?? ''; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Max Participants</label>
                    <input type="number" name="max_participants" class="form-input" value="<?php echo $edit_event['max_participants'] ?? '0'; ?>">
                </div>

                <?php if ($edit_event): ?>
                <div class="form-group">
                    <label class="form-label">Current Participants</label>
                    <input type="number" name="current_participants" class="form-input" value="<?php echo $edit_event['current_participants'] ?? '0'; ?>">
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Prize Pool ($)</label>
                    <input type="number" name="prize_pool" class="form-input" value="<?php echo $edit_event['prize_pool'] ?? '0'; ?>" step="0.01">
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="Upcoming" <?php echo ($edit_event['status'] ?? '') == 'Upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                        <option value="Active" <?php echo ($edit_event['status'] ?? '') == 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Completed" <?php echo ($edit_event['status'] ?? '') == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="Cancelled" <?php echo ($edit_event['status'] ?? '') == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Difficulty</label>
                    <select name="difficulty_level" class="form-select">
                        <option value="Beginner" <?php echo ($edit_event['difficulty_level'] ?? '') == 'Beginner' ? 'selected' : ''; ?>>Beginner</option>
                        <option value="Intermediate" <?php echo ($edit_event['difficulty_level'] ?? '') == 'Intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                        <option value="Advanced" <?php echo ($edit_event['difficulty_level'] ?? '') == 'Advanced' ? 'selected' : ''; ?>>Advanced</option>
                        <option value="Expert" <?php echo ($edit_event['difficulty_level'] ?? '') == 'Expert' ? 'selected' : ''; ?>>Expert</option>
                        <option value="Master" <?php echo ($edit_event['difficulty_level'] ?? '') == 'Master' ? 'selected' : ''; ?>>Master</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Created By</label>
                    <input type="text" name="created_by" class="form-input" value="<?php echo $edit_event['created_by'] ?? ''; ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    <?php echo $edit_event ? '💾 Update' : '⚡ Create'; ?>
                </button>
                
                <?php if ($edit_event): ?>
                    <a href="edit_events.php" class="btn btn-edit">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Events List -->
        <div class="events-section">
            <h2 class="section-title">All Events (<?php echo count($events); ?>)</h2>
            
            <?php foreach ($events as $event): ?>
                <div class="event-card">
                    <div class="event-name"><?php echo htmlspecialchars($event['event_name']); ?></div>
                    
                    <div class="event-info">
                        <div><strong>Type:</strong> <?php echo $event['event_type']; ?></div>
                        <div><strong>Start:</strong> <?php echo date('M d, Y H:i', strtotime($event['start_date'])); ?></div>
                        <div><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></div>
                        <div><strong>Participants:</strong> <?php echo $event['current_participants']; ?>/<?php echo $event['max_participants']; ?></div>
                        <div><strong>Prize:</strong> $<?php echo number_format($event['prize_pool'], 2); ?></div>
                        <div><strong>Creator:</strong> <?php echo htmlspecialchars($event['created_by']); ?></div>
                    </div>
                    
                    <div style="margin-top: 10px;">
                        <span class="badge status-<?php echo strtolower($event['status']); ?>">
                            <?php echo $event['status']; ?>
                        </span>
                        <span class="badge" style="background: #666;">
                            <?php echo $event['difficulty_level']; ?>
                        </span>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <a href="?edit=<?php echo $event['id']; ?>" class="btn btn-edit">✏️ Edit</a>
                        
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this event?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                            <button type="submit" class="btn btn-delete">🗑️ Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
