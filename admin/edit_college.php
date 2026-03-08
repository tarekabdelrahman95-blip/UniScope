<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];
$college = $conn->query("SELECT * FROM colleges WHERE college_id = $id")->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $website = $_POST['website'];
    
    $sql = "UPDATE colleges SET college_name=?, description=?, location=?, website=? WHERE college_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $name, $description, $location, $website, $id);
    
    if ($stmt->execute()) {
        header("Location: colleges.php?msg=updated");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit College</title>
    <style>
        /* Same styles as add_college.php */
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
            padding: 12px 20px;
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
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        textarea {
            min-height: 100px;
        }
        button {
            background: #3498db;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
        </div>
        <div class="sidebar-menu">
            <a href="index.php" class="menu-item">📊 Dashboard</a>
            <a href="colleges.php" class="menu-item active">🏛️ Colleges</a>
            <a href="majors.php" class="menu-item">📚 Majors</a>
            <a href="professors.php" class="menu-item">👨‍🏫 Professors</a>
            <a href="users.php" class="menu-item">👥 Users</a>
            <a href="logout.php" class="menu-item">🚪 Logout</a>
        </div>
    </div>
    
    <div class="main-content">
        <h1>Edit College</h1>
        <a href="colleges.php" style="display: block; margin: 20px 0;">← Back to Colleges</a>
        
        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label>College Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($college['college_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" required><?php echo htmlspecialchars($college['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($college['location']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Website</label>
                    <input type="url" name="website" value="<?php echo htmlspecialchars($college['website']); ?>">
                </div>
                <button type="submit">Update College</button>
            </form>
        </div>
    </div>
</body>
</html>