<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get all interest categories (we'll create this table first)
// For now, let's define them manually
$categories = [
    ['id' => 1, 'name' => 'Computer & Technology', 'icon' => '💻'],
    ['id' => 2, 'name' => 'Medical & Healthcare', 'icon' => '🏥'],
    ['id' => 3, 'name' => 'Engineering & Design', 'icon' => '⚙️'],
    ['id' => 4, 'name' => 'Business & Management', 'icon' => '💼'],
    ['id' => 5, 'name' => 'Law & Legal', 'icon' => '⚖️'],
    ['id' => 6, 'name' => 'Arts & Creative', 'icon' => '🎨'],
    ['id' => 7, 'name' => 'Science & Research', 'icon' => '🔬'],
    ['id' => 8, 'name' => 'Education & Teaching', 'icon' => '📚'],
    ['id' => 9, 'name' => 'Languages & Translation', 'icon' => '🗣️'],
    ['id' => 10, 'name' => 'Media & Communication', 'icon' => '📺'],
    ['id' => 11, 'name' => 'Agriculture & Environment', 'icon' => '🌱'],
    ['id' => 12, 'name' => 'Sports & Physical', 'icon' => '⚽'],
    ['id' => 13, 'name' => 'Tourism & Hospitality', 'icon' => '🏨'],
    ['id' => 14, 'name' => 'Social Services', 'icon' => '🤝'],
    ['id' => 15, 'name' => 'Mathematics & Statistics', 'icon' => '📊']
];

// Process recommendation request
$recommendations = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['interests'])) {
    $selected_interests = $_POST['interests'];
    
    // For now, we'll use a simple query based on major names
    // This is a simplified version - you'll need to create the interest tables later
    $conditions = [];
    foreach ($selected_interests as $interest_id) {
        // Map interest IDs to keywords in major names
        $keywords = [
            1 => ['Computer', 'IT', 'Software', 'Programming'],
            2 => ['Medical', 'Medicine', 'Health', 'Pharmacy', 'Nursing'],
            3 => ['Engineering', 'Architecture', 'Design'],
            4 => ['Business', 'Management', 'Commerce', 'Economics', 'Accounting'],
            5 => ['Law', 'Legal'],
            6 => ['Arts', 'Fine Arts', 'Design', 'Creative'],
            7 => ['Science', 'Physics', 'Chemistry', 'Biology'],
            8 => ['Education', 'Teaching'],
            9 => ['Languages', 'Translation', 'Linguistics'],
            10 => ['Media', 'Communication', 'Journalism'],
            11 => ['Agriculture', 'Environment', 'Farming'],
            12 => ['Sports', 'Physical Education'],
            13 => ['Tourism', 'Hospitality', 'Hotel'],
            14 => ['Social', 'Sociology', 'Psychology'],
            15 => ['Mathematics', 'Statistics', 'Math']
        ];
        
        if (isset($keywords[$interest_id])) {
            foreach ($keywords[$interest_id] as $keyword) {
                $conditions[] = "major_name LIKE '%$keyword%'";
            }
        }
    }
    
    if (!empty($conditions)) {
        $where_clause = implode(' OR ', $conditions);
        $rec_sql = "SELECT m.*, c.college_name, c.college_id 
                    FROM majors m
                    JOIN colleges c ON m.college_id = c.college_id
                    WHERE $where_clause
                    GROUP BY m.major_id
                    LIMIT 10";
        
        $rec_result = $conn->query($rec_sql);
        
        if ($rec_result) {
            while($row = $rec_result->fetch_assoc()) {
                $recommendations[] = $row;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>AI Recommendations - College Rating System</title>
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
        <a href="index.php" class="back-link">← Back to Home</a>
        
        <div class="interest-section">
            <div class="ai-badge">
                🤖 AI Powered Recommendations
            </div>
            <h1>Find Your Perfect Major</h1>
            <p>Select your interests below and our AI will recommend the best majors and colleges for you.</p>
            
            <form method="POST" id="recommendationForm">
                <div class="interest-grid">
                    <?php foreach($categories as $category): ?>
                    <div class="interest-card" onclick="toggleInterest(this, <?php echo $category['id']; ?>)">
                        <div class="interest-icon"><?php echo $category['icon']; ?></div>
                        <div class="interest-name"><?php echo $category['name']; ?></div>
                        <input type="checkbox" name="interests[]" value="<?php echo $category['id']; ?>" style="display: none;" class="interest-checkbox">
                        
                        <div class="level-slider">
                            <label>Interest level:</label>
                            <input type="range" name="level_<?php echo $category['id']; ?>" class="slider" min="1" max="10" value="5" onchange="updateLevel(this)">
                            <span class="slider-value">5/10</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="text-align: center;">
                    <button type="submit" class="submit-btn">🔍 Get Recommendations</button>
                </div>
            </form>
        </div>
        
        <?php if(!empty($recommendations)): ?>
        <div class="recommendations-section">
            <h2>Your Recommendations</h2>
            <p style="color: #666; margin-bottom: 20px;">Based on your selected interests</p>
            
            <div class="recommendations-grid">
                <?php foreach($recommendations as $rec): 
                    $match_percent = rand(70, 98); // Random for now, you'll calculate properly later
                ?>
                <div class="recommendation-card">
                    <span class="match-score">✨ <?php echo $match_percent; ?>% Match</span>
                    <h3 style="margin: 10px 0 5px;"><?php echo htmlspecialchars($rec['major_name']); ?></h3>
                    <div class="college-name">🏛️ <?php echo htmlspecialchars($rec['college_name']); ?></div>
                    
                    <div class="rating">
                        <?php 
                        $rating = round($rec['avg_rating'], 1);
                        for($i = 1; $i <= 5; $i++) {
                            if($i <= $rating) echo "★";
                            else echo "☆";
                        }
                        ?>
                        <span style="color: #666; font-size: 12px;">(<?php echo $rec['total_ratings']; ?> reviews)</span>
                    </div>
                    
                    <p style="color: #555; font-size: 14px; margin: 10px 0;">
                        <?php echo htmlspecialchars(substr($rec['description'], 0, 100)) . '...'; ?>
                    </p>
                    
                    <div>
                        <a href="major.php?id=<?php echo $rec['major_id']; ?>" class="view-btn">View Major</a>
                        <a href="college.php?id=<?php echo $rec['college_id']; ?>" class="view-btn green">View College</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php elseif($_SERVER["REQUEST_METHOD"] == "POST"): ?>
        <div class="no-results">
            <h3>No recommendations found</h3>
            <p>Try selecting different interests</p>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
    function toggleInterest(card, id) {
        card.classList.toggle('selected');
        const checkbox = card.querySelector('.interest-checkbox');
        checkbox.checked = card.classList.contains('selected');
    }
    
    function updateLevel(slider) {
        const value = slider.value;
        slider.parentElement.querySelector('.slider-value').textContent = value + '/10';
    }
    </script>
</body>
</html>