<?php
require_once 'config-live.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get all conversations for this user
$conv_sql = "SELECT c.*, 
             (SELECT message_text FROM messages WHERE conversation_id = c.conversation_id ORDER BY created_at DESC LIMIT 1) as last_message,
             (SELECT created_at FROM messages WHERE conversation_id = c.conversation_id ORDER BY created_at DESC LIMIT 1) as last_message_time,
             (SELECT COUNT(*) FROM messages WHERE conversation_id = c.conversation_id AND is_read = FALSE AND sender_id != ?) as unread_count,
             (SELECT u.username FROM conversation_participants cp 
              JOIN users u ON cp.user_id = u.user_id 
              WHERE cp.conversation_id = c.conversation_id AND cp.user_id != ? LIMIT 1) as other_user
             FROM conversations c
             JOIN conversation_participants cp ON c.conversation_id = cp.conversation_id
             WHERE cp.user_id = ?
             ORDER BY last_message_time DESC";

$stmt = $conn->prepare($conv_sql);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$conversations = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Messages - UniScope</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .messages-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .messages-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .conversations-list {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .conversation-item {
            display: flex;
            padding: 20px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background 0.3s;
            position: relative;
        }
        
        .conversation-item:hover {
            background: #f8f9fa;
        }
        
        .conversation-item.unread {
            background: #e8f0fe;
        }
        
        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #1a73e8;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
            margin-right: 15px;
        }
        
        .conversation-info {
            flex: 1;
        }
        
        .conversation-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .other-user {
            font-weight: bold;
            font-size: 16px;
        }
        
        .time {
            color: #666;
            font-size: 12px;
        }
        
        .last-message {
            color: #555;
            font-size: 14px;
            margin-bottom: 5px;
            max-width: 80%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .unread-badge {
            background: #1a73e8;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #1a73e8;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1><a href="index.php">🎓 UniScope</a></h1>
        <div class="user-menu">
            <span class="username">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>
    
    <div class="messages-container">
        <a href="index.php" class="back-link">← Back to Home</a>
        
        <div class="messages-header">
            <h1>Messages</h1>
        </div>
        
        <?php if($conversations->num_rows > 0): ?>
            <div class="conversations-list">
                <?php while($conv = $conversations->fetch_assoc()): ?>
                    <div class="conversation-item <?php echo $conv['unread_count'] > 0 ? 'unread' : ''; ?>" 
                         onclick="window.location.href='conversation.php?id=<?php echo $conv['conversation_id']; ?>'">
                        <div class="avatar">
                            <?php echo strtoupper(substr($conv['other_user'], 0, 1)); ?>
                        </div>
                        <div class="conversation-info">
                            <div class="conversation-header">
                                <span class="other-user"><?php echo htmlspecialchars($conv['other_user']); ?></span>
                                <span class="time">
                                    <?php 
                                    $time = strtotime($conv['last_message_time']);
                                    $now = time();
                                    $diff = $now - $time;
                                    
                                    if ($diff < 60) {
                                        echo 'Just now';
                                    } elseif ($diff < 3600) {
                                        echo floor($diff/60) . ' minutes ago';
                                    } elseif ($diff < 86400) {
                                        echo floor($diff/3600) . ' hours ago';
                                    } else {
                                        echo date('M d', $time);
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="last-message">
                                <?php echo htmlspecialchars($conv['last_message']); ?>
                            </div>
                        </div>
                        <?php if($conv['unread_count'] > 0): ?>
                            <span class="unread-badge"><?php echo $conv['unread_count']; ?></span>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div style="font-size: 64px; margin-bottom: 20px;">💬</div>
                <h2>No messages yet</h2>
                <p>When you ask questions on reviews, your conversations will appear here.</p>
                <a href="index.php" class="btn" style="display: inline-block; margin-top: 20px;">Browse Colleges</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>