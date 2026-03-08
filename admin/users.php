<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle delete user
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE user_id = $id");
    header("Location: users.php?msg=deleted");
    exit();
}

// Get all users with their review counts
$users = $conn->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM college_ratings WHERE user_id = u.user_id) as college_reviews,
           (SELECT COUNT(*) FROM major_ratings WHERE user_id = u.user_id) as major_reviews,
           (SELECT COUNT(*) FROM professor_ratings WHERE user_id = u.user_id) as professor_reviews
    FROM users u 
    ORDER BY u.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - Admin</title>
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
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .top-bar h1 {
            color: #333;
        }
        
        .admin-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
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
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        tr:hover {
            background: #f5f5f5;
        }
        
        .delete-btn {
            background: #e74c3c;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 12px;
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
        
        .user-email {
            color: #3498db;
            font-size: 12px;
        }
        
        .badge {
            background: #3498db;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
        }
        
        .review-count {
            font-weight: bold;
            color: #27ae60;
        }
        
        .date {
            color: #7f8c8d;
            font-size: 12px;
        }
        
        .stats-grid {
            display: flex;
            gap: 10px;
        }
        
        .stat-item {
            background: #ecf0f1;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
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
            <a href="professors.php" class="menu-item">👨‍🏫 Professors</a>
            <a href="users.php" class="menu-item active">👥 Users</a>
            <a href="logout.php" class="menu-item">🚪 Logout</a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="top-bar">
            <h1>Manage Users</h1>
            <div class="admin-info">
                <span class="admin-name"><?php echo $_SESSION['admin_name']; ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="msg">User deleted successfully!</div>
        <?php endif; ?>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Joined</th>
                        <th>Reviews</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($users->num_rows > 0): ?>
                        <?php while($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($user['email']); ?>
                            </td>
                            <td class="date">
                                <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td>
                                <div class="stats-grid">
                                    <span class="stat-item">🏛️ <?php echo $user['college_reviews']; ?></span>
                                    <span class="stat-item">📚 <?php echo $user['major_reviews']; ?></span>
                                    <span class="stat-item">👨‍🏫 <?php echo $user['professor_reviews']; ?></span>
                                </div>
                                <div style="margin-top: 5px;">
                                    <span class="badge">Total: <?php echo $user['college_reviews'] + $user['major_reviews'] + $user['professor_reviews']; ?></span>
                                </div>
                            </td>
                            <td>
                                <a href="users.php?delete=<?php echo $user['user_id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user? This will delete all their reviews too!')">Delete User</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px;">No users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>