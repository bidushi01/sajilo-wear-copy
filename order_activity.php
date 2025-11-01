<?php
session_start();
require_once 'db_connect.php';

// Require login
requireLogin();

$user = getCurrentUser();

// Get user's rental activity (both as borrower and as owner)
$stmt = $pdo->prepare("
    SELECT r.*, c.name as dress_name, c.image as dress_image, 
           u.fullname as owner_name, u.email as owner_email,
           b.fullname as borrower_name, b.email as borrower_email,
           CASE 
               WHEN r.borrower_id = ? THEN 'borrowed'
               WHEN r.owner_id = ? THEN 'lent'
           END as user_role
    FROM rentals r 
    JOIN clothes c ON r.dress_id = c.id 
    JOIN users u ON r.owner_id = u.id 
    JOIN users b ON r.borrower_id = b.id
    WHERE (r.borrower_id = ? OR r.owner_id = ?)
    ORDER BY r.created_at DESC
");
$stmt->execute([$user['id'], $user['id'], $user['id'], $user['id']]);
$rental_activity = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Activity - SajiloWear</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .dashboard-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }
        .dashboard-header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .dashboard-header p {
            color: #6c757d;
            font-size: 1.1rem;
        }
        .activity-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .activity-header h3 {
            color: #2c3e50;
            margin: 0;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-requested {
            background: #fff3cd;
            color: #856404;
        }
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        .status-returned {
            background: #d1ecf1;
            color: #0c5460;
        }
        .activity-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .activity-detail {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        .activity-detail strong {
            color: #2c3e50;
        }
        .activity-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            float: left;
            margin-right: 15px;
        }
        .activity-content {
            overflow: hidden;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .empty-state h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .empty-state p {
            color: #6c757d;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #800000;
            color: white;
            text-decoration: none;
            border-radius: 20px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: #a00000;
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        .back-link {
            margin-bottom: 20px;
        }
        .back-link a {
            color: #800000;
            text-decoration: none;
            font-weight: bold;
        }
        .back-link a:hover {
            text-decoration: underline;
            color: #a00000;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div><b>SajiloWear</b></div>
        <div>
            <a href="home.php">Home</a>
            <a href="home.php#featured">Browse</a>
            <a href="list_cloth.php">List Your Cloth</a>
            <a href="home.php#about">About</a>
            <a href="home.php#contact">Contact</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        </div>
    </div>

    <div class="dashboard-container">
        <div class="back-link">
            <a href="profile.php">← Back to Profile</a>
        </div>
        
        <div class="dashboard-header">
            <h1>📊 Order Activity</h1>
            <p>Track your rental requests and activities</p>
        </div>

        <?php if (empty($rental_activity)): ?>
            <div class="empty-state">
                <h3>No rental activity yet</h3>
                <p>When you request to rent clothes from other users, your activity will appear here.</p>
                <a href="home.php#featured" class="btn">Browse Available Clothes</a>
            </div>
        <?php else: ?>
            <?php foreach ($rental_activity as $activity): ?>
                <div class="activity-card">
                    <div class="activity-header">
                        <h3><?php echo htmlspecialchars($activity['dress_name']); ?></h3>
                        <span class="status-badge status-<?php echo $activity['status']; ?>">
                            <?php echo ucfirst($activity['status']); ?>
                        </span>
                    </div>
                    
                    <div class="activity-content">
                        <img src="uploads/dress_images/<?php echo htmlspecialchars($activity['dress_image']); ?>" 
                             alt="<?php echo htmlspecialchars($activity['dress_name']); ?>" 
                             class="activity-image">
                        
                        <div class="activity-details">
                            <?php if ($activity['user_role'] == 'borrowed'): ?>
                                <!-- User is the borrower -->
                                <div class="activity-detail">
                                    <strong>Owner:</strong><br>
                                    <a href="view_user.php?id=<?php echo $activity['owner_id']; ?>" style="color: #800000; text-decoration: none;">
                                        👤 <?php echo htmlspecialchars($activity['owner_name']); ?>
                                    </a><br>
                                    <small><?php echo htmlspecialchars($activity['owner_email']); ?></small>
                                </div>
                                <div class="activity-detail">
                                    <strong>Your Details:</strong><br>
                                    📞 <?php echo htmlspecialchars($activity['borrower_phone']); ?><br>
                                    📍 <?php echo htmlspecialchars($activity['borrower_location']); ?><br>
                                    🚚 <?php echo ucfirst($activity['delivery_option']); ?>
                                </div>
                            <?php else: ?>
                                <!-- User is the owner -->
                                <div class="activity-detail">
                                    <strong>Borrower:</strong><br>
                                    <a href="view_user.php?id=<?php echo $activity['borrower_id']; ?>" style="color: #800000; text-decoration: none;">
                                        👤 <?php echo htmlspecialchars($activity['borrower_name']); ?>
                                    </a><br>
                                    <small><?php echo htmlspecialchars($activity['borrower_email']); ?></small>
                                </div>
                                <div class="activity-detail">
                                    <strong>Borrower Details:</strong><br>
                                    📞 <?php echo htmlspecialchars($activity['borrower_phone']); ?><br>
                                    📍 <?php echo htmlspecialchars($activity['borrower_location']); ?><br>
                                    🚚 <?php echo ucfirst($activity['delivery_option']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="activity-detail">
                                <strong>Rental Period:</strong><br>
                                <?php echo date('M d, Y', strtotime($activity['start_date'])); ?><br>
                                to <?php echo date('M d, Y', strtotime($activity['end_date'])); ?>
                            </div>
                            <div class="activity-detail">
                                <strong>Total Days:</strong><br>
                                <?php 
                                $days = (strtotime($activity['end_date']) - strtotime($activity['start_date'])) / (60 * 60 * 24);
                                echo $days . ' days';
                                ?>
                            </div>
                            <div class="activity-detail">
                                <strong>Requested on:</strong><br>
                                <?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?>
                            </div>
                            <?php if ($activity['status'] == 'completed'): ?>
                                <div class="activity-detail">
                                    <strong>Action:</strong><br>
                                    <?php if ($activity['user_role'] == 'borrowed'): ?>
                                        <a href="review_system.php?rental_id=<?php echo $activity['id']; ?>" class="btn" style="font-size: 12px; padding: 5px 10px;">⭐ Review Owner</a>
                                    <?php else: ?>
                                        <a href="review_system.php?rental_id=<?php echo $activity['id']; ?>" class="btn" style="font-size: 12px; padding: 5px 10px;">⭐ Review Borrower</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
