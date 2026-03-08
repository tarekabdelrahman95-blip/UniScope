<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get statistics
$stats = [];

$result = $conn->query("SELECT COUNT(*) as count FROM colleges");
$stats['colleges'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM majors");
$stats['majors'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM professors");
$stats['professors'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['users'] = $result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 32px;
            color: #2c3e50;
        }
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
        </div>
        <div class="sidebar-menu">
            <a href="index.php" class="menu-item active">📊 Dashboard</a>
            <a href="colleges.php" class="menu-item">🏛️ Colleges</a>
            <a href="majors.php" class="menu-item">📚 Majors</a>
            <a href="professors.php" class="menu-item">👨‍🏫 Professors</a>
            <a href="users.php" class="menu-item">👥 Users</a>
            <a href="logout.php" class="menu-item">🚪 Logout</a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="top-bar">
            <h1>Dashboard</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $stats['colleges']; ?></h3>
                <p>Colleges</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['majors']; ?></h3>
                <p>Majors</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['professors']; ?></h3>
                <p>Professors</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['users']; ?></h3>
                <p>Users</p>
            </div>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 10px;">
            <h2>Quick Actions</h2>
            <div style="margin-top: 20px;">
                <a href="add_college.php" style="background: #2ecc71; color: white; padding: 10px 20px; text-decoration: none; margin-right: 10px;">+ Add College</a>
                <a href="add_major.php" style="background: #3498db; color: white; padding: 10px 20px; text-decoration: none; margin-right: 10px;">+ Add Major</a>
                <a href="add_professor.php" style="background: #e67e22; color: white; padding: 10px 20px; text-decoration: none;">+ Add Professor</a>
            </div>
        </div>
    </div>
</body>
</html>