<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];
$review_id = $_POST['review_id'];
$review_type = $_POST['review_type'];
$message_text = trim($_POST['message']);

// Validate inputs
if (empty($message_text)) {
    echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
    exit();
}

if ($sender_id == $receiver_id) {
    echo json_encode(['success' => false, 'error' => 'Cannot message yourself']);
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Check if conversation already exists between these users
    $check_sql = "SELECT c.conversation_id 
                 FROM conversations c
                 JOIN conversation_participants cp1 ON c.conversation_id = cp1.conversation_id
                 JOIN conversation_participants cp2 ON c.conversation_id = cp2.conversation_id
                 WHERE cp1.user_id = ? AND cp2.user_id = ?
                 GROUP BY c.conversation_id
                 HAVING COUNT(*) = 2";
    
    $check = $conn->prepare($check_sql);
    $check->bind_param("ii", $sender_id, $receiver_id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        // Use existing conversation
        $row = $result->fetch_assoc();
        $conversation_id = $row['conversation_id'];
    } else {
        // Create new conversation
        $subject = "Question about your review";
        $conv_sql = "INSERT INTO conversations (subject) VALUES (?)";
        $conv_stmt = $conn->prepare($conv_sql);
        $conv_stmt->bind_param("s", $subject);
        $conv_stmt->execute();
        $conversation_id = $conn->insert_id;
        
        // Add participants
        $part_sql = "INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?), (?, ?)";
        $part_stmt = $conn->prepare($part_sql);
        $part_stmt->bind_param("iiii", $conversation_id, $sender_id, $conversation_id, $receiver_id);
        $part_stmt->execute();
    }
    
    // Save message
    $msg_sql = "INSERT INTO messages (conversation_id, sender_id, message_text) VALUES (?, ?, ?)";
    $msg_stmt = $conn->prepare($msg_sql);
    $msg_stmt->bind_param("iis", $conversation_id, $sender_id, $message_text);
    $msg_stmt->execute();
    
    // Link to review inquiry
    $inq_sql = "INSERT INTO review_inquiries (review_id, review_type, conversation_id) VALUES (?, ?, ?)";
    $inq_stmt = $conn->prepare($inq_sql);
    $inq_stmt->bind_param("isi", $review_id, $review_type, $conversation_id);
    $inq_stmt->execute();
    
    $conn->commit();
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>