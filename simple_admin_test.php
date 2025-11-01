<?php
session_start();
require_once 'db_connect.php';

echo "<h2>Simple Admin Test</h2>";

$email = 'admin@sajilowear.com';
$password = 'admin123';

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<p>✅ User found: " . $user['fullname'] . "</p>";
        echo "<p>Email: " . $user['email'] . "</p>";
        echo "<p>User Type: " . ($user['user_type'] ?? 'NULL') . "</p>";
        
        // Test password
        if (password_verify($password, $user['password'])) {
            echo "<p>✅ Password is CORRECT!</p>";
            echo "<p><a href='admin_dashboard.php' style='background: #800000; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Dashboard</a></p>";
        } else {
            echo "<p>❌ Password is wrong!</p>";
            echo "<p>Let's create a new password hash...</p>";
            
            // Create new password hash
            $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
            echo "<p>New hash: " . $new_hash . "</p>";
            
            // Update password
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$new_hash, $email]);
            
            echo "<p>✅ Password updated! Try again:</p>";
            echo "<p><a href='simple_admin_test.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Again</a></p>";
        }
    } else {
        echo "<p>❌ User not found!</p>";
    }
} catch (PDOException $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
