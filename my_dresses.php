<?php
session_start();
require_once 'db_connect.php';

// Require login
requireLogin();

$user = getCurrentUser();

// Get user's clothes
$stmt = $pdo->prepare("SELECT * FROM clothes WHERE user_id = ? ORDER BY posted_date DESC");
$stmt->execute([$user['id']]);
$user_clothes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dress Listings - SajiloWear</title>
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
        .clothes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .cloth-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .cloth-card:hover {
            transform: translateY(-5px);
        }
        .cloth-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .cloth-card-content {
            padding: 20px;
        }
        .cloth-card h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .cloth-card p {
            color: #666;
            margin: 5px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }
        .status-available {
            background: #d4edda;
            color: #155724;
        }
        .status-rented {
            background: #f8d7da;
            color: #721c24;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #800000;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
            margin: 5px;
        }
        .btn:hover {
            background: #a00000;
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
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
            <h1>👗 My Dress Listings</h1>
            <p>Manage your traditional clothes that are available for rent</p>
            <a href="list_cloth.php" class="btn">Add New Dress</a>
        </div>

        <?php if (empty($user_clothes)): ?>
            <div class="empty-state">
                <h3>No dresses listed yet</h3>
                <p>Start earning by listing your beautiful traditional clothes!</p>
                <a href="list_cloth.php" class="btn">List Your First Dress</a>
            </div>
        <?php else: ?>
            <div class="clothes-grid">
                <?php foreach ($user_clothes as $cloth): ?>
                    <div class="cloth-card">
                        <img src="uploads/dress_images/<?php echo htmlspecialchars($cloth['image']); ?>" 
                             alt="<?php echo htmlspecialchars($cloth['name']); ?>">
                        <div class="cloth-card-content">
                            <h3><?php echo htmlspecialchars($cloth['name']); ?></h3>
                            <p><strong>Type:</strong> <?php echo htmlspecialchars($cloth['type']); ?></p>
                            <p><strong>Rent:</strong> NPR <?php echo number_format($cloth['rent_per_day']); ?>/day</p>
                            <p><strong>Deposit:</strong> NPR <?php echo number_format($cloth['fixed_deposit']); ?></p>
                            <p><strong>Condition:</strong> <?php echo htmlspecialchars($cloth['condition_status']); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($cloth['location']); ?></p>
                            <span class="status-badge status-<?php echo $cloth['is_available'] ? 'available' : 'rented'; ?>">
                                <?php echo $cloth['is_available'] ? 'Available' : 'Currently Rented'; ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
