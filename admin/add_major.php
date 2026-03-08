<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get all colleges for dropdown
$colleges = $conn->query("SELECT college_id, college_name FROM colleges ORDER BY college_name");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $college_id = $_POST['college_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $duration = $_POST['duration'];
    $career_fields = $_POST['career_fields'];
    
    $sql = "INSERT INTO majors (college_id, major_name, description, category, duration_years, career_fields, avg_rating, total_ratings) 
            VALUES (?, ?, ?, ?, ?, ?, 0, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssis", $college_id, $name, $description, $category, $duration, $career_fields);
    
    if ($stmt->execute()) {
        header("Location: majors.php?msg=added");
        exit();
    } else {
        $error = "Error adding major: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Major - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; display: flex; }
        
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
        }
        
        .sidebar-header {
            padding: 20px;
            background: #1a252f;
            text-align: center;
        }
        
        .sidebar-header h2 {
            color: white;
            font-size: 20px;
        }
        
        .sidebar-header h2 span {
            color: #3498db;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 12px 25px;
            display: block;
            color: #ecf0f1;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .menu-item:hover, .menu-item.active {
            background: #34495e;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
        }
        
        .top-bar {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .top-bar h1 {
            color: #333;
            font-size: 24px;
        }
        
        .admin-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .admin-name {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 700px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #3498db;
        }
        
        select {
            background: white;
            cursor: pointer;
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
            font-family: Arial, sans-serif;
        }
        
        .help-text {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        .btn-primary {
            background: #2ecc71;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            flex: 1;
        }
        
        .btn-primary:hover {
            background: #27ae60;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            text-align: center;
            flex: 1;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #3498db;
            text-decoration: none;
            font-size: 16px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .error {
            background: #fed7d7;
            color: #c53030;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #feb2b2;
        }
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 5px;
        }
        
        .category-option {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        
        .category-option:hover {
            background: #f0f2f5;
        }
        
        .category-option.selected {
            background: #3498db;
            color: white;
            border-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>College<span>Rating</span></h2>
        </div>
        <div class="sidebar-menu">
            <a href="index.php" class="menu-item">📊 Dashboard</a>
            <a href="colleges.php" class="menu-item">🏛️ Colleges</a>
            <a href="majors.php" class="menu-item active">📚 Majors</a>
            <a href="professors.php" class="menu-item">👨‍🏫 Professors</a>
            <a href="users.php" class="menu-item">👥 Users</a>
            <a href="logout.php" class="menu-item">🚪 Logout</a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="top-bar">
            <h1>Add New Major</h1>
            <div class="admin-info">
                <span class="admin-name"><?php echo $_SESSION['admin_name']; ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        
        <a href="majors.php" class="back-link">← Back to Majors</a>
        
        <div class="form-container">
            <?php if(isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" onsubmit="return validateForm()">
                <div class="form-group">
                    <label>🏛️ Select College</label>
                    <select name="college_id" required>
                        <option value="">-- Choose a college --</option>
                        <?php while($college = $colleges->fetch_assoc()): ?>
                            <option value="<?php echo $college['college_id']; ?>">
                                <?php echo htmlspecialchars($college['college_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>📚 Major Name</label>
                    <input type="text" name="name" placeholder="e.g. Computer Science, Medicine, Law" required>
                    <div class="help-text">Enter the full official name of the major</div>
                </div>
                
                <div class="form-group">
                    <label>📖 Description</label>
                    <textarea name="description" placeholder="Provide a detailed description of the major..." required></textarea>
                    <div class="help-text">Describe what students will learn, the focus areas, and unique aspects</div>
                </div>
                
                <div class="form-group">
                    <label>🏷️ Category</label>
                    <select name="category" required>
                        <option value="">-- Select category --</option>
                        <option value="Medical & Health Sciences">🏥 Medical & Health Sciences</option>
                        <option value="Engineering & Technology">⚙️ Engineering & Technology</option>
                        <option value="Computer Science">💻 Computer Science</option>
                        <option value="Business & Economics">💼 Business & Economics</option>
                        <option value="Law & Legal Studies">⚖️ Law & Legal Studies</option>
                        <option value="Arts & Humanities">🎨 Arts & Humanities</option>
                        <option value="General Sciences">🔬 General Sciences</option>
                        <option value="Life Sciences">🌱 Life Sciences</option>
                    </select>
                    <div class="help-text">Select the category that best fits this major</div>
                </div>
                
                <div class="form-group">
                    <label>⏱️ Duration (years)</label>
                    <input type="number" name="duration" min="1" max="7" value="4" required>
                    <div class="help-text">How many years does it take to complete this major? (e.g., Medicine=6, Engineering=5, Arts=4)</div>
                </div>
                
                <div class="form-group">
                    <label>💼 Career Fields</label>
                    <textarea name="career_fields" placeholder="e.g. Software Developer, Data Scientist, IT Consultant, Systems Architect" required></textarea>
                    <div class="help-text">List career paths separated by commas</div>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn-primary">➕ Add Major</button>
                    <a href="majors.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function validateForm() {
        var name = document.getElementsByName('name')[0].value;
        var description = document.getElementsByName('description')[0].value;
        var career = document.getElementsByName('career_fields')[0].value;
        
        if(name.length < 3) {
            alert('Major name must be at least 3 characters long');
            return false;
        }
        if(description.length < 20) {
            alert('Please provide a more detailed description (at least 20 characters)');
            return false;
        }
        if(career.split(',').length < 2) {
            alert('Please list at least 2 career fields separated by commas');
            return false;
        }
        return true;
    }
    </script>
</body>
</html>