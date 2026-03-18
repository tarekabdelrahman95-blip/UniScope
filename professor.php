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

$professor_id = $_GET['id'];

// Get professor details with college info
$prof_sql = "SELECT p.*, c.college_name, c.college_id 
             FROM professors p 
             JOIN colleges c ON p.college_id = c.college_id 
             WHERE p.professor_id = ?";
$stmt = $conn->prepare($prof_sql);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$prof_result = $stmt->get_result();

if ($prof_result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$professor = $prof_result->fetch_assoc();

// Get professor reviews
$review_sql = "SELECT pr.*, u.username 
               FROM professor_ratings pr 
               JOIN users u ON pr.user_id = u.user_id 
               WHERE pr.professor_id = ? 
               ORDER BY pr.created_at DESC";
$stmt = $conn->prepare($review_sql);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$reviews = $stmt->get_result();

// Handle rating submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rating'])) {
    $rating = $_POST['rating'];
    $review = trim($_POST['review']);
    $user_id = $_SESSION['user_id'];
    
    // Check if user already rated this professor
    $check_sql = "SELECT * FROM professor_ratings WHERE user_id = ? AND professor_id = ?";
    $check = $conn->prepare($check_sql);
    $check->bind_param("ii", $user_id, $professor_id);
    $check->execute();
    $check_result = $check->get_result();
    
    if ($check_result->num_rows == 0) {
        // Insert new rating
        $insert_sql = "INSERT INTO professor_ratings (user_id, professor_id, rating, review) VALUES (?, ?, ?, ?)";
        $insert = $conn->prepare($insert_sql);
        $insert->bind_param("iiis", $user_id, $professor_id, $rating, $review);
        
        if ($insert->execute()) {
            // Update professor average rating
            $avg_sql = "UPDATE professors SET 
                        avg_rating = (SELECT AVG(rating) FROM professor_ratings WHERE professor_id = ?),
                        total_ratings = (SELECT COUNT(*) FROM professor_ratings WHERE professor_id = ?)
                        WHERE professor_id = ?";
            $avg = $conn->prepare($avg_sql);
            $avg->bind_param("iii", $professor_id, $professor_id, $professor_id);
            $avg->execute();
            
            header("Location: professor.php?id=" . $professor_id . "&review=success");
            exit();
        }
    } else {
        $error = "You have already reviewed this professor.";
    }
}

// Check if user has already reviewed
$user_reviewed = false;
$user_review_check = $conn->prepare("SELECT * FROM professor_ratings WHERE user_id = ? AND professor_id = ?");
$user_review_check->bind_param("ii", $_SESSION['user_id'], $professor_id);
$user_review_check->execute();
if ($user_review_check->get_result()->num_rows > 0) {
    $user_reviewed = true;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($professor['professor_name']); ?> - Professor Profile</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="navbar">
        <h1><a href="index.php">🎓 College Rating System</a></h1>
        <div class="user-menu">
            <span class="username">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <a href="college.php?id=<?php echo $professor['college_id']; ?>" class="back-link">← Back to <?php echo htmlspecialchars($professor['college_name']); ?></a>
        
        <?php if(isset($_GET['review']) && $_GET['review'] == 'success'): ?>
            <div class="success-message">✓ Thank you! Your review has been submitted successfully.</div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="professor-card">
            <h1 class="professor-name"><?php echo htmlspecialchars($professor['professor_name']); ?></h1>
            <div class="department"><?php echo htmlspecialchars($professor['department']); ?></div>
            <div class="college-link">
                at <a href="college.php?id=<?php echo $professor['college_id']; ?>"><?php echo htmlspecialchars($professor['college_name']); ?></a>
            </div>
            
            <div class="rating-big">
                <?php 
                $rating = round($professor['avg_rating'], 1);
                for($i = 1; $i <= 5; $i++) {
                    if($i <= $rating) echo "★";
                    else echo "☆";
                }
                ?>
                <span>(<?php echo $professor['total_ratings']; ?> reviews)</span>
            </div>
            
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value"><?php echo $professor['total_ratings']; ?></div>
                    <div class="stat-label">Total Reviews</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo round($professor['avg_rating'], 1); ?></div>
                    <div class="stat-label">Average Rating</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php 
                        if($professor['avg_rating'] >= 4.5) echo "🌟 Excellent";
                        elseif($professor['avg_rating'] >= 4.0) echo "👍 Very Good";
                        elseif($professor['avg_rating'] >= 3.0) echo "👌 Good";
                        else echo "📚 Needs Improvement";
                    ?></div>
                    <div class="stat-label">Performance</div>
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <span class="badge">👨‍🏫 Professor</span>
                <span class="badge">📚 <?php echo htmlspecialchars($professor['department']); ?></span>
            </div>
        </div>
        
        <div class="section">
            <h2 class="section-title">📝 About Professor</h2>
            <p class="info-text">
                Dr. <?php echo htmlspecialchars($professor['professor_name']); ?> is a respected faculty member 
                in the Department of <?php echo htmlspecialchars($professor['department']); ?> at 
                <?php echo htmlspecialchars($professor['college_name']); ?>. 
                They have received <?php echo $professor['total_ratings']; ?> student reviews with an 
                average rating of <?php echo round($professor['avg_rating'], 1); ?> out of 5.
            </p>
        </div>
        
        <div class="section">
            <h2 class="section-title">📝 Student Reviews</h2>
            
            <?php if(!$user_reviewed): ?>
                <div class="review-form">
                    <h3 style="margin-bottom: 15px;">Review this Professor</h3>
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
                            <textarea name="review" placeholder="Share your experience with this professor... (teaching style, helpfulness, course difficulty, etc.)" required></textarea>
                        </div>
                        <button type="submit" class="btn">Submit Review</button>
                    </form>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 30px;">
    <?php if($reviews->num_rows > 0): ?>
        <?php while($review = $reviews->fetch_assoc()): ?>
            <div class="review">
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
                
                <!-- ASK QUESTION BUTTON - ADD THIS RIGHT HERE -->
                <div class="review-actions" style="margin-top: 10px;">
                    <?php if($review['user_id'] != $_SESSION['user_id']): ?>
                    <button onclick="openMessageModal(<?php echo $review['rating_id']; ?>, 'professor', <?php echo $review['user_id']; ?>)" 
                            class="ask-btn" 
                            style="background: none; border: 1px solid #1a73e8; color: #1a73e8; padding: 5px 15px; border-radius: 20px; cursor: pointer; font-size: 12px;">
                        💬 Ask Question
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <p>No reviews yet. Be the first to review this professor!</p>
        </div>
    <?php endif; ?>
</div>
        </div>
    </div>
</body>
</html>