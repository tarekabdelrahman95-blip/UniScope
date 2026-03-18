<?php
require_once 'config-live.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: messages.php");
    exit();
}

$conversation_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify user is part of this conversation
$check_sql = "SELECT * FROM conversation_participants WHERE conversation_id = ? AND user_id = ?";
$check = $conn->prepare($check_sql);
$check->bind_param("ii", $conversation_id, $user_id);
$check->execute();
if ($check->get_result()->num_rows == 0) {
    header("Location: messages.php");
    exit();
}

// Get conversation details and other participant
$conv_sql = "SELECT c.*, 
             (SELECT u.username FROM conversation_participants cp 
              JOIN users u ON cp.user_id = u.user_id 
              WHERE cp.conversation_id = c.conversation_id AND cp.user_id != ?) as other_user,
             (SELECT u.user_id FROM conversation_participants cp 
              JOIN users u ON cp.user_id = u.user_id 
              WHERE cp.conversation_id = c.conversation_id AND cp.user_id != ?) as other_user_id
             FROM conversations c
             WHERE c.conversation_id = ?";
$conv_stmt = $conn->prepare($conv_sql);
$conv_stmt->bind_param("iii", $user_id, $user_id, $conversation_id);
$conv_stmt->execute();
$conv = $conv_stmt->get_result()->fetch_assoc();

// Get all messages
$msg_sql = "SELECT m.*, u.username 
            FROM messages m
            JOIN users u ON m.sender_id = u.user_id
            WHERE m.conversation_id = ?
            ORDER BY m.created_at ASC";
$msg_stmt = $conn->prepare($msg_sql);
$msg_stmt->bind_param("i", $conversation_id);
$msg_stmt->execute();
$messages = $msg_stmt->get_result();

// Mark messages as read
$update_sql = "UPDATE messages SET is_read = TRUE, read_at = NOW() 
               WHERE conversation_id = ? AND sender_id != ? AND is_read = FALSE";
$update = $conn->prepare($update_sql);
$update->bind_param("ii", $conversation_id, $user_id);
$update->execute();

// Handle new message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $new_message = trim($_POST['message']);
    
    if (!empty($new_message)) {
        $insert_sql = "INSERT INTO messages (conversation_id, sender_id, message_text) VALUES (?, ?, ?)";
        $insert = $conn->prepare($insert_sql);
        $insert->bind_param("iis", $conversation_id, $user_id, $new_message);
        $insert->execute();
        
        header("Location: conversation.php?id=" . $conversation_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Conversation with <?php echo htmlspecialchars($conv['other_user']); ?> - UniScope</title>
    
   <style>
        .chat-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .chat-header {
            background: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-bottom: 2px solid #1a73e8;
        }
        
        .chat-header h2 {
            color: #333;
        }
        
        .chat-header p {
            color: #666;
        }
        
        .messages-area {
            background: #f8f9fa;
            padding: 20px;
            min-height: 400px;
            max-height: 500px;
            overflow-y: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .message {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .message.sent {
            align-items: flex-end;
        }
        
        .message.received {
            align-items: flex-start;
        }
        
        .message-bubble {
            max-width: 70%;
            padding: 12px 18px;
            border-radius: 18px;
            position: relative;
        }
        
        .message.sent .message-bubble {
            background: #1a73e8;
            color: white;
            border-bottom-right-radius: 4px;
        }
        
        .message.received .message-bubble {
            background: white;
            color: #333;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .message-info {
            font-size: 11px;
            color: #666;
            margin-top: 4px;
            padding: 0 5px;
        }
        
        .message.sent .message-info {
            text-align: right;
        }
        
        .chat-footer {
            background: white;
            padding: 20px;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .message-form {
            display: flex;
            gap: 10px;
        }
        
        .message-form input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 14px;
        }
        
        .message-form input:focus {
            outline: none;
            border-color: #1a73e8;
        }
        
        .message-form button {
            padding: 12px 25px;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
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
    
    <div class="chat-container">
        <a href="messages.php" class="back-link">← Back to Messages</a>
        
        <div class="chat-header">
            <h2><?php echo htmlspecialchars($conv['other_user']); ?></h2>
            <p>Conversation started <?php echo date('M d, Y', strtotime($conv['created_at'])); ?></p>
        </div>
        
        <div class="messages-area" id="messagesArea">
            <?php if($messages->num_rows > 0): ?>
                <?php while($msg = $messages->fetch_assoc()): ?>
                    <div class="message <?php echo $msg['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                        <div class="message-bubble">
                            <?php echo htmlspecialchars($msg['message_text']); ?>
                        </div>
                        <div class="message-info">
                            <?php echo date('M d, H:i', strtotime($msg['created_at'])); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; color: #666; padding: 50px;">
                    No messages yet. Start the conversation!
                </div>
            <?php endif; ?>
        </div>
        
        <div class="chat-footer">
            <form method="POST" class="message-form">
                <input type="text" name="message" placeholder="Type your message..." required autocomplete="off">
                <button type="submit">Send</button>
            </form>
        </div>
    </div>
    
    <script>
        // Auto-scroll to bottom
        const messagesArea = document.getElementById('messagesArea');
        messagesArea.scrollTop = messagesArea.scrollHeight;
    </script>
</body>
</html>