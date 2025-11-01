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
    $listing_id = intval($_GET['id']);
    
    try {
        switch ($action) {
            case 'disapprove':
                $stmt = $pdo->prepare("UPDATE clothes SET is_approved = 0 WHERE id = ?");
                $stmt->execute([$listing_id]);
                $message = 'Listing marked as suspicious and hidden.';
                break;
                
            case 'approve':
                $stmt = $pdo->prepare("UPDATE clothes SET is_approved = 1 WHERE id = ?");
                $stmt->execute([$listing_id]);
                $message = 'Listing restored and made visible.';
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM clothes WHERE id = ?");
                $stmt->execute([$listing_id]);
                $message = 'Listing deleted successfully.';
                break;
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Get all listings
try {
    $stmt = $pdo->query("
        SELECT c.*, u.fullname as owner_name, u.email as owner_email,
               cat.name as category_name
        FROM clothes c 
        JOIN users u ON c.user_id = u.id 
        LEFT JOIN categories cat ON c.category_id = cat.id
        ORDER BY c.posted_date DESC
    ");
    $listings = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Listings - Admin Dashboard</title>
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
        .listings-grid {
            display: grid;
            gap: 20px;
        }
        .listing-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-left: 4px solid #800000;
        }
        .listing-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .listing-header h3 {
            margin: 0;
            color: #333;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .listing-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        .listing-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }
        .listing-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
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
            <h1>📋 Manage Listings</h1>
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
            <h1>Listing Management</h1>
            <p>Manage all clothing listings, approve/disapprove items, and monitor content</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="listings-grid">
            <?php foreach ($listings as $listing): ?>
                <div class="listing-card">
                    <div class="listing-header">
                        <h3><?php echo htmlspecialchars($listing['name']); ?></h3>
                        <span class="status-badge status-<?php echo $listing['is_approved'] ? 'approved' : 'pending'; ?>">
                            <?php echo $listing['is_approved'] ? 'Approved' : 'Pending'; ?>
                        </span>
                    </div>
                    
                    <div class="listing-content">
                        <img src="uploads/dress_images/<?php echo htmlspecialchars($listing['image']); ?>" 
                             alt="<?php echo htmlspecialchars($listing['name']); ?>" 
                             class="listing-image"
                             onerror="this.src='https://via.placeholder.com/300x200'">
                        
                        <div class="listing-info">
                            <div class="info-item">
                                <strong>Owner:</strong><br>
                                <?php echo htmlspecialchars($listing['owner_name']); ?><br>
                                <small><?php echo htmlspecialchars($listing['owner_email']); ?></small>
                            </div>
                            <div class="info-item">
                                <strong>Category:</strong><br>
                                <?php echo htmlspecialchars($listing['category_name'] ?? 'Uncategorized'); ?>
                            </div>
                            <div class="info-item">
                                <strong>Type:</strong><br>
                                <?php echo htmlspecialchars($listing['type']); ?>
                            </div>
                            <div class="info-item">
                                <strong>Rent:</strong><br>
                                NPR <?php echo number_format($listing['rent_per_day']); ?>/day
                            </div>
                            <div class="info-item">
                                <strong>Deposit:</strong><br>
                                NPR <?php echo number_format($listing['fixed_deposit']); ?>
                            </div>
                            <div class="info-item">
                                <strong>Condition:</strong><br>
                                <?php echo htmlspecialchars($listing['condition_status']); ?>
                            </div>
                            <div class="info-item">
                                <strong>Location:</strong><br>
                                <?php echo htmlspecialchars($listing['location']); ?>
                            </div>
                            <div class="info-item">
                                <strong>Posted:</strong><br>
                                <?php echo date('M j, Y', strtotime($listing['posted_date'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($listing['description']): ?>
                        <div class="info-item" style="margin-bottom: 15px;">
                            <strong>Description:</strong><br>
                            <?php echo htmlspecialchars($listing['description']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="action-buttons">
                        <a href="view_user.php?id=<?php echo $listing['user_id']; ?>" class="btn">View Owner</a>
                        <?php if ($listing['is_approved']): ?>
                            <a href="admin_listings.php?action=disapprove&id=<?php echo $listing['id']; ?>" 
                               class="btn btn-warning" 
                               onclick="return confirm('Mark this listing as suspicious and hide it?')">Mark Suspicious</a>
                        <?php else: ?>
                            <a href="admin_listings.php?action=approve&id=<?php echo $listing['id']; ?>" 
                               class="btn btn-success">Restore</a>
                        <?php endif; ?>
                        <a href="admin_listings.php?action=delete&id=<?php echo $listing['id']; ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this listing? This action cannot be undone!')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
