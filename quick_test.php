<?php
session_start();
require_once 'db_connect.php';

if (!isLoggedIn()) {
    echo "Please login first: <a href='login.php'>Login</a>";
    exit();
}

$user = getCurrentUser();
echo "<h2>Quick Test - User: " . htmlspecialchars($user['fullname']) . "</h2>";

// Check if user has any completed rentals
try {
    $stmt = $pdo->prepare("
        SELECT r.*, c.name as dress_name
        FROM rentals r 
        JOIN clothes c ON r.dress_id = c.id 
        WHERE (r.borrower_id = ? OR r.owner_id = ?) 
        AND r.status = 'completed'
    ");
    $stmt->execute([$user['id'], $user['id']]);
    $completed = $stmt->fetchAll();
    
    if (empty($completed)) {
        echo "<p>❌ No completed rentals found.</p>";
        echo "<p>To test reviews, you need to:</p>";
        echo "<ol>";
        echo "<li>Go to <a href='order_requests.php'>Order Requests</a></li>";
        echo "<li>Click 'Mark as Completed' on an approved rental</li>";
        echo "<li>Come back here to see the review button</li>";
        echo "</ol>";
    } else {
        echo "<p>✅ Found " . count($completed) . " completed rental(s):</p>";
        foreach ($completed as $rental) {
            echo "<div style='border: 1px solid #28a745; padding: 15px; margin: 10px 0; background: #d4edda;'>";
            echo "<h4>📦 " . htmlspecialchars($rental['dress_name']) . "</h4>";
            echo "<p><strong>Status:</strong> " . $rental['status'] . "</p>";
            echo "<p><a href='review_system.php?rental_id=" . $rental['id'] . "' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>⭐ Leave Review</a></p>";
            echo "</div>";
        }
    }
} catch (PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>

<p><a href="order_requests.php">📋 Order Requests</a> | <a href="order_activity.php">📊 Order Activity</a> | <a href="home.php">🏠 Home</a></p>
