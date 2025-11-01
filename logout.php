<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit();
}

// Destroy session
session_destroy();

// Redirect to home page with success message
header('Location: home.php?logout=success');
exit();
?>
