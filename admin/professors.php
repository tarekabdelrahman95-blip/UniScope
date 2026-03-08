<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM professors WHERE professor_id = $id");
    header("Location: professors.php?msg=deleted");
    exit();
}

// Get all professors with college names
$professors = $conn->query("
    SELECT p.*, c.college_name 
    FROM professors p 
    JOIN colleges c ON p.college_id = c.college_id 
    ORDER BY c.college_name, p.professor_name
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Professors - Admin</title>
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
        
        .add-btn {
            background: #2ecc71;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .add-btn:hover {
            background: #27ae60;
        }
        
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #ddd;
            color: #333;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        tr:hover {
            background: #f5f5f5;
        }
        
        .action-btn {
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
            margin: 0 2px;
            font-size: 12px;
        }
        
        .edit-btn {
            background: #3498db;
            color: white;
        }
        
        .edit-btn:hover {
            background: #2980b9;
        }
        
        .delete-btn {
            background: #e74c3c;
            color: white;
        }
        
        .delete-btn:hover {
            background: #c0392b;
        }
        
        .msg {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .rating {
            color: #f39c12;
        }
        
        .college-badge {
            background: #3498db;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
        }
        
        .search-box {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        
        .search-box input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .search-box button {
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
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
            <h1>Manage Professors</h1>
            <div class="admin-info">
                <span class="admin-name"><?php echo $_SESSION['admin_name']; ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <a href="add_professor.php" class="add-btn">+ Add New Professor</a>
        </div>
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="msg">
                <?php 
                if($_GET['msg'] == 'added') echo "Professor added successfully!";
                if($_GET['msg'] == 'updated') echo "Professor updated successfully!";
                if($_GET['msg'] == 'deleted') echo "Professor deleted successfully!";
                ?>
            </div>
        <?php endif; ?>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Professor Name</th>
                        <th>Department</th>
                        <th>College</th>
                        <th>Rating</th>
                        <th>Reviews</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($professors->num_rows > 0): ?>
                        <?php while($prof = $professors->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $prof['professor_id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($prof['professor_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($prof['department']); ?></td>
                            <td><span class="college-badge"><?php echo htmlspecialchars($prof['college_name']); ?></span></td>
                            <td class="rating">
                                <?php 
                                $rating = round($prof['avg_rating'], 1);
                                for($i = 1; $i <= 5; $i++) {
                                    if($i <= $rating) echo "★";
                                    else echo "☆";
                                }
                                ?>
                            </td>
                            <td><?php echo $prof['total_ratings']; ?></td>
                            <td>
                                <a href="edit_professor.php?id=<?php echo $prof['professor_id']; ?>" class="action-btn edit-btn">✏️ Edit</a>
                                <a href="professors.php?delete=<?php echo $prof['professor_id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this professor? This will also delete all their reviews!')">🗑️ Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: #666;">No professors found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>