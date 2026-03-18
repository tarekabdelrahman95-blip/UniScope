<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$major_id = $_GET['id'];

// Get major details with college info
$major_sql = "SELECT m.*, c.college_name, c.college_id 
              FROM majors m 
              JOIN colleges c ON m.college_id = c.college_id 
              WHERE m.major_id = ?";
$stmt = $conn->prepare($major_sql);
$stmt->bind_param("i", $major_id);
$stmt->execute();
$major_result = $stmt->get_result();

if ($major_result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$major = $major_result->fetch_assoc();

// Get major reviews
$review_sql = "SELECT mr.*, u.username 
               FROM major_ratings mr 
               JOIN users u ON mr.user_id = u.user_id 
               WHERE mr.major_id = ? 
               ORDER BY mr.created_at DESC";
$stmt = $conn->prepare($review_sql);
$stmt->bind_param("i", $major_id);
$stmt->execute();
$reviews = $stmt->get_result();

// Handle rating submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rating'])) {
    $rating = $_POST['rating'];
    $review = trim($_POST['review']);
    $user_id = $_SESSION['user_id'];
    
    // Check if user already rated this major
    $check_sql = "SELECT * FROM major_ratings WHERE user_id = ? AND major_id = ?";
    $check = $conn->prepare($check_sql);
    $check->bind_param("ii", $user_id, $major_id);
    $check->execute();
    $check_result = $check->get_result();
    
    if ($check_result->num_rows == 0) {
        // Insert new rating
        $insert_sql = "INSERT INTO major_ratings (user_id, major_id, rating, review) VALUES (?, ?, ?, ?)";
        $insert = $conn->prepare($insert_sql);
        $insert->bind_param("iiis", $user_id, $major_id, $rating, $review);
        
        if ($insert->execute()) {
            // Update major average rating
            $avg_sql = "UPDATE majors SET 
                        avg_rating = (SELECT AVG(rating) FROM major_ratings WHERE major_id = ?),
                        total_ratings = (SELECT COUNT(*) FROM major_ratings WHERE major_id = ?)
                        WHERE major_id = ?";
            $avg = $conn->prepare($avg_sql);
            $avg->bind_param("iii", $major_id, $major_id, $major_id);
            $avg->execute();
            
            header("Location: major.php?id=" . $major_id . "&review=success");
            exit();
        }
    } else {
        $error = "You have already reviewed this major.";
    }
}

// Check if user has already reviewed
$user_reviewed = false;
$user_review_check = $conn->prepare("SELECT * FROM major_ratings WHERE user_id = ? AND major_id = ?");
$user_review_check->bind_param("ii", $_SESSION['user_id'], $major_id);
$user_review_check->execute();
if ($user_review_check->get_result()->num_rows > 0) {
    $user_reviewed = true;
}

// Get icon based on category
function getCategoryIcon($category) {
    $icons = [
        'Engineering & Technology' => '⚙️',
        'Medical & Health Sciences' => '🏥',
        'Arts & Humanities' => '🎨',
        'Law & Legal Studies' => '⚖️',
        'Business & Economics' => '💼',
        'General' => '📚'
    ];
    return isset($icons[$category]) ? $icons[$category] : '📚';
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($major['major_name']); ?> - Major Details</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="label">Total Duration</div>
                    <div class="value"><?php echo $major['duration_years']; ?></div>
                    <div class="unit">years</div>
                </div>
                <div class="info-item">
                    <div class="label">Bachelor's</div>
                    <div class="value"><?php echo $major['bachelor_years']; ?></div>
                    <div class="unit">years</div>
                </div>
                <div class="info-item">
                    <div class="label">Master's</div>
                    <div class="value"><?php echo $major['masters_years']; ?></div>
                    <div class="unit">years</div>
                </div>
                <div class="info-item">
                    <div class="label">PhD</div>
                    <div class="value"><?php echo $major['phd_years']; ?></div>
                    <div class="unit">years</div>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2 class="section-title">📖 Program Overview</h2>
            <p class="general-info"><?php echo nl2br(htmlspecialchars($major['general_info'] ?: $major['description'])); ?></p>
        </div>
        
        <div class="section">
            <h2 class="section-title">🎯 Career Opportunities</h2>
            <p style="color: #555; margin-bottom: 15px;">Graduates can pursue careers in:</p>
            <div class="career-fields">
                <?php 
                $careers = explode(',', $major['career_fields']);
                foreach($careers as $career): 
                ?>
                    <span class="career-tag"><?php echo trim(htmlspecialchars($career)); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="section">
            <h2 class="section-title">📅 Academic Timeline</h2>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-dot">B</div>
                    <div class="timeline-label">Bachelor's</div>
                    <div class="timeline-years"><?php echo $major['bachelor_years']; ?> years</div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot">M</div>
                    <div class="timeline-label">Master's</div>
                    <div class="timeline-years"><?php echo $major['masters_years']; ?> years</div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot">D</div>
                    <div class="timeline-label">PhD</div>
                    <div class="timeline-years"><?php echo $major['phd_years']; ?> years</div>
                </div>
            </div>
            
            <div class="degree-path">
                <strong>🎓 Total time to complete all degrees:</strong> 
                <?php echo $major['bachelor_years'] + $major['masters_years'] + $major['phd_years']; ?> years
                (Bachelor's: <?php echo $major['bachelor_years']; ?> yrs + Master's: <?php echo $major['masters_years']; ?> yrs + PhD: <?php echo $major['phd_years']; ?> yrs)
            </div>
        </div>
        
        <div class="section">
            <h2 class="section-title">📝 Student Reviews</h2>
            
            <?php if(!$user_reviewed): ?>
                <div class="review-form">
                    <h3 style="margin-bottom: 15px;">Review this Major</h3>
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
                            <textarea name="review" placeholder="Share your experience with this major..." required></textarea>
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
                    <button onclick="openMessageModal(<?php echo $review['rating_id']; ?>, 'major', <?php echo $review['user_id']; ?>)" 
                            class="ask-btn" 
                            style="background: none; border: 1px solid #1a73e8; color: #1a73e8; padding: 5px 15px; border-radius: 20px; cursor: pointer; font-size: 12px;">
                        💬 Ask Question
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align: center; color: #666; padding: 30px;">No reviews yet. Be the first to review this major!</p>
    <?php endif; ?>
</div>
        </div>
    </div>
</body>
</html>