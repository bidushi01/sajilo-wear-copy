<?php
session_start();
require_once 'db_connect.php';

// Require login
requireLogin();

$user = getCurrentUser();
$action = $_GET['action'] ?? '';
$request_id = intval($_GET['id'] ?? 0);

if (!$request_id || !in_array($action, ['approve', 'decline', 'complete'])) {
    header('Location: order_requests.php');
    exit();
}

try {
    // Get the request details
    $stmt = $pdo->prepare("
        SELECT r.*, c.name as dress_name, c.user_id as dress_owner_id 
        FROM rentals r 
        JOIN clothes c ON r.dress_id = c.id 
        WHERE r.id = ? AND r.owner_id = ?
    ");
    $stmt->execute([$request_id, $user['id']]);
    $request = $stmt->fetch();
    
    if (!$request) {
        header('Location: order_requests.php?error=Request not found');
        exit();
    }
    
    if ($action == 'complete' && $request['status'] != 'approved') {
        header('Location: order_requests.php?error=Can only complete approved requests');
        exit();
    }
    
    if (in_array($action, ['approve', 'decline']) && $request['status'] != 'requested') {
        header('Location: order_requests.php?error=Request already processed');
        exit();
    }
    
    if ($action == 'approve') {
        // Approve the request
        $stmt = $pdo->prepare("UPDATE rentals SET status = 'approved' WHERE id = ?");
        $stmt->execute([$request_id]);
        
        // Mark dress as unavailable
        $stmt = $pdo->prepare("UPDATE clothes SET is_available = 0 WHERE id = ?");
        $stmt->execute([$request['dress_id']]);
        
        header('Location: order_requests.php?success=Request approved successfully');
    } elseif ($action == 'decline') {
        // Decline the request
        $stmt = $pdo->prepare("UPDATE rentals SET status = 'declined' WHERE id = ?");
        $stmt->execute([$request_id]);
        
        header('Location: order_requests.php?success=Request declined');
    } elseif ($action == 'complete') {
        // Mark as completed
        $stmt = $pdo->prepare("UPDATE rentals SET status = 'completed', is_completed = 1 WHERE id = ?");
        $stmt->execute([$request_id]);
        
        header('Location: order_requests.php?success=Rental marked as completed! Both parties can now leave reviews.');
    }
    
} catch (PDOException $e) {
    header('Location: order_requests.php?error=Database error: ' . urlencode($e->getMessage()));
}

exit();
?>
