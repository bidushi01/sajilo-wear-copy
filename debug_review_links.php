<?php
session_start();
require_once 'db_connect.php';

if (!isLoggedIn()) {
    echo "Please login first: <a href='login.php'>Login</a>";
    exit();
}

$user = getCurrentUser();
echo "<h2>🔍 Debug Review Links</h2>";
echo "<p><strong>Current User:</strong> " . htmlspecialchars($user['fullname']) . " (ID: " . $user['id'] . ")</p>";

try {
    // Get reviews for this user
    $stmt = $pdo->prepare("
        SELECT r.*, u.fullname as reviewer_name, u.profile_photo as reviewer_photo
        FROM reviews r
        JOIN users u ON r.reviewer_id = u.id
        WHERE r.reviewee_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $reviews = $stmt->fetchAll();
    
    echo "<h3>📊 Reviews About You:</h3>";
    if (empty($reviews)) {
        echo "<p>No reviews found.</p>";
    } else {
        foreach ($reviews as $review) {
            echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
            echo "<h4>Review Details:</h4>";
            echo "<p><strong>Review ID:</strong> " . $review['id'] . "</p>";
            echo "<p><strong>Reviewer ID:</strong> " . $review['reviewer_id'] . "</p>";
            echo "<p><strong>Reviewer Name:</strong> " . htmlspecialchars($review['reviewer_name']) . "</p>";
            echo "<p><strong>Rating:</strong> " . $review['rating'] . "/5</p>";
            echo "<p><strong>Comment:</strong> " . htmlspecialchars($review['comment']) . "</p>";
            echo "<p><strong>Reviewer Link:</strong> <a href='view_user.php?id=" . $review['reviewer_id'] . "'>view_user.php?id=" . $review['reviewer_id'] . "</a></p>";
            echo "<p><strong>Your Profile Link:</strong> <a href='profile.php'>profile.php</a></p>";
            echo "</div>";
        }
    }
    
    // Also check if there are any reviews in the database
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM reviews");
    $total_reviews = $stmt->fetch()['total'];
    echo "<p><strong>Total reviews in database:</strong> " . $total_reviews . "</p>";
    
    if ($total_reviews > 0) {
        echo "<h4>All reviews in database:</h4>";
        $stmt = $pdo->query("
            SELECT r.*, u.fullname as reviewer_name, v.fullname as reviewee_name
            FROM reviews r 
            JOIN users u ON r.reviewer_id = u.id
            JOIN users v ON r.reviewee_id = v.id
            ORDER BY r.created_at DESC
        ");
        $all_reviews = $stmt->fetchAll();
        
        foreach ($all_reviews as $review) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px 0; background: #f9f9f9;'>";
            echo "<strong>" . htmlspecialchars($review['reviewer_name']) . "</strong> reviewed <strong>" . htmlspecialchars($review['reviewee_name']) . "</strong><br>";
            echo "Reviewer ID: " . $review['reviewer_id'] . " | Reviewee ID: " . $review['reviewee_id'] . "<br>";
            echo "Rating: " . $review['rating'] . "/5 | Comment: " . htmlspecialchars($review['comment']) . "<br>";
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
    <title>Debug Review Links</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
    </style>
</head>
<body>
    <hr>
    <h3>🔗 Quick Links:</h3>
    <a href="profile.php">👤 My Profile</a> |
    <a href="view_user.php?id=10">👤 View User 10</a> |
    <a href="home.php">🏠 Home</a>
</body>
</html>
