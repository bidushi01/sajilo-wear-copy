<?php
session_start();
require_once 'db_connect.php';

if (!isLoggedIn()) {
    echo "Please login first: <a href='login.php'>Login</a>";
    exit();
}

$user = getCurrentUser();
echo "<h2>🔍 Debug Reviewer Link</h2>";
echo "<p><strong>You are:</strong> " . htmlspecialchars($user['fullname']) . " (ID: " . $user['id'] . ")</p>";

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
            echo "<div style='border: 2px solid #800000; padding: 20px; margin: 20px 0; border-radius: 10px; background: #fff5f5;'>";
            echo "<h4>🔍 Review Details:</h4>";
            echo "<p><strong>Reviewer Name:</strong> " . htmlspecialchars($review['reviewer_name']) . "</p>";
            echo "<p><strong>Reviewer ID:</strong> " . $review['reviewer_id'] . "</p>";
            echo "<p><strong>Your ID:</strong> " . $user['id'] . "</p>";
            echo "<p><strong>Review ID:</strong> " . $review['id'] . "</p>";
            echo "<p><strong>Rating:</strong> " . $review['rating'] . "/5</p>";
            echo "<p><strong>Comment:</strong> " . htmlspecialchars($review['comment']) . "</p>";
            
            echo "<h4>🔗 Test Links:</h4>";
            echo "<p><strong>Link to reviewer:</strong> <a href='view_user.php?id=" . $review['reviewer_id'] . "' target='_blank' style='color: #800000; font-weight: bold; font-size: 18px;'>Click here to go to " . htmlspecialchars($review['reviewer_name']) . "'s profile</a></p>";
            echo "<p><strong>Your profile:</strong> <a href='profile.php' target='_blank' style='color: #666;'>profile.php</a></p>";
            
            // Test if the reviewer actually exists
            $stmt2 = $pdo->prepare("SELECT id, fullname, email FROM users WHERE id = ?");
            $stmt2->execute([$review['reviewer_id']]);
            $reviewer_info = $stmt2->fetch();
            
            if ($reviewer_info) {
                echo "<p style='color: green;'>✅ Reviewer exists: " . htmlspecialchars($reviewer_info['fullname']) . " (ID: " . $reviewer_info['id'] . ")</p>";
            } else {
                echo "<p style='color: red;'>❌ Reviewer NOT found with ID: " . $review['reviewer_id'] . "</p>";
            }
            echo "</div>";
        }
    }
    
    // Also show all users to compare
    echo "<h3>👥 All Users in Database:</h3>";
    $stmt = $pdo->query("SELECT id, fullname, email FROM users ORDER BY id");
    $all_users = $stmt->fetchAll();
    
    foreach ($all_users as $u) {
        $style = ($u['id'] == $user['id']) ? "background: #e6f3ff; border-left: 4px solid #007bff;" : "background: #f9f9f9;";
        echo "<div style='padding: 10px; margin: 5px 0; border-radius: 5px; " . $style . "'>";
        echo "<strong>ID " . $u['id'] . ":</strong> " . htmlspecialchars($u['fullname']) . " (" . htmlspecialchars($u['email']) . ")";
        if ($u['id'] == $user['id']) {
            echo " <em>(This is you)</em>";
        }
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug Reviewer</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        a { text-decoration: none; }
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
