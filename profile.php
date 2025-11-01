<?php
session_start();
require_once 'db_connect.php';

// Require login
requireLogin();

$user = getCurrentUser();
$error = '';
$success = '';

// Handle profile photo update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_photo'])) {
    if (isset($_FILES['new_profile_photo']) && $_FILES['new_profile_photo']['error'] == 0) {
        $upload_dir = 'uploads/profile_photos/';
        $file_extension = strtolower(pathinfo($_FILES['new_profile_photo']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['new_profile_photo']['tmp_name'], $upload_path)) {
                // Delete old profile photo if it's not default
                if ($user['profile_photo'] != 'default_profile.jpg' && file_exists($upload_dir . $user['profile_photo'])) {
                    unlink($upload_dir . $user['profile_photo']);
                }
                
                // Update database
                $stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
                $stmt->execute([$new_filename, $user['id']]);
                
                // Update session
                $_SESSION['profile_photo'] = $new_filename;
                $user['profile_photo'] = $new_filename;
                
                $success = 'Profile photo updated successfully!';
            } else {
                $error = 'Failed to upload profile photo.';
            }
        } else {
            $error = 'Please upload a valid image file (JPG, PNG, GIF).';
        }
    } else {
        $error = 'Please select a profile photo to upload.';
    }
}

// Get user's clothes
$stmt = $pdo->prepare("SELECT * FROM clothes WHERE user_id = ? ORDER BY posted_date DESC");
$stmt->execute([$user['id']]);
$user_clothes = $stmt->fetchAll();

// Get rental requests for user's clothes
$stmt = $pdo->prepare("
    SELECT r.*, c.name as dress_name, u.fullname as borrower_name 
    FROM rentals r 
    JOIN clothes c ON r.dress_id = c.id 
    JOIN users u ON r.borrower_id = u.id 
    WHERE r.owner_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$user['id']]);
$rental_requests = $stmt->fetchAll();

// Get user's rental activity
$stmt = $pdo->prepare("
    SELECT r.*, c.name as dress_name, c.image as dress_image, u.fullname as owner_name 
    FROM rentals r 
    JOIN clothes c ON r.dress_id = c.id 
    JOIN users u ON r.owner_id = u.id 
    WHERE r.borrower_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$user['id']]);
$rental_activity = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - SajiloWear</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .profile-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }
        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 4px solid #007bff;
        }
        .profile-info h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .profile-info p {
            color: #666;
            margin: 5px 0;
        }
        .profile-actions {
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .btn-primary {
            background: #800000;
            color: white;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(128,0,0,0.3);
        }
        .btn-primary:hover {
            background: #a00000;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(128,0,0,0.4);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
            transform: translateY(-2px);
        }
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            color: white;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #1e7e34 0%, #155724 100%);
            transform: translateY(-2px);
        }
        .btn-danger {
            background: #800000;
            color: white;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-danger:hover {
            background: #a00000;
            transform: translateY(-2px);
        }
        .btn:hover {
            opacity: 0.8;
        }
        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .dashboard-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .dashboard-card h3 {
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .cloth-item, .rental-item {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .cloth-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .rental-item {
            background: #f8f9fa;
        }
        .status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-requested {
            background: #fff3cd;
            color: #856404;
        }
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        .status-returned {
            background: #d1ecf1;
            color: #0c5460;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        
        /* Reviews Section Styles */
        .reviews-section {
            margin-top: 40px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .reviews-section h2 {
            color: #800000;
            margin-bottom: 20px;
        }
        
        .rating-summary {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .average-rating {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .rating-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #800000;
        }
        
        .stars {
            font-size: 1.5em;
        }
        
        .review-count {
            color: #666;
            font-size: 1.1em;
        }
        
        .reviews-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .review-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .review-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .reviewer-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .reviewer-info {
            flex: 1;
        }
        
        .reviewer-info strong {
            display: block;
            color: #333;
            margin-bottom: 5px;
        }
        
        .reviewer-info strong a {
            color: #800000;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .reviewer-info strong a:hover {
            color: #a00000;
            text-decoration: underline;
        }
        
        .review-stars {
            font-size: 1.2em;
        }
        
        .review-date {
            color: #666;
            font-size: 0.9em;
        }
        
        .review-comment {
            color: #333;
            line-height: 1.5;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div><b>SajiloWear</b></div>
        <div>
            <a href="home.php">Home</a>
            <a href="home.php#featured">Browse</a>
            <a href="list_cloth.php">List Your Cloth</a>
            <a href="home.php#about">About</a>
            <a href="home.php#contact">Contact</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        </div>
    </div>

    <div class="profile-container">
        <div class="profile-header">
            <img src="uploads/profile_photos/<?php echo htmlspecialchars($user['profile_photo']); ?>" 
                 alt="Profile Photo" class="profile-photo" 
                 onerror="this.src='uploads/profile_photos/default_profile.jpg'">
            
            <div class="profile-info">
                <h1>👋 Welcome, <?php echo htmlspecialchars($user['fullname']); ?>!</h1>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($user['location']); ?></p>
                <p><strong>Last Login:</strong> <?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'First time login'; ?></p>
            </div>

            <div class="profile-actions">
                <button class="btn btn-primary" onclick="openModal('photoModal')">Change Profile Photo</button>
                <a href="list_cloth.php" class="btn btn-primary">List Your Traditional Cloth</a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="dashboard">
            <div class="dashboard-card">
                <h3>👗 Your Dress Listings</h3>
                <p>Manage your traditional clothes that are available for rent</p>
                <a href="my_dresses.php" class="btn btn-primary">View All My Dresses</a>
            </div>

            <div class="dashboard-card">
                <h3>📋 Order Requests</h3>
                <p>Manage rental requests for your traditional clothes</p>
                <a href="order_requests.php" class="btn btn-primary">View All Requests</a>
            </div>

            <div class="dashboard-card">
                <h3>📊 Order Activity</h3>
                <p>Track your rental requests and activities</p>
                <a href="order_activity.php" class="btn btn-primary">View All Activity</a>
            </div>
        </div>
    </div>

    <!-- Change Profile Photo Modal -->
    <div id="photoModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('photoModal')">&times;</span>
            <h2>Change Profile Photo</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_photo" value="1">
                <div style="margin: 20px 0;">
                    <label for="new_profile_photo">Select New Photo:</label>
                    <input type="file" id="new_profile_photo" name="new_profile_photo" accept="image/*" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Photo</button>
            </form>
        </div>
    </div>
    
    <!-- Reviews Section -->
    <div class="reviews-section">
        <h2>⭐ Reviews About You</h2>
        <?php
        // Get reviews for this user
        try {
            $stmt = $pdo->prepare("
                SELECT r.*, u.fullname as reviewer_name, u.profile_photo as reviewer_photo
                FROM reviews r
                JOIN users u ON r.reviewer_id = u.id
                WHERE r.reviewee_id = ?
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$user['id']]);
            $reviews = $stmt->fetchAll();
            
            if (empty($reviews)) {
                echo "<p style='text-align: center; color: #666; padding: 20px;'>No reviews yet.</p>";
            } else {
                // Calculate average rating
                $total_rating = 0;
                foreach ($reviews as $review) {
                    $total_rating += $review['rating'];
                }
                $average_rating = count($reviews) > 0 ? round($total_rating / count($reviews), 1) : 0;
                
                echo "<div class='rating-summary'>";
                echo "<div class='average-rating'>";
                echo "<span class='rating-number'>{$average_rating}</span>";
                echo "<div class='stars'>";
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $average_rating) {
                        echo "⭐";
                    } else {
                        echo "☆";
                    }
                }
                echo "</div>";
                echo "<span class='review-count'>(" . count($reviews) . " reviews)</span>";
                echo "</div>";
                echo "</div>";
                
                echo "<div class='reviews-list'>";
                foreach ($reviews as $review) {
                    echo "<div class='review-item'>";
                    echo "<div class='review-header'>";
                    echo "<img src='uploads/profile_photos/" . htmlspecialchars($review['reviewer_photo']) . "' alt='Reviewer' class='reviewer-photo'>";
                    echo "<div class='reviewer-info'>";
                    echo "<strong><a href='view_user.php?id=" . $review['reviewer_id'] . "' style='color: #800000; text-decoration: none;'>" . htmlspecialchars($review['reviewer_name']) . "</a></strong>";
                    echo "<div class='review-stars'>";
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $review['rating']) {
                            echo "⭐";
                        } else {
                            echo "☆";
                        }
                    }
                    echo "</div>";
                    echo "</div>";
                    echo "<div class='review-date'>" . date('M d, Y', strtotime($review['created_at'])) . "</div>";
                    echo "</div>";
                    if (!empty($review['comment'])) {
                        echo "<div class='review-comment'>" . htmlspecialchars($review['comment']) . "</div>";
                    }
                    echo "</div>";
                }
                echo "</div>";
            }
        } catch (PDOException $e) {
            echo "<p style='text-align: center; color: #666; padding: 20px;'>Error loading reviews.</p>";
        }
        ?>
    </div>
    
    <!-- Reviews You Wrote Section -->
    <div class="reviews-section">
        <h2>⭐ Reviews You Wrote</h2>
        <?php
        // Get reviews written by this user
        try {
            $stmt = $pdo->prepare("
                SELECT r.*, u.fullname as reviewee_name, u.profile_photo as reviewee_photo
                FROM reviews r
                JOIN users u ON r.reviewee_id = u.id
                WHERE r.reviewer_id = ?
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$user['id']]);
            $reviews_written = $stmt->fetchAll();
            
            if (empty($reviews_written)) {
                echo "<p style='text-align: center; color: #666; padding: 20px;'>You haven't written any reviews yet.</p>";
            } else {
                echo "<div class='reviews-list'>";
                foreach ($reviews_written as $review) {
                    echo "<div class='review-item'>";
                    echo "<div class='review-header'>";
                    echo "<img src='uploads/profile_photos/" . htmlspecialchars($review['reviewee_photo']) . "' alt='Reviewee' class='reviewer-photo'>";
                    echo "<div class='reviewer-info'>";
                    echo "<strong>You reviewed: <a href='view_user.php?id=" . $review['reviewee_id'] . "' style='color: #800000; text-decoration: none;'>" . htmlspecialchars($review['reviewee_name']) . "</a></strong>";
                    echo "<div class='review-stars'>";
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $review['rating']) {
                            echo "⭐";
                        } else {
                            echo "☆";
                        }
                    }
                    echo "</div>";
                    echo "</div>";
                    echo "<div class='review-date'>" . date('M d, Y', strtotime($review['created_at'])) . "</div>";
                    echo "</div>";
                    if (!empty($review['comment'])) {
                        echo "<div class='review-comment'>" . htmlspecialchars($review['comment']) . "</div>";
                    }
                    echo "</div>";
                }
                echo "</div>";
            }
        } catch (PDOException $e) {
            echo "<p style='text-align: center; color: #666; padding: 20px;'>Error loading reviews.</p>";
        }
        ?>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).style.display = 'block';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        window.onclick = function(event) {
            document.querySelectorAll('.modal').forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
