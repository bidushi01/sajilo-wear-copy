<?php
session_start();
require_once 'db_connect.php';

if (!isLoggedIn()) {
    echo "Please login first: <a href='login.php'>Login</a>";
    exit();
}

$user = getCurrentUser();
echo "<h2>🔍 Test Reviewer ID Issue</h2>";
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
            echo "<div style='border: 2px solid #800000; padding: 20px; margin: 20px 0; border-radius: 10px;'>";
            echo "<h4>Review by: " . htmlspecialchars($review['reviewer_name']) . "</h4>";
            echo "<p><strong>Reviewer ID:</strong> " . $review['reviewer_id'] . "</p>";
            echo "<p><strong>Your ID:</strong> " . $user['id'] . "</p>";
            echo "<p><strong>Review ID:</strong> " . $review['id'] . "</p>";
            
            // Check if reviewer ID is same as your ID
            if ($review['reviewer_id'] == $user['id']) {
                echo "<p style='color: red; font-weight: bold; background: #ffe6e6; padding: 10px; border-radius: 5px;'>❌ PROBLEM: Reviewer ID (" . $review['reviewer_id'] . ") is same as your ID (" . $user['id'] . ")!</p>";
                echo "<p>This means the review was created incorrectly - you reviewed yourself!</p>";
            } else {
                echo "<p style='color: green; background: #e6ffe6; padding: 10px; border-radius: 5px;'>✅ Reviewer ID is different from your ID</p>";
            }
            
            // Show the actual link
            $link = "view_user.php?id=" . $review['reviewer_id'];
            echo "<p><strong>Link to reviewer:</strong> <a href='" . $link . "' target='_blank' style='background: #800000; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Click to go to " . htmlspecialchars($review['reviewer_name']) . "'s profile</a></p>";
            
            echo "</div>";
        }
    }
    
    // Show all users to help identify the issue
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
    <title>Test Reviewer ID</title>
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
