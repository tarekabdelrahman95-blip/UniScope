<?php
require_once 'config-live.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['user_id'];
$review_id = $_POST['review_id'];
$review_type = $_POST['review_type'];
$receiver_id = $_POST['receiver_id'];
$reply_text = trim($_POST['reply_text']);

if (empty($reply_text)) {
    echo json_encode(['success' => false, 'error' => 'Reply cannot be empty']);
    exit();
}

// Insert reply
$sql = "INSERT INTO review_replies (review_id, review_type, user_id, reply_text) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isis", $review_id, $review_type, $user_id, $reply_text);

if ($stmt->execute()) {
    // Get username for response
    $user_sql = "SELECT username FROM users WHERE user_id = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'username' => $user['username']
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>