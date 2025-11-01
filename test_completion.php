<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo "<h2>❌ Please login first</h2>";
    echo "<p><a href='login.php'>Go to Login</a></p>";
    exit();
}

$user = getCurrentUser();
echo "<h2>🔍 Test Completion System</h2>";
echo "<p><strong>Current User:</strong> " . htmlspecialchars($user['fullname']) . "</p>";

// Check current rental statuses
try {
    // Check rentals where user is owner
    $stmt = $pdo->prepare("
        SELECT r.*, c.name as dress_name, b.fullname as borrower_name
        FROM rentals r 
        JOIN clothes c ON r.dress_id = c.id 
        JOIN users b ON r.borrower_id = b.id
        WHERE r.owner_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $owner_rentals = $stmt->fetchAll();
    
    echo "<h3>📋 Your Rentals (as Owner):</h3>";
    if (empty($owner_rentals)) {
        echo "<p>No rentals found where you are the owner.</p>";
    } else {
        foreach ($owner_rentals as $rental) {
            echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
            echo "<h4>📦 " . htmlspecialchars($rental['dress_name']) . "</h4>";
            echo "<p><strong>Borrower:</strong> " . htmlspecialchars($rental['borrower_name']) . "</p>";
            echo "<p><strong>Status:</strong> " . ucfirst($rental['status']) . "</p>";
            echo "<p><strong>Rental ID:</strong> " . $rental['id'] . "</p>";
            echo "<p><strong>Is Completed:</strong> " . ($rental['is_completed'] ? 'Yes' : 'No') . "</p>";
            
            if ($rental['status'] == 'approved') {
                echo "<p><a href='handle_request.php?action=complete&id=" . $rental['id'] . "' style='background: #800000; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>✅ Mark as Completed</a></p>";
            } elseif ($rental['status'] == 'completed') {
                echo "<p><a href='review_system.php?rental_id=" . $rental['id'] . "' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>⭐ Leave Review</a></p>";
            }
            echo "</div>";
        }
    }
    
    // Check rentals where user is borrower
    $stmt = $pdo->prepare("
        SELECT r.*, c.name as dress_name, u.fullname as owner_name
        FROM rentals r 
        JOIN clothes c ON r.dress_id = c.id 
        JOIN users u ON r.owner_id = u.id
        WHERE r.borrower_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $borrower_rentals = $stmt->fetchAll();
    
    echo "<h3>📥 Your Rentals (as Borrower):</h3>";
    if (empty($borrower_rentals)) {
        echo "<p>No rentals found where you are the borrower.</p>";
    } else {
        foreach ($borrower_rentals as $rental) {
            echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
            echo "<h4>📦 " . htmlspecialchars($rental['dress_name']) . "</h4>";
            echo "<p><strong>Owner:</strong> " . htmlspecialchars($rental['owner_name']) . "</p>";
            echo "<p><strong>Status:</strong> " . ucfirst($rental['status']) . "</p>";
            echo "<p><strong>Rental ID:</strong> " . $rental['id'] . "</p>";
            echo "<p><strong>Is Completed:</strong> " . ($rental['is_completed'] ? 'Yes' : 'No') . "</p>";
            
            if ($rental['status'] == 'completed') {
                echo "<p><a href='review_system.php?rental_id=" . $rental['id'] . "' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>⭐ Leave Review</a></p>";
            }
            echo "</div>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Completion System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .btn { display: inline-block; margin: 5px; }
    </style>
</head>
<body>
    <hr>
    <h3>🔗 Quick Links:</h3>
    <a href="home.php" class="btn">🏠 Home</a>
    <a href="profile.php" class="btn">👤 Profile</a>
    <a href="order_requests.php" class="btn">📋 Order Requests</a>
    <a href="order_activity.php" class="btn">📊 Order Activity</a>
    <a href="logout.php" class="btn">🚪 Logout</a>
</body>
</html>
