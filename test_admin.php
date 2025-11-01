<?php
session_start();
require_once 'db_connect.php';

echo "<h2>Admin Login Test</h2>";

$email = 'admin@sajilowear.com';
$password = 'admin123';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<p><strong>User Found:</strong> " . $user['fullname'] . "</p>";
        echo "<p><strong>Email:</strong> " . $user['email'] . "</p>";
        echo "<p><strong>User Type:</strong> " . ($user['user_type'] ?? 'NULL') . "</p>";
        echo "<p><strong>Is Active:</strong> " . ($user['is_active'] ?? 'NULL') . "</p>";
        
        if (password_verify($password, $user['password'])) {
            echo "<p style='color: green;'><strong>✅ Password is CORRECT!</strong></p>";
            
            if (isset($user['user_type']) && $user['user_type'] === 'admin') {
                echo "<p style='color: green;'><strong>✅ User is ADMIN!</strong></p>";
                echo "<p><a href='admin_dashboard.php'>Go to Admin Dashboard</a></p>";
            } else {
                echo "<p style='color: red;'><strong>❌ User is NOT admin (type: " . ($user['user_type'] ?? 'NULL') . ")</strong></p>";
            }
        } else {
            echo "<p style='color: red;'><strong>❌ Password is WRONG!</strong></p>";
        }
    } else {
        echo "<p style='color: red;'><strong>❌ User not found!</strong></p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'><strong>Database Error:</strong> " . $e->getMessage() . "</p>";
}
?>
