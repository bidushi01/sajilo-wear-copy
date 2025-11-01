<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo "<h2>❌ Please login first to test review system</h2>";
    echo "<p><a href='login.php'>Go to Login</a></p>";
    exit();
}

$user = getCurrentUser();
echo "<h2>🔍 Review System Test</h2>";
echo "<p><strong>Current User:</strong> " . htmlspecialchars($user['fullname']) . "</p>";

// Check if user has any completed rentals
try {
    $stmt = $pdo->prepare("
        SELECT r.*, c.name as dress_name, u.fullname as owner_name
        FROM rentals r 
        JOIN clothes c ON r.dress_id = c.id
        JOIN users u ON r.owner_id = u.id
        WHERE (r.borrower_id = ? OR r.owner_id = ?) 
        AND r.status = 'completed'
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$user['id'], $user['id']]);
    $completed_rentals = $stmt->fetchAll();
    
    if (empty($completed_rentals)) {
        echo "<h3>📝 No Completed Rentals Found</h3>";
        echo "<p>To test the review system, you need to:</p>";
        echo "<ol>";
        echo "<li>Create a rental request</li>";
        echo "<li>Have the owner approve it</li>";
        echo "<li>Mark it as completed</li>";
        echo "<li>Then you can leave a review</li>";
        echo "</ol>";
        echo "<p><a href='home.php'>Go to Home</a> | <a href='profile.php'>Go to Profile</a></p>";
    } else {
        echo "<h3>✅ Found " . count($completed_rentals) . " Completed Rental(s)</h3>";
        foreach ($completed_rentals as $rental) {
            echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
            echo "<h4>📦 " . htmlspecialchars($rental['dress_name']) . "</h4>";
            echo "<p><strong>Owner:</strong> " . htmlspecialchars($rental['owner_name']) . "</p>";
            echo "<p><strong>Status:</strong> " . ucfirst($rental['status']) . "</p>";
            echo "<p><strong>Rental ID:</strong> " . $rental['id'] . "</p>";
            echo "<p><a href='review_system.php?rental_id=" . $rental['id'] . "' style='background: #800000; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>⭐ Leave Review</a></p>";
            echo "</div>";
        }
    }
    
    // Check if user has any pending rentals
    $stmt = $pdo->prepare("
        SELECT r.*, c.name as dress_name, u.fullname as owner_name
        FROM rentals r 
        JOIN clothes c ON r.dress_id = c.id
        JOIN users u ON r.owner_id = u.id
        WHERE (r.borrower_id = ? OR r.owner_id = ?) 
        AND r.status IN ('requested', 'approved')
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$user['id'], $user['id']]);
    $pending_rentals = $stmt->fetchAll();
    
    if (!empty($pending_rentals)) {
        echo "<h3>⏳ Pending Rentals (" . count($pending_rentals) . ")</h3>";
        echo "<p>These rentals need to be completed before you can review:</p>";
        foreach ($pending_rentals as $rental) {
            echo "<div style='border: 1px solid #ffc107; padding: 15px; margin: 10px 0; border-radius: 8px; background: #fff3cd;'>";
            echo "<h4>📦 " . htmlspecialchars($rental['dress_name']) . "</h4>";
            echo "<p><strong>Owner:</strong> " . htmlspecialchars($rental['owner_name']) . "</p>";
            echo "<p><strong>Status:</strong> " . ucfirst($rental['status']) . "</p>";
            echo "<p><em>Complete this rental to leave a review</em></p>";
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
    <title>Review System Test</title>
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
    <a href="order_activity.php" class="btn">📋 Order Activity</a>
    <a href="logout.php" class="btn">🚪 Logout</a>
</body>
</html>
