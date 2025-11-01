<?php
session_start();
require_once 'db_connect.php';

if (!isLoggedIn()) {
    echo "Please login first: <a href='login.php'>Login</a>";
    exit();
}

$user = getCurrentUser();
echo "<h2>Simple Test</h2>";
echo "<p><strong>You are:</strong> " . htmlspecialchars($user['fullname']) . " (ID: " . $user['id'] . ")</p>";

// Test different user IDs
$test_ids = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

echo "<h3>Test Links:</h3>";
foreach ($test_ids as $id) {
    echo "<p><a href='view_user.php?id=" . $id . "' target='_blank'>View User ID " . $id . "</a></p>";
}

echo "<h3>Your Profile:</h3>";
echo "<p><a href='profile.php' target='_blank'>My Profile</a></p>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        a { color: #800000; text-decoration: none; margin: 5px; display: block; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
</body>
</html>
