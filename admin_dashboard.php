<?php
session_start();
require_once 'db_connect.php';

// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

$admin = getCurrentUser();

// Get statistics
try {
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'user'");
    $total_users = $stmt->fetch()['total'];
    
    // Total listings
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clothes");
    $total_listings = $stmt->fetch()['total'];
    
    // Total rentals
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM rentals");
    $total_rentals = $stmt->fetch()['total'];
    
    // Recent users
    $stmt = $pdo->query("SELECT * FROM users WHERE user_type = 'user' ORDER BY created_at DESC LIMIT 5");
    $recent_users = $stmt->fetchAll();
    
    // Recent listings
    $stmt = $pdo->query("
        SELECT c.*, u.fullname as owner_name 
        FROM clothes c 
        JOIN users u ON c.user_id = u.id 
        ORDER BY c.posted_date DESC LIMIT 5
    ");
    $recent_listings = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SajiloWear</title>
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
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            color: #800000;
            margin: 0 0 10px 0;
            font-size: 32px;
        }
        .stat-card p {
            color: #666;
            margin: 0;
            font-size: 16px;
        }
        .dashboard-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .dashboard-section h2 {
            color: #800000;
            margin: 0 0 20px 0;
            border-bottom: 2px solid #800000;
            padding-bottom: 10px;
        }
        .user-list, .listing-list {
            display: grid;
            gap: 15px;
        }
        .user-item, .listing-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #800000;
        }
        .user-item h4, .listing-item h4 {
            margin: 0 0 5px 0;
            color: #333;
        }
        .user-item p, .listing-item p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
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
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-navbar">
        <div class="container">
            <h1>🔧 Admin Dashboard</h1>
            <div class="nav-links">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="admin_users.php">Manage Users</a>
                <a href="admin_listings.php">Manage Listings</a>
                <a href="admin_rentals.php">Manage Rentals</a>
                <a href="home.php">View Site</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="dashboard-container">
        <h1>Welcome, <?php echo htmlspecialchars($admin['fullname']); ?>!</h1>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $total_users; ?></h3>
                <p>Total Users</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $total_listings; ?></h3>
                <p>Total Listings</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $total_rentals; ?></h3>
                <p>Total Rentals</p>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="dashboard-section">
            <h2>Recent Users</h2>
            <div class="user-list">
                <?php foreach ($recent_users as $user): ?>
                    <div class="user-item">
                        <h4><?php echo htmlspecialchars($user['fullname']); ?></h4>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($user['location']); ?></p>
                        <p><strong>Joined:</strong> <?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
                        <div class="action-buttons">
                            <a href="admin_users.php?action=view&id=<?php echo $user['id']; ?>" class="btn">View Details</a>
                            <?php if ($user['is_active']): ?>
                                <a href="admin_users.php?action=deactivate&id=<?php echo $user['id']; ?>" class="btn btn-danger">Deactivate</a>
                            <?php else: ?>
                                <a href="admin_users.php?action=activate&id=<?php echo $user['id']; ?>" class="btn btn-success">Activate</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Listings -->
        <div class="dashboard-section">
            <h2>Recent Listings</h2>
            <div class="listing-list">
                <?php foreach ($recent_listings as $listing): ?>
                    <div class="listing-item">
                        <h4><?php echo htmlspecialchars($listing['name']); ?></h4>
                        <p><strong>Owner:</strong> <?php echo htmlspecialchars($listing['owner_name']); ?></p>
                        <p><strong>Type:</strong> <?php echo htmlspecialchars($listing['type']); ?></p>
                        <p><strong>Rent:</strong> NPR <?php echo number_format($listing['rent_per_day']); ?>/day</p>
                        <p><strong>Posted:</strong> <?php echo date('M j, Y', strtotime($listing['posted_date'])); ?></p>
                        <div class="action-buttons">
                            <a href="admin_listings.php?action=view&id=<?php echo $listing['id']; ?>" class="btn">View Details</a>
                            <?php if ($listing['is_approved']): ?>
                                <a href="admin_listings.php?action=disapprove&id=<?php echo $listing['id']; ?>" class="btn btn-danger">Disapprove</a>
                            <?php else: ?>
                                <a href="admin_listings.php?action=approve&id=<?php echo $listing['id']; ?>" class="btn btn-success">Approve</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
