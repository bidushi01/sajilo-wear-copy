<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo "<h2>❌ Please login first</h2>";
    exit();
}

$user = getCurrentUser();
echo "<h2>🔍 Debug Order Activity</h2>";
echo "<p><strong>Current User:</strong> " . htmlspecialchars($user['fullname']) . " (ID: " . $user['id'] . ")</p>";

try {
    // Test the exact query from order_activity.php
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
    
    echo "<h3>📊 Query Results:</h3>";
    echo "<p>Found " . count($rental_activity) . " rental(s)</p>";
    
    if (empty($rental_activity)) {
        echo "<p>❌ No rentals found. This could be because:</p>";
        echo "<ul>";
        echo "<li>No rentals exist in the database</li>";
        echo "<li>User ID doesn't match any rentals</li>";
        echo "<li>Database connection issue</li>";
        echo "</ul>";
        
        // Let's check if there are any rentals at all
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM rentals");
        $total_rentals = $stmt->fetch()['total'];
        echo "<p><strong>Total rentals in database:</strong> " . $total_rentals . "</p>";
        
        if ($total_rentals > 0) {
            echo "<h4>All rentals in database:</h4>";
            $stmt = $pdo->query("
                SELECT r.*, c.name as dress_name, 
                       u.fullname as owner_name, b.fullname as borrower_name
                FROM rentals r 
                JOIN clothes c ON r.dress_id = c.id 
                JOIN users u ON r.owner_id = u.id 
                JOIN users b ON r.borrower_id = b.id
                ORDER BY r.created_at DESC
            ");
            $all_rentals = $stmt->fetchAll();
            
            foreach ($all_rentals as $rental) {
                echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0;'>";
                echo "<strong>" . htmlspecialchars($rental['dress_name']) . "</strong><br>";
                echo "Owner: " . htmlspecialchars($rental['owner_name']) . " (ID: " . $rental['owner_id'] . ")<br>";
                echo "Borrower: " . htmlspecialchars($rental['borrower_name']) . " (ID: " . $rental['borrower_id'] . ")<br>";
                echo "Status: " . $rental['status'] . "<br>";
                echo "Is Completed: " . ($rental['is_completed'] ? 'Yes' : 'No') . "<br>";
                echo "</div>";
            }
        }
    } else {
        foreach ($rental_activity as $activity) {
            echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
            echo "<h4>📦 " . htmlspecialchars($activity['dress_name']) . "</h4>";
            echo "<p><strong>Role:</strong> " . $activity['user_role'] . "</p>";
            echo "<p><strong>Status:</strong> " . ucfirst($activity['status']) . "</p>";
            echo "<p><strong>Is Completed:</strong> " . ($activity['is_completed'] ? 'Yes' : 'No') . "</p>";
            
            if ($activity['status'] == 'completed') {
                echo "<p><a href='review_system.php?rental_id=" . $activity['id'] . "' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>⭐ Leave Review</a></p>";
            }
            echo "</div>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Database Error: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug Order Activity</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
    </style>
</head>
<body>
    <hr>
    <h3>🔗 Quick Links:</h3>
    <a href="test_completion.php">Test Completion</a> |
    <a href="order_activity.php">Order Activity</a> |
    <a href="order_requests.php">Order Requests</a>
</body>
</html>
