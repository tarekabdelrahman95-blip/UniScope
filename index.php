<?php
require_once 'config-live.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get suggested colleges (top rated)
$suggested_sql = "SELECT c.*, 
                  (SELECT COUNT(*) FROM college_ratings WHERE college_id = c.college_id) as rating_count 
                  FROM colleges c 
                  ORDER BY c.avg_rating DESC, c.total_ratings DESC 
                  LIMIT 6";
$suggested_result = $conn->query($suggested_sql);

// Handle search
$search_results = null;
$search_term = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $_GET['search'];
    $search = "%" . $search_term . "%";
    $search_sql = "SELECT c.*, 
                   (SELECT COUNT(*) FROM college_ratings WHERE college_id = c.college_id) as rating_count 
                   FROM colleges c 
                   WHERE c.college_name LIKE ? 
                   ORDER BY c.college_name";
    $stmt = $conn->prepare($search_sql);
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $search_results = $stmt->get_result();
}

// Function to get college logo
function getCollegeLogo($college_name) {
    $name = strtolower($college_name);
    if (strpos($name, 'helwan') !== false) {
        return 'assets/images/Helwan.jpg';
    } elseif (strpos($name, 'cairo') !== false) {
        return 'assets/images/cairo-logo.png';
    } elseif (strpos($name, 'alexandria') !== false) {
        return 'assets/images/alexandria-logo.png';
    } else {
        return 'assets/images/default-college.png';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Home - UniScope</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="navbar">
        <h1><a href="index.php">🎓 College Rating System</a></h1>
        <div class="user-menu">
            <div class="nav-actions">
                <a href="recommend.php" class="recommend-btn">
                    <span>🤖</span> AI Recommendations
                </a>
                <a href="messages.php" class="messages-btn">
                    <span>💬</span> Messages
                </a>
            </div>
            <span class="username">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="search-section">
            <h2 class="section-title">Find Your College</h2>
            <form class="search-box" method="GET">
                <input type="text" name="search" placeholder="Search colleges by name..." 
                       value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit">Search</button>
            </form>
            <?php if(!empty($search_term)): ?>
                <div style="text-align: center; margin-top: 10px;">
                    <a href="index.php" class="clear-search">← Clear search</a>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($search_results && $search_results->num_rows > 0): ?>
            <h2 class="section-title">Search Results for "<?php echo htmlspecialchars($search_term); ?>"</h2>
            <div class="college-grid">
                <?php while($college = $search_results->fetch_assoc()): 
                    $is_helwan = (strpos(strtolower($college['college_name']), 'helwan') !== false);
                ?>
                    <div class="college-card <?php echo $is_helwan ? 'featured-card' : ''; ?>">
                        <div class="college-header">
                            <img src="<?php echo getCollegeLogo($college['college_name']); ?>" 
                                 alt="<?php echo htmlspecialchars($college['college_name']); ?>" 
                                 class="college-logo"
                                 onerror="this.src='https://via.placeholder.com/60x60?text=College'">
                            <div>
                                <div class="college-name">
                                    <a href="college.php?id=<?php echo $college['college_id']; ?>">
                                        <?php echo htmlspecialchars($college['college_name']); ?>
                                    </a>
                                    <?php if($is_helwan): ?>
                                        <span class="featured-badge">FEATURED</span>
                                    <?php endif; ?>
                                </div>
                                <div class="college-location">
                                    📍 <?php echo htmlspecialchars($college['location'] ?: 'Location not specified'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="rating">
                            <?php 
                            $rating = round($college['avg_rating'], 1);
                            for($i = 1; $i <= 5; $i++) {
                                if($i <= $rating) echo "★";
                                else echo "☆";
                            }
                            ?>
                        </div>
                        
                        <p class="description"><?php echo htmlspecialchars(substr($college['description'], 0, 150)) . '...'; ?></p>
                        
                        <div class="card-footer">
                            <a href="college.php?id=<?php echo $college['college_id']; ?>" class="view-details">View Details →</a>
                            <span class="review-count"><?php echo $college['total_ratings']; ?> reviews</span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php elseif(!empty($search_term)): ?>
            <div class="no-results">
                <h3>🔍 No colleges found matching "<?php echo htmlspecialchars($search_term); ?>"</h3>
                <p>Try different keywords or browse our suggestions below</p>
            </div>
        <?php endif; ?>
        
        <h2 class="section-title">🌟 Top Rated Colleges</h2>
        <div class="college-grid">
            <?php 
            if ($suggested_result->num_rows > 0) {
                while($college = $suggested_result->fetch_assoc()): 
                    $is_helwan = (strpos(strtolower($college['college_name']), 'helwan') !== false);
            ?>
                <div class="college-card <?php echo $is_helwan ? 'featured-card' : ''; ?>">
                    <div class="college-header">
                        <img src="<?php echo getCollegeLogo($college['college_name']); ?>" 
                             alt="<?php echo htmlspecialchars($college['college_name']); ?>" 
                             class="college-logo"
                             onerror="this.src='https://via.placeholder.com/60x60?text=College'">
                        <div>
                            <div class="college-name">
                                <a href="college.php?id=<?php echo $college['college_id']; ?>">
                                    <?php echo htmlspecialchars($college['college_name']); ?>
                                </a>
                                <?php if($is_helwan): ?>
                                    <span class="featured-badge">FEATURED</span>
                                <?php endif; ?>
                            </div>
                            <div class="college-location">
                                📍 <?php echo htmlspecialchars($college['location'] ?: 'Location not specified'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="rating">
                        <?php 
                        $rating = round($college['avg_rating'], 1);
                        for($i = 1; $i <= 5; $i++) {
                            if($i <= $rating) echo "★";
                            else echo "☆";
                        }
                        ?>
                    </div>
                    
                    <p class="description"><?php echo htmlspecialchars(substr($college['description'], 0, 150)) . '...'; ?></p>
                    
                    <div class="card-footer">
                        <a href="college.php?id=<?php echo $college['college_id']; ?>" class="view-details">View Details →</a>
                        <span class="review-count"><?php echo $college['total_ratings']; ?> reviews</span>
                    </div>
                </div>
            <?php 
                endwhile;
            } else {
                echo "<p class='empty-state'>No colleges available yet.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>