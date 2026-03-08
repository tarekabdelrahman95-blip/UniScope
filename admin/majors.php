<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM majors WHERE major_id = $id");
    header("Location: majors.php?msg=deleted");
    exit();
}

$majors = $conn->query("
    SELECT m.*, c.college_name 
    FROM majors m 
    JOIN colleges c ON m.college_id = c.college_id 
    ORDER BY c.college_name, m.major_name
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Majors</title>
    <style>
        /* Same styles as colleges.php */
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
        .top-bar {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
        }
        .add-btn {
            background: #2ecc71;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        table {
            width: 100%;
            background: white;
            border-radius: 10px;
            border-collapse: collapse;
        }
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .action-btn {
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
            margin: 0 2px;
        }
        .edit-btn { background: #3498db; color: white; }
        .delete-btn { background: #e74c3c; color: white; }
        .msg {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
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
            <a href="colleges.php" class="menu-item">🏛️ Colleges</a>
            <a href="majors.php" class="menu-item active">📚 Majors</a>
            <a href="professors.php" class="menu-item">👨‍🏫 Professors</a>
            <a href="users.php" class="menu-item">👥 Users</a>
            <a href="logout.php" class="menu-item">🚪 Logout</a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="top-bar">
            <h1>Manage Majors</h1>
            <a href="add_major.php" class="add-btn">+ Add Major</a>
        </div>
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="msg">Major deleted successfully!</div>
        <?php endif; ?>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Major Name</th>
                    <th>College</th>
                    <th>Category</th>
                    <th>Duration</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($major = $majors->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $major['major_id']; ?></td>
                    <td><?php echo htmlspecialchars($major['major_name']); ?></td>
                    <td><?php echo htmlspecialchars($major['college_name']); ?></td>
                    <td><?php echo htmlspecialchars($major['category']); ?></td>
                    <td><?php echo $major['duration_years']; ?> years</td>
                    <td>
                        <a href="edit_major.php?id=<?php echo $major['major_id']; ?>" class="action-btn edit-btn">Edit</a>
                        <a href="majors.php?delete=<?php echo $major['major_id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>