<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get major ID
$id = $_GET['id'];
$major = $conn->query("SELECT * FROM majors WHERE major_id = $id")->fetch_assoc();

// Get all colleges for dropdown
$colleges = $conn->query("SELECT college_id, college_name FROM colleges ORDER BY college_name");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $college_id = $_POST['college_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $duration = $_POST['duration'];
    $career_fields = $_POST['career_fields'];
    
    $sql = "UPDATE majors SET college_id=?, major_name=?, description=?, category=?, duration_years=?, career_fields=? WHERE major_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssisi", $college_id, $name, $description, $category, $duration, $career_fields, $id);
    
    if ($stmt->execute()) {
        header("Location: majors.php?msg=updated");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Major - Admin</title>
    <style>
        /* Same styles as add_major.php */
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
        .sidebar-menu {
            padding: 20px 0;
        }
        .menu-item {
            padding: 12px 25px;
            display: block;
            color: #ecf0f1;
            text-decoration: none;
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
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 700px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        textarea {
            min-height: 120px;
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        .btn-primary {
            background: #3498db;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            flex: 1;
        }
        .btn-secondary {
            background: #95a5a6;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            flex: 1;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #3498db;
            text-decoration: none;
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
            <h1>Edit Major</h1>
        </div>
        
        <a href="majors.php" class="back-link">← Back to Majors</a>
        
        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label>Select College</label>
                    <select name="college_id" required>
                        <?php while($college = $colleges->fetch_assoc()): ?>
                            <option value="<?php echo $college['college_id']; ?>" 
                                <?php echo ($college['college_id'] == $major['college_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($college['college_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Major Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($major['major_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" required><?php echo htmlspecialchars($major['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="Medical & Health Sciences" <?php echo ($major['category'] == 'Medical & Health Sciences') ? 'selected' : ''; ?>>🏥 Medical & Health Sciences</option>
                        <option value="Engineering & Technology" <?php echo ($major['category'] == 'Engineering & Technology') ? 'selected' : ''; ?>>⚙️ Engineering & Technology</option>
                        <option value="Computer Science" <?php echo ($major['category'] == 'Computer Science') ? 'selected' : ''; ?>>💻 Computer Science</option>
                        <option value="Business & Economics" <?php echo ($major['category'] == 'Business & Economics') ? 'selected' : ''; ?>>💼 Business & Economics</option>
                        <option value="Law & Legal Studies" <?php echo ($major['category'] == 'Law & Legal Studies') ? 'selected' : ''; ?>>⚖️ Law & Legal Studies</option>
                        <option value="Arts & Humanities" <?php echo ($major['category'] == 'Arts & Humanities') ? 'selected' : ''; ?>>🎨 Arts & Humanities</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Duration (years)</label>
                    <input type="number" name="duration" value="<?php echo $major['duration_years']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Career Fields</label>
                    <textarea name="career_fields" required><?php echo htmlspecialchars($major['career_fields']); ?></textarea>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn-primary">Update Major</button>
                    <a href="majors.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>