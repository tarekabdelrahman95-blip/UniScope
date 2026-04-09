<?php
require_once 'config-live.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$college_id = $_GET['id'];

// Get college details
$college_sql = "SELECT * FROM colleges WHERE college_id = ?";
$stmt = $conn->prepare($college_sql);
$stmt->bind_param("i", $college_id);
$stmt->execute();
$college_result = $stmt->get_result();

if ($college_result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$college = $college_result->fetch_assoc();

// Get professors
$prof_sql = "SELECT * FROM professors WHERE college_id = ? ORDER BY professor_name";
$stmt = $conn->prepare($prof_sql);
$stmt->bind_param("i", $college_id);
$stmt->execute();
$professors = $stmt->get_result();

// Get majors
$major_sql = "SELECT * FROM majors WHERE college_id = ? ORDER BY category, major_name";
$stmt = $conn->prepare($major_sql);
$stmt->bind_param("i", $college_id);
$stmt->execute();
$majors = $stmt->get_result();

// Get reviews
$review_sql = "SELECT cr.*, u.username FROM college_ratings cr 
               JOIN users u ON cr.user_id = u.user_id 
               WHERE cr.college_id = ? 
               ORDER BY cr.created_at DESC";
$stmt = $conn->prepare($review_sql);
$stmt->bind_param("i", $college_id);
$stmt->execute();
$reviews = $stmt->get_result();

// Handle rating submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rating'])) {
    $rating = $_POST['rating'];
    $review = trim($_POST['review']);
    $user_id = $_SESSION['user_id'];
    
    $check_sql = "SELECT * FROM college_ratings WHERE user_id = ? AND college_id = ?";
    $check = $conn->prepare($check_sql);
    $check->bind_param("ii", $user_id, $college_id);
    $check->execute();
    $check_result = $check->get_result();
    
    if ($check_result->num_rows == 0) {
        $insert_sql = "INSERT INTO college_ratings (user_id, college_id, rating, review) VALUES (?, ?, ?, ?)";
        $insert = $conn->prepare($insert_sql);
        $insert->bind_param("iiis", $user_id, $college_id, $rating, $review);
        
        if ($insert->execute()) {
            $avg_sql = "UPDATE colleges SET 
                        avg_rating = (SELECT AVG(rating) FROM college_ratings WHERE college_id = ?),
                        total_ratings = (SELECT COUNT(*) FROM college_ratings WHERE college_id = ?)
                        WHERE college_id = ?";
            $avg = $conn->prepare($avg_sql);
            $avg->bind_param("iii", $college_id, $college_id, $college_id);
            $avg->execute();
            
            header("Location: college.php?id=" . $college_id . "&review=success");
            exit();
        }
    } else {
        $error = "You have already reviewed this college.";
    }
}

$user_reviewed = false;
$review_check = $conn->prepare("SELECT * FROM college_ratings WHERE user_id = ? AND college_id = ?");
$review_check->bind_param("ii", $_SESSION['user_id'], $college_id);
$review_check->execute();
if ($review_check->get_result()->num_rows > 0) {
    $user_reviewed = true;
}

// Parse gallery images
$gallery_images = [];
if (!empty($college['gallery_images'])) {
    $gallery_images = explode(',', $college['gallery_images']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($college['college_name']); ?> - UniScope</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="navbar">
        <h1><a href="index.php">UniScope</a></h1>
        <div class="user-menu">
            <span class="username">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <a href="index.php" class="back-link">← Back to Home</a>
        
        <?php if(isset($_GET['review']) && $_GET['review'] == 'success'): ?>
            <div class="success-message">✓ Thank you! Your review has been submitted successfully.</div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- College Header -->
        <div class="college-header">
            <?php if (!empty($college['college_image'])): ?>
            <div class="college-image-container">
                <img src="<?php echo $college['college_image']; ?>" alt="<?php echo htmlspecialchars($college['college_name']); ?>" class="college-image">
            </div>
            <?php endif; ?>
            
            <h1 class="college-name"><?php echo htmlspecialchars($college['college_name']); ?></h1>
            <div class="rating-big">
                <?php 
                $rating = round($college['avg_rating'], 1);
                for($i = 1; $i <= 5; $i++) {
                    if($i <= $rating) echo "★";
                    else echo "☆";
                }
                ?>
                <span>(<?php echo $college['total_ratings']; ?> reviews)</span>
            </div>
            
            <div class="info-row">
                <?php if($college['location']): ?>
                    <div class="info-item">📍 <?php echo htmlspecialchars($college['location']); ?></div>
                <?php endif; ?>
                <?php if($college['website']): ?>
                    <div class="info-item">🌐 <a href="<?php echo htmlspecialchars($college['website']); ?>" target="_blank">Official Website</a></div>
                <?php endif; ?>
            </div>
            
            <h3>Overview</h3>
            <p class="college-overview">
                <?php echo !empty($college['overview']) ? nl2br(htmlspecialchars($college['overview'])) : 'No overview available yet.'; ?>
            </p>
        </div>
        
        <!-- Gallery Section -->
        <div class="section">
            <h2 class="section-title">📸 University Gallery</h2>
            
            <?php if (!empty($gallery_images)): ?>
            <div class="gallery-grid">
                <?php foreach($gallery_images as $image): ?>
                <div class="gallery-item">
                    <img src="<?php echo trim($image); ?>" alt="University image">
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="empty-state">No gallery images yet.</p>
            <?php endif; ?>
        </div>
        
        <!-- Majors Section -->
        <div class="section">
            <h2 class="section-title">📚 Academic Programs</h2>
            
            <?php if($majors->num_rows > 0): ?>
                <div class="grid">
                    <?php while($major = $majors->fetch_assoc()): ?>
                        <div class="major-card">
                            <h3 class="card-name">
                                <a href="major.php?id=<?php echo $major['major_id']; ?>">
                                    <?php echo htmlspecialchars($major['major_name']); ?>
                                </a>
                            </h3>
                            <div class="card-rating">
                                <?php 
                                $major_rating = round($major['avg_rating'], 1);
                                for($i = 1; $i <= 5; $i++) {
                                    if($i <= $major_rating) echo "★";
                                    else echo "☆";
                                }
                                ?>
                                <span>(<?php echo $major['total_ratings']; ?> reviews)</span>
                            </div>
                            <p class="card-description">
                                <?php echo htmlspecialchars(substr($major['description'], 0, 100)) . '...'; ?>
                            </p>
                            <div class="card-footer">
                                <span class="duration">🕒 <?php echo $major['duration_years']; ?> years</span>
                                <a href="major.php?id=<?php echo $major['major_id']; ?>" class="view-link">View Program →</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>No academic programs available yet.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Professors Section -->
        <div class="section">
            <h2 class="section-title">👨‍🏫 Faculty</h2>
            <?php if($professors->num_rows > 0): ?>
                <div class="prof-grid">
                    <?php while($prof = $professors->fetch_assoc()): ?>
                        <div class="prof-card">
                            <a href="professor.php?id=<?php echo $prof['professor_id']; ?>" class="prof-name">
                                <?php echo htmlspecialchars($prof['professor_name']); ?>
                            </a>
                            <div class="dept"><?php echo htmlspecialchars($prof['department']); ?></div>
                            <div class="rating">
                                <?php 
                                $prof_rating = round($prof['avg_rating'], 1);
                                for($i = 1; $i <= 5; $i++) {
                                    if($i <= $prof_rating) echo "★";
                                    else echo "☆";
                                }
                                ?>
                                <span>(<?php echo $prof['total_ratings']; ?> reviews)</span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>No faculty listed yet.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Reviews Section -->
        <div class="section">
            <h2 class="section-title">📝 Student Reviews</h2>
            
            <?php if(!$user_reviewed): ?>
                <div class="rating-form">
                    <h3>Write a Review</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Your Rating</label>
                            <select name="rating" required>
                                <option value="">Select rating</option>
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Very Good</option>
                                <option value="3">3 - Good</option>
                                <option value="2">2 - Fair</option>
                                <option value="1">1 - Poor</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Your Review</label>
                            <textarea name="review" placeholder="Share your experience..." required></textarea>
                        </div>
                        <button type="submit" class="btn">Submit Review</button>
                    </form>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 30px;">
                <?php if($reviews->num_rows > 0): ?>
                    <?php while($review = $reviews->fetch_assoc()): 
                        $replies_sql = "SELECT rr.*, u.username 
                                       FROM review_replies rr 
                                       JOIN users u ON rr.user_id = u.user_id 
                                       WHERE rr.review_id = ? AND rr.review_type = 'college' 
                                       ORDER BY rr.created_at ASC";
                        $replies_stmt = $conn->prepare($replies_sql);
                        $replies_stmt->bind_param("i", $review['rating_id']);
                        $replies_stmt->execute();
                        $replies = $replies_stmt->get_result();
                    ?>
                        <div class="review" id="review-<?php echo $review['rating_id']; ?>">
                            <div class="review-header">
                                <span class="review-user"><?php echo htmlspecialchars($review['username']); ?></span>
                                <span class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                            </div>
                            <div class="review-rating">
                                <?php 
                                for($i = 1; $i <= 5; $i++) {
                                    if($i <= $review['rating']) echo "★";
                                    else echo "☆";
                                }
                                ?>
                            </div>
                            <p class="review-text"><?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
                            
                            <div class="review-actions">
                                <?php if($review['user_id'] != $_SESSION['user_id']): ?>
                                <button onclick="openMessageModal(<?php echo $review['rating_id']; ?>, 'college', <?php echo $review['user_id']; ?>)" class="ask-btn">💬 Ask Question</button>
                                <?php endif; ?>
                                <button onclick="showReplyForm(<?php echo $review['rating_id']; ?>, 'college', <?php echo $review['user_id']; ?>)" class="reply-btn">↩️ Reply</button>
                            </div>
                            
                            <div id="reply-form-<?php echo $review['rating_id']; ?>" class="reply-form" style="display: none;">
                                <form onsubmit="submitReply(event, <?php echo $review['rating_id']; ?>, 'college', <?php echo $review['user_id']; ?>)">
                                    <textarea id="reply-text-<?php echo $review['rating_id']; ?>" rows="2" placeholder="Write your reply..." required></textarea>
                                    <div>
                                        <button type="submit">Post Reply</button>
                                        <button type="button" onclick="hideReplyForm(<?php echo $review['rating_id']; ?>)">Cancel</button>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="replies-section">
                                <?php if($replies->num_rows > 0): ?>
                                    <?php while($reply = $replies->fetch_assoc()): ?>
                                        <div class="reply">
                                            <div>
                                                <span>↪️ <?php echo htmlspecialchars($reply['username']); ?></span>
                                                <span><?php echo date('M d, Y', strtotime($reply['created_at'])); ?></span>
                                            </div>
                                            <p><?php echo nl2br(htmlspecialchars($reply['reply_text'])); ?></p>
                                        </div>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No reviews yet. Be the first to review!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Message Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content slide-up">
            <span onclick="closeMessageModal()" class="close">&times;</span>
            <h3>Send Message</h3>
            
            <form id="messageForm" onsubmit="sendMessage(event)">
                <input type="hidden" id="receiverId" name="receiver_id">
                <input type="hidden" id="reviewId" name="review_id">
                <input type="hidden" id="reviewType" name="review_type">
                
                <textarea id="messageText" name="message" rows="4" required placeholder="Type your question here..."></textarea>
                
                <div>
                    <button type="submit">Send Message</button>
                    <button type="button" onclick="closeMessageModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openMessageModal(reviewId, reviewType, receiverId) {
        document.getElementById('receiverId').value = receiverId;
        document.getElementById('reviewId').value = reviewId;
        document.getElementById('reviewType').value = reviewType;
        document.getElementById('messageModal').style.display = 'block';
    }

    function closeMessageModal() {
        document.getElementById('messageModal').style.display = 'none';
        document.getElementById('messageForm').reset();
    }

    function sendMessage(event) {
        event.preventDefault();
        
        const formData = new FormData(document.getElementById('messageForm'));
        
        fetch('send_message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Message sent successfully!');
                closeMessageModal();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            alert('Error sending message. Please try again.');
        });
    }

    function showReplyForm(reviewId, reviewType, receiverId) {
        document.querySelectorAll('.reply-form').forEach(form => {
            form.style.display = 'none';
        });
        
        const form = document.getElementById('reply-form-' + reviewId);
        form.style.display = 'block';
        form.dataset.receiverId = receiverId;
        form.dataset.reviewType = reviewType;
    }

    function hideReplyForm(reviewId) {
        const form = document.getElementById('reply-form-' + reviewId);
        form.style.display = 'none';
    }

    function submitReply(event, reviewId, reviewType, receiverId) {
        event.preventDefault();
        
        const replyText = document.getElementById('reply-text-' + reviewId).value;
        
        if (!replyText.trim()) {
            alert('Please write a reply');
            return;
        }
        
        const formData = new FormData();
        formData.append('review_id', reviewId);
        formData.append('review_type', reviewType);
        formData.append('receiver_id', receiverId);
        formData.append('reply_text', replyText);
        
        fetch('submit_reply.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const repliesSection = document.querySelector('#review-' + reviewId + ' .replies-section');
                const newReply = document.createElement('div');
                newReply.className = 'reply';
                newReply.innerHTML = `
                    <div>
                        <span>↪️ ${escapeHtml(data.username)}</span>
                        <span>Just now</span>
                    </div>
                    <p>${escapeHtml(replyText)}</p>
                `;
                repliesSection.appendChild(newReply);
                hideReplyForm(reviewId);
                document.getElementById('reply-text-' + reviewId).value = '';
            } else {
                alert('Error posting reply: ' + data.error);
            }
        })
        .catch(error => {
            alert('Error posting reply. Please try again.');
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    window.onclick = function(event) {
        const modal = document.getElementById('messageModal');
        if (event.target == modal) {
            closeMessageModal();
        }
    }
    </script>
</body>
</html>