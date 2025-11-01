<?php
session_start();
require_once 'db_connect.php';

// Require login
requireLogin();

$user = getCurrentUser();

// Get rental requests for user's clothes
$stmt = $pdo->prepare("
    SELECT r.*, c.name as dress_name, c.image as dress_image, u.fullname as borrower_name, u.email as borrower_email
    FROM rentals r 
    JOIN clothes c ON r.dress_id = c.id 
    JOIN users u ON r.borrower_id = u.id 
    WHERE r.owner_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$user['id']]);
$rental_requests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Requests - SajiloWear</title>
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
        .request-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .request-header h3 {
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
        .request-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .request-detail {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        .request-detail strong {
            color: #2c3e50;
        }
        .request-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
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
            border: none;
            cursor: pointer;
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
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #1e7e34;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
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
            <h1>📋 Order Requests</h1>
            <p>Manage rental requests for your traditional clothes</p>
        </div>

        <?php if (empty($rental_requests)): ?>
            <div class="empty-state">
                <h3>No rental requests yet</h3>
                <p>When people request to rent your clothes, they'll appear here for you to approve or decline.</p>
            </div>
        <?php else: ?>
            <?php foreach ($rental_requests as $request): ?>
                <div class="request-card">
                    <div class="request-header">
                        <h3><?php echo htmlspecialchars($request['dress_name']); ?></h3>
                        <span class="status-badge status-<?php echo $request['status']; ?>">
                            <?php echo ucfirst($request['status']); ?>
                        </span>
                    </div>
                    
                    <div class="request-details">
                        <div class="request-detail">
                            <strong>Requested by:</strong><br>
                            <a href="view_user.php?id=<?php echo $request['borrower_id']; ?>" style="color: #800000; text-decoration: none; font-weight: bold;">
                                👤 <?php echo htmlspecialchars($request['borrower_name']); ?>
                            </a><br>
                            <small><?php echo htmlspecialchars($request['borrower_email']); ?></small>
                        </div>
                        <div class="request-detail">
                            <strong>Contact Info:</strong><br>
                            📞 <?php echo htmlspecialchars($request['borrower_phone']); ?><br>
                            📍 <?php echo htmlspecialchars($request['borrower_location']); ?>
                        </div>
                        <div class="request-detail">
                            <strong>Delivery Option:</strong><br>
                            <?php echo ucfirst($request['delivery_option']); ?><br>
                            <?php if ($request['delivery_option'] == 'delivery'): ?>
                                <small>Will deliver to borrower</small>
                            <?php else: ?>
                                <small>Borrower will pickup</small>
                            <?php endif; ?>
                        </div>
                        <div class="request-detail">
                            <strong>Rental Period:</strong><br>
                            <?php echo date('M d, Y', strtotime($request['start_date'])); ?><br>
                            to <?php echo date('M d, Y', strtotime($request['end_date'])); ?>
                        </div>
                        <div class="request-detail">
                            <strong>Total Days:</strong><br>
                            <?php 
                            $days = (strtotime($request['end_date']) - strtotime($request['start_date'])) / (60 * 60 * 24);
                            echo $days . ' days';
                            ?>
                        </div>
                        <div class="request-detail">
                            <strong>Requested on:</strong><br>
                            <?php echo date('M d, Y H:i', strtotime($request['created_at'])); ?>
                        </div>
                    </div>

                    <?php if ($request['status'] == 'requested'): ?>
                        <div class="request-actions">
                            <button class="btn btn-success" onclick="approveRequest(<?php echo $request['id']; ?>)">
                                Approve Request
                            </button>
                            <button class="btn btn-danger" onclick="declineRequest(<?php echo $request['id']; ?>)">
                                Decline Request
                            </button>
                        </div>
                    <?php elseif ($request['status'] == 'approved'): ?>
                        <div class="request-actions">
                            <span class="status-info">✅ Request approved. Waiting for rental period to complete.</span>
                            <br><br>
                            <button class="btn btn-primary" onclick="markCompleted(<?php echo $request['id']; ?>)">
                                ✅ Mark as Completed
                            </button>
                        </div>
                    <?php elseif ($request['status'] == 'declined'): ?>
                        <div class="request-actions">
                            <span class="status-info">❌ Request declined.</span>
                        </div>
                    <?php elseif ($request['status'] == 'completed'): ?>
                        <div class="request-actions">
                            <span class="status-info">🎉 Rental completed! Both parties can now leave reviews.</span>
                            <br><br>
                            <a href="review_system.php?rental_id=<?php echo $request['id']; ?>" class="btn btn-primary" style="background: #800000; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
                                ⭐ Leave Review for Borrower
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        function approveRequest(requestId) {
            if (confirm('Are you sure you want to approve this rental request?')) {
                window.location.href = 'handle_request.php?action=approve&id=' + requestId;
            }
        }

        function declineRequest(requestId) {
            if (confirm('Are you sure you want to decline this rental request?')) {
                window.location.href = 'handle_request.php?action=decline&id=' + requestId;
            }
        }

        function markCompleted(requestId) {
            if (confirm('Are you sure you want to mark this rental as completed? This will allow both parties to leave reviews.')) {
                window.location.href = 'handle_request.php?action=complete&id=' + requestId;
            }
        }
    </script>
</body>
</html>
