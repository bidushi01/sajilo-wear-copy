<?php
session_start();
require_once 'db_connect.php';

$email = 'admin@sajilowear.com';
$password = 'admin123';

echo "<h2>Debug Login Process</h2>";

try {
    // Test 1: Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    echo "<h3>Step 1: User Lookup</h3>";
    if ($user) {
        echo "<p>✅ User found: " . $user['fullname'] . "</p>";
        echo "<p>Email: " . $user['email'] . "</p>";
        echo "<p>User Type: " . ($user['user_type'] ?? 'NULL') . "</p>";
        echo "<p>Is Active: " . ($user['is_active'] ?? 'NULL') . "</p>";
    } else {
        echo "<p>❌ User not found!</p>";
        exit;
    }
    
    // Test 2: Check password
    echo "<h3>Step 2: Password Check</h3>";
    if (password_verify($password, $user['password'])) {
        echo "<p>✅ Password is correct!</p>";
    } else {
        echo "<p>❌ Password is wrong!</p>";
        echo "<p>Stored hash: " . substr($user['password'], 0, 20) . "...</p>";
        echo "<p>Testing with: " . $password . "</p>";
        exit;
    }
    
    // Test 3: Check active status
    echo "<h3>Step 3: Active Status Check</h3>";
    if ($user['is_active'] == 1 || $user['is_active'] === null) {
        echo "<p>✅ User is active!</p>";
    } else {
        echo "<p>❌ User is not active!</p>";
        exit;
    }
    
    // Test 4: Check user type
    echo "<h3>Step 4: User Type Check</h3>";
    if (isset($user['user_type']) && $user['user_type'] === 'admin') {
        echo "<p>✅ User is admin!</p>";
        echo "<p><a href='admin_dashboard.php'>Go to Admin Dashboard</a></p>";
    } else {
        echo "<p>❌ User is not admin (type: " . ($user['user_type'] ?? 'NULL') . ")</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Database Error: " . $e->getMessage() . "</p>";
}
?>
