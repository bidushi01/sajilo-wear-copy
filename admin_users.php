<?php
session_start();
require_once 'db_connect.php';

// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

$message = '';
$error = '';

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = intval($_GET['id']);
    
    try {
        switch ($action) {
            case 'activate':
                $stmt = $pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ? AND user_type = 'user'");
                $stmt->execute([$user_id]);
                $message = 'User activated successfully.';
                break;
                
            case 'deactivate':
                $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ? AND user_type = 'user'");
                $stmt->execute([$user_id]);
                $message = 'User deactivated successfully.';
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND user_type = 'user'");
                $stmt->execute([$user_id]);
                $message = 'User deleted successfully.';
                break;
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Get all users
try {
    $stmt = $pdo->query("
        SELECT u.*, 
               COUNT(c.id) as listing_count,
               COUNT(r.id) as rental_count
        FROM users u 
        LEFT JOIN clothes c ON u.id = c.user_id 
        LEFT JOIN rentals r ON u.id = r.borrower_id OR u.id = r.owner_id
        WHERE u.user_type = 'user'
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background: #f8f9fa;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }
        .admin-navbar {
            background: #800000;
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .admin-navbar .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-navbar h1 {
            margin: 0;
            font-size: 24px;
        }
        .admin-navbar .nav-links {
            display: flex;
            gap: 20px;
        }
        .admin-navbar a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .admin-navbar a:hover {
            background: rgba(255,255,255,0.1);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .page-header {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .page-header h1 {
            color: #800000;
            margin: 0 0 10px 0;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .users-grid {
            display: grid;
            gap: 20px;
        }
        .user-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-left: 4px solid #800000;
        }
        .user-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .user-header h3 {
            margin: 0;
            color: #333;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        .user-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .info-item {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        .info-item strong {
            color: #800000;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #800000;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background: #a00000;
            color: white;
            text-decoration: none;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background: #e0a800;
            color: #212529;
        }
    </style>
</head>
<body>
    <div class="admin-navbar">
        <div class="container">
            <h1>👥 Manage Users</h1>
            <div class="nav-links">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="admin_users.php">Manage Users</a>
                <a href="admin_listings.php">Manage Listings</a>
                <a href="home.php">View Site</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1>User Management</h1>
            <p>Manage all registered users, their status, and activities</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="users-grid">
            <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <div class="user-header">
                        <h3><?php echo htmlspecialchars($user['fullname']); ?></h3>
                        <span class="status-badge status-<?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    
                    <div class="user-info">
                        <div class="info-item">
                            <strong>Email:</strong><br>
                            <?php echo htmlspecialchars($user['email']); ?>
                        </div>
                        <div class="info-item">
                            <strong>Location:</strong><br>
                            <?php echo htmlspecialchars($user['location']); ?>
                        </div>
                        <div class="info-item">
                            <strong>Listings:</strong><br>
                            <?php echo $user['listing_count']; ?> items
                        </div>
                        <div class="info-item">
                            <strong>Rentals:</strong><br>
                            <?php echo $user['rental_count']; ?> transactions
                        </div>
                        <div class="info-item">
                            <strong>Joined:</strong><br>
                            <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                        </div>
                        <div class="info-item">
                            <strong>Last Login:</strong><br>
                            <?php echo $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never'; ?>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="view_user.php?id=<?php echo $user['id']; ?>" class="btn">View Profile</a>
                        <?php if ($user['is_active']): ?>
                            <a href="admin_users.php?action=deactivate&id=<?php echo $user['id']; ?>" 
                               class="btn btn-warning" 
                               onclick="return confirm('Are you sure you want to deactivate this user?')">Deactivate</a>
                        <?php else: ?>
                            <a href="admin_users.php?action=activate&id=<?php echo $user['id']; ?>" 
                               class="btn btn-success">Activate</a>
                        <?php endif; ?>
                        <a href="admin_users.php?action=delete&id=<?php echo $user['id']; ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone!')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
