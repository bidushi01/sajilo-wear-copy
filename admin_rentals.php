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
    $rental_id = intval($_GET['id']);
    
    try {
        switch ($action) {
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM rentals WHERE id = ?");
                $stmt->execute([$rental_id]);
                $message = 'Rental request deleted successfully.';
                break;
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Get all rental requests
try {
    $stmt = $pdo->query("
        SELECT r.*, c.name as dress_name, c.image as dress_image,
               u_borrower.fullname as borrower_name, u_borrower.email as borrower_email,
               u_owner.fullname as owner_name, u_owner.email as owner_email
        FROM rentals r 
        JOIN clothes c ON r.dress_id = c.id
        JOIN users u_borrower ON r.borrower_id = u_borrower.id
        JOIN users u_owner ON r.owner_id = u_owner.id
        ORDER BY r.created_at DESC
    ");
    $rentals = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rental Requests - Admin Dashboard</title>
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
        .rentals-grid {
            display: grid;
            gap: 20px;
        }
        .rental-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-left: 4px solid #800000;
        }
        .rental-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .rental-header h3 {
            margin: 0;
            color: #333;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
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
        .status-declined {
            background: #f8d7da;
            color: #721c24;
        }
        .rental-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        .rental-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }
        .rental-info {
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
        .btn-info {
            background: #17a2b8;
        }
        .btn-info:hover {
            background: #138496;
        }
    </style>
</head>
<body>
    <div class="admin-navbar">
        <div class="container">
            <h1>📋 Manage Rental Requests</h1>
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

    <div class="container">
        <div class="page-header">
            <h1>Rental Request Management</h1>
            <p>Monitor and manage all rental requests. Delete suspicious requests if found.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="rentals-grid">
            <?php foreach ($rentals as $rental): ?>
                <div class="rental-card">
                    <div class="rental-header">
                        <h3><?php echo htmlspecialchars($rental['dress_name']); ?></h3>
                        <span class="status-badge status-<?php echo $rental['status']; ?>">
                            <?php echo ucfirst($rental['status']); ?>
                        </span>
                    </div>
                    
                    <div class="rental-content">
                        <img src="uploads/dress_images/<?php echo htmlspecialchars($rental['dress_image']); ?>" 
                             alt="<?php echo htmlspecialchars($rental['dress_name']); ?>" 
                             class="rental-image"
                             onerror="this.src='https://via.placeholder.com/300x200'">
                        
                        <div class="rental-info">
                            <div class="info-item">
                                <strong>Borrower:</strong><br>
                                <?php echo htmlspecialchars($rental['borrower_name']); ?><br>
                                <small><?php echo htmlspecialchars($rental['borrower_email']); ?></small>
                            </div>
                            <div class="info-item">
                                <strong>Owner:</strong><br>
                                <?php echo htmlspecialchars($rental['owner_name']); ?><br>
                                <small><?php echo htmlspecialchars($rental['owner_email']); ?></small>
                            </div>
                            <div class="info-item">
                                <strong>Rental Period:</strong><br>
                                <?php echo date('M d, Y', strtotime($rental['start_date'])); ?><br>
                                to <?php echo date('M d, Y', strtotime($rental['end_date'])); ?>
                            </div>
                            <div class="info-item">
                                <strong>Total Amount:</strong><br>
                                NPR <?php echo number_format($rental['total_amount']); ?>
                            </div>
                            <div class="info-item">
                                <strong>Contact Info:</strong><br>
                                📞 <?php echo htmlspecialchars($rental['borrower_phone']); ?><br>
                                📍 <?php echo htmlspecialchars($rental['borrower_location']); ?>
                            </div>
                            <div class="info-item">
                                <strong>Delivery:</strong><br>
                                <?php echo ucfirst($rental['delivery_option']); ?>
                            </div>
                            <div class="info-item">
                                <strong>Requested:</strong><br>
                                <?php echo date('M d, Y H:i', strtotime($rental['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($rental['special_notes']): ?>
                        <div class="info-item" style="margin-bottom: 15px;">
                            <strong>Special Notes:</strong><br>
                            <?php echo htmlspecialchars($rental['special_notes']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="action-buttons">
                        <a href="view_user.php?id=<?php echo $rental['borrower_id']; ?>" class="btn">View Borrower</a>
                        <a href="view_user.php?id=<?php echo $rental['owner_id']; ?>" class="btn">View Owner</a>
                        <a href="admin_rentals.php?action=delete&id=<?php echo $rental['id']; ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this rental request? This action cannot be undone!')">Delete Request</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
