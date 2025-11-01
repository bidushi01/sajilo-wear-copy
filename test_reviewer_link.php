<?php
session_start();
require_once 'db_connect.php';

if (!isLoggedIn()) {
    echo "Please login first: <a href='login.php'>Login</a>";
    exit();
}

$user = getCurrentUser();
echo "<h2>🔍 Test Reviewer Link Issue</h2>";
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
            echo "<h4>Review by: " . htmlspecialchars($review['reviewer_name']) . "</h4>";
            echo "<p><strong>Reviewer ID:</strong> " . $review['reviewer_id'] . "</p>";
            echo "<p><strong>Your ID:</strong> " . $user['id'] . "</p>";
            echo "<p><strong>Link should go to:</strong> view_user.php?id=" . $review['reviewer_id'] . "</p>";
            echo "<p><strong>Test link:</strong> <a href='view_user.php?id=" . $review['reviewer_id'] . "' target='_blank'>Click here to test</a></p>";
            echo "<p><strong>Your profile link:</strong> <a href='profile.php' target='_blank'>profile.php</a></p>";
            echo "</div>";
        }
    }
    
    // Also check what happens when we try to get the reviewer's info
    if (!empty($reviews)) {
        $reviewer_id = $reviews[0]['reviewer_id'];
        echo "<h3>🔍 Testing Reviewer ID: " . $reviewer_id . "</h3>";
        
        $stmt = $pdo->prepare("SELECT id, fullname, email FROM users WHERE id = ?");
        $stmt->execute([$reviewer_id]);
        $reviewer_info = $stmt->fetch();
        
        if ($reviewer_info) {
            echo "<p>✅ Reviewer found: " . htmlspecialchars($reviewer_info['fullname']) . " (ID: " . $reviewer_info['id'] . ")</p>";
        } else {
            echo "<p>❌ Reviewer not found with ID: " . $reviewer_id . "</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Reviewer Link</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        a { color: #800000; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <hr>
    <h3>🔗 Quick Links:</h3>
    <a href="profile.php">👤 My Profile</a> |
    <a href="home.php">🏠 Home</a>
</body>
</html>
