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

// Get majors with all fields
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
    
    // Check if user already rated
    $check_sql = "SELECT * FROM college_ratings WHERE user_id = ? AND college_id = ?";
    $check = $conn->prepare($check_sql);
    $check->bind_param("ii", $user_id, $college_id);
    $check->execute();
    $check_result = $check->get_result();
    
    if ($check_result->num_rows == 0) {
        // Insert new rating
        $insert_sql = "INSERT INTO college_ratings (user_id, college_id, rating, review) VALUES (?, ?, ?, ?)";
        $insert = $conn->prepare($insert_sql);
        $insert->bind_param("iiis", $user_id, $college_id, $rating, $review);
        
        if ($insert->execute()) {
            // Update college average rating
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

// Check if user has already reviewed
$user_reviewed = false;
$review_check = $conn->prepare("SELECT * FROM college_ratings WHERE user_id = ? AND college_id = ?");
$review_check->bind_param("ii", $_SESSION['user_id'], $college_id);
$review_check->execute();
if ($review_check->get_result()->num_rows > 0) {
    $user_reviewed = true;
}

// Function to get icon based on category
function getCategoryIcon($category) {
    $icons = [
        'Engineering & Technology' => '⚙️',
        'Medical & Health Sciences' => '🏥',
        'Arts & Humanities' => '🎨',
        'Law & Legal Studies' => '⚖️',
        'Business & Economics' => '💼',
        'Computer Science' => '💻',
        'General' => '📚'
    ];
    
    foreach ($icons as $cat => $icon) {
        if (strpos($category, $cat) !== false) {
            return $icon;
        }
    }
    return '📚';
}
?>

<!DOCTYPE html>
<html>
<head>
    <head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($college['college_name']); ?> - College Rating</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
</head>
<body>
    <div class="navbar">
        <h1><a href="index.php">🎓 UniScope</a></h1>
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
        
        
        <div class="college-header">
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
            
            <?php if($college['description']): ?>
                <p class="description"><?php echo nl2br(htmlspecialchars($college['description'])); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2 class="section-title">📚 Majors Offered</h2>
            
            <!-- Category Filter Buttons -->
            <div class="filter-container" id="filterContainer">
                <button class="category-filter active" data-category="all">All Majors</button>
                <button class="category-filter" data-category="Engineering & Technology">⚙️ Engineering</button>
                <button class="category-filter" data-category="Medical & Health Sciences">🏥 Medical</button>
                <button class="category-filter" data-category="Law & Legal Studies">⚖️ Law</button>
                <button class="category-filter" data-category="Business & Economics">💼 Business</button>
                <button class="category-filter" data-category="Arts & Humanities">🎨 Arts</button>
                <button class="category-filter" data-category="Computer Science">💻 Computer Science</button>
            </div>
            
            <?php if($majors->num_rows > 0): ?>
                <div class="grid" id="majorsGrid">
                    <?php 
                    // Reset pointer to fetch all majors again
                    $majors->data_seek(0);
                    while($major = $majors->fetch_assoc()): 
                        $category = isset($major['category']) ? $major['category'] : 'General';
                        $icon = isset($major['icon']) ? $major['icon'] : getCategoryIcon($category);
                        $duration = isset($major['duration_years']) ? $major['duration_years'] : 4;
                    ?>
                        <div class="major-card" data-category="<?php echo htmlspecialchars($category); ?>">
                            <div class="major-icon"><?php echo $icon; ?></div>
                            <span class="category-tag"><?php echo htmlspecialchars($category); ?></span>
                            
                            <a href="major.php?id=<?php echo $major['major_id']; ?>" class="card-name">
                                <?php echo htmlspecialchars($major['major_name']); ?>
                            </a>
                            
                            <div class="card-rating">
                                <?php 
                                $major_rating = round($major['avg_rating'], 1);
                                for($i = 1; $i <= 5; $i++) {
                                    if($i <= $major_rating) echo "★";
                                    else echo "☆";
                                }
                                ?>
                               
                            </div>
                            
                            <p class="card-description">
                                <?php echo htmlspecialchars(substr($major['description'], 0, 100)) . '...'; ?>
                            </p>
                            
                            <div class="card-footer">
                                <span class="duration">🕒 <?php echo $duration; ?> years</span>
                                <a href="major.php?id=<?php echo $major['major_id']; ?>" class="view-link">
                                    View Details →
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>No majors available for this college yet.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2 class="section-title">👨‍🏫 Professors</h2>
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
                                <span style="color: #666; font-size: 12px;">(<?php echo $prof['total_ratings']; ?> reviews)</span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>No professors listed for this college yet.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2 class="section-title">📝 Student Reviews</h2>
            
            <?php if(!$user_reviewed): ?>
                <div class="rating-form">
                    <h3 style="margin-bottom: 15px;">Write a Review</h3>
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
                            <textarea name="review" placeholder="Share your experience with this college..." required></textarea>
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
                    <button onclick="openMessageModal(<?php echo $review['rating_id']; ?>, 'college', <?php echo $review['user_id']; ?>)" 
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
            <p>No reviews yet. Be the first to review this college!</p>
        </div>
    <?php endif; ?>
</div>
        </div>
    </div>

    <!-- JavaScript for filtering -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filters = document.querySelectorAll('.category-filter');
        const majorCards = document.querySelectorAll('.major-card');
        
        filters.forEach(filter => {
            filter.addEventListener('click', function() {
                // Remove active class from all filters
                filters.forEach(f => f.classList.remove('active'));
                
                // Add active class to clicked filter
                this.classList.add('active');
                
                const category = this.dataset.category;
                
                // Filter majors
                majorCards.forEach(card => {
                    if(category === 'all' || card.dataset.category === category) {
                        card.style.display = 'block';
                        // Add animation
                        card.style.animation = 'fadeIn 0.5s';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
        
        // Add fade-in animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;
        document.head.appendChild(style);
    });
    </script>
    <!-- Message Modal -->
<div id="messageModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background: white; max-width: 500px; margin: 100px auto; padding: 30px; border-radius: 10px; position: relative;">
        <span onclick="closeMessageModal()" style="position: absolute; right: 20px; top: 20px; font-size: 24px; cursor: pointer;">&times;</span>
        <h3 style="margin-bottom: 20px; color: #1a73e8;">Send Message</h3>
        
        <form id="messageForm" onsubmit="sendMessage(event)">
            <input type="hidden" id="receiverId" name="receiver_id">
            <input type="hidden" id="reviewId" name="review_id">
            <input type="hidden" id="reviewType" name="review_type">
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Your Message:</label>
                <textarea id="messageText" name="message" rows="4" required 
                          style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
                          placeholder="Type your question here..."></textarea>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn" style="background: #1a73e8; flex: 1;">Send Message</button>
                <button type="button" onclick="closeMessageModal()" class="btn" style="background: #6c757d; flex: 1;">Cancel</button>
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

// Close modal if user clicks outside
window.onclick = function(event) {
    const modal = document.getElementById('messageModal');
    if (event.target == modal) {
        closeMessageModal();
    }
}
</script>
</body>
</html>