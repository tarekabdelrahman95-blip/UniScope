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
    $department = $_POST['department'];
    
    $sql = "INSERT INTO professors (college_id, professor_name, department, avg_rating, total_ratings) 
            VALUES (?, ?, ?, 0, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $college_id, $name, $department);
    
    if ($stmt->execute()) {
        header("Location: professors.php?msg=added");
        exit();
    } else {
        $error = "Error adding professor: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Professor - Admin</title>
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
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
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        select {
            background: white;
        }
        
        button {
            background: #2ecc71;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        button:hover {
            background: #27ae60;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #3498db;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .error {
            background: #fed7d7;
            color: #c53030;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
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
            <a href="majors.php" class="menu-item">📚 Majors</a>
            <a href="professors.php" class="menu-item active">👨‍🏫 Professors</a>
            <a href="users.php" class="menu-item">👥 Users</a>
            <a href="logout.php" class="menu-item">🚪 Logout</a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="top-bar">
            <h1>Add New Professor</h1>
        </div>
        
        <a href="professors.php" class="back-link">← Back to Professors</a>
        
        <div class="form-container">
            <?php if(isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Select College</label>
                    <select name="college_id" required>
                        <option value="">-- Select College --</option>
                        <?php while($college = $colleges->fetch_assoc()): ?>
                            <option value="<?php echo $college['college_id']; ?>">
                                <?php echo htmlspecialchars($college['college_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Professor Name</label>
                    <input type="text" name="name" placeholder="e.g. Dr. Ahmed Hassan" required>
                </div>
                
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" name="department" placeholder="e.g. Computer Science" required>
                </div>
                
                <button type="submit">Add Professor</button>
            </form>
        </div>
    </div>
</body>
</html>